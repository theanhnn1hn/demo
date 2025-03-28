<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\VideoModel;
use App\Models\RewrittenContentModel;
use App\Models\GeneratedImageModel;
use App\Models\ExportedProjectModel;
use App\Helpers\ExportHelper;

class ExportController extends Controller
{
    private $videoModel;
    private $rewrittenContentModel;
    private $generatedImageModel;
    private $exportedProjectModel;
    private $exportHelper;
    
    public function __construct()
    {
        $this->videoModel = $this->model('VideoModel');
        $this->rewrittenContentModel = $this->model('RewrittenContentModel');
        $this->generatedImageModel = $this->model('GeneratedImageModel');
        $this->exportedProjectModel = $this->model('ExportedProjectModel');
        $this->exportHelper = new ExportHelper();
    }
    
    /**
     * Export options page
     * 
     * @param int $id Video ID
     */
    public function index($id)
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Get video
        $video = $this->videoModel->find($id);
        
        if (!$video) {
            $_SESSION['error_message'] = 'Video not found.';
            $this->redirect('/video');
        }
        
        // Check if video has been processed
        if ($video['status'] !== 'completed') {
            $_SESSION['error_message'] = 'Video has not been fully processed yet.';
            $this->redirect('/video/view/' . $id);
        }
        
        // Get rewritten content
        $rewrittenContent = $this->rewrittenContentModel->getByVideoId($id);
        
        if (!$rewrittenContent) {
            $_SESSION['error_message'] = 'No rewritten content found for this video.';
            $this->redirect('/video/view/' . $id);
        }
        
        // Get generated images
        $generatedImages = $this->generatedImageModel->getByContentSectionId($id, 'video');
        
        // Get previous exports
        $previousExports = $this->exportedProjectModel->getByVideoId($id);
        
