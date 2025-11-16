<?php
include __DIR__ . '/includes/header.php';
?>
<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    <section class="content-card mb-4">
      <div class="content-card-header">
        <div class="left">
          <h2>Staff Management</h2>
          <p>Add new staff members (kitchen, driver, admin, etc.)</p>
        </div>
        <div class="right">
          <button class="btn btn-success" id="saveStaffTopBtn" type="submit" form="addStaffForm">
            <i class="bi bi-person-plus"></i> Save Staff
          </button>
        </div>
      </div>

      <div id="staffAlert" class="alert d-none" role="alert"></div>

      <form class="row g-3" id="addStaffForm">
        <div class="col-md-4">
          <label class="form-label">First Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="first_name" placeholder="Juan" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Last Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="last_name" placeholder="Dela Cruz" required>
        </div>
         <div class="col-md-4">
          <label class="form-label">Role <span class="text-danger">*</span></label>
          <select class="form-select" name="role" required>
            <option value="staff">Staff (Kitchen, Cashier)</option>
            <option value="driver">Driver</option>
            <option value="admin">Admin (Full Access)</option>
          </select>
          <div class="form-text">This controls their login permissions.</div>
        </div>
        
        <div class="col-md-4">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input type="email" class="form-control" name="email" placeholder="staff@example.com" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Phone</label>
          <input type="text" class="form-control" name="phone" placeholder="+63 9XX XXX XXXX">
        </div>

        <div class="col-md-4">
          <label class="form-label">Password <span class="text-danger">*</span></label>
          <input type="password" class="form-control" name="password" placeholder="••••••••" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
          <input type="password" class="form-control" name="password2" placeholder="••••••••" required>
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Add Staff Member
          </button>
        </div>
      </form>
    </section>

    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Current Staff</h2>
          <p>Active team members with system access</p>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover" id="staffTable">
          <thead>
            <tr>
              <th>Name</th>
              <th>Role</th>
              <th>Email</th>
              <th>Contact</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="staffTbody"></tbody>
        </table>
      </div>
    </section>
  </main>
</div>

