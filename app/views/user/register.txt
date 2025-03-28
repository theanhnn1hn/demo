<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - YouTube Processor</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset('images/favicon.png') ?>">
    
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        .register-bg {
            background-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="register-bg">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 py-6 px-4 sm:px-10">
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-white">YouTube Processor</h2>
                    <p class="mt-2 text-white text-opacity-80">Đăng ký tài khoản mới</p>
                </div>
            </div>
            
            <div class="py-8 px-4 sm:px-10">
                <?php if (isset($_SESSION['error_message'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?= $_SESSION['error_message'] ?></p>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                
                <form class="space-y-6" action="<?= url('/user/register') ?>" method="POST">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Tên người dùng</label>
                        <div class="mt-1">
                            <input id="username" name="username" type="text" autocomplete="username" required
                                   value="<?= isset($username) ? htmlspecialchars($username) : '' ?>"
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <?php if (isset($errors['username'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $errors['username'] ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" autocomplete="email" required
                                   value="<?= isset($email) ? htmlspecialchars($email) : '' ?>"
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <?php if (isset($errors['email'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $errors['email'] ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu</label>
                        <div class="mt-1">
                            <input id="password" name="password" type="password" autocomplete="new-password" required
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <?php if (isset($errors['password'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $errors['password'] ?></p>
                        <?php endif; ?>
                        <p class="mt-1 text-xs text-gray-500">Mật khẩu cần tối thiểu 8 ký tự.</p>
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Nhập lại mật khẩu</label>
                        <div class="mt-1">
                            <input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" required
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <?php if (isset($errors['confirm_password'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $errors['confirm_password'] ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-user-plus mr-2"></i> Đăng ký
                        </button>
                    </div>
                </form>
                
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">Hoặc</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600">
                            Đã có tài khoản?
                            <a href="<?= url('/user/login') ?>" class="font-medium text-blue-600 hover:text-blue-500">
                                Đăng nhập
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
