<?php
include __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    
    <section class="content-card mb-4">
      <div class="content-card-header border-0 pb-0 mb-0">
        <div class="left">
          <h2 class="page-title mb-1">Menu & Category Management</h2>
          <p class="text-muted small mb-0">Add new items, manage prices, or organize your menu categories.</p>
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
              <h2 class="section-title mb-1">Current Menu</h2>
              <p class="text-muted small mb-0">Live items visible to customers.</p>
            </div>

            <div class="right header-actions">
              <div class="menu-search-bar">
                <div class="input-group input-group-sm">
                  <span class="input-group-text"><i class="bi bi-search"></i></span>
                  <input type="text" class="form-control" id="searchInput" placeholder="Search items...">
                </div>
              </div>
              <button class="btn btn-success btn-sm text-nowrap w-100-mobile" id="addNewItemBtn">
                <i class="bi bi-plus-circle"></i> Add New Item
              </button>
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
                  <th class="text-end text-nowrap">Actions</th>
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
              <p class="text-muted small mb-0">Organize your menu items.</p>
            </div>
            <div class="right header-actions">
               <div class="menu-search-bar">
                <div class="input-group input-group-sm">
                  <span class="input-group-text"><i class="bi bi-search"></i></span>
                  <input type="text" class="form-control" id="categorySearchInput" placeholder="Search categories...">
                </div>
              </div>
              <button class="btn btn-success btn-sm text-nowrap w-100-mobile" id="addNewCategoryBtn">
                <i class="bi bi-plus-circle"></i> Add Category
              </button>
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
                  <th class="text-end text-nowrap">Actions</th>
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

<div class="modal fade" id="menuModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form id="menuForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="menuModalLabel">Add New Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="product_id" name="product_id" value="">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Item Name</label>
              <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Category</label>
              <select class="form-select" id="category_id" name="category_id" required>
                <option value="">Select Category</option>
                <?php
                $categories_query = "SELECT * FROM categories ORDER BY category_name";
                $categories_result = $conn->query($categories_query);
                if ($categories_result) {
                    while ($cat = $categories_result->fetch_assoc()):
                ?>
                  <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                <?php 
                    endwhile;
                }
                ?>
              </select>
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Price (₱)</label> 
              <input type="number" step="0.01" class="form-control" id="base_price" name="base_price" required>
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Availability</label>
              <select class="form-select" id="is_available" name="is_available" required>
                <option value="1">Visible</option>
                <option value="0">Hidden</option>
              </select>
            </div>
             <div class="col-12 col-md-12">
              <label class="form-label">Description</label>
              <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Product Image</label>
              <input type="file" class="form-control" id="product_image" name="product_image" accept="image/*">
              <div id="imagePreview" class="mt-3 text-center bg-light rounded p-2" style="min-height: 100px; display: flex; align-items: center; justify-content: center;">
                  <span class="text-muted small">No image selected</span>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success" id="submitMenuButton">Save Item</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="categoryForm">
        <div class="modal-header">
          <h5 class="modal-title" id="categoryModalLabel">Add New Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="cat_id" name="category_id" value="">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Category Name</label>
              <input type="text" class="form-control" id="cat_name" name="category_name" required>
            </div>
             <div class="col-md-4">
              <label class="form-label">Order</label>
              <input type="number" class="form-control" id="cat_order" name="display_order" value="0">
            </div>
             <div class="col-md-12">
              <label class="form-label">Status</label>
              <select class="form-select" id="cat_status" name="is_active">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
            <div class="col-md-12">
              <label class="form-label">Description</label>
              <textarea class="form-control" id="cat_description" name="description" rows="3"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success" id="submitCategoryButton">Save Category</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<style>
  /* Global Background & Layout */
  body { background-color: #f3f4f6; }
  .main-content { 
      min-height: 100vh; 
      padding-top: 1.5rem; 
      padding-bottom: 1.5rem; 
      margin-left: 220px; /* Desktop sidebar offset */
      transition: margin-left 0.3s ease;
  }
  
  /* Card Styles */
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
  
  /* Header Actions (Search + Button) */
  .header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  /* Search & Inputs */
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
  
  /* Table Styling */
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

  /* Tabs Styling - SCOPED TO #menuTabs to prevent Sidebar conflict */
  .nav-tabs {
    border-bottom: none;
    margin-bottom: 1.5rem;
    gap: 10px;
    flex-wrap: nowrap;
    overflow-x: auto;
    white-space: nowrap;
    padding-bottom: 5px;
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE */
  }
  .nav-tabs::-webkit-scrollbar { display: none; }

  /* FIXED: Scope these styles to #menuTabs so sidebar nav-links aren't affected */
  #menuTabs .nav-link {
    border: none !important;
    color: #6b7280;
    font-weight: 500;
    padding: 0.75rem 1.25rem;
    background: transparent !important;
    border-radius: 8px;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    position: relative;
  }
  
  #menuTabs .nav-link::before,
  #menuTabs .nav-link::after {
    content: none !important;
    display: none !important;
  }

  #menuTabs .nav-link i { margin-right: 8px; }
  
  #menuTabs .nav-link:hover {
    color: #111827;
    background-color: #e5e7eb !important;
  }
  
  /* Active Tab */
  #menuTabs .nav-link.active {
    background-color: #4f46e5 !important; /* Indigo/Blue Background */
    color: #fff !important;               /* White Text */
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

  /* Button Styling */
  .btn { border-radius: 999px; }
  .btn-group-sm .btn { border-radius: 999px; }

  /* RESPONSIVE BREAKPOINTS */
  @media (max-width: 992px) {
    .main-content {
      margin-left: 0;
    }
  }

  @media (max-width: 768px) {
    .content-card-header {
      flex-direction: column;
      align-items: stretch; /* Full width items */
      gap: 15px;
    }
    
    /* Mobile: Stack search and add button */
    .header-actions {
      width: 100%;
      flex-direction: column; /* Search Top, Button Bottom */
      gap: 10px;
    }
    
    .menu-search-bar {
      width: 100%;
    }
    
    .menu-search-bar .form-control {
        min-width: 0; /* Reset min-width */
    }
    
    .w-100-mobile {
        width: 100%;
    }
    
    /* Ensure table doesn't break layout */
    .table-responsive {
      margin-left: -20px;
      margin-right: -20px;
      width: calc(100% + 40px);
      padding-left: 20px;
      padding-right: 20px;
    }
  }
