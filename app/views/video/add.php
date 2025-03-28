<div class="container mx-auto">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Thêm video mới</h2>
            <a href="<?= url('/video') ?>" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-1"></i> Quay lại danh sách
            </a>
        </div>
        
        <form action="<?= url('/video/add') ?>" method="POST">
            <div class="mb-6">
                <label for="video_url" class="block text-sm font-medium text-gray-700 mb-1">URL video YouTube</label>
                <input type="text" id="video_url" name="video_url" placeholder="Ví dụ: https://www.youtube.com/watch?v=dQw4w9WgXcQ" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <p class="mt-1 text-sm text-gray-500">Nhập URL của video YouTube muốn xử lý. Hỗ trợ tất cả các định dạng URL YouTube.</p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <h3 class="font-medium text-gray-800 mb-2 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-blue-500"></i> Thông tin bổ sung
                </h3>
                <p class="text-sm text-gray-600">
                    Video sẽ được tải xuống và xử lý qua các bước: trích xuất phụ đề, phân tích nội dung, viết lại nội dung, và tạo hình ảnh minh họa.
                </p>
                <p class="text-sm text-gray-600 mt-2">
                    Bạn có thể tùy chỉnh quá trình xử lý trong bước tiếp theo sau khi thêm video.
                </p>
            </div>
            
            <!-- Video Preview (shown when URL is input) -->
            <div id="videoPreview" class="border border-gray-200 rounded-lg p-4 mb-6 hidden">
                <h3 class="font-medium text-gray-800 mb-3">Xem trước video</h3>
                <div class="flex flex-col md:flex-row md:items-start">
                    <div id="thumbnailContainer" class="w-full md:w-64 mb-4 md:mb-0 md:mr-4"></div>
                    <div class="flex-1">
                        <h4 id="videoTitle" class="font-medium text-gray-900 mb-1"></h4>
                        <p id="videoChannel" class="text-sm text-gray-600 mb-2"></p>
                        <p id="videoPublished" class="text-sm text-gray-500"></p>
                        <p id="videoDuration" class="text-sm text-gray-500 mb-3"></p>
                        <div id="videoDescription" class="text-sm text-gray-600 overflow-y-auto max-h-24 bg-gray-50 p-2 rounded"></div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="button" id="fetchVideoButton" class="px-4 py-2 mr-3 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                    <i class="fas fa-search mr-1"></i> Xem trước video
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-plus mr-1"></i> Thêm video
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const videoUrlInput = document.getElementById('video_url');
        const fetchVideoButton = document.getElementById('fetchVideoButton');
        const videoPreview = document.getElementById('videoPreview');
        const thumbnailContainer = document.getElementById('thumbnailContainer');
        const videoTitle = document.getElementById('videoTitle');
        const videoChannel = document.getElementById('videoChannel');
        const videoPublished = document.getElementById('videoPublished');
        const videoDuration = document.getElementById('videoDuration');
        const videoDescription = document.getElementById('videoDescription');
        
        // Handle fetch video button click
        fetchVideoButton.addEventListener('click', function() {
            const videoUrl = videoUrlInput.value.trim();
            
            if (!videoUrl) {
                alert('Vui lòng nhập URL video YouTube.');
                return;
            }
            
            // Show loading state
            fetchVideoButton.disabled = true;
            fetchVideoButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Đang tải...';
            
            // Fetch video info from server (this would need a dedicated endpoint)
            fetch('/api/fetch-video-info?url=' + encodeURIComponent(videoUrl))
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    
                    // Update preview
                    thumbnailContainer.innerHTML = `<img src="${data.thumbnail_url}" alt="${data.title}" class="w-full rounded">`;
                    videoTitle.textContent = data.title;
                    videoChannel.textContent = `Kênh: ${data.channel_name}`;
                    videoPublished.textContent = `Đăng ngày: ${formatDate(data.publish_date)}`;
                    videoDuration.textContent = `Thời lượng: ${formatDuration(data.duration)}`;
                    videoDescription.textContent = data.description;
                    
                    // Show preview
                    videoPreview.classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi tải thông tin video. Vui lòng thử lại.');
                })
                .finally(() => {
                    // Reset button state
                    fetchVideoButton.disabled = false;
                    fetchVideoButton.innerHTML = '<i class="fas fa-search mr-1"></i> Xem trước video';
                });
        });
        
        // Helper functions
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }
        
        function formatDuration(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds / 60) % 60);
            const secs = seconds % 60;
            
            if (hours > 0) {
                return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            } else {
                return `${minutes}:${secs.toString().padStart(2, '0')}`;
            }
        }
        
        // Extract video ID from YouTube URL for preview (simple approach)
        videoUrlInput.addEventListener('input', function() {
            const videoUrl = this.value.trim();
            
            if (!videoUrl) {
                videoPreview.classList.add('hidden');
                return;
            }
            
            // For real-time preview, we could show a YouTube embed
            // This is optional and can be implemented if needed
        });
    });
</script>
