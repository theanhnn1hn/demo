<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Cài đặt Xử lý</h2>
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
                    <a href="<?= url('/settings') ?>" class="inline-block py-2 px-4 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Tổng quan
                    </a>
                </li>
                <li class="mr-2">
                    <a href="<?= url('/settings/api') ?>" class="inline-block py-2 px-4 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        API
                    </a>
                </li>
                <li class="mr-2">
                    <a href="<?= url('/settings/processing') ?>" class="inline-block py-2 px-4 border-b-2 border-blue-500 font-medium text-sm text-blue-600">
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
        
        <form action="<?= url('/settings/saveProcessing') ?>" method="POST">
            <!-- Video Processing Settings -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-video mr-2 text-blue-600"></i>
                    Cài đặt Video
                </h3>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <?php
                    $videoSettings = array_filter($settings, function($setting) {
                        return strpos($setting['setting_key'], 'processing.max_video') === 0 || 
                               strpos($setting['setting_key'], 'processing.allowed_video') === 0;
                    });
                    ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($videoSettings as $setting): ?>
                        <div>
                            <label for="<?= $setting['setting_key'] ?>" class="block text-sm font-medium text-gray-700">
                                <?= formatProcessingSettingName($setting['setting_key']) ?>
                            </label>
                            
                            <?php if (strpos($setting['setting_key'], 'formats') !== false): ?>
                            <!-- Formats field (comma-separated) -->
                            <div class="mt-1">
                                <input type="text" id="<?= $setting['setting_key'] ?>" 
                                       name="processing[<?= getSettingName($setting['setting_key']) ?>]"
                                       value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="mt-1 text-xs text-gray-500">Ngăn cách các định dạng bằng dấu phẩy (ví dụ: mp4,webm,mkv)</p>
                            </div>
                            <?php else: ?>
                            <!-- Numeric fields (size and duration) -->
                            <div class="mt-1">
                                <input type="number" id="<?= $setting['setting_key'] ?>" 
                                       name="processing[<?= getSettingName($setting['setting_key']) ?>]"
                                       value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                
                                <?php if (strpos($setting['setting_key'], 'duration')): ?>
                                <p class="mt-1 text-xs text-gray-500">Thời lượng tối đa tính bằng giây (3600 = 1 giờ)</p>
                                <?php elseif (strpos($setting['setting_key'], 'size')): ?>
                                <p class="mt-1 text-xs text-gray-500">Kích thước tối đa tính bằng byte (524288000 = 500MB)</p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Language and Tone Settings -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-language mr-2 text-green-600"></i>
                    Ngôn ngữ và Tông giọng
                </h3>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <?php
                    $languageSettings = array_filter($settings, function($setting) {
                        return strpos($setting['setting_key'], 'processing.default_language') === 0 ||
                               strpos($setting['setting_key'], 'processing.default_tone') === 0;
                    });
                    ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($languageSettings as $setting): ?>
                        <div>
                            <label for="<?= $setting['setting_key'] ?>" class="block text-sm font-medium text-gray-700">
                                <?= formatProcessingSettingName($setting['setting_key']) ?>
                            </label>
                            
                            <?php if (strpos($setting['setting_key'], 'language')): ?>
                            <div class="mt-1">
                                <select id="<?= $setting['setting_key'] ?>" 
                                        name="processing[<?= getSettingName($setting['setting_key']) ?>]"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="vi" <?= $setting['setting_value'] === 'vi' ? 'selected' : '' ?>>Tiếng Việt</option>
                                    <option value="en" <?= $setting['setting_value'] === 'en' ? 'selected' : '' ?>>English</option>
                                    <option value="auto" <?= $setting['setting_value'] === 'auto' ? 'selected' : '' ?>>Tự động phát hiện</option>
                                </select>
                            </div>
                            <?php elseif (strpos($setting['setting_key'], 'tone')): ?>
                            <div class="mt-1">
                                <select id="<?= $setting['setting_key'] ?>" 
                                        name="processing[<?= getSettingName($setting['setting_key']) ?>]"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="informative" <?= $setting['setting_value'] === 'informative' ? 'selected' : '' ?>>Thông tin - Giáo dục</option>
                                    <option value="humorous" <?= $setting['setting_value'] === 'humorous' ? 'selected' : '' ?>>Hài hước - Giải trí</option>
                                    <option value="dramatic" <?= $setting['setting_value'] === 'dramatic' ? 'selected' : '' ?>>Kịch tính - Nghiêm túc</option>
                                    <option value="persuasive" <?= $setting['setting_value'] === 'persuasive' ? 'selected' : '' ?>>Thuyết phục - Quảng cáo</option>
                                    <option value="emotional" <?= $setting['setting_value'] === 'emotional' ? 'selected' : '' ?>>Cảm xúc - Truyền cảm hứng</option>
                                </select>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Scan Settings -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-sync-alt mr-2 text-purple-600"></i>
                    Cài đặt quét kênh
                </h3>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <?php
                    $scanSettings = array_filter($settings, function($setting) {
                        return strpos($setting['setting_key'], 'scan.max_videos') === 0;
                    });
                    ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($scanSettings as $setting): ?>
                        <div>
                            <label for="<?= $setting['setting_key'] ?>" class="block text-sm font-medium text-gray-700">
                                <?= formatProcessingSettingName($setting['setting_key']) ?>
                            </label>
                            
                            <div class="mt-1">
                                <input type="number" id="<?= $setting['setting_key'] ?>" 
                                       name="processing[<?= getSettingName($setting['setting_key']) ?>]"
                                       value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="mt-1 text-xs text-gray-500">Số lượng video tối đa được quét từ mỗi kênh trong một lần</p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Scan Frequency Settings -->
                    <div class="mt-6">
                        <h4 class="font-medium text-gray-700 mb-2">Tùy chỉnh tần suất quét</h4>
                        
                        <?php
                        $frequencySettings = array_filter($settings, function($setting) {
                            return strpos($setting['setting_key'], 'scan.frequency_options') === 0;
                        });
                        ?>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <?php foreach ($frequencySettings as $setting): ?>
                            <div>
                                <label for="<?= $setting['setting_key'] ?>" class="block text-sm font-medium text-gray-700">
                                    <?= formatFrequencyName(getFrequencyName($setting['setting_key'])) ?>
                                </label>
                                
                                <div class="mt-1">
                                    <input type="number" id="<?= $setting['setting_key'] ?>" 
                                           name="processing[<?= getSettingName($setting['setting_key']) ?>]"
                                           value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <p class="mt-1 text-xs text-gray-500">Thời gian tính bằng giây</p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Image Settings -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-image mr-2 text-yellow-600"></i>
                    Cài đặt hình ảnh
                </h3>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <?php
                    $imageSettings = array_filter($settings, function($setting) {
                        return strpos($setting['setting_key'], 'image.') === 0;
                    });
                    ?>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <?php foreach ($imageSettings as $setting): ?>
                        <div>
                            <label for="<?= $setting['setting_key'] ?>" class="block text-sm font-medium text-gray-700">
                                <?= formatProcessingSettingName($setting['setting_key']) ?>
                            </label>
                            
                            <?php if (strpos($setting['setting_key'], 'style')): ?>
                            <div class="mt-1">
                                <select id="<?= $setting['setting_key'] ?>" 
                                        name="processing[<?= getSettingName($setting['setting_key']) ?>]"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="realistic" <?= $setting['setting_value'] === 'realistic' ? 'selected' : '' ?>>Realistic Photo</option>
                                    <option value="cartoon" <?= $setting['setting_value'] === 'cartoon' ? 'selected' : '' ?>>Cartoon/Animation</option>
                                    <option value="render3d" <?= $setting['setting_value'] === 'render3d' ? 'selected' : '' ?>>3D Render</option>
                                    <option value="artistic" <?= $setting['setting_value'] === 'artistic' ? 'selected' : '' ?>>Artistic Painting</option>
                                    <option value="cinematic" <?= $setting['setting_value'] === 'cinematic' ? 'selected' : '' ?>>Cinematic Scene</option>
                                    <option value="anime" <?= $setting['setting_value'] === 'anime' ? 'selected' : '' ?>>Anime/Manga</option>
                                </select>
                            </div>
                            <?php elseif (strpos($setting['setting_key'], 'prompt_template')): ?>
                            <div class="mt-1">
                                <textarea id="<?= $setting['setting_key'] ?>" 
                                         name="processing[<?= getSettingName($setting['setting_key']) ?>]"
                                         rows="3"
                                         class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($setting['setting_value']) ?></textarea>
                                <p class="mt-1 text-xs text-gray-500">Sử dụng [theme] để thay thế bằng nội dung chính của mỗi phần</p>
                            </div>
                            <?php else: ?>
                            <div class="mt-1">
                                <input type="number" id="<?= $setting['setting_key'] ?>" 
                                       name="processing[<?= getSettingName($setting['setting_key']) ?>]"
                                       value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                       
                                <?php if (strpos($setting['setting_key'], 'width') || strpos($setting['setting_key'], 'height')): ?>
                                <p class="mt-1 text-xs text-gray-500">Kích thước tính bằng pixel</p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="flex justify-between">
                <button type="button" onclick="confirmReset('processing')" class="px-4 py-2 border border-red-500 text-red-500 rounded-md hover:bg-red-50">
                    <i class="fas fa-undo mr-1"></i> Đặt lại về mặc định
                </button>
                
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-save mr-1"></i> Lưu cài đặt xử lý
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
    function confirmReset(group) {
        if (confirm('Bạn có chắc chắn muốn đặt lại cài đặt xử lý về mặc định? Hành động này không thể hoàn tác.')) {
            const form = document.getElementById('reset-form');
            const groupField = document.getElementById('reset-group');
            
            form.action = '<?= url('/settings/reset') ?>';
            groupField.value = group;
            form.submit();
        }
    }
