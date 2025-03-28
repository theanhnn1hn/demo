<?php
namespace App\Models;

use App\Core\Model;

class UserModel extends Model
{
    protected $table = 'users';
    
    /**
     * Check if username exists
     *
     * @param string $username Username to check
     * @return bool Whether username exists
     */
    public function usernameExists($username)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = ?";
        $result = $this->db->single($sql, [$username]);
        
        return $result['count'] > 0;
    }
    
    /**
     * Check if email exists
     *
     * @param string $email Email to check
     * @return bool Whether email exists
     */
    public function emailExists($email)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
        $result = $this->db->single($sql, [$email]);
        
        return $result['count'] > 0;
    }
    
    /**
     * Login user
     *
     * @param string $email User email
     * @param string $password User password
     * @return array|false User data or false on failure
     */
    public function login($email, $password)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        $user = $this->db->single($sql, [$email]);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Update last login time
     *
     * @param int $userId User ID
     * @return bool Success
     */
    public function updateLastLogin($userId)
    {
        $sql = "UPDATE {$this->table} SET last_login = ? WHERE id = ?";
        
        return $this->db->query($sql, [date('Y-m-d H:i:s'), $userId]);
    }
    
    /**
     * Store remember me token
     *
     * @param int $userId User ID
     * @param string $token Remember me token
     * @return bool Success
     */
    public function storeRememberToken($userId, $token)
    {
        $sql = "UPDATE {$this->table} SET remember_token = ? WHERE id = ?";
        
        return $this->db->query($sql, [$token, $userId]);
    }
    
    /**
     * Verify remember me token
     *
     * @param int $userId User ID
     * @param string $token Remember me token
     * @return array|false User data or false on failure
     */
    public function verifyRememberToken($userId, $token)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $user = $this->db->single($sql, [$userId]);
        
        if ($user && password_verify($token, $user['remember_token'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Clear remember me token
     *
     * @param int $userId User ID
     * @return bool Success
     */
    public function clearRememberToken($userId)
    {
        $sql = "UPDATE {$this->table} SET remember_token = NULL WHERE id = ?";
        
        return $this->db->query($sql, [$userId]);
    }
    
    /**
     * Update API usage
     *
     * @param int $userId User ID
     * @param int $tokensUsed Number of tokens used
     * @return bool Success
     */
    public function updateApiUsage($userId, $tokensUsed)
    {
        $sql = "UPDATE {$this->table} SET api_used = api_used + ? WHERE id = ?";
        
        return $this->db->query($sql, [$tokensUsed, $userId]);
    }
    
    /**
     * Check if user has remaining API usage
     *
     * @param int $userId User ID
     * @return bool Whether user has remaining API usage
     */
    public function hasRemainingApiUsage($userId)
    {
        $sql = "SELECT api_used, api_limit FROM {$this->table} WHERE id = ?";
        $result = $this->db->single($sql, [$userId]);
        
        if (!$result) {
            return false;
        }
        
        // If api_limit is 0, there is no limit
        if ($result['api_limit'] === 0) {
            return true;
        }
        
        return $result['api_used'] < $result['api_limit'];
    }
    
    /**
     * Reset API usage for all users
     *
     * @return bool Success
     */
    public function resetApiUsage()
    {
        $sql = "UPDATE {$this->table} SET api_used = 0";
        
        return $this->db->query($sql);
    }
    
    /**
     * Get users with pagination
     *
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param string $search Search term
     * @return array Paginated users
     */
    public function getUsersPaginated($page = 1, $perPage = 20, $search = '')
    {
        $offset = ($page - 1) * $perPage;
        
        if (!empty($search)) {
            $sql = "SELECT * FROM {$this->table} WHERE username LIKE ? OR email LIKE ? ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
            $data = $this->db->all($sql, ["%{$search}%", "%{$search}%"]);
            
            $countSql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username LIKE ? OR email LIKE ?";
            $totalCount = $this->db->single($countSql, ["%{$search}%", "%{$search}%"])['count'];
        } else {
            $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
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