</style>

<script>
// --- Global Variables ---
let menuModalInstance = null;
let categoryModalInstance = null;
let menuPage = 1;
let categoryPage = 1;
let menuSearchTimeout = null;
let categorySearchTimeout = null;

$(document).ready(function() {
    // Initialize Modals
    menuModalInstance = new bootstrap.Modal(document.getElementById('menuModal'));
    categoryModalInstance = new bootstrap.Modal(document.getElementById('categoryModal'));

    // Load initial data
    loadMenuItems(menuPage, '');
    loadCategories(categoryPage, '');

    // ==================== MENU ITEM EVENTS ====================
    
    $('#searchInput').on('keyup', function() {
        clearTimeout(menuSearchTimeout);
        const term = $(this).val();
        menuSearchTimeout = setTimeout(() => loadMenuItems(1, term), 400);
    });

    $('#addNewItemBtn').click(function() {
        resetMenuForm();
        $('#menuModalLabel').text('Add New Item');
        $('#submitMenuButton').text('Save Item');
        menuModalInstance.show();
    });

    $('#menuForm').on('submit', function(e) {
        e.preventDefault();
        saveMenuItem();
    });

    // Use Delegation for dynamic buttons
    $('#menuTableBody').on('click', '.btn-edit', function() {
        editItem($(this).data('id'));
    });

    $('#menuTableBody').on('click', '.btn-delete', function() {
        deleteItem($(this).data('id'));
    });

    $('#paginationNavContainer').on('click', '.page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        const $parent = $(this).parent();
        if ($parent.hasClass('disabled') || $parent.hasClass('active')) return;
        
        loadMenuItems(page, $('#searchInput').val());
    });

    $('#product_image').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => $('#imagePreview').html(`<img src="${e.target.result}" class="img-thumbnail" style="max-height: 100px;">`);
            reader.readAsDataURL(file);
        }
    });

    // ==================== CATEGORY EVENTS ====================

    $('#categorySearchInput').on('keyup', function() {
        clearTimeout(categorySearchTimeout);
        const term = $(this).val();
        categorySearchTimeout = setTimeout(() => loadCategories(1, term), 400);
    });

    $('#addNewCategoryBtn').click(function() {
        resetCategoryForm();
        $('#categoryModalLabel').text('Add New Category');
        $('#submitCategoryButton').text('Save Category');
        categoryModalInstance.show();
    });

    $('#categoryForm').on('submit', function(e) {
        e.preventDefault();
        saveCategory();
    });

    $('#categoryTableBody').on('click', '.btn-edit-cat', function() {
        editCategory($(this).data('id'));
    });

    $('#categoryTableBody').on('click', '.btn-delete-cat', function() {
        deleteCategory($(this).data('id'));
    });

    $('#categoryPaginationNav').on('click', '.page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        const $parent = $(this).parent();
        if ($parent.hasClass('disabled') || $parent.hasClass('active')) return;

        loadCategories(page, $('#categorySearchInput').val());
    });
});

