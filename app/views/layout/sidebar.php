<!-- Sidebar -->
<div class="w-64 bg-white shadow-md flex-shrink-0 hidden md:block">
    <div class="p-4 border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-800">YouTube Processor</h1>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="mt-4">
        <div class="px-4 py-2 text-sm font-medium text-gray-600">QUẢN LÝ</div>
        <a href="<?= url('/') ?>" class="flex items-center px-4 py-3 text-gray-800 hover:bg-blue-50 <?= $_SERVER['REQUEST_URI'] === '/' ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>">
            <i class="fas fa-home w-5 h-5 mr-3"></i>
            Dashboard
        </a>
        <a href="<?= url('/channel') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 <?= strpos($_SERVER['REQUEST_URI'], '/channel') === 0 ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>">
            <i class="fas fa-tv w-5 h-5 mr-3"></i>
            Kênh YouTube
        </a>
        <a href="<?= url('/video') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 <?= strpos($_SERVER['REQUEST_URI'], '/video') === 0 ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>">
            <i class="fas fa-video w-5 h-5 mr-3"></i>
            Videos
        </a>
        <a href="<?= url('/video/add') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 <?= $_SERVER['REQUEST_URI'] === '/video/add' ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>">
            <i class="fas fa-plus w-5 h-5 mr-3"></i>
            Thêm Video Mới
        </a>
        
        <div class="px-4 py-2 mt-4 text-sm font-medium text-gray-600">XỬ LÝ</div>
        <a href="<?= url('/video?status=processing') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 <?= strpos($_SERVER['REQUEST_URI'], '/video?status=processing') === 0 ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>">
            <i class="fas fa-cogs w-5 h-5 mr-3"></i>
            Đang Xử Lý
        </a>
        <a href="<?= url('/video?status=completed') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 <?= strpos($_SERVER['REQUEST_URI'], '/video?status=completed') === 0 ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>">
            <i class="fas fa-check-circle w-5 h-5 mr-3"></i>
            Đã Hoàn Thành
        </a>
        <a href="<?= url('/video?status=error') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 <?= strpos($_SERVER['REQUEST_URI'], '/video?status=error') === 0 ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>">
            <i class="fas fa-exclamation-triangle w-5 h-5 mr-3"></i>
            Lỗi
        </a>
        
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <div class="px-4 py-2 mt-4 text-sm font-medium text-gray-600">QUẢN TRỊ</div>
        <a href="<?= url('/user/manage') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 <?= strpos($_SERVER['REQUEST_URI'], '/user/manage') === 0 ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>">
            <i class="fas fa-users w-5 h-5 mr-3"></i>
            Quản Lý Người Dùng
        </a>
        <a href="<?= url('/settings') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 <?= strpos($_SERVER['REQUEST_URI'], '/settings') === 0 ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>">
            <i class="fas fa-cog w-5 h-5 mr-3"></i>
            Cài Đặt Hệ Thống
        </a>
        <a href="<?= url('/settings/api') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 <?= strpos($_SERVER['REQUEST_URI'], '/settings/api') === 0 ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>">
            <i class="fas fa-key w-5 h-5 mr-3"></i>
            Cài Đặt API
        </a>
        <?php endif; ?>
        
        <div class="px-4 py-2 mt-4 text-sm font-medium text-gray-600">TÀI KHOẢN</div>
        <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?= url('/user/profile') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 <?= strpos($_SERVER['REQUEST_URI'], '/user/profile') === 0 ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>">
            <i class="fas fa-user w-5 h-5 mr-3"></i>
            Hồ Sơ Của Tôi
        </a>
        <a href="<?= url('/user/logout') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50">
            <i class="fas fa-sign-out-alt w-5 h-5 mr-3"></i>
            Đăng Xuất
        </a>
        <?php else: ?>
        <a href="<?= url('/user/login') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 <?= strpos($_SERVER['REQUEST_URI'], '/user/login') === 0 ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>">
            <i class="fas fa-sign-in-alt w-5 h-5 mr-3"></i>
            Đăng Nhập
        </a>
        <a href="<?= url('/user/register') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 <?= strpos($_SERVER['REQUEST_URI'], '/user/register') === 0 ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>">
            <i class="fas fa-user-plus w-5 h-5 mr-3"></i>
            Đăng Ký
        </a>
        <?php endif; ?>
    </nav>
    
    <!-- Mobile Sidebar Toggle -->
    <div class="absolute top-0 right-0 p-4 md:hidden">
        <button id="toggleSidebar" class="text-gray-500 hover:text-gray-700 focus:outline-none">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</div>

