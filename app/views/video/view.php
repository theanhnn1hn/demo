<?php
// Set JavaScript file for this page
$js_file = 'video';
?>

<div class="container mx-auto" id="videoContainer">
    <input type="hidden" id="videoId" value="<?= $video['id'] ?>">
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-col md:flex-row md:items-start mb-6">
            <!-- Video Thumbnail -->
            <div class="md:w-1/3 mb-4 md:mb-0 md:mr-6">
                <div class="aspect-w-16 aspect-h-9 relative">
                    <img src="<?= $video['thumbnail_url'] ?>" alt="<?= $video['title'] ?>" class="object-cover rounded-lg w-full">
                    <a href="https://www.youtube.com/watch?v=<?= $video['youtube_id'] ?>" target="_blank" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 opacity-0 hover:opacity-100 transition-opacity duration-300 rounded-lg">
                        <i class="fab fa-youtube text-red-600 text-5xl"></i>
                    </a>
                </div>
                
                <!-- Channel Info -->
                <?php if ($channel): ?>
                <div class="mt-4 flex items-center">
                    <img src="<?= $channel['avatar_url'] ?>" alt="<?= $channel['channel_name'] ?>" class="w-10 h-10 rounded-full mr-3">
                    <div>
                        <a href="<?= url('/channel/view/' . $channel['id']) ?>" class="font-medium text-gray-800 hover:text-blue-600">
                            <?= $channel['channel_name'] ?>
                        </a>
                        <p class="text-xs text-gray-500">
                            <?= number_format($channel['subscriber_count']) ?> subscribers
                        </p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Video Info -->
            <div class="md:w-2/3">
                <div class="flex items-start justify-between">
                    <h1 class="text-2xl font-bold text-gray-800"><?= $video['title'] ?></h1>
                    
                    <div>
                        <span id="statusBadge" data-status="<?= $video['status'] ?>" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                            <?php if ($video['status'] === 'completed'): ?>
                                bg-green-100 text-green-800
                            <?php elseif ($video['status'] === 'processing'): ?>
                                bg-yellow-100 text-yellow-800
                            <?php elseif ($video['status'] === 'error'): ?>
                                bg-red-100 text-red-800
                            <?php else: ?>
                                bg-gray-100 text-gray-800
                            <?php endif; ?>
                        ">
                            <?php if ($video['status'] === 'completed'): ?>
                                <i class="fas fa-check-circle mr-1"></i> Đã hoàn thành
                            <?php elseif ($video['status'] === 'processing'): ?>
                                <i class="fas fa-cog fa-spin mr-1"></i> Đang xử lý
                            <?php elseif ($video['status'] === 'error'): ?>
                                <i class="fas fa-exclamation-circle mr-1"></i> Lỗi
                            <?php else: ?>
                                <i class="fas fa-clock mr-1"></i> Chờ xử lý
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                
                <div class="mt-2 text-sm text-gray-500">
                    <p>
                        <i class="far fa-calendar-alt mr-1"></i> Đăng ngày: <?= formatDate($video['publish_date'], 'd/m/Y') ?>
                        &bull; <i class="far fa-clock mr-1"></i> Thời lượng: <?= formatDuration($video['duration']) ?>
                    </p>
                    <p class="mt-1">
                        <i class="fas fa-plus mr-1"></i> Thêm vào: <?= formatDate($video['created_at']) ?>
                        <?php if ($video['processing_completed']): ?>
                        &bull; <i class="fas fa-check mr-1"></i> Hoàn thành: <?= formatDate($video['processing_completed']) ?>
                        <?php endif; ?>
                    </p>
                </div>
                
                <div class="mt-4">
                    <h3 class="font-medium text-gray-800 mb-2">Mô tả</h3>
                    <div class="text-sm text-gray-600 overflow-auto max-h-32 border border-gray-200 rounded-md p-3 bg-gray-50">
                        <?= nl2br(htmlspecialchars($video['description'])) ?>
                    </div>
                </div>
                
                <!-- Video Actions -->
                <div class="mt-6 flex flex-wrap gap-3">
                    <?php if ($video['status'] === 'pending' || $video['status'] === 'error'): ?>
                    <form id="processVideoForm" action="<?= url('/video/process/' . $video['id']) ?>" method="POST">
                        <button type="button" id="processVideoBtn" data-video-id="<?= $video['id'] ?>" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            <i class="fas fa-play mr-1"></i> Xử lý video
                        </button>
                    </form>
                    <?php elseif ($video['status'] === 'processing'): ?>
                    <a href="<?= url('/processing/status/' . $video['id']) ?>" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <i class="fas fa-tasks mr-1"></i> Xem trạng thái xử lý
                    </a>
                    <?php elseif ($video['status'] === 'completed'): ?>
                    <a href="<?= url('/export/preview/' . $video['id']) ?>" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        <i class="fas fa-eye mr-1"></i> Xem trước nội dung
                    </a>
                    <a href="<?= url('/export/' . $video['id']) ?>" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <i class="fas fa-file-export mr-1"></i> Xuất dự án
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($video['status'] === 'processing' || $video['status'] === 'completed'): ?>
                    <form id="resetVideoForm" action="<?= url('/video/reset/' . $video['id']) ?>" method="POST">
                        <button type="button" id="resetVideoBtn" data-video-id="<?= $video['id'] ?>" class="px-4 py-2 border border-yellow-500 text-yellow-500 rounded-md hover:bg-yellow-50">
                            <i class="fas fa-redo mr-1"></i> Đặt lại
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <form id="deleteVideoForm" action="<?= url('/video/delete/' . $video['id']) ?>" method="POST">
                        <button type="button" id="deleteVideoBtn" data-video-id="<?= $video['id'] ?>" class="px-4 py-2 border border-red-500 text-red-500 rounded-md hover:bg-red-50">
                            <i class="fas fa-trash-alt mr-1"></i> Xóa
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Processing Error Message -->
        <?php if ($video['status'] === 'error' && !empty($video['error_message'])): ?>
        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <h3 class="font-medium text-red-800 mb-2">Lỗi xử lý</h3>
            <p class="text-red-700"><?= $video['error_message'] ?></p>
        </div>
        <?php endif; ?>
        
        <!-- Tabs for Content (if video is completed) -->
        <?php if ($video['status'] === 'completed' && isset($rewritten_content)): ?>
        <div class="mt-8">
            <div class="border-b border-gray-200">
                <ul class="flex -mb-px">
                    <li class="mr-1">
                        <button class="video-tab-btn inline-block px-4 py-2 border-b-2 border-blue-500 font-medium text-sm text-blue-600 bg-blue-500 text-white rounded-t-md" data-tab="content-tab">
                            Nội dung viết lại
                        </button>
                    </li>
                    <li class="mr-1">
                        <button class="video-tab-btn inline-block px-4 py-2 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="analysis-tab">
                            Phân tích
                        </button>
                    </li>
                    <?php if (isset($processing)): ?>
                    <li class="mr-1">
                        <button class="video-tab-btn inline-block px-4 py-2 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="processing-tab">
                            Quá trình xử lý
                        </button>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($generated_images)): ?>
                    <li class="mr-1">
                        <button class="video-tab-btn inline-block px-4 py-2 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="images-tab">
                            Hình ảnh
                        </button>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($exported_projects)): ?>
                    <li>
                        <button class="video-tab-btn inline-block px-4 py-2 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="exports-tab">
                            Xuất dự án
                        </button>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Content Tab -->
            <div id="content-tab" class="video-tab-content py-4">
                <div class="space-y-8">
                    <?php if (!empty($rewritten_content['hook'])): ?>
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">Hook</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <?= nl2br(htmlspecialchars($rewritten_content['hook'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($rewritten_content['introduction'])): ?>
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">Giới thiệu</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <?= nl2br(htmlspecialchars($rewritten_content['introduction'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($rewritten_content['main_content'])): ?>
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">Nội dung chính</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <?= nl2br(htmlspecialchars($rewritten_content['main_content'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($rewritten_content['climax'])): ?>
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">Cao trào</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <?= nl2br(htmlspecialchars($rewritten_content['climax'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($rewritten_content['twist'])): ?>
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">Twist</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <?= nl2br(htmlspecialchars($rewritten_content['twist'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($rewritten_content['transition'])): ?>
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">Chuyển tiếp</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <?= nl2br(htmlspecialchars($rewritten_content['transition'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($rewritten_content['controversy'])): ?>
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">Tranh cãi</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <?= nl2br(htmlspecialchars($rewritten_content['controversy'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($rewritten_content['conclusion'])): ?>
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">Kết luận</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <?= nl2br(htmlspecialchars($rewritten_content['conclusion'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($rewritten_content['call_to_action'])): ?>
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">Call to Action</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <?= nl2br(htmlspecialchars($rewritten_content['call_to_action'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <a href="<?= url('/export/preview/' . $video['id']) ?>" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <i class="fas fa-eye mr-1"></i> Xem trước đầy đủ
                    </a>
                </div>
            </div>
            
            <!-- Analysis Tab -->
            <div id="analysis-tab" class="video-tab-content py-4 hidden">
                <?php if (isset($content_analysis) && $content_analysis['analysis_status'] === 'completed'): ?>
                    <?php 
                    // Parse structured content
                    $structuredContent = json_decode($content_analysis['structured_content'], true);
                    ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Main Topic and Theme -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <?php if (isset($structuredContent['main_topic'])): ?>
                            <div class="mb-4">
                                <h4 class="font-medium text-gray-800">Chủ đề chính:</h4>
                                <p><?= $structuredContent['main_topic'] ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($structuredContent['theme'])): ?>
                            <div class="mb-4">
                                <h4 class="font-medium text-gray-800">Chủ đề nền:</h4>
                                <p><?= $structuredContent['theme'] ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($structuredContent['summary'])): ?>
                            <div>
                                <h4 class="font-medium text-gray-800">Tóm tắt:</h4>
                                <p><?= $structuredContent['summary'] ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Audience and Style -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <?php if (isset($structuredContent['audience'])): ?>
                            <div class="mb-4">
                                <h4 class="font-medium text-gray-800">Đối tượng khán giả:</h4>
                                <p>Mục tiêu: <?= $structuredContent['audience']['target'] ?? 'Không xác định' ?></p>
                                <p>Trình độ kỹ thuật: <?= $structuredContent['audience']['technical_level'] ?? 'Không xác định' ?></p>
                                <?php if (isset($structuredContent['audience']['prerequisites'])): ?>
                                <p>Kiến thức cần thiết: <?= $structuredContent['audience']['prerequisites'] ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($structuredContent['style'])): ?>
                            <div>
                                <h4 class="font-medium text-gray-800">Phong cách trình bày:</h4>
                                <p>Tông giọng: <?= $structuredContent['style']['tone'] ?? 'Không xác định' ?></p>
                                <p>Cách trình bày: <?= $structuredContent['style']['presentation'] ?? 'Không xác định' ?></p>
                                <p>Nhịp độ: <?= $structuredContent['style']['pacing'] ?? 'Không xác định' ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Key Points -->
                    <?php if (isset($structuredContent['key_points']) && !empty($structuredContent['key_points'])): ?>
                    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-2">Các điểm chính:</h4>
                        <ul class="list-disc list-inside space-y-2">
                            <?php foreach ($structuredContent['key_points'] as $point): ?>
                                <li>
                                    <span class="font-medium"><?= $point['point'] ?></span>
                                    <?php if (isset($point['timestamp_approx'])): ?>
                                    <span class="text-sm text-gray-500">(<?= $point['timestamp_approx'] ?>)</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Structure -->
                    <?php if (isset($structuredContent['structure'])): ?>
                    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-2">Cấu trúc nội dung:</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <?php if (isset($structuredContent['structure']['beginning'])): ?>
                            <div>
                                <h5 class="font-medium text-gray-700">Mở đầu:</h5>
                                <p><?= $structuredContent['structure']['beginning'] ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($structuredContent['structure']['middle'])): ?>
                            <div>
                                <h5 class="font-medium text-gray-700">Thân bài:</h5>
                                <p><?= $structuredContent['structure']['middle'] ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($structuredContent['structure']['end'])): ?>
                            <div>
                                <h5 class="font-medium text-gray-700">Kết thúc:</h5>
                                <p><?= $structuredContent['structure']['end'] ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Assessment -->
                    <?php if (isset($structuredContent['content_assessment'])): ?>
                    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-2">Đánh giá nội dung:</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <?php if (isset($structuredContent['content_assessment']['strengths']) && !empty($structuredContent['content_assessment']['strengths'])): ?>
                            <div>
                                <h5 class="font-medium text-gray-700">Điểm mạnh:</h5>
                                <ul class="list-disc list-inside">
                                    <?php foreach ($structuredContent['content_assessment']['strengths'] as $strength): ?>
                                        <li><?= $strength ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($structuredContent['content_assessment']['weaknesses']) && !empty($structuredContent['content_assessment']['weaknesses'])): ?>
                            <div>
                                <h5 class="font-medium text-gray-700">Điểm yếu:</h5>
                                <ul class="list-disc list-inside">
                                    <?php foreach ($structuredContent['content_assessment']['weaknesses'] as $weakness): ?>
                                        <li><?= $weakness ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($structuredContent['content_assessment']['improvement_areas']) && !empty($structuredContent['content_assessment']['improvement_areas'])): ?>
                            <div>
                                <h5 class="font-medium text-gray-700">Cơ hội cải thiện:</h5>
                                <ul class="list-disc list-inside">
                                    <?php foreach ($structuredContent['content_assessment']['improvement_areas'] as $area): ?>
                                        <li><?= $area ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Notable Quotes -->
                    <?php if (isset($structuredContent['notable_quotes']) && !empty($structuredContent['notable_quotes'])): ?>
                    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-2">Trích dẫn đáng chú ý:</h4>
                        
                        <div class="space-y-3">
                            <?php foreach ($structuredContent['notable_quotes'] as $quote): ?>
                                <div class="border-l-4 border-blue-500 pl-4 py-1">
                                    <p class="italic">"<?= $quote['quote'] ?>"</p>
                                    <?php if (isset($quote['context'])): ?>
                                    <p class="text-sm text-gray-500 mt-1">Ngữ cảnh: <?= $quote['context'] ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Keywords -->
                    <?php if (isset($structuredContent['keywords']) && !empty($structuredContent['keywords'])): ?>
                    <div class="mt-6">
                        <h4 class="font-medium text-gray-800 mb-2">Từ khóa:</h4>
                        
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($structuredContent['keywords'] as $keyword): ?>
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm"><?= $keyword ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-gray-500 italic text-center py-8">Chưa có dữ liệu phân tích.</p>
                <?php endif; ?>
            </div>
            
            <!-- Processing Tab -->
            <?php if (isset($processing)): ?>
            <div id="processing-tab" class="video-tab-content py-4 hidden">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-medium text-gray-800 mb-4">Thông tin xử lý</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">Bắt đầu:</span> <?= formatDate($processing['started_at']) ?>
                            </p>
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">Hoàn thành:</span> <?= $processing['completed_at'] ? formatDate($processing['completed_at']) : 'N/A' ?>
                            </p>
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">Trạng thái:</span> 
                                <?php if ($processing['processing_status'] === 'completed'): ?>
                                    <span class="text-green-600">Hoàn thành</span>
                                <?php elseif ($processing['processing_status'] === 'error'): ?>
                                    <span class="text-red-600">Lỗi</span>
                                <?php elseif ($processing['processing_status'] === 'processing'): ?>
                                    <span class="text-yellow-600">Đang xử lý</span>
                                <?php else: ?>
                                    <span class="text-gray-600">Đang chuẩn bị</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">Giai đoạn:</span> 
                                <?php 
                                $stages = [
                                    'initiated' => 'Khởi tạo',
                                    'downloading' => 'Tải video',
                                    'speech_to_text' => 'Chuyển đổi giọng nói thành văn bản',
                                    'content_analysis' => 'Phân tích nội dung',
                                    'rewriting' => 'Viết lại nội dung',
                                    'generating_images' => 'Tạo hình ảnh',
                                    'completed' => 'Hoàn thành'
                                ];
                                echo $stages[$processing['processing_stage']] ?? $processing['processing_stage'];
                                ?>
                            </p>
                            
                            <?php if (!empty($processing['local_video_path'])): ?>
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">Video đã tải:</span> Có
                            </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($processing['subtitle_path'])): ?>
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">Phụ đề đã trích xuất:</span> Có
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($processing['processing_settings'])): ?>
                <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-medium text-gray-800 mb-4">Cài đặt xử lý</h3>
                    
                    <?php
                    $settings = json_decode($processing['processing_settings'], true);
                    
                    // API settings
                    $apiSettings = [
                        'speech_to_text_api' => [
                            'label' => 'API trích xuất subtitle',
                            'values' => [
                                'assembly_ai' => 'AssemblyAI',
                                'rev_ai' => 'Rev.ai',
                                'whisper' => 'OpenAI Whisper'
                            ]
                        ],
                        'content_analysis_api' => [
                            'label' => 'API phân tích nội dung',
                            'values' => [
                                'claude' => 'Claude',
                                'gpt4' => 'GPT-4',
                                'gpt35' => 'GPT-3.5 Turbo'
                            ]
                        ],
                        'processing_language' => [
                            'label' => 'Ngôn ngữ xử lý',
                            'values' => [
                                'vi' => 'Tiếng Việt',
                                'en' => 'English',
                                'auto' => 'Auto Detect'
                            ]
                        ],
                        'content_tone' => [
                            'label' => 'Tông giọng điệu',
                            'values' => [
                                'informative' => 'Thông tin - Giáo dục',
                                'humorous' => 'Hài hước - Giải trí',
                                'dramatic' => 'Kịch tính - Nghiêm túc',
                                'persuasive' => 'Thuyết phục - Quảng cáo',
                                'emotional' => 'Cảm xúc - Truyền cảm hứng'
                            ]
                        ],
                        'image_api' => [
                            'label' => 'API tạo ảnh',
                            'values' => [
                                'dall_e' => 'DALL-E 3',
                                'midjourney' => 'Midjourney API',
                                'stable_diffusion' => 'Stable Diffusion'
                            ]
                        ],
                        'image_style' => [
                            'label' => 'Phong cách ảnh',
                            'values' => [
                                'realistic' => 'Realistic Photo',
                                'cartoon' => 'Cartoon/Animation',
                                'render3d' => '3D Render',
                                'artistic' => 'Artistic Painting',
                                'cinematic' => 'Cinematic Scene',
                                'anime' => 'Anime/Manga'
                            ]
                        ]
                    ];
                    ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- API Settings -->
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Cài đặt API</h4>
                            
                            <dl class="space-y-2">
                                <?php foreach ($apiSettings as $key => $setting): ?>
                                    <?php if (isset($settings[$key])): ?>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500"><?= $setting['label'] ?>:</dt>
                                        <dd class="text-sm font-medium"><?= $setting['values'][$settings[$key]] ?? $settings[$key] ?></dd>
                                    </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </dl>
                        </div>
                        
                        <!-- Rewrite Settings -->
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Cài đặt viết lại</h4>
                            
                            <dl class="space-y-2">
                                <?php if (isset($settings['rewrite_level'])): ?>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Mức độ viết lại:</dt>
                                    <dd class="text-sm font-medium">
                                        <?php
                                        $rewriteLevels = [
                                            'light' => 'Nhẹ',
                                            'moderate' => 'Vừa phải',
                                            'complete' => 'Hoàn toàn'
                                        ];
                                        echo $rewriteLevels[$settings['rewrite_level']] ?? $settings['rewrite_level'];
                                        ?>
                                    </dd>
                                </div>
                                <?php endif; ?>
                                
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Thay đổi tên:</dt>
                                    <dd class="text-sm font-medium"><?= isset($settings['change_names']) && $settings['change_names'] ? 'Có' : 'Không' ?></dd>
                                </div>
                                
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Thay đổi địa điểm:</dt>
                                    <dd class="text-sm font-medium"><?= isset($settings['change_locations']) && $settings['change_locations'] ? 'Có' : 'Không' ?></dd>
                                </div>
                                
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Thay đổi ví dụ:</dt>
                                    <dd class="text-sm font-medium"><?= isset($settings['change_examples']) && $settings['change_examples'] ? 'Có' : 'Không' ?></dd>
                                </div>
                                
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Thêm chi tiết mới:</dt>
                                    <dd class="text-sm font-medium"><?= isset($settings['add_details']) && $settings['add_details'] ? 'Có' : 'Không' ?></dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="mt-4 flex justify-center">
                    <a href="<?= url('/processing/status/' . $video['id']) ?>" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <i class="fas fa-eye mr-1"></i> Xem chi tiết quá trình xử lý
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Images Tab -->
            <?php if (!empty($generated_images)): ?>
            <div id="images-tab" class="video-tab-content py-4 hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($generated_images as $image): ?>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-2">
                            <?php
                            $sectionTitles = [
                                'hook' => 'Hook',
                                'introduction' => 'Giới thiệu',
                                'main_content' => 'Nội dung chính',
                                'climax' => 'Cao trào',
                                'twist' => 'Twist',
                                'transition' => 'Chuyển tiếp',
                                'controversy' => 'Tranh cãi',
                                'conclusion' => 'Kết luận',
                                'call_to_action' => 'Call to Action'
                            ];
                            echo $sectionTitles[$image['content_section_type']] ?? $image['content_section_type'];
                            ?>
                        </h4>
                        
                        <div class="mb-3 text-center">
                            <img src="<?= $image['image_path'] ?>" alt="<?= $image['content_section_type'] ?> Illustration" class="inline-block max-h-48 rounded">
                        </div>
                        
                        <div class="text-xs text-gray-500">
                            <p><span class="font-medium">API:</span> <?= $image['api_used'] ?></p>
                            <p><span class="font-medium">Phong cách:</span> <?= $image['style'] ?></p>
                            <p><span class="font-medium">Kích thước:</span> <?= $image['width'] ?>x<?= $image['height'] ?></p>
                        </div>
                        
                        <?php if (!empty($image['image_prompt'])): ?>
                        <div class="mt-2">
                            <p class="text-xs font-medium text-gray-700">Prompt:</p>
                            <p class="text-xs text-gray-600 bg-gray-100 p-2 rounded-md mt-1 overflow-auto max-h-20">
                                <?= $image['image_prompt'] ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Exports Tab -->
            <?php if (!empty($exported_projects)): ?>
            <div id="exports-tab" class="video-tab-content py-4 hidden">
                <div class="bg-white border border-gray-200 rounded-md overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thời gian
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Định dạng
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bao gồm
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hành động
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($exported_projects as $export): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= formatDate($export['exported_at']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= strtoupper($export['export_format']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php 
                                    $includes = [];
                                    if ($export['include_subtitles']) $includes[] = 'Phụ đề';
                                    if ($export['include_images']) $includes[] = 'Hình ảnh';
                                    if ($export['include_prompts']) $includes[] = 'Prompts';
                                    echo !empty($includes) ? implode(', ', $includes) : 'Chỉ nội dung';
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="<?= url('/export/download/' . $export['id']) ?>" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-download"></i> Tải xuống
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 flex justify-center">
                    <a href="<?= url('/export/' . $video['id']) ?>" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        <i class="fas fa-file-export mr-1"></i> Xuất mới
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
