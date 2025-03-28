<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\SystemSettingModel;

class SettingsController extends Controller
{
    private $settingsModel;
    
    public function __construct()
    {
        $this->settingsModel = $this->model('SystemSettingModel');
    }
    
    /**
     * System settings page
     */
    public function index()
    {
        // Check if user is logged in and is admin
        $this->requireLogin();
        
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error_message'] = 'You do not have permission to access this page.';
            $this->redirect('/');
        }
        
        // Get all settings grouped by category
        $settings = $this->settingsModel->getAllSettingsGrouped();
        
        // Render settings page
        $this->render('settings/index', [
            'settings' => $settings
        ], 'System Settings');
    }
    
    /**
     * Save system settings
     */
    public function save()
    {
        // Check if user is logged in and is admin
        $this->requireLogin();
        
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error_message'] = 'You do not have permission to access this page.';
            $this->redirect('/');
        }
        
        // Allow only POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings');
        }
        
        // Get posted settings
        $settingsData = $_POST['settings'] ?? [];
        
        // Save settings
        foreach ($settingsData as $key => $value) {
            $this->settingsModel->updateSetting($key, $value);
        }
        
        // Redirect back to settings page
        $_SESSION['success_message'] = 'Settings saved successfully.';
        $this->redirect('/settings');
    }
    
    /**
     * API settings page
     */
    public function api()
    {
        // Check if user is logged in and is admin
        $this->requireLogin();
        
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error_message'] = 'You do not have permission to access this page.';
            $this->redirect('/');
        }
        
        // Get all API settings
        $settings = $this->settingsModel->getSettingsByGroup('api');
        
        // Render API settings page
        $this->render('settings/api', [
            'settings' => $settings
        ], 'API Settings');
    }
    
    /**
     * Save API settings
     */
    public function saveApi()
    {
        // Check if user is logged in and is admin
        $this->requireLogin();
        
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error_message'] = 'You do not have permission to access this page.';
            $this->redirect('/');
        }
        
        // Allow only POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings/api');
        }
        
        // Get posted settings
        $apiSettings = $_POST['api'] ?? [];
        
        // Save API settings
        foreach ($apiSettings as $service => $keys) {
            foreach ($keys as $keyName => $keyValue) {
                $settingKey = "api.{$service}.{$keyName}";
                $this->settingsModel->updateSetting($settingKey, $keyValue);
            }
        }
        
        // Redirect back to API settings page
        $_SESSION['success_message'] = 'API settings saved successfully.';
        $this->redirect('/settings/api');
    }
    
    /**
     * Processing settings page
     */
    public function processing()
    {
        // Check if user is logged in and is admin
        $this->requireLogin();
        
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error_message'] = 'You do not have permission to access this page.';
            $this->redirect('/');
        }
        
        // Get all processing settings
        $settings = $this->settingsModel->getSettingsByGroup('processing');
        
        // Render processing settings page
        $this->render('settings/processing', [
            'settings' => $settings
        ], 'Processing Settings');
    }
    
    /**
     * Save processing settings
     */
    public function saveProcessing()
    {
        // Check if user is logged in and is admin
        $this->requireLogin();
        
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error_message'] = 'You do not have permission to access this page.';
            $this->redirect('/');
        }
        
        // Allow only POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings/processing');
        }
        
        // Get posted settings
        $processingSettings = $_POST['processing'] ?? [];
        
        // Save processing settings
        foreach ($processingSettings as $key => $value) {
            $settingKey = "processing.{$key}";
            $this->settingsModel->updateSetting($settingKey, $value);
        }
        
        // Redirect back to processing settings page
        $_SESSION['success_message'] = 'Processing settings saved successfully.';
        $this->redirect('/settings/processing');
    }
    
    /**
     * User settings page
     */
    public function user()
    {
        // Check if user is logged in and is admin
        $this->requireLogin();
        
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error_message'] = 'You do not have permission to access this page.';
            $this->redirect('/');
        }
        
        // Get all user settings
        $settings = $this->settingsModel->getSettingsByGroup('users');
        
        // Render user settings page
        $this->render('settings/user', [
            'settings' => $settings
        ], 'User Settings');
    }
    
    /**
     * Save user settings
     */
    public function saveUser()
    {
        // Check if user is logged in and is admin
        $this->requireLogin();
        
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error_message'] = 'You do not have permission to access this page.';
            $this->redirect('/');
        }
        
        // Allow only POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings/user');
        }
        
        // Get posted settings
        $userSettings = $_POST['users'] ?? [];
        
        // Save user settings
        foreach ($userSettings as $key => $value) {
            $settingKey = "users.{$key}";
            $this->settingsModel->updateSetting($settingKey, $value);
        }
        
        // Redirect back to user settings page
        $_SESSION['success_message'] = 'User settings saved successfully.';
        $this->redirect('/settings/user');
    }
    
    /**
     * Reset settings to default
     */
    public function reset()
    {
        // Check if user is logged in and is admin
        $this->requireLogin();
        
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error_message'] = 'You do not have permission to access this page.';
            $this->redirect('/');
        }
        
        // Allow only POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings');
        }
        
        // Get group to reset
        $group = $_POST['group'] ?? '';
        
        if (empty($group)) {
            $_SESSION['error_message'] = 'No settings group specified.';
            $this->redirect('/settings');
        }
        
        // Reset settings for group
        $defaultConfig = require 'config/config.php';
        
        if (isset($defaultConfig[$group])) {
            $defaultSettings = $defaultConfig[$group];
            
            foreach ($defaultSettings as $key => $value) {
                if (is_array($value)) {
                    // Skip nested arrays
                    continue;
                }
                
                $settingKey = "{$group}.{$key}";
                $this->settingsModel->updateSetting($settingKey, $value);
            }
            
            $_SESSION['success_message'] = ucfirst($group) . ' settings reset to default.';
        } else {
            $_SESSION['error_message'] = 'Invalid settings group.';
        }
        
        $this->redirect('/settings');
    }
}