<style>
.simple-modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.45);display:none;align-items:center;justify-content:center;z-index:1000}
.simple-modal{width:680px;max-width:94%;background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden}
.simple-modal .hdr{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid #eee}
.simple-modal .body{padding:16px}
.simple-modal .ftr{display:flex;gap:8px;justify-content:flex-end;padding:12px 16px;border-top:1px solid #eee}
</style>
<div class="simple-modal-backdrop" id="editBackdrop">
  <div class="simple-modal">
    <div class="hdr">
      <strong>Edit Staff Member</strong>
      <button type="button" id="editClose" class="btn btn-outline-secondary btn-sm">Close</button>
    </div>
    <form id="editForm">
      <input type="hidden" name="user_id" id="edit_user_id">
      <div class="body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">First Name</label>
            <input class="form-control" name="first_name" id="edit_first_name" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Last Name</label>
            <input class="form-control" name="last_name" id="edit_last_name" required>
          </div>
           <div class="col-md-6">
            <label class="form-label">Email</label>
            <input class="form-control" name="email" id="edit_email" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input class="form-control" name="phone" id="edit_phone">
          </div>
          <div class="col-md-12">
            <label class="form-label">Role</label>
            <select class="form-select" name="role" id="edit_role">
              <option value="staff">Staff (Kitchen, Cashier)</option>
              <option value="driver">Driver</option>
              <option value="admin">Admin (Full Access)</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">New Password (optional)</label>
            <input type="password" class="form-control" name="new_password" id="edit_new_password" placeholder="Leave blank to keep current">
          </div>
          <div class="col-md-6">
            <label class="form-label">Confirm</label>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('addStaffForm');
  const topBtn = document.getElementById('saveStaffTopBtn');
  const alertBox = document.getElementById('staffAlert');
  const tbody = document.getElementById('staffTbody');

  const editBackdrop = document.getElementById('editBackdrop');
  const editClose = document.getElementById('editClose');
  const editCancel = document.getElementById('editCancel');
  const editForm = document.getElementById('editForm');

  function showAlert(type, msg){
    alertBox.className = 'alert alert-' + type;
    alertBox.textContent = msg;
    alertBox.classList.remove('d-none');
    setTimeout(()=> alertBox.classList.add('d-none'), 4200);
  }

  function badgeForRole(role) {
    role = (role||'').toLowerCase();
    const label =
      role === 'admin' ? 'Admin' :
      role === 'driver' ? 'Driver' : 'Staff';
    const klass =
      role === 'admin' ? 'badge-danger' :
      (role === 'driver' ? 'badge-info' : 'badge-success');
    return {label, klass};
  }

  function rowHTML(s){
    const r = badgeForRole(s.role);
    return `
      <tr data-id="${s.user_id}">
        <td><strong>${s.first_name || ''} ${s.last_name || ''}</strong></td>
        <td><span class="badge ${r.klass}">${r.label}</span></td>
        <td>${s.email || '—'}</td>
        <td>${s.phone || '—'}</td>
        <td>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary btn-edit">Edit</button>
            <button class="btn btn-outline-danger btn-remove">Remove</button>
          </div>
        </td>
      </tr>
    `;
  }

  async function loadStaff(){
    tbody.innerHTML = `<tr><td colspan="5">Loading…</td></tr>`;
    try{
      const res = await fetch('actions/list_staff.php');
      const data = await res.json();
      if (data.status !== 'ok') throw new Error(data.message || 'Failed to load');
      if (!Array.isArray(data.rows)) data.rows = [];
      tbody.innerHTML = data.rows.map(rowHTML).join('') || `<tr><td colspan="5">No staff found.</td></tr>`;
    }catch(e){
      tbody.innerHTML = `<tr><td colspan="5" class="text-danger">${e.message}</td></tr>`;
    }
  }

  // Add new staff (use API then reload list)
  // topBtn.addEventListener('click', () => form.requestSubmit()); // This is handled by the button's form attribute
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    if ((fd.get('password')||'') !== (fd.get('password2')||'')) {
      showAlert('danger','Passwords do not match.'); return;
    }
    try{
      const res = await fetch('actions/add_staff.php', { method:'POST', body:fd });
      const ct = res.headers.get('content-type')||'';
      const data = ct.includes('json') ? await res.json() : {status:'error',message:await res.text()};
      if (data.status !== 'ok') throw new Error(data.message || 'Failed to add staff');
      showAlert('success','Staff added successfully.');
      form.reset();
      await loadStaff();
    }catch(err){ showAlert('danger', err.message); }
  });

  // Edit / Remove actions
  tbody.addEventListener('click', async (e) => {
    const tr = e.target.closest('tr[data-id]');
    if (!tr) return;
    const id = tr.getAttribute('data-id');

    if (e.target.classList.contains('btn-remove')) {
      if (!confirm('Remove this staff member? This is permanent.')) return;
      try{
        const fd = new FormData(); fd.append('user_id', id);
        const res = await fetch('actions/delete_staff.php', { method:'POST', body:fd });
        const data = await res.json();
        if (data.status !== 'ok') throw new Error(data.message || 'Delete failed');
        showAlert('success','Staff removed.');
        tr.remove();
        if (!tbody.children.length) loadStaff();
      }catch(err){ showAlert('danger', err.message); }
    }

    if (e.target.classList.contains('btn-edit')) {
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
        document.getElementById('edit_phone').value = data.row.phone || '';

        editBackdrop.style.display = 'flex';
      }catch(err){ showAlert('danger', err.message); }
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
      showAlert('danger','New passwords do not match.'); return;
    }
    try{
      const res = await fetch('actions/update_staff.php', { method:'POST', body:fd });
      const data = await res.json();
      if (data.status !== 'ok') throw new Error(data.message || 'Update failed');
      showAlert('success','Changes saved.');
      closeEdit();
      await loadStaff();
    }catch(err){ showAlert('danger', err.message); }
  });

  // Initial load
  loadStaff();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>