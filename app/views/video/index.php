<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 mb-2 md:mb-0">Quản lý video</h2>
            <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-2">
                <a href="<?= url('/video/add') ?>" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 flex items-center justify-center">
                    <i class="fas fa-plus mr-2"></i> Thêm video mới
                </a>
                
                <form action="<?= url('/video') ?>" method="GET" class="flex items-center">
                    <div class="relative">
                        <input type="text" name="search" placeholder="Tìm kiếm video..." value="<?= $search ?>" 
                            class="border border-gray-300 rounded-l-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="submit" class="bg-gray-100 border border-gray-300 border-l-0 rounded-r-md px-4 py-2 hover:bg-gray-200">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Filter options -->
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <div class="flex flex-wrap items-center">
                <span class="text-gray-700 mr-4">Lọc theo:</span>
                
                <div class="flex flex-wrap gap-2">
                    <a href="<?= url('/video') ?>" class="px-3 py-1 rounded-full text-sm <?= $current_status === null ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                        Tất cả
                    </a>
                    <?php foreach ($statuses as $status => $label): ?>
                    <a href="<?= url('/video?status=' . $status) ?>" class="px-3 py-1 rounded-full text-sm <?= $current_status === $status ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                        <?= $label ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Batch actions -->
        <div id="batchActionsContainer" class="px-6 py-3 border-b border-gray-200 bg-white hidden">
            <form id="batchActionForm" method="POST">
                <div class="flex flex-wrap items-center">
                    <label for="batchAction" class="text-gray-700 mr-4">Hành động:</label>
                    
                    <select id="batchAction" name="action" class="border border-gray-300 rounded-md px-3 py-1 mr-4">
                        <option value="">Chọn hành động...</option>
                        <option value="process">Xử lý video</option>
                        <option value="reset">Đặt lại trạng thái</option>
                        <option value="delete">Xóa video</option>
                    </select>
                    
                    <button id="batchActionBtn" type="button" class="px-4 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        Thực hiện
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Video list -->
        <?php if (empty($videos['data'])): ?>
        <div class="p-6 text-center">
            <p class="text-gray-500">Không tìm thấy video nào.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-3">
                            <div class="flex items-center">
                                <input type="checkbox" id="selectAll" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            </div>
                        </th>
                        <th class="px-6 py-3">Video</th>
                        <th class="px-6 py-3">Kênh</th>
                        <th class="px-6 py-3">Thời lượng</th>
                        <th class="px-6 py-3">Ngày thêm</th>
                        <th class="px-6 py-3">Trạng thái</th>
                        <th class="px-6 py-3 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($videos['data'] as $video): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <input type="checkbox" class="video-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" value="<?= $video['id'] ?>">
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <img class="h-12 w-20 object-cover rounded mr-4" src="<?= $video['thumbnail_url'] ?>" alt="<?= $video['title'] ?>">
                                <div>
                                    <a href="<?= url('/video/view/' . $video['id']) ?>" class="text-sm font-medium text-gray-900 hover:text-blue-600 block mb-1">
                                        <?= strlen($video['title']) > 50 ? substr($video['title'], 0, 50) . '...' : $video['title'] ?>
                                    </a>
                                    <a href="https://www.youtube.com/watch?v=<?= $video['youtube_id'] ?>" target="_blank" class="text-xs text-gray-500 hover:text-gray-700 flex items-center">
                                        <i class="fab fa-youtube text-red-600 mr-1"></i> Xem trên YouTube
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if (isset($channels[$video['channel_id']])): ?>
                            <a href="<?= url('/channel/view/' . $video['channel_id']) ?>" class="text-sm text-gray-900 hover:text-blue-600 flex items-center">
                                <img class="h-6 w-6 rounded-full mr-2" src="<?= $channels[$video['channel_id']]['avatar_url'] ?>" alt="<?= $channels[$video['channel_id']]['channel_name'] ?>">
                                <?= $channels[$video['channel_id']]['channel_name'] ?>
                            </a>
                            <?php else: ?>
                            <span class="text-sm text-gray-500">Không có kênh</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= formatDuration($video['duration']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= formatDate($video['created_at'], 'd/m/Y') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($video['status'] === 'completed'): ?>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Đã hoàn thành
                                </span>
                            <?php elseif ($video['status'] === 'processing'): ?>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-cog fa-spin mr-1"></i> Đang xử lý
                                </span>
                            <?php elseif ($video['status'] === 'error'): ?>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Lỗi
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <i class="fas fa-clock mr-1"></i> Chờ xử lý
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="<?= url('/video/view/' . $video['id']) ?>" class="text-blue-600 hover:text-blue-900 mr-2" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            <?php if ($video['status'] === 'pending' || $video['status'] === 'error'): ?>
                            <a href="<?= url('/video/process/' . $video['id']) ?>" class="text-blue-600 hover:text-blue-900 mr-2" title="Xử lý video">
                                <i class="fas fa-play"></i>
                            </a>
                            <?php elseif ($video['status'] === 'processing'): ?>
                            <a href="<?= url('/processing/status/' . $video['id']) ?>" class="text-yellow-600 hover:text-yellow-900 mr-2" title="Xem trạng thái xử lý">
                                <i class="fas fa-tasks"></i>
                            </a>
                            <?php elseif ($video['status'] === 'completed'): ?>
                            <a href="<?= url('/export/' . $video['id']) ?>" class="text-green-600 hover:text-green-900 mr-2" title="Xuất dự án">
                                <i class="fas fa-file-export"></i>
                            </a>
                            <?php endif; ?>
                            
                            <a href="#" onclick="confirmDelete(<?= $video['id'] ?>); return false;" class="text-red-600 hover:text-red-900" title="Xóa video">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                            <form id="delete-form-<?= $video['id'] ?>" action="<?= url('/video/delete/' . $video['id']) ?>" method="POST" class="hidden">
                                <!-- This form will be submitted via JavaScript -->
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($videos['last_page'] > 1): ?>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Hiển thị <?= count($videos['data']) ?> / <?= $videos['total'] ?> video
                </div>
                
                <div class="flex space-x-2">
                    <?php if ($videos['current_page'] > 1): ?>
                        <a href="<?= url('/video?' . http_build_query(array_merge($_GET, ['page' => $videos['current_page'] - 1]))) ?>" 
                           class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $start = max(1, $videos['current_page'] - 2);
                    $end = min($videos['last_page'], $videos['current_page'] + 2);
                    
                    if ($start > 1) {
                        echo '<a href="' . url('/video?' . http_build_query(array_merge($_GET, ['page' => 1]))) . '" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">1</a>';
                        if ($start > 2) {
                            echo '<span class="px-3 py-1 text-gray-500">...</span>';
                        }
                    }
                    
                    for ($i = $start; $i <= $end; $i++) {
                        $isActive = $i === $videos['current_page'];
                        echo '<a href="' . url('/video?' . http_build_query(array_merge($_GET, ['page' => $i]))) . '" class="px-3 py-1 rounded border ' . 
                            ($isActive ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50') . 
                            '">' . $i . '</a>';
                    }
                    
                    if ($end < $videos['last_page']) {
                        if ($end < $videos['last_page'] - 1) {
                            echo '<span class="px-3 py-1 text-gray-500">...</span>';
                        }
                        echo '<a href="' . url('/video?' . http_build_query(array_merge($_GET, ['page' => $videos['last_page']]))) . '" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">' . $videos['last_page'] . '</a>';
                    }
                    ?>
                    
                    <?php if ($videos['current_page'] < $videos['last_page']): ?>
                        <a href="<?= url('/video?' . http_build_query(array_merge($_GET, ['page' => $videos['current_page'] + 1]))) ?>" 
                           class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show batch actions when videos are checked
        const selectAll = document.getElementById('selectAll');
        const videoCheckboxes = document.querySelectorAll('.video-checkbox');
        const batchActionsContainer = document.getElementById('batchActionsContainer');
        
        if (selectAll && videoCheckboxes.length > 0 && batchActionsContainer) {
            // Initialize the container visibility
            updateBatchActionsVisibility();
            
            // Handle select all checkbox
            selectAll.addEventListener('change', function() {
                videoCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                
                updateBatchActionsVisibility();
            });
            
            // Handle individual checkboxes
            videoCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Update select all checkbox
                    selectAll.checked = Array.from(videoCheckboxes).every(cb => cb.checked);
                    
                    updateBatchActionsVisibility();
                });
            });
            
            function updateBatchActionsVisibility() {
                const checkedVideos = document.querySelectorAll('.video-checkbox:checked');
                
                if (checkedVideos.length > 0) {
                    batchActionsContainer.classList.remove('hidden');
                } else {
                    batchActionsContainer.classList.add('hidden');
                }
            }
        }
    });
    
    function confirmDelete(videoId) {
        if (confirm('Bạn có chắc chắn muốn xóa video này? Hành động này không thể hoàn tác.')) {
            document.getElementById('delete-form-' + videoId).submit();
        }
    }
</script>