// ==================== MENU FUNCTIONS ====================

function loadMenuItems(page, search) {
    menuPage = page;
    $.ajax({
        url: 'actions/get_menu_items.php',
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

function saveMenuItem() {
    const btn = $('#submitMenuButton');
    const originalText = btn.text();
    btn.prop('disabled', true).text('Saving...');

    const formData = new FormData($('#menuForm')[0]);
    $.ajax({
        url: 'actions/save_menu_item.php',
        type: 'POST',
        data: formData,
        processData: false, contentType: false,
        success: function(res) {
            const result = (typeof res === 'string') ? JSON.parse(res) : res;
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: result.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                menuModalInstance.hide();
                loadMenuItems(menuPage, $('#searchInput').val());
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        },
        complete: function() {
            btn.prop('disabled', false).text(originalText);
        }
    });
}

function editItem(id) {
    $.ajax({
        url: 'actions/get_menu_item.php',
        data: { product_id: id },
        dataType: 'json',
        success: function(item) {
            if(!item.error) {
                resetMenuForm();
                $('#product_id').val(item.product_id);
                $('#name').val(item.name);
                $('#category_id').val(item.category_id);
                $('#base_price').val(item.base_price);
                $('#is_available').val(item.is_available);
                $('#description').val(item.description);
                if(item.image_url) {
                    $('#imagePreview').html(`<img src="../${item.image_url}" class="img-thumbnail" style="max-height: 150px;">`);
                } else {
                    $('#imagePreview').html(`<div class="text-muted small p-3 bg-light rounded">No image set</div>`);
                }
                $('#menuModalLabel').text('Edit Menu Item');
                $('#submitMenuButton').text('Update Item');
                menuModalInstance.show();
            }
        }
    });
}

function deleteItem(id) {
    Swal.fire({
        title: 'Delete Item?', 
        text: "You won't be able to revert this!",
        icon: 'warning', 
        showCancelButton: true, 
        confirmButtonText: 'Yes, delete', 
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('actions/delete_menu_item.php', { product_id: id }, function(res) {
                const r = (typeof res === 'string') ? JSON.parse(res) : res;
                if(r.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!', 
                        text: r.message,
                        timer: 1000,
                        showConfirmButton: false
                    });
                    loadMenuItems(menuPage, $('#searchInput').val());
                } else {
                    Swal.fire('Error', r.message, 'error');
                }
            });
        }
    });
}

function resetMenuForm() {
    $('#menuForm')[0].reset();
    $('#product_id').val('');
    $('#imagePreview').html('');
}

// ==================== CATEGORY FUNCTIONS ====================