</script>

<?php
// Helper function to format setting names
function formatProcessingSettingName($key) {
    $parts = explode('.', $key);
    $name = end($parts);
    
    // Create friendly names for settings
    $friendlyNames = [
        'max_video_duration' => 'Thời lượng video tối đa',
        'max_video_size' => 'Kích thước video tối đa',
        'allowed_video_formats' => 'Định dạng video cho phép',
        'default_language' => 'Ngôn ngữ mặc định',
        'default_tone' => 'Tông giọng mặc định',
        'max_videos_per_scan' => 'Số video tối đa mỗi lần quét',
        'default_style' => 'Phong cách ảnh mặc định',
        'default_width' => 'Chiều rộng mặc định',
        'default_height' => 'Chiều cao mặc định',
        'default_prompt_template' => 'Mẫu prompt mặc định'
    ];
    
    return $friendlyNames[$name] ?? ucwords(str_replace('_', ' ', $name));
}

// Helper function to get setting name from key
function getSettingName($key) {
    $parts = explode('.', $key);
    
    if (count($parts) >= 3) {
        return implode('.', array_slice($parts, 1));
    }
    
    return end($parts);
}

// Helper function to get frequency name
function getFrequencyName($key) {
    $parts = explode('.', $key);
    
    if (count($parts) >= 3) {
        return end($parts);
    }
    
    return '';
}

// Helper function to format frequency names
function formatFrequencyName($frequency) {
    $friendlyNames = [
        'hourly' => 'Mỗi giờ',
        '6_hours' => 'Mỗi 6 giờ',
        '12_hours' => 'Mỗi 12 giờ',
        'daily' => 'Mỗi ngày',
        'weekly' => 'Mỗi tuần'
    ];
    
    return $friendlyNames[$frequency] ?? ucwords(str_replace('_', ' ', $frequency));
}
?>
