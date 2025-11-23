<footer class="site-footer">
  <p>&copy; <?= date('Y') ?> Bente Sais Staff · Internal Use Only</p>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  // Enhanced sidebar toggle functionality
  document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebar = document.getElementById('sidebar');
    
    // Create overlay for mobile
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);
    
    // Toggle sidebar
    if (sidebarToggle && sidebar) {
      sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
        document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
      });
    }
    
    // Close sidebar with close button
    if (sidebarClose && sidebar) {
      sidebarClose.addEventListener('click', function() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
      });
    }
    
    // Close sidebar when clicking overlay
    overlay.addEventListener('click', function() {
      sidebar.classList.remove('show');
      overlay.classList.remove('show');
      document.body.style.overflow = '';
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
      if (window.innerWidth <= 992) {
        const isClickInsideSidebar = sidebar.contains(event.target);
        const isClickInsideToggle = sidebarToggle.contains(event.target);
        
        if (!isClickInsideSidebar && !isClickInsideToggle && sidebar.classList.contains('show')) {
          sidebar.classList.remove('show');
          overlay.classList.remove('show');
          document.body.style.overflow = '';
        }
      }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
      if (window.innerWidth > 992) {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
      }
    });

    // Header Dropdown Functionality
    const userDropdown = document.getElementById('userDropdown');
    const profileDropdown = document.getElementById('profileDropdown');
    
    if (userDropdown && profileDropdown) {
      // Toggle dropdown on click
      userDropdown.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const isOpen = profileDropdown.classList.contains('show');
        
        // Close all dropdowns first
        closeAllDropdowns();
        
        // Toggle current dropdown
        if (!isOpen) {
          profileDropdown.classList.add('show');
          userDropdown.classList.add('dropdown-open');
        }
      });
      
      // Close dropdown when clicking outside
      document.addEventListener('click', function(e) {
        if (userDropdown && profileDropdown && 
            !userDropdown.contains(e.target) && 
            !profileDropdown.contains(e.target)) {
          closeAllDropdowns();
        }
      });
      
      // Close dropdown on escape key
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          closeAllDropdowns();
        }
      });
      
      // Function to close all dropdowns
      function closeAllDropdowns() {
        if (profileDropdown) profileDropdown.classList.remove('show');
        if (userDropdown) userDropdown.classList.remove('dropdown-open');
      }
    }

    // Initialize dropdowns
    const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    const dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
      return new bootstrap.Dropdown(dropdownToggleEl);
    });
  });
</script>
</body>
</html>