<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\VideoModel;
use App\Models\YoutubeChannelModel;
use App\Models\VideoProcessingModel;
use App\Helpers\YoutubeApiHelper;

class VideoController extends Controller
{
    private $videoModel;
    private $channelModel;
    private $processingModel;
    private $youtubeHelper;
    
    public function __construct()
    {
        $this->videoModel = $this->model('VideoModel');
        $this->channelModel = $this->model('YoutubeChannelModel');
        $this->processingModel = $this->model('VideoProcessingModel');
        $this->youtubeHelper = new YoutubeApiHelper();
    }
    
    /**
     * List all videos
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
        
        // Get status filter
        $status = $_GET['status'] ?? null;
        
        // Get videos with pagination
        $videos = $this->videoModel->getVideosPaginated($page, 20, $search, $status);
        
        // Get channel data for all videos
        $channelIds = array_unique(array_column($videos['data'], 'channel_id'));
        $channels = [];
        
        if (!empty($channelIds)) {
            $channelData = $this->channelModel->where('id IN (' . implode(',', $channelIds) . ')');
            foreach ($channelData as $channel) {
                $channels[$channel['id']] = $channel;
            }
        }
        
        // Render videos page
        $this->render('video/index', [
            'videos' => $videos,
            'channels' => $channels,
            'search' => $search,
            'current_status' => $status,
            'statuses' => [
                'pending' => 'Pending',
                'processing' => 'Processing',
                'completed' => 'Completed',
                'error' => 'Error'
            ]
        ], 'All Videos');
    }
    
    /**
     * Add a new video
     */
    public function add()
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get video URL
            $videoUrl = $_POST['video_url'] ?? '';
            
            // Validate input
            if (empty($videoUrl)) {
                $_SESSION['error_message'] = 'Please enter a valid YouTube video URL.';
                $this->redirect('/video/add');
            }
            
            // Extract video ID from URL
            $videoId = $this->youtubeHelper->extractVideoId($videoUrl);
            
            if (!$videoId) {
                $_SESSION['error_message'] = 'Invalid YouTube video URL. Please check and try again.';
                $this->redirect('/video/add');
            }
            
            // Check if video already exists
            if ($this->videoModel->videoExists($videoId)) {
                $_SESSION['error_message'] = 'This video is already in your list.';
                $this->redirect('/video/add');
            }
            
            // Get video details from YouTube API
            $videoDetails = $this->youtubeHelper->getVideoDetails($videoId);
            
            if (!$videoDetails) {
                $_SESSION['error_message'] = 'Could not fetch video details from YouTube. Please check the URL and try again.';
                $this->redirect('/video/add');
            }
            
            // Check if channel exists, if not, add it
            $channelId = null;
            if (!$this->channelModel->channelExists($videoDetails['channel_id'])) {
                // Get channel details
                $channelDetails = $this->youtubeHelper->getChannelDetails($videoDetails['channel_id']);
                
                if ($channelDetails) {
                    // Add channel to database
                    $channelData = [
                        'channel_id' => $channelDetails['channel_id'],
                        'channel_name' => $channelDetails['channel_name'],
                        'avatar_url' => $channelDetails['avatar_url'],
                        'banner_url' => $channelDetails['banner_url'],
                        'subscriber_count' => $channelDetails['subscriber_count'],
                        'video_count' => $channelDetails['video_count'],
                        'processed_count' => 0,
                        'scan_frequency' => 'daily',
                        'next_scan' => date('Y-m-d H:i:s'),
                    ];
                    
                    $channelId = $this->channelModel->addChannel($channelData);
                }
            } else {
                // Get channel ID from database
                $channel = $this->channelModel->firstWhere("channel_id = ?", [$videoDetails['channel_id']]);
                $channelId = $channel['id'];
            }
            
            // Add video to database
            $videoData = [
                'youtube_id' => $videoDetails['youtube_id'],
                'channel_id' => $channelId,
                'title' => $videoDetails['title'],
                'description' => $videoDetails['description'],
                'thumbnail_url' => $videoDetails['thumbnail_url'],
                'publish_date' => date('Y-m-d H:i:s', strtotime($videoDetails['publish_date'])),
                'duration' => $videoDetails['duration'],
                'status' => 'pending'
            ];
            
            $newVideoId = $this->videoModel->addVideo($videoData);
            
