// Admin Sidebar Toggle
document.addEventListener('DOMContentLoaded', function() {
  const sidebar = document.getElementById('sidebar');
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebarOverlay = document.getElementById('sidebarOverlay');
  const closeSidebarBtn = document.querySelector('.btn-close-sidebar');

  // Toggle sidebar on mobile
  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
      sidebar.classList.add('show');
      sidebarOverlay.classList.add('show');
    });
  }

  // Close sidebar
  if (closeSidebarBtn) {
    closeSidebarBtn.addEventListener('click', closeSidebar);
  }

  if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', closeSidebar);
  }

  function closeSidebar() {
    sidebar.classList.remove('show');
    sidebarOverlay.classList.remove('show');
  }

  // Close sidebar when clicking on a link (mobile)
  const sidebarLinks = document.querySelectorAll('.sidebar-link');
  sidebarLinks.forEach(link => {
    link.addEventListener('click', function() {
      if (window.innerWidth < 992) {
        closeSidebar();
      }
    });
  });

  // Update active states
  function updateActiveStates() {
    const currentPage = window.location.pathname.split('/').pop();
    sidebarLinks.forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('href').includes(currentPage)) {
        link.classList.add('active');
      }
    });
  }

  updateActiveStates();
});