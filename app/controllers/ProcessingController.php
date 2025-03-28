<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\VideoModel;
use App\Models\VideoProcessingModel;
use App\Models\ContentAnalysisModel;
use App\Models\RewrittenContentModel;
use App\Helpers\VideoDownloaderHelper;
use App\Helpers\SpeechToTextHelper;
use App\Helpers\AiContentHelper;

class ProcessingController extends Controller
{
    private $videoModel;
    private $processingModel;
    private $contentAnalysisModel;
    private $rewrittenContentModel;
    private $videoDownloader;
    private $speechToText;
    private $aiContent;
    
    public function __construct()
    {
        $this->videoModel = $this->model('VideoModel');
        $this->processingModel = $this->model('VideoProcessingModel');
        $this->contentAnalysisModel = $this->model('ContentAnalysisModel');
        $this->rewrittenContentModel = $this->model('RewrittenContentModel');
        $this->videoDownloader = new VideoDownloaderHelper();
        $this->speechToText = new SpeechToTextHelper();
        $this->aiContent = new AiContentHelper();
    }
    
    /**
     * Configure processing options
     * 
     * @param int $id Video ID
     */
    public function configure($id)
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Get video
        $video = $this->videoModel->find($id);
        
        if (!$video) {
            $_SESSION['error_message'] = 'Video not found.';
            $this->redirect('/video');
        }
        
        // Check if video is in processing status
        if ($video['status'] !== 'processing') {
            $_SESSION['error_message'] = 'This video is not in processing state.';
            $this->redirect('/video/view/' . $id);
        }
        
        // Get default settings
        $settings = [
            'speech_to_text_api' => 'whisper',
            'content_analysis_api' => 'claude',
            'processing_language' => config('processing.default_language'),
            'content_tone' => config('processing.default_tone'),
            'rewrite_level' => 'complete',
            'change_names' => true,
            'change_locations' => true,
            'change_examples' => true,
            'add_details' => true,
            'image_api' => 'dall_e',
            'image_style' => config('image.default_style'),
            'image_prompt_template' => config('image.default_prompt_template'),
            'generate_images_for_all' => true,
            'content_sections' => [
                'hook' => true,
                'introduction' => true,
                'main_content' => true,
                'climax' => true,
                'twist' => true,
                'transition' => true,
                'controversy' => true,
                'engagement' => true,
                'conclusion' => true,
                'call_to_action' => true
            ]
        ];
        
        // Check if user has submitted configuration
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Update settings with user input
            $settings['speech_to_text_api'] = $_POST['speech_to_text_api'] ?? $settings['speech_to_text_api'];
            $settings['content_analysis_api'] = $_POST['content_analysis_api'] ?? $settings['content_analysis_api'];
            $settings['processing_language'] = $_POST['processing_language'] ?? $settings['processing_language'];
            $settings['content_tone'] = $_POST['content_tone'] ?? $settings['content_tone'];
            $settings['rewrite_level'] = $_POST['rewrite_level'] ?? $settings['rewrite_level'];
            $settings['change_names'] = isset($_POST['change_names']);
            $settings['change_locations'] = isset($_POST['change_locations']);
            $settings['change_examples'] = isset($_POST['change_examples']);
            $settings['add_details'] = isset($_POST['add_details']);
            $settings['image_api'] = $_POST['image_api'] ?? $settings['image_api'];
            $settings['image_style'] = $_POST['image_style'] ?? $settings['image_style'];
            $settings['image_prompt_template'] = $_POST['image_prompt_template'] ?? $settings['image_prompt_template'];
            $settings['generate_images_for_all'] = isset($_POST['generate_images_for_all']);
            
            // Content sections
            $settings['content_sections']['hook'] = isset($_POST['sections']['hook']);
            $settings['content_sections']['introduction'] = isset($_POST['sections']['introduction']);
            $settings['content_sections']['main_content'] = isset($_POST['sections']['main_content']);
            $settings['content_sections']['climax'] = isset($_POST['sections']['climax']);
            $settings['content_sections']['twist'] = isset($_POST['sections']['twist']);
            $settings['content_sections']['transition'] = isset($_POST['sections']['transition']);
            $settings['content_sections']['controversy'] = isset($_POST['sections']['controversy']);
            $settings['content_sections']['engagement'] = isset($_POST['sections']['engagement']);
            $settings['content_sections']['conclusion'] = isset($_POST['sections']['conclusion']);
            $settings['content_sections']['call_to_action'] = isset($_POST['sections']['call_to_action']);
            
