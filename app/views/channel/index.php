<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 mb-2 md:mb-0">Quản lý kênh YouTube</h2>
            <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-2">
                <a href="<?= url('/channel/add') ?>" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 flex items-center justify-center">
                    <i class="fas fa-plus mr-2"></i> Thêm kênh mới
                </a>
                
                <form action="<?= url('/channel') ?>" method="GET" class="flex items-center">
                    <div class="relative">
                        <input type="text" name="search" placeholder="Tìm kiếm kênh..." value="<?= $search ?>" 
                            class="border border-gray-300 rounded-l-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="submit" class="bg-gray-100 border border-gray-300 border-l-0 rounded-r-md px-4 py-2 hover:bg-gray-200">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Channel Grid -->
        <?php if (empty($channels['data'])): ?>
        <div class="p-6 text-center">
            <p class="text-gray-500">Không tìm thấy kênh nào.</p>
        </div>
        <?php else: ?>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($channels['data'] as $channel): ?>
            <div class="border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200">
                <div class="h-24 bg-gray-100 relative">
                    <?php if (!empty($channel['banner_url'])): ?>
                    <img src="<?= $channel['banner_url'] ?>" alt="<?= $channel['channel_name'] ?> banner" class="w-full h-full object-cover">
                    <?php else: ?>
                    <div class="w-full h-full bg-gray-200 flex items-center justify-center text-gray-400">
                        <i class="fas fa-image text-3xl"></i>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="relative px-4 pt-12 pb-4">
                    <div class="absolute -top-8 left-4">
                        <?php if (!empty($channel['avatar_url'])): ?>
                        <img src="<?= $channel['avatar_url'] ?>" alt="<?= $channel['channel_name'] ?>" class="w-16 h-16 rounded-full border-4 border-white">
                        <?php else: ?>
                        <div class="w-16 h-16 rounded-full bg-gray-300 border-4 border-white flex items-center justify-center text-gray-500">
                            <i class="fas fa-user text-2xl"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <h3 class="text-lg font-medium text-gray-800 mb-1">
                        <a href="<?= url('/channel/view/' . $channel['id']) ?>" class="hover:text-blue-600">
                            <?= $channel['channel_name'] ?>
                        </a>
                    </h3>
                    
                    <p class="text-sm text-gray-500 mb-3">
                        <?= number_format($channel['subscriber_count']) ?> subscribers
                    </p>
                    
                    <div class="flex flex-wrap gap-2 text-sm mb-4">
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                            <i class="fas fa-video mr-1"></i> <?= $channel['video_count'] ?? 0 ?> videos
                        </span>
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full">
                            <i class="fas fa-check-circle mr-1"></i> <?= $channel['processed_count'] ?> processed
                        </span>
                    </div>
                    
                    <div class="text-sm text-gray-500">
                        <p>
                            <strong>Last scan:</strong> <?= $channel['last_scan'] ? formatDate($channel['last_scan']) : 'Never' ?>
                        </p>
                        <p>
                            <strong>Next scan:</strong> <?= $channel['next_scan'] ? formatDate($channel['next_scan']) : 'N/A' ?>
                        </p>
                    </div>
                    
                    <div class="flex mt-4 justify-between">
                        <a href="<?= url('/channel/view/' . $channel['id']) ?>" class="text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fas fa-eye mr-1"></i> Chi tiết
                        </a>
                        <a href="<?= url('/channel/scan/' . $channel['id']) ?>" class="text-green-600 hover:text-green-800 text-sm">
                            <i class="fas fa-sync-alt mr-1"></i> Quét ngay
                        </a>
                        <a href="#" onclick="confirmDelete(<?= $channel['id'] ?>); return false;" class="text-red-600 hover:text-red-800 text-sm">
                            <i class="fas fa-trash-alt mr-1"></i> Xóa
                        </a>
                        <form id="delete-form-<?= $channel['id'] ?>" action="<?= url('/channel/delete/' . $channel['id']) ?>" method="POST" class="hidden">
                            <!-- This form will be submitted via JavaScript -->
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($channels['last_page'] > 1): ?>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Hiển thị <?= count($channels['data']) ?> / <?= $channels['total'] ?> kênh
                </div>
                
                <div class="flex space-x-2">
                    <?php if ($channels['current_page'] > 1): ?>
                        <a href="<?= url('/channel?' . http_build_query(array_merge($_GET, ['page' => $channels['current_page'] - 1]))) ?>" 
                           class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $start = max(1, $channels['current_page'] - 2);
                    $end = min($channels['last_page'], $channels['current_page'] + 2);
                    
                    if ($start > 1) {
                        echo '<a href="' . url('/channel?' . http_build_query(array_merge($_GET, ['page' => 1]))) . '" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">1</a>';
                        if ($start > 2) {
                            echo '<span class="px-3 py-1 text-gray-500">...</span>';
                        }
                    }
                    
                    for ($i = $start; $i <= $end; $i++) {
                        $isActive = $i === $channels['current_page'];
                        echo '<a href="' . url('/channel?' . http_build_query(array_merge($_GET, ['page' => $i]))) . '" class="px-3 py-1 rounded border ' . 
                            ($isActive ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50') . 
                            '">' . $i . '</a>';
                    }
                    
                    if ($end < $channels['last_page']) {
                        if ($end < $channels['last_page'] - 1) {
                            echo '<span class="px-3 py-1 text-gray-500">...</span>';
                        }
                        echo '<a href="' . url('/channel?' . http_build_query(array_merge($_GET, ['page' => $channels['last_page']]))) . '" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50">' . $channels['last_page'] . '</a>';
                    }
                    ?>
                    
                    <?php if ($channels['current_page'] < $channels['last_page']): ?>
                        <a href="<?= url('/channel?' . http_build_query(array_merge($_GET, ['page' => $channels['current_page'] + 1]))) ?>" 
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
    function confirmDelete(channelId) {
        if (confirm('Bạn có chắc chắn muốn xóa kênh này? Hành động này sẽ xóa tất cả video thuộc kênh và không thể hoàn tác.')) {
            document.getElementById('delete-form-' + channelId).submit();
        }
    }
</script>
