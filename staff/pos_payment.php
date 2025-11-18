<?php
// staff/pos_payment.php
include __DIR__ . '/includes/header.php'; // db + auth

if (!isset($_GET['order_id']) || !ctype_digit($_GET['order_id'])) {
    echo "<div class='container-fluid'><p class='text-danger m-4'>Invalid order ID.</p></div>";
    include __DIR__ . '/includes/footer.php';
    exit;
}

$order_id = (int)$_GET['order_id'];

// ----------------------------
// FETCH ORDER + CUSTOMER
// ----------------------------
$stmt = $conn->prepare("
    SELECT 
        o.order_id,
        o.order_number,
        o.order_type,
        o.status,
        o.total_amount,
        o.created_at,
        ocd.customer_first_name,
        ocd.customer_last_name
    FROM orders o
    LEFT JOIN order_customer_details ocd ON o.order_id = ocd.order_id
    WHERE o.order_id = ?
    LIMIT 1
");
$stmt->bind_param('i', $order_id);
$stmt->execute();
$order_res = $stmt->get_result();

if ($order_res->num_rows === 0) {
    echo "<div class='container-fluid'><p class='text-danger m-4'>Order not found.</p></div>";
    include __DIR__ . '/includes/footer.php';
    exit;
}

$order = $order_res->fetch_assoc();
$stmt->close();

$customer_name = trim(
    ($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? '')
);
if ($customer_name === '') {
    $customer_name = 'Walk-in Customer';
}

$total_amount = (float)$order['total_amount'];

// ----------------------------
// CURRENT PAYMENT (IF ANY)
// ----------------------------
$payment_stmt = $conn->prepare("
    SELECT *
    FROM order_payment_details
    WHERE order_id = ?
    LIMIT 1
");
$payment_stmt->bind_param('i', $order_id);
$payment_stmt->execute();
$payment_res = $payment_stmt->get_result();
$current_payment = $payment_res->fetch_assoc();
$payment_stmt->close();

$errors = [];
$success = false;

// ----------------------------
// HANDLE FORM SUBMIT
// ----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method      = $_POST['payment_method'] ?? '';
    $amount_paid_input   = $_POST['amount_paid'] ?? '';
    $gcash_reference     = trim($_POST['gcash_reference'] ?? '');
    $gcash_sender_name   = trim($_POST['gcash_sender_name'] ?? '');

    $amount_paid_input   = str_replace(',', '', $amount_paid_input); // in case of comma
    $amount_paid         = (float)$amount_paid_input;
    $total               = $total_amount;

    if ($payment_method !== 'cash' && $payment_method !== 'gcash') {
        $errors[] = 'Invalid payment method.';
    }

    if ($payment_method === 'cash') {
        if ($amount_paid <= 0) {
            $errors[] = 'Amount given is required for cash payments.';
        } elseif ($amount_paid < $total) {
            $errors[] = 'Amount given cannot be less than the order total.';
        }
        $change_amount = max(0, $amount_paid - $total);
    } else { // gcash
        if ($gcash_reference === '') {
            $errors[] = 'GCash reference number is required.';
        }
        // assume they paid exact total
        $amount_paid   = $total;
        $change_amount = 0;
    }

    if (empty($errors)) {
        // Upsert into order_payment_details
        if ($current_payment) {
            // UPDATE
            $pay_stmt = $conn->prepare("
                UPDATE order_payment_details
                SET payment_method = ?,
                    payment_status = 'paid',
                    gcash_reference = ?,
                    gcash_sender_name = ?,
                    amount_paid = ?,
                    change_amount = ?,
                    paid_at = NOW()
                WHERE payment_id = ?
            ");
            $payment_id = (int)$current_payment['payment_id'];
            $pay_stmt->bind_param(
                'sssddi',
                $payment_method,
                $gcash_reference,
                $gcash_sender_name,
                $amount_paid,
                $change_amount,
                $payment_id
            );
        } else {
            // INSERT
            $pay_stmt = $conn->prepare("
                INSERT INTO order_payment_details
                    (order_id, payment_method, payment_status, gcash_reference, gcash_sender_name, amount_paid, change_amount, paid_at)
                VALUES
                    (?, ?, 'paid', ?, ?, ?, ?, NOW())
            ");
            $pay_stmt->bind_param(
                'isssdd',
                $order_id,
                $payment_method,
                $gcash_reference,
                $gcash_sender_name,
                $amount_paid,
                $change_amount
            );
        }

        if ($pay_stmt->execute()) {
            $pay_stmt->close();

            // Mark pickup orders as completed once paid.
            if ($order['order_type'] === 'pickup') {
                $upd = $conn->prepare("
                    UPDATE orders 
                    SET status = 'completed', updated_at = NOW()
                    WHERE order_id = ?
                ");
                $upd->bind_param('i', $order_id);
                $upd->execute();
                $upd->close();
            }

            $success = true;

            // Refresh current payment info
            $payment_stmt = $conn->prepare("
                SELECT *
                FROM order_payment_details
                WHERE order_id = ?
                LIMIT 1
            ");
            $payment_stmt->bind_param('i', $order_id);
            $payment_stmt->execute();
            $payment_res = $payment_stmt->get_result();
            $current_payment = $payment_res->fetch_assoc();
            $payment_stmt->close();
        } else {
            $errors[] = 'Failed to save payment. Please try again.';
            $pay_stmt->close();
        }
    }
}

?>

<div class="container-fluid">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    <h2 class="mb-4">POS Payment</h2>

    <?php if ($success): ?>
      <div class="alert alert-success">
        Payment recorded successfully.
        <?php if ($order['order_type'] === 'pickup'): ?>
          Order has been marked as <strong>Completed</strong>.
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <section class="content-card mb-4">
      <div class="content-card-header">
        <div class="left">
          <h3>Order Summary</h3>
          <p>
            Order #<?= htmlspecialchars($order['order_number'] ?? $order['order_id']) ?> ·
            <?= htmlspecialchars(ucfirst($order['order_type'])) ?> ·
            Customer: <?= htmlspecialchars($customer_name) ?>
          </p>
        </div>
        <div class="right">
          <a href="view_orders.php" class="btn btn-outline-secondary">Back to Orders Queue</a>
        </div>
      </div>

      <div class="p-3">
        <p><strong>Placed:</strong> <?= date('M d, Y g:i A', strtotime($order['created_at'])) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $order['status']))) ?></p>

        <h5 class="mt-3 mb-2">Items</h5>
        <ul class="mb-3" style="padding-left: 18px; font-size: 0.9em;">
          <?php
            $items_stmt = $conn->prepare("
                SELECT product_name, quantity 
                FROM order_items 
                WHERE order_id = ?
            ");
            $items_stmt->bind_param('i', $order_id);
            $items_stmt->execute();
            $items_res = $items_stmt->get_result();
            if ($items_res->num_rows === 0):
          ?>
            <li class="text-muted">No items found.</li>
          <?php
            else:
              while ($item = $items_res->fetch_assoc()):
          ?>
            <li>
              <?= htmlspecialchars($item['product_name']) ?> 
              x <strong><?= (int)$item['quantity'] ?></strong>
            </li>
          <?php
              endwhile;
            endif;
            $items_stmt->close();
          ?>
        </ul>

        <h4>Total: ₱<?= number_format($total_amount, 2) ?></h4>
      </div>
    </section>

    <section class="content-card">
      <div class="content-card-header">
        <div class="left">
          <h3>Record Payment</h3>
          <?php if ($current_payment && $current_payment['payment_status'] === 'paid'): ?>
            <p class="text-success mb-0">
              Existing payment: <?= htmlspecialchars(strtoupper($current_payment['payment_method'])) ?>,
              Amount: ₱<?= number_format((float)$current_payment['amount_paid'], 2) ?>,
              Paid at: <?= htmlspecialchars($current_payment['paid_at']) ?>
            </p>
          <?php else: ?>
            <p class="mb-0">Select the method and confirm payment.</p>
          <?php endif; ?>
        </div>
      </div>

      <div class="p-3">
        <?php if ($current_payment && $current_payment['payment_status'] === 'paid'): ?>
          <div class="alert alert-info">
            This order is already marked as <strong>paid</strong>.  
            If you really need to edit it, update directly in the database or adjust this page to allow edits.
          </div>
        <?php else: ?>
          <form method="post">
            <div class="mb-3">
              <label class="form-label">Payment Method</label>
              <select name="payment_method" class="form-select" required>
                <option value="">Select method</option>
                <option value="cash"  <?= (isset($_POST['payment_method']) && $_POST['payment_method'] === 'cash')  ? 'selected' : '' ?>>Cash</option>
                <option value="gcash" <?= (isset($_POST['payment_method']) && $_POST['payment_method'] === 'gcash') ? 'selected' : '' ?>>GCash</option>
              </select>
            </div>

            <div id="cash-fields" class="mb-3">
              <label class="form-label">Amount Given (Cash)</label>
              <input type="number" step="0.01" min="0" class="form-control" name="amount_paid"
                     value="<?= htmlspecialchars($_POST['amount_paid'] ?? '') ?>">
              <div class="form-text">Must be at least ₱<?= number_format($total_amount, 2) ?>.</div>
            </div>

            <div id="gcash-fields" class="mb-3">
              <label class="form-label">GCash Reference No.</label>
              <input type="text" class="form-control" name="gcash_reference"
                     value="<?= htmlspecialchars($_POST['gcash_reference'] ?? '') ?>">

              <label class="form-label mt-2">GCash Sender Name</label>
              <input type="text" class="form-control" name="gcash_sender_name"
                     value="<?= htmlspecialchars($_POST['gcash_sender_name'] ?? '') ?>">
            </div>

            <button type="submit" class="btn btn-success">Confirm Payment</button>
          </form>
        <?php endif; ?>
      </div>
    </section>

  </main>
</div>

<script>
// Simple toggle of fields based on method
document.addEventListener('DOMContentLoaded', function () {
  const methodSelect = document.querySelector('select[name="payment_method"]');
  const cashFields   = document.getElementById('cash-fields');
  const gcashFields  = document.getElementById('gcash-fields');

  function toggleFields() {
    const val = methodSelect ? methodSelect.value : '';
    if (val === 'cash') {
      cashFields.style.display  = 'block';
      gcashFields.style.display = 'none';
    } else if (val === 'gcash') {
      cashFields.style.display  = 'none';
      gcashFields.style.display = 'block';
    } else {
      cashFields.style.display  = 'none';
      gcashFields.style.display = 'none';
    }
  }

  if (methodSelect) {
    methodSelect.addEventListener('change', toggleFields);
    toggleFields(); // initial
  }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