        // Render export options page
        $this->render('export/index', [
            'video' => $video,
            'rewritten_content' => $rewrittenContent,
            'generated_images' => $generatedImages,
            'previous_exports' => $previousExports,
            'export_formats' => [
                'markdown' => 'Markdown Document',
                'html' => 'HTML Document',
                'pdf' => 'PDF Document',
                'json' => 'JSON Data',
                'text' => 'Plain Text'
            ]
        ], 'Export Project: ' . $video['title']);
    }
    
    /**
     * Export project
     * 
     * @param int $id Video ID
     */
    public function export($id)
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Allow only POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/export/' . $id);
        }
        
        // Get video
        $video = $this->videoModel->find($id);
        
        if (!$video) {
            $_SESSION['error_message'] = 'Video not found.';
            $this->redirect('/video');
        }
        
        // Get export options
        $exportFormat = $_POST['export_format'] ?? 'markdown';
        $includeSubtitles = isset($_POST['include_subtitles']);
        $includeImages = isset($_POST['include_images']);
        $includePrompts = isset($_POST['include_prompts']);
        
        try {
            // Get rewritten content
            $rewrittenContent = $this->rewrittenContentModel->getByVideoId($id);
            
            if (!$rewrittenContent) {
                throw new \Exception('No rewritten content found for this video.');
            }
            
            // Get generated images if needed
            $generatedImages = $includeImages ? $this->generatedImageModel->getByContentSectionId($id, 'video') : [];
            
            // Export project
            $exportResult = $this->exportHelper->exportProject(
                $video,
                $rewrittenContent,
                $generatedImages,
                $exportFormat,
                $includeSubtitles,
                $includePrompts
            );
            
            if (!$exportResult) {
                throw new \Exception('Failed to export project.');
            }
            
            // Create export record
            $exportData = [
                'video_id' => $id,
                'export_format' => $exportFormat,
                'include_subtitles' => $includeSubtitles ? 1 : 0,
                'include_images' => $includeImages ? 1 : 0,
                'include_prompts' => $includePrompts ? 1 : 0,
                'export_path' => $exportResult['file_path'],
                'exported_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->exportedProjectModel->create($exportData);
            
            // Redirect to download
            header('Content-Type: ' . $exportResult['content_type']);
            header('Content-Disposition: attachment; filename="' . $exportResult['filename'] . '"');
            header('Content-Length: ' . filesize($exportResult['file_path']));
            readfile($exportResult['file_path']);
            exit;
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Export error: ' . $e->getMessage();
            $this->redirect('/export/' . $id);
        }
    }
    
    /**
     * Download previously exported project
     * 
     * @param int $id Export ID
     */
    public function download($id)
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Get export record
        $export = $this->exportedProjectModel->find($id);
        
        if (!$export) {
            $_SESSION['error_message'] = 'Export not found.';
            $this->redirect('/video');
        }
        
        // Get file path
        $filePath = $export['export_path'];
        
        if (!file_exists($filePath)) {
            $_SESSION['error_message'] = 'Export file not found.';
            $this->redirect('/video/view/' . $export['video_id']);
        }
        
        // Get content type based on format
        $contentType = $this->getContentTypeForFormat($export['export_format']);
        
        // Generate filename
        $filename = 'export_' . $export['video_id'] . '_' . date('Ymd', strtotime($export['exported_at'])) . '.' . $this->getExtensionForFormat($export['export_format']);
        
        // Output file
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
    
    /**
     * Delete exported project
     * 
     * @param int $id Export ID
     */
    public function delete($id)
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Allow only POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/video');
        }
        
        // Get export record
        $export = $this->exportedProjectModel->find($id);
        
        if (!$export) {
            $_SESSION['error_message'] = 'Export not found.';
            $this->redirect('/video');
        }
        
        // Get video ID
        $videoId = $export['video_id'];
        
        // Delete file
        if (file_exists($export['export_path'])) {
            unlink($export['export_path']);
        }
        
        // Delete record
        $this->exportedProjectModel->delete($id);
        
        $_SESSION['success_message'] = 'Export deleted successfully.';
        $this->redirect('/export/' . $videoId);
    }
    
    /**
     * Preview exported content
     * 
     * @param int $id Video ID
     */
    public function preview($id)
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Get video
        $video = $this->videoModel->find($id);
        
        if (!$video) {
            $_SESSION['error_message'] = 'Video not found.';
            $this->redirect('/video');
        }
        
        // Get rewritten content
        $rewrittenContent = $this->rewrittenContentModel->getByVideoId($id);
        
        if (!$rewrittenContent) {
            $_SESSION['error_message'] = 'No rewritten content found for this video.';
            $this->redirect('/video/view/' . $id);
        }
        
        // Get generated images
        $generatedImages = $this->generatedImageModel->getByContentSectionId($id, 'video');
        
        // Group images by section
        $imagesBySections = [];
        foreach ($generatedImages as $image) {
            $sectionType = $image['content_section_type'];
            $imagesBySections[$sectionType] = $image;
        }
        
        // Render preview page
        $this->render('export/preview', [
            'video' => $video,
            'rewritten_content' => $rewrittenContent,
            'images' => $imagesBySections
        ], 'Preview: ' . $video['title']);
    }
    
    /**
     * Get content type for export format
     */
    private function getContentTypeForFormat($format)
    {
        switch ($format) {
            case 'markdown':
                return 'text/markdown';
            
            case 'html':
                return 'text/html';
            
            case 'pdf':
                return 'application/pdf';
            
            case 'json':
                return 'application/json';
            
            case 'text':
            default:
                return 'text/plain';
        }
    }
    
    /**
     * Get file extension for export format
     */
    private function getExtensionForFormat($format)
    {
        switch ($format) {
            case 'markdown':
                return 'md';
            
            case 'html':
                return 'html';
            
            case 'pdf':
                return 'pdf';
            
            case 'json':
                return 'json';
            
            case 'text':
            default:
                return 'txt';
        }
    }
}
