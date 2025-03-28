<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;

class UserController extends Controller
{
    private $userModel;
    
    public function __construct()
    {
        $this->userModel = $this->model('UserModel');
    }
    
    /**
     * Login page
     */
    public function login()
    {
        // If already logged in, redirect to home
        if ($this->isLoggedIn()) {
            $this->redirect('/');
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']);
            
            // Validate input
            if (empty($email) || empty($password)) {
                $_SESSION['error_message'] = 'Please fill in all required fields.';
                $this->view('user/login', ['email' => $email]);
                return;
            }
            
            // Attempt login
            $user = $this->userModel->login($email, $password);
            
            if ($user) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['username'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Set remember me cookie if requested
                if ($rememberMe) {
                    $token = bin2hex(random_bytes(32));
                    $tokenHash = password_hash($token, PASSWORD_DEFAULT);
                    
                    // Store token in database
                    $this->userModel->storeRememberToken($user['id'], $tokenHash);
                    
                    // Set cookie for 30 days
                    setcookie('remember_token', $token, time() + 60 * 60 * 24 * 30, '/', '', false, true);
                    setcookie('remember_user', $user['id'], time() + 60 * 60 * 24 * 30, '/', '', false, true);
                }
                
                // Update last login time
                $this->userModel->updateLastLogin($user['id']);
                
                // Redirect to previous page or home
                $redirectUrl = $_SESSION['redirect_after_login'] ?? '/';
                unset($_SESSION['redirect_after_login']);
                
                $this->redirect($redirectUrl);
            } else {
                // Login failed
                $_SESSION['error_message'] = 'Invalid email or password.';
                $this->view('user/login', ['email' => $email]);
            }
        } else {
            // Display login form
            $this->view('user/login');
        }
    }
    
    /**
     * Register page
     */
    public function register()
    {
        // If already logged in, redirect to home
        if ($this->isLoggedIn()) {
            $this->redirect('/');
        }
        
        // Check if registration is enabled
        $registrationEnabled = true; // You can control this via settings
        
        if (!$registrationEnabled) {
            $_SESSION['error_message'] = 'Registration is currently disabled.';
            $this->redirect('/user/login');
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validate input
            $errors = [];
            
            if (empty($username)) {
                $errors['username'] = 'Username is required.';
            } elseif (strlen($username) < 3 || strlen($username) > 50) {
                $errors['username'] = 'Username must be between 3 and 50 characters.';
            } elseif ($this->userModel->usernameExists($username)) {
                $errors['username'] = 'Username already exists.';
            }
            
            if (empty($email)) {
                $errors['email'] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format.';
            } elseif ($this->userModel->emailExists($email)) {
                $errors['email'] = 'Email already exists.';
            }
            
            if (empty($password)) {
                $errors['password'] = 'Password is required.';
            } elseif (strlen($password) < 8) {
                $errors['password'] = 'Password must be at least 8 characters.';
            }
            
            if ($password !== $confirmPassword) {
                $errors['confirm_password'] = 'Passwords do not match.';
            }
            
            if (!empty($errors)) {
                // Display errors
                $this->view('user/register', [
                    'username' => $username,
                    'email' => $email,
                    'errors' => $errors
                ]);
                return;
            }
            
            // Create user
            $userData = [
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'user',
                'api_limit' => config('users.default_api_limit'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $userId = $this->userModel->create($userData);
            
            if ($userId) {
                // Registration successful
                $_SESSION['success_message'] = 'Registration successful. Please log in.';
                $this->redirect('/user/login');
            } else {
                // Registration failed
                $_SESSION['error_message'] = 'Registration failed. Please try again.';
                $this->view('user/register', [
                    'username' => $username,
                    'email' => $email
                ]);
            }
        } else {
            // Display registration form
            $this->view('user/register');
        }
    }
    
    /**
     * Logout
     */
    public function logout()
    {
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token']) && isset($_COOKIE['remember_user'])) {
            $userId = $_COOKIE['remember_user'];
            
            // Remove token from database
            $this->userModel->clearRememberToken($userId);
            
            // Clear cookies
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
            setcookie('remember_user', '', time() - 3600, '/', '', false, true);
        }
        
        // Clear session
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_role']);
        
        // Destroy session
        session_destroy();
        
        // Redirect to login
        $this->redirect('/user/login');
    }
    
    /**
     * Profile page
     */
    public function profile()
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Get user
        $user = $this->userModel->find($_SESSION['user_id']);
        
        if (!$user) {
            $_SESSION['error_message'] = 'User not found.';
            $this->redirect('/');
        }
        
        // Get API usage
        $apiUsed = $user['api_used'];
        $apiLimit = $user['api_limit'];
        $apiUsagePercent = $apiLimit > 0 ? round(($apiUsed / $apiLimit) * 100) : 0;
        
        // Render profile page
        $this->render('user/profile', [
            'user' => $user,
            'api_used' => $apiUsed,
            'api_limit' => $apiLimit,
            'api_usage_percent' => $apiUsagePercent
        ], 'My Profile');
    }
    
