<?php
namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AiContentHelper
{
    private $apiKeys;
    private $client;
    
    public function __construct()
    {
        // Load API keys
        $apiConfig = require 'config/api_keys.php';
        $this->apiKeys = $apiConfig['ai_content'];
        
        // Initialize HTTP client
        $this->client = new Client([
            'timeout' => 180, // Longer timeout for AI processing
        ]);
    }
    
    /**
     * Analyze content using AI
     *
     * @param string $transcript Video transcript
     * @param string $api AI API to use (claude, gpt4, gpt35)
     * @return array|false Analysis result or false on failure
     */
    public function analyzeContent($transcript, $api = 'claude')
    {
        try {
            switch ($api) {
                case 'claude':
                    return $this->analyzeWithClaude($transcript);
                
                case 'gpt4':
                    return $this->analyzeWithGpt4($transcript);
                
                case 'gpt35':
                    return $this->analyzeWithGpt35($transcript);
                
                default:
                    throw new \Exception('Invalid API specified.');
            }
        } catch (\Exception $e) {
            error_log('AI Content Analysis Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rewrite content using AI
     *
     * @param array $structuredContent Structured content from analysis
     * @param string $api AI API to use (claude, gpt4, gpt35)
     * @param array $options Rewriting options
     * @return array|false Rewritten content or false on failure
     */
    public function rewriteContent($structuredContent, $api = 'claude', $options = [])
    {
        try {
            switch ($api) {
                case 'claude':
                    return $this->rewriteWithClaude($structuredContent, $options);
                
                case 'gpt4':
                    return $this->rewriteWithGpt4($structuredContent, $options);
                
                case 'gpt35':
                    return $this->rewriteWithGpt35($structuredContent, $options);
                
                default:
                    throw new \Exception('Invalid API specified.');
            }
        } catch (\Exception $e) {
            error_log('AI Content Rewriting Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Analyze content with Claude
     */
    private function analyzeWithClaude($transcript)
    {
        $apiKey = $this->apiKeys['claude']['api_key'];
        
        if (empty($apiKey)) {
            throw new \Exception('Claude API key not configured');
        }
        
        $prompt = $this->createAnalysisPrompt($transcript);
        
        $response = $this->client->request('POST', 'https://api.anthropic.com/v1/messages', [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01'
            ],
            'json' => [
                'model' => 'claude-3-opus-20240229',
                'max_tokens' => 4000,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.3
            ]
        ]);
        
        $responseData = json_decode($response->getBody(), true);
        $content = $responseData['content'][0]['text'];
        
        // Parse the JSON response
        preg_match('/```json(.*?)```/s', $content, $matches);
        
        if (empty($matches[1])) {
            // Try alternative format
            preg_match('/{.*}/s', $content, $matches);
            
            if (empty($matches[0])) {
                throw new \Exception('Failed to parse Claude response');
            }
            
            $jsonStr = $matches[0];
        } else {
            $jsonStr = $matches[1];
        }
        
        $analysis = json_decode(trim($jsonStr), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from Claude: ' . json_last_error_msg());
        }
        
        return [
            'analysis' => $analysis,
            'tokens_used' => $responseData['usage']['input_tokens'] + $responseData['usage']['output_tokens']
        ];
    }
    
    /**
     * Analyze content with GPT-4
     */
    private function analyzeWithGpt4($transcript)
    {
        $apiKey = $this->apiKeys['openai']['api_key'];
        
        if (empty($apiKey)) {
            throw new \Exception('OpenAI API key not configured');
        }
        
        $prompt = $this->createAnalysisPrompt($transcript);
        
        $response = $this->client->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a content analysis expert. Provide detailed analysis in JSON format.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.3,
                'max_tokens' => 4000
            ]
        ]);
        
        $responseData = json_decode($response->getBody(), true);
        $content = $responseData['choices'][0]['message']['content'];
        
        // Parse the JSON response
        preg_match('/```json(.*?)```/s', $content, $matches);
        
        if (empty($matches[1])) {
            // Try alternative format
            preg_match('/{.*}/s', $content, $matches);
            
            if (empty($matches[0])) {
                throw new \Exception('Failed to parse GPT-4 response');
            }
            
            $jsonStr = $matches[0];
        } else {
            $jsonStr = $matches[1];
        }
        
        $analysis = json_decode(trim($jsonStr), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from GPT-4: ' . json_last_error_msg());
        }
        
        return [
            'analysis' => $analysis,
            'tokens_used' => $responseData['usage']['prompt_tokens'] + $responseData['usage']['completion_tokens']
        ];
    }
    
    /**
     * Analyze content with GPT-3.5 Turbo
     */
    private function analyzeWithGpt35($transcript)
    {
        $apiKey = $this->apiKeys['openai']['api_key'];
        
        if (empty($apiKey)) {
            throw new \Exception('OpenAI API key not configured');
        }
        
        $prompt = $this->createAnalysisPrompt($transcript);
        
        $response = $this->client->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a content analysis expert. Provide detailed analysis in JSON format.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.3,
                'max_tokens' => 4000
            ]
        ]);
        
        $responseData = json_decode($response->getBody(), true);
        $content = $responseData['choices'][0]['message']['content'];
        
        // Parse the JSON response
        preg_match('/```json(.*?)```/s', $content, $matches);
        
        if (empty($matches[1])) {
            // Try alternative format
            preg_match('/{.*}/s', $content, $matches);
            
            if (empty($matches[0])) {
                throw new \Exception('Failed to parse GPT-3.5 response');
            }
            
            $jsonStr = $matches[0];
        } else {
            $jsonStr = $matches[1];
        }
        
        $analysis = json_decode(trim($jsonStr), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from GPT-3.5: ' . json_last_error_msg());
        }
        
        return [
            'analysis' => $analysis,
            'tokens_used' => $responseData['usage']['prompt_tokens'] + $responseData['usage']['completion_tokens']
        ];
    }
    
    /**
     * Rewrite content with Claude
     */
    private function rewriteWithClaude($structuredContent, $options)
    {
        $apiKey = $this->apiKeys['claude']['api_key'];
        
        if (empty($apiKey)) {
            throw new \Exception('Claude API key not configured');
        }
        
        $prompt = $this->createRewritePrompt($structuredContent, $options);
        
        $response = $this->client->request('POST', 'https://api.anthropic.com/v1/messages', [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01'
            ],
            'json' => [
                'model' => 'claude-3-opus-20240229',
                'max_tokens' => 4000,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7
            ]
        ]);
        
        $responseData = json_decode($response->getBody(), true);
        $content = $responseData['content'][0]['text'];
        
        // Parse the JSON response
        preg_match('/```json(.*?)```/s', $content, $matches);
        
        if (empty($matches[1])) {
            // Try alternative format
            preg_match('/{.*}/s', $content, $matches);
            
            if (empty($matches[0])) {
                throw new \Exception('Failed to parse Claude response');
            }
            
            $jsonStr = $matches[0];
        } else {
            $jsonStr = $matches[1];
        }
        
        $rewritten = json_decode(trim($jsonStr), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from Claude: ' . json_last_error_msg());
        }
        
        return [
            'rewritten' => $rewritten,
            'tokens_used' => $responseData['usage']['input_tokens'] + $responseData['usage']['output_tokens']
        ];
    }
    
    /**
     * Rewrite content with GPT-4
     */
    private function rewriteWithGpt4($structuredContent, $options)
    {
        $apiKey = $this->apiKeys['openai']['api_key'];
        
        if (empty($apiKey)) {
            throw new \Exception('OpenAI API key not configured');
        }
        
        $prompt = $this->createRewritePrompt($structuredContent, $options);
        
        $response = $this->client->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a creative content writer. Rewrite the provided content according to the instructions.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 4000
            ]
        ]);
        
        $responseData = json_decode($response->getBody(), true);
        $content = $responseData['choices'][0]['message']['content'];
        
        // Parse the JSON response
        preg_match('/```json(.*?)```/s', $content, $matches);
        
        if (empty($matches[1])) {
            // Try alternative format
            preg_match('/{.*}/s', $content, $matches);
            
            if (empty($matches[0])) {
                throw new \Exception('Failed to parse GPT-4 response');
            }
            
            $jsonStr = $matches[0];
        } else {
            $jsonStr = $matches[1];
        }
        
        $rewritten = json_decode(trim($jsonStr), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from GPT-4: ' . json_last_error_msg());
        }
        
        return [
            'rewritten' => $rewritten,
            'tokens_used' => $responseData['usage']['prompt_tokens'] + $responseData['usage']['completion_tokens']
        ];
    }
    
    /**
     * Rewrite content with GPT-3.5 Turbo
     */
    private function rewriteWithGpt35($structuredContent, $options)
    {
        $apiKey = $this->apiKeys['openai']['api_key'];
        
        if (empty($apiKey)) {
            throw new \Exception('OpenAI API key not configured');
        }
        
        $prompt = $this->createRewritePrompt($structuredContent, $options);
        
        $response = $this->client->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a creative content writer. Rewrite the provided content according to the instructions.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 4000
            ]
        ]);
        
        $responseData = json_decode($response->getBody(), true);
        $content = $responseData['choices'][0]['message']['content'];
        
        // Parse the JSON response
        preg_match('/```json(.*?)```/s', $content, $matches);
        
        if (empty($matches[1])) {
            // Try alternative format
            preg_match('/{.*}/s', $content, $matches);
            
            if (empty($matches[0])) {
                throw new \Exception('Failed to parse GPT-3.5 response');
            }
            
            $jsonStr = $matches[0];
        } else {
            $jsonStr = $matches[1];
        }
        
        $rewritten = json_decode(trim($jsonStr), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from GPT-3.5: ' . json_last_error_msg());
        }
        
        return [
            'rewritten' => $rewritten,
            'tokens_used' => $responseData['usage']['prompt_tokens'] + $responseData['usage']['completion_tokens']
        ];
    }
    
    /**
     * Create analysis prompt
     */
    private function createAnalysisPrompt($transcript)
    {
        $prompt = <<<EOT
Analyze the following video transcript and provide a structured analysis in JSON format. 

Identify:
1. Main topic and theme
2. Key points and ideas
3. Content structure (beginning, middle, end)
4. Presentation style and tone
5. Target audience
6. Technical level
7. Strengths and weaknesses
8. Notable quotes
9. Potential rewriting opportunities (e.g., concepts that could be expanded, reorganized, or improved)

OUTPUT FORMAT: Provide your analysis in the following JSON structure:

```json
{
  "main_topic": "Brief description of main topic",
  "theme": "Underlying theme or message",
  "key_points": [
    {"point": "Key point 1", "timestamp_approx": "Where this appears in content"},
    {"point": "Key point 2", "timestamp_approx": "Where this appears in content"}
    // more points as needed
  ],
  "structure": {
    "beginning": "Description of opening",
    "middle": "Description of main body",
    "end": "Description of conclusion"
  },
  "style": {
    "tone": "Formal/informal/conversational/etc.",
    "presentation": "How information is presented",
    "pacing": "Fast/slow/varied"
  },
  "audience": {
    "target": "Who this content is for",
    "technical_level": "Beginner/intermediate/advanced",
    "prerequisites": "What the audience should know"
  },
  "content_assessment": {
    "strengths": ["Strength 1", "Strength 2"],
    "weaknesses": ["Weakness 1", "Weakness 2"],
    "improvement_areas": ["Area 1", "Area 2"]
  },
  "notable_quotes": [
    {"quote": "Quote 1", "context": "Context of quote"},
    {"quote": "Quote 2", "context": "Context of quote"}
  ],
  "keywords": ["keyword1", "keyword2", "keyword3"],
  "summary": "Brief comprehensive summary of the content"
}
```

TRANSCRIPT:
{$transcript}

Remember to provide your analysis in valid JSON format.
EOT;

        return $prompt;
    }
    
    /**
     * Create rewrite prompt
     */
    private function createRewritePrompt($structuredContent, $options)
    {
        // Default options
        $defaultOptions = [
            'level' => 'moderate',
            'change_names' => true,
            'change_locations' => true,
            'change_examples' => true,
            'add_details' => true,
            'tone' => 'informative',
            'sections' => [
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
        
        // Merge with provided options
        $options = array_merge($defaultOptions, $options);
        
        // Prepare options for prompt
        $levelText = match($options['level']) {
            'light' => 'Light rewriting - Maintain most of the content but improve style and language',
            'complete' => 'Complete rewriting - Create entirely new content on the same topic',
            default => 'Moderate rewriting - Significantly alter while keeping the same message'
        };
        
        $toneText = match($options['tone']) {
            'humorous' => 'Humorous and entertaining',
            'dramatic' => 'Dramatic and serious',
            'persuasive' => 'Persuasive and advertising',
            'emotional' => 'Emotional and inspirational',
            default => 'Informative and educational'
        };
        
        // Create list of sections to include
        $sectionsToInclude = [];
        foreach ($options['sections'] as $section => $include) {
            if ($include) {
                $sectionsToInclude[] = $section;
            }
        }
        
        // Create JSON of structured content
        $contentJson = json_encode($structuredContent, JSON_PRETTY_PRINT);
        
        $prompt = <<<EOT
Rewrite the content based on the analysis provided. Follow these guidelines:

REWRITING LEVEL: {$levelText}

CHANGES TO MAKE:
- Change character/person names: {$options['change_names'] ? 'Yes' : 'No'}
- Change locations/settings: {$options['change_locations'] ? 'Yes' : 'No'}
- Change examples/data points: {$options['change_examples'] ? 'Yes' : 'No'}
- Add new interesting details: {$options['add_details'] ? 'Yes' : 'No'}

TONE: {$toneText}

CONTENT STRUCTURE:
Include the following sections in your rewrite:
- Hook: Start with an attention-grabbing opening
- Introduction: Introduce the topic and set expectations
- Main Content: Present the main ideas and information
- Climax: Build to the most important or impactful point
- Twist: Include an unexpected angle or surprise
- Transition: Smoothly move between sections
- Engagement: Ask questions or elements to engage the audience
- Conclusion: Wrap up the main points
- Call-to-Action: End with a clear next step for the audience

SPECIFIC SECTIONS TO INCLUDE: 
EOT;

        // Add sections list
        $prompt .= implode(', ', $sectionsToInclude);
        
        $prompt .= <<<EOT


OUTPUT FORMAT: Provide your rewritten content in the following JSON structure:

```json
{
  "hook": "Attention-grabbing opening",
  "introduction": "Topic introduction and setup",
  "main_content": "Main ideas and information",
  "climax": "Most important or impactful point",
  "twist": "Unexpected angle or surprise element",
  "transition": "Smooth connection between sections",
  "controversy": "Provocative or debatable aspect",
  "engagement": "Questions or interactive elements",
  "conclusion": "Summary and wrap-up",
  "call_to_action": "Next steps for the audience"
}
```

CONTENT ANALYSIS:
{$contentJson}

Remember to provide your rewritten content in valid JSON format.
EOT;

        return $prompt;
    }
}
