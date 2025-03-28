<?php
namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ImageGenerationHelper
{
    private $apiKeys;
    private $client;
    private $storagePath;
    
    public function __construct()
    {
        // Load API keys
        $apiConfig = require 'config/api_keys.php';
        $this->apiKeys = $apiConfig['image_generation'];
        
        // Initialize HTTP client
        $this->client = new Client([
            'timeout' => 60,
        ]);
        
        // Set storage path
        $this->storagePath = config('storage.images');
        
        // Create storage directory if not exists
        if (!file_exists($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }
    
    /**
     * Generate prompt from text
     *
     * @param string $text Text to generate prompt from
     * @param string $template Prompt template
     * @param string $style Image style
     * @return string Generated prompt
     */
    public function generatePromptFromText($text, $template, $style)
    {
        // Truncate text to a reasonable length
        $text = substr($text, 0, 500);
        
        // Extract main theme or subject from text
        $theme = $this->extractTheme($text);
        
        // Apply theme to template
        $prompt = str_replace('[theme]', $theme, $template);
        
        // Add style-specific keywords
        switch ($style) {
            case 'cartoon':
                $prompt .= ', cartoon style, vibrant colors, stylized, animation, illustrated';
                break;
            
            case 'render3d':
                $prompt .= ', 3D rendering, realistic textures, depth, volumetric lighting, raytracing';
                break;
            
            case 'artistic':
                $prompt .= ', artistic painting, brush strokes, canvas texture, expressive, color palette';
                break;
            
            case 'cinematic':
                $prompt .= ', cinematic scene, movie still, film photography, dramatic lighting, depth of field';
                break;
            
            case 'anime':
                $prompt .= ', anime style, manga illustration, cel shading, japanese animation style';
                break;
            
            case 'realistic':
            default:
                $prompt .= ', photorealistic, detailed, high resolution photography';
                break;
        }
        
        return $prompt;
    }
    
    /**
     * Extract main theme or subject from text
     *
     * @param string $text Text to extract theme from
     * @return string Extracted theme
     */
    private function extractTheme($text)
    {
        // Simplified theme extraction
        // In a real-world scenario, you might want to use NLP or AI to extract themes
        
        // Remove common words and punctuation
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        $text = strtolower($text);
        
        $commonWords = ['a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'with', 'by', 'about', 'like', 'through', 'over', 'before', 'after', 'between', 'under', 'above', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'can', 'could', 'will', 'would', 'shall', 'should', 'may', 'might', 'must', 'of', 'that', 'this', 'these', 'those', 'it', 'its', 'we', 'us', 'our', 'they', 'them', 'their', 'he', 'him', 'his', 'she', 'her'];
        
        $words = explode(' ', $text);
        $words = array_filter($words, function($word) use ($commonWords) {
            return !in_array($word, $commonWords) && strlen($word) > 3;
        });
        
        // Count word frequency
        $wordCounts = array_count_values($words);
        
        // Sort by frequency
        arsort($wordCounts);
        
        // Get top 5 words
        $topWords = array_slice(array_keys($wordCounts), 0, 5);
        
        return implode(' ', $topWords);
    }
    
    /**
     * Generate image
     *
     * @param string $prompt Image generation prompt
     * @param string $api API to use (dall_e, midjourney, stable_diffusion)
     * @param string $style Image style
     * @param int $width Image width
     * @param int $height Image height
     * @return string|false Path to generated image or false on failure
     */
    public function generateImage($prompt, $api = 'dall_e', $style = 'realistic', $width = null, $height = null)
    {
        try {
            // Set default dimensions if not provided
            $width = $width ?? config('image.default_width');
            $height = $height ?? config('image.default_height');
            
            switch ($api) {
                case 'dall_e':
                    return $this->generateWithDallE($prompt, $width, $height);
                
                case 'midjourney':
                    return $this->generateWithMidjourney($prompt, $width, $height);
                
                case 'stable_diffusion':
                    return $this->generateWithStableDiffusion($prompt, $style, $width, $height);
                
                default:
                    throw new \Exception('Invalid API specified.');
            }
        } catch (\Exception $e) {
            error_log('Image Generation Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate image with DALL-E 3
     */
    private function generateWithDallE($prompt, $width, $height)
    {
        $apiKey = $this->apiKeys['dall_e']['api_key'];
        
        if (empty($apiKey)) {
            throw new \Exception('DALL-E API key not configured');
        }
        
        // Standardize dimensions to supported values
        $size = $this->standardizeDallESize($width, $height);
        
        $response = $this->client->request('POST', 'https://api.openai.com/v1/images/generations', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey
            ],
            'json' => [
                'model' => 'dall-e-3',
                'prompt' => $prompt,
                'n' => 1,
                'size' => $size,
                'quality' => 'standard',
                'response_format' => 'url'
            ]
        ]);
        
        $responseData = json_decode($response->getBody(), true);
        $imageUrl = $responseData['data'][0]['url'];
        
        // Download image
        return $this->downloadImage($imageUrl, $prompt);
    }
    
    /**
     * Generate image with Midjourney API
     * Note: This is a conceptual implementation as Midjourney doesn't offer a public API
     */
    private function generateWithMidjourney($prompt, $width, $height)
    {
        $apiKey = $this->apiKeys['midjourney']['api_key'];
        
        if (empty($apiKey)) {
            throw new \Exception('Midjourney API key not configured');
        }
        
        // Midjourney doesn't have an official API, this is conceptual
        // In a real implementation, you might use a third-party service that wraps Midjourney
        $response = $this->client->request('POST', 'https://api.example-midjourney-proxy.com/generate', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey
            ],
            'json' => [
                'prompt' => $prompt,
                'width' => $width,
                'height' => $height,
                'num_images' => 1
            ]
        ]);
        
        $responseData = json_decode($response->getBody(), true);
        $imageUrl = $responseData['images'][0]['url'];
        
        // Download image
        return $this->downloadImage($imageUrl, $prompt);
    }
    
    /**
     * Generate image with Stable Diffusion
     */
    private function generateWithStableDiffusion($prompt, $style, $width, $height)
    {
        $apiKey = $this->apiKeys['stable_diffusion']['api_key'];
        
        if (empty($apiKey)) {
            throw new \Exception('Stable Diffusion API key not configured');
        }
        
        // Map style to model
        $model = $this->mapStyleToModel($style);
        
        $response = $this->client->request('POST', 'https://api.stability.ai/v1/generation/stable-diffusion-xl-1024-v1-0/text-to-image', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey
            ],
            'json' => [
                'text_prompts' => [
                    [
                        'text' => $prompt,
                        'weight' => 1
                    ]
                ],
                'cfg_scale' => 7,
                'height' => $height,
                'width' => $width,
                'samples' => 1,
                'steps' => 30
            ]
        ]);
        
        $responseData = json_decode($response->getBody(), true);
        $base64Image = $responseData['artifacts'][0]['base64'];
        
        // Save base64 image to file
        $filename = 'img_' . time() . '_' . substr(md5($prompt), 0, 8) . '.png';
        $filePath = $this->storagePath . '/' . $filename;
        
        file_put_contents($filePath, base64_decode($base64Image));
        
        return $filePath;
    }
    
    /**
     * Download image from URL
     */
    private function downloadImage($url, $prompt)
    {
        // Generate filename from prompt and timestamp
        $filename = 'img_' . time() . '_' . substr(md5($prompt), 0, 8) . '.png';
        $filePath = $this->storagePath . '/' . $filename;
        
        // Download image
        $imageContent = file_get_contents($url);
        file_put_contents($filePath, $imageContent);
        
        return $filePath;
    }
    
    /**
     * Standardize dimensions for DALL-E 3
     * DALL-E 3 only supports specific size combinations
     */
    private function standardizeDallESize($width, $height)
    {
        // DALL-E 3 supports these sizes:
        // 1024x1024, 1024x1792, 1792x1024
        
        $ratio = $width / $height;
        
        if ($ratio > 1.5) {
            // Landscape
            return '1792x1024';
        } elseif ($ratio < 0.67) {
            // Portrait
            return '1024x1792';
        } else {
            // Square-ish
            return '1024x1024';
        }
    }
    
    /**
     * Map style to Stable Diffusion model
     */
    private function mapStyleToModel($style)
    {
        switch ($style) {
            case 'cartoon':
                return 'stable-diffusion-512-v2-1';
            
            case 'anime':
                return 'stable-diffusion-anime';
            
            case 'realistic':
                return 'stable-diffusion-xl-1024-v1-0';
            
            default:
                return 'stable-diffusion-xl-1024-v1-0';
        }
    }
    
    /**
     * Regenerate image
     *
     * @param string $imagePath Original image path
     * @param string $prompt New or modified prompt
     * @param string $api API to use
     * @param string $style Image style
     * @return string|false Path to regenerated image or false on failure
     */
    public function regenerateImage($imagePath, $prompt, $api = 'dall_e', $style = 'realistic')
    {
        try {
            // Get image dimensions
            list($width, $height) = getimagesize($imagePath);
            
            // Generate new image
            return $this->generateImage($prompt, $api, $style, $width, $height);
        } catch (\Exception $e) {
            error_log('Image Regeneration Error: ' . $e->getMessage());
            return false;
        }
    }
}
