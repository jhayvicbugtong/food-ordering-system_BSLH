<?php
include __DIR__ . '/includes/header.php';

// Fetch System Settings
$settings = [];
$settings_query = $conn->query("SELECT * FROM system_settings ORDER BY setting_id");
if ($settings_query) {
    while ($row = $settings_query->fetch_assoc()) {
        $settings[$row['setting_key']] = $row;
    }
}

// Helper to safely get setting value
function get_setting($key, $settings) {
    return $settings[$key]['setting_value'] ?? '';
}

// Fetch Deliverable Barangays
$barangays_query = $conn->query("SELECT * FROM deliverable_barangays ORDER BY barangay_name ASC");
?>

<style>
  /* Page Specific Styles */
  body {
    background-color: #f8f9fa;
  }
  .main-content {
    padding-top: 1.5rem;
    padding-bottom: 3rem;
  }
  
  /* Modern Card Design */
  .settings-card {
    background: #fff;
    border: 1px solid rgba(230, 230, 230, 1);
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.02);
    height: 100%;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .settings-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
  }
  
  /* Header Layout */
  .settings-card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    background: #fff;
    border-radius: 12px 12px 0 0;
    
    /* Desktop Layout: Flex Row, Space Between */
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
  }

  .settings-card-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 1.2rem;
    flex-shrink: 0;
  }
  .icon-blue { background-color: #e0f2fe; color: #0284c7; }
  .icon-green { background-color: #dcfce7; color: #16a34a; }
  .icon-purple { background-color: #f3e8ff; color: #9333ea; }
  
  .settings-card-title h5 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #1f2937;
  }
  .settings-card-title p {
    margin: 2px 0 0;
    font-size: 0.85rem;
    color: #6b7280;
  }
  .settings-card-body {
    padding: 1.5rem;
  }

  /* --- Tabs Style (Scoped to #settingsTabs to fix sidebar issue) --- */
  .nav-tabs {
    border-bottom: none;
    margin-bottom: 1.5rem;
    gap: 10px;
    flex-wrap: nowrap;
    overflow-x: auto;
    white-space: nowrap;
    padding-bottom: 5px;
  }
  .nav-tabs::-webkit-scrollbar { display: none; }
  .nav-tabs { -ms-overflow-style: none; scrollbar-width: none; }

  #settingsTabs .nav-link {
    border: none !important; /* Ensure no border */
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
  
  /* Force removal of any pseudo-elements that might create the green line */
  #settingsTabs .nav-link::before,
  #settingsTabs .nav-link::after {
    content: none !important;
    display: none !important;
  }

  #settingsTabs .nav-link i { margin-right: 8px; }
  
  #settingsTabs .nav-link:hover {
    color: #111827;
    background-color: #e5e7eb !important;
  }
  
  #settingsTabs .nav-link.active {
    background-color: #4f46e5 !important; /* Indigo/Blue Background */
    color: #fff !important;               /* White Text */
    box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
    font-weight: 600;
    border: none !important;              /* Explicitly remove border again for active state */
  }

  /* Form Controls */
  .form-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    margin-bottom: 0.5rem;
  }
  .form-control, .form-select {
    border-radius: 8px;
    border-color: #d1d5db;
    padding: 0.6rem 0.85rem;
    font-size: 0.95rem;
  }
  .form-control:focus, .form-select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
  }
  .input-group-text {
    background-color: #f9fafb;
    border-color: #d1d5db;
    color: #6b7280;
  }

  /* Visual Status Toggle */
  .status-toggle {
    position: relative;
    display: inline-block;
    width: 100%;
  }
  .status-toggle select {
    appearance: none;
    padding-left: 40px;
    font-weight: 600;
    cursor: pointer;
  }
  .status-indicator {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 10px;
    height: 10px;
    border-radius: 50%;
    z-index: 5;
    pointer-events: none;
    transition: background-color 0.3s;
  }
  .status-open .status-indicator { background-color: #10b981; box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2); }
  .status-open select { border-color: #10b981; color: #065f46; background-color: #ecfdf5; }
  
  .status-closed .status-indicator { background-color: #ef4444; box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2); }
  .status-closed select { border-color: #ef4444; color: #991b1b; background-color: #fef2f2; }

  /* Table Styles */
  .modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
  }
  .modern-table th {
    background-color: #f9fafb;
    color: #6b7280;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
  }
  .modern-table td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
  }
  .modern-table tr:last-child td {
    border-bottom: none;
  }
  .modern-table tr:hover td {
    background-color: #f9fafb;
  }
  
  /* Search Box */
  .header-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
  }
  .search-wrapper {
    position: relative;
    width: 250px;
  }
  .search-wrapper i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
  }
  .search-wrapper input {
    padding-left: 35px;
    border-radius: 8px;
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    font-size: 0.9rem;
    transition: all 0.2s;
  }
  .search-wrapper input:focus {
    background-color: #fff;
    border-color: #4f46e5;
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
  }

  /* --- MOBILE RESPONSIVENESS --- */
  @media (max-width: 768px) {
    .main-content {
      padding: 1rem;
      padding-bottom: 5rem;
    }
    .page-header {
      flex-direction: column;
      align-items: flex-start !important;
      gap: 10px;
      margin-bottom: 1.25rem;
    }
    .nav-tabs { gap: 5px; }
    #settingsTabs .nav-link { padding: 0.6rem 1rem; font-size: 0.9rem; }

    /* Stack Header Components on Mobile */
    .settings-card-header {
      flex-direction: column;
      align-items: stretch;
      gap: 1rem;
      padding: 1rem;
    }
    
    /* Header Actions Full Width on Mobile */
    .header-actions {
      flex-direction: column;
      width: 100%;
    }
    .search-wrapper { width: 100%; }
    .add-area-btn { width: 100%; }

    .settings-card-body { padding: 1.25rem; }
    .table-responsive { border: 0; }
    .modern-table th, .modern-table td { padding: 0.75rem; }
    .btn-save-general { width: 100%; margin-top: 1rem; }
  }
