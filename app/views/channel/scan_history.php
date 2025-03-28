<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Lịch sử quét kênh: <?= $channel['channel_name'] ?></h2>
            <a href="<?= url('/channel/view/' . $channel['id']) ?>" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-1"></i> Quay lại chi tiết kênh
            </a>
        </div>
        
        <div class="mb-6 flex flex-col md:flex-row md:items-center">
            <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-4">
                <?php if (!empty($channel['avatar_url'])): ?>
                <img src="<?= $channel['avatar_url'] ?>" alt="<?= $channel['channel_name'] ?>" class="w-16 h-16 rounded-full">
                <?php else: ?>
                <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center">
                    <i class="fas fa-user text-gray-400 text-2xl"></i>
                </div>
                <?php endif; ?>
            </div>
            
            <div>
                <h3 class="text-lg font-medium text-gray-800"><?= $channel['channel_name'] ?></h3>
                <p class="text-sm text-gray-500"><?= number_format($channel['subscriber_count']) ?> subscribers</p>
                <p class="text-sm text-gray-500 mt-1">
                    Tần suất quét: <?= $scan_frequencies[$channel['scan_frequency']] ?? $channel['scan_frequency'] ?>
                </p>
            </div>
            
            <div class="md:ml-auto mt-4 md:mt-0">
                <a href="<?= url('/channel/scan/' . $channel['id']) ?>" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    <i class="fas fa-sync-alt mr-1"></i> Quét ngay
                </a>
            </div>
        </div>
        
        <?php if (empty($scan_logs['data'])): ?>
        <div class="text-center py-8">
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Thông tin
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($scan_logs['data'] as $log): ?>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php if (!empty($log['error_message'])): ?>
                                <span class="text-red-600 cursor-pointer" onclick="showErrorDetails('<?= $log['id'] ?>')">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Xem lỗi
                                </span>
                                
                                <!-- Error Dialog (Hidden) -->
                                <div id="error-details-<?= $log['id'] ?>" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                                    <div class="bg-white rounded-lg p-6 max-w-2xl w-full max-h-[80vh] overflow-y-auto">
                                        <div class="flex justify-between items-center mb-4">
                                            <h3 class="text-lg font-medium text-gray-800">Chi tiết lỗi</h3>
                                            <button onclick="hideErrorDetails('<?= $log['id'] ?>')" class="text-gray-500 hover:text-gray-700">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="bg-red-50 p-4 rounded-lg">
                                            <p class="text-red-700 whitespace-normal"><?= $log['error_message'] ?></p>
                                        </div>
                                        <div class="mt-4 text-right">
                                            <button onclick="hideErrorDetails('<?= $log['id'] ?>')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                                Đóng
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="text-green-600">
                                    <?php if ($log['videos_added'] > 0): ?>
                                        <i class="fas fa-check-circle mr-1"></i> Thành công
                                    <?php else: ?>
                                        <i class="fas fa-info-circle mr-1"></i> Không có video mới
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($scan_logs['last_page'] > 1): ?>
        <div class="mt-6 flex justify-center">
            <div class="flex space-x-2">
                <?php if ($scan_logs['current_page'] > 1): ?>
                    <a href="<?= url('/channel/scan-history/' . $channel['id'] . '?' . http_build_query(array_merge($_GET, ['page' => $scan_logs['current_page'] - 1]))) ?>" 
                       class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                
                <?php
                $start = max(1, $scan_logs['current_page'] - 2);
                $end = min($scan_logs['last_page'], $scan_logs['current_page'] + 2);
                
                if ($start > 1) {
                    echo '<a href="' . url('/channel/scan-history/' . $channel['id'] . '?' . http_build_query(array_merge($_GET, ['page' => 1]))) . '" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">1</a>';
                    if ($start > 2) {
                        echo '<span class="px-3 py-1 text-gray-500">...</span>';
                    }
                }
                
                for ($i = $start; $i <= $end; $i++) {
                    $isActive = $i === $scan_logs['current_page'];
                    echo '<a href="' . url('/channel/scan-history/' . $channel['id'] . '?' . http_build_query(array_merge($_GET, ['page' => $i]))) . '" class="px-3 py-1 rounded border ' . 
                        ($isActive ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50') . 
                        '">' . $i . '</a>';
                }
                
                if ($end < $scan_logs['last_page']) {
                    if ($end < $scan_logs['last_page'] - 1) {
                        echo '<span class="px-3 py-1 text-gray-500">...</span>';
                    }
                    echo '<a href="' . url('/channel/scan-history/' . $channel['id'] . '?' . http_build_query(array_merge($_GET, ['page' => $scan_logs['last_page']]))) . '" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">' . $scan_logs['last_page'] . '</a>';
                }
                ?>
                
                <?php if ($scan_logs['current_page'] < $scan_logs['last_page']): ?>
                    <a href="<?= url('/channel/scan-history/' . $channel['id'] . '?' . http_build_query(array_merge($_GET, ['page' => $scan_logs['current_page'] + 1]))) ?>" 
                       class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    function showErrorDetails(logId) {
        document.getElementById('error-details-' + logId).classList.remove('hidden');
    }
    
    function hideErrorDetails(logId) {
        document.getElementById('error-details-' + logId).classList.add('hidden');
    }
</script>
