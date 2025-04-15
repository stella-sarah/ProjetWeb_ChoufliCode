            </div> <!-- Closing content-wrapper -->
        </div> <!-- Closing content-area -->
    </div> <!-- Closing main-content -->
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Sidebar Toggle Function
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                });
            }
            
            // Active menu item
            const currentPath = window.location.href;
            document.querySelectorAll('.sidebar-nav a').forEach(link => {
                if (currentPath.includes(link.getAttribute('href'))) {
                    link.parentElement.classList.add('active');
                }
            });
            
            // Handle responsive design
            function checkWidth() {
                if ($(window).width() < 992) {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                }
            }
            
            // Check on load and resize
            checkWidth();
            $(window).resize(checkWidth);
        });
    </script>
</body>
</html> 