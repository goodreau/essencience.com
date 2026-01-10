#!/bin/bash
# Create a Certificate Authority using macOS Keychain

DOMAIN="essencience.com"
CA_NAME="Essencience Root CA"

echo "Creating Certificate Authority for $DOMAIN..."

# 1. Create CA private key
openssl genrsa -out ~/essencience-ca-key.pem 4096

# 2. Create CA certificate (10 years validity)
openssl req -new -x509 -days 3650 -key ~/essencience-ca-key.pem -out ~/essencience-ca-cert.pem \
  -subj "/C=US/ST=State/L=City/O=Essencience/CN=$CA_NAME"

# 3. Import CA certificate to Keychain
sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain ~/essencience-ca-cert.pem

echo "✓ CA certificate created and trusted in Keychain"
echo "  CA Key: ~/essencience-ca-key.pem"
echo "  CA Cert: ~/essencience-ca-cert.pem"

# 4. Create server certificate signed by CA
openssl genrsa -out ~/essencience-server-key.pem 2048

# Create certificate signing request
openssl req -new -key ~/essencience-server-key.pem -out ~/essencience-server.csr \
  -subj "/C=US/ST=State/L=City/O=Essencience/CN=$DOMAIN"

# Sign with CA
openssl x509 -req -in ~/essencience-server.csr -CA ~/essencience-ca-cert.pem \
  -CAkey ~/essencience-ca-key.pem -CAcreateserial -out ~/essencience-server-cert.pem \
  -days 365 -sha256

# 5. Create PKCS12 format for import to Keychain
openssl pkcs12 -export -out ~/essencience-server.p12 \
  -inkey ~/essencience-server-key.pem \
  -in ~/essencience-server-cert.pem \
  -certfile ~/essencience-ca-cert.pem \
  -passout pass:essencience

# Import server certificate to Keychain
security import ~/essencience-server.p12 -k ~/Library/Keychains/login.keychain-db -P essencience

echo "✓ Server certificate created and imported to Keychain"
echo "  Server Key: ~/essencience-server-key.pem"
echo "  Server Cert: ~/essencience-server-cert.pem"
echo ""
echo "You can now use these certificates for HTTPS in Laravel"
