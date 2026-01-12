<?php

namespace Essencience\Passport\Middleware;

use Closure;
use Essencience\Passport\Services\PassportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateWithCertificate
{
    public function __construct(
        protected PassportService $passportService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!config('passport.enabled')) {
            return $next($request);
        }

        // Get certificate from request
        $certPem = $this->passportService->getCertificateFromRequest($request);

        if (!$certPem) {
            return response()->json([
                'error' => 'Client certificate required'
            ], 401);
        }

        // Verify certificate
        $certInfo = $this->passportService->verifyCertificate($certPem);

        if (!$certInfo) {
            return response()->json([
                'error' => 'Invalid client certificate'
            ], 401);
        }

        // Find user by certificate
        $user = $this->passportService->findUserByCertificate($certPem);

        if (!$user) {
            // Auto-create user if enabled
            if (config('passport.auto_create_users')) {
                $user = $this->createUserFromCertificate($certInfo);
            } else {
                return response()->json([
                    'error' => 'User not found for certificate'
                ], 401);
            }
        }

        // Authenticate user
        Auth::login($user);
        $request->merge(['authenticated_via' => 'certificate']);

        return $next($request);
    }

    /**
     * Create user from certificate information
     */
    protected function createUserFromCertificate(array $certInfo)
    {
        $userModel = config('passport.user_model');

        return $userModel::create([
            'name' => $certInfo['cn'] ?? 'Unknown',
            'email' => $certInfo['email'],
            'email_verified_at' => now(),
        ]);
    }
}
