<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Channel Header -->
        <div class="relative">
            <!-- Banner -->
            <div class="h-40 bg-gray-200">
                <?php if (!empty($channel['banner_url'])): ?>
                <img src="<?= $channel['banner_url'] ?>" alt="<?= $channel['channel_name'] ?> banner" class="w-full h-full object-cover">
                <?php endif; ?>
            </div>
            
            <!-- Avatar and Channel Info -->
            <div class="px-6 pb-5 pt-16 md:flex md:items-start">
                <div class="absolute -mt-16 ml-6">
                    <?php if (!empty($channel['avatar_url'])): ?>
                    <img src="<?= $channel['avatar_url'] ?>" alt="<?= $channel['channel_name'] ?>" class="w-24 h-24 rounded-full border-4 border-white">
                    <?php else: ?>
                    <div class="w-24 h-24 rounded-full bg-gray-300 border-4 border-white flex items-center justify-center text-gray-500">
                        <i class="fas fa-user text-4xl"></i>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-3 md:mt-0 md:ml-28 flex-1">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800"><?= $channel['channel_name'] ?></h1>
                            <p class="text-gray-600"><?= number_format($channel['subscriber_count']) ?> subscribers</p>
                        </div>
                        
                        <div class="mt-3 md:mt-0 flex space-x-2">
                            <a href="<?= url('/channel/edit/' . $channel['id']) ?>" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <i class="fas fa-edit mr-1"></i> Sửa
                            </a>
                            <a href="<?= url('/channel/scan/' . $channel['id']) ?>" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                <i class="fas fa-sync-alt mr-1"></i> Quét ngay
                            </a>
                            <a href="https://www.youtube.com/channel/<?= $channel['channel_id'] ?>" target="_blank" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                <i class="fab fa-youtube mr-1"></i> YouTube
                            </a>
                        </div>
                    </div>
                    
                    <!-- Channel Statistics -->
                    <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 p-3 rounded-lg">
                            <p class="text-xs text-blue-600 uppercase font-semibold mb-1">Tổng số video</p>
                            <p class="text-2xl font-bold text-blue-800"><?= $channel['video_count'] ?></p>
                        </div>
                        
                        <div class="bg-green-50 p-3 rounded-lg">
                            <p class="text-xs text-green-600 uppercase font-semibold mb-1">Đã xử lý</p>
                            <p class="text-2xl font-bold text-green-800"><?= $channel['processed_count'] ?></p>
                        </div>
                        
                        <div class="bg-yellow-50 p-3 rounded-lg">
                            <p class="text-xs text-yellow-600 uppercase font-semibold mb-1">Đang xử lý</p>
                            <p class="text-2xl font-bold text-yellow-800"><?= $channel['processing_count'] ?></p>
                        </div>
                        
                        <div class="bg-red-50 p-3 rounded-lg">
                            <p class="text-xs text-red-600 uppercase font-semibold mb-1">Lỗi</p>
                            <p class="text-2xl font-bold text-red-800"><?= $channel['error_count'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Channel Content Tabs -->
        <div class="border-t border-gray-200">
            <div class="px-6 py-4 bg-gray-50">
                <ul class="flex border-b">
                    <li class="mr-1">
                        <button class="channel-tab-btn inline-block px-4 py-2 border-b-2 border-blue-500 font-medium text-sm text-blue-600 bg-white rounded-t-md" data-tab="videos-tab">
                            Videos
                        </button>
                    </li>
                    <li class="mr-1">
                        <button class="channel-tab-btn inline-block px-4 py-2 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="scan-history-tab">
                            Lịch sử quét
                        </button>
                    </li>
                    <li class="mr-1">
                        <button class="channel-tab-btn inline-block px-4 py-2 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="settings-tab">
                            Cài đặt
                        </button>
                    </li>
                </ul>
            </div>
            
            <!-- Videos Tab -->
            <div id="videos-tab" class="channel-tab-content p-6">
                <!-- Filter options -->
                <div class="mb-6">
                    <div class="flex flex-wrap items-center">
                        <span class="text-gray-700 mr-4">Lọc theo:</span>
                        
                        <div class="flex flex-wrap gap-2">
                            <a href="<?= url('/channel/view/' . $channel['id']) ?>" class="px-3 py-1 rounded-full text-sm <?= $current_status === null ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                                Tất cả
                            </a>
                            <a href="<?= url('/channel/view/' . $channel['id'] . '?status=pending') ?>" class="px-3 py-1 rounded-full text-sm <?= $current_status === 'pending' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                                Chờ xử lý
                            </a>
                            <a href="<?= url('/channel/view/' . $channel['id'] . '?status=processing') ?>" class="px-3 py-1 rounded-full text-sm <?= $current_status === 'processing' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                                Đang xử lý
                            </a>
                            <a href="<?= url('/channel/view/' . $channel['id'] . '?status=completed') ?>" class="px-3 py-1 rounded-full text-sm <?= $current_status === 'completed' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                                Đã hoàn thành
                            </a>
                            <a href="<?= url('/channel/view/' . $channel['id'] . '?status=error') ?>" class="px-3 py-1 rounded-full text-sm <?= $current_status === 'error' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                                Lỗi
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Video List -->
                <?php if (empty($videos['data'])): ?>
                <div class="text-center py-6">
                    <p class="text-gray-500">Không tìm thấy video nào.</p>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($videos['data'] as $video): ?>
                    <div class="border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="relative aspect-w-16 aspect-h-9">
                            <img src="<?= $video['thumbnail_url'] ?>" alt="<?= $video['title'] ?>" class="w-full h-48 object-cover">
                            <div class="absolute bottom-2 right-2 bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded">
                                <?= formatDuration($video['duration']) ?>
                            </div>
                            <?php if ($video['status'] === 'completed'): ?>
                                <div class="absolute top-2 right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                                    <i class="fas fa-check-circle mr-1"></i> Đã hoàn thành
                                </div>
                            <?php elseif ($video['status'] === 'processing'): ?>
                                <div class="absolute top-2 right-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">
                                    <i class="fas fa-cog fa-spin mr-1"></i> Đang xử lý
                                </div>
                            <?php elseif ($video['status'] === 'error'): ?>
                                <div class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Lỗi
                                </div>
                            <?php else: ?>
                                <div class="absolute top-2 right-2 bg-gray-500 text-white text-xs px-2 py-1 rounded-full">
                                    <i class="fas fa-clock mr-1"></i> Chờ xử lý
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-4">
                            <h3 class="text-sm font-medium text-gray-900 mb-1 line-clamp-2">
                                <a href="<?= url('/video/view/' . $video['id']) ?>" class="hover:text-blue-600">
                                    <?= $video['title'] ?>
                                </a>
                            </h3>
                            
                            <p class="text-xs text-gray-500 mb-3">
                                <?= formatDate($video['publish_date'], 'd/m/Y') ?>
                            </p>
                            
                            <div class="flex justify-between">
                                <a href="<?= url('/video/view/' . $video['id']) ?>" class="text-blue-600 hover:text-blue-800 text-xs">
                                    <i class="fas fa-eye mr-1"></i> Chi tiết
                                </a>
                                
                                <?php if ($video['status'] === 'pending'): ?>
                                <a href="<?= url('/video/process/' . $video['id']) ?>" class="text-green-600 hover:text-green-800 text-xs">
                                    <i class="fas fa-play mr-1"></i> Xử lý
                                </a>
                                <?php elseif ($video['status'] === 'completed'): ?>
                                <a href="<?= url('/export/' . $video['id']) ?>" class="text-green-600 hover:text-green-800 text-xs">
                                    <i class="fas fa-file-export mr-1"></i> Xuất
                                </a>
                                <?php endif; ?>
                                
                                <a href="https://www.youtube.com/watch?v=<?= $video['youtube_id'] ?>" target="_blank" class="text-red-600 hover:text-red-800 text-xs">
                                    <i class="fab fa-youtube mr-1"></i> YouTube
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($videos['last_page'] > 1): ?>
                <div class="mt-6 flex justify-center">
                    <div class="flex space-x-2">
                        <?php if ($videos['current_page'] > 1): ?>
                            <a href="<?= url('/channel/view/' . $channel['id'] . '?' . http_build_query(array_merge($_GET, ['page' => $videos['current_page'] - 1]))) ?>" 
                               class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $start = max(1, $videos['current_page'] - 2);
                        $end = min($videos['last_page'], $videos['current_page'] + 2);
                        
                        if ($start > 1) {
                            echo '<a href="' . url('/channel/view/' . $channel['id'] . '?' . http_build_query(array_merge($_GET, ['page' => 1]))) . '" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">1</a>';
                            if ($start > 2) {
                                echo '<span class="px-3 py-1 text-gray-500">...</span>';
                            }
                        }
                        
                        for ($i = $start; $i <= $end; $i++) {
                            $isActive = $i === $videos['current_page'];
                            echo '<a href="' . url('/channel/view/' . $channel['id'] . '?' . http_build_query(array_merge($_GET, ['page' => $i]))) . '" class="px-3 py-1 rounded border ' . 
                                ($isActive ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50') . 
                                '">' . $i . '</a>';
                        }
                        
                        if ($end < $videos['last_page']) {
                            if ($end < $videos['last_page'] - 1) {
                                echo '<span class="px-3 py-1 text-gray-500">...</span>';
                            }
                            echo '<a href="' . url('/channel/view/' . $channel['id'] . '?' . http_build_query(array_merge($_GET, ['page' => $videos['last_page']]))) . '" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">' . $videos['last_page'] . '</a>';
                        }
                        ?>
                        
                        <?php if ($videos['current_page'] < $videos['last_page']): ?>
                            <a href="<?= url('/channel/view/' . $channel['id'] . '?' . http_build_query(array_merge($_GET, ['page' => $videos['current_page'] + 1]))) ?>" 
                               class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Scan History Tab -->
            <div id="scan-history-tab" class="channel-tab-content p-6 hidden">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Lịch sử quét gần đây</h3>
                
                <?php if (empty($scan_logs)): ?>
                <div class="text-center py-6">
                    <p class="text-gray-500">Chưa có lịch sử quét nào.</p>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thời gian bắt đầu
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thời gian kết thúc
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Trạng thái
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tìm thấy
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Đã thêm
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($scan_logs as $log): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= formatDate($log['start_time']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= $log['end_time'] ? formatDate($log['end_time']) : 'N/A' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($log['status'] === 'completed'): ?>
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i> Hoàn thành
                                        </span>
                                    <?php elseif ($log['status'] === 'processing'): ?>
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-spinner fa-spin mr-1"></i> Đang xử lý
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-circle mr-1"></i> Lỗi
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= $log['videos_found'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= $log['videos_added'] ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 flex justify-center">
                    <a href="<?= url('/channel/scan-history/' . $channel['id']) ?>" class="text-blue-600 hover:text-blue-800">
                        Xem tất cả lịch sử quét <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Settings Tab -->
            <div id="settings-tab" class="channel-tab-content p-6 hidden">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Cài đặt kênh</h3>
                
                <form action="<?= url('/channel/edit/' . $channel['id']) ?>" method="POST">
                    <div class="mb-6">
                        <label for="scan_frequency" class="block text-sm font-medium text-gray-700 mb-1">Tần suất quét</label>
                        <select id="scan_frequency" name="scan_frequency" class="w-full max-w-md px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="hourly" <?= $channel['scan_frequency'] === 'hourly' ? 'selected' : '' ?>>Mỗi giờ</option>
                            <option value="6_hours" <?= $channel['scan_frequency'] === '6_hours' ? 'selected' : '' ?>>Mỗi 6 giờ</option>
                            <option value="12_hours" <?= $channel['scan_frequency'] === '12_hours' ? 'selected' : '' ?>>Mỗi 12 giờ</option>
                            <option value="daily" <?= $channel['scan_frequency'] === 'daily' ? 'selected' : '' ?>>Mỗi ngày</option>
                            <option value="weekly" <?= $channel['scan_frequency'] === 'weekly' ? 'selected' : '' ?>>Mỗi tuần</option>
                        </select>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="font-medium text-gray-800 mb-2">Lịch quét tiếp theo</h4>
                        <p><?= $channel['next_scan'] ? formatDate($channel['next_scan']) : 'Not scheduled' ?></p>
                    </div>
                    
                    <div class="bg-red-50 p-4 rounded-lg mb-6">
                        <h4 class="font-medium text-red-800 mb-2 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i> Xóa kênh
                        </h4>
                        <p class="text-red-700 mb-2 text-sm">
                            Thao tác này sẽ xóa kênh và tất cả video liên quan. Không thể hoàn tác.
                        </p>
                        <button type="button" onclick="confirmDelete(<?= $channel['id'] ?>)" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            <i class="fas fa-trash-alt mr-1"></i> Xóa kênh
                        </button>
                    </div>
                    
                    <div class="flex">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            <i class="fas fa-save mr-1"></i> Lưu cài đặt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tabs
        const tabButtons = document.querySelectorAll('.channel-tab-btn');
        const tabContents = document.querySelectorAll('.channel-tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.getAttribute('data-tab');
                
                // Deactivate all tabs
                tabButtons.forEach(btn => {
                    btn.classList.remove('border-blue-500', 'text-blue-600', 'bg-white');
                    btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                });
                
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });
                
                // Activate current tab
                button.classList.add('border-blue-500', 'text-blue-600', 'bg-white');
                button.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                document.getElementById(tabId).classList.remove('hidden');
            });
        });
    });
    
    function confirmDelete(channelId) {
        if (confirm('Bạn có chắc chắn muốn xóa kênh này? Hành động này sẽ xóa tất cả video thuộc kênh và không thể hoàn tác.')) {
            document.getElementById('delete-form-' + channelId).submit();
        }
    }
</script>

<form id="delete-form-<?= $channel['id'] ?>" action="<?= url('/channel/delete/' . $channel['id']) ?>" method="POST" class="hidden">
    <!-- This form will be submitted via JavaScript -->
</form>
