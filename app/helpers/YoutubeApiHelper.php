<?php
namespace App\Helpers;

class YoutubeApiHelper
{
    private $apiKey;
    private $client;
    
    public function __construct()
    {
        $apiConfig = require 'config/api_keys.php';
        $this->apiKey = $apiConfig['youtube']['api_key'];
        
        // Initialize Google API Client
        $this->client = new \Google_Client();
        $this->client->setDeveloperKey($this->apiKey);
        $this->client->setApplicationName(config('app.name'));
    }
    
    /**
     * Get channel details by ID
     *
     * @param string $channelId YouTube channel ID
     * @return array|false Channel details or false on failure
     */
    public function getChannelDetails($channelId)
    {
        try {
            $youtube = new \Google_Service_YouTube($this->client);
            
            $response = $youtube->channels->listChannels('snippet,statistics,brandingSettings', [
                'id' => $channelId
            ]);
            
            if (empty($response->items)) {
                return false;
            }
            
            $channel = $response->items[0];
            
            return [
                'channel_id' => $channel->id,
                'channel_name' => $channel->snippet->title,
                'avatar_url' => $channel->snippet->thumbnails->default->url,
                'banner_url' => $channel->brandingSettings->image->bannerExternalUrl ?? null,
                'subscriber_count' => $channel->statistics->subscriberCount,
                'video_count' => $channel->statistics->videoCount,
                'description' => $channel->snippet->description,
                'published_at' => $channel->snippet->publishedAt,
                'country' => $channel->snippet->country ?? null,
            ];
        } catch (\Exception $e) {
            error_log('YouTube API Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get channel videos
     *
     * @param string $channelId YouTube channel ID
     * @param int $maxResults Maximum number of results to return
     * @param string $publishedAfter Only return videos published after this date (RFC 3339 format)
     * @return array|false Videos or false on failure
     */
    public function getChannelVideos($channelId, $maxResults = 50, $publishedAfter = null)
    {
        try {
            $youtube = new \Google_Service_YouTube($this->client);
            
            // First get upload playlist ID
            $response = $youtube->channels->listChannels('contentDetails', [
                'id' => $channelId
            ]);
            
            if (empty($response->items)) {
                return false;
            }
            
            $uploadsPlaylistId = $response->items[0]->contentDetails->relatedPlaylists->uploads;
            
            // Get videos from uploads playlist
            $params = [
                'playlistId' => $uploadsPlaylistId,
                'maxResults' => $maxResults
            ];
            
            if ($publishedAfter) {
                $params['publishedAfter'] = $publishedAfter;
            }
            
            $playlistItems = $youtube->playlistItems->listPlaylistItems('snippet,contentDetails', $params);
            
            $videoIds = [];
            foreach ($playlistItems->items as $item) {
                $videoIds[] = $item->contentDetails->videoId;
            }
            
            if (empty($videoIds)) {
                return [];
            }
            
            // Get video details
            $videos = $youtube->videos->listVideos('snippet,contentDetails,statistics', [
                'id' => implode(',', $videoIds)
            ]);
            
            $result = [];
            foreach ($videos->items as $video) {
                $duration = $this->parseDuration($video->contentDetails->duration);
                
                $result[] = [
                    'youtube_id' => $video->id,
                    'title' => $video->snippet->title,
                    'description' => $video->snippet->description,
                    'thumbnail_url' => $video->snippet->thumbnails->high->url,
                    'publish_date' => $video->snippet->publishedAt,
                    'duration' => $duration,
                    'view_count' => $video->statistics->viewCount,
                    'like_count' => $video->statistics->likeCount ?? 0,
                    'comment_count' => $video->statistics->commentCount ?? 0,
                ];
            }
            
            return $result;
        } catch (\Exception $e) {
            error_log('YouTube API Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get video details by ID
     *
     * @param string $videoId YouTube video ID
     * @return array|false Video details or false on failure
     */
    public function getVideoDetails($videoId)
    {
        try {
            $youtube = new \Google_Service_YouTube($this->client);
            
            $response = $youtube->videos->listVideos('snippet,contentDetails,statistics', [
                'id' => $videoId
            ]);
            
            if (empty($response->items)) {
                return false;
            }
            
            $video = $response->items[0];
            $duration = $this->parseDuration($video->contentDetails->duration);
            
            return [
                'youtube_id' => $video->id,
                'channel_id' => $video->snippet->channelId,
                'title' => $video->snippet->title,
                'description' => $video->snippet->description,
                'thumbnail_url' => $video->snippet->thumbnails->high->url,
                'publish_date' => $video->snippet->publishedAt,
                'duration' => $duration,
                'view_count' => $video->statistics->viewCount,
                'like_count' => $video->statistics->likeCount ?? 0,
                'comment_count' => $video->statistics->commentCount ?? 0,
            ];
        } catch (\Exception $e) {
            error_log('YouTube API Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Parse ISO 8601 duration to seconds
     *
     * @param string $duration ISO 8601 duration string
     * @return int Duration in seconds
     */
    private function parseDuration($duration)
    {
        $matches = [];
        preg_match('/PT(\d+H)?(\d+M)?(\d+S)?/', $duration, $matches);
        
        $hours = 0;
        $minutes = 0;
        $seconds = 0;
        
        if (isset($matches[1])) {
            $hours = intval(str_replace('H', '', $matches[1]));
        }
        
        if (isset($matches[2])) {
            $minutes = intval(str_replace('M', '', $matches[2]));
        }
        
        if (isset($matches[3])) {
            $seconds = intval(str_replace('S', '', $matches[3]));
        }
        
        return $hours * 3600 + $minutes * 60 + $seconds;
    }
    
    /**
     * Extract YouTube Channel ID from URL
     *
     * @param string $url YouTube channel URL
     * @return string|false Channel ID or false on failure
     */
    public function extractChannelId($url)
    {
        $patterns = [
            '/youtube\.com\/channel\/([^\/\?]+)/', // https://www.youtube.com/channel/UCxxx
            '/youtube\.com\/c\/([^\/\?]+)/',       // https://www.youtube.com/c/xxxx
            '/youtube\.com\/user\/([^\/\?]+)/',    // https://www.youtube.com/user/xxxx
            '/youtube\.com\/@([^\/\?]+)/'          // https://www.youtube.com/@xxxx
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                if (isset($matches[1])) {
                    // If it's a custom URL, we need to get the actual channel ID
                    if (strpos($pattern, 'channel') === false) {
                        try {
                            $youtube = new \Google_Service_YouTube($this->client);
                            
                            // For @handle format
                            if (strpos($pattern, '@') !== false) {
                                $handle = $matches[1];
                                $searchParams = [
                                    'q' => '@' . $handle,
                                    'type' => 'channel',
                                    'maxResults' => 1
                                ];
                            } else {
                                // For /c/ or /user/ format
                                $customName = $matches[1];
                                $searchParams = [
                                    'q' => $customName,
                                    'type' => 'channel',
                                    'maxResults' => 1
                                ];
                            }
                            
                            $response = $youtube->search->listSearch('snippet', $searchParams);
                            
                            if (!empty($response->items)) {
                                return $response->items[0]->id->channelId;
                            }
                        } catch (\Exception $e) {
                            error_log('YouTube API Error: ' . $e->getMessage());
                            return false;
                        }
                    } else {
                        return $matches[1];
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Extract YouTube Video ID from URL
     *
     * @param string $url YouTube video URL
     * @return string|false Video ID or false on failure
     */
    public function extractVideoId($url)
    {
        $patterns = [
            '/youtube\.com\/watch\?v=([^&\?]+)/',   // https://www.youtube.com/watch?v=xxx
            '/youtu\.be\/([^\/\?]+)/',              // https://youtu.be/xxx
            '/youtube\.com\/embed\/([^\/\?]+)/',    // https://www.youtube.com/embed/xxx
            '/youtube\.com\/v\/([^\/\?]+)/'         // https://www.youtube.com/v/xxx
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                if (isset($matches[1])) {
                    return $matches[1];
                }
            }
        }
        
        return false;
    }
}
