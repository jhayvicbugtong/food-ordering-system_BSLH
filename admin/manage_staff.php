<?php
include __DIR__ . '/includes/header.php';
//include __DIR__ . '/../../includes/db_connect.php';
?>
<div class="container">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <main class="content">
    <h2>Manage Staff</h2>
    <form method="POST">
      <label>Name</label><br>
      <input type="text" name="name"><br>
      <label>Email</label><br>
      <input type="email" name="email"><br>
      <label>Password</label><br>
      <input type="password" name="password"><br>
      <label>Role</label><br>
      <select name="role">
        <option value="staff">Staff</option>
        <option value="admin">Admin</option>
      </select><br><br>
      <button type="submit">Add Staff</button>
    </form>
  </main>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
