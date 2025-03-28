<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Cài đặt API</h2>
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
                    <a href="<?= url('/settings/api') ?>" class="inline-block py-2 px-4 border-b-2 border-blue-500 font-medium text-sm text-blue-600">
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
        
        <form action="<?= url('/settings/saveApi') ?>" method="POST">
            <!-- YouTube API Settings -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-800 mb-3 flex items-center">
                    <i class="fab fa-youtube mr-2 text-red-600"></i>
                    YouTube API
                </h3>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <?php
                    $youtubeSettings = array_filter($settings, function($setting) {
                        return strpos($setting['setting_key'], 'api.youtube') === 0;
                    });
                    ?>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <?php foreach ($youtubeSettings as $setting): ?>
                        <div>
                            <label for="<?= $setting['setting_key'] ?>" class="block text-sm font-medium text-gray-700">
                                <?= formatApiSettingName($setting['setting_key']) ?>
                            </label>
                            
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="password" id="<?= $setting['setting_key'] ?>" 
                                       name="api[youtube][<?= getApiKeyName($setting['setting_key']) ?>]"
                                       value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700"
                                      onclick="togglePasswordVisibility('<?= $setting['setting_key'] ?>')">
                                    <i class="fas fa-eye" id="icon-<?= $setting['setting_key'] ?>"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-4 text-sm text-gray-500">
                        <p>
                            <i class="fas fa-info-circle mr-1"></i>
                            Cần API key YouTube để quét kênh và lấy thông tin video. <a href="https://console.cloud.google.com/apis/dashboard" target="_blank" class="text-blue-600 hover:text-blue-800">Tạo API key</a>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Speech to Text API Settings -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-comment-alt mr-2 text-blue-600"></i>
                    Speech-to-Text API
                </h3>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <?php
                    $sttSettings = array_filter($settings, function($setting) {
                        return strpos($setting['setting_key'], 'api.speech_to_text') === 0;
                    });
                    ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($sttSettings as $setting): ?>
                        <div>
                            <label for="<?= $setting['setting_key'] ?>" class="block text-sm font-medium text-gray-700">
                                <?= formatApiSettingName($setting['setting_key']) ?>
                            </label>
                            
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="password" id="<?= $setting['setting_key'] ?>"
                                       name="api[speech_to_text][<?= getServiceAndKeyName($setting['setting_key']) ?>]"
                                       value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700"
                                      onclick="togglePasswordVisibility('<?= $setting['setting_key'] ?>')">
                                    <i class="fas fa-eye" id="icon-<?= $setting['setting_key'] ?>"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-4 text-sm text-gray-500">
                        <p>
                            <i class="fas fa-info-circle mr-1"></i>
                            Các API này được sử dụng để chuyển đổi audio từ video thành text. Chỉ cần thiết lập một trong các API.
                        </p>
                        <ul class="mt-2 list-disc list-inside pl-4">
                            <li><a href="https://www.assemblyai.com/" target="_blank" class="text-blue-600 hover:text-blue-800">AssemblyAI</a> - Dịch vụ chuyển đổi giọng nói thành văn bản</li>
                            <li><a href="https://www.rev.ai/" target="_blank" class="text-blue-600 hover:text-blue-800">Rev.ai</a> - API chuyển đổi âm thanh thành văn bản</li>
                            <li><a href="https://platform.openai.com/docs/guides/speech-to-text" target="_blank" class="text-blue-600 hover:text-blue-800">OpenAI Whisper</a> - API chuyển đổi giọng nói thành văn bản của OpenAI</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- AI Content API Settings -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-brain mr-2 text-purple-600"></i>
                    AI Content API
                </h3>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <?php
                    $aiContentSettings = array_filter($settings, function($setting) {
                        return strpos($setting['setting_key'], 'api.ai_content') === 0;
                    });
                    ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($aiContentSettings as $setting): ?>
                        <div>
                            <label for="<?= $setting['setting_key'] ?>" class="block text-sm font-medium text-gray-700">
                                <?= formatApiSettingName($setting['setting_key']) ?>
                            </label>
                            
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="password" id="<?= $setting['setting_key'] ?>"
                                       name="api[ai_content][<?= getServiceAndKeyName($setting['setting_key']) ?>]"
                                       value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700"
                                      onclick="togglePasswordVisibility('<?= $setting['setting_key'] ?>')">
                                    <i class="fas fa-eye" id="icon-<?= $setting['setting_key'] ?>"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-4 text-sm text-gray-500">
                        <p>
                            <i class="fas fa-info-circle mr-1"></i>
                            Các API này được sử dụng để phân tích và viết lại nội dung. Cần thiết lập tối thiểu một trong hai API.
                        </p>
                        <ul class="mt-2 list-disc list-inside pl-4">
                            <li><a href="https://platform.openai.com/" target="_blank" class="text-blue-600 hover:text-blue-800">OpenAI API</a> - Cung cấp truy cập tới GPT-3.5/GPT-4</li>
                            <li><a href="https://www.anthropic.com/claude" target="_blank" class="text-blue-600 hover:text-blue-800">Claude API</a> - Mô hình AI của Anthropic</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Image Generation API Settings -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-image mr-2 text-green-600"></i>
                    Image Generation API
                </h3>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <?php
                    $imageGenSettings = array_filter($settings, function($setting) {
                        return strpos($setting['setting_key'], 'api.image_generation') === 0;
                    });
                    ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($imageGenSettings as $setting): ?>
                        <div>
                            <label for="<?= $setting['setting_key'] ?>" class="block text-sm font-medium text-gray-700">
                                <?= formatApiSettingName($setting['setting_key']) ?>
                            </label>
                            
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="password" id="<?= $setting['setting_key'] ?>"
                                       name="api[image_generation][<?= getServiceAndKeyName($setting['setting_key']) ?>]"
                                       value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700"
                                      onclick="togglePasswordVisibility('<?= $setting['setting_key'] ?>')">
                                    <i class="fas fa-eye" id="icon-<?= $setting['setting_key'] ?>"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-4 text-sm text-gray-500">
                        <p>
                            <i class="fas fa-info-circle mr-1"></i>
                            Các API này được sử dụng để tạo hình ảnh minh họa. Thiết lập ít nhất một API để sử dụng chức năng tạo ảnh.
                        </p>
                        <ul class="mt-2 list-disc list-inside pl-4">
                            <li><a href="https://platform.openai.com/docs/guides/images" target="_blank" class="text-blue-600 hover:text-blue-800">DALL-E API</a> - Mô hình tạo ảnh của OpenAI</li>
                            <li><a href="https://www.midjourney.com/" target="_blank" class="text-blue-600 hover:text-blue-800">Midjourney API</a> - Dịch vụ tạo ảnh AI</li>
                            <li><a href="https://stability.ai/api" target="_blank" class="text-blue-600 hover:text-blue-800">Stable Diffusion API</a> - API tạo ảnh của Stability AI</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="flex justify-between">
                <button type="button" onclick="confirmReset('api')" class="px-4 py-2 border border-red-500 text-red-500 rounded-md hover:bg-red-50">
                    <i class="fas fa-undo mr-1"></i> Đặt lại về mặc định
                </button>
                
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-save mr-1"></i> Lưu cài đặt API
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
        if (confirm('Bạn có chắc chắn muốn đặt lại cài đặt API về mặc định? Hành động này không thể hoàn tác.')) {
            const form = document.getElementById('reset-form');
            const groupField = document.getElementById('reset-group');
            
            form.action = '<?= url('/settings/reset') ?>';
            groupField.value = group;
            form.submit();
        }
    }
