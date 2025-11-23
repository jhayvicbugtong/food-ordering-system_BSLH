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

// Fetch Deliverable Barangays
$barangays_query = $conn->query("SELECT * FROM deliverable_barangays ORDER BY barangay_name ASC");
?>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    
    <section class="content-card mb-4">
      <div class="content-card-header border-0 pb-0 mb-0">
        <div class="left">
          <h2 class="page-title mb-1">System Settings</h2>
          <p class="text-muted small mb-0">Manage store configuration and delivery areas.</p>
        </div>
      </div>
    </section>

    <ul class="nav nav-tabs mb-3" id="settingsTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#generalPane" type="button" role="tab">
          <i class="bi bi-sliders"></i> General
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="delivery-tab" data-bs-toggle="tab" data-bs-target="#deliveryPane" type="button" role="tab">
          <i class="bi bi-map"></i> Delivery Areas
        </button>
      </li>
    </ul>

    <div class="tab-content" id="settingsTabsContent">
      
      <div class="tab-pane fade show active" id="generalPane" role="tabpanel">
        <section class="content-card">
          <div class="content-card-header">
            <div class="left">
              <h2 class="section-title mb-1">Store Configuration</h2>
            </div>
            <div class="right">
                <button type="submit" form="settingsForm" class="btn btn-success btn-sm">
                    <i class="bi bi-save"></i> Save Changes
                </button>
            </div>
          </div>

          <form id="settingsForm">
            <div class="row g-3">
                <?php foreach ($settings as $key => $data): ?>
                    <div class="col-md-6">
                        <label class="form-label text-capitalize">
                            <?= str_replace('_', ' ', $key) ?>
                            <?php if(!empty($data['description'])): ?>
                                <i class="bi bi-info-circle text-muted ms-1" data-bs-toggle="tooltip" title="<?= htmlspecialchars($data['description']) ?>"></i>
                            <?php endif; ?>
                        </label>

                        <?php if ($key === 'store_status'): ?>
                             <select class="form-select fw-bold <?= $data['setting_value'] === 'open' ? 'text-success' : 'text-danger' ?>" name="<?= $key ?>" onchange="this.className = 'form-select fw-bold ' + (this.value === 'open' ? 'text-success' : 'text-danger')">
                                <option value="open" class="text-success" <?= $data['setting_value'] == 'open' ? 'selected' : '' ?>>Open</option>
                                <option value="closed" class="text-danger" <?= $data['setting_value'] == 'closed' ? 'selected' : '' ?>>Closed</option>
                            </select>

                        <?php elseif ($data['setting_type'] === 'boolean'): ?>
                             <select class="form-select" name="<?= $key ?>">
                                <option value="1" <?= $data['setting_value'] == '1' ? 'selected' : '' ?>>Enabled</option>
                                <option value="0" <?= $data['setting_value'] == '0' ? 'selected' : '' ?>>Disabled</option>
                            </select>
                        <?php elseif ($key === 'opening_time' || $key === 'closing_time'): ?>
                            <input type="time" class="form-control" name="<?= $key ?>" value="<?= htmlspecialchars($data['setting_value']) ?>">
                        <?php elseif ($data['setting_type'] === 'number'): ?>
                             <input type="number" step="any" class="form-control" name="<?= $key ?>" value="<?= htmlspecialchars($data['setting_value']) ?>">
                        <?php else: ?>
                            <input type="text" class="form-control" name="<?= $key ?>" value="<?= htmlspecialchars($data['setting_value']) ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
          </form>
        </section>
      </div>

      <div class="tab-pane fade" id="deliveryPane" role="tabpanel">
        <section class="content-card">
          <div class="content-card-header">
            <div class="left">
              <h2 class="section-title mb-1">Deliverable Barangays</h2>
              <p class="text-muted small mb-0">Manage delivery locations and fees.</p>
            </div>
            <div class="right">
              <button class="btn btn-success btn-sm" id="addBarangayBtn">
                <i class="bi bi-plus-circle"></i> Add Barangay
              </button>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover table-vcenter modern-table" id="barangayTable">
              <thead>
                <tr>
                  <th>Barangay Name</th>
                  <th>Delivery Fee (₱)</th>
                  <th>Status</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($barangays_query && $barangays_query->num_rows > 0): ?>
                    <?php while($b = $barangays_query->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($b['barangay_name']) ?></td>
                            <td>₱<?= number_format($b['delivery_fee'], 2) ?></td>
                            <td>
                                <?php if($b['is_active']): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-secondary btn-edit-barangay" data-id="<?= $b['barangay_id'] ?>">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-delete-barangay" data-id="<?= $b['barangay_id'] ?>">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center text-muted">No barangays found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
      </div>

    </div>
  </main>
</div>

<div class="modal fade" id="barangayModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="barangayForm">
        <div class="modal-header">
          <h5 class="modal-title" id="barangayModalLabel">Add Barangay</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="barangay_id" name="barangay_id" value="">
          
          <div class="mb-3">
            <label class="form-label">Barangay Name</label>
            <select class="form-select" id="barangay_name" name="barangay_name" required>
                <option value="" disabled selected>Select Barangay</option>
            </select>
            <div id="barangayLoading" class="form-text text-muted mt-1" style="display:none;">
                <span class="spinner-border spinner-border-sm" style="width: 0.8rem; height: 0.8rem;"></span> Loading barangays...
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Delivery Fee (₱)</label>
            <input type="number" step="0.01" class="form-control" id="delivery_fee" name="delivery_fee" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-select" id="is_active" name="is_active">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
    /* Override Nav Tabs to match Manage Menu style */
    .nav-tabs { border-bottom: none; gap: 10px; }
    .nav-tabs .nav-link { 
        border-radius: 8px; 
        color: #6b7280; 
        font-weight: 500; 
        border: none !important; 
        background: transparent; 
        transition: all 0.2s; 
    }
    .nav-tabs .nav-link:hover { background: #e5e7eb; color: #111827; }
    .nav-tabs .nav-link.active { 
        background: #4f46e5 !important; 
        color: #fff !important; 
        box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    const barangayModal = new bootstrap.Modal(document.getElementById('barangayModal'));

    // --- FIXED: PERSIST ACTIVE TAB ---
    const activeTab = localStorage.getItem('activeSettingsTab');
    if (activeTab) {
        // Find the button triggering the tab
        const triggerEl = document.querySelector(`button[data-bs-target="${activeTab}"]`);
        if (triggerEl) {
            const tabInstance = new bootstrap.Tab(triggerEl);
            tabInstance.show();
        }
    }

    // Save the active tab whenever a tab is clicked
    const tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabEls.forEach(tabEl => {
        tabEl.addEventListener('shown.bs.tab', function (event) {
            // event.target is the newly active tab button
            const target = event.target.getAttribute('data-bs-target');
            localStorage.setItem('activeSettingsTab', target);
        });
    });
    // --- END TAB PERSISTENCE ---


    // --- FETCH BARANGAYS FROM PSGC API ---
    // Nasugbu PSGC Code: 041019000
    function fetchNasugbuBarangays() {
        const select = document.getElementById('barangay_name');
        const loadingText = document.getElementById('barangayLoading');
        
        // Prevent re-fetching if already populated
        if (select.options.length > 1) return; 

        loadingText.style.display = 'block';

        fetch('https://psgc.gitlab.io/api/cities-municipalities/041019000/barangays/')
            .then(response => response.json())
            .then(data => {
                // Sort alphabetically
                data.sort((a, b) => a.name.localeCompare(b.name));
                
                data.forEach(b => {
                    const option = document.createElement('option');
                    option.value = b.name;
                    option.textContent = b.name;
                    select.appendChild(option);
                });
            })
            .catch(err => console.error('Failed to fetch barangays:', err))
            .finally(() => {
                loadingText.style.display = 'none';
            });
    }

    // Fetch immediately on load so it's ready when they click
    fetchNasugbuBarangays();


    // --- SAVE SETTINGS ---
    document.getElementById('settingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // FIX: Find the button globally because it is outside the <form> tags
        const btn = document.querySelector('button[form="settingsForm"]');
        
        let originalText = '';
        if (btn) {
            originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
        }

        const formData = new FormData(this);

        fetch('actions/save_settings.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                // RELOAD PAGE AFTER CLICKING OK
                Swal.fire('Saved', data.message, 'success').then(() => {
                    location.reload(); 
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Failed to save settings', 'error'))
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    });

    // --- BARANGAY CRUD ---
    document.getElementById('addBarangayBtn').addEventListener('click', function() {
        document.getElementById('barangayForm').reset();
        document.getElementById('barangay_id').value = '';
        document.getElementById('barangayModalLabel').textContent = 'Add Barangay';
        
        // Ensure dropdown is reset to default
        document.getElementById('barangay_name').value = "";
        
        barangayModal.show();
    });

    document.getElementById('barangayForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('actions/save_barangay.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                Swal.fire('Success', data.message, 'success').then(() => location.reload());
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
                if(data.error) {
                    Swal.fire('Error', data.error, 'error');
                } else {
                    document.getElementById('barangay_id').value = data.barangay_id;
                    
                    // Set the dropdown value. If the value isn't in the list (rare),
                    // we might need to append it dynamically, but since we fetch ALL, it should be there.
                    document.getElementById('barangay_name').value = data.barangay_name;
                    
                    document.getElementById('delivery_fee').value = data.delivery_fee;
                    document.getElementById('is_active').value = data.is_active;
                    document.getElementById('barangayModalLabel').textContent = 'Edit Barangay';
                    barangayModal.show();
                }
            });
        });
    });

    document.querySelectorAll('.btn-delete-barangay').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            Swal.fire({
                title: 'Are you sure?',
                text: "This will remove the barangay from delivery options.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('barangay_id', id);
                    fetch('actions/delete_barangay.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(data => {
                        if(data.success) {
                            Swal.fire('Deleted!', 'Barangay has been deleted.', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
                }
            });
        });
    });
});
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>