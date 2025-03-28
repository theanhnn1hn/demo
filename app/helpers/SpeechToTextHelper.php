<?php
namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SpeechToTextHelper
{
    private $apiKeys;
    private $client;
    private $storagePath;
    
    public function __construct()
    {
        // Load API keys
        $apiConfig = require 'config/api_keys.php';
        $this->apiKeys = $apiConfig['speech_to_text'];
        
        // Initialize HTTP client
        $this->client = new Client([
            'timeout' => 120,
        ]);
        
        // Set storage path
        $this->storagePath = config('storage.subtitles');
        
        // Create storage directory if not exists
        if (!file_exists($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }
    
    /**
     * Convert speech to text using AssemblyAI
     *
     * @param string $audioPath Path to audio file
     * @param string $videoId YouTube video ID
     * @param string $language Language code (e.g. 'en', 'vi')
     * @return array|false Transcription result or false on failure
     */
    public function assemblyAiTranscribe($audioPath, $videoId, $language = 'vi')
    {
        try {
            $apiKey = $this->apiKeys['assembly_ai']['api_key'];
            
            if (empty($apiKey)) {
                throw new \Exception('AssemblyAI API key not configured');
            }
            
            // Step 1: Upload audio file
            $uploadResponse = $this->client->request('POST', 'https://api.assemblyai.com/v2/upload', [
                'headers' => [
                    'Authorization' => $apiKey,
                ],
                'body' => fopen($audioPath, 'r'),
            ]);
            
            $uploadResponseData = json_decode($uploadResponse->getBody(), true);
            $uploadUrl = $uploadResponseData['upload_url'];
            
            // Step 2: Start transcription
            $transcriptionResponse = $this->client->request('POST', 'https://api.assemblyai.com/v2/transcript', [
                'headers' => [
                    'Authorization' => $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'audio_url' => $uploadUrl,
                    'language_code' => $language,
                    'punctuate' => true,
                    'format_text' => true,
                    'speaker_labels' => false,
                ],
            ]);
            
            $transcriptionData = json_decode($transcriptionResponse->getBody(), true);
            $transcriptionId = $transcriptionData['id'];
            
            // Step 3: Poll for completion
            $completed = false;
            $result = null;
            
            while (!$completed) {
                sleep(5); // Wait 5 seconds before polling
                
                $pollResponse = $this->client->request('GET', "https://api.assemblyai.com/v2/transcript/{$transcriptionId}", [
                    'headers' => [
                        'Authorization' => $apiKey,
                    ],
                ]);
                
                $pollData = json_decode($pollResponse->getBody(), true);
                
                if ($pollData['status'] === 'completed') {
                    $completed = true;
                    $result = $pollData;
                } elseif ($pollData['status'] === 'error') {
                    throw new \Exception('AssemblyAI transcription error: ' . $pollData['error']);
                }
            }
            
            // Step 4: Save transcript
            $transcriptText = $result['text'];
            $transcriptWords = $result['words'];
            $subtitlePath = $this->saveSubtitles($videoId, $transcriptText, $transcriptWords);
            
            return [
                'transcript' => $transcriptText,
                'words' => $transcriptWords,
                'subtitle_path' => $subtitlePath,
                'language' => $language,
                'processing_time' => $result['processing_time'],
            ];
        } catch (RequestException $e) {
            error_log('AssemblyAI API Error: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            error_log('AssemblyAI Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Convert speech to text using Rev.ai
     *
     * @param string $audioPath Path to audio file
     * @param string $videoId YouTube video ID
     * @param string $language Language code (e.g. 'en', 'vi')
     * @return array|false Transcription result or false on failure
     */
    public function revAiTranscribe($audioPath, $videoId, $language = 'vi')
    {
        try {
            $apiKey = $this->apiKeys['rev_ai']['api_key'];
            
            if (empty($apiKey)) {
                throw new \Exception('Rev.ai API key not configured');
            }
            
            // Step 1: Submit job
            $response = $this->client->request('POST', 'https://api.rev.ai/speechtotext/v1/jobs', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'media_url' => $audioPath,
                    'language' => $language,
                ],
            ]);
            
            $responseData = json_decode($response->getBody(), true);
            $jobId = $responseData['id'];
            
            // Step 2: Poll for completion
            $completed = false;
            
            while (!$completed) {
                sleep(5); // Wait 5 seconds before polling
                
                $pollResponse = $this->client->request('GET', "https://api.rev.ai/speechtotext/v1/jobs/{$jobId}", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $apiKey,
                    ],
                ]);
                
                $pollData = json_decode($pollResponse->getBody(), true);
                
                if ($pollData['status'] === 'transcribed') {
                    $completed = true;
                } elseif ($pollData['status'] === 'failed') {
                    throw new \Exception('Rev.ai transcription failed: ' . $pollData['failure_detail']);
                }
            }
            
            // Step 3: Get transcript
            $transcriptResponse = $this->client->request('GET', "https://api.rev.ai/speechtotext/v1/jobs/{$jobId}/transcript", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept' => 'application/json',
                ],
            ]);
            
            $transcriptData = json_decode($transcriptResponse->getBody(), true);
            $transcriptText = '';
            $transcriptWords = [];
            
            foreach ($transcriptData['monologues'] as $monologue) {
                foreach ($monologue['elements'] as $element) {
                    if ($element['type'] === 'text') {
                        $transcriptText .= $element['value'] . ' ';
                        $transcriptWords[] = [
                            'text' => $element['value'],
                            'start' => $element['timestamp'],
                            'end' => $element['end_timestamp'],
                            'confidence' => $element['confidence'],
                        ];
                    } elseif ($element['type'] === 'punctuation') {
                        $transcriptText .= $element['value'];
                    }
                }
            }
            
            // Step 4: Save transcript
            $subtitlePath = $this->saveSubtitles($videoId, $transcriptText, $transcriptWords);
            
            return [
                'transcript' => $transcriptText,
                'words' => $transcriptWords,
                'subtitle_path' => $subtitlePath,
                'language' => $language,
                'job_id' => $jobId,
            ];
        } catch (RequestException $e) {
            error_log('Rev.ai API Error: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            error_log('Rev.ai Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Convert speech to text using OpenAI Whisper API
     *
     * @param string $audioPath Path to audio file
     * @param string $videoId YouTube video ID
     * @param string $language Language code (e.g. 'en', 'vi')
     * @return array|false Transcription result or false on failure
     */
    public function whisperTranscribe($audioPath, $videoId, $language = 'vi')
    {
        try {
            $apiKey = $this->apiKeys['whisper']['api_key'];
            
            if (empty($apiKey)) {
                throw new \Exception('OpenAI API key not configured');
            }
            
            // Prepare request
            $response = $this->client->request('POST', 'https://api.openai.com/v1/audio/transcriptions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                ],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($audioPath, 'r'),
                        'filename' => basename($audioPath),
                    ],
                    [
                        'name' => 'model',
                        'contents' => 'whisper-1',
                    ],
                    [
                        'name' => 'language',
                        'contents' => $language,
                    ],
                    [
                        'name' => 'response_format',
                        'contents' => 'verbose_json',
                    ],
                ],
            ]);
            
            $responseData = json_decode($response->getBody(), true);
            
            // Process response
            $transcriptText = $responseData['text'];
            $transcriptWords = [];
            
            foreach ($responseData['segments'] as $segment) {
                foreach ($segment['words'] as $word) {
                    $transcriptWords[] = [
                        'text' => $word['word'],
                        'start' => $word['start'],
                        'end' => $word['end'],
                        'confidence' => $segment['confidence'],
                    ];
                }
            }
            
            // Save transcript
            $subtitlePath = $this->saveSubtitles($videoId, $transcriptText, $transcriptWords);
            
            return [
                'transcript' => $transcriptText,
                'words' => $transcriptWords,
                'subtitle_path' => $subtitlePath,
                'language' => $language,
                'processing_time' => time(),
            ];
        } catch (RequestException $e) {
            error_log('Whisper API Error: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            error_log('Whisper Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Save subtitles to files (plain text and SRT format)
     *
     * @param string $videoId YouTube video ID
     * @param string $transcript Transcript text
     * @param array $words Words with timing information
     * @return array Paths to saved files
     */
    private function saveSubtitles($videoId, $transcript, $words)
    {
        // Save plain text transcript
        $textPath = "{$this->storagePath}/{$videoId}.txt";
        file_put_contents($textPath, $transcript);
        
        // Generate and save SRT subtitles
        $srtPath = "{$this->storagePath}/{$videoId}.srt";
        $srtContent = $this->generateSrtContent($words);
        file_put_contents($srtPath, $srtContent);
        
        // Save words as JSON
        $jsonPath = "{$this->storagePath}/{$videoId}.json";
        file_put_contents($jsonPath, json_encode($words, JSON_PRETTY_PRINT));
        
        return [
            'text' => $textPath,
            'srt' => $srtPath,
            'json' => $jsonPath,
        ];
    }
    
    /**
     * Generate SRT content from words
     *
     * @param array $words Words with timing information
     * @return string SRT content
     */
    private function generateSrtContent($words)
    {
        if (empty($words)) {
            return '';
        }
        
        $srtContent = '';
        $index = 1;
        $currentLine = '';
        $startTime = $words[0]['start'];
        $endTime = 0;
        
        foreach ($words as $i => $word) {
            $currentLine .= $word['text'] . ' ';
            $endTime = $word['end'];
            
            // Create a new subtitle every 10 words or when there is a long pause
            if ($i > 0 && $i % 10 === 0 || 
                (isset($words[$i + 1]) && $words[$i + 1]['start'] - $endTime > 1)) {
                
                $srtContent .= $index . PHP_EOL;
                $srtContent .= $this->formatSrtTime($startTime) . ' --> ' . $this->formatSrtTime($endTime) . PHP_EOL;
                $srtContent .= trim($currentLine) . PHP_EOL . PHP_EOL;
                
                $index++;
                $currentLine = '';
                
                if (isset($words[$i + 1])) {
                    $startTime = $words[$i + 1]['start'];
                }
            }
        }
        
        // Add the last line if needed
        if (!empty($currentLine)) {
            $srtContent .= $index . PHP_EOL;
            $srtContent .= $this->formatSrtTime($startTime) . ' --> ' . $this->formatSrtTime($endTime) . PHP_EOL;
            $srtContent .= trim($currentLine) . PHP_EOL . PHP_EOL;
        }
        
        return $srtContent;
    }
    
    /**
     * Format time for SRT (HH:MM:SS,mmm)
     *
     * @param float $time Time in seconds
     * @return string Formatted time
     */
    private function formatSrtTime($time)
    {
        $hours = floor($time / 3600);
        $minutes = floor(($time / 60) % 60);
        $seconds = floor($time % 60);
        $milliseconds = round(($time - floor($time)) * 1000);
        
        return sprintf('%02d:%02d:%02d,%03d', $hours, $minutes, $seconds, $milliseconds);
    }
    
    /**
     * Get transcript for a video
     *
     * @param string $videoId YouTube video ID
     * @return string|false Transcript text or false if not found
     */
    public function getTranscript($videoId)
    {
        $textPath = "{$this->storagePath}/{$videoId}.txt";
        
        if (file_exists($textPath)) {
            return file_get_contents($textPath);
        }
        
        return false;
    }
    
    /**
     * Get words with timing information for a video
     *
     * @param string $videoId YouTube video ID
     * @return array|false Words or false if not found
     */
    public function getWords($videoId)
    {
        $jsonPath = "{$this->storagePath}/{$videoId}.json";
        
        if (file_exists($jsonPath)) {
            $content = file_get_contents($jsonPath);
            return json_decode($content, true);
        }
        
        return false;
    }
    
    /**
     * Delete subtitle files for a video
     *
     * @param string $videoId YouTube video ID
     * @return bool Success
     */
    public function deleteSubtitleFiles($videoId)
    {
        try {
            $files = [
                "{$this->storagePath}/{$videoId}.txt",
                "{$this->storagePath}/{$videoId}.srt",
                "{$this->storagePath}/{$videoId}.json",
            ];
            
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            
            return true;
        } catch (\Exception $e) {
            error_log('Delete Subtitle Files Error: ' . $e->getMessage());
            return false;
        }
    }
}
