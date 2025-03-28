<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Cài đặt hệ thống</h2>
        </div>
        
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
        
        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
            <ul class="flex flex-wrap -mb-px">
                <li class="mr-2">
                    <a href="<?= url('/settings') ?>" class="inline-block py-2 px-4 border-b-2 border-blue-500 font-medium text-sm text-blue-600">
                        Tổng quan
                    </a>
                </li>
                <li class="mr-2">
                    <a href="<?= url('/settings/api') ?>" class="inline-block py-2 px-4 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        API
                    </a>
                </li>
                <li class="mr-2">
                    <a href="<?= url('/settings/processing') ?>" class="inline-block py-2 px-4 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Xử lý
                    </a>
                </li>
                <li class="mr-2">
                    <a href="<?= url('/settings/user') ?>" class="inline-block py-2 px-4 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Người dùng
                    </a>
                </li>
            </ul>
        </div>
        
        <form action="<?= url('/settings/save') ?>" method="POST">
            <!-- System Settings -->
            <?php foreach ($settings as $group => $groupSettings): ?>
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-cog mr-2 text-gray-600"></i>
                    <?= ucfirst($group) ?>
                </h3>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-1 gap-4">
                        <?php foreach ($groupSettings as $setting): ?>
                        <div>
                            <label for="<?= $setting['setting_key'] ?>" class="block text-sm font-medium text-gray-700">
                                <?= formatSettingName($setting['setting_key']) ?>
                            </label>
                            
                            <?php if (strpos($setting['setting_key'], 'password') !== false || strpos($setting['setting_key'], 'api_key') !== false): ?>
                            <!-- Password/API Key Field -->
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="password" id="<?= $setting['setting_key'] ?>" name="settings[<?= $setting['setting_key'] ?>]"
                                      value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700"
                                      onclick="togglePasswordVisibility('<?= $setting['setting_key'] ?>')">
                                    <i class="fas fa-eye" id="icon-<?= $setting['setting_key'] ?>"></i>
                                </button>
                            </div>
                            <?php elseif (strpos($setting['setting_value'], ',') !== false && strpos($setting['setting_key'], 'formats') !== false): ?>
                            <!-- Array Field (Comma Separated) -->
                            <div class="mt-1">
                                <input type="text" id="<?= $setting['setting_key'] ?>" name="settings[<?= $setting['setting_key'] ?>]"
                                       value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="mt-1 text-xs text-gray-500">Ngăn cách các giá trị bằng dấu phẩy (ví dụ: mp4,webm,mkv)</p>
                            </div>
                            <?php elseif (is_numeric($setting['setting_value'])): ?>
                            <!-- Numeric Field -->
                            <div class="mt-1">
                                <input type="number" id="<?= $setting['setting_key'] ?>" name="settings[<?= $setting['setting_key'] ?>]"
                                       value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <?php elseif (stripos($setting['setting_key'], 'template') !== false): ?>
                            <!-- Template/Large Text Field -->
                            <div class="mt-1">
                                <textarea id="<?= $setting['setting_key'] ?>" name="settings[<?= $setting['setting_key'] ?>]" rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($setting['setting_value']) ?></textarea>
                            </div>
                            <?php else: ?>
                            <!-- Default Text Field -->
                            <div class="mt-1">
                                <input type="text" id="<?= $setting['setting_key'] ?>" name="settings[<?= $setting['setting_key'] ?>]"
                                       value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Reset Button -->
                    <div class="mt-4 flex justify-end">
                        <button type="button" onclick="confirmReset('<?= $group ?>')" class="px-3 py-1 text-sm border border-red-500 text-red-500 rounded hover:bg-red-50">
                            <i class="fas fa-undo mr-1"></i> Đặt lại về mặc định
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-save mr-1"></i> Lưu cài đặt
                </button>
            </div>
        </form>
        
        <!-- Hidden Reset Form -->
        <form id="reset-form" method="POST" class="hidden">
            <input type="hidden" id="reset-group" name="group" value="">
        </form>
    </div>
</div>

<script>
    function togglePasswordVisibility(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById('icon-' + fieldId);
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    function confirmReset(group) {
        if (confirm('Bạn có chắc chắn muốn đặt lại cài đặt của nhóm ' + group + ' về mặc định? Hành động này không thể hoàn tác.')) {
            const form = document.getElementById('reset-form');
            const groupField = document.getElementById('reset-group');
            
            form.action = '<?= url('/settings/reset') ?>';
            groupField.value = group;
            form.submit();
        }
    }
</script>

<?php
// Helper function to format setting key names
function formatSettingName($key) {
    $parts = explode('.', $key);
    $name = end($parts);
    
    // Replace underscores with spaces and capitalize each word
    $name = str_replace('_', ' ', $name);
    $name = ucwords($name);
    
    return $name;
}
?>
