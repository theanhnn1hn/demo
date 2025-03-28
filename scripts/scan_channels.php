<?php
/**
 * Scan YouTube channels for new videos
 * 
 * This script is designed to be run as a cron job.
 * Example cron entry (run every hour):
 * 0 * * * * /usr/bin/php /path/to/youtube-processor/scripts/scan_channels.php
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Change to project root directory
chdir(BASE_PATH);

// Load autoloader
require 'vendor/autoload.php';

// Load environment variables
if (file_exists('.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();
}

// Initialize database connection
$db = App\Core\Database::getInstance();

// Load models
$channelModel = new App\Models\YoutubeChannelModel();
$videoModel = new App\Models\VideoModel();
$scanLogModel = new App\Models\ScanLogModel();

// Initialize YouTube API helper
$youtubeHelper = new App\Helpers\YoutubeApiHelper();

// Log function
function logMessage($message) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

logMessage('Starting channel scan job...');

// Get channels that need to be scanned
$channels = $channelModel->getChannelsToScan();

if (empty($channels)) {
    logMessage('No channels need scanning at this time.');
    exit(0);
}

logMessage('Found ' . count($channels) . ' channels to scan.');

// Get scan limit
$scanLimit = (int) config('scan.max_videos_per_scan');

// Scan each channel
foreach ($channels as $channel) {
    logMessage("Scanning channel: {$channel['channel_name']} (ID: {$channel['channel_id']})");
    
    // Create scan log
    $scanLogId = $scanLogModel->create([
        'channel_id' => $channel['id'],
        'start_time' => date('Y-m-d H:i:s'),
        'status' => 'processing',
        'videos_found' => 0,
        'videos_added' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    try {
        // Get latest videos from YouTube API
        $lastScan = $channel['last_scan'] ?? null;
        $publishedAfter = $lastScan ? date('c', strtotime($lastScan)) : null;
        
        $videos = $youtubeHelper->getChannelVideos($channel['channel_id'], $scanLimit, $publishedAfter);
        
        if ($videos === false) {
            throw new Exception('Failed to fetch videos from YouTube API.');
        }
        
        // Update scan log with videos found
        $scanLogModel->update($scanLogId, [
            'videos_found' => count($videos)
        ]);
        
        logMessage("Found " . count($videos) . " videos.");
        
        // Add new videos to database
        $addedCount = 0;
        
        foreach ($videos as $video) {
            if (!$videoModel->videoExists($video['youtube_id'])) {
                $videoData = [
                    'youtube_id' => $video['youtube_id'],
                    'channel_id' => $channel['id'],
                    'title' => $video['title'],
                    'description' => $video['description'],
                    'thumbnail_url' => $video['thumbnail_url'],
                    'publish_date' => date('Y-m-d H:i:s', strtotime($video['publish_date'])),
                    'duration' => $video['duration'],
                    'status' => 'pending'
                ];
                
                $videoId = $videoModel->addVideo($videoData);
                
                if ($videoId) {
                    $addedCount++;
                    logMessage("Added video: {$video['title']} (ID: {$video['youtube_id']})");
                }
            } else {
                logMessage("Skipping existing video: {$video['title']} (ID: {$video['youtube_id']})");
            }
        }
        
        // Update scan log
        $scanLogModel->update($scanLogId, [
            'end_time' => date('Y-m-d H:i:s'),
            'status' => 'completed',
            'videos_added' => $addedCount
        ]);
        
        // Calculate next scan time based on frequency
        $frequency = $channel['scan_frequency'] ?? 'daily';
        $frequencySeconds = config("scan.frequency_options.{$frequency}", 86400); // Default to daily
        $nextScan = date('Y-m-d H:i:s', time() + $frequencySeconds);
        
        // Update channel scan schedule
        $channelModel->updateScanSchedule($channel['id'], date('Y-m-d H:i:s'), $nextScan);
        
        logMessage("Scan completed for channel {$channel['channel_name']}. Added {$addedCount} videos. Next scan: {$nextScan}");
    } catch (Exception $e) {
        // Update scan log with error
        $scanLogModel->update($scanLogId, [
            'end_time' => date('Y-m-d H:i:s'),
            'status' => 'error',
            'error_message' => $e->getMessage()
        ]);
        
        logMessage("Error scanning channel {$channel['channel_name']}: " . $e->getMessage());
    }
}

logMessage('Channel scan job completed.');
