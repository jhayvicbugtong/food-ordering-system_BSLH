<?php
include __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    
    <section class="content-card mb-4">
      <div class="content-card-header border-0 pb-0 mb-0">
        <div class="left">
          <h2 class="page-title mb-1">Menu Overview</h2>
          <p class="text-muted small mb-0">View current menu items, prices, and availability.</p>
        </div>
      </div>
    </section>

    <ul class="nav nav-tabs mb-3" id="menuTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="menu-items-tab" data-bs-toggle="tab" data-bs-target="#menuItemsPane" type="button" role="tab">
          <i class="bi bi-egg-fried"></i> Menu Items
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categoriesPane" type="button" role="tab">
          <i class="bi bi-tags"></i> Categories
        </button>
      </li>
    </ul>

    <div class="tab-content" id="menuTabsContent">
      
      <div class="tab-pane fade show active" id="menuItemsPane" role="tabpanel">
        <section class="content-card">
          <div class="content-card-header">
            <div class="left">
              <h2 class="section-title mb-1">All Items</h2>
              <p class="text-muted small mb-0">Search for items to check details.</p>
            </div>

            <div class="right header-actions">
              <div class="menu-search-bar">
                <div class="input-group input-group-sm">
                  <span class="input-group-text"><i class="bi bi-search"></i></span>
                  <input type="text" class="form-control" id="searchInput" placeholder="Search items...">
                </div>
              </div>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover table-vcenter modern-table" id="menuTable">
              <thead>
                <tr>
                  <th style="width: 60px;">Image</th>
                  <th>Item</th>
                  <th>Category</th>
                  <th class="text-nowrap">Price</th>
                  <th>Availability</th>
                </tr>
              </thead>
              <tbody id="menuTableBody"></tbody>
            </table>
          </div>
          
          <nav id="paginationNavContainer" aria-label="Menu Pagination" class="mt-3">
            <ul class="pagination justify-content-end mb-0"></ul>
          </nav>
        </section>
      </div>

      <div class="tab-pane fade" id="categoriesPane" role="tabpanel">
        <section class="content-card">
          <div class="content-card-header">
            <div class="left">
              <h2 class="section-title mb-1">Categories</h2>
              <p class="text-muted small mb-0">Active menu categories.</p>
            </div>
            <div class="right header-actions">
               <div class="menu-search-bar">
                <div class="input-group input-group-sm">
                  <span class="input-group-text"><i class="bi bi-search"></i></span>
                  <input type="text" class="form-control" id="categorySearchInput" placeholder="Search categories...">
                </div>
              </div>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover table-vcenter modern-table" id="categoryTable">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Description</th>
                  <th>Order</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody id="categoryTableBody"></tbody>
            </table>
          </div>
           <nav id="categoryPaginationNav" aria-label="Category Pagination" class="mt-3">
            <ul class="pagination justify-content-end mb-0"></ul>
          </nav>
        </section>
      </div>

    </div>
  </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<style>
  /* Reusing Admin Menu Styles */
  body { background-color: #f3f4f6; }
  .main-content { 
      min-height: 100vh; 
      padding-top: 1.5rem; 
      padding-bottom: 1.5rem; 
      margin-left: 220px;
      transition: margin-left 0.3s ease;
  }
  
  .content-card { 
      border-radius: 18px; 
      border: 1px solid rgba(148, 163, 184, 0.3); 
      background: #ffffff; 
      box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06); 
      padding: 18px 20px; 
  }
  .content-card-header { 
      border-bottom: 1px solid rgba(148, 163, 184, 0.25); 
      padding-bottom: 10px; 
      margin-bottom: 12px; 
      display: flex; 
      justify-content: space-between; 
      align-items: center; 
      gap: 0.75rem; 
  }
  
  .menu-search-bar .input-group-text { 
      border-radius: 999px 0 0 999px; 
      background-color: #f9fafb; 
      border-color: #e5e7eb;
  }
  .menu-search-bar .form-control { 
      border-radius: 0 999px 999px 0; 
      border-color: #e5e7eb;
      font-size: 0.9rem;
      min-width: 200px;
  }
  .menu-search-bar .form-control:focus {
      border-color: #4f46e5;
      box-shadow: 0 0 0 1px rgba(79, 70, 229, 0.15);
  }
  
  .modern-table thead th { 
      font-size: 0.75rem; 
      text-transform: uppercase; 
      font-weight: 600; 
      color: #6b7280; 
      border-bottom: 1px solid #e5e7eb;
      padding: 12px 10px;
  }
  .modern-table tbody td { 
      vertical-align: middle; 
      font-size: 0.9rem; 
      padding: 12px 10px;
  }
  .menu-table-img { 
      width: 46px; 
      height: 46px; 
      border-radius: 12px; 
      object-fit: cover; 
      background: #e5e7eb; 
  }
  .menu-table-img-placeholder { 
      width: 46px; 
      height: 46px; 
      border-radius: 12px; 
      background-color: #f0f0f0; 
      display: flex; 
      align-items: center; 
      justify-content: center; 
      color: #adb5bd; 
      font-size: 1.2rem;
  }

  /* Tabs Styling */
  #menuTabs.nav-tabs {
    border-bottom: none;
    margin-bottom: 1.5rem;
    gap: 10px;
  }
  
  #menuTabs .nav-link {
    border: none !important;
    color: #6b7280;
    font-weight: 500;
    padding: 0.75rem 1.25rem;
    background: transparent !important;
    border-radius: 8px;
    transition: all 0.2s;
  }

  /* Explicitly remove the vertical green bar if it was bleeding in from global styles */
  #menuTabs .nav-link::before {
    display: none !important;
    content: none !important;
  }
  
  #menuTabs .nav-link:hover {
    color: #111827;
    background-color: #e5e7eb !important;
  }
  
  #menuTabs .nav-link.active {
    background-color: #4f46e5 !important;
    color: #fff !important;
    box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
    font-weight: 600;
  }
  
  /* Pagination */
  .pagination .page-link { 
      border-radius: 999px !important; 
      font-size: 0.8rem; 
      border-color: #e5e7eb; 
      margin: 0 2px; 
      color: #4f46e5; 
  }
  .pagination .page-item.active .page-link { 
      background-color: #4f46e5; 
      border-color: #4f46e5; 
      color: white; 
  }
  .pagination .page-item.disabled .page-link { 
      color: #9ca3af; 
      border-color: #e5e7eb; 
      background-color: #f9fafb; 
  }

  @media (max-width: 992px) {
    .main-content { margin-left: 0; }
  }
  @media (max-width: 768px) {
    .content-card-header { flex-direction: column; align-items: stretch; }
    .menu-search-bar { width: 100%; }
    .table-responsive { margin: 0 -20px; width: calc(100% + 40px); padding: 0 20px; }
  }
