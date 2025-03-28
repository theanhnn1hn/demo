<?php
/**
 * Process videos in the queue
 * 
 * This script is designed to be run as a cron job.
 * Example cron entry (run every 15 minutes):
 * */15 * * * * /usr/bin/php /path/to/youtube-processor/scripts/process_video_queue.php
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
$videoModel = new App\Models\VideoModel();
$processingModel = new App\Models\VideoProcessingModel();
$channelModel = new App\Models\YoutubeChannelModel();
$contentAnalysisModel = new App\Models\ContentAnalysisModel();
$rewrittenContentModel = new App\Models\RewrittenContentModel();
$generatedImageModel = new App\Models\GeneratedImageModel();

// Initialize helpers
$videoDownloader = new App\Helpers\VideoDownloaderHelper();
$speechToText = new App\Helpers\SpeechToTextHelper();
$aiContent = new App\Helpers\AiContentHelper();
$imageGenerator = new App\Helpers\ImageGenerationHelper();

// Log function
function logMessage($message) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

logMessage('Starting video processing job...');

// Get videos with processing status
$processingVideos = $videoModel->where("status = 'processing'");

if (empty($processingVideos)) {
    // If no videos are currently processing, get pending videos
    $pendingVideos = $videoModel->getPendingVideos(5); // Process 5 videos at a time
    
    if (empty($pendingVideos)) {
        logMessage('No videos to process at this time.');
        exit(0);
    }
    
    // Update status to processing
    foreach ($pendingVideos as $video) {
        $videoModel->updateStatus($video['id'], 'processing');
    }
    
    $processingVideos = $pendingVideos;
}

logMessage('Found ' . count($processingVideos) . ' videos to process.');

