<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\YoutubeChannelModel;
use App\Models\VideoModel;
use App\Models\ScanLogModel;
use App\Helpers\YoutubeApiHelper;

class ChannelController extends Controller
{
    private $channelModel;
    private $videoModel;
    private $scanLogModel;
    private $youtubeHelper;
    
    public function __construct()
    {
        $this->channelModel = $this->model('YoutubeChannelModel');
        $this->videoModel = $this->model('VideoModel');
        $this->scanLogModel = $this->model('ScanLogModel');
        $this->youtubeHelper = new YoutubeApiHelper();
    }
    
    /**
     * List all channels
     */
    public function index()
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Get page number
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page = max(1, $page);
        
        // Get search term
        $search = $_GET['search'] ?? '';
        
        // Get channels with pagination
        $channels = $this->channelModel->getChannelsPaginated($page, 12, $search);
        
        // Render channels page
        $this->render('channel/index', [
            'channels' => $channels,
            'search' => $search
        ], 'YouTube Channels');
    }
    
    /**
     * Add a new channel
     */
    public function add()
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get channel URL
            $channelUrl = $_POST['channel_url'] ?? '';
            $scanFrequency = $_POST['scan_frequency'] ?? 'daily';
            
            // Validate input
            if (empty($channelUrl)) {
                $_SESSION['error_message'] = 'Please enter a valid YouTube channel URL.';
                $this->redirect('/channel/add');
            }
            
            // Extract channel ID from URL
            $channelId = $this->youtubeHelper->extractChannelId($channelUrl);
            
            if (!$channelId) {
                $_SESSION['error_message'] = 'Invalid YouTube channel URL. Please check and try again.';
                $this->redirect('/channel/add');
            }
            
            // Check if channel already exists
            if ($this->channelModel->channelExists($channelId)) {
                $_SESSION['error_message'] = 'This channel is already in your list.';
                $this->redirect('/channel/add');
            }
            
            // Get channel details from YouTube API
            $channelDetails = $this->youtubeHelper->getChannelDetails($channelId);
            
            if (!$channelDetails) {
                $_SESSION['error_message'] = 'Could not fetch channel details from YouTube. Please check the URL and try again.';
                $this->redirect('/channel/add');
            }
            
            // Add channel to database
            $channelData = [
                'channel_id' => $channelDetails['channel_id'],
                'channel_name' => $channelDetails['channel_name'],
                'avatar_url' => $channelDetails['avatar_url'],
                'banner_url' => $channelDetails['banner_url'],
                'subscriber_count' => $channelDetails['subscriber_count'],
                'video_count' => $channelDetails['video_count'],
                'processed_count' => 0,
                'scan_frequency' => $scanFrequency,
                'next_scan' => date('Y-m-d H:i:s'),
            ];
            
            $channelId = $this->channelModel->addChannel($channelData);
            
            if ($channelId) {
                $_SESSION['success_message'] = 'Channel added successfully.';
                $this->redirect('/channel/view/' . $channelId);
            } else {
                $_SESSION['error_message'] = 'Failed to add channel. Please try again.';
                $this->redirect('/channel/add');
            }
        }
        
        // Render add channel form
        $this->render('channel/add', [
            'scan_frequencies' => [
                'hourly' => 'Every Hour',
                '6_hours' => 'Every 6 Hours',
                '12_hours' => 'Every 12 Hours',
                'daily' => 'Once a Day',
                'weekly' => 'Once a Week'
            ]
        ], 'Add YouTube Channel');
    }
    
    /**
     * View channel details
     * 
     * @param int $id Channel ID
     */
    public function view($id)
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Get channel statistics
        $channel = $this->channelModel->getStatistics($id);
        
        if (!$channel) {
            $_SESSION['error_message'] = 'Channel not found.';
            $this->redirect('/channel');
        }
        
        // Get page number
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page = max(1, $page);
        
        // Get status filter
        $status = $_GET['status'] ?? null;
        
        // Get videos by channel
        $videos = $this->videoModel->getVideosByChannel($id, $page, 10, $status);
        
        // Get recent scan logs
        $scanLogs = $this->scanLogModel->getChannelScans($id, 5);
        
        // Render channel view
        $this->render('channel/view', [
            'channel' => $channel,
            'videos' => $videos,
            'scan_logs' => $scanLogs,
            'current_status' => $status
        ], $channel['channel_name']);
    }
    
    /**
     * Edit channel
     * 
     * @param int $id Channel ID
     */
    public function edit($id)
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Get channel
        $channel = $this->channelModel->find($id);
        
        if (!$channel) {
            $_SESSION['error_message'] = 'Channel not found.';
            $this->redirect('/channel');
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $scanFrequency = $_POST['scan_frequency'] ?? 'daily';
            
            // Update channel
            $updateData = [
                'scan_frequency' => $scanFrequency,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            // Calculate next scan time based on frequency
            $frequency = config("scan.frequency_options.{$scanFrequency}", 86400); // Default to daily
            $updateData['next_scan'] = date('Y-m-d H:i:s', time() + $frequency);
            
            $success = $this->channelModel->update($id, $updateData);
            
            if ($success) {
                $_SESSION['success_message'] = 'Channel updated successfully.';
                $this->redirect('/channel/view/' . $id);
            } else {
                $_SESSION['error_message'] = 'Failed to update channel. Please try again.';
            }
        }
        
        // Render edit channel form
        $this->render('channel/edit', [
            'channel' => $channel,
            'scan_frequencies' => [
                'hourly' => 'Every Hour',
                '6_hours' => 'Every 6 Hours',
                '12_hours' => 'Every 12 Hours',
                'daily' => 'Once a Day',
                'weekly' => 'Once a Week'
            ]
        ], 'Edit ' . $channel['channel_name']);
    }
    
    /**
     * Delete channel
     * 
     * @param int $id Channel ID
     */
    public function delete($id)
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Allow only POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/channel');
        }
        
        // Get channel
        $channel = $this->channelModel->find($id);
        
        if (!$channel) {
            $_SESSION['error_message'] = 'Channel not found.';
            $this->redirect('/channel');
        }
        
        // Delete channel
        $success = $this->channelModel->delete($id);
        
        if ($success) {
            $_SESSION['success_message'] = 'Channel deleted successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to delete channel. Please try again.';
        }
        
        $this->redirect('/channel');
    }
    
    /**
     * Scan channel for new videos
     * 
     * @param int $id Channel ID
     */
    public function scan($id)
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Get channel
        $channel = $this->channelModel->find($id);
        
        if (!$channel) {
            $_SESSION['error_message'] = 'Channel not found.';
            $this->redirect('/channel');
        }
        
        // Create scan log
        $scanLogId = $this->scanLogModel->create([
            'channel_id' => $id,
            'start_time' => date('Y-m-d H:i:s'),
            'status' => 'processing',
            'videos_found' => 0,
            'videos_added' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Get latest videos from YouTube API
        $lastScan = $channel['last_scan'] ?? null;
        $publishedAfter = $lastScan ? date('c', strtotime($lastScan)) : null;
        
        $videos = $this->youtubeHelper->getChannelVideos($channel['channel_id'], 50, $publishedAfter);
        
        if ($videos === false) {
            // Update scan log
            $this->scanLogModel->update($scanLogId, [
                'end_time' => date('Y-m-d H:i:s'),
                'status' => 'error',
                'error_message' => 'Failed to fetch videos from YouTube API.'
            ]);
            
            $_SESSION['error_message'] = 'Failed to scan channel. Please try again.';
            $this->redirect('/channel/view/' . $id);
        }
        
        // Update scan log with videos found
        $this->scanLogModel->update($scanLogId, [
            'videos_found' => count($videos)
        ]);
        
        // Add new videos to database
        $addedCount = 0;
        
        foreach ($videos as $video) {
            if (!$this->videoModel->videoExists($video['youtube_id'])) {
                $videoData = [
                    'youtube_id' => $video['youtube_id'],
                    'channel_id' => $id,
                    'title' => $video['title'],
                    'description' => $video['description'],
                    'thumbnail_url' => $video['thumbnail_url'],
                    'publish_date' => date('Y-m-d H:i:s', strtotime($video['publish_date'])),
                    'duration' => $video['duration'],
                    'status' => 'pending'
                ];
                
                $videoId = $this->videoModel->addVideo($videoData);
                
                if ($videoId) {
                    $addedCount++;
                }
            }
        }
        
        // Update scan log
        $this->scanLogModel->update($scanLogId, [
            'end_time' => date('Y-m-d H:i:s'),
            'status' => 'completed',
            'videos_added' => $addedCount
        ]);
        
        // Update channel scan schedule
        $this->channelModel->updateScanSchedule(
            $id, 
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s', time() + config("scan.frequency_options.{$channel['scan_frequency']}", 86400))
        );
        
        $_SESSION['success_message'] = "Channel scanned successfully. Found {$addedCount} new videos.";
        $this->redirect('/channel/view/' . $id);
    }
    
    /**
     * View scan history
     * 
     * @param int $id Channel ID
     */
    public function scanHistory($id)
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Get channel
        $channel = $this->channelModel->find($id);
        
        if (!$channel) {
            $_SESSION['error_message'] = 'Channel not found.';
            $this->redirect('/channel');
        }
        
        // Get page number
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page = max(1, $page);
        
        // Get scan logs with pagination
        $scanLogs = $this->scanLogModel->getChannelScansPaginated($id, $page, 20);
        
        // Render scan history
        $this->render('channel/scan_history', [
            'channel' => $channel,
            'scan_logs' => $scanLogs
        ], 'Scan History: ' . $channel['channel_name']);
    }
}
