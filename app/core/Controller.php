<?php
namespace App\Core;

class Controller
{
    // Load and return model
    protected function model($model)
    {
        $modelClass = 'App\\Models\\' . $model;
        return new $modelClass();
    }
    
    // Load view with data
    protected function view($view, $data = [])
    {
        // Make data available to the view
        extract($data);
        
        // Include the view file
        if(file_exists('app/views/' . $view . '.php')) {
            require_once 'app/views/' . $view . '.php';
        } else {
            die('View does not exist');
        }
    }
    
    // Render a complete page with header, content and footer
    protected function render($view, $data = [], $title = '')
    {
        // Set default title if not provided
        if(empty($title)) {
            $title = 'YouTube Processor';
        }
        
        // Extend data with title
        $data['page_title'] = $title;
        
        // Include header, view and footer
        $this->view('layout/header', $data);
        $this->view('layout/sidebar', $data);
        $this->view($view, $data);
        $this->view('layout/footer', $data);
    }
    
    // Return JSON response
    protected function json($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
    
    // Redirect to another URL
    protected function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }
    
    // Check if user is logged in
    protected function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }
    
    // Require authentication to access a page
    protected function requireLogin()
    {
        if(!$this->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $this->redirect('/user/login');
        }
    }
}
