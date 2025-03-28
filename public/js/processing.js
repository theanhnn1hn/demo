/**
 * YouTube Processor - Processing JavaScript
 * 
 * This script handles the video processing flow and status updates.
 */

// Wait for document to fully load
document.addEventListener('DOMContentLoaded', function() {
    const processingContainer = document.getElementById('processingContainer');
    
    if (!processingContainer) return;
    
    // Initialize processing
    initProcessing();
});

/**
 * Initialize processing functionality
 */
function initProcessing() {
    const videoId = document.getElementById('videoId')?.value;
    const statusContainer = document.getElementById('processingStatus');
    const progressBar = document.getElementById('progressBar');
    const statusText = document.getElementById('statusText');
    const startButton = document.getElementById('startProcessingBtn');
    const stagesContainer = document.getElementById('processingStages');
    
    if (!videoId || !statusContainer) return;
    
    // Handle start processing button
    if (startButton) {
        startButton.addEventListener('click', function() {
            // Disable button
            startButton.disabled = true;
            startButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang bắt đầu...';
            
            // Start processing
            startProcessing(videoId)
                .then(response => {
                    if (response.status === 'success') {
                        // Show status container
                        statusContainer.classList.remove('hidden');
                        
                        // Update UI
                        updateProcessingUI(response.processing_stage, response.processing_status);
                        
                        // Start polling for status
                        pollProcessingStatus(videoId);
                    } else {
                        // Show error
                        showToast(response.message || 'Có lỗi xảy ra khi bắt đầu xử lý.', 'error');
                        
                        // Re-enable button
                        startButton.disabled = false;
                        startButton.innerHTML = 'Bắt đầu xử lý';
                    }
                })
                .catch(error => {
                    console.error('Error starting processing:', error);
                    showToast('Có lỗi xảy ra khi bắt đầu xử lý.', 'error');
                    
                    // Re-enable button
                    startButton.disabled = false;
                    startButton.innerHTML = 'Bắt đầu xử lý';
                });
        });
    }
    
    // Check if we should start polling immediately
    const currentStage = document.getElementById('currentStage')?.value;
    const currentStatus = document.getElementById('currentStatus')?.value;
    
    if (currentStage && currentStatus && currentStage !== 'completed') {
        // Show status container
        statusContainer.classList.remove('hidden');
        
        // Update UI
        updateProcessingUI(currentStage, currentStatus);
        
        // Start polling
        pollProcessingStatus(videoId);
    }
}

/**
 * Start processing a video
 * 
 * @param {string} videoId Video ID
 * @returns {Promise} Promise resolving to response
 */
function startProcessing(videoId) {
    return ajax(`/processing/startProcessing/${videoId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    });
}

/**
 * Poll for processing status
 * 
 * @param {string} videoId Video ID
 */
function pollProcessingStatus(videoId) {
    // Set polling interval
    const pollingInterval = 3000; // 3 seconds
    
    // Process one step at a time
    processStep(videoId)
        .then(response => {
            // Update UI
            updateProcessingUI(response.processing_stage, response.processing_status);
            
            // Check if processing is complete or has error
            if (response.completed || response.processing_status === 'error') {
                if (response.status === 'error') {
                    // Show error
                    showToast(response.message || 'Có lỗi xảy ra trong quá trình xử lý.', 'error');
                    
                    // Redirect to video page after delay
                    setTimeout(() => {
                        window.location.href = `/video/view/${videoId}`;
                    }, 3000);
                } else {
                    // Show success
                    showToast('Xử lý hoàn tất!', 'success');
                    
                    // Redirect to video page after delay
                    setTimeout(() => {
                        window.location.href = `/video/view/${videoId}`;
                    }, 2000);
                }
            } else {
                // Continue polling
                setTimeout(() => {
                    pollProcessingStatus(videoId);
                }, pollingInterval);
            }
        })
        .catch(error => {
            console.error('Error polling processing status:', error);
            showToast('Có lỗi xảy ra khi kiểm tra trạng thái xử lý.', 'error');
            
            // Retry after delay
            setTimeout(() => {
                pollProcessingStatus(videoId);
            }, pollingInterval * 2);
        });
}

/**
 * Process one step
 * 
 * @param {string} videoId Video ID
 * @returns {Promise} Promise resolving to response
 */
function processStep(videoId) {
    return ajax(`/processing/processStep/${videoId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    });
}

