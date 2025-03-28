<?php
namespace App\Core;

class App
{
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];

    public function __construct()
    {
        $url = $this->parseUrl();
        
        // Check if controller exists
        if(isset($url[0]) && file_exists('app/controllers/' . ucfirst($url[0]) . 'Controller.php')) {
            $this->controller = ucfirst($url[0]) . 'Controller';
            unset($url[0]);
        }

        // Include the controller
        require_once 'app/controllers/' . $this->controller . '.php';
        
        // Initialize controller
        $controllerClass = 'App\\Controllers\\' . $this->controller;
        $this->controller = new $controllerClass;
        
        // Check if method exists in controller
        if(isset($url[1]) && method_exists($this->controller, $url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        }
        
        // Get URL parameters
        $this->params = $url ? array_values($url) : [];
        
        // Call the method with parameters
        call_user_func_array([$this->controller, $this->method], $this->params);
    }
    
    // Parse URL into controller, method and parameters
    protected function parseUrl()
    {
        if(isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        
        return [];
    }
}