// Process each video
foreach ($processingVideos as $video) {
    $videoId = $video['id'];
    logMessage("Processing video: {$video['title']} (ID: {$videoId})");
    
    // Get or create processing record
    $processing = $processingModel->getByVideoId($videoId);
    
    if (!$processing) {
        // Create default processing record
        $processingId = $processingModel->create([
            'video_id' => $videoId,
            'processing_stage' => 'initiated',
            'processing_status' => 'pending',
            'processing_settings' => json_encode([
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
                    'conclusion' => true,
                    'call_to_action' => true
                ]
            ]),
            'started_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $processing = $processingModel->find($processingId);
    }
    
    $settings = json_decode($processing['processing_settings'], true);
    
    // Process based on current stage
    try {
        switch ($processing['processing_stage']) {
            case 'initiated':
                // Download video
                processDownloadVideo($videoId, $video, $processing, $processingModel, $videoDownloader);
                break;
                
            case 'downloading':
                if ($processing['processing_status'] === 'completed') {
                    // Convert speech to text
                    processSpeechToText($videoId, $video, $processing, $processingModel, $settings, $speechToText, $videoDownloader);
                }
                break;
                
            case 'speech_to_text':
                if ($processing['processing_status'] === 'completed') {
                    // Analyze content
                    processContentAnalysis($videoId, $video, $processing, $processingModel, $settings, $contentAnalysisModel, $aiContent);
                }
                break;
                
            case 'content_analysis':
                if ($processing['processing_status'] === 'completed') {
                    // Rewrite content
                    processRewriting($videoId, $video, $processing, $processingModel, $settings, $contentAnalysisModel, $rewrittenContentModel, $aiContent);
                }
                break;
                
            case 'rewriting':
                if ($processing['processing_status'] === 'completed') {
                    // Generate images
                    processImageGeneration($videoId, $video, $processing, $processingModel, $settings, $rewrittenContentModel, $generatedImageModel, $imageGenerator);
                }
                break;
                
            case 'generating_images':
                if ($processing['processing_status'] === 'completed') {
                    // Complete processing
                    completeProcessing($videoId, $video, $processing, $processingModel, $videoModel, $channelModel);
                }
                break;
                
            case 'completed':
                logMessage("Video {$videoId} has already been processed.");
                break;
                
            default:
                logMessage("Unknown processing stage: {$processing['processing_stage']}");
                break;
        }
    } catch (Exception $e) {
        // Update processing record with error
        $processingModel->update($processing['id'], [
            'processing_status' => 'error',
            'error_message' => $e->getMessage(),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Update video status
        $videoModel->updateStatus($videoId, 'error', $e->getMessage());
        
        logMessage("Error processing video {$videoId}: " . $e->getMessage());
    }
}

logMessage('Video processing job completed.');

/**
 * Process video download
 */
function processDownloadVideo($videoId, $video, $processing, $processingModel, $videoDownloader) {
    global $logMessage;
    
    logMessage("Downloading video {$videoId}...");
    
    // Update processing record
    $processingModel->update($processing['id'], [
        'processing_stage' => 'downloading',
        'processing_status' => 'processing',
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    // Check if video is already downloaded
    if ($videoDownloader->isVideoDownloaded($video['youtube_id'])) {
        $videoPath = $videoDownloader->getVideoPath($video['youtube_id']);
        
        // Update processing record
        $processingModel->update($processing['id'], [
            'local_video_path' => $videoPath,
            'processing_status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        logMessage("Video was already downloaded: {$videoPath}");
    } else {
        // Download video
        $downloadResult = $videoDownloader->downloadVideo($video['youtube_id']);
        
        if ($downloadResult) {
            // Update processing record
            $processingModel->update($processing['id'], [
                'local_video_path' => $downloadResult['video_file'],
                'processing_status' => 'completed',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            logMessage("Video downloaded successfully: {$downloadResult['video_file']}");
        } else {
            throw new Exception('Failed to download video.');
        }
    }
}

/**
 * Process speech to text conversion
 */
function processSpeechToText($videoId, $video, $processing, $processingModel, $settings, $speechToText, $videoDownloader) {
    global $logMessage;
    
    logMessage("Converting speech to text for video {$videoId}...");
    
    // Update processing record
    $processingModel->update($processing['id'], [
        'processing_stage' => 'speech_to_text',
        'processing_status' => 'processing',
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    // Check if subtitle files already exist
    $transcript = $speechToText->getTranscript($video['youtube_id']);
    
    if ($transcript) {
        // Subtitle files already exist
        $subtitlePath = config('storage.subtitles') . '/' . $video['youtube_id'] . '.txt';
        
        $processingModel->update($processing['id'], [
            'subtitle_path' => $subtitlePath,
            'processing_status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        logMessage("Subtitles already exist: {$subtitlePath}");
        return;
    }
    
    // Extract audio from video
    $videoPath = $processing['local_video_path'];
    $audioPath = $videoDownloader->extractAudio($videoPath);
    
    if (!$audioPath) {
        throw new Exception('Failed to extract audio from video.');
    }
    
    // Update processing record with audio path
    $processingModel->update($processing['id'], [
        'audio_path' => $audioPath,
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    logMessage("Audio extracted: {$audioPath}");
    
    // Convert speech to text based on selected API
    $language = $settings['processing_language'];
    $sttResult = null;
    
    switch ($settings['speech_to_text_api']) {
        case 'assembly_ai':
            $sttResult = $speechToText->assemblyAiTranscribe($audioPath, $video['youtube_id'], $language);
            break;
        
        case 'rev_ai':
            $sttResult = $speechToText->revAiTranscribe($audioPath, $video['youtube_id'], $language);
            break;
        
        case 'whisper':
        default:
            $sttResult = $speechToText->whisperTranscribe($audioPath, $video['youtube_id'], $language);
            break;
    }
    
    if (!$sttResult) {
        throw new Exception('Speech to text conversion failed.');
    }
    
    // Update processing record
    $processingModel->update($processing['id'], [
        'subtitle_path' => $sttResult['subtitle_path']['text'],
        'processing_status' => 'completed',
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    logMessage("Speech to text conversion completed successfully.");
}

/**
 * Process content analysis
 */
function processContentAnalysis($videoId, $video, $processing, $processingModel, $settings, $contentAnalysisModel, $aiContent) {
    global $logMessage;
    
    logMessage("Analyzing content for video {$videoId}...");
    
    // Update processing record
    $processingModel->update($processing['id'], [
        'processing_stage' => 'content_analysis',
        'processing_status' => 'processing',
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    // Check if content analysis already exists
    $existingAnalysis = $contentAnalysisModel->getByVideoId($videoId);
    
    if ($existingAnalysis && $existingAnalysis['analysis_status'] === 'completed') {
        // Content analysis already exists
        $processingModel->update($processing['id'], [
            'processing_status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        logMessage("Content analysis already exists.");
        return;
    }
    
    // Get transcript
    $transcriptPath = $processing['subtitle_path'];
    $transcript = file_get_contents($transcriptPath);
    
    if (!$transcript) {
        throw new Exception('Failed to read transcript file.');
    }
    
    // Analyze content using AI
    $apiUsed = $settings['content_analysis_api'];
    $analysisResult = $aiContent->analyzeContent($transcript, $apiUsed);
    
    if (!$analysisResult) {
        throw new Exception('Content analysis failed.');
    }
    
    // Create or update content analysis record
    $analysisData = [
        'video_id' => $videoId,
        'original_content' => $transcript,
        'structured_content' => json_encode($analysisResult['analysis']),
        'analysis_status' => 'completed',
        'api_used' => $apiUsed,
        'tokens_used' => $analysisResult['tokens_used'],
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    if ($existingAnalysis) {
        $contentAnalysisModel->update($existingAnalysis['id'], $analysisData);
    } else {
        $analysisData['created_at'] = date('Y-m-d H:i:s');
        $contentAnalysisModel->create($analysisData);
    }
    
    // Update processing record
    $processingModel->update($processing['id'], [
        'processing_status' => 'completed',
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    logMessage("Content analysis completed successfully.");
}

/**
 * Process content rewriting
 */
function processRewriting($videoId, $video, $processing, $processingModel, $settings, $contentAnalysisModel, $rewrittenContentModel, $aiContent) {
    global $logMessage;
    
    logMessage("Rewriting content for video {$videoId}...");
    
    // Update processing record
    $processingModel->update($processing['id'], [
        'processing_stage' => 'rewriting',
        'processing_status' => 'processing',
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    // Check if rewritten content already exists
    $existingRewrite = $rewrittenContentModel->getByVideoId($videoId);
    
    if ($existingRewrite) {
        // Rewritten content already exists
        $processingModel->update($processing['id'], [
            'processing_status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        logMessage("Rewritten content already exists.");
        return;
    }
    
    // Get content analysis
    $contentAnalysis = $contentAnalysisModel->getByVideoId($videoId);
    
    if (!$contentAnalysis || $contentAnalysis['analysis_status'] !== 'completed') {
        throw new Exception('Content analysis not found or incomplete.');
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
    
    $rewriteResult = $aiContent->rewriteContent($structuredContent, $apiUsed, $rewriteOptions);
    
    if (!$rewriteResult || !isset($rewriteResult['rewritten'])) {
        throw new Exception('Content rewriting failed.');
    }
    
    // Create rewritten content record
    $rewriteData = [
        'video_id' => $videoId,
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
    
    $rewrittenContentModel->create($rewriteData);
    
    // Update processing record
    $processingModel->update($processing['id'], [
        'processing_status' => 'completed',
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    logMessage("Content rewriting completed successfully.");
}

/**
 * Process image generation
 */
function processImageGeneration($videoId, $video, $processing, $processingModel, $settings, $rewrittenContentModel, $generatedImageModel, $imageGenerator) {
    global $logMessage;
    
    logMessage("Generating images for video {$videoId}...");
    
    // Update processing record
    $processingModel->update($processing['id'], [
        'processing_stage' => 'generating_images',
        'processing_status' => 'processing',
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    // Check if images should be generated
    if (!$settings['generate_images_for_all']) {
        // Skip image generation
        $processingModel->update($processing['id'], [
            'processing_status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        logMessage("Image generation skipped.");
        return;
    }
    
    // Get rewritten content
    $rewrittenContent = $rewrittenContentModel->getByVideoId($videoId);
    
    if (!$rewrittenContent) {
        throw new Exception('Rewritten content not found.');
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
    $generatedCount = 0;
    
    foreach ($sections as $sectionType => $content) {
        if (empty($content) || !$settings['content_sections'][$sectionType]) {
            continue;
        }
        
        // Check if image already exists for this section
        $existingImage = $generatedImageModel->where("content_section_id = ? AND content_section_type = ?", [$videoId, $sectionType]);
        
        if (!empty($existingImage)) {
            logMessage("Image for {$sectionType} already exists.");
            $generatedCount++;
            continue;
        }
        
        // Generate image prompt
        $prompt = $imageGenerator->generatePromptFromText($content, $promptTemplate, $imageStyle);
        
        // Generate image
        $imagePath = $imageGenerator->generateImage($prompt, $imageApi, $imageStyle);
        
        if ($imagePath) {
            // Save image info to database
            $imageData = [
                'content_section_id' => $videoId,
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
            
            $generatedImageModel->create($imageData);
            $generatedCount++;
            
            logMessage("Generated image for {$sectionType}: {$imagePath}");
        } else {
            logMessage("Failed to generate image for {$sectionType}.");
        }
    }
    
    // Update processing record
    $processingModel->update($processing['id'], [
        'processing_status' => 'completed',
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    logMessage("Generated {$generatedCount} images successfully.");
}

/**
 * Complete processing
 */
function completeProcessing($videoId, $video, $processing, $processingModel, $videoModel, $channelModel) {
    global $logMessage;
    
    logMessage("Completing processing for video {$videoId}...");
    
    // Update processing record
    $processingModel->update($processing['id'], [
        'processing_stage' => 'completed',
        'processing_status' => 'completed',
        'completed_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    // Update video status
    $videoModel->updateStatus($videoId, 'completed');
    
    // Increment channel processed count
    if ($video['channel_id']) {
        $channelModel->incrementProcessedCount($video['channel_id']);
    }
    
    logMessage("Video processing completed successfully.");
}
