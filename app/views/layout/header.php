<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>YouTube Processor</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset('images/favicon.png') ?>">
    
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
        
        /* Dark Mode Adjustments */
        .dark-mode {
            background-color: #1a202c;
            color: #e2e8f0;
        }
        
        /* Toast Notifications */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px;
            border-radius: 4px;
            z-index: 9999;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease-in-out;
            transform: translateX(120%);
        }
        .toast.show {
            transform: translateX(0);
        }
        .toast-success {
            background-color: #48bb78;
            color: white;
        }
        .toast-error {
            background-color: #f56565;
            color: white;
        }
        .toast-info {
            background-color: #4299e1;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div id="successToast" class="toast toast-success show">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span><?= $_SESSION['success_message'] ?></span>
            </div>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div id="errorToast" class="toast toast-error show">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span><?= $_SESSION['error_message'] ?></span>
            </div>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['info_message'])): ?>
        <div id="infoToast" class="toast toast-info show">
            <div class="flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                <span><?= $_SESSION['info_message'] ?></span>
            </div>
        </div>
        <?php unset($_SESSION['info_message']); ?>
    <?php endif; ?>
    
    <div class="flex flex-grow">
        <!-- Sidebar is included separately -->