</style>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    
    <div class="page-header d-flex justify-content-between align-items-end mb-4">
      <div>
        <h1 class="h3 fw-bold text-dark mb-1">Settings</h1>
        <p class="text-muted mb-0">Configure store details and delivery zones.</p>
      </div>
    </div>

    <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#generalPane" type="button" role="tab">
          <i class="bi bi-sliders"></i>General
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="delivery-tab" data-bs-toggle="tab" data-bs-target="#deliveryPane" type="button" role="tab">
          <i class="bi bi-map"></i>Delivery Areas
        </button>
      </li>
    </ul>

    <div class="tab-content" id="settingsTabsContent">
      
      <div class="tab-pane fade show active" id="generalPane" role="tabpanel">
        <form id="settingsForm">
          <div class="row g-4">
            
            <div class="col-lg-6">
              <div class="settings-card">
                <div class="settings-card-header">
                  <div class="d-flex align-items-center gap-3">
                    <div class="settings-card-icon icon-blue"><i class="bi bi-shop"></i></div>
                    <div class="settings-card-title">
                      <h5>Store Identity</h5>
                      <p>Public facing information.</p>
                    </div>
                  </div>
                </div>
                <div class="settings-card-body">
                  <div class="mb-4">
                    <label class="form-label">Store Name</label>
                    <input type="text" class="form-control" name="store_name" value="<?= htmlspecialchars(get_setting('store_name', $settings)) ?>" required>
                  </div>

                  <div class="mb-4">
                    <label class="form-label">Store Location</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                        <input type="text" class="form-control" name="store_location" value="<?= htmlspecialchars(get_setting('store_location', $settings)) ?>" placeholder="City, Province">
                    </div>
                  </div>
                  
                  <div class="row g-3">
                      <div class="col-12 col-md-6">
                        <label class="form-label">Contact Phone</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="text" class="form-control" name="store_phone" value="<?= htmlspecialchars(get_setting('store_phone', $settings)) ?>">
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <label class="form-label">Contact Email</label>
                        <div class="input-group">
                             <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" name="store_email" value="<?= htmlspecialchars(get_setting('store_email', $settings)) ?>">
                        </div>
                      </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="settings-card">
                <div class="settings-card-header">
                   <div class="d-flex align-items-center gap-3">
                      <div class="settings-card-icon icon-green"><i class="bi bi-clock-history"></i></div>
                      <div class="settings-card-title">
                        <h5>Operations</h5>
                        <p>Availability and timing.</p>
                      </div>
                   </div>
                </div>
                <div class="settings-card-body">
                  
                  <div class="mb-4">
                    <label class="form-label">Store Status</label>
                    <?php $current_status = get_setting('store_status', $settings); ?>
                    <div class="status-toggle <?= $current_status === 'open' ? 'status-open' : 'status-closed' ?>" id="statusToggleContainer">
                        <div class="status-indicator"></div>
                        <select class="form-select" name="store_status" id="storeStatusSelect">
                            <option value="open" <?= $current_status === 'open' ? 'selected' : '' ?>>Open for Business</option>
                            <option value="closed" <?= $current_status === 'closed' ? 'selected' : '' ?>>Temporarily Closed</option>
                        </select>
                    </div>
                    <div class="form-text text-muted mt-2">When closed, customers cannot place new orders.</div>
                  </div>

                  <div class="row g-3">
                      <div class="col-6">
                        <label class="form-label">Opening Time</label>
                        <input type="time" class="form-control" name="opening_time" value="<?= htmlspecialchars(get_setting('opening_time', $settings)) ?>">
                      </div>
                      <div class="col-6">
                        <label class="form-label">Closing Time</label>
                        <input type="time" class="form-control" name="closing_time" value="<?= htmlspecialchars(get_setting('closing_time', $settings)) ?>">
                      </div>
                  </div>
                  
                </div>
              </div>
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold btn-save-general" id="btnSaveGeneral">
                    <i class="bi bi-check-circle me-2"></i>Save Changes
                </button>
            </div>

          </div>
        </form>
      </div>

      <div class="tab-pane fade" id="deliveryPane" role="tabpanel">
        <div class="settings-card">
            
            <div class="settings-card-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="settings-card-icon icon-purple"><i class="bi bi-map"></i></div>
                    <div class="settings-card-title">
                        <h5>Delivery Areas</h5>
                        <p>Manage serviceable barangays.</p>
                    </div>
                </div>
                
                <div class="header-actions">
                     <div class="search-wrapper">
                         <i class="bi bi-search"></i>
                         <input type="text" id="barangaySearch" class="form-control" placeholder="Search area...">
                     </div>
                     <button class="btn btn-primary fw-semibold add-area-btn text-nowrap" id="addBarangayBtn">
                        <i class="bi bi-plus-lg me-1"></i> Add Area
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table modern-table mb-0" id="barangayTable">
                    <thead>
                        <tr>
                            <th>Barangay Name</th>
                            <th>Delivery Fee</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="barangayTableBody">
                    <?php if ($barangays_query && $barangays_query->num_rows > 0): ?>
                        <?php while($b = $barangays_query->fetch_assoc()): ?>
                            <tr id="row-<?= $b['barangay_id'] ?>">
                                <td class="fw-bold text-dark"><?= htmlspecialchars($b['barangay_name']) ?></td>
                                <td class="text-secondary">₱<?= number_format($b['delivery_fee'], 2) ?></td>
                                <td>
                                    <?php if($b['is_active']): ?>
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <button class="btn btn-sm btn-light border btn-edit-barangay" 
                                                data-id="<?= $b['barangay_id'] ?>" 
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light border text-danger btn-delete-barangay" 
                                                data-id="<?= $b['barangay_id'] ?>" 
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr id="noAreasRow"><td colspan="4" class="text-center text-muted py-5">No delivery areas configured yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
      </div>

    </div>
  </main>
