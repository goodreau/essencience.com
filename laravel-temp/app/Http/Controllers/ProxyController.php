<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProxyController extends Controller
{
    public function proxy(Request $request, ?string $target = null, ?string $path = null)
    {
        if (!config('proxy.enabled')) {
            abort(404);
        }

        // Guard: local-only unless explicitly allowed
        if (!app()->environment('local') && !config('proxy.allow_public')) {
            abort(403, 'Proxy not available in this environment');
        }

        $upstreams = (array) config('proxy.upstreams', []);
        $defaultKey = (string) config('proxy.default', '');
        $key = $target ?: $defaultKey;
        if ($key === '' || !array_key_exists($key, $upstreams)) {
            abort(404, 'Unknown proxy target');
        }

        $base = rtrim((string) $upstreams[$key], '/');
        $url = $base . '/' . ltrim((string) ($path ?? ''), '/');

        // Optional host allow-list
        $allowedHosts = (array) config('proxy.allowed_hosts', []);
        $host = parse_url($base, PHP_URL_HOST);
        if (empty($allowedHosts)) {
            $allowedHosts = array_filter([$host]);
        }
        if ($host && !in_array($host, $allowedHosts, true)) {
            abort(403, 'Target host is not allowed');
        }

        $client = new Client([
            'timeout' => (int) config('proxy.timeout', 30),
            'http_errors' => false, // surface upstream status codes to client
        ]);

        $method = strtoupper($request->getMethod());

        // Forward a safe subset of headers
        $forwardHeaders = [];
        $allowedForward = array_map('strtolower', (array) config('proxy.forward_headers', []));
        foreach ($request->headers->all() as $name => $values) {
            $lname = strtolower($name);
            if (in_array($lname, ['host', 'content-length', 'connection', 'expect'], true)) {
                continue;
            }
            if (!empty($allowedForward) && !in_array($lname, $allowedForward, true)) {
                continue;
            }
            $forwardHeaders[$name] = implode(', ', $values);
        }

        $options = [
            'headers' => $forwardHeaders,
            'query' => $request->query(),
            'stream' => true,
        ];

        // Body handling
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            if (count($request->allFiles()) > 0) {
                // Build multipart for files + fields
                $multipart = [];
                foreach ($request->all() as $n => $v) {
                    if (is_array($v)) {
                        foreach ($v as $vv) {
                            $multipart[] = ['name' => $n . '[]', 'contents' => (string) $vv];
                        }
                    } else {
                        $multipart[] = ['name' => $n, 'contents' => (string) $v];
                    }
                }
                foreach ($request->allFiles() as $name => $file) {
                    if (is_array($file)) {
                        foreach ($file as $f) {
                            $multipart[] = [
                                'name' => $name . '[]',
                                'contents' => fopen($f->getRealPath(), 'r'),
                                'filename' => $f->getClientOriginalName(),
                                'headers' => ['Content-Type' => $f->getMimeType()],
                            ];
                        }
                    } else {
                        $multipart[] = [
                            'name' => $name,
                            'contents' => fopen($file->getRealPath(), 'r'),
                            'filename' => $file->getClientOriginalName(),
                            'headers' => ['Content-Type' => $file->getMimeType()],
                        ];
                    }
                }
                $options['multipart'] = $multipart;
            } else {
                $options['body'] = $request->getContent();
                if ($request->headers->has('Content-Type')) {
                    $options['headers']['Content-Type'] = $request->headers->get('Content-Type');
                }
            }
        }

        $res = $client->request($method, $url, $options);

        $status = $res->getStatusCode();
        $respHeaders = [];
        $hopByHop = ['connection', 'keep-alive', 'proxy-authenticate', 'proxy-authorization', 'te', 'trailers', 'transfer-encoding', 'upgrade'];
        foreach ($res->getHeaders() as $name => $values) {
            if (in_array(strtolower($name), $hopByHop, true)) {
                continue;
            }
            $respHeaders[$name] = implode(', ', $values);
        }

        $body = $res->getBody();

        return new StreamedResponse(function () use ($body) {
            while (!$body->eof()) {
                echo $body->read(8192);
                @ob_flush();
                flush();
            }
        }, $status, $respHeaders);
    }
}
