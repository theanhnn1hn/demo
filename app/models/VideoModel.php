<?php
namespace App\Models;

use App\Core\Model;

class VideoModel extends Model
{
    protected $table = 'videos';
    
    /**
     * Get pending videos for processing
     *
     * @param int $limit Maximum number of videos to return
     * @return array Pending videos
     */
    public function getPendingVideos($limit = 10)
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'pending' ORDER BY created_at ASC LIMIT {$limit}";
        
        return $this->db->all($sql);
    }
    
    /**
     * Update video status
     *
     * @param int $videoId Video ID
     * @param string $status New status
     * @param string $errorMessage Error message (optional)
     * @return bool Success
     */
    public function updateStatus($videoId, $status, $errorMessage = null)
    {
        $params = ['status' => $status];
        
        if ($status === 'processing') {
            $params['processing_started'] = date('Y-m-d H:i:s');
        } elseif ($status === 'completed' || $status === 'error') {
            $params['processing_completed'] = date('Y-m-d H:i:s');
        }
        
        if ($errorMessage !== null) {
            $params['error_message'] = $errorMessage;
        }
        
        return $this->update($videoId, $params);
    }
    
    /**
     * Check if a video exists by YouTube video ID
     *
     * @param string $youtubeId YouTube video ID
     * @return bool Whether video exists
     */
    public function videoExists($youtubeId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE youtube_id = ?";
        $result = $this->db->single($sql, [$youtubeId]);
        
        return $result['count'] > 0;
    }
    
    /**
     * Add a video
     *
     * @param array $videoData Video data
     * @return int|false Video ID or false on failure
     */
    public function addVideo($videoData)
    {
        // Check if video already exists
        if ($this->videoExists($videoData['youtube_id'])) {
            return false;
        }
        
        // Set default values
        $videoData['status'] = $videoData['status'] ?? 'pending';
        $videoData['created_at'] = date('Y-m-d H:i:s');
        $videoData['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->create($videoData);
    }
    
    /**
     * Get videos by channel
     *
     * @param int $channelId Channel ID
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param string $status Filter by status
     * @return array Paginated videos
     */
    public function getVideosByChannel($channelId, $page = 1, $perPage = 10, $status = null)
    {
        $offset = ($page - 1) * $perPage;
        $params = [$channelId];
        
        if ($status !== null) {
            $statusWhere = " AND status = ?";
            $params[] = $status;
        } else {
            $statusWhere = "";
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE channel_id = ?{$statusWhere} ORDER BY publish_date DESC LIMIT {$perPage} OFFSET {$offset}";
        $data = $this->db->all($sql, $params);
        
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} WHERE channel_id = ?{$statusWhere}";
        $totalCount = $this->db->single($countSql, $params)['count'];
        
        $lastPage = ceil($totalCount / $perPage);
        
        return [
            'data' => $data,
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $totalCount
        ];
    }
    
    /**
     * Get videos with pagination
     *
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param string $search Search term
     * @param string $status Filter by status
     * @return array Paginated videos
     */
    public function getVideosPaginated($page = 1, $perPage = 10, $search = '', $status = null)
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = [];
        
        if (!empty($search)) {
            $where[] = "(title LIKE ? OR description LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if ($status !== null) {
            $where[] = "status = ?";
            $params[] = $status;
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "SELECT * FROM {$this->table} {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $data = $this->db->all($sql, $params);
        
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} {$whereClause}";
        $totalCount = $this->db->single($countSql, $params)['count'];
        
        $lastPage = ceil($totalCount / $perPage);
        
        return [
            'data' => $data,
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $totalCount
        ];
    }
    
    /**
     * Get video with all related data
     *
     * @param int $videoId Video ID
     * @return array|false Video data or false on failure
     */
    public function getVideoWithData($videoId)
    {
        $video = $this->find($videoId);
        
        if (!$video) {
            return false;
        }
        
        // Get channel data
        $db = $this->db->getConnection();
        $stmt = $db->prepare("SELECT * FROM youtube_channels WHERE id = ?");
        $stmt->execute([$video['channel_id']]);
        $channel = $stmt->fetch();
        
        // Get processing data
        $stmt = $db->prepare("SELECT * FROM video_processing WHERE video_id = ?");
        $stmt->execute([$videoId]);
        $processing = $stmt->fetch();
        
        // Get content analysis
        $stmt = $db->prepare("SELECT * FROM content_analysis WHERE video_id = ?");
        $stmt->execute([$videoId]);
        $contentAnalysis = $stmt->fetch();
        
        // Get rewritten content
        $stmt = $db->prepare("SELECT * FROM rewritten_content WHERE video_id = ?");
        $stmt->execute([$videoId]);
        $rewrittenContent = $stmt->fetch();
        
        // Get generated images
        $stmt = $db->prepare("SELECT * FROM generated_images WHERE content_section_id = ? AND content_section_type = 'video'");
        $stmt->execute([$videoId]);
        $generatedImages = $stmt->fetchAll();
        
        // Get exported projects
        $stmt = $db->prepare("SELECT * FROM exported_projects WHERE video_id = ?");
        $stmt->execute([$videoId]);
        $exportedProjects = $stmt->fetchAll();
        
        return [
            'video' => $video,
            'channel' => $channel,
            'processing' => $processing,
            'content_analysis' => $contentAnalysis,
            'rewritten_content' => $rewrittenContent,
            'generated_images' => $generatedImages,
            'exported_projects' => $exportedProjects
        ];
    }
    
    /**
     * Get processing statistics
     *
     * @return array Processing statistics
     */
    public function getProcessingStats()
    {
        $db = $this->db->getConnection();
        
        // Get total videos
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM {$this->table}");
        $stmt->execute();
        $totalVideos = $stmt->fetch()['total'];
        
        // Get pending videos
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'pending'");
        $stmt->execute();
        $pendingVideos = $stmt->fetch()['total'];
        
        // Get processing videos
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'processing'");
        $stmt->execute();
        $processingVideos = $stmt->fetch()['total'];
        
        // Get completed videos
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'completed'");
        $stmt->execute();
        $completedVideos = $stmt->fetch()['total'];
        
        // Get error videos
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'error'");
        $stmt->execute();
        $errorVideos = $stmt->fetch()['total'];
        
        return [
            'total' => $totalVideos,
            'pending' => $pendingVideos,
            'processing' => $processingVideos,
            'completed' => $completedVideos,
            'error' => $errorVideos
        ];
    }
}