</style>

<script>
let menuPage = 1;
let categoryPage = 1;
let menuSearchTimeout = null;
let categorySearchTimeout = null;

$(document).ready(function() {
    loadMenuItems(menuPage, '');
    loadCategories(categoryPage, '');

    // Menu Search
    $('#searchInput').on('keyup', function() {
        clearTimeout(menuSearchTimeout);
        const term = $(this).val();
        menuSearchTimeout = setTimeout(() => loadMenuItems(1, term), 400);
    });

    // Menu Pagination
    $('#paginationNavContainer').on('click', '.page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        const $parent = $(this).parent();
        if ($parent.hasClass('disabled') || $parent.hasClass('active')) return;
        loadMenuItems(page, $('#searchInput').val());
    });

    // Category Search
    $('#categorySearchInput').on('keyup', function() {
        clearTimeout(categorySearchTimeout);
        const term = $(this).val();
        categorySearchTimeout = setTimeout(() => loadCategories(1, term), 400);
    });

    // Category Pagination
    $('#categoryPaginationNav').on('click', '.page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        const $parent = $(this).parent();
        if ($parent.hasClass('disabled') || $parent.hasClass('active')) return;
        loadCategories(page, $('#categorySearchInput').val());
    });
});

function loadMenuItems(page, search) {
    menuPage = page;
    $.ajax({
        url: 'actions/get_menu_items.php', // Points to staff action
        type: 'GET',
        data: { page: page, search: search },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                $('#menuTableBody').html(res.html);
                buildPagination(res.pagination, '#paginationNavContainer');
            }
        }
    });
}

function loadCategories(page, search) {
    categoryPage = page;
    $.ajax({
        url: 'actions/get_categories.php', // Points to staff action
        type: 'GET',
        data: { page: page, search: search },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                renderCategoryTable(res.categories);
                buildPagination(res.pagination, '#categoryPaginationNav');
            }
        }
    });
}

function renderCategoryTable(categories) {
    let html = '';
    if(categories.length === 0) {
        html = '<tr><td colspan="4" class="text-center py-4 text-muted">No categories found.</td></tr>';
    } else {
        categories.forEach(cat => {
            const statusBadge = cat.is_active == 1 
                ? '<span class="badge bg-success-subtle text-success rounded-pill">Active</span>' 
                : '<span class="badge bg-secondary-subtle text-secondary rounded-pill">Inactive</span>';
            
            html += `
                <tr>
                    <td class="fw-bold text-dark">${cat.category_name}</td>
                    <td class="text-muted small text-wrap" style="max-width: 200px;">${cat.description || '<em>No description</em>'}</td>
                    <td>${cat.display_order}</td>
                    <td>${statusBadge}</td>
                </tr>
            `;
        });
    }
    $('#categoryTableBody').html(html);
}

function buildPagination(pagination, containerSelector) {
    const { currentPage, totalPages } = pagination;
    const container = $(containerSelector + ' .pagination');
    container.empty();

    if (totalPages <= 1) return;

    let prevClass = (currentPage == 1) ? 'disabled' : '';
    container.append(`<li class="page-item ${prevClass}"><a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a></li>`);

    let startPage = Math.max(1, currentPage - 1);
    let endPage = Math.min(totalPages, currentPage + 1);

    if (startPage > 1) {
        container.append(`<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`);
        if (startPage > 2) container.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
    }

    for (let i = startPage; i <= endPage; i++) {
        let activeClass = (i == currentPage) ? 'active' : '';
        container.append(`<li class="page-item ${activeClass}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`);
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) container.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        container.append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
    }

    let nextClass = (currentPage == totalPages) ? 'disabled' : '';
    container.append(`<li class="page-item ${nextClass}"><a class="page-link" href="#" data-page="${currentPage + 1}">Next</a></li>`);
}
</script>