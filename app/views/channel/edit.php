<div class="container mx-auto">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Chỉnh sửa kênh YouTube</h2>
            <a href="<?= url('/channel/view/' . $channel['id']) ?>" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-1"></i> Quay lại chi tiết kênh
            </a>
        </div>
        
        <form action="<?= url('/channel/edit/' . $channel['id']) ?>" method="POST">
            <div class="mb-6 flex items-start">
                <div class="mr-4">
                    <?php if (!empty($channel['avatar_url'])): ?>
                    <img src="<?= $channel['avatar_url'] ?>" alt="<?= $channel['channel_name'] ?>" class="w-24 h-24 rounded-full">
                    <?php else: ?>
                    <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-user text-gray-400 text-4xl"></i>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="flex-1">
                    <h3 class="font-medium text-gray-800 mb-1"><?= $channel['channel_name'] ?></h3>
                    <p class="text-sm text-gray-500 mb-2"><?= number_format($channel['subscriber_count']) ?> subscribers</p>
                    <p class="text-sm text-gray-500">
                        Channel ID: <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded"><?= $channel['channel_id'] ?></span>
                    </p>
                </div>
            </div>
            
            <div class="mb-6">
                <label for="scan_frequency" class="block text-sm font-medium text-gray-700 mb-1">Tần suất quét</label>
                <select id="scan_frequency" name="scan_frequency" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php foreach ($scan_frequencies as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $channel['scan_frequency'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1 text-sm text-gray-500">Tần suất hệ thống sẽ quét kênh này để tìm video mới.</p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <h3 class="font-medium text-gray-800 mb-2 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-blue-500"></i> Thông tin quét
                </h3>
                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Quét gần nhất:</dt>
                        <dd class="text-sm font-medium"><?= $channel['last_scan'] ? formatDate($channel['last_scan']) : 'Chưa quét' ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Quét tiếp theo:</dt>
                        <dd class="text-sm font-medium"><?= $channel['next_scan'] ? formatDate($channel['next_scan']) : 'Không có lịch' ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Số video đã xử lý:</dt>
                        <dd class="text-sm font-medium"><?= $channel['processed_count'] ?> / <?= $channel['video_count'] ?></dd>
                    </div>
                </dl>
                
                <div class="mt-4">
                    <a href="<?= url('/channel/scan/' . $channel['id']) ?>" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        <i class="fas fa-sync-alt mr-1"></i> Quét ngay
                    </a>
                    <p class="mt-2 text-xs text-gray-500">Quét kênh ngay lập tức để tìm video mới.</p>
                </div>
            </div>
            
            <div class="flex justify-between">
                <button type="button" onclick="confirmDelete(<?= $channel['id'] ?>)" class="px-4 py-2 border border-red-500 text-red-500 rounded-md hover:bg-red-50">
                    <i class="fas fa-trash-alt mr-1"></i> Xóa kênh
                </button>
                
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-save mr-1"></i> Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function confirmDelete(channelId) {
        if (confirm('Bạn có chắc chắn muốn xóa kênh này? Hành động này sẽ xóa tất cả video thuộc kênh và không thể hoàn tác.')) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('/channel/delete/') ?>' + channelId;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
