<?php
include __DIR__ . '/includes/header.php';
//include __DIR__ . '/../../includes/db_connect.php';
?>
<div class="container">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <main class="content">
    <h2>Manage Menu</h2>
    <form method="POST" action="">
      <label>Item Name</label><br>
      <input type="text" name="name" required><br>
      <label>Price</label><br>
      <input type="number" name="price" step="0.01" required><br><br>
      <button type="submit">Add Item</button>
    </form>
  </main>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