</script>

<?php
// Helper function to format API setting key names
function formatApiSettingName($key) {
    // Extract the service name and key type
    $parts = explode('.', $key);
    
    if (count($parts) >= 3) {
        $service = $parts[1];
        $provider = $parts[2];
        
        if (count($parts) >= 4) {
            $keyType = end($parts);
            
            // Format the display name
            if ($service === 'youtube') {
                if ($keyType === 'api_key') {
                    return 'YouTube API Key';
                } elseif ($keyType === 'client_id') {
                    return 'YouTube Client ID';
                } elseif ($keyType === 'client_secret') {
                    return 'YouTube Client Secret';
                }
            } else {
                return ucfirst($provider) . ' ' . ucwords(str_replace('_', ' ', $keyType));
            }
        } else {
            return ucfirst($provider) . ' API Key';
        }
    }
    
    // Fallback to a simple formatting
    $name = end($parts);
    return ucwords(str_replace('_', ' ', $name));
}

// Helper function to get just the key name part from the setting key
function getApiKeyName($key) {
    $parts = explode('.', $key);
    return end($parts);
}

// Helper function to get service and key name
function getServiceAndKeyName($key) {
    $parts = explode('.', $key);
    
    if (count($parts) >= 4) {
        return $parts[2] . '.' . $parts[3];
    }
    
    return $parts[2] . '.api_key';
}
?>
