<div class="container mx-auto">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Thêm kênh YouTube mới</h2>
            <a href="<?= url('/channel') ?>" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-1"></i> Quay lại danh sách
            </a>
        </div>
        
        <form action="<?= url('/channel/add') ?>" method="POST">
            <div class="mb-6">
                <label for="channel_url" class="block text-sm font-medium text-gray-700 mb-1">URL kênh YouTube</label>
                <input type="text" id="channel_url" name="channel_url" placeholder="Ví dụ: https://www.youtube.com/c/example hoặc https://www.youtube.com/@example" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <p class="mt-1 text-sm text-gray-500">Nhập URL kênh YouTube bạn muốn thêm để theo dõi và xử lý video.</p>
                <p class="mt-1 text-sm text-gray-500">Hỗ trợ các định dạng: /channel/ID, /c/custom-name, /user/username, và /@handle.</p>
            </div>
            
            <div class="mb-6">
                <label for="scan_frequency" class="block text-sm font-medium text-gray-700 mb-1">Tần suất quét</label>
                <select id="scan_frequency" name="scan_frequency" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php foreach ($scan_frequencies as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $value === 'daily' ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1 text-sm text-gray-500">Chọn tần suất quét kênh để tìm video mới.</p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <h3 class="font-medium text-gray-800 mb-2 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-blue-500"></i> Thông tin bổ sung
                </h3>
                <p class="text-sm text-gray-600">
                    Sau khi thêm kênh, hệ thống sẽ tự động quét để tìm video mới theo tần suất được cài đặt.
                </p>
                <p class="text-sm text-gray-600 mt-2">
                    Video mới được phát hiện sẽ được thêm vào danh sách chờ xử lý.
                </p>
            </div>
            
            <!-- Channel Preview (shown when URL is input) -->
            <div id="channelPreview" class="border border-gray-200 rounded-lg p-4 mb-6 hidden">
                <h3 class="font-medium text-gray-800 mb-3">Xem trước kênh</h3>
                <div class="flex flex-col md:flex-row md:items-start">
                    <div id="avatarContainer" class="w-24 h-24 mb-4 md:mb-0 md:mr-4 flex items-center justify-center bg-gray-100 rounded-full">
                        <i class="fas fa-user text-gray-300 text-4xl"></i>
                    </div>
                    <div class="flex-1">
                        <h4 id="channelTitle" class="font-medium text-gray-900 mb-1"></h4>
                        <p id="channelSubscribers" class="text-sm text-gray-600 mb-2"></p>
                        <p id="channelVideos" class="text-sm text-gray-500 mb-3"></p>
                        <div id="channelDescription" class="text-sm text-gray-600 overflow-y-auto max-h-24 bg-gray-50 p-2 rounded"></div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="button" id="fetchChannelButton" class="px-4 py-2 mr-3 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                    <i class="fas fa-search mr-1"></i> Xem trước kênh
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-plus mr-1"></i> Thêm kênh
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const channelUrlInput = document.getElementById('channel_url');
        const fetchChannelButton = document.getElementById('fetchChannelButton');
        const channelPreview = document.getElementById('channelPreview');
        const avatarContainer = document.getElementById('avatarContainer');
        const channelTitle = document.getElementById('channelTitle');
        const channelSubscribers = document.getElementById('channelSubscribers');
        const channelVideos = document.getElementById('channelVideos');
        const channelDescription = document.getElementById('channelDescription');
        
        // Handle fetch channel button click
        fetchChannelButton.addEventListener('click', function() {
            const channelUrl = channelUrlInput.value.trim();
            
            if (!channelUrl) {
                alert('Vui lòng nhập URL kênh YouTube.');
                return;
            }
            
            // Show loading state
            fetchChannelButton.disabled = true;
            fetchChannelButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Đang tải...';
            
            // Fetch channel info from server (this would need a dedicated endpoint)
            fetch('/api/fetch-channel-info?url=' + encodeURIComponent(channelUrl))
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    
                    // Update preview
                    if (data.avatar_url) {
                        avatarContainer.innerHTML = `<img src="${data.avatar_url}" alt="${data.channel_name}" class="w-24 h-24 rounded-full">`;
                    }
                    
                    channelTitle.textContent = data.channel_name;
                    channelSubscribers.textContent = `${numberFormat(data.subscriber_count)} subscribers`;
                    channelVideos.textContent = `${numberFormat(data.video_count)} videos`;
                    channelDescription.textContent = data.description || 'No description available.';
                    
                    // Show preview
                    channelPreview.classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi tải thông tin kênh. Vui lòng thử lại.');
                })
                .finally(() => {
                    // Reset button state
                    fetchChannelButton.disabled = false;
                    fetchChannelButton.innerHTML = '<i class="fas fa-search mr-1"></i> Xem trước kênh';
                });
        });
        
        // Helper function to format numbers
        function numberFormat(num) {
            return new Intl.NumberFormat().format(num);
        }
    });
</script>