    /**
     * Update profile
     */
    public function updateProfile()
    {
        // Check if user is logged in
        $this->requireLogin();
        
        // Allow only POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/profile');
        }
        
        // Get user
        $user = $this->userModel->find($_SESSION['user_id']);
        
        if (!$user) {
            $_SESSION['error_message'] = 'User not found.';
            $this->redirect('/');
        }
        
        // Get form data
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate input
        $errors = [];
        $updateData = [];
        
        // Username validation
        if (empty($username)) {
            $errors['username'] = 'Username is required.';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors['username'] = 'Username must be between 3 and 50 characters.';
        } elseif ($username !== $user['username'] && $this->userModel->usernameExists($username)) {
            $errors['username'] = 'Username already exists.';
        } else {
            $updateData['username'] = $username;
        }
        
        // Email validation
        if (empty($email)) {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format.';
        } elseif ($email !== $user['email'] && $this->userModel->emailExists($email)) {
            $errors['email'] = 'Email already exists.';
        } else {
            $updateData['email'] = $email;
        }
        
        // Password validation
        if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
            if (empty($currentPassword)) {
                $errors['current_password'] = 'Current password is required to set a new password.';
            } elseif (!password_verify($currentPassword, $user['password'])) {
                $errors['current_password'] = 'Current password is incorrect.';
            } else {
                if (empty($newPassword)) {
                    $errors['new_password'] = 'New password is required.';
                } elseif (strlen($newPassword) < 8) {
                    $errors['new_password'] = 'New password must be at least 8 characters.';
                } elseif ($newPassword !== $confirmPassword) {
                    $errors['confirm_password'] = 'Passwords do not match.';
                } else {
                    $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }
            }
        }
        
        if (!empty($errors)) {
            // Display errors
            $_SESSION['form_errors'] = $errors;
            $this->redirect('/user/profile');
        }
        
        // Update user
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        $success = $this->userModel->update($user['id'], $updateData);
        
        if ($success) {
            // Update session data
            if (isset($updateData['username'])) {
                $_SESSION['user_name'] = $updateData['username'];
            }
            
            if (isset($updateData['email'])) {
                $_SESSION['user_email'] = $updateData['email'];
            }
            
            $_SESSION['success_message'] = 'Profile updated successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to update profile. Please try again.';
        }
        
        $this->redirect('/user/profile');
    }
    
    /**
     * Admin: User management
     */
    public function manage()
    {
        // Check if user is logged in and is admin
        $this->requireLogin();
        
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error_message'] = 'You do not have permission to access this page.';
            $this->redirect('/');
        }
        
        // Get page number
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page = max(1, $page);
        
        // Get search term
        $search = $_GET['search'] ?? '';
        
        // Get users with pagination
        $users = $this->userModel->getUsersPaginated($page, 20, $search);
        
        // Render user management page
        $this->render('user/manage', [
            'users' => $users,
            'search' => $search,
            'roles' => [
                'user' => 'Standard User',
                'editor' => 'Editor',
                'admin' => 'Administrator'
            ]
        ], 'User Management');
    }
    
    /**
     * Admin: Edit user
     * 
     * @param int $id User ID
     */
    public function edit($id)
    {
        // Check if user is logged in and is admin
        $this->requireLogin();
        
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error_message'] = 'You do not have permission to access this page.';
            $this->redirect('/');
        }
        
        // Get user
        $user = $this->userModel->find($id);
        
        if (!$user) {
            $_SESSION['error_message'] = 'User not found.';
            $this->redirect('/user/manage');
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $apiLimit = isset($_POST['api_limit']) ? (int)$_POST['api_limit'] : $user['api_limit'];
            $newPassword = $_POST['new_password'] ?? '';
            
            // Validate input
            $errors = [];
            $updateData = [];
            
            // Username validation
            if (empty($username)) {
                $errors['username'] = 'Username is required.';
            } elseif (strlen($username) < 3 || strlen($username) > 50) {
                $errors['username'] = 'Username must be between 3 and 50 characters.';
            } elseif ($username !== $user['username'] && $this->userModel->usernameExists($username)) {
                $errors['username'] = 'Username already exists.';
            } else {
                $updateData['username'] = $username;
            }
            
            // Email validation
            if (empty($email)) {
                $errors['email'] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format.';
            } elseif ($email !== $user['email'] && $this->userModel->emailExists($email)) {
                $errors['email'] = 'Email already exists.';
            } else {
                $updateData['email'] = $email;
            }
            
            // Role validation
            if (!in_array($role, ['user', 'editor', 'admin'])) {
                $errors['role'] = 'Invalid role.';
            } else {
                $updateData['role'] = $role;
            }
            
            // API limit validation
            if ($apiLimit < 0) {
                $errors['api_limit'] = 'API limit cannot be negative.';
            } else {
                $updateData['api_limit'] = $apiLimit;
            }
            
            // Password validation
            if (!empty($newPassword)) {
                if (strlen($newPassword) < 8) {
                    $errors['new_password'] = 'New password must be at least 8 characters.';
                } else {
                    $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }
            }
            
            if (!empty($errors)) {
                // Display errors
                $this->view('user/edit', [
                    'user' => $user,
                    'errors' => $errors,
                    'roles' => [
                        'user' => 'Standard User',
                        'editor' => 'Editor',
                        'admin' => 'Administrator'
                    ]
                ]);
                return;
            }
            
            // Update user
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            $success = $this->userModel->update($user['id'], $updateData);
            
            if ($success) {
                $_SESSION['success_message'] = 'User updated successfully.';
                $this->redirect('/user/manage');
            } else {
                $_SESSION['error_message'] = 'Failed to update user. Please try again.';
                $this->redirect('/user/edit/' . $id);
            }
        } else {
            // Display edit form
            $this->render('user/edit', [
                'user' => $user,
                'roles' => [
                    'user' => 'Standard User',
                    'editor' => 'Editor',
                    'admin' => 'Administrator'
                ]
            ], 'Edit User: ' . $user['username']);
        }
    }
    
    /**
     * Admin: Delete user
     * 
     * @param int $id User ID
     */
    public function delete($id)
    {
        // Check if user is logged in and is admin
        $this->requireLogin();
        
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error_message'] = 'You do not have permission to access this page.';
            $this->redirect('/');
        }
        
        // Allow only POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/manage');
        }
        
        // Get user
        $user = $this->userModel->find($id);
        
        if (!$user) {
            $_SESSION['error_message'] = 'User not found.';
            $this->redirect('/user/manage');
        }
        
        // Prevent deleting self
        if ($user['id'] == $_SESSION['user_id']) {
            $_SESSION['error_message'] = 'You cannot delete your own account.';
            $this->redirect('/user/manage');
        }
        
        // Delete user
        $success = $this->userModel->delete($user['id']);
        
        if ($success) {
            $_SESSION['success_message'] = 'User deleted successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to delete user. Please try again.';
        }
        
        $this->redirect('/user/manage');
    }
}
