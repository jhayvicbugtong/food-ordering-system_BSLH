<?php
include __DIR__ . '/includes/header.php';
?>
<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    <!-- ADD STAFF -->
    <section class="content-card mb-4">
      <div class="content-card-header">
        <div class="left">
          <h2>Staff Management</h2>
          <p>Kitchen, riders, front of house</p>
        </div>
        <div class="right">
          <button class="btn btn-success" id="saveStaffTopBtn">
            <i class="bi bi-person-plus"></i> Save Staff
          </button>
        </div>
      </div>

      <div id="staffAlert" class="alert d-none" role="alert"></div>

      <form class="row g-3" id="addStaffForm">
        <div class="col-md-4">
          <label class="form-label">Full Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="full_name" placeholder="Juan Dela Cruz" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Role <span class="text-danger">*</span></label>
          <select class="form-select" name="staff_role" required>
            <option value="kitchen">Cook / Kitchen</option>
            <option value="cashier">Cashier / Front Desk</option>
            <option value="rider">Delivery Rider</option>
            <option value="manager">Manager</option>
          </select>
          <div class="form-text">Stored as sub-role; main role in users.role = "staff".</div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Shift</label>
          <select class="form-select" name="shift">
            <option value="morning">Morning (6am - 2pm)</option>
            <option value="mid">Mid (10am - 6pm)</option>
            <option value="evening">Evening (2pm - 10pm)</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Contact #</label>
          <input type="text" class="form-control" name="phone" placeholder="+63 9XX XXX XXXX">
        </div>

        <div class="col-md-8">
          <label class="form-label">Notes</label>
          <input type="text" class="form-control" name="notes" placeholder="Allergic to peanuts, prefers delivery shifts, etc.">
        </div>

        <!-- Credentials -->
        <div class="col-md-4">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input type="email" class="form-control" name="email" placeholder="staff@example.com" required>
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

    <!-- CURRENT STAFF LIST -->
    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Current Staff</h2>
          <p>Active team members</p>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover" id="staffTable">
          <thead>
            <tr>
              <th>Name</th>
              <th>Role</th>
              <th>Shift</th>
              <th>Contact</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="staffTbody"><!-- filled by JS --></tbody>
        </table>
      </div>
    </section>
  </main>
</div>

<!-- SIMPLE EDIT MODAL -->
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
      <input type="hidden" name="id" id="edit_id">
      <div class="body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input class="form-control" name="full_name" id="edit_full_name" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input class="form-control" name="email" id="edit_email" required>
          </div>

          <div class="col-md-4">
            <label class="form-label">Role</label>
            <select class="form-select" name="staff_role" id="edit_staff_role">
              <option value="kitchen">Cook / Kitchen</option>
              <option value="cashier">Cashier / Front Desk</option>
              <option value="rider">Delivery Rider</option>
              <option value="manager">Manager</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Shift</label>
            <select class="form-select" name="shift" id="edit_shift">
              <option value="morning">Morning (6am - 2pm)</option>
              <option value="mid">Mid (10am - 6pm)</option>
              <option value="evening">Evening (2pm - 10pm)</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Contact #</label>
            <input class="form-control" name="phone" id="edit_phone">
          </div>

          <div class="col-12">
            <label class="form-label">Notes</label>
            <input class="form-control" name="notes" id="edit_notes">
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
      role === 'kitchen' ? 'Kitchen' :
      role === 'cashier' ? 'Front Desk' :
      role === 'rider'   ? 'Delivery Rider' :
      role === 'manager' ? 'Manager' : 'Staff';
    const klass =
      role === 'manager' ? 'badge-danger' :
      (role === 'cashier' ? 'badge-warning' : 'badge-success');
    return {label, klass};
  }

  function shiftText(code){
    return code==='morning' ? 'Morning (6am - 2pm)' :
           code==='mid'     ? 'Mid (10am - 6pm)' :
           code==='evening' ? 'Evening (2pm - 10pm)' : '—';
  }

  function monthYear(dstr){
    if (!dstr) return '—';
    const d = new Date(dstr.replace(' ','T'));
    const opts = {month:'short', year:'numeric'};
    if (isNaN(d.getTime())) return '—';
    return d.toLocaleString(undefined, opts);
  }

  function rowHTML(s){
    const r = badgeForRole(s.staff_role);
    const started = s.started_at ? `Started: ${monthYear(s.started_at)}` : '';
    return `
      <tr data-id="${s.id}">
        <td>
          <strong>${s.name || '—'}</strong><br>
          <small class="text-muted">${started}</small>
        </td>
        <td><span class="badge ${r.klass}">${r.label}</span></td>
        <td>${shiftText(s.shift)}</td>
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
  topBtn.addEventListener('click', () => form.requestSubmit());
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
      if (!confirm('Remove this staff member?')) return;
      try{
        const fd = new FormData(); fd.append('id', id);
        const res = await fetch('actions/delete_staff.php', { method:'POST', body:fd });
        const data = await res.json();
        if (data.status !== 'ok') throw new Error(data.message || 'Delete failed');
        showAlert('success','Staff removed.');
        tr.remove();
        if (!tbody.children.length) loadStaff();
      }catch(err){ showAlert('danger', err.message); }
    }

    if (e.target.classList.contains('btn-edit')) {
      // Load the single row data (reuse list endpoint and pick one) or read from DOM if you prefer
      try{
        const res = await fetch('actions/list_staff.php?id='+encodeURIComponent(id));
        const data = await res.json();
        if (data.status !== 'ok' || !data.row) throw new Error('Failed to fetch staff details');

        // Fill modal
        editForm.reset();
        document.getElementById('edit_id').value = data.row.id;
        document.getElementById('edit_full_name').value = data.row.name || '';
        document.getElementById('edit_email').value = data.row.email || '';
        document.getElementById('edit_staff_role').value = (data.row.staff_role || 'kitchen');
        document.getElementById('edit_shift').value = (data.row.shift || 'morning');
        document.getElementById('edit_phone').value = data.row.phone || '';
        document.getElementById('edit_notes').value = data.row.notes || '';

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
