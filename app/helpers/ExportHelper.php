<?php
namespace App\Helpers;

class ExportHelper
{
    private $storagePath;
    
    public function __construct()
    {
        // Set storage path
        $this->storagePath = config('storage.exports');
        
        // Create storage directory if not exists
        if (!file_exists($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }
    
    /**
     * Export project
     *
     * @param array $video Video data
     * @param array $rewrittenContent Rewritten content data
     * @param array $generatedImages Generated images data
     * @param string $format Export format (markdown, html, pdf, json, text)
     * @param bool $includeSubtitles Whether to include original subtitles
     * @param bool $includePrompts Whether to include image generation prompts
     * @return array|false Export result or false on failure
     */
    public function exportProject($video, $rewrittenContent, $generatedImages, $format = 'markdown', $includeSubtitles = false, $includePrompts = false)
    {
        try {
            $videoId = $video['id'];
            $timestamp = time();
            $filename = "export_{$videoId}_{$timestamp}";
            $extension = $this->getExtensionForFormat($format);
            $filePath = "{$this->storagePath}/{$filename}.{$extension}";
            
            // Group images by section
            $imagesBySections = [];
            foreach ($generatedImages as $image) {
                $sectionType = $image['content_section_type'];
                $imagesBySections[$sectionType] = $image;
            }
            
            // Generate content based on format
            switch ($format) {
                case 'markdown':
                    $content = $this->generateMarkdown($video, $rewrittenContent, $imagesBySections, $includeSubtitles, $includePrompts);
                    break;
                
                case 'html':
                    $content = $this->generateHtml($video, $rewrittenContent, $imagesBySections, $includeSubtitles, $includePrompts);
                    break;
                
                case 'pdf':
                    $htmlContent = $this->generateHtml($video, $rewrittenContent, $imagesBySections, $includeSubtitles, $includePrompts);
                    $content = $this->generatePdf($htmlContent, $filePath);
                    break;
                
                case 'json':
                    $content = $this->generateJson($video, $rewrittenContent, $imagesBySections, $includeSubtitles, $includePrompts);
                    break;
                
                case 'text':
                default:
                    $content = $this->generateText($video, $rewrittenContent, $imagesBySections, $includeSubtitles);
                    break;
            }
            
            // In case of PDF, the content is already saved to file
            if ($format !== 'pdf') {
                file_put_contents($filePath, $content);
            }
            
            return [
                'filename' => "{$filename}.{$extension}",
                'file_path' => $filePath,
                'content_type' => $this->getContentTypeForFormat($format)
            ];
        } catch (\Exception $e) {
            error_log('Export Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate Markdown content
     */
    private function generateMarkdown($video, $rewrittenContent, $imagesBySections, $includeSubtitles, $includePrompts)
    {
        $markdown = "# {$video['title']}\n\n";
        
        // Video info
        $markdown .= "## Video Information\n\n";
        $markdown .= "- **Original YouTube Video**: [Watch on YouTube](https://www.youtube.com/watch?v={$video['youtube_id']})\n";
        $markdown .= "- **Published**: " . date('F j, Y', strtotime($video['publish_date'])) . "\n";
        $markdown .= "- **Duration**: " . formatDuration($video['duration']) . "\n\n";
        
        // Content sections
        if (!empty($rewrittenContent['hook'])) {
            $markdown .= "## Hook\n\n{$rewrittenContent['hook']}\n\n";
            
            // Add image if available
            if (isset($imagesBySections['hook'])) {
                $imagePath = $imagesBySections['hook']['image_path'];
                $markdown .= "![Hook Image]({$imagePath})\n\n";
                
                // Add prompt if requested
                if ($includePrompts && !empty($imagesBySections['hook']['image_prompt'])) {
                    $prompt = $imagesBySections['hook']['image_prompt'];
                    $markdown .= "> Image prompt: {$prompt}\n\n";
                }
            }
        }
        
        if (!empty($rewrittenContent['introduction'])) {
            $markdown .= "## Introduction\n\n{$rewrittenContent['introduction']}\n\n";
            
            // Add image if available
            if (isset($imagesBySections['introduction'])) {
                $imagePath = $imagesBySections['introduction']['image_path'];
                $markdown .= "![Introduction Image]({$imagePath})\n\n";
                
                // Add prompt if requested
                if ($includePrompts && !empty($imagesBySections['introduction']['image_prompt'])) {
                    $prompt = $imagesBySections['introduction']['image_prompt'];
                    $markdown .= "> Image prompt: {$prompt}\n\n";
                }
            }
        }
        
        if (!empty($rewrittenContent['main_content'])) {
            $markdown .= "## Main Content\n\n{$rewrittenContent['main_content']}\n\n";
            
            // Add image if available
            if (isset($imagesBySections['main_content'])) {
                $imagePath = $imagesBySections['main_content']['image_path'];
                $markdown .= "![Main Content Image]({$imagePath})\n\n";
                
                // Add prompt if requested
                if ($includePrompts && !empty($imagesBySections['main_content']['image_prompt'])) {
                    $prompt = $imagesBySections['main_content']['image_prompt'];
                    $markdown .= "> Image prompt: {$prompt}\n\n";
                }
            }
        }
        
        if (!empty($rewrittenContent['climax'])) {
            $markdown .= "## Climax\n\n{$rewrittenContent['climax']}\n\n";
            
            // Add image if available
            if (isset($imagesBySections['climax'])) {
                $imagePath = $imagesBySections['climax']['image_path'];
                $markdown .= "![Climax Image]({$imagePath})\n\n";
                
                // Add prompt if requested
                if ($includePrompts && !empty($imagesBySections['climax']['image_prompt'])) {
                    $prompt = $imagesBySections['climax']['image_prompt'];
                    $markdown .= "> Image prompt: {$prompt}\n\n";
                }
            }
        }
        
        if (!empty($rewrittenContent['twist'])) {
            $markdown .= "## Twist\n\n{$rewrittenContent['twist']}\n\n";
            
            // Add image if available
            if (isset($imagesBySections['twist'])) {
                $imagePath = $imagesBySections['twist']['image_path'];
                $markdown .= "![Twist Image]({$imagePath})\n\n";
                
                // Add prompt if requested
                if ($includePrompts && !empty($imagesBySections['twist']['image_prompt'])) {
                    $prompt = $imagesBySections['twist']['image_prompt'];
                    $markdown .= "> Image prompt: {$prompt}\n\n";
                }
            }
        }
        
        if (!empty($rewrittenContent['transition'])) {
            $markdown .= "## Transition\n\n{$rewrittenContent['transition']}\n\n";
            
            // Add image if available
            if (isset($imagesBySections['transition'])) {
                $imagePath = $imagesBySections['transition']['image_path'];
                $markdown .= "![Transition Image]({$imagePath})\n\n";
                
                // Add prompt if requested
                if ($includePrompts && !empty($imagesBySections['transition']['image_prompt'])) {
                    $prompt = $imagesBySections['transition']['image_prompt'];
                    $markdown .= "> Image prompt: {$prompt}\n\n";
                }
            }
        }
        
        if (!empty($rewrittenContent['controversy'])) {
            $markdown .= "## Controversy\n\n{$rewrittenContent['controversy']}\n\n";
            
            // Add image if available
            if (isset($imagesBySections['controversy'])) {
                $imagePath = $imagesBySections['controversy']['image_path'];
                $markdown .= "![Controversy Image]({$imagePath})\n\n";
                
                // Add prompt if requested
                if ($includePrompts && !empty($imagesBySections['controversy']['image_prompt'])) {
                    $prompt = $imagesBySections['controversy']['image_prompt'];
                    $markdown .= "> Image prompt: {$prompt}\n\n";
                }
            }
        }
        
        if (!empty($rewrittenContent['conclusion'])) {
            $markdown .= "## Conclusion\n\n{$rewrittenContent['conclusion']}\n\n";
            
            // Add image if available
            if (isset($imagesBySections['conclusion'])) {
                $imagePath = $imagesBySections['conclusion']['image_path'];
                $markdown .= "![Conclusion Image]({$imagePath})\n\n";
                
                // Add prompt if requested
                if ($includePrompts && !empty($imagesBySections['conclusion']['image_prompt'])) {
                    $prompt = $imagesBySections['conclusion']['image_prompt'];
                    $markdown .= "> Image prompt: {$prompt}\n\n";
                }
            }
        }
        
        if (!empty($rewrittenContent['call_to_action'])) {
            $markdown .= "## Call to Action\n\n{$rewrittenContent['call_to_action']}\n\n";
            
            // Add image if available
            if (isset($imagesBySections['call_to_action'])) {
                $imagePath = $imagesBySections['call_to_action']['image_path'];
                $markdown .= "![Call to Action Image]({$imagePath})\n\n";
                
                // Add prompt if requested
                if ($includePrompts && !empty($imagesBySections['call_to_action']['image_prompt'])) {
                    $prompt = $imagesBySections['call_to_action']['image_prompt'];
                    $markdown .= "> Image prompt: {$prompt}\n\n";
                }
            }
        }
        
        // Add original subtitles if requested
        if ($includeSubtitles) {
            $subtitlePath = config('storage.subtitles') . '/' . $video['youtube_id'] . '.txt';
            
            if (file_exists($subtitlePath)) {
                $subtitles = file_get_contents($subtitlePath);
                $markdown .= "## Original Transcript\n\n```\n{$subtitles}\n```\n\n";
            }
        }
        
        // Add footer
        $markdown .= "---\n";
        $markdown .= "Generated by YouTube Processor on " . date('F j, Y, g:i a') . "\n";
        
        return $markdown;
    }
    
    /**
     * Generate HTML content
     */
    private function generateHtml($video, $rewrittenContent, $imagesBySections, $includeSubtitles, $includePrompts)
    {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($video['title']) . '</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 20px 0;
            border-radius: 5px;
        }
        .video-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .prompt {
            background-color: #f0f4f8;
            padding: 10px 15px;
            border-left: 3px solid #3498db;
            margin: 10px 0;
            font-style: italic;
            color: #555;
        }
        .section {
            margin-bottom: 30px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 0.9em;
            color: #777;
        }
        pre {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>' . htmlspecialchars($video['title']) . '</h1>
    
    <div class="video-info">
        <p><strong>Original YouTube Video:</strong> <a href="https://www.youtube.com/watch?v=' . $video['youtube_id'] . '" target="_blank">Watch on YouTube</a></p>
        <p><strong>Published:</strong> ' . date('F j, Y', strtotime($video['publish_date'])) . '</p>
        <p><strong>Duration:</strong> ' . formatDuration($video['duration']) . '</p>
    </div>';
        
        // Content sections
        if (!empty($rewrittenContent['hook'])) {
            $html .= '<div class="section">
        <h2>Hook</h2>
        <div>' . nl2br(htmlspecialchars($rewrittenContent['hook'])) . '</div>';
            
            // Add image if available
            if (isset($imagesBySections['hook'])) {
                $imagePath = $imagesBySections['hook']['image_path'];
                $html .= '<img src="' . $imagePath . '" alt="Hook Illustration">';
                
                // Add prompt if requested
                if ($includePrompts && !empty($imagesBySections['hook']['image_prompt'])) {
                    $prompt = htmlspecialchars($imagesBySections['hook']['image_prompt']);
                    $html .= '<div class="prompt">Image prompt: ' . $prompt . '</div>';
                }
            }
            
            $html .= '</div>';
        }
        
        if (!empty($rewrittenContent['introduction'])) {
            $html .= '<div class="section">
        <h2>Introduction</h2>
        <div>' . nl2br(htmlspecialchars($rewrittenContent['introduction'])) . '</div>';
            
            // Add image if available
            if (isset($imagesBySections['introduction'])) {
                $imagePath = $imagesBySections['introduction']['image_path'];
                $html .= '<img src="' . $imagePath . '" alt="Introduction Illustration">';
                
                // Add prompt if requested
                if ($includePrompts && !empty($imagesBySections['introduction']['image_prompt'])) {
                    $prompt = htmlspecialchars($imagesBySections['introduction']['image_prompt']);
                    $html .= '<div class="prompt">Image prompt: ' . $prompt . '</div>';
                }
            }
            
            $html .= '</div>';
        }
        
        if (!empty($rewrittenContent['main_content'])) {
            $html .= '<div class="section">
        <h2>Main Content</h2>
        <div>' . nl2br(htmlspecialchars($rewrittenContent['main_content'])) . '</div>';
            
            // Add image if available
            if (isset($imagesBySections['main_content'])) {
                $imagePath = $imagesBySections['main_content']['image_path'];
                $html .= '<img src="' . $imagePath . '" alt="Main Content Illustration">';
                
                // Add prompt if requested
                if ($includePrompts && !empty($imagesBySections['main_content']['image_prompt'])) {
                    $prompt = htmlspecialchars($imagesBySections['main_content']['image_prompt']);
                    $html .= '<div class="prompt">Image prompt: ' . $prompt . '</div>';
                }
            }
            
            $html .= '</div>';
        }
        
        if (!empty($rewrittenContent['climax'])) {
            $html .= '<div class="section">
        <h2>Climax</h2>
        <div>' . nl2br(htmlspecialchars($rewrittenContent['climax'])) . '</div>';
            
            // Add image if available
            if (isset($imagesBySections['climax'])) {
                $imagePath = $imagesBySections['climax']['image_path'];
                $html .= '<img src="' . $imagePath . '" alt="Climax Illustration">';
                
                // Add prompt if requested
                if ($includePrompts && !empty($imagesBySections['climax']['image_prompt'])) {
                    $prompt = htmlspecialchars($imagesBySections['climax']['image_prompt']);
                    $html .= '<div class="prompt">Image prompt: ' . $prompt . '</div>';
                }
            }
            
            $html .= '</div>';
        }
        
        if (!empty($rewrittenContent['twist'])) {
            $html .= '<div class="section">
        <h2>Twist</h2>
        <div>' . nl2br(htmlspecialchars($rewrittenContent['twist'])) . '</div>';
            
            // Add image if available
            if (isset($imagesBySections['twist'])) {
                $imagePath = $imagesBySections['twist']['image_path'];
                $html .= '<img src="' . $imagePath . '" alt="Twist Illustration">';
                
                // Add prompt if requested
                if ($includePrompts && !empty($imagesBySections['twist']['image_prompt'])) {
                    $prompt = htmlspecialchars($imagesBySections['twist']['image_prompt']);
                    $html .= '<div class="prompt">Image prompt: ' . $prompt . '</div>';
                }
            }
            
            $html .= '</div>';
        }
        
        if (!empty($rewrittenContent['transition'])) {
            $html .= '<div class="section">
        <h2>Transition</h2>
        <div>' . nl2br(htmlspecialchars($rewrittenContent['transition'])) . '</div>';
            
            // Add image if available
            if (isset($imagesBySections['transition'])) {
                $imagePath = $imagesBySections['transition']['image_path'];
                $html .= '<img src="' . $imagePath . '" alt="Transition Illustration">';
                
                // Add prompt if requested
                if ($includePrompts && !empty($imagesBySections['transition']['image_prompt'])) {
                    $prompt = htmlspecialchars($imagesBySections['transition']['image_prompt']);
                    $html .= '<div class="prompt">Image prompt: ' . $prompt . '</div>';
                }
            }
            
            $html .= '</div>';
        }
        
        if (!empty($rewrittenContent['controversy'])) {
            $html .= '<div class="section">
        <h2>Controversy</h2>
        <div>' . nl2br(htmlspecialchars($rewrittenContent['controversy'])) . '</div>';
            
            // Add image if available
            if (isset($imagesBySections['controversy'])) {
                $imagePath = $imagesBySections['controversy']['image_path'];
                $html .= '<img src="' . $imagePath . '" alt="Controversy Illustration">';
                
                // Add prompt if requested
                if ($includePrompts && !empty($imagesBySections['controversy']['image_prompt'])) {
                    $prompt = htmlspecialchars($imagesBySections['controversy']['image_prompt']);
                    $html .= '<div class="prompt">Image prompt: ' . $prompt . '</div>';
                }
            }
            
            $html .= '</div>';
        }
        
        if (!empty($rewrittenContent['conclusion'])) {
            $html .= '<div class="section">
        <h2>Conclusion</h2>
        <div>' . nl2br(htmlspecialchars($rewrittenContent['conclusion'])) . '</div>';
            
            // Add image if available
            if (isset($imagesBySections['conclusion'])) {
                $imagePath = $imagesBySections['conclusion']['image_path'];
                $html .= '<img src="' . $imagePath . '" alt="Conclusion Illustration">';
                
                // Add prompt if requested
                if ($includePrompts && !empty($imagesBySections['conclusion']['image_prompt'])) {
                    $prompt = htmlspecialchars($imagesBySections['conclusion']['image_prompt']);
                    $html .= '<div class="prompt">Image prompt: ' . $prompt . '</div>';
                }
            }
            
            $html .= '</div>';
        }
        
        if (!empty($rewrittenContent['call_to_action'])) {
            $html .= '<div class="section">
        <h2>Call to Action</h2>
        <div>' . nl2br(htmlspecialchars($rewrittenContent['call_to_action'])) . '</div>';
            
            // Add image if available
            if (isset($imagesBySections['call_to_action'])) {
                $imagePath = $imagesBySections['call_to_action']['image_path'];
                $html .= '<img src="' . $imagePath . '" alt="Call to Action Illustration">';
                
                // Add prompt if requested
                if ($includePrompts && !empty($imagesBySections['call_to_action']['image_prompt'])) {
                    $prompt = htmlspecialchars($imagesBySections['call_to_action']['image_prompt']);
                    $html .= '<div class="prompt">Image prompt: ' . $prompt . '</div>';
                }
            }
            
            $html .= '</div>';
        }
        
        // Add original subtitles if requested
        if ($includeSubtitles) {
            $subtitlePath = config('storage.subtitles') . '/' . $video['youtube_id'] . '.txt';
            
            if (file_exists($subtitlePath)) {
                $subtitles = htmlspecialchars(file_get_contents($subtitlePath));
                $html .= '<div class="section">
        <h2>Original Transcript</h2>
        <pre>' . $subtitles . '</pre>
    </div>';
            }
        }
        
        // Add footer
        $html .= '<div class="footer">
        <p>Generated by YouTube Processor on ' . date('F j, Y, g:i a') . '</p>
    </div>

</body>
</html>';
        
        return $html;
    }
    
    /**
     * Generate PDF content
     * Note: This requires a PDF library like FPDF, TCPDF, or Dompdf
     */
    private function generatePdf($htmlContent, $outputPath)
    {
        // In a real application, you would use a PDF library like FPDF, TCPDF, or Dompdf
        // For this example, we'll just create a simple HTML file with PDF content type
        // You can replace this with actual PDF generation
        file_put_contents($outputPath, $htmlContent);
        
        return true;
    }
    
    /**
     * Generate JSON content
     */
    private function generateJson($video, $rewrittenContent, $imagesBySections, $includeSubtitles, $includePrompts)
    {
        $data = [
            'video' => [
                'id' => $video['id'],
                'youtube_id' => $video['youtube_id'],
                'title' => $video['title'],
                'description' => $video['description'],
                'publish_date' => $video['publish_date'],
                'duration' => $video['duration'],
                'youtube_url' => 'https://www.youtube.com/watch?v=' . $video['youtube_id']
            ],
            'content' => []
        ];
        
        // Add content sections
        $sections = [
            'hook',
            'introduction',
            'main_content',
            'climax',
            'twist',
            'transition',
            'controversy',
            'conclusion',
            'call_to_action'
        ];
        
        foreach ($sections as $section) {
            if (!empty($rewrittenContent[$section])) {
                $sectionData = [
                    'type' => $section,
                    'content' => $rewrittenContent[$section]
                ];
                
                // Add image if available
                if (isset($imagesBySections[$section])) {
                    $imagePath = $imagesBySections[$section]['image_path'];
                    $sectionData['image'] = $imagePath;
                    
                    // Add prompt if requested
                    if ($includePrompts && !empty($imagesBySections[$section]['image_prompt'])) {
                        $sectionData['image_prompt'] = $imagesBySections[$section]['image_prompt'];
                    }
                }
                
                $data['content'][] = $sectionData;
            }
        }
        
        // Add original subtitles if requested
        if ($includeSubtitles) {
            $subtitlePath = config('storage.subtitles') . '/' . $video['youtube_id'] . '.txt';
            
            if (file_exists($subtitlePath)) {
                $subtitles = file_get_contents($subtitlePath);
                $data['original_transcript'] = $subtitles;
            }
        }
        
        // Add export metadata
        $data['export_info'] = [
            'generated_at' => date('Y-m-d H:i:s'),
            'generator' => 'YouTube Processor'
        ];
        
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Generate plain text content
     */
    private function generateText($video, $rewrittenContent, $imagesBySections, $includeSubtitles)
    {
        $text = strtoupper($video['title']) . "\n";
        $text .= str_repeat("=", strlen($video['title'])) . "\n\n";
        
        // Video info
        $text .= "VIDEO INFORMATION\n";
        $text .= "----------------\n";
        $text .= "Original YouTube Video: https://www.youtube.com/watch?v={$video['youtube_id']}\n";
        $text .= "Published: " . date('F j, Y', strtotime($video['publish_date'])) . "\n";
        $text .= "Duration: " . formatDuration($video['duration']) . "\n\n";
        
        // Content sections
        if (!empty($rewrittenContent['hook'])) {
            $text .= "HOOK\n";
            $text .= "----\n";
            $text .= "{$rewrittenContent['hook']}\n\n";
        }
        
        if (!empty($rewrittenContent['introduction'])) {
            $text .= "INTRODUCTION\n";
            $text .= "------------\n";
            $text .= "{$rewrittenContent['introduction']}\n\n";
        }
        
        if (!empty($rewrittenContent['main_content'])) {
            $text .= "MAIN CONTENT\n";
            $text .= "------------\n";
            $text .= "{$rewrittenContent['main_content']}\n\n";
        }
        
        if (!empty($rewrittenContent['climax'])) {
            $text .= "CLIMAX\n";
            $text .= "------\n";
            $text .= "{$rewrittenContent['climax']}\n\n";
        }
        
        if (!empty($rewrittenContent['twist'])) {
            $text .= "TWIST\n";
            $text .= "-----\n";
            $text .= "{$rewrittenContent['twist']}\n\n";
        }
        
        if (!empty($rewrittenContent['transition'])) {
            $text .= "TRANSITION\n";
            $text .= "----------\n";
            $text .= "{$rewrittenContent['transition']}\n\n";
        }
        
        if (!empty($rewrittenContent['controversy'])) {
            $text .= "CONTROVERSY\n";
            $text .= "-----------\n";
            $text .= "{$rewrittenContent['controversy']}\n\n";
        }
        
        if (!empty($rewrittenContent['conclusion'])) {
            $text .= "CONCLUSION\n";
            $text .= "----------\n";
            $text .= "{$rewrittenContent['conclusion']}\n\n";
        }
        
        if (!empty($rewrittenContent['call_to_action'])) {
            $text .= "CALL TO ACTION\n";
            $text .= "-------------\n";
            $text .= "{$rewrittenContent['call_to_action']}\n\n";
        }
        
        // Add original subtitles if requested
        if ($includeSubtitles) {
            $subtitlePath = config('storage.subtitles') . '/' . $video['youtube_id'] . '.txt';
            
            if (file_exists($subtitlePath)) {
                $subtitles = file_get_contents($subtitlePath);
                $text .= "ORIGINAL TRANSCRIPT\n";
                $text .= "------------------\n";
                $text .= "{$subtitles}\n\n";
            }
        }
        
        // Add footer
        $text .= "---------------------------------------\n";
        $text .= "Generated by YouTube Processor on " . date('F j, Y, g:i a') . "\n";
        
        return $text;
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
