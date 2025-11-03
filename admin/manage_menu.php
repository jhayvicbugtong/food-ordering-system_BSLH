<?php
include __DIR__ . '/includes/header.php';

?>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    
    <section class="content-card mb-4">
      <div class="content-card-header">
        <div class="left">
          <h2>Menu Management</h2>
          <p>Add new items or update pricing / availability</p>
        </div>
        <div class="right">
          <button class="btn btn-success" id="addNewItemBtn">
            <i class="bi bi-plus-circle"></i> Add New Item
          </button>
        </div>
      </div>
    </section>

    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Current Menu</h2>
          <p>Your live items</p>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover" id="menuTable">
          <thead>
            <tr>
              <th>Image</th>
              <th>Item</th>
              <th>Category</th>
              <th>Price</th>
              <th>Availability</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="menuTableBody">
            </tbody>
        </table>
      </div>
      
      <nav id="paginationNavContainer" aria-label="Menu Pagination">
        <ul class="pagination justify-content-center">
          </ul>
      </nav>

    </section>
  </main>
</div>

<div class="modal fade" id="menuModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="menuForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="menuModalLabel">Add New Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          
          <input type="hidden" id="product_id" name="product_id" value="">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Item Name</label>
              <input type="text" class="form-control" id="name" name="name" placeholder="Lomi Special" required>
            </div>

            <div class="col-md-3">
              <label class="form-label">Category</label>
              <select class="form-select" id="category_id" name="category_id" required>
                <option value="">Select Category</option>
                <?php
                $categories_query = "SELECT * FROM categories ORDER BY category_name";
                $categories_result = $conn->query($categories_query);
                if ($categories_result) {
                    while ($category = $categories_result->fetch_assoc()):
                ?>
                  <option value="<?= $category['category_id'] ?>"><?= htmlspecialchars($category['category_name']) ?></option>
                <?php 
                    endwhile;
                }
                ?>
              </select>
            </div>

            <div class="col-md-2">
              <label class="form-label">Price (₱)</label> 
              <input type="number" step="0.01" class="form-control" id="base_price" name="base_price" placeholder="89.00" required>
            </div>

            <div class="col-md-3">
              <label class="form-label">Availability</label>
              <select class="form-select" id="is_available" name="is_available" required>
                <option value="1">Visible (Orderable)</option>
                <option value="0">Hidden / Sold out</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Product Image</label>
              <input type="file" class="form-control" id="product_image" name="product_image" accept="image/*">
              <small class="text-muted">Recommended size: 500x500px, max 2MB</small>
              <div id="imagePreview" class="mt-2"></div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Description</label>
              <textarea class="form-control" id="description" name="description" rows="5" placeholder="Thick noodles, rich broth, egg, chicharon..."></textarea>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success" id="submitMenuButton">Add Item</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
// --- Define modal variables in a higher scope ---
let menuModalInstance = null;
let currentPage = 1; // Keep track of the current page

// --- Setup SweetAlert Toast for success messages ---
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

$(document).ready(function() {
    
    // --- Initialize modal instance ---
    menuModalInstance = new bootstrap.Modal($('#menuModal')[0]);
    
    // Load initial menu items (page 1)
    loadMenuItems(currentPage);

    // --- Event Handlers ---

    // Handle form submission
    $('#menuForm').on('submit', function(e) {
        e.preventDefault();
        saveMenuItem();
    });

    // Image preview
    $('#product_image').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').html(`<img src="${e.target.result}" class="img-thumbnail" style="max-height: 150px;">`);
            }
            reader.readAsDataURL(file);
        }
    });

    // "Add New Item" button click
    $('#addNewItemBtn').on('click', function() {
        resetForm();
        $('#menuModalLabel').text('Add New Item');
        $('#submitMenuButton').text('Add Item');
        menuModalInstance.show();
    });

    // "Edit" button click (Event Delegation)
    $('#menuTableBody').on('click', '.btn-edit', function() {
        const productId = $(this).data('id');
        editItem(productId);
    });

    // "Delete" button click (Event Delegation)
    $('#menuTableBody').on('click', '.btn-delete', function() {
        const productId = $(this).data('id');
        deleteItem(productId);
    });
    
    // *** NEW: Pagination click handler ***
    $('#paginationNavContainer').on('click', '.page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        const $parent = $(this).parent();

        if ($parent.hasClass('disabled') || $parent.hasClass('active')) {
            return; // Do nothing if disabled or current
        }
        
        loadMenuItems(page);
    });
});