            // Create or update processing record
            $existingProcessing = $this->processingModel->getByVideoId($id);
            
            $processingData = [
                'video_id' => $id,
                'processing_stage' => 'configured',
                'processing_status' => 'pending',
                'processing_settings' => json_encode($settings),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($existingProcessing) {
                $this->processingModel->update($existingProcessing['id'], $processingData);
            } else {
                $processingData['created_at'] = date('Y-m-d H:i:s');
                $this->processingModel->create($processingData);
            }
            
            // Start processing if immediate option was selected
            if (isset($_POST['start_now'])) {
                return $this->startProcessing($id);
            }
            
            $_SESSION['success_message'] = 'Processing configuration saved.';
            $this->redirect('/processing/status/' . $id);
        }
        
        // Render configuration form
        $this->render('processing/configure', [
            'video' => $video,
            'settings' => $settings,
            'speech_to_text_options' => [
                'assembly_ai' => 'AssemblyAI',
                'rev_ai' => 'Rev.ai',
                'whisper' => 'OpenAI Whisper'
            ],
            'content_analysis_options' => [
                'claude' => 'Claude',
                'gpt4' => 'GPT-4',
                'gpt35' => 'GPT-3.5 Turbo'
            ],
            'language_options' => [
                'vi' => 'Tiếng Việt',
                'en' => 'English',
                'auto' => 'Auto Detect'
            ],
            'tone_options' => [
                'informative' => 'Informative - Educational',
                'humorous' => 'Humorous - Entertainment',
                'dramatic' => 'Dramatic - Serious',
                'persuasive' => 'Persuasive - Advertising',
                'emotional' => 'Emotional - Inspirational'
            ],
            'rewrite_level_options' => [
                'light' => 'Light (Keep most content, improve style)',
                'moderate' => 'Moderate (Significantly alter, same message)',
                'complete' => 'Complete (Entirely rewrite, same topic)'
            ],
            'image_api_options' => [
                'dall_e' => 'DALL-E 3',
                'midjourney' => 'Midjourney API',
                'stable_diffusion' => 'Stable Diffusion'
            ],
            'image_style_options' => [
                'realistic' => 'Realistic Photo',
                'cartoon' => 'Cartoon/Animation',
                'render3d' => '3D Render',
                'artistic' => 'Artistic Painting',
                'cinematic' => 'Cinematic Scene',
                'anime' => 'Anime/Manga'
            ]
        ], 'Configure Processing: ' . $video['title']);
    }
    
    /**
     * Start processing a video
     * 
     * @param int $id Video ID
     */
    public function startProcessing($id)
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Get video
        $video = $this->videoModel->find($id);
        
        if (!$video) {
            $_SESSION['error_message'] = 'Video not found.';
            $this->redirect('/video');
        }
        
        // Check if video is in processing status
        if ($video['status'] !== 'processing') {
            $_SESSION['error_message'] = 'This video is not in processing state.';
            $this->redirect('/video/view/' . $id);
        }
        
        // Get processing record
        $processing = $this->processingModel->getByVideoId($id);
        
        if (!$processing) {
            $_SESSION['error_message'] = 'No processing configuration found. Please configure the processing options first.';
            $this->redirect('/processing/configure/' . $id);
        }
        
        // Update processing status
        $this->processingModel->update($processing['id'], [
            'processing_stage' => 'initiated',
            'processing_status' => 'processing',
            'started_at' => date('Y-m-d H:i:s')
        ]);
        
        // Redirect to status page
        $_SESSION['success_message'] = 'Processing started.';
        $this->redirect('/processing/status/' . $id);
    }
    
    /**
     * View processing status
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
            $_SESSION['error_message'] = 'Video not found.';
            $this->redirect('/video');
        }
        
        // Get processing record
        $processing = $this->processingModel->getByVideoId($id);
        
        if (!$processing) {
            $_SESSION['error_message'] = 'No processing configuration found. Please configure the processing options first.';
            $this->redirect('/processing/configure/' . $id);
        }
        
        // Get content analysis and rewritten content if available
        $contentAnalysis = $this->contentAnalysisModel->getByVideoId($id);
        $rewrittenContent = $this->rewrittenContentModel->getByVideoId($id);
        
        // Render status page
        $this->render('processing/status', [
            'video' => $video,
            'processing' => $processing,
            'content_analysis' => $contentAnalysis,
            'rewritten_content' => $rewrittenContent,
            'processing_stages' => [
                'configured' => 'Configuration Saved',
                'initiated' => 'Processing Initiated',
                'downloading' => 'Downloading Video',
                'speech_to_text' => 'Converting Speech to Text',
                'content_analysis' => 'Analyzing Content',
                'rewriting' => 'Rewriting Content',
                'generating_images' => 'Generating Images',
                'completed' => 'Processing Completed'
            ]
        ], 'Processing Status: ' . $video['title']);
    }
    
    /**
     * Process video step by step (AJAX endpoint)
     * 
     * @param int $id Video ID
     */
    public function processStep($id)
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Get video
        $video = $this->videoModel->find($id);
        
        if (!$video) {
            $this->json(['error' => 'Video not found.'], 404);
        }
        
        // Get processing record
        $processing = $this->processingModel->getByVideoId($id);
        
        if (!$processing) {
            $this->json(['error' => 'No processing configuration found.'], 400);
        }
        
        // Get settings
        $settings = json_decode($processing['processing_settings'], true);
        
        // Switch based on processing stage
        switch ($processing['processing_stage']) {
            case 'configured':
            case 'initiated':
                // Start downloading video
                $this->processDownloadVideo($id, $video, $processing);
                break;
                
            case 'downloading':
                // Check if download is complete and proceed to speech to text
                $this->processSpeechToText($id, $video, $processing, $settings);
                break;
                
            case 'speech_to_text':
                // Check if speech to text is complete and proceed to content analysis
                $this->processContentAnalysis($id, $video, $processing, $settings);
                break;
                
            case 'content_analysis':
                // Check if content analysis is complete and proceed to rewriting
                $this->processRewriting($id, $video, $processing, $settings);
                break;
                
            case 'rewriting':
                // Check if rewriting is complete and proceed to image generation
                $this->processImageGeneration($id, $video, $processing, $settings);
                break;
                
            case 'generating_images':
                // Check if image generation is complete and finish
                $this->completeProcessing($id, $video, $processing);
                break;
                
            case 'completed':
                // Processing is already complete
                $this->json([
                    'status' => 'success',
                    'message' => 'Processing already completed.',
                    'processing_stage' => 'completed',
                    'processing_status' => 'completed',
                    'completed' => true
                ]);
                break;
        }
    }
    
    /**
     * Process download video step
     */
    private function processDownloadVideo($id, $video, $processing)
    {
        // Update processing record
        $this->processingModel->update($processing['id'], [
            'processing_stage' => 'downloading',
            'processing_status' => 'processing',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        try {
            // Check if video is already downloaded
            if ($this->videoDownloader->isVideoDownloaded($video['youtube_id'])) {
                $videoPath = $this->videoDownloader->getVideoPath($video['youtube_id']);
                
                // Update processing record
                $this->processingModel->update($processing['id'], [
                    'local_video_path' => $videoPath,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                // Return success
                $this->json([
                    'status' => 'success',
                    'message' => 'Video was already downloaded.',
                    'processing_stage' => 'downloading',
                    'processing_status' => 'completed',
                    'completed' => false
                ]);
            } else {
                // Download video
                $downloadResult = $this->videoDownloader->downloadVideo($video['youtube_id']);
                
                if ($downloadResult) {
                    // Update processing record
                    $this->processingModel->update($processing['id'], [
                        'local_video_path' => $downloadResult['video_file'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    // Return success
                    $this->json([
                        'status' => 'success',
                        'message' => 'Video downloaded successfully.',
                        'processing_stage' => 'downloading',
                        'processing_status' => 'completed',
                        'completed' => false
                    ]);
                } else {
                    // Update processing record with error
                    $this->processingModel->update($processing['id'], [
                        'processing_status' => 'error',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    // Update video status
                    $this->videoModel->updateStatus($id, 'error', 'Failed to download video.');
                    
                    // Return error
                    $this->json([
                        'status' => 'error',
                        'message' => 'Failed to download video.',
                        'processing_stage' => 'downloading',
                        'processing_status' => 'error',
                        'completed' => true
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Update processing record with error
            $this->processingModel->update($processing['id'], [
                'processing_status' => 'error',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Update video status
            $this->videoModel->updateStatus($id, 'error', 'Error during video download: ' . $e->getMessage());
            
            // Return error
            $this->json([
                'status' => 'error',
                'message' => 'Error during video download: ' . $e->getMessage(),
                'processing_stage' => 'downloading',
                'processing_status' => 'error',
                'completed' => true
            ]);
        }
    }
    
    /**
     * Process speech to text step
     */
    private function processSpeechToText($id, $video, $processing, $settings)
    {
        // Check if download is complete
        if ($processing['processing_status'] !== 'completed') {
            $this->json([
                'status' => 'info',
                'message' => 'Video download is still in progress.',
                'processing_stage' => 'downloading',
                'processing_status' => $processing['processing_status'],
                'completed' => false
            ]);
            return;
        }
        
        // Update processing record
        $this->processingModel->update($processing['id'], [
            'processing_stage' => 'speech_to_text',
            'processing_status' => 'processing',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        try {
            // Check if subtitle files already exist
            $transcript = $this->speechToText->getTranscript($video['youtube_id']);
            
            if ($transcript) {
                // Subtitle files already exist
                $this->processingModel->update($processing['id'], [
                    'subtitle_path' => config('storage.subtitles') . '/' . $video['youtube_id'] . '.txt',
                    'processing_status' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                // Return success
                $this->json([
                    'status' => 'success',
                    'message' => 'Speech to text conversion was already completed.',
                    'processing_stage' => 'speech_to_text',
                    'processing_status' => 'completed',
                    'completed' => false
                ]);
                return;
            }
            
            // Extract audio from video
            $videoPath = $processing['local_video_path'];
            $audioPath = $this->videoDownloader->extractAudio($videoPath);
            
            if (!$audioPath) {
                throw new \Exception('Failed to extract audio from video.');
            }
            
            // Update processing record with audio path
            $this->processingModel->update($processing['id'], [
                'audio_path' => $audioPath,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Convert speech to text based on selected API
            $language = $settings['processing_language'];
            $sttResult = null;
            
            switch ($settings['speech_to_text_api']) {
                case 'assembly_ai':
                    $sttResult = $this->speechToText->assemblyAiTranscribe($audioPath, $video['youtube_id'], $language);
                    break;
                
                case 'rev_ai':
                    $sttResult = $this->speechToText->revAiTranscribe($audioPath, $video['youtube_id'], $language);
                    break;
                
                case 'whisper':
                default:
                    $sttResult = $this->speechToText->whisperTranscribe($audioPath, $video['youtube_id'], $language);
                    break;
            }
            
            if (!$sttResult) {
                throw new \Exception('Speech to text conversion failed.');
            }
            
            // Update processing record
            $this->processingModel->update($processing['id'], [
                'subtitle_path' => $sttResult['subtitle_path']['text'],
                'processing_status' => 'completed',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Return success
            $this->json([
                'status' => 'success',
                'message' => 'Speech to text conversion completed successfully.',
                'processing_stage' => 'speech_to_text',
                'processing_status' => 'completed',
                'completed' => false
            ]);
        } catch (\Exception $e) {
            // Update processing record with error
            $this->processingModel->update($processing['id'], [
                'processing_status' => 'error',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Update video status
            $this->videoModel->updateStatus($id, 'error', 'Error during speech to text conversion: ' . $e->getMessage());
            
            // Return error
            $this->json([
                'status' => 'error',
                'message' => 'Error during speech to text conversion: ' . $e->getMessage(),
                'processing_stage' => 'speech_to_text',
                'processing_status' => 'error',
                'completed' => true
            ]);
        }
    }
    
    /**
     * Process content analysis step
     */
    private function processContentAnalysis($id, $video, $processing, $settings)
    {
        // Check if speech to text is complete
        if ($processing['processing_status'] !== 'completed') {
            $this->json([
                'status' => 'info',
                'message' => 'Speech to text conversion is still in progress.',
                'processing_stage' => 'speech_to_text',
                'processing_status' => $processing['processing_status'],
                'completed' => false
            ]);
            return;
        }
        
        // Update processing record
        $this->processingModel->update($processing['id'], [
            'processing_stage' => 'content_analysis',
            'processing_status' => 'processing',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        try {
            // Check if content analysis already exists
            $existingAnalysis = $this->contentAnalysisModel->getByVideoId($id);
            
            if ($existingAnalysis && $existingAnalysis['analysis_status'] === 'completed') {
                // Content analysis already exists
                $this->processingModel->update($processing['id'], [
                    'processing_status' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                // Return success
                $this->json([
                    'status' => 'success',
                    'message' => 'Content analysis was already completed.',
                    'processing_stage' => 'content_analysis',
                    'processing_status' => 'completed',
                    'completed' => false
                ]);
                return;
            }
            
            // Get transcript
            $transcriptPath = $processing['subtitle_path'];
            $transcript = file_get_contents($transcriptPath);
            
            if (!$transcript) {
                throw new \Exception('Failed to read transcript file.');
            }
            
            // Analyze content using AI
            $apiUsed = $settings['content_analysis_api'];
            $analysisResult = $this->aiContent->analyzeContent($transcript, $apiUsed);
            
            if (!$analysisResult) {
                throw new \Exception('Content analysis failed.');
            }
            
            // Create or update content analysis record
            $analysisData = [
                'video_id' => $id,
                'original_content' => $transcript,
                'structured_content' => json_encode($analysisResult['analysis']),
                'analysis_status' => 'completed',
                'api_used' => $apiUsed,
                'tokens_used' => $analysisResult['tokens_used'],
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($existingAnalysis) {
                $this->contentAnalysisModel->update($existingAnalysis['id'], $analysisData);
            } else {
                $analysisData['created_at'] = date('Y-m-d H:i:s');
                $this->contentAnalysisModel->create($analysisData);
            }
            
            // Update processing record
            $this->processingModel->update($processing['id'], [
                'processing_status' => 'completed',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Return success
            $this->json([
                'status' => 'success',
                'message' => 'Content analysis completed successfully.',
                'processing_stage' => 'content_analysis',
                'processing_status' => 'completed',
                'completed' => false
            ]);
        } catch (\Exception $e) {
            // Update processing record with error
            $this->processingModel->update($processing['id'], [
                'processing_status' => 'error',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Update content analysis record if it exists
            $existingAnalysis = $this->contentAnalysisModel->getByVideoId($id);
            if ($existingAnalysis) {
                $this->contentAnalysisModel->update($existingAnalysis['id'], [
                    'analysis_status' => 'error',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            // Update video status
            $this->videoModel->updateStatus($id, 'error', 'Error during content analysis: ' . $e->getMessage());
            
            // Return error
            $this->json([
                'status' => 'error',
                'message' => 'Error during content analysis: ' . $e->getMessage(),
                'processing_stage' => 'content_analysis',
                'processing_status' => 'error',
                'completed' => true
            ]);
        }
    }
    
    /**
     * Process content rewriting step
     */
    private function processRewriting($id, $video, $processing, $settings)
    {
        // Check if content analysis is complete
        if ($processing['processing_status'] !== 'completed') {
            $this->json([
                'status' => 'info',
                'message' => 'Content analysis is still in progress.',
                'processing_stage' => 'content_analysis',
                'processing_status' => $processing['processing_status'],
                'completed' => false
            ]);
            return;
        }
        
        // Update processing record
        $this->processingModel->update($processing['id'], [
            'processing_stage' => 'rewriting',
            'processing_status' => 'processing',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        try {
            // Check if rewritten content already exists
            $existingRewrite = $this->rewrittenContentModel->getByVideoId($id);
            
            if ($existingRewrite) {
                // Rewritten content already exists
                $this->processingModel->update($processing['id'], [
                    'processing_status' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                // Return success
                $this->json([
                    'status' => 'success',
                    'message' => 'Content rewriting was already completed.',
                    'processing_stage' => 'rewriting',
                    'processing_status' => 'completed',
                    'completed' => false
                ]);
                return;
            }
            
            // Get content analysis
            $contentAnalysis = $this->contentAnalysisModel->getByVideoId($id);
            
            if (!$contentAnalysis || $contentAnalysis['analysis_status'] !== 'completed') {
                throw new \Exception('Content analysis not found or incomplete.');
            }
            
            // Parse structured content
            $structuredContent = json_decode($contentAnalysis['structured_content'], true);
            
            // Rewrite content using AI
            $apiUsed = $settings['content_analysis_api']; // Use same API for consistency
            $rewriteOptions = [
                'level' => $settings['rewrite_level'],
                'change_names' => $settings['change_names'],
                'change_locations' => $settings['change_locations'],
                'change_examples' => $settings['change_examples'],
                'add_details' => $settings['add_details'],
                'tone' => $settings['content_tone'],
                'sections' => $settings['content_sections']
            ];
            
            $rewriteResult = $this->aiContent->rewriteContent($structuredContent, $apiUsed, $rewriteOptions);
            
            if (!$rewriteResult || !isset($rewriteResult['rewritten'])) {
                throw new \Exception('Content rewriting failed.');
            }
            
            // Create rewritten content record
            $rewriteData = [
                'video_id' => $id,
                'rewrite_level' => $settings['rewrite_level'],
                'change_names' => $settings['change_names'] ? 1 : 0,
                'change_locations' => $settings['change_locations'] ? 1 : 0,
                'change_examples' => $settings['change_examples'] ? 1 : 0,
                'add_details' => $settings['add_details'] ? 1 : 0,
                'hook' => $rewriteResult['rewritten']['hook'] ?? null,
                'introduction' => $rewriteResult['rewritten']['introduction'] ?? null,
                'main_content' => $rewriteResult['rewritten']['main_content'] ?? null,
                'climax' => $rewriteResult['rewritten']['climax'] ?? null,
                'twist' => $rewriteResult['rewritten']['twist'] ?? null,
                'transition' => $rewriteResult['rewritten']['transition'] ?? null,
                'controversy' => $rewriteResult['rewritten']['controversy'] ?? null,
                'conclusion' => $rewriteResult['rewritten']['conclusion'] ?? null,
                'call_to_action' => $rewriteResult['rewritten']['call_to_action'] ?? null,
                'api_used' => $apiUsed,
                'tokens_used' => $rewriteResult['tokens_used'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->rewrittenContentModel->create($rewriteData);
            
            // Update processing record
            $this->processingModel->update($processing['id'], [
                'processing_status' => 'completed',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Return success
            $this->json([
                'status' => 'success',
                'message' => 'Content rewriting completed successfully.',
                'processing_stage' => 'rewriting',
                'processing_status' => 'completed',
                'completed' => false
            ]);
        } catch (\Exception $e) {
            // Update processing record with error
            $this->processingModel->update($processing['id'], [
                'processing_status' => 'error',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Update video status
            $this->videoModel->updateStatus($id, 'error', 'Error during content rewriting: ' . $e->getMessage());
            
            // Return error
            $this->json([
                'status' => 'error',
                'message' => 'Error during content rewriting: ' . $e->getMessage(),
                'processing_stage' => 'rewriting',
                'processing_status' => 'error',
                'completed' => true
            ]);
        }
    }
    
    /**
     * Process image generation step
     */
    private function processImageGeneration($id, $video, $processing, $settings)
    {
        // Check if rewriting is complete
        if ($processing['processing_status'] !== 'completed') {
            $this->json([
                'status' => 'info',
                'message' => 'Content rewriting is still in progress.',
                'processing_stage' => 'rewriting',
                'processing_status' => $processing['processing_status'],
                'completed' => false
            ]);
            return;
        }
        
        // Update processing record
        $this->processingModel->update($processing['id'], [
            'processing_stage' => 'generating_images',
            'processing_status' => 'processing',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        try {
            // Check if images should be generated
            if (!$settings['generate_images_for_all']) {
                // Skip image generation
                $this->processingModel->update($processing['id'], [
                    'processing_status' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                // Return success
                $this->json([
                    'status' => 'success',
                    'message' => 'Image generation skipped.',
                    'processing_stage' => 'generating_images',
                    'processing_status' => 'completed',
                    'completed' => false
                ]);
                return;
            }
            
            // Load ImageGenerationHelper
            $imageHelper = new \App\Helpers\ImageGenerationHelper();
            
            // Get rewritten content
            $rewrittenContent = $this->rewrittenContentModel->getByVideoId($id);
            
            if (!$rewrittenContent) {
                throw new \Exception('Rewritten content not found.');
            }
            
            // Generate images for each section
            $imageApi = $settings['image_api'];
            $imageStyle = $settings['image_style'];
            $promptTemplate = $settings['image_prompt_template'];
            
            $sections = [
                'hook' => $rewrittenContent['hook'],
                'introduction' => $rewrittenContent['introduction'],
                'main_content' => $rewrittenContent['main_content'],
                'climax' => $rewrittenContent['climax'],
                'twist' => $rewrittenContent['twist'],
                'transition' => $rewrittenContent['transition'],
                'controversy' => $rewrittenContent['controversy'],
                'conclusion' => $rewrittenContent['conclusion'],
                'call_to_action' => $rewrittenContent['call_to_action']
            ];
            
            // Generate images for each non-empty section
            $generatedImagesModel = $this->model('GeneratedImageModel');
            $generatedCount = 0;
            
            foreach ($sections as $sectionType => $content) {
                if (empty($content) || !$settings['content_sections'][$sectionType]) {
                    continue;
                }
                
                // Generate image prompt
                $prompt = $imageHelper->generatePromptFromText($content, $promptTemplate, $imageStyle);
                
                // Generate image
                $imagePath = $imageHelper->generateImage($prompt, $imageApi, $imageStyle);
                
                if ($imagePath) {
                    // Save image info to database
                    $imageData = [
                        'content_section_id' => $id,
                        'content_section_type' => $sectionType,
                        'image_prompt' => $prompt,
                        'image_path' => $imagePath,
                        'api_used' => $imageApi,
                        'style' => $imageStyle,
                        'generation_status' => 'completed',
                        'width' => config('image.default_width'),
                        'height' => config('image.default_height'),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $generatedImagesModel->create($imageData);
                    $generatedCount++;
                }
            }
            
            // Update processing record
            $this->processingModel->update($processing['id'], [
                'processing_status' => 'completed',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Return success
            $this->json([
                'status' => 'success',
                'message' => "Generated {$generatedCount} images successfully.",
                'processing_stage' => 'generating_images',
                'processing_status' => 'completed',
                'completed' => false
            ]);
        } catch (\Exception $e) {
            // Update processing record with error
            $this->processingModel->update($processing['id'], [
                'processing_status' => 'error',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Update video status
            $this->videoModel->updateStatus($id, 'error', 'Error during image generation: ' . $e->getMessage());
            
            // Return error
            $this->json([
                'status' => 'error',
                'message' => 'Error during image generation: ' . $e->getMessage(),
                'processing_stage' => 'generating_images',
                'processing_status' => 'error',
                'completed' => true
            ]);
        }
    }
    
    /**
     * Complete processing
     */
    private function completeProcessing($id, $video, $processing)
    {
        // Check if image generation is complete
        if ($processing['processing_status'] !== 'completed') {
            $this->json([
                'status' => 'info',
                'message' => 'Image generation is still in progress.',
                'processing_stage' => 'generating_images',
                'processing_status' => $processing['processing_status'],
                'completed' => false
            ]);
            return;
        }
        
        // Update processing record
        $this->processingModel->update($processing['id'], [
            'processing_stage' => 'completed',
            'processing_status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Update video status
        $this->videoModel->updateStatus($id, 'completed');
        
        // Increment channel processed count
        if ($video['channel_id']) {
            $this->model('YoutubeChannelModel')->incrementProcessedCount($video['channel_id']);
        }
        
        // Return success
        $this->json([
            'status' => 'success',
            'message' => 'Video processing completed successfully.',
            'processing_stage' => 'completed',
            'processing_status' => 'completed',
            'completed' => true
        ]);
    }
}
