/**
 * YouTube Processor - Video JavaScript
 * 
 * This script handles video-related functionality.
 */

// Wait for document to fully load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize video functionality
    initVideoPage();
    
    // Initialize batch actions
    initBatchActions();
});

/**
 * Initialize video page functionality
 */
function initVideoPage() {
    const videoContainer = document.getElementById('videoContainer');
    
    if (!videoContainer) return;
    
    // Initialize video status polling if viewing a processing video
    initVideoStatusPolling();
    
    // Initialize tabs if present
    initVideoTabs();
    
    // Initialize action buttons
    initVideoActions();
}

/**
 * Initialize video status polling
 */
function initVideoStatusPolling() {
    const videoId = document.getElementById('videoId')?.value;
    const statusBadge = document.getElementById('statusBadge');
    
    if (!videoId || !statusBadge) return;
    
    // If status is 'processing', start polling
    if (statusBadge.getAttribute('data-status') === 'processing') {
        // Set polling interval
        const pollingInterval = 5000; // 5 seconds
        
        // Poll for status
        function pollStatus() {
            ajax(`/video/status/${videoId}`)
                .then(response => {
                    // Update status badge
                    updateStatusBadge(statusBadge, response.status);
                    
                    // If still processing, continue polling
                    if (response.status === 'processing') {
                        setTimeout(pollStatus, pollingInterval);
                    } else if (response.status === 'completed') {
                        // Reload page after short delay
                        showToast('Xử lý đã hoàn tất! Đang tải lại trang...', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error polling video status:', error);
                    
                    // Retry after delay
                    setTimeout(pollStatus, pollingInterval * 2);
                });
        }
        
        // Start polling
        pollStatus();
    }
}

/**
 * Update status badge
 * 
 * @param {HTMLElement} badge Status badge element
 * @param {string} status New status
 */
function updateStatusBadge(badge, status) {
    // Remove existing classes
    badge.classList.remove('bg-green-100', 'text-green-800', 'bg-yellow-100', 'text-yellow-800', 'bg-red-100', 'text-red-800', 'bg-gray-100', 'text-gray-800');
    
    // Set classes based on status
    switch (status) {
        case 'completed':
            badge.classList.add('bg-green-100', 'text-green-800');
            badge.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Đã hoàn thành';
            break;
            
        case 'processing':
            badge.classList.add('bg-yellow-100', 'text-yellow-800');
            badge.innerHTML = '<i class="fas fa-cog fa-spin mr-1"></i> Đang xử lý';
            break;
            
        case 'error':
            badge.classList.add('bg-red-100', 'text-red-800');
            badge.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i> Lỗi';
            break;
            
        default:
            badge.classList.add('bg-gray-100', 'text-gray-800');
            badge.innerHTML = '<i class="fas fa-clock mr-1"></i> Chờ xử lý';
            break;
    }
    
    // Update data attribute
    badge.setAttribute('data-status', status);
}

/**
 * Initialize video tabs
 */
function initVideoTabs() {
    const tabButtons = document.querySelectorAll('.video-tab-btn');
    const tabContents = document.querySelectorAll('.video-tab-content');
    
    if (tabButtons.length === 0 || tabContents.length === 0) return;
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const target = button.getAttribute('data-tab');
            
            // Deactivate all tabs
            tabButtons.forEach(btn => btn.classList.remove('bg-blue-500', 'text-white'));
            tabContents.forEach(content => content.classList.add('hidden'));
            
            // Activate target tab
            button.classList.add('bg-blue-500', 'text-white');
            document.getElementById(target).classList.remove('hidden');
        });
    });
}

/**
 * Initialize video action buttons
 */
