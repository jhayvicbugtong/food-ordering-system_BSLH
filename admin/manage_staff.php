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
          <button class="btn btn-success">Save Staff</button>
        </div>
      </div>

      <form class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" placeholder="Juan Dela Cruz">
        </div>

        <div class="col-md-4">
          <label class="form-label">Role</label>
          <select class="form-select">
            <option>Cook / Kitchen</option>
            <option>Cashier / Front Desk</option>
            <option>Delivery Rider</option>
            <option>Manager</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Shift</label>
          <select class="form-select">
            <option>Morning (6am - 2pm)</option>
            <option>Mid (10am - 6pm)</option>
            <option>Evening (2pm - 10pm)</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Contact #</label>
          <input type="text" class="form-control" placeholder="+63 9XX XXX XXXX">
        </div>

        <div class="col-md-8">
          <label class="form-label">Notes</label>
          <input type="text" class="form-control" placeholder="Allergic to peanuts, prefers delivery shifts, etc.">
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-success">Add Staff Member</button>
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
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Name</th>
              <th>Role</th>
              <th>Shift</th>
              <th>Contact</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <strong>Rex Perez</strong><br>
                <small class="text-muted">Started: Jan 2025</small>
              </td>
              <td><span class="badge badge-success">Delivery Rider</span></td>
              <td>Evening (2pm - 10pm)</td>
              <td>+63 912 345 6789</td>
              <td>
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-secondary">Edit</button>
                  <button class="btn btn-outline-danger">Remove</button>
                </div>
              </td>
            </tr>

            <tr>
              <td>
                <strong>Janelle Ramos</strong><br>
                <small class="text-muted">Started: Dec 2024</small>
              </td>
              <td><span class="badge badge-warning">Front Desk</span></td>
              <td>Mid (10am - 6pm)</td>
              <td>+63 921 222 1111</td>
              <td>
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-secondary">Edit</button>
                  <button class="btn btn-outline-danger">Remove</button>
                </div>
              </td>
            </tr>

            <tr>
              <td>
                <strong>Carlo Medina</strong><br>
                <small class="text-muted">Started: Oct 2024</small>
              </td>
              <td><span class="badge badge-success">Kitchen</span></td>
              <td>Morning (6am - 2pm)</td>
              <td>+63 987 111 2233</td>
              <td>
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-secondary">Edit</button>
                  <button class="btn btn-outline-danger">Remove</button>
                </div>
              </td>
            </tr>

            <tr>
              <td>
                <strong>Erika Soriano</strong><br>
                <small class="text-muted">Started: Feb 2025</small>
              </td>
              <td><span class="badge badge-danger">Manager</span></td>
              <td>Mid (10am - 6pm)</td>
              <td>+63 995 222 4455</td>
              <td>
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-secondary">Edit</button>
                  <button class="btn btn-outline-danger">Remove</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>