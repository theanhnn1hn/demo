<div class="container mx-auto">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Hồ sơ của tôi</h2>
        
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-500"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700"><?= $_SESSION['success_message'] ?></p>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
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
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Column: User Info -->
            <div class="md:col-span-1">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="flex flex-col items-center">
                        <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 mb-3">
                            <i class="fas fa-user text-4xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-800"><?= $user['username'] ?></h3>
                        <p class="text-sm text-gray-500"><?= $user['email'] ?></p>
                        <p class="mt-2 text-sm text-gray-500">
                            <span class="inline-block px-2 py-1 bg-<?= $user['role'] === 'admin' ? 'purple' : 'blue' ?>-100 text-<?= $user['role'] === 'admin' ? 'purple' : 'blue' ?>-800 rounded-full">
                                <?= $user['role'] === 'admin' ? 'Quản trị viên' : 'Người dùng' ?>
                            </span>
                        </p>
                    </div>
                    
                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Thông tin tài khoản</h4>
                        <ul class="space-y-2 text-sm">
                            <li class="flex justify-between">
                                <span class="text-gray-500">Ngày đăng ký:</span>
                                <span><?= formatDate($user['created_at'], 'd/m/Y') ?></span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-500">Đăng nhập gần nhất:</span>
                                <span><?= $user['last_login'] ? formatDate($user['last_login']) : 'N/A' ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="mt-6 bg-blue-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-blue-700 mb-2">Sử dụng API</h4>
                    <div class="mb-2">
                        <div class="flex justify-between text-sm mb-1">
                            <span>Đã sử dụng:</span>
                            <span><?= number_format($api_used) ?> / <?= $api_limit > 0 ? number_format($api_limit) : 'Không giới hạn' ?></span>
                        </div>
                        <div class="w-full bg-blue-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $api_usage_percent ?>%"></div>
                        </div>
                    </div>
                    <p class="text-xs text-blue-600">
                        Tổng số tokens API đã sử dụng trong tháng này.
                    </p>
                </div>
            </div>
            
            <!-- Right Column: Edit Profile -->
            <div class="md:col-span-2">
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                    <h4 class="text-lg font-medium text-gray-800 mb-4">Chỉnh sửa hồ sơ</h4>
                    
                    <form action="<?= url('/user/updateProfile') ?>" method="POST">
                        <div class="space-y-4">
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Tên người dùng</label>
                                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <?php if (isset($_SESSION['form_errors']['username'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?= $_SESSION['form_errors']['username'] ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <?php if (isset($_SESSION['form_errors']['email'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?= $_SESSION['form_errors']['email'] ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-4 mt-4">
                                <h5 class="text-sm font-medium text-gray-700 mb-2">Thay đổi mật khẩu</h5>
                                <p class="text-xs text-gray-500 mb-3">Để trống nếu không muốn thay đổi mật khẩu.</p>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu hiện tại</label>
                                        <input type="password" id="current_password" name="current_password" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <?php if (isset($_SESSION['form_errors']['current_password'])): ?>
                                        <p class="mt-1 text-sm text-red-600"><?= $_SESSION['form_errors']['current_password'] ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div>
                                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu mới</label>
                                        <input type="password" id="new_password" name="new_password" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <?php if (isset($_SESSION['form_errors']['new_password'])): ?>
                                        <p class="mt-1 text-sm text-red-600"><?= $_SESSION['form_errors']['new_password'] ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div>
                                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu mới</label>
                                        <input type="password" id="confirm_password" name="confirm_password" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <?php if (isset($_SESSION['form_errors']['confirm_password'])): ?>
                                        <p class="mt-1 text-sm text-red-600"><?= $_SESSION['form_errors']['confirm_password'] ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <i class="fas fa-save mr-1"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Clear any form errors after displaying them
if (isset($_SESSION['form_errors'])) {
    unset($_SESSION['form_errors']);
}
?>