function initVideoActions() {
    // Process button
    const processBtn = document.getElementById('processVideoBtn');
    if (processBtn) {
        processBtn.addEventListener('click', function() {
            const videoId = this.getAttribute('data-video-id');
            
            // Confirm action
            if (confirm('Bạn có chắc chắn muốn xử lý video này?')) {
                // Disable button
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Đang xử lý...';
                
                // Submit form
                document.getElementById('processVideoForm').submit();
            }
        });
    }
    
    // Reset button
    const resetBtn = document.getElementById('resetVideoBtn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            const videoId = this.getAttribute('data-video-id');
            
            // Confirm action
            if (confirm('Bạn có chắc chắn muốn đặt lại trạng thái xử lý video này? Hành động này sẽ xóa tất cả dữ liệu xử lý hiện tại.')) {
                // Disable button
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Đang đặt lại...';
                
                // Submit form
                document.getElementById('resetVideoForm').submit();
            }
        });
    }
    
    // Delete button
    const deleteBtn = document.getElementById('deleteVideoBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            const videoId = this.getAttribute('data-video-id');
            
            // Confirm action
            if (confirm('Bạn có chắc chắn muốn xóa video này? Hành động này không thể hoàn tác.')) {
                // Disable button
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Đang xóa...';
                
                // Submit form
                document.getElementById('deleteVideoForm').submit();
            }
        });
    }
}

/**
 * Initialize batch actions for videos
 */
function initBatchActions() {
    const batchActionForm = document.getElementById('batchActionForm');
    const selectAllCheckbox = document.getElementById('selectAll');
    const videoCheckboxes = document.querySelectorAll('.video-checkbox');
    const batchActionBtn = document.getElementById('batchActionBtn');
    
    if (!batchActionForm || !selectAllCheckbox || videoCheckboxes.length === 0 || !batchActionBtn) return;
    
    // Select all checkbox
    selectAllCheckbox.addEventListener('change', function() {
        videoCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        
        // Update batch action button
        updateBatchActionButton();
    });
    
    // Individual checkboxes
    videoCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Update select all checkbox
            selectAllCheckbox.checked = Array.from(videoCheckboxes).every(cb => cb.checked);
            
            // Update batch action button
            updateBatchActionButton();
        });
    });
    
    // Batch action dropdown
    const batchActionSelect = document.getElementById('batchAction');
    if (batchActionSelect) {
        batchActionSelect.addEventListener('change', function() {
            updateBatchActionButton();
        });
    }
    
    // Batch action button
    batchActionBtn.addEventListener('click', function() {
        const selectedAction = batchActionSelect.value;
        const selectedVideos = Array.from(videoCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
        
        if (selectedVideos.length === 0) {
            showToast('Vui lòng chọn ít nhất một video.', 'error');
            return;
        }
        
        if (!selectedAction) {
            showToast('Vui lòng chọn một hành động.', 'error');
            return;
        }
        
        // Confirm action
        let confirmMessage = '';
        
        switch (selectedAction) {
            case 'process':
                confirmMessage = `Bạn có chắc chắn muốn xử lý ${selectedVideos.length} video đã chọn?`;
                break;
                
            case 'reset':
                confirmMessage = `Bạn có chắc chắn muốn đặt lại trạng thái xử lý cho ${selectedVideos.length} video đã chọn? Hành động này sẽ xóa tất cả dữ liệu xử lý hiện tại.`;
                break;
                
            case 'delete':
                confirmMessage = `Bạn có chắc chắn muốn xóa ${selectedVideos.length} video đã chọn? Hành động này không thể hoàn tác.`;
                break;
        }
        
        if (confirm(confirmMessage)) {
            // Create hidden inputs for each selected video
            selectedVideos.forEach(videoId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'video_ids[]';
                input.value = videoId;
                batchActionForm.appendChild(input);
            });
            
            // Set action
            batchActionForm.action = `/video/batch${selectedAction}`;
            
            // Submit form
            batchActionForm.submit();
        }
    });
    
    /**
     * Update batch action button state and text
     */
    function updateBatchActionButton() {
        const selectedVideos = Array.from(videoCheckboxes).filter(cb => cb.checked);
        const selectedAction = batchActionSelect.value;
        
        // Update button text
        let buttonText = 'Thực hiện';
        
        if (selectedVideos.length > 0) {
            buttonText += ` (${selectedVideos.length} video)`;
        }
        
        batchActionBtn.textContent = buttonText;
        
        // Update button state
        batchActionBtn.disabled = selectedVideos.length === 0 || !selectedAction;
    }
}