function loadCategories(page, search) {
    categoryPage = page;
    $.ajax({
        url: 'actions/get_categories.php',
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
        html = '<tr><td colspan="5" class="text-center py-4 text-muted">No categories found.</td></tr>';
    } else {
        categories.forEach(cat => {
            // Badge style
            const statusBadge = cat.is_active == 1 
                ? '<span class="badge bg-success-subtle text-success rounded-pill">Active</span>' 
                : '<span class="badge bg-secondary-subtle text-secondary rounded-pill">Inactive</span>';
            
            html += `
                <tr>
                    <td class="fw-bold text-dark">${cat.category_name}</td>
                    <td class="text-muted small text-wrap" style="max-width: 200px;">${cat.description || '<em>No description</em>'}</td>
                    <td>${cat.display_order}</td>
                    <td>${statusBadge}</td>
                    <td class="text-end text-nowrap">
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-secondary btn-edit-cat" data-id="${cat.category_id}">
                                <i class="bi bi-pencil-fill"></i> Edit
                            </button>
                            <button class="btn btn-outline-danger btn-delete-cat" data-id="${cat.category_id}">
                                <i class="bi bi-trash-fill"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
    $('#categoryTableBody').html(html);
}

function saveCategory() {
    const btn = $('#submitCategoryButton');
    const originalText = btn.text();
    btn.prop('disabled', true).text('Saving...');

    const data = $('#categoryForm').serialize();
    $.post('actions/save_category.php', data, function(res) {
        const result = (typeof res === 'string') ? JSON.parse(res) : res;
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success', 
                text: result.message,
                timer: 1500,
                showConfirmButton: false
            });
            categoryModalInstance.hide();
            
            // 1. Refresh the category table
            loadCategories(categoryPage, $('#categorySearchInput').val());

            // 2. Update the "Add New Item" Category Dropdown immediately
            const $dropdown = $('#category_id');
            const catId = result.category_id;
            const catName = result.category_name;
            
            // Check if this option already exists (Edit mode)
            let $option = $dropdown.find(`option[value="${catId}"]`);
            
            if ($option.length > 0) {
                $option.text(catName);
            } else {
                $dropdown.append(new Option(catName, catId));
            }

        } else {
            Swal.fire('Error', result.message, 'error');
        }
    }).always(function() {
        btn.prop('disabled', false).text(originalText);
    });
}

function deleteCategory(id) {
    Swal.fire({
        title: 'Delete Category?', 
        text: 'This will fail if products are assigned to this category.',
        icon: 'warning', 
        showCancelButton: true, 
        confirmButtonText: 'Yes, delete', 
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('actions/delete_category.php', { category_id: id }, function(res) {
                const r = (typeof res === 'string') ? JSON.parse(res) : res;
                
                if(r.success) {
                    Swal.fire('Deleted!', r.message, 'success');
                    loadCategories(categoryPage, $('#categorySearchInput').val());
                    $(`#category_id option[value="${id}"]`).remove();
                } else {
                    Swal.fire('Cannot Delete', r.message, 'error');
                }
            });
        }
    });
}

function editCategory(id) {
    $.get('actions/get_category.php', { category_id: id }, function(cat) {
        if(cat.error) {
            Swal.fire('Error', cat.error, 'error');
        } else {
            resetCategoryForm();
            $('#cat_id').val(cat.category_id);
            $('#cat_name').val(cat.category_name);
            $('#cat_description').val(cat.description);
            $('#cat_order').val(cat.display_order);
            $('#cat_status').val(cat.is_active);
            
            $('#categoryModalLabel').text('Edit Category');
            $('#submitCategoryButton').text('Update Category');
            categoryModalInstance.show();
        }
    }, 'json');
}

function resetCategoryForm() {
    $('#categoryForm')[0].reset();
    $('#cat_id').val('');
}

// Shared Pagination Builder
function buildPagination(pagination, containerSelector) {
    const { currentPage, totalPages } = pagination;
    const container = $(containerSelector + ' .pagination');
    container.empty();

    if (totalPages <= 1) {
        return;
    }

    // Previous Button
    let prevClass = (currentPage == 1) ? 'disabled' : '';
    container.append(`<li class="page-item ${prevClass}">
                        <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                      </li>`);

    // Page Numbers
    let startPage = Math.max(1, currentPage - 1);
    let endPage = Math.min(totalPages, currentPage + 1);

    if (startPage > 1) {
        container.append(`<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`);
        if (startPage > 2) {
             container.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        let activeClass = (i == currentPage) ? 'active' : '';
        container.append(`<li class="page-item ${activeClass}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                          </li>`);
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            container.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        }
        container.append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
    }

    // Next Button
    let nextClass = (currentPage == totalPages) ? 'disabled' : '';
    container.append(`<li class="page-item ${nextClass}">
                        <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                      </li>`);
}
</script>