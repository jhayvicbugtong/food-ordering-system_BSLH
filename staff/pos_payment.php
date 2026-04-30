<?php
// staff/pos_payment.php
include __DIR__ . '/includes/header.php'; // db + auth

if (!isset($_GET['order_id']) || !ctype_digit($_GET['order_id'])) {
    echo "<div class='container-fluid'><div class='alert alert-danger m-4'>Invalid order ID. <a href='view_orders.php'>Back to Orders</a></div></div>";
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
    echo "<div class='container-fluid'><div class='alert alert-danger m-4'>Order not found.</div></div>";
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
            
            // Re-fetch order status in case it changed to completed
            $stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = ?");
            $stmt->bind_param('i', $order_id);
            $stmt->execute();
            $stmt->bind_result($order['status']);
            $stmt->fetch();
            $stmt->close();
            
        } else {
            $errors[] = 'Failed to save payment. Please try again.';
            $pay_stmt->close();
        }
    }
}
?>

<style>
    /* Clean Card Styling */
    .pos-card {
        background: #fff;
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .pos-header {
        background: #f8f9fa;
        padding: 20px 24px;
        border-bottom: 1px solid #edf2f7;
    }
    
    .pos-body {
        padding: 24px;
        flex: 1;
    }

    /* Receipt Styling */
    .receipt-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .receipt-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px dashed #e2e8f0;
        font-size: 0.95rem;
    }
    .receipt-item:last-child {
        border-bottom: none;
    }
    .receipt-total {
        background: #f1f8ff;
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
        text-align: center;
        border: 1px solid #d0e6ff;
    }
    .total-label {
        color: #64748b;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    .total-amount {
        color: #0f172a;
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.2;
    }

    /* Payment Method Selector */
    .method-selector {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 24px;
    }
    .method-option {
        display: none; /* Hide default radio */
    }
    .method-label {
        cursor: pointer;
        padding: 16px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        text-align: center;
        transition: all 0.2s ease;
        background: #fff;
    }
    .method-label i {
        display: block;
        font-size: 24px;
        margin-bottom: 8px;
        color: #64748b;
    }
    .method-label span {
        font-weight: 600;
        color: #475569;
    }
    
    /* Checked State */
    .method-option:checked + .method-label {
        border-color: #28a745;
        background: #f0fff4;
    }
    .method-option:checked + .method-label i,
    .method-option:checked + .method-label span {
        color: #28a745;
    }

    /* Status Badges */
    .status-badge {
        font-size: 0.8rem;
        padding: 6px 12px;
        border-radius: 50px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .status-pending { background: #fff7ed; color: #c2410c; }
    .status-completed { background: #f0fdf4; color: #15803d; }
    .status-cancelled { background: #fef2f2; color: #b91c1c; }
    
    .back-btn {
        text-decoration: none;
        color: #64748b;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: color 0.2s;
    }
    .back-btn:hover { color: #0f172a; }
</style>

<div class="container-fluid">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="view_orders.php" class="back-btn mb-2">
                    <i class="bi bi-arrow-left"></i> Back to Queue
                </a>
                <h2 class="m-0 fw-bold">Process Payment</h2>
            </div>
            <div>
                <span class="status-badge <?= $order['status'] === 'completed' ? 'status-completed' : ($order['status'] === 'cancelled' ? 'status-cancelled' : 'status-pending') ?>">
                    <?= htmlspecialchars(str_replace('_', ' ', $order['status'])) ?>
                </span>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success d-flex align-items-center shadow-sm border-0 mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">Success!</h6>
                    <p class="mb-0">Payment recorded. <?= $order['order_type'] === 'pickup' ? 'Order marked as completed.' : '' ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger d-flex align-items-center shadow-sm border-0 mb-4">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-5 col-xl-4">
                <div class="pos-card">
                    <div class="pos-header">
                        <h5 class="fw-bold mb-1">Order Summary</h5>
                        <small class="text-muted">Order #<?= htmlspecialchars($order['order_number'] ?? $order['order_id']) ?></small>
                    </div>
                    <div class="pos-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <small class="text-muted d-block">Customer</small>
                                <span class="fw-semibold"><?= htmlspecialchars($customer_name) ?></span>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Type</small>
                                <span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($order['order_type'])) ?></span>
                            </div>
                        </div>

                        <hr class="text-muted opacity-25">

                        <h6 class="fw-bold mb-3 text-secondary" style="font-size: 0.8rem; text-transform: uppercase;">Items Ordered</h6>
                        <ul class="receipt-list">
                            <?php
                            $items_stmt = $conn->prepare("SELECT product_name, quantity FROM order_items WHERE order_id = ?");
                            $items_stmt->bind_param('i', $order_id);
                            $items_stmt->execute();
                            $items_res = $items_stmt->get_result();
                            
                            if ($items_res->num_rows > 0):
                                while ($item = $items_res->fetch_assoc()):
                            ?>
                                <li class="receipt-item">
                                    <span class="text-dark"><?= htmlspecialchars($item['product_name']) ?></span>
                                    <span class="fw-bold">x<?= (int)$item['quantity'] ?></span>
                                </li>
                            <?php
                                endwhile;
                            else:
                            ?>
                                <li class="text-muted text-center py-3">No items found.</li>
                            <?php endif; $items_stmt->close(); ?>
                        </ul>

                        <div class="receipt-total">
                            <div class="total-label">Total Amount Due</div>
                            <div class="total-amount">₱<?= number_format($total_amount, 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 col-xl-8">
                <div class="pos-card">
                    <div class="pos-header">
                        <h5 class="fw-bold mb-1">Payment Details</h5>
                        <small class="text-muted">Select method and enter details</small>
                    </div>
                    <div class="pos-body">
                        <?php if ($current_payment && $current_payment['payment_status'] === 'paid'): ?>
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                                </div>
                                <h3 class="fw-bold text-dark">Payment Completed</h3>
                                <p class="text-muted mb-4">
                                    Paid via <strong><?= htmlspecialchars(strtoupper($current_payment['payment_method'])) ?></strong><br>
                                    Amount: ₱<?= number_format((float)$current_payment['amount_paid'], 2) ?><br>
                                    <small><?= date('M d, Y h:i A', strtotime($current_payment['paid_at'])) ?></small>
                                </p>
                                <button class="btn btn-outline-secondary" disabled>Edit Payment</button>
                            </div>
                        <?php else: ?>
                            <form method="post" id="paymentForm">
                                <label class="form-label fw-bold text-dark mb-2">Select Payment Method</label>
                                <div class="method-selector">
                                    <div>
                                        <input type="radio" name="payment_method" id="method_cash" value="cash" class="method-option" 
                                            <?= (!isset($_POST['payment_method']) || $_POST['payment_method'] === 'cash') ? 'checked' : '' ?>>
                                        <label for="method_cash" class="method-label w-100">
                                            <i class="bi bi-cash-coin"></i>
                                            <span>Cash Payment</span>
                                        </label>
                                    </div>
                                    <div>
                                        <input type="radio" name="payment_method" id="method_gcash" value="gcash" class="method-option"
                                            <?= (isset($_POST['payment_method']) && $_POST['payment_method'] === 'gcash') ? 'checked' : '' ?>>
                                        <label for="method_gcash" class="method-label w-100">
                                            <i class="bi bi-phone"></i>
                                            <span>GCash e-Wallet</span>
                                        </label>
                                    </div>
                                </div>

                                <div id="cash-fields">
                                    <div class="mb-3">
                                        <label class="form-label text-secondary">Amount Given</label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-light border-end-0">₱</span>
                                            <input type="number" step="0.01" min="0" class="form-control border-start-0 ps-0" 
                                                   name="amount_paid" id="amount_paid" 
                                                   placeholder="0.00"
                                                   value="<?= htmlspecialchars($_POST['amount_paid'] ?? '') ?>">
                                        </div>
                                        <div class="d-flex justify-content-between mt-2">
                                            <small class="text-muted">Minimum required: ₱<?= number_format($total_amount, 2) ?></small>
                                            <small class="fw-bold text-primary" id="change-display">Change: ₱0.00</small>
                                        </div>
                                    </div>
                                </div>

                                <div id="gcash-fields" style="display: none;">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label text-secondary">Reference No.</label>
                                            <input type="text" class="form-control form-control-lg" name="gcash_reference" 
                                                   placeholder="e.g. 90123..."
                                                   value="<?= htmlspecialchars($_POST['gcash_reference'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-secondary">Sender Name (Optional)</label>
                                            <input type="text" class="form-control form-control-lg" name="gcash_sender_name" 
                                                   placeholder="Juan Dela Cruz"
                                                   value="<?= htmlspecialchars($_POST['gcash_sender_name'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="alert alert-info mt-3 py-2 small">
                                        <i class="bi bi-info-circle me-1"></i> Ensure the exact amount of <strong>₱<?= number_format($total_amount, 2) ?></strong> is received.
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-top">
                                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold py-3 shadow-sm">
                                        <i class="bi bi-check-lg me-2"></i> Confirm Payment
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cashRadio   = document.getElementById('method_cash');
    const gcashRadio  = document.getElementById('method_gcash');
    const cashFields  = document.getElementById('cash-fields');
    const gcashFields = document.getElementById('gcash-fields');
    const amountInput = document.getElementById('amount_paid');
    const changeDisplay = document.getElementById('change-display');
    const totalAmount = <?= $total_amount ?>;

    function toggleFields() {
        if (cashRadio.checked) {
            cashFields.style.display = 'block';
            gcashFields.style.display = 'none';
            amountInput.setAttribute('required', 'required');
        } else {
            cashFields.style.display = 'none';
            gcashFields.style.display = 'block';
            amountInput.removeAttribute('required');
        }
    }

    function calculateChange() {
        if (!amountInput) return;
        const val = parseFloat(amountInput.value) || 0;
        const change = Math.max(0, val - totalAmount);
        changeDisplay.textContent = 'Change: ₱' + change.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        if (val < totalAmount && val > 0) {
            changeDisplay.classList.remove('text-primary');
            changeDisplay.classList.add('text-danger');
            changeDisplay.textContent = 'Insufficient Amount';
        } else {
            changeDisplay.classList.remove('text-danger');
            changeDisplay.classList.add('text-primary');
        }
    }

    if (cashRadio && gcashRadio) {
        cashRadio.addEventListener('change', toggleFields);
        gcashRadio.addEventListener('change', toggleFields);
        // Initialize
        toggleFields();
    }

    if (amountInput) {
        amountInput.addEventListener('input', calculateChange);
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>