<!-- Mobile Sidebar -->
<div id="mobileSidebar" class="fixed inset-0 z-50 bg-black bg-opacity-50 hidden">
    <div class="w-64 h-full bg-white shadow-md overflow-y-auto">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h1 class="text-xl font-bold text-gray-800">YouTube Processor</h1>
            <button id="closeSidebar" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Same navigation as desktop sidebar -->
        <nav class="mt-4">
            <!-- Copy all navigation links from above -->
            <div class="px-4 py-2 text-sm font-medium text-gray-600">QUẢN LÝ</div>
            <a href="<?= url('/') ?>" class="flex items-center px-4 py-3 text-gray-800 hover:bg-blue-50 <?= $_SERVER['REQUEST_URI'] === '/' ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>">
                <i class="fas fa-home w-5 h-5 mr-3"></i>
                Dashboard
            </a>
            <a href="<?= url('/channel') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 <?= strpos($_SERVER['REQUEST_URI'], '/channel') === 0 ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>">
                <i class="fas fa-tv w-5 h-5 mr-3"></i>
                Kênh YouTube
            </a>
            <a href="<?= url('/video') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 <?= strpos($_SERVER['REQUEST_URI'], '/video') === 0 ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>">
                <i class="fas fa-video w-5 h-5 mr-3"></i>
                Videos
            </a>
            <a href="<?= url('/video/add') ?>" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 <?= $_SERVER['REQUEST_URI'] === '/video/add' ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>">
                <i class="fas fa-plus w-5 h-5 mr-3"></i>
                Thêm Video Mới
            </a>
            
            <!-- Rest of the navigation menu -->
            <!-- ... -->
        </nav>
    </div>
</div>

<!-- Main content container -->
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Top bar -->
    <header class="bg-white shadow-sm z-10">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center md:hidden">
                <button id="mobileMenuBtn" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <div class="ml-4 md:ml-0">
                <h2 class="text-lg font-semibold text-gray-800"><?= isset($page_title) ? $page_title : 'YouTube Processor' ?></h2>
            </div>
            
            <div class="flex items-center">
                <!-- Search Form -->
                <form action="<?= url('/search') ?>" method="GET" class="mr-4 hidden md:block">
                    <div class="relative">
                        <input type="text" name="q" placeholder="Tìm kiếm..." class="w-48 px-3 py-1 pl-8 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </form>
                
                <!-- User Menu -->
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="relative" id="userDropdown">
                    <button class="flex items-center text-gray-700 hover:text-gray-900 focus:outline-none">
                        <span class="mr-1 text-sm hidden md:block"><?= $_SESSION['user_name'] ?? 'User' ?></span>
                        <i class="fas fa-user-circle text-xl"></i>
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>
                    
                    <div id="userDropdownMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden">
                        <a href="<?= url('/user/profile') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Hồ sơ của tôi</a>
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="<?= url('/settings') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Cài đặt</a>
                        <?php endif; ?>
                        <div class="border-t border-gray-100"></div>
                        <a href="<?= url('/user/logout') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Đăng xuất</a>
                    </div>
                </div>
                <?php else: ?>
                <div>
                    <a href="<?= url('/user/login') ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium mr-2">Đăng nhập</a>
                    <a href="<?= url('/user/register') ?>" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-1 rounded-md">Đăng ký</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>
    
    <!-- Main content -->
    <main class="flex-1 overflow-auto bg-gray-100 p-4">
