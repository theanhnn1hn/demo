<?php
namespace App\Models;

use App\Core\Model;

class SystemSettingModel extends Model
{
    protected $table = 'system_settings';
    
    /**
     * Get setting by key
     *
     * @param string $key Setting key
     * @param mixed $default Default value if key not found
     * @return mixed Setting value or default value
     */
    public function getSetting($key, $default = null)
    {
        $sql = "SELECT setting_value FROM {$this->table} WHERE setting_key = ?";
        $result = $this->db->single($sql, [$key]);
        
        if ($result) {
            return $result['setting_value'];
        }
        
        return $default;
    }
    
    /**
     * Update or create setting
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @param string $group Setting group
     * @return bool Success
     */
    public function updateSetting($key, $value, $group = null)
    {
        // Check if setting exists
        $sql = "SELECT id FROM {$this->table} WHERE setting_key = ?";
        $result = $this->db->single($sql, [$key]);
        
        if ($result) {
            // Update existing setting
            $sql = "UPDATE {$this->table} SET setting_value = ?, updated_at = ? WHERE setting_key = ?";
            return $this->db->query($sql, [$value, date('Y-m-d H:i:s'), $key]);
        } else {
            // Create new setting
            $data = [
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_group' => $group,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            return $this->create($data);
        }
    }
    
    /**
     * Delete setting
     *
     * @param string $key Setting key
     * @return bool Success
     */
    public function deleteSetting($key)
    {
        $sql = "DELETE FROM {$this->table} WHERE setting_key = ?";
        return $this->db->query($sql, [$key]);
    }
    
    /**
     * Get all settings by group
     *
     * @param string $group Setting group
     * @return array Settings
     */
    public function getSettingsByGroup($group)
    {
        $sql = "SELECT * FROM {$this->table} WHERE setting_group = ? ORDER BY setting_key";
        return $this->db->all($sql, [$group]);
    }
    
    /**
     * Get all settings grouped by group
     *
     * @return array Settings grouped by group
     */
    public function getAllSettingsGrouped()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY setting_group, setting_key";
        $results = $this->db->all($sql);
        
        $grouped = [];
        
        foreach ($results as $result) {
            $group = $result['setting_group'];
            
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            
            $grouped[$group][] = $result;
        }
        
        return $grouped;
    }
    
    /**
     * Reset settings to default values
     *
     * @param string $group Setting group to reset
     * @return bool Success
     */
    public function resetToDefault($group)
    {
        // Get default settings from config
        $defaultConfig = require 'config/config.php';
        
        if (isset($defaultConfig[$group])) {
            $defaultSettings = $defaultConfig[$group];
            
            // Start transaction
            $this->db->beginTransaction();
            
            try {
                foreach ($defaultSettings as $key => $value) {
                    if (is_array($value)) {
                        // Handle nested settings
                        foreach ($value as $subKey => $subValue) {
                            if (is_array($subValue)) {
                                continue; // Skip nested arrays
                            }
                            
                            $settingKey = "{$group}.{$key}.{$subKey}";
                            $this->updateSetting($settingKey, $subValue, $group);
                        }
                    } else {
                        // Handle direct settings
                        $settingKey = "{$group}.{$key}";
                        $this->updateSetting($settingKey, $value, $group);
                    }
                }
                
                // Commit transaction
                $this->db->commit();
                return true;
            } catch (\Exception $e) {
                // Rollback transaction
                $this->db->rollback();
                error_log("Error resetting settings: " . $e->getMessage());
                return false;
            }
        }
        
        return false;
    }
}