// --- Functions ---

function loadMenuItems(page = 1) {
    currentPage = page; // Store the current page
    $.ajax({
        url: 'actions/get_menu_items.php',
        type: 'GET',
        data: { page: page }, // Send page number to server
        dataType: 'json', // Expect a JSON response
        success: function(response) {
            if (response.success) {
                $('#menuTableBody').html(response.html);
                buildPagination(response.pagination); // Call new function
            } else {
                 Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message || 'Could not load menu items.'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Could not load menu items from the server.'
            });
        }
    });
}

function saveMenuItem() {
    const formData = new FormData($('#menuForm')[0]);
    
    $.ajax({
        url: 'actions/save_menu_item.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    Swal.fire('Success!', result.message, 'success');
                    resetForm();
                    loadMenuItems(currentPage); // Reload the CURRENT page
                    menuModalInstance.hide();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: result.message
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Save Failed!',
                    text: 'Received an invalid response from the server. Check the action file.'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Could not save menu item. Please try again.'
            });
        }
    });
}

function editItem(productId) {
    $.ajax({
        url: 'actions/get_menu_item.php',
        type: 'GET',
        data: { product_id: productId },
        dataType: 'json',
        success: function(item) {
            if(item.error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: item.error
                });
                return;
            }

            resetForm(); 

            $('#product_id').val(item.product_id);
            $('#name').val(item.name);
            $('#category_id').val(item.category_id);
            $('#base_price').val(item.base_price);
            $('#is_available').val(item.is_available);
            $('#description').val(item.description);
            
            if (item.image_url) {
                $('#imagePreview').html(`<img src="../${item.image_url}" class="img-thumbnail" style="max-height: 150px;">`);
            }
            
            $('#menuModalLabel').text('Edit Menu Item');
            $('#submitMenuButton').text('Update Item');
            menuModalInstance.show();
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Error loading menu item for editing.'
            });
        }
    });
}

function deleteItem(productId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'actions/delete_menu_item.php',
                type: 'POST',
                data: { product_id: productId },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Swal.fire('Deleted!', res.message, 'success');
                        loadMenuItems(currentPage); // Reload the CURRENT page
                    } else {
                        Swal.fire('Error!', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Could not connect to the server.', 'error');
                }
            });
        }
    });
}

function resetForm() {
    $('#menuForm')[0].reset();
    $('#product_id').val('');
    $('#imagePreview').html('');
}

// *** NEW: Function to build pagination links ***
function buildPagination(pagination) {
    const { currentPage, totalPages } = pagination;
    const container = $('#paginationNavContainer .pagination');
    container.empty(); // Clear old links

    if (totalPages <= 1) {
        return; // No pagination needed
    }

    // Previous Button
    let prevClass = (currentPage === 1) ? 'disabled' : '';
    container.append(`<li class="page-item ${prevClass}">
                        <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                      </li>`);

    // Page Numbers (Smart logic to show only a few)
    const pageRange = 2; // Show 2 pages before and 2 after current
    let startPage = Math.max(1, currentPage - pageRange);
    let endPage = Math.min(totalPages, currentPage + pageRange);

    if (startPage > 1) {
        container.append(`<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`);
        if (startPage > 2) {
            container.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        let activeClass = (i === currentPage) ? 'active' : '';
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
    let nextClass = (currentPage === totalPages) ? 'disabled' : '';
    container.append(`<li class="page-item ${nextClass}">
                        <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                      </li>`);
}
</script>