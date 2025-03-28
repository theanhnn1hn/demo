<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Cấu hình xử lý</h2>
            <a href="<?= url('/video/view/' . $video['id']) ?>" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-1"></i> Trở về Video
            </a>
        </div>
        
        <form action="<?= url('/processing/configure/' . $video['id']) ?>" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div>
                    <h3 class="font-medium text-gray-800 mb-4">Cài đặt tách nội dung</h3>
                    
                    <div class="space-y-4">
                        <!-- Speech to Text API -->
                        <div>
                            <label for="speech_to_text_api" class="block text-sm font-medium text-gray-700 mb-1">
                                API trích xuất subtitle
                            </label>
                            <select name="speech_to_text_api" id="speech_to_text_api" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <?php foreach ($speech_to_text_options as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $settings['speech_to_text_api'] === $value ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Content Analysis API -->
                        <div>
                            <label for="content_analysis_api" class="block text-sm font-medium text-gray-700 mb-1">
                                API phân tích nội dung
                            </label>
                            <select name="content_analysis_api" id="content_analysis_api" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <?php foreach ($content_analysis_options as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $settings['content_analysis_api'] === $value ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Processing Language -->
                        <div>
                            <label for="processing_language" class="block text-sm font-medium text-gray-700 mb-1">
                                Ngôn ngữ xử lý
                            </label>
                            <select name="processing_language" id="processing_language" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <?php foreach ($language_options as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $settings['processing_language'] === $value ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Content Tone -->
                        <div>
                            <label for="content_tone" class="block text-sm font-medium text-gray-700 mb-1">
                                Tông giọng điệu
                            </label>
                            <select name="content_tone" id="content_tone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <?php foreach ($tone_options as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $settings['content_tone'] === $value ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <h3 class="font-medium text-gray-800 mb-4 mt-8">Cài đặt viết lại</h3>
                    
                    <div class="space-y-4">
                        <!-- Rewrite Level -->
                        <div>
                            <label for="rewrite_level" class="block text-sm font-medium text-gray-700 mb-1">
                                Mức độ viết lại
                            </label>
                            <select name="rewrite_level" id="rewrite_level" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <?php foreach ($rewrite_level_options as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $settings['rewrite_level'] === $value ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Rewrite Options -->
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="checkbox" id="change_names" name="change_names" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?= $settings['change_names'] ? 'checked' : '' ?>>
                                <label for="change_names" class="ml-2 block text-sm text-gray-700">
                                    Thay đổi tên nhân vật/thương hiệu
                                </label>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" id="change_locations" name="change_locations" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?= $settings['change_locations'] ? 'checked' : '' ?>>
                                <label for="change_locations" class="ml-2 block text-sm text-gray-700">
                                    Thay đổi địa điểm/bối cảnh
                                </label>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" id="change_examples" name="change_examples" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?= $settings['change_examples'] ? 'checked' : '' ?>>
                                <label for="change_examples" class="ml-2 block text-sm text-gray-700">
                                    Thay đổi ví dụ/con số/dữ liệu
                                </label>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" id="add_details" name="add_details" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?= $settings['add_details'] ? 'checked' : '' ?>>
                                <label for="add_details" class="ml-2 block text-sm text-gray-700">
                                    Thêm chi tiết mới hấp dẫn
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div>
                    <h3 class="font-medium text-gray-800 mb-4">Cấu trúc nội dung</h3>
                    
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="checkbox" id="sections[hook]" name="sections[hook]" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?= $settings['content_sections']['hook'] ? 'checked' : '' ?>>
                            <label for="sections[hook]" class="ml-2 block text-sm text-gray-700">
                                Hook (mở đầu thu hút)
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="sections[introduction]" name="sections[introduction]" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?= $settings['content_sections']['introduction'] ? 'checked' : '' ?>>
                            <label for="sections[introduction]" class="ml-2 block text-sm text-gray-700">
                                Giới thiệu tổng quan
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="sections[main_content]" name="sections[main_content]" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?= $settings['content_sections']['main_content'] ? 'checked' : '' ?>>
                            <label for="sections[main_content]" class="ml-2 block text-sm text-gray-700">
                                Nội dung chính
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="sections[climax]" name="sections[climax]" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?= $settings['content_sections']['climax'] ? 'checked' : '' ?>>
                            <label for="sections[climax]" class="ml-2 block text-sm text-gray-700">
                                Cao trào
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="sections[twist]" name="sections[twist]" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?= $settings['content_sections']['twist'] ? 'checked' : '' ?>>
                            <label for="sections[twist]" class="ml-2 block text-sm text-gray-700">
                                Điểm bất ngờ/twist
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="sections[transition]" name="sections[transition]" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?= $settings['content_sections']['transition'] ? 'checked' : '' ?>>
                            <label for="sections[transition]" class="ml-2 block text-sm text-gray-700">
                                Chuyển tiếp
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="sections[controversy]" name="sections[controversy]" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?= $settings['content_sections']['controversy'] ? 'checked' : '' ?>>
                            <label for="sections[controversy]" class="ml-2 block text-sm text-gray-700">
                                Khúc giật gân/tranh cãi
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="sections[conclusion]" name="sections[conclusion]" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?= $settings['content_sections']['conclusion'] ? 'checked' : '' ?>>
                            <label for="sections[conclusion]" class="ml-2 block text-sm text-gray-700">
                                Hồi kết
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="sections[call_to_action]" name="sections[call_to_action]" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?= $settings['content_sections']['call_to_action'] ? 'checked' : '' ?>>
                            <label for="sections[call_to_action]" class="ml-2 block text-sm text-gray-700">
                                Call-to-Action
                            </label>
                        </div>
                    </div>
                    
                    <h3 class="font-medium text-gray-800 mb-4 mt-8">Cài đặt tạo ảnh minh họa</h3>
                    
                    <div class="space-y-4">
                        <!-- Image API -->
                        <div>
                            <label for="image_api" class="block text-sm font-medium text-gray-700 mb-1">
                                API tạo ảnh
                            </label>
                            <select name="image_api" id="image_api" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <?php foreach ($image_api_options as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $settings['image_api'] === $value ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Image Style -->
                        <div>
                            <label for="image_style" class="block text-sm font-medium text-gray-700 mb-1">
                                Phong cách ảnh
                            </label>
                            <select name="image_style" id="image_style" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <?php foreach ($image_style_options as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $settings['image_style'] === $value ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Image Prompt Template -->
                        <div>
                            <label for="image_prompt_template" class="block text-sm font-medium text-gray-700 mb-1">
                                Điều chỉnh prompt
                            </label>
                            <textarea name="image_prompt_template" id="image_prompt_template" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Thêm các điều chỉnh cho prompt tạo ảnh..."><?= $settings['image_prompt_template'] ?></textarea>
                        </div>
                        
                        <!-- Generate Images for All Sections -->
                        <div class="flex items-center">
                            <input type="checkbox" id="generate_images_for_all" name="generate_images_for_all" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?= $settings['generate_images_for_all'] ? 'checked' : '' ?>>
                            <label for="generate_images_for_all" class="ml-2 block text-sm text-gray-700">
                                Tạo ảnh cho từng phần của cấu trúc nội dung
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Video Information -->
            <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium text-gray-800 mb-4">Thông tin video</h3>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0 mr-4">
                        <img src="<?= $video['thumbnail_url'] ?>" alt="<?= $video['title'] ?>" class="w-32 h-18 object-cover rounded">
                    </div>
                    <div>
                        <h4 class="font-medium"><?= $video['title'] ?></h4>
                        <p class="text-sm text-gray-500 mt-1">
                            Thời lượng: <?= formatDuration($video['duration']) ?> | 
                            Ngày đăng: <?= formatDate($video['publish_date'], 'd/m/Y') ?>
                        </p>
                        <p class="text-sm mt-2"><?= substr($video['description'], 0, 150) ?>...</p>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="mt-8 flex justify-end space-x-4">
                <a href="<?= url('/video/view/' . $video['id']) ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                    Hủy
                </a>
                <button type="submit" name="start_now" value="1" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Lưu và bắt đầu xử lý
                </button>
                <button type="submit" class="px-4 py-2 border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50">
                    Lưu cấu hình
                </button>
            </div>
        </form>
    </div>
</div>
