</main>
        
        <!-- Footer -->
        <footer class="bg-white shadow-md py-4 px-6 text-center text-sm text-gray-600">
            <p>&copy; <?= date('Y') ?> YouTube Processor. All rights reserved.</p>
        </footer>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Custom JavaScript -->
<script src="<?= asset('js/main.js') ?>"></script>

<!-- Page-specific JavaScript -->
<?php if (isset($js_file) && file_exists('public/js/' . $js_file . '.js')): ?>
    <script src="<?= asset('js/' . $js_file . '.js') ?>"></script>
<?php endif; ?>

<script>
    // Toggle user dropdown
    document.addEventListener('DOMContentLoaded', function() {
        const userDropdown = document.getElementById('userDropdown');
        const userDropdownMenu = document.getElementById('userDropdownMenu');
        
        if (userDropdown && userDropdownMenu) {
            userDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('hidden');
            });
            
            document.addEventListener('click', function() {
                userDropdownMenu.classList.add('hidden');
            });
        }
        
        // Mobile sidebar toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileSidebar = document.getElementById('mobileSidebar');
        const closeSidebar = document.getElementById('closeSidebar');
        
        if (mobileMenuBtn && mobileSidebar) {
            mobileMenuBtn.addEventListener('click', function() {
                mobileSidebar.classList.remove('hidden');
            });
            
            if (closeSidebar) {
                closeSidebar.addEventListener('click', function() {
                    mobileSidebar.classList.add('hidden');
                });
            }
            
            // Close when clicking outside
            mobileSidebar.addEventListener('click', function(e) {
                if (e.target === mobileSidebar) {
                    mobileSidebar.classList.add('hidden');
                }
            });
        }
        
        // Toast notifications
        const successToast = document.getElementById('successToast');
        const errorToast = document.getElementById('errorToast');
        const infoToast = document.getElementById('infoToast');
        
        const hideToast = function(toast) {
            if (toast) {
                setTimeout(function() {
                    toast.classList.remove('show');
                    
                    // Remove the element after animation
                    setTimeout(function() {
                        toast.remove();
                    }, 300);
                }, 5000);
            }
        };
        
        hideToast(successToast);
        hideToast(errorToast);
        hideToast(infoToast);
    });
</script>
</body>
</html>