</div>

<div class="modal fade" id="barangayModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <form id="barangayForm">
        <div class="modal-header border-bottom-0 pb-0">
          <h5 class="modal-title fw-bold" id="barangayModalLabel">Manage Area</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body pt-4">
          <input type="hidden" id="barangay_id" name="barangay_id" value="">
          
          <div class="mb-3">
            <label class="form-label">Barangay</label>
            <select class="form-select form-select-lg bg-light border-0" id="barangay_name" name="barangay_name" required>
                <option value="" disabled selected>Select Barangay...</option>
            </select>
            <div id="barangayLoading" class="form-text text-primary mt-2" style="display:none;">
                <span class="spinner-border spinner-border-sm me-1"></span> Loading official list...
            </div>
          </div>
          
          <div class="row g-3">
              <div class="col-6">
                <label class="form-label">Fee (₱)</label>
                <input type="number" step="0.01" class="form-control bg-light border-0" id="delivery_fee" name="delivery_fee" required placeholder="0.00">
              </div>
              <div class="col-6">
                <label class="form-label">Status</label>
                <select class="form-select bg-light border-0" id="is_active" name="is_active">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
              </div>
          </div>
        </div>
        <div class="modal-footer border-top-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary px-4">Save Area</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('storeStatusSelect');
    const statusContainer = document.getElementById('statusToggleContainer');

    // 1. Status Toggle Visuals
    if(statusSelect) {
        statusSelect.addEventListener('change', function() {
            statusContainer.className = 'status-toggle ' + (this.value === 'open' ? 'status-open' : 'status-closed');
        });
    }

    // 2. Save General Settings
    document.getElementById('settingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSaveGeneral');
        const originalText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        const formData = new FormData(this);

        fetch('actions/save_settings.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                const newName = formData.get('store_name');
                const headerBrand = document.querySelector('.brand-main');
                if(headerBrand && newName) {
                    headerBrand.textContent = newName;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Configuration Saved',
                    text: 'Store settings have been updated successfully.',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Failed to save settings.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });

    // 3. PSGC API for Barangays (Cached)
    let cachedBarangays = null;
    function populateBarangays(selectedValue = null) {
        const select = document.getElementById('barangay_name');
        const loader = document.getElementById('barangayLoading');
        
        select.innerHTML = '<option value="" disabled selected>Select Barangay...</option>';

        if(cachedBarangays) {
            renderBarangayOptions(cachedBarangays, selectedValue);
            return;
        }

        loader.style.display = 'block';
        select.disabled = true;

        // Nasugbu Code: 041019000
        fetch('https://psgc.gitlab.io/api/cities-municipalities/041019000/barangays/')
            .then(res => res.json())
            .then(data => {
                cachedBarangays = data.sort((a,b) => a.name.localeCompare(b.name));
                renderBarangayOptions(cachedBarangays, selectedValue);
            })
            .catch(err => {
                console.error(err);
                select.innerHTML += '<option value="" disabled>Error loading list</option>';
            })
            .finally(() => {
                loader.style.display = 'none';
                select.disabled = false;
            });
    }

    function renderBarangayOptions(data, selected) {
        const select = document.getElementById('barangay_name');
        data.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b.name;
            opt.textContent = b.name;
            if(selected && b.name === selected) opt.selected = true;
            select.appendChild(opt);
        });
    }

    // 4. Barangay Search
    document.getElementById('barangaySearch').addEventListener('keyup', function() {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('#barangayTableBody tr:not(#noAreasRow)');
        
        rows.forEach(row => {
            const nameCell = row.querySelector('td:first-child');
            if(nameCell) {
                const text = nameCell.textContent.toLowerCase();
                if(text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    });

    // 5. Barangay Modal & Actions
    const barangayModal = new bootstrap.Modal(document.getElementById('barangayModal'));
    
    document.getElementById('addBarangayBtn').addEventListener('click', () => {
        document.getElementById('barangayForm').reset();
        document.getElementById('barangay_id').value = '';
        document.getElementById('barangayModalLabel').innerText = 'Add Delivery Area';
        populateBarangays();
        barangayModal.show();
    });

    document.getElementById('barangayForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);

        fetch('actions/save_barangay.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    barangayModal.hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: data.message,
                        timer: 1000,
                        showConfirmButton: false
                    }).then(() => location.reload()); 
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
    });

    document.querySelectorAll('.btn-edit-barangay').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch(`actions/get_barangay.php?id=${id}`)
                .then(r => r.json())
                .then(data => {
                    if(!data.error) {
                        document.getElementById('barangay_id').value = data.barangay_id;
                        document.getElementById('delivery_fee').value = data.delivery_fee;
                        document.getElementById('is_active').value = data.is_active;
                        document.getElementById('barangayModalLabel').innerText = 'Edit Delivery Area';
                        
                        populateBarangays(data.barangay_name);
                        barangayModal.show();
                    } else {
                        Swal.fire('Error', 'Could not fetch data', 'error');
                    }
                });
        });
    });

    document.querySelectorAll('.btn-delete-barangay').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            Swal.fire({
                title: 'Remove Area?',
                text: "This area will no longer be available for delivery.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, remove'
            }).then((result) => {
                if (result.isConfirmed) {
                    const fd = new FormData();
                    fd.append('barangay_id', id);
                    fetch('actions/delete_barangay.php', { method: 'POST', body: fd })
                        .then(r => r.json())
                        .then(data => {
                            if(data.success) {
                                document.getElementById(`row-${id}`).remove();
                                Swal.fire('Deleted!', 'Area removed.', 'success');
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        });
                }
            });
        });
    });
    
    // 6. Persist Tabs on Refresh (FIXED: Wait for bootstrap to load)
    const activeTab = localStorage.getItem('activeSettingsTab');
    if (activeTab) {
        const tabBtn = document.querySelector(`button[data-bs-target="${activeTab}"]`);
        // Check if bootstrap is defined (it should be because footer is loaded above)
        if(tabBtn && typeof bootstrap !== 'undefined') {
            bootstrap.Tab.getOrCreateInstance(tabBtn).show();
        }
    }
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(el => {
        el.addEventListener('shown.bs.tab', e => {
            localStorage.setItem('activeSettingsTab', e.target.dataset.bsTarget);
        });
    });
});
</script>