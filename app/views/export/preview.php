<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Xem trước nội dung</h2>
            
            <div class="flex space-x-4">
                <a href="<?= url('/video/view/' . $video['id']) ?>" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left mr-1"></i> Trở về Video
                </a>
                <a href="<?= url('/export/' . $video['id']) ?>" class="text-green-600 hover:text-green-800">
                    <i class="fas fa-file-export mr-1"></i> Xuất dự án
                </a>
            </div>
        </div>
        
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0 mr-4">
                    <img src="<?= $video['thumbnail_url'] ?>" alt="<?= $video['title'] ?>" class="w-32 h-18 object-cover rounded">
                </div>
                <div>
                    <h3 class="text-lg font-medium"><?= $video['title'] ?></h3>
                    <p class="text-sm text-gray-500 mt-1">
                        <a href="https://www.youtube.com/watch?v=<?= $video['youtube_id'] ?>" target="_blank" class="flex items-center">
                            <i class="fab fa-youtube text-red-600 mr-1"></i> Xem trên YouTube
                        </a>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Content Preview -->
        <div class="markdown prose max-w-none">
            <?php if (!empty($rewritten_content['hook'])): ?>
            <div class="mb-8">
                <h2>Hook</h2>
                <div class="mb-4">
                    <?= nl2br(htmlspecialchars($rewritten_content['hook'])) ?>
                </div>
                
                <?php if (isset($images['hook'])): ?>
                <div class="my-4 text-center">
                    <img src="<?= $images['hook']['image_path'] ?>" alt="Hook Illustration" class="inline-block max-h-80">
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($rewritten_content['introduction'])): ?>
            <div class="mb-8">
                <h2>Giới thiệu</h2>
                <div class="mb-4">
                    <?= nl2br(htmlspecialchars($rewritten_content['introduction'])) ?>
                </div>
                
                <?php if (isset($images['introduction'])): ?>
                <div class="my-4 text-center">
                    <img src="<?= $images['introduction']['image_path'] ?>" alt="Introduction Illustration" class="inline-block max-h-80">
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($rewritten_content['main_content'])): ?>
            <div class="mb-8">
                <h2>Nội dung chính</h2>
                <div class="mb-4">
                    <?= nl2br(htmlspecialchars($rewritten_content['main_content'])) ?>
                </div>
                
                <?php if (isset($images['main_content'])): ?>
                <div class="my-4 text-center">
                    <img src="<?= $images['main_content']['image_path'] ?>" alt="Main Content Illustration" class="inline-block max-h-80">
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($rewritten_content['climax'])): ?>
            <div class="mb-8">
                <h2>Cao trào</h2>
                <div class="mb-4">
                    <?= nl2br(htmlspecialchars($rewritten_content['climax'])) ?>
                </div>
                
                <?php if (isset($images['climax'])): ?>
                <div class="my-4 text-center">
                    <img src="<?= $images['climax']['image_path'] ?>" alt="Climax Illustration" class="inline-block max-h-80">
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($rewritten_content['twist'])): ?>
            <div class="mb-8">
                <h2>Twist</h2>
                <div class="mb-4">
                    <?= nl2br(htmlspecialchars($rewritten_content['twist'])) ?>
                </div>
                
                <?php if (isset($images['twist'])): ?>
                <div class="my-4 text-center">
                    <img src="<?= $images['twist']['image_path'] ?>" alt="Twist Illustration" class="inline-block max-h-80">
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($rewritten_content['transition'])): ?>
            <div class="mb-8">
                <h2>Chuyển tiếp</h2>
                <div class="mb-4">
                    <?= nl2br(htmlspecialchars($rewritten_content['transition'])) ?>
                </div>
                
                <?php if (isset($images['transition'])): ?>
                <div class="my-4 text-center">
                    <img src="<?= $images['transition']['image_path'] ?>" alt="Transition Illustration" class="inline-block max-h-80">
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($rewritten_content['controversy'])): ?>
            <div class="mb-8">
                <h2>Tranh cãi</h2>
                <div class="mb-4">
                    <?= nl2br(htmlspecialchars($rewritten_content['controversy'])) ?>
                </div>
                
                <?php if (isset($images['controversy'])): ?>
                <div class="my-4 text-center">
                    <img src="<?= $images['controversy']['image_path'] ?>" alt="Controversy Illustration" class="inline-block max-h-80">
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($rewritten_content['conclusion'])): ?>
            <div class="mb-8">
                <h2>Kết luận</h2>
                <div class="mb-4">
                    <?= nl2br(htmlspecialchars($rewritten_content['conclusion'])) ?>
                </div>
                
                <?php if (isset($images['conclusion'])): ?>
                <div class="my-4 text-center">
                    <img src="<?= $images['conclusion']['image_path'] ?>" alt="Conclusion Illustration" class="inline-block max-h-80">
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($rewritten_content['call_to_action'])): ?>
            <div class="mb-8">
                <h2>Call to Action</h2>
                <div class="mb-4">
                    <?= nl2br(htmlspecialchars($rewritten_content['call_to_action'])) ?>
                </div>
                
                <?php if (isset($images['call_to_action'])): ?>
                <div class="my-4 text-center">
                    <img src="<?= $images['call_to_action']['image_path'] ?>" alt="Call to Action Illustration" class="inline-block max-h-80">
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Actions -->
        <div class="mt-8 flex justify-between">
            <a href="<?= url('/video/view/' . $video['id']) ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                Trở về Video
            </a>
            
            <a href="<?= url('/export/' . $video['id']) ?>" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                <i class="fas fa-file-export mr-1"></i> Xuất dự án
            </a>
        </div>
    </div>
</div>