/**
 * Update processing UI
 * 
 * @param {string} stage Processing stage
 * @param {string} status Processing status
 */
function updateProcessingUI(stage, status) {
    const progressBar = document.getElementById('progressBar');
    const statusText = document.getElementById('statusText');
    const stagesContainer = document.getElementById('processingStages');
    
    if (!progressBar || !statusText || !stagesContainer) return;
    
    // Update status text
    let statusDisplay = 'Đang xử lý';
    if (status === 'completed') {
        statusDisplay = 'Hoàn thành';
    } else if (status === 'error') {
        statusDisplay = 'Lỗi';
    } else if (status === 'pending') {
        statusDisplay = 'Đang chuẩn bị';
    }
    
    statusText.textContent = `Trạng thái: ${statusDisplay}`;
    
    // Calculate progress based on stage
    let progress = 0;
    
    switch (stage) {
        case 'initiated':
            progress = 5;
            break;
        
        case 'downloading':
            progress = status === 'completed' ? 20 : 10;
            break;
        
        case 'speech_to_text':
            progress = status === 'completed' ? 40 : 30;
            break;
        
        case 'content_analysis':
            progress = status === 'completed' ? 60 : 50;
            break;
        
        case 'rewriting':
            progress = status === 'completed' ? 80 : 70;
            break;
        
        case 'generating_images':
            progress = status === 'completed' ? 95 : 85;
            break;
        
        case 'completed':
            progress = 100;
            break;
        
        default:
            progress = 0;
            break;
    }
    
    // Update progress bar
    progressBar.style.width = `${progress}%`;
    
    // Update stages
    const stages = stagesContainer.querySelectorAll('.processing-stage');
    
    stages.forEach(stageElement => {
        const stageName = stageElement.getAttribute('data-stage');
        
        // Reset all stages
        stageElement.classList.remove('text-blue-600', 'text-green-600', 'text-red-600');
        stageElement.querySelector('.stage-icon').classList.remove('bg-blue-100', 'text-blue-600', 'bg-green-100', 'text-green-600', 'bg-red-100', 'text-red-600');
        
        // Update current and completed stages
        if (stageName === stage) {
            if (status === 'completed') {
                stageElement.classList.add('text-green-600');
                stageElement.querySelector('.stage-icon').classList.add('bg-green-100', 'text-green-600');
            } else if (status === 'error') {
                stageElement.classList.add('text-red-600');
                stageElement.querySelector('.stage-icon').classList.add('bg-red-100', 'text-red-600');
            } else {
                stageElement.classList.add('text-blue-600');
                stageElement.querySelector('.stage-icon').classList.add('bg-blue-100', 'text-blue-600');
            }
        } else if ((stageName === 'initiated' && stage !== 'initiated') ||
                  (stageName === 'downloading' && ['speech_to_text', 'content_analysis', 'rewriting', 'generating_images', 'completed'].includes(stage)) ||
                  (stageName === 'speech_to_text' && ['content_analysis', 'rewriting', 'generating_images', 'completed'].includes(stage)) ||
                  (stageName === 'content_analysis' && ['rewriting', 'generating_images', 'completed'].includes(stage)) ||
                  (stageName === 'rewriting' && ['generating_images', 'completed'].includes(stage)) ||
                  (stageName === 'generating_images' && stage === 'completed')) {
            stageElement.classList.add('text-green-600');
            stageElement.querySelector('.stage-icon').classList.add('bg-green-100', 'text-green-600');
        }
    });
}
