<?php

namespace App\Services;

class YoutubeService
{
    /**
     * Extract the YouTube Video ID from a URL or return the string if it's already an ID.
     */
    public static function extractId(?string $input): ?string
    {
        if (empty($input)) {
            return null;
        }

        // Trim whitespace
        $input = trim($input);

        // If it's already a raw 11-character ID
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $input)) {
            return $input;
        }

        // Match common YouTube URL patterns
        // https://youtu.be/VIDEO_ID
        // https://www.youtube.com/watch?v=VIDEO_ID
        // https://youtube.com/watch?v=VIDEO_ID
        // https://www.youtube.com/embed/VIDEO_ID
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';
        
        if (preg_match($pattern, $input, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
