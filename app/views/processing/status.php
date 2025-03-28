<?php
// Set JavaScript file for this page
$js_file = 'processing';
?>

<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-800"><?= $video['title'] ?></h2>
            <a href="<?= url('/video/view/' . $video['id']) ?>" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-1"></i> Trở về Video
            </a>
        </div>
        
        <!-- Hidden inputs for JavaScript -->
        <input type="hidden" id="videoId" value="<?= $video['id'] ?>">
        <input type="hidden" id="currentStage" value="<?= $processing['processing_stage'] ?>">
        <input type="hidden" id="currentStatus" value="<?= $processing['processing_status'] ?>">
        
        <!-- Processing Status -->
        <div id="processingStatus" class="mb-6">
            <div class="mb-2 flex justify-between items-center">
                <div id="statusText" class="text-sm font-medium text-gray-700">
                    Trạng thái: 
                    <?php if ($processing['processing_status'] === 'completed'): ?>
                        Hoàn thành
                    <?php elseif ($processing['processing_status'] === 'error'): ?>
                        Lỗi
                    <?php elseif ($processing['processing_status'] === 'processing'): ?>
                        Đang xử lý
                    <?php else: ?>
                        Đang chuẩn bị
                    <?php endif; ?>
                </div>
                <div class="text-sm text-gray-500">
                    Bắt đầu: <?= formatDate($processing['started_at']) ?>
                    <?php if ($processing['completed_at']): ?>
                    | Hoàn thành: <?= formatDate($processing['completed_at']) ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div id="progressBar" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
            </div>
        </div>
        
        <!-- Processing Stages -->
        <div id="processingStages" class="mb-8">
            <h3 class="text-lg font-medium text-gray-800 mb-4">Quy trình xử lý</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Stage 1: Initiated -->
                <div class="processing-stage flex items-start space-x-3" data-stage="initiated">
                    <div class="stage-icon flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-500">
                        <i class="fas fa-play"></i>
                    </div>
                    <div>
                        <p class="font-medium">Bắt đầu xử lý</p>
                        <p class="text-sm text-gray-500">Khởi tạo quá trình xử lý video</p>
                    </div>
                </div>
                
                <!-- Stage 2: Downloading -->
                <div class="processing-stage flex items-start space-x-3" data-stage="downloading">
                    <div class="stage-icon flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-500">
                        <i class="fas fa-download"></i>
                    </div>
                    <div>
                        <p class="font-medium">Tải video</p>
                        <p class="text-sm text-gray-500">Tải video từ YouTube</p>
                    </div>
                </div>
                
                <!-- Stage 3: Speech to Text -->
                <div class="processing-stage flex items-start space-x-3" data-stage="speech_to_text">
                    <div class="stage-icon flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-500">
                        <i class="fas fa-comment-alt"></i>
                    </div>
                    <div>
                        <p class="font-medium">Chuyển đổi giọng nói thành văn bản</p>
                        <p class="text-sm text-gray-500">Trích xuất phụ đề từ video</p>
                    </div>
                </div>
                
                <!-- Stage 4: Content Analysis -->
                <div class="processing-stage flex items-start space-x-3" data-stage="content_analysis">
                    <div class="stage-icon flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-500">
                        <i class="fas fa-search"></i>
                    </div>
                    <div>
                        <p class="font-medium">Phân tích nội dung</p>
                        <p class="text-sm text-gray-500">Phân tích nội dung văn bản bằng AI</p>
                    </div>
                </div>
                
                <!-- Stage 5: Rewriting -->
                <div class="processing-stage flex items-start space-x-3" data-stage="rewriting">
                    <div class="stage-icon flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-500">
                        <i class="fas fa-pen"></i>
                    </div>
                    <div>
                        <p class="font-medium">Viết lại nội dung</p>
                        <p class="text-sm text-gray-500">Viết lại nội dung với cấu trúc mới</p>
                    </div>
                </div>
                
                <!-- Stage 6: Generating Images -->
                <div class="processing-stage flex items-start space-x-3" data-stage="generating_images">
                    <div class="stage-icon flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-500">
                        <i class="fas fa-image"></i>
                    </div>
                    <div>
                        <p class="font-medium">Tạo hình ảnh</p>
                        <p class="text-sm text-gray-500">Tạo hình ảnh minh họa cho từng phần</p>
                    </div>
                </div>
                
                <!-- Stage 7: Completed -->
                <div class="processing-stage flex items-start space-x-3" data-stage="completed">
                    <div class="stage-icon flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-500">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <p class="font-medium">Hoàn thành</p>
                        <p class="text-sm text-gray-500">Xử lý video hoàn tất</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Content Preview (only show if content analysis or later stages are completed) -->
        <?php if (isset($content_analysis) && $content_analysis['analysis_status'] === 'completed'): ?>
        <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-800 mb-4">Phân tích nội dung</h3>
            
            <?php 
            // Parse structured content
            $structuredContent = json_decode($content_analysis['structured_content'], true);
            ?>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <?php if (isset($structuredContent['main_topic'])): ?>
                <div class="mb-4">
                    <p class="font-medium">Chủ đề chính:</p>
                    <p><?= $structuredContent['main_topic'] ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (isset($structuredContent['key_points']) && !empty($structuredContent['key_points'])): ?>
                <div class="mb-4">
                    <p class="font-medium">Các điểm chính:</p>
                    <ul class="list-disc list-inside">
                        <?php foreach ($structuredContent['key_points'] as $point): ?>
                            <li><?= $point['point'] ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if (isset($structuredContent['summary'])): ?>
                <div class="mb-4">
                    <p class="font-medium">Tóm tắt:</p>
                    <p><?= $structuredContent['summary'] ?></p>
                </div>
                <?php endif; ?>
                
                <div class="flex justify-center mt-4">
                    <a href="<?= url('/video/view/' . $video['id']) ?>" class="text-blue-600 hover:text-blue-800">
                        Xem chi tiết đầy đủ
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Rewritten Content Preview (only show if rewriting or later stages are completed) -->
        <?php if (isset($rewritten_content)): ?>
        <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-800 mb-4">Nội dung đã viết lại</h3>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <?php if (!empty($rewritten_content['hook'])): ?>
                <div class="mb-4">
                    <p class="font-medium">Hook:</p>
                    <p><?= nl2br(substr($rewritten_content['hook'], 0, 200)) ?><?= strlen($rewritten_content['hook']) > 200 ? '...' : '' ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($rewritten_content['introduction'])): ?>
                <div class="mb-4">
                    <p class="font-medium">Giới thiệu:</p>
                    <p><?= nl2br(substr($rewritten_content['introduction'], 0, 200)) ?><?= strlen($rewritten_content['introduction']) > 200 ? '...' : '' ?></p>
                </div>
                <?php endif; ?>
                
                <div class="flex justify-center mt-4">
                    <a href="<?= url('/export/preview/' . $video['id']) ?>" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Xem trước toàn bộ
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Error Display -->
        <?php if ($processing['processing_status'] === 'error'): ?>
        <div class="mt-8 bg-red-50 border border-red-200 rounded-lg p-4">
            <h3 class="text-lg font-medium text-red-800 mb-2">Lỗi xử lý</h3>
            <p class="text-red-700"><?= $processing['error_message'] ?></p>
            
            <div class="mt-4">
                <form action="<?= url('/video/reset/' . $video['id']) ?>" method="POST">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Đặt lại trạng thái xử lý
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Actions -->
        <div class="mt-8 flex justify-between">
            <a href="<?= url('/video/view/' . $video['id']) ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                Trở về Video
            </a>
            
            <!-- Show export button if completed -->
            <?php if ($processing['processing_stage'] === 'completed' && $processing['processing_status'] === 'completed'): ?>
            <a href="<?= url('/export/' . $video['id']) ?>" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                Xuất dự án
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