            if ($newVideoId) {
                $_SESSION['success_message'] = 'Video added successfully.';
                $this->redirect('/video/view/' . $newVideoId);
            } else {
                $_SESSION['error_message'] = 'Failed to add video. Please try again.';
                $this->redirect('/video/add');
            }
        }
        
        // Render add video form
        $this->render('video/add', [], 'Add YouTube Video');
    }
    
    /**
     * View video details
     * 
     * @param int $id Video ID
     */
    public function view($id)
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Get video with all related data
        $data = $this->videoModel->getVideoWithData($id);
        
        if (!$data || !$data['video']) {
            $_SESSION['error_message'] = 'Video not found.';
            $this->redirect('/video');
        }
        
        // Render video view
        $this->render('video/view', [
            'video' => $data['video'],
            'channel' => $data['channel'],
            'processing' => $data['processing'],
            'content_analysis' => $data['content_analysis'],
            'rewritten_content' => $data['rewritten_content'],
            'generated_images' => $data['generated_images'],
            'exported_projects' => $data['exported_projects']
        ], $data['video']['title']);
    }
    
    /**
     * Delete video
     * 
     * @param int $id Video ID
     */
    public function delete($id)
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Allow only POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/video');
        }
        
        // Get video
        $video = $this->videoModel->find($id);
        
        if (!$video) {
            $_SESSION['error_message'] = 'Video not found.';
            $this->redirect('/video');
        }
        
        // Delete video
        $success = $this->videoModel->delete($id);
        
        if ($success) {
            $_SESSION['success_message'] = 'Video deleted successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to delete video. Please try again.';
        }
        
        $this->redirect('/video');
    }
    
    /**
     * Process a video
     * 
     * @param int $id Video ID
     */
    public function process($id)
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Get video
        $video = $this->videoModel->find($id);
        
        if (!$video) {
            $_SESSION['error_message'] = 'Video not found.';
            $this->redirect('/video');
        }
        
        // Check if video is already processing or completed
        if ($video['status'] !== 'pending' && $video['status'] !== 'error') {
            $_SESSION['error_message'] = 'This video is already being processed or has been processed.';
            $this->redirect('/video/view/' . $id);
        }
        
        // Update video status to processing
        $this->videoModel->updateStatus($id, 'processing');
        
        // Redirect to processing configuration page
        $this->redirect('/processing/configure/' . $id);
    }
    
    /**
     * Reset video processing status
     * 
     * @param int $id Video ID
     */
    public function reset($id)
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Allow only POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/video');
        }
        
        // Get video
        $video = $this->videoModel->find($id);
        
        if (!$video) {
            $_SESSION['error_message'] = 'Video not found.';
            $this->redirect('/video');
        }
        
        // Reset video status to pending
        $success = $this->videoModel->updateStatus($id, 'pending');
        
        // Delete processing data
        $this->processingModel->deleteByVideoId($id);
        
        if ($success) {
            $_SESSION['success_message'] = 'Video processing status reset successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to reset video processing status. Please try again.';
        }
        
        $this->redirect('/video/view/' . $id);
    }
    
    /**
     * Batch process videos
     */
    public function batchProcess()
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Allow only POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/video');
        }
        
        // Get selected videos
        $videoIds = $_POST['video_ids'] ?? [];
        
        if (empty($videoIds)) {
            $_SESSION['error_message'] = 'No videos selected.';
            $this->redirect('/video');
        }
        
        // Update status for each video
        $processedCount = 0;
        
        foreach ($videoIds as $videoId) {
            $video = $this->videoModel->find($videoId);
            
            if ($video && ($video['status'] === 'pending' || $video['status'] === 'error')) {
                $this->videoModel->updateStatus($videoId, 'processing');
                $processedCount++;
            }
        }
        
        if ($processedCount > 0) {
            $_SESSION['success_message'] = "{$processedCount} videos added to processing queue.";
        } else {
            $_SESSION['error_message'] = 'No videos were added to the processing queue.';
        }
        
        $this->redirect('/video');
    }
    
    /**
     * API endpoint to get video status
     * 
     * @param int $id Video ID
     */
    public function status($id)
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Get video
        $video = $this->videoModel->find($id);
        
        if (!$video) {
            $this->json(['error' => 'Video not found.'], 404);
        }
        
        // Get processing status
        $processing = $this->processingModel->getByVideoId($id);
        
        // Return status as JSON
        $this->json([
            'id' => $video['id'],
            'status' => $video['status'],
            'processing_stage' => $processing ? $processing['processing_stage'] : null,
            'processing_status' => $processing ? $processing['processing_status'] : null,
            'started_at' => $video['processing_started'],
            'completed_at' => $video['processing_completed'],
            'error_message' => $video['error_message']
        ]);
    }
}
