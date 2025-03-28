<?php
namespace App\Helpers;

use YoutubeDl\YoutubeDl;
use YoutubeDl\Exception\ExecutionException;
use YoutubeDl\Exception\NotFoundException;

class VideoDownloaderHelper
{
    private $youtubeDl;
    private $storagePath;
    
    public function __construct()
    {
        // Set storage path
        $this->storagePath = config('storage.videos');
        
        // Create storage directory if not exists
        if (!file_exists($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
        
        // Initialize youtube-dl
        $this->youtubeDl = new YoutubeDl();
        
        // Configure youtube-dl
        $this->youtubeDl->setBinPath(getenv('YOUTUBE_DL_PATH') ?: '/usr/local/bin/yt-dlp');
        $this->youtubeDl->setPythonPath(getenv('PYTHON_PATH') ?: '/usr/bin/python3');
    }
    
    /**
     * Download video
     *
     * @param string $videoId YouTube video ID
     * @param string $format Video format
     * @return array|false Video file info or false on failure
     */
    public function downloadVideo($videoId, $format = 'best')
    {
        try {
            $videoUrl = "https://www.youtube.com/watch?v={$videoId}";
            $outputFile = "{$this->storagePath}/{$videoId}.%(ext)s";
            
            // Set download options
            $this->youtubeDl->setOptions([
                'format' => $format,
                'output' => $outputFile,
                'no-cache-dir' => true,
                'no-playlist' => true,
                'write-info-json' => true,
                'write-thumbnail' => true,
            ]);
            
            // Download the video
            $downloadedVideo = $this->youtubeDl->download($videoUrl);
            
            // Get the downloaded video info
            $videoFiles = glob("{$this->storagePath}/{$videoId}.*");
            $videoFile = null;
            $thumbnailFile = null;
            $infoJsonFile = null;
            
            foreach ($videoFiles as $file) {
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if (in_array($ext, ['mp4', 'webm', 'mkv'])) {
                    $videoFile = $file;
                } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $thumbnailFile = $file;
                } elseif ($ext === 'json') {
                    $infoJsonFile = $file;
                }
            }
            
            if (!$videoFile) {
                return false;
            }
            
            // Parse info JSON if available
            $videoInfo = null;
            if ($infoJsonFile && file_exists($infoJsonFile)) {
                $infoJson = file_get_contents($infoJsonFile);
                $videoInfo = json_decode($infoJson, true);
            }
            
            return [
                'video_file' => $videoFile,
                'thumbnail_file' => $thumbnailFile,
                'info_file' => $infoJsonFile,
                'video_info' => $videoInfo,
                'size' => filesize($videoFile),
                'format' => pathinfo($videoFile, PATHINFO_EXTENSION),
            ];
        } catch (ExecutionException $e) {
            error_log('YouTube-DL Execution Error: ' . $e->getMessage());
            return false;
        } catch (NotFoundException $e) {
            error_log('YouTube-DL Not Found Error: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            error_log('YouTube-DL Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Extract audio from video
     *
     * @param string $videoPath Path to video file
     * @return string|false Path to audio file or false on failure
     */
    public function extractAudio($videoPath)
    {
        try {
            $pathInfo = pathinfo($videoPath);
            $audioPath = "{$pathInfo['dirname']}/{$pathInfo['filename']}.mp3";
            
            // Use FFmpeg to extract audio
            $command = "ffmpeg -i \"{$videoPath}\" -vn -ab 128k -ar 44100 -f mp3 \"{$audioPath}\"";
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                error_log('FFmpeg Error: ' . implode("\n", $output));
                return false;
            }
            
            return $audioPath;
        } catch (\Exception $e) {
            error_log('Audio Extraction Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete video files
     *
     * @param string $videoId YouTube video ID
     * @return bool Success
     */
    public function deleteVideoFiles($videoId)
    {
        try {
            $videoFiles = glob("{$this->storagePath}/{$videoId}.*");
            
            foreach ($videoFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            
            return true;
        } catch (\Exception $e) {
            error_log('Delete Video Files Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if video is already downloaded
     *
     * @param string $videoId YouTube video ID
     * @return bool Whether video is downloaded
     */
    public function isVideoDownloaded($videoId)
    {
        $videoFiles = glob("{$this->storagePath}/{$videoId}.*");
        
        foreach ($videoFiles as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (in_array($ext, ['mp4', 'webm', 'mkv'])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get video path
     *
     * @param string $videoId YouTube video ID
     * @return string|false Path to video file or false if not found
     */
    public function getVideoPath($videoId)
    {
        $videoFiles = glob("{$this->storagePath}/{$videoId}.*");
        
        foreach ($videoFiles as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (in_array($ext, ['mp4', 'webm', 'mkv'])) {
                return $file;
            }
        }
        
        return false;
    }
}
