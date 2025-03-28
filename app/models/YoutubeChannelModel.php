<?php
namespace App\Models;

use App\Core\Model;

class YoutubeChannelModel extends Model
{
    protected $table = 'youtube_channels';
    
    /**
     * Get channels that need to be scanned based on next_scan time
     *
     * @return array Channels to scan
     */
    public function getChannelsToScan()
    {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM {$this->table} WHERE next_scan <= ? OR next_scan IS NULL";
        
        return $this->db->all($sql, [$now]);
    }
    
    /**
     * Update scan schedule for a channel
     *
     * @param int $channelId Channel ID
     * @param string $lastScan Last scan time
     * @param string $nextScan Next scan time
     * @return bool Success
     */
    public function updateScanSchedule($channelId, $lastScan, $nextScan)
    {
        $sql = "UPDATE {$this->table} SET last_scan = ?, next_scan = ? WHERE id = ?";
        
        return $this->db->query($sql, [$lastScan, $nextScan, $channelId]);
    }
    
    /**
     * Increment processed video count for a channel
     *
     * @param int $channelId Channel ID
     * @return bool Success
     */
    public function incrementProcessedCount($channelId)
    {
        $sql = "UPDATE {$this->table} SET processed_count = processed_count + 1 WHERE id = ?";
        
        return $this->db->query($sql, [$channelId]);
    }
    
    /**
     * Get channel statistics
     *
     * @param int $channelId Channel ID
     * @return array Channel statistics
     */
    public function getStatistics($channelId)
    {
        $channel = $this->find($channelId);
        
        if (!$channel) {
            return [];
        }
        
        // Get video count
        $db = $this->db->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM videos WHERE channel_id = ?");
        $stmt->execute([$channelId]);
        $videoCount = $stmt->fetch()['total'];
        
        // Get processed video count
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM videos WHERE channel_id = ? AND status = 'completed'");
        $stmt->execute([$channelId]);
        $processedCount = $stmt->fetch()['total'];
        
        // Get processing video count
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM videos WHERE channel_id = ? AND status = 'processing'");
        $stmt->execute([$channelId]);
        $processingCount = $stmt->fetch()['total'];
        
        // Get error video count
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM videos WHERE channel_id = ? AND status = 'error'");
        $stmt->execute([$channelId]);
        $errorCount = $stmt->fetch()['total'];
        
        // Get pending video count
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM videos WHERE channel_id = ? AND status = 'pending'");
        $stmt->execute([$channelId]);
        $pendingCount = $stmt->fetch()['total'];
        
        return [
            'id' => $channel['id'],
            'channel_id' => $channel['channel_id'],
            'channel_name' => $channel['channel_name'],
            'avatar_url' => $channel['avatar_url'],
            'banner_url' => $channel['banner_url'],
            'subscriber_count' => $channel['subscriber_count'],
            'video_count' => $videoCount,
            'processed_count' => $processedCount,
            'processing_count' => $processingCount,
            'error_count' => $errorCount,
            'pending_count' => $pendingCount,
            'last_scan' => $channel['last_scan'],
            'next_scan' => $channel['next_scan'],
            'scan_frequency' => $channel['scan_frequency'],
        ];
    }
    
    /**
     * Check if a channel exists by YouTube channel ID
     *
     * @param string $youtubeChannelId YouTube channel ID
     * @return bool Whether channel exists
     */
    public function channelExists($youtubeChannelId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE channel_id = ?";
        $result = $this->db->single($sql, [$youtubeChannelId]);
        
        return $result['count'] > 0;
    }
    
    /**
     * Add a channel
     *
     * @param array $channelData Channel data
     * @return int|false Channel ID or false on failure
     */
    public function addChannel($channelData)
    {
        // Check if channel already exists
        if ($this->channelExists($channelData['channel_id'])) {
            return false;
        }
        
        // Set default values
        $channelData['created_at'] = date('Y-m-d H:i:s');
        $channelData['updated_at'] = date('Y-m-d H:i:s');
        
        // Calculate next scan time based on frequency
        if (isset($channelData['scan_frequency'])) {
            $frequency = $channelData['scan_frequency'];
            $frequencySeconds = config("scan.frequency_options.{$frequency}", 86400); // Default to daily
            $channelData['next_scan'] = date('Y-m-d H:i:s', time() + $frequencySeconds);
        }
        
        return $this->create($channelData);
    }
    
    /**
     * Get channels with pagination
     *
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param string $search Search term
     * @return array Paginated channels
     */
    public function getChannelsPaginated($page = 1, $perPage = 10, $search = '')
    {
        $offset = ($page - 1) * $perPage;
        
        if (!empty($search)) {
            $sql = "SELECT * FROM {$this->table} WHERE channel_name LIKE ? OR channel_id LIKE ? ORDER BY channel_name ASC LIMIT {$perPage} OFFSET {$offset}";
            $data = $this->db->all($sql, ["%{$search}%", "%{$search}%"]);
            
            $countSql = "SELECT COUNT(*) as count FROM {$this->table} WHERE channel_name LIKE ? OR channel_id LIKE ?";
            $totalCount = $this->db->single($countSql, ["%{$search}%", "%{$search}%"])['count'];
        } else {
            $sql = "SELECT * FROM {$this->table} ORDER BY channel_name ASC LIMIT {$perPage} OFFSET {$offset}";
            $data = $this->db->all($sql);
            
            $totalCount = $this->count();
        }
        
        $lastPage = ceil($totalCount / $perPage);
        
        return [
            'data' => $data,
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $totalCount
        ];
    }
}
