<?php
include __DIR__ . '/includes/header.php';
?>
<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    <section class="content-card mb-4">
      <div class="content-card-header">
        <div class="left">
          <h2 class="page-title mb-1">Staff Management</h2>
          <p class="text-muted small mb-0">Add and maintain users with access to the system.</p>
        </div>
        <div class="right header-actions">
            </div>
      </div>

      <form class="row g-3" id="addStaffForm">
        <div class="col-12 col-md-4">
          <label class="form-label">First Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="first_name" placeholder="Juan" required>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Last Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="last_name" placeholder="Dela Cruz" required>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Role <span class="text-danger">*</span></label>
          <select class="form-select" name="role" required>
            <option value="staff">Staff</option>
            <option value="customer">Customer</option>
            <option value="admin">Admin (Full Access)</option>
          </select>
          <div class="form-text small text-muted">Controls login permissions.</div>
        </div>
        
        <div class="col-12 col-md-4">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input type="email" class="form-control" name="email" placeholder="user@example.com" required>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Phone</label>
          <div class="input-group">
            <span class="input-group-text">+63</span>
            <input
              type="text"
              class="form-control"
              name="phone"
              id="phone"
              placeholder="9XX XXX XXXX"
              inputmode="numeric"
              maxlength="10">
          </div>
        </div>

        <div class="col-12 col-md-4"></div> 

        <div class="col-12 col-md-4">
          <label class="form-label">Password <span class="text-danger">*</span></label>
          <input type="password" class="form-control" name="password" placeholder="••••••••" required>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
          <input type="password" class="form-control" name="password2" placeholder="••••••••" required>
        </div>

        <div class="col-12 text-start mt-4">
          <button type="submit" class="btn btn-success w-100-mobile px-4">
            <i class="bi bi-plus-circle me-1"></i> Add User
          </button>
        </div>
      </form>
    </section>

    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2 class="section-title mb-1">Current Staff</h2>
          <p class="text-muted small mb-0">Active team members with system access.</p>
        </div>
        <div class="right header-actions">
          <select class="form-select form-select-sm w-100-mobile" id="roleFilter">
            <option value="">All roles</option>
            <option value="staff">Staff</option>
            <option value="customer">Customer</option>
            <option value="admin">Admin</option>
          </select>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover modern-table" id="staffTable">
          <thead>
            <tr>
              <th>Staff Member</th>
              <th>Role</th>
              <th>Email</th>
              <th>Contact</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody id="staffTbody"></tbody>
        </table>
      </div>
    </section>
  </main>
</div>

