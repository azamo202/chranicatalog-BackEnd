<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Services\YoutubeService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename the column
        Schema::table('support_videos', function (Blueprint $table) {
            $table->renameColumn('youtube_url', 'youtube_id');
        });

        // 2. Normalize existing data
        $videos = DB::table('support_videos')->get();
        foreach ($videos as $video) {
            $id = YoutubeService::extractId($video->youtube_id);
            if ($id && $id !== $video->youtube_id) {
                DB::table('support_videos')
                    ->where('id', $video->id)
                    ->update(['youtube_id' => $id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Revert data to URLs
        $videos = DB::table('support_videos')->get();
        foreach ($videos as $video) {
            if ($video->youtube_id && !str_contains($video->youtube_id, 'youtube.com') && !str_contains($video->youtube_id, 'youtu.be')) {
                DB::table('support_videos')
                    ->where('id', $video->id)
                    ->update(['youtube_id' => 'https://www.youtube.com/watch?v=' . $video->youtube_id]);
            }
        }

        // 2. Rename column back
        Schema::table('support_videos', function (Blueprint $table) {
            $table->renameColumn('youtube_id', 'youtube_url');
        });
    }
};
