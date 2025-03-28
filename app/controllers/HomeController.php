<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\YoutubeChannelModel;
use App\Models\VideoModel;
use App\Models\ScanLogModel;

class HomeController extends Controller
{
    private $channelModel;
    private $videoModel;
    private $scanLogModel;
    
    public function __construct()
    {
        $this->channelModel = $this->model('YoutubeChannelModel');
        $this->videoModel = $this->model('VideoModel');
        $this->scanLogModel = $this->model('ScanLogModel');
    }
    
    /**
     * Dashboard page
     */
    public function index()
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Get statistics
        $totalChannels = $this->channelModel->count();
        $videoStats = $this->videoModel->getProcessingStats();
        
        // Get recent videos
        $recentVideos = $this->videoModel->getVideosPaginated(1, 5);
        
        // Get recent scan logs
        $recentScans = $this->scanLogModel->getRecentScans(5);
        
        // Get channels with most videos
        $popularChannels = $this->channelModel->where('1 ORDER BY processed_count DESC LIMIT 5');
        
        // Render dashboard
        $this->render('home/index', [
            'total_channels' => $totalChannels,
            'video_stats' => $videoStats,
            'recent_videos' => $recentVideos['data'],
            'recent_scans' => $recentScans,
            'popular_channels' => $popularChannels
        ], 'Dashboard');
    }
    
    /**
     * Statistics page
     */
    public function statistics()
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Get video processing statistics
        $videoStats = $this->videoModel->getProcessingStats();
        
        // Get monthly statistics
        $monthlyScanStats = $this->scanLogModel->getMonthlyStats();
        $monthlyVideoStats = $this->videoModel->where('1 GROUP BY YEAR(created_at), MONTH(created_at) ORDER BY created_at DESC LIMIT 12');
        
        // Get processing time statistics
        $processingTimeStats = $this->videoModel->query(
            "SELECT AVG(TIMESTAMPDIFF(SECOND, processing_started, processing_completed)) as avg_time, 
            status FROM videos WHERE processing_started IS NOT NULL AND processing_completed IS NOT NULL 
            GROUP BY status"
        )->fetchAll();
        
        // Render statistics page
        $this->render('home/statistics', [
            'video_stats' => $videoStats,
            'monthly_scan_stats' => $monthlyScanStats,
            'monthly_video_stats' => $monthlyVideoStats,
            'processing_time_stats' => $processingTimeStats
        ], 'Statistics');
    }
    
    /**
     * Search page
     */
    public function search()
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Get search term
        $search = $_GET['q'] ?? '';
        
        if (empty($search)) {
            $this->redirect('/');
        }
        
        // Search in videos
        $videos = $this->videoModel->getVideosPaginated(1, 10, $search);
        
        // Search in channels
        $channels = $this->channelModel->getChannelsPaginated(1, 10, $search);
        
        // Render search results
        $this->render('home/search', [
            'search' => $search,
            'videos' => $videos['data'],
            'channels' => $channels['data'],
            'total_videos' => $videos['total'],
            'total_channels' => $channels['total']
        ], 'Search Results: ' . $search);
    }
    
    /**
     * Settings page
     */
    public function settings()
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Load system settings model
        $settingsModel = $this->model('SystemSettingModel');
        
        // Get all settings grouped by category
        $settings = $settingsModel->getAllSettingsGrouped();
        
        // Render settings page
        $this->render('home/settings', [
            'settings' => $settings
        ], 'System Settings');
    }
    
    /**
     * Save settings
     */
    public function saveSettings()
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings');
        }
        
        // Load system settings model
        $settingsModel = $this->model('SystemSettingModel');
        
        // Get posted settings
        $settingsData = $_POST['settings'] ?? [];
        
        // Save settings
        foreach ($settingsData as $key => $value) {
            $settingsModel->updateSetting($key, $value);
        }
        
        // Redirect back to settings page
        $_SESSION['success_message'] = 'Settings saved successfully.';
        $this->redirect('/settings');
    }
    
    /**
     * Help page
     */
    public function help()
    {
        // Render help page
        $this->render('home/help', [], 'Help & Documentation');
    }
    
    /**
     * About page
     */
    public function about()
    {
        // Render about page
        $this->render('home/about', [], 'About YouTube Processor');
    }
}