<style>
  /* Global Background & Layout */
  body { 
    background-color: #f3f4f6; 
  }
  
  .main-content {
    min-height: 100vh;
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
    margin-left: 220px; /* Desktop sidebar offset */
    transition: margin-left 0.3s ease;
  }

  /* Modern cards */
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

  .content-card-header .left h2.page-title {
    font-size: 1.25rem;
    font-weight: 600;
  }

  .content-card-header .left h2.section-title {
    font-size: 1.1rem;
    font-weight: 600;
  }

  .content-card-header .left p {
    margin: 0;
    font-size: 0.8rem;
    color: #6b7280;
  }

  .content-card-header .right .btn {
    border-radius: 999px;
    font-size: 0.85rem;
  }

  /* Form styling */
  .form-label {
    font-size: 0.9rem;
    font-weight: 500;
    color: #374151;
  }

  .form-control,
  .form-select {
    font-size: 0.9rem;
    border-radius: 10px;
    border-color: #e5e7eb;
    padding: 0.5rem 0.75rem;
  }

  .form-control:focus,
  .form-select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
  }

  .input-group-text {
    border-radius: 10px 0 0 10px;
    background-color: #f9fafb;
    border-color: #e5e7eb;
    font-size: 0.9rem;
  }
  
  /* Fix input group radius when attached */
  .input-group .form-control {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  /* Table Styles */
  .modern-table thead th {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 600;
    color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap; /* Prevent header wrapping */
  }

  .modern-table tbody td {
    font-size: 0.9rem;
    vertical-align: middle;
    white-space: nowrap; /* Keep data on one line mostly */
  }

  .table-hover tbody tr:hover {
    background-color: #f9fafb;
  }

  /* Role badges */
  .badge-role-admin {
    background-color: #fee2e2;
    color: #b91c1c;
    border-radius: 999px;
    font-size: 0.75rem;
    padding: 0.25rem 0.7rem;
    font-weight: 600;
  }

  .badge-role-staff {
    background-color: #dcfce7;
    color: #15803d;
    border-radius: 999px;
    font-size: 0.75rem;
    padding: 0.25rem 0.7rem;
    font-weight: 600;
  }

  .badge-role-customer {
    background-color: #e0f2fe;
    color: #0369a1;
    border-radius: 999px;
    font-size: 0.75rem;
    padding: 0.25rem 0.7rem;
    font-weight: 600;
  }

  /* Name cell: avatar + text */
  .staff-name-cell {
    display: flex;
    align-items: center;
    gap: 0.6rem;
  }

  .staff-avatar {
    width: 32px;
    height: 32px;
    border-radius: 999px;
    background: #eef2ff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    color: #4f46e5;
    flex-shrink: 0;
  }

  .staff-name-main {
    font-weight: 600;
    font-size: 0.92rem;
    color: #111827;
  }

  .staff-name-sub {
    font-size: 0.78rem;
    color: #9ca3af;
  }

  /* Role filter */
  #roleFilter {
    border-radius: 999px;
    border-color: #e5e7eb;
    font-size: 0.8rem;
    min-width: 140px;
  }

  /* Buttons */
  .btn { border-radius: 999px; }
  .btn-group-sm .btn { border-radius: 999px; }

  /* Simple Modal Styling */
  .simple-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5); /* Standard dark overlay */
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    backdrop-filter: blur(2px);
  }
  .simple-modal {
    width: 680px;
    max-width: 94%;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.35);
    overflow: hidden;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
  }
  .simple-modal .hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
    flex-shrink: 0;
  }
  .simple-modal .hdr strong {
    font-size: 1rem;
    color: #111827;
  }
  .simple-modal .body {
    padding: 20px;
    overflow-y: auto;
  }
  .simple-modal .ftr {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    padding: 12px 20px;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    flex-shrink: 0;
  }

  /* RESPONSIVE BREAKPOINTS */
  @media (max-width: 992px) {
    .main-content {
      margin-left: 0;
    }
  }

  @media (max-width: 768px) {
    .content-card-header {
      flex-direction: column;
      align-items: stretch;
      gap: 15px;
    }
    
    .header-actions {
      width: 100%;
    }
    
    .w-100-mobile {
      width: 100% !important;
    }
    
    /* Ensure table fits horizontally */
    .table-responsive {
      margin-left: -20px;
      margin-right: -20px;
      width: calc(100% + 40px);
      padding-left: 20px;
      padding-right: 20px;
    }
    
    /* Adjust modal for mobile */
    .simple-modal .body {
      padding: 16px;
    }
  }
</style>

