<?php
include __DIR__ . '/includes/header.php';
?>

<div class="container">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="content">

    <!-- ADD / EDIT MENU ITEM -->
    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Menu Management</h2>
          <p>Add new items or update pricing / availability</p>
        </div>
        <div class="right">
          <button class="btn-primary">Save Changes</button>
        </div>
      </div>

      <form class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Item Name</label>
          <input type="text" class="form-control" placeholder="Avocado Power Bowl">
        </div>

        <div class="col-md-3">
          <label class="form-label">Category</label>
          <select class="form-control">
            <option>Bowls</option>
            <option>Wraps</option>
            <option>Breakfast</option>
            <option>Drinks</option>
            <option>Desserts</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Price ($)</label>
          <input type="number" step="0.01" class="form-control" placeholder="11.50">
        </div>

        <div class="col-md-3">
          <label class="form-label">Availability</label>
          <select class="form-control">
            <option>Visible (Orderable)</option>
            <option>Low stock</option>
            <option>Hidden / Sold out</option>
          </select>
        </div>

        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea class="form-control" rows="2" placeholder="Fresh avocado, quinoa, chickpeas, roasted corn, lime dressing..."></textarea>
        </div>

        <div class="col-12">
          <button type="submit" class="btn-primary">Add Item</button>
        </div>
      </form>
    </section>

    <!-- CURRENT MENU TABLE -->
    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h2>Current Menu</h2>
          <p>Your live items</p>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Item</th>
              <th>Category</th>
              <th>Price</th>
              <th>Availability</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>

            <tr>
              <td>
                Avocado Power Bowl<br>
                <small>Fresh avo, quinoa, lime dressing</small>
              </td>
              <td>Bowls</td>
              <td>$11.50</td>
              <td><span class="badge badge-success">Visible</span></td>
              <td>
                <button class="btn btn-sm btn-outline-secondary">Edit</button>
                <button class="btn btn-sm btn-outline-danger">Delete</button>
              </td>
            </tr>

            <tr>
              <td>
                Vegan Pancake Stack<br>
                <small>Fluffy oat-banana pancakes, no sugar</small>
              </td>
              <td>Breakfast</td>
              <td>$9.00</td>
              <td><span class="badge badge-warning">Low stock</span></td>
              <td>
                <button class="btn btn-sm btn-outline-secondary">Edit</button>
                <button class="btn btn-sm btn-outline-danger">Delete</button>
              </td>
            </tr>

            <tr>
              <td>
                Matcha Coconut Latte<br>
                <small>Matcha + coconut milk, lightly sweet</small>
              </td>
              <td>Drinks</td>
              <td>$4.75</td>
              <td><span class="badge badge-success">Visible</span></td>
              <td>
                <button class="btn btn-sm btn-outline-secondary">Edit</button>
                <button class="btn btn-sm btn-outline-danger">Delete</button>
              </td>
            </tr>

            <tr>
              <td>
                Keto Chicken Wrap<br>
                <small>Grilled chicken, avocado crema, low-carb wrap</small>
              </td>
              <td>Wraps</td>
              <td>$10.00</td>
              <td><span class="badge badge-danger">Hidden</span></td>
              <td>
                <button class="btn btn-sm btn-outline-secondary">Edit</button>
                <button class="btn btn-sm btn-outline-danger">Delete</button>
              </td>
            </tr>

          </tbody>
        </table>
      </div>
    </section>

  </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
