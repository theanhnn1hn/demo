<div class="container mx-auto">
    <!-- Dashboard Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Channels -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 mr-4">
                    <i class="fas fa-tv text-blue-500 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Tổng số kênh</p>
                    <p class="text-2xl font-semibold"><?= $total_channels ?></p>
                </div>
            </div>
        </div>
        
        <!-- Total Videos -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 mr-4">
                    <i class="fas fa-video text-green-500 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Tổng số video</p>
                    <p class="text-2xl font-semibold"><?= $video_stats['total'] ?></p>
                </div>
            </div>
        </div>
        
        <!-- Completed Videos -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 mr-4">
                    <i class="fas fa-check-circle text-purple-500 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Đã hoàn thành</p>
                    <p class="text-2xl font-semibold"><?= $video_stats['completed'] ?></p>
                </div>
            </div>
        </div>
        
        <!-- Processing Videos -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 mr-4">
                    <i class="fas fa-cogs text-orange-500 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Đang xử lý</p>
                    <p class="text-2xl font-semibold"><?= $video_stats['processing'] ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Videos -->
        <div class="bg-white rounded-lg shadow">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="font-medium text-lg text-gray-800">Video gần đây</h3>
            </div>
            <div class="p-6">
                <?php if (empty($recent_videos)): ?>
                    <p class="text-gray-500 text-center py-4">Chưa có video nào</p>
                <?php else: ?>
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($recent_videos as $video): ?>
                            <div class="py-3 flex items-center">
                                <div class="flex-shrink-0 mr-4">
                                    <img src="<?= $video['thumbnail_url'] ?>" alt="<?= $video['title'] ?>" class="w-16 h-9 object-cover rounded">
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-800">
                                        <a href="<?= url('/video/view/' . $video['id']) ?>" class="hover:text-blue-600">
                                            <?= $video['title'] ?>
                                        </a>
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        <?= formatDate($video['created_at'], 'd/m/Y H:i') ?>
                                        • <?= formatDuration($video['duration']) ?>
                                    </p>
                                </div>
                                <div class="ml-4">
                                    <?php if ($video['status'] === 'completed'): ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Đã hoàn thành</span>
                                    <?php elseif ($video['status'] === 'processing'): ?>
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Đang xử lý</span>
                                    <?php elseif ($video['status'] === 'error'): ?>
                                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Lỗi</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">Chờ xử lý</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-4 text-center">
                        <a href="<?= url('/video') ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Xem tất cả videos <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Popular Channels -->
        <div class="bg-white rounded-lg shadow">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="font-medium text-lg text-gray-800">Kênh phổ biến</h3>
            </div>
            <div class="p-6">
                <?php if (empty($popular_channels)): ?>
                    <p class="text-gray-500 text-center py-4">Chưa có kênh nào</p>
                <?php else: ?>
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($popular_channels as $channel): ?>
                            <div class="py-3 flex items-center">
                                <div class="flex-shrink-0 mr-4">
                                    <img src="<?= $channel['avatar_url'] ?>" alt="<?= $channel['channel_name'] ?>" class="w-10 h-10 object-cover rounded-full">
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-800">
                                        <a href="<?= url('/channel/view/' . $channel['id']) ?>" class="hover:text-blue-600">
                                            <?= $channel['channel_name'] ?>
                                        </a>
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        <?= $channel['processed_count'] ?> video đã được xử lý
                                    </p>
                                </div>
                                <div class="ml-4">
                                    <a href="<?= url('/channel/view/' . $channel['id']) ?>" class="px-3 py-1 bg-blue-600 text-white text-xs rounded-md hover:bg-blue-700">
                                        Chi tiết
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-4 text-center">
                        <a href="<?= url('/channel') ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Xem tất cả kênh <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Scan Activities -->
    <div class="mt-6 bg-white rounded-lg shadow">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="font-medium text-lg text-gray-800">Lịch sử quét gần đây</h3>
        </div>
        <div class="p-6">
            <?php if (empty($recent_scans)): ?>
                <p class="text-gray-500 text-center py-4">Chưa có lịch sử quét nào</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <th class="px-6 py-3">Kênh</th>
                                <th class="px-6 py-3">Thời gian</th>
                                <th class="px-6 py-3">Trạng thái</th>
                                <th class="px-6 py-3">Tìm thấy</th>
                                <th class="px-6 py-3">Đã thêm</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($recent_scans as $scan): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <?php 
                                            // Get channel data - in real app, you'd pass this from controller
                                            $channel = (new App\Models\YoutubeChannelModel())->find($scan['channel_id']);
                                            ?>
                                            <?php if ($channel): ?>
                                                <img class="h-8 w-8 rounded-full mr-2" src="<?= $channel['avatar_url'] ?>" alt="Channel avatar">
                                                <div class="text-sm font-medium text-gray-900"><?= $channel['channel_name'] ?></div>
                                            <?php else: ?>
                                                <div class="text-sm text-gray-500">Kênh không tồn tại</div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= formatDate($scan['start_time']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($scan['status'] === 'completed'): ?>
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Hoàn thành
                                            </span>
                                        <?php elseif ($scan['status'] === 'processing'): ?>
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Đang xử lý
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Lỗi
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= $scan['videos_found'] ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= $scan['videos_added'] ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="mt-6 bg-white rounded-lg shadow p-6">
        <h3 class="font-medium text-lg text-gray-800 mb-4">Thao tác nhanh</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="<?= url('/channel/add') ?>" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-200">
                <div class="p-3 rounded-full bg-blue-100 mr-4">
                    <i class="fas fa-plus text-blue-500"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800">Thêm kênh mới</h4>
                    <p class="text-sm text-gray-500">Thêm kênh YouTube để quét</p>
                </div>
            </a>
            
            <a href="<?= url('/video/add') ?>" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-200">
                <div class="p-3 rounded-full bg-green-100 mr-4">
                    <i class="fas fa-file-video text-green-500"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800">Thêm video</h4>
                    <p class="text-sm text-gray-500">Thêm video để xử lý</p>
                </div>
            </a>
            
            <a href="<?= url('/video?status=pending') ?>" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-200">
                <div class="p-3 rounded-full bg-purple-100 mr-4">
                    <i class="fas fa-tasks text-purple-500"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800">Video chờ xử lý</h4>
                    <p class="text-sm text-gray-500">Xem các video đang chờ</p>
                </div>
            </a>
        </div>
    </div>
</div>