<div class="simple-modal-backdrop" id="editBackdrop">
  <div class="simple-modal">
    <div class="hdr">
      <strong>Edit Staff Member</strong>
      <button type="button" id="editClose" class="btn btn-outline-secondary btn-sm">Close</button>
    </div>
    <form id="editForm" style="display: flex; flex-direction: column; height: 100%;">
      <input type="hidden" name="user_id" id="edit_user_id">
      <div class="body">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label">First Name</label>
            <input class="form-control" name="first_name" id="edit_first_name" required>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Last Name</label>
            <input class="form-control" name="last_name" id="edit_last_name" required>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Email</label>
            <input class="form-control" name="email" id="edit_email" required>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Phone</label>
            <div class="input-group">
              <span class="input-group-text">+63</span>
              <input
                class="form-control"
                name="phone"
                id="edit_phone"
                placeholder="9XX XXX XXXX"
                inputmode="numeric"
                maxlength="10">
            </div>
          </div>
          <div class="col-12">
            <label class="form-label">Role</label>
            <select class="form-select" name="role" id="edit_role">
              <option value="staff">Staff</option>
              <option value="customer">Customer</option>
              <option value="admin">Admin (Full Access)</option>
            </select>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">New Password <small class="text-muted">(optional)</small></label>
            <input type="password" class="form-control" name="new_password" id="edit_new_password" placeholder="Leave blank to keep">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Confirm New Password</label>
            <input type="password" class="form-control" name="new_password2" id="edit_new_password2" placeholder="Repeat new password">
          </div>
        </div>
      </div>
      <div class="ftr">
        <button type="button" class="btn btn-outline-secondary" id="editCancel">Cancel</button>
        <button type="submit" class="btn btn-success">Save changes</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('addStaffForm');
  // const topBtn = document.getElementById('saveStaffTopBtn'); // ID removed
  const tbody = document.getElementById('staffTbody');
  const roleFilter = document.getElementById('roleFilter');

  const editBackdrop = document.getElementById('editBackdrop');
  const editClose = document.getElementById('editClose');
  const editCancel = document.getElementById('editCancel');
  const editForm = document.getElementById('editForm');

  const phoneInput = document.getElementById('phone');
  const editPhoneInput = document.getElementById('edit_phone');

  function showAlert(type, msg){
    let icon = 'info';
    let title = 'Notice';

    if (type === 'success') {
      icon = 'success';
      title = 'Success';
    } else if (type === 'danger' || type === 'error') {
      icon = 'error';
      title = 'Error';
    } else if (type === 'warning') {
      icon = 'warning';
      title = 'Warning';
    }

    Swal.fire({
      icon: icon,
      title: title,
      text: msg,
      timer: 2200,
      showConfirmButton: false,
      timerProgressBar: true
    });
  }

  // Force only digits and max 10 digits in the visible phone input
  function enforceLocalPhone(el) {
    if (!el) return;
    el.addEventListener('input', () => {
      let digits = el.value.replace(/\D/g, '');
      if (digits.length > 10) digits = digits.slice(0, 10);
      el.value = digits;
    });
  }

  enforceLocalPhone(phoneInput);
  enforceLocalPhone(editPhoneInput);

  // Normalize phone to +63XXXXXXXXXX before sending to backend
  function normalizePhone(value) {
    if (!value) return '';
    let digits = value.replace(/[^\d]/g, '');

    // handle pasted values with 63 or 09 prefix
    if (digits.startsWith('63')) {
      digits = digits.slice(2);
    } else if (digits.startsWith('0')) {
      digits = digits.slice(1);
    }

    if (digits.length > 10) {
      digits = digits.slice(0, 10);
    }

    if (!digits) return '';
    return '+63' + digits;
  }

  function getLocalDigits(value) {
    if (!value) return '';
    return value.replace(/\D/g, '');
  }

  function badgeForRole(role) {
    role = (role||'').toLowerCase();
    const label =
      role === 'admin' ? 'Admin' :
      role === 'customer' ? 'Customer' : 'Staff';
    const klass =
      role === 'admin' ? 'badge-role-admin' :
      (role === 'customer' ? 'badge-role-customer' : 'badge-role-staff');
    return {label, klass};
  }

  function initials(first, last) {
    const f = (first || '').trim();
    const l = (last || '').trim();
    if (!f && !l) return '?';
    const fi = f ? f[0] : '';
    const li = l ? l[0] : '';
    return (fi + li).toUpperCase();
  }

  function rowHTML(s){
    const r = badgeForRole(s.role);
    const first = s.first_name || '';
    const last  = s.last_name || '';
    const name  = `${first} ${last}`.trim() || 'Unnamed User';
    const avatar = initials(first, last);
    return `
      <tr data-id="${s.user_id}" data-role="${(s.role || 'staff').toLowerCase()}">
        <td>
          <div class="staff-name-cell">
            <div class="staff-avatar">${avatar}</div>
            <div>
              <div class="staff-name-main">${name}</div>
              <div class="staff-name-sub">${s.email || '&nbsp;'}</div>
            </div>
          </div>
        </td>
        <td><span class="${r.klass}">${r.label}</span></td>
        <td class="text-nowrap">${s.email || '—'}</td>
        <td class="text-nowrap">${s.phone || '—'}</td>
        <td class="text-end text-nowrap">
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary btn-edit"><i class="bi bi-pencil"></i> Edit</button>
            <button class="btn btn-outline-danger btn-remove"><i class="bi bi-trash"></i> </button>
          </div>
        </td>
      </tr>
    `;
  }

  function applyRoleFilter() {
    if (!roleFilter) return;
    const selected = (roleFilter.value || '').toLowerCase();

    [...tbody.querySelectorAll('tr[data-id]')].forEach(tr => {
      const rowRole = (tr.getAttribute('data-role') || '').toLowerCase();
      tr.style.display = (!selected || selected === rowRole) ? '' : 'none';
    });
  }

  async function loadStaff(){
    tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4">Loading…</td></tr>`;
    try{
      const res = await fetch('actions/list_staff.php');
      const data = await res.json();
      if (data.status !== 'ok') throw new Error(data.message || 'Failed to load');
      if (!Array.isArray(data.rows)) data.rows = [];
      tbody.innerHTML = data.rows.map(rowHTML).join('') || `<tr><td colspan="5" class="text-center py-4 text-muted">No staff found.</td></tr>`;
      applyRoleFilter();
    }catch(e){
      tbody.innerHTML = `<tr><td colspan="5" class="text-danger text-center py-4">${e.message}</td></tr>`;
    }
  }

  // Add new staff
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    if ((fd.get('password')||'') !== (fd.get('password2')||'')) {
      showAlert('danger','Passwords do not match.');
      return;
    }

    const rawPhone = fd.get('phone') || '';
    const localDigits = getLocalDigits(rawPhone);

    // Phone is optional, but if user entered something, it must be exactly 10 digits
    if (localDigits && localDigits.length !== 10) {
      showAlert('danger','Phone number must be exactly 10 digits after +63.');
      return;
    }

    const fullPhone = normalizePhone(rawPhone);
    fd.set('phone', fullPhone);

    try{
      const res = await fetch('actions/add_staff.php', { method:'POST', body:fd });
      const ct = res.headers.get('content-type')||'';
      const data = ct.includes('json') ? await res.json() : {status:'error',message:await res.text()};
      if (data.status !== 'ok') throw new Error(data.message || 'Failed to add staff');
      showAlert('success','Staff added successfully.');
      form.reset();
      await loadStaff();
    }catch(err){
      showAlert('danger', err.message);
    }
  });

  // Edit / Remove actions
  tbody.addEventListener('click', async (e) => {
    const tr = e.target.closest('tr[data-id]');
    if (!tr) return;
    const id = tr.getAttribute('data-id');

    if (e.target.closest('.btn-remove')) {
      // SweetAlert2 confirmation
      Swal.fire({
        title: 'Remove this staff member?',
        text: 'This action is permanent.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, remove',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33'
      }).then(async (result) => {
        if (!result.isConfirmed) return;
        try{
          const fd = new FormData();
          fd.append('user_id', id);
          const res = await fetch('actions/delete_staff.php', { method:'POST', body:fd });
          const data = await res.json();
          if (data.status !== 'ok') throw new Error(data.message || 'Delete failed');
          showAlert('success','Staff removed.');
          tr.remove();
          if (!tbody.children.length) loadStaff();
        }catch(err){
          showAlert('danger', err.message);
        }
      });

      return; // don’t fall through to edit handler
    }

    if (e.target.closest('.btn-edit')) {
      try{
        const res = await fetch('actions/list_staff.php?user_id='+encodeURIComponent(id));
        const data = await res.json();
        if (data.status !== 'ok' || !data.row) throw new Error('Failed to fetch staff details');

        // Fill modal
        editForm.reset();
        document.getElementById('edit_user_id').value = data.row.user_id;
        document.getElementById('edit_first_name').value = data.row.first_name || '';
        document.getElementById('edit_last_name').value = data.row.last_name || '';
        document.getElementById('edit_email').value = data.row.email || '';
        document.getElementById('edit_role').value = (data.row.role || 'staff');

        // Strip leading +63 when showing in the input (prefix is fixed in UI)
        let dbPhone = data.row.phone || '';
        dbPhone = dbPhone.replace(/^\+?63/, '');
        dbPhone = dbPhone.replace(/\D/g, '');
        if (dbPhone.length > 10) dbPhone = dbPhone.slice(0,10);
        document.getElementById('edit_phone').value = dbPhone;

        editBackdrop.style.display = 'flex';
      }catch(err){
        showAlert('danger', err.message);
      }
    }
  });

  // Modal actions
  function closeEdit(){ editBackdrop.style.display='none'; }
  editClose.addEventListener('click', closeEdit);
  editCancel.addEventListener('click', closeEdit);
  editBackdrop.addEventListener('click', (e)=>{ if(e.target===editBackdrop) closeEdit(); });

  editForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(editForm);
    if ((fd.get('new_password')||'') !== (fd.get('new_password2')||'')) {
      showAlert('danger','New passwords do not match.');
      return;
    }

    const rawPhone = fd.get('phone') || '';
    const localDigits = getLocalDigits(rawPhone);

    if (localDigits && localDigits.length !== 10) {
      showAlert('danger','Phone number must be exactly 10 digits after +63.');
      return;
    }

    const fullPhone = normalizePhone(rawPhone);
    fd.set('phone', fullPhone);

    try{
      const res = await fetch('actions/update_staff.php', { method:'POST', body:fd });
      const data = await res.json();
      if (data.status !== 'ok') throw new Error(data.message || 'Update failed');
      showAlert('success','Changes saved.');
      closeEdit();
      await loadStaff();
    }catch(err){
      showAlert('danger', err.message);
    }
  });

  // Role filter change
  if (roleFilter) {
    roleFilter.addEventListener('change', applyRoleFilter);
  }

  // Initial load
  loadStaff();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>