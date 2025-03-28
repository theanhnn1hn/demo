<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Xuất dự án</h2>
            <a href="<?= url('/video/view/' . $video['id']) ?>" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-1"></i> Trở về Video
            </a>
        </div>
        
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0 mr-4">
                    <img src="<?= $video['thumbnail_url'] ?>" alt="<?= $video['title'] ?>" class="w-32 h-18 object-cover rounded">
                </div>
                <div>
                    <h3 class="text-lg font-medium"><?= $video['title'] ?></h3>
                    <p class="text-sm text-gray-500 mt-1">
                        <a href="https://www.youtube.com/watch?v=<?= $video['youtube_id'] ?>" target="_blank" class="flex items-center">
                            <i class="fab fa-youtube text-red-600 mr-1"></i> Xem trên YouTube
                        </a>
                    </p>
                    <a href="<?= url('/export/preview/' . $video['id']) ?>" class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
                        <i class="fas fa-eye mr-1"></i> Xem trước nội dung
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Export options form -->
        <form action="<?= url('/export/export/' . $video['id']) ?>" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left column: Export format -->
                <div>
                    <h3 class="font-medium text-gray-800 mb-4">Định dạng xuất</h3>
                    
                    <div class="space-y-4">
                        <?php foreach ($export_formats as $value => $label): ?>
                        <div class="flex items-center">
                            <input type="radio" id="format_<?= $value ?>" name="export_format" value="<?= $value ?>" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" <?= $value === 'markdown' ? 'checked' : '' ?>>
                            <label for="format_<?= $value ?>" class="ml-2 block text-sm text-gray-700">
                                <?= $label ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-6">
                        <h3 class="font-medium text-gray-800 mb-4">Tùy chọn nội dung</h3>
                        
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="checkbox" id="include_subtitles" name="include_subtitles" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="include_subtitles" class="ml-2 block text-sm text-gray-700">
                                    Bao gồm phụ đề gốc
                                </label>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" id="include_images" name="include_images" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" checked>
                                <label for="include_images" class="ml-2 block text-sm text-gray-700">
                                    Bao gồm hình ảnh minh họa
                                </label>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" id="include_prompts" name="include_prompts" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="include_prompts" class="ml-2 block text-sm text-gray-700">
                                    Bao gồm prompt tạo ảnh
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right column: Previous exports -->
                <div>
                    <h3 class="font-medium text-gray-800 mb-4">Xuất trước đó</h3>
                    
                    <?php if (empty($previous_exports)): ?>
                    <p class="text-gray-500 italic">Chưa có xuất nào trước đó.</p>
                    <?php else: ?>
                    <div class="border border-gray-200 rounded-md overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Thời gian
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Định dạng
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Hành động
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($previous_exports as $export): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= formatDate($export['exported_at']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= strtoupper($export['export_format']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="<?= url('/export/download/' . $export['id']) ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fas fa-download"></i> Tải xuống
                                        </a>
                                        <a href="#" onclick="confirmDelete(<?= $export['id'] ?>); return false;" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash-alt"></i> Xóa
                                        </a>
                                        <form id="delete-form-<?= $export['id'] ?>" action="<?= url('/export/delete/' . $export['id']) ?>" method="POST" class="hidden">
                                            <!-- This form will be submitted via JavaScript -->
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-6">
                        <div class="bg-blue-50 rounded-md p-4 border border-blue-200">
                            <h4 class="font-medium text-blue-800 mb-2 flex items-center">
                                <i class="fas fa-info-circle mr-2"></i> Thông tin xuất
                            </h4>
                            <ul class="text-sm text-blue-700 list-disc list-inside space-y-1">
                                <li>Định dạng Markdown phù hợp để sử dụng với các trình soạn thảo hỗ trợ Markdown.</li>
                                <li>Định dạng HTML có thể được mở trực tiếp trong trình duyệt web.</li>
                                <li>Định dạng PDF phù hợp để chia sẻ tài liệu chuyên nghiệp.</li>
                                <li>Định dạng JSON phù hợp để tích hợp với các ứng dụng khác.</li>
                                <li>Định dạng Văn bản đơn giản phù hợp để sao chép và dán nhanh.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="mt-8 flex justify-between">
                <a href="<?= url('/video/view/' . $video['id']) ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                    Hủy
                </a>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    <i class="fas fa-file-export mr-1"></i> Xuất dự án
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function confirmDelete(exportId) {
        if (confirm('Bạn có chắc chắn muốn xóa bản xuất này?')) {
            document.getElementById('delete-form-' + exportId).submit();
        }
    }
</script>
