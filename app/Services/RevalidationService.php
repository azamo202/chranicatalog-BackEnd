<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RevalidationService
{
    /**
     * Send a cache revalidation request to the Next.js frontend.
     *
     * @param array $tags
     * @param array $paths
     * @return void
     */
    public static function revalidate(array $tags = [], array $paths = [])
    {
        $url = config('services.next.revalidate_url');
        $secret = config('services.next.revalidate_secret');

        if (empty($url) || empty($secret)) {
            Log::warning('Next.js Revalidation skipped: NEXT_REVALIDATE_URL or NEXT_REVALIDATE_SECRET is not set.');
            return;
        }

        try {
            $response = Http::timeout(3)
                ->withHeaders([
                    'x-revalidate-secret' => $secret,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($url, [
                    'tags' => $tags,
                    'paths' => $paths,
                ]);

            if ($response->failed()) {
                Log::error('Next.js Revalidation Failed. Status: ' . $response->status() . '. Response: ' . $response->body());
            } else {
                Log::info('Next.js Revalidation Succeeded. Tags: ' . implode(', ', $tags));
            }
        } catch (\Exception $e) {
            Log::error('Next.js Revalidation Exception: ' . $e->getMessage());
        }
    }
}
