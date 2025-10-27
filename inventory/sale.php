<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(2); // Inventory Keeper only
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../inc/header_back.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $batch_id   = $_POST['batch_id'] ?? null;
    $quantity   = $_POST['quantity'] ?? null;
    $price      = $_POST['price'] ?? null;
    $invoice_no = $_POST['invoice_no'] ?? null;
    $customer   = trim($_POST['customer'] ?? '');
    $date       = $_POST['date'] ?? date('Y-m-d');

    if ($batch_id && $quantity && $invoice_no && $customer) {
        $stmt = $pdo->prepare("SELECT stock_on_hand, medicine_id, cost_per_unit FROM medicine_batches WHERE id = ?");
        $stmt->execute([$batch_id]);
        $batch = $stmt->fetch();

        if ($batch && $batch['stock_on_hand'] >= $quantity) {
            $update = $pdo->prepare("UPDATE medicine_batches SET stock_on_hand = stock_on_hand - ? WHERE id = ?");
            $update->execute([$quantity, $batch_id]);

            $price = ($price !== null && $price !== '') ? $price : null;

            $sql = "INSERT INTO stock_transactions 
                    (medicine_id, batch_id, transaction_type, date, quantity, cost, price, invoice_no, customer) 
                    VALUES (?, ?, 'OUT', ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $batch['medicine_id'],
                $batch_id,
                $date,
                $quantity,
                $batch['cost_per_unit'],
                $price,
                $invoice_no,
                $customer
            ]);

            $_SESSION['success'] = "Sale recorded successfully.";
        } else {
            $_SESSION['error'] = "Not enough stock available in this batch.";
        }
    } else {
        $_SESSION['error'] = "Please fill in all required fields.";
    }
}

$sql = "SELECT b.id AS batch_id, m.generic_name, m.brand_name, m.unit, 
               b.batch_no, b.expiry_date, b.stock_on_hand, b.cost_per_unit
        FROM medicine_batches b
        JOIN medicines m ON b.medicine_id = m.id
        WHERE b.stock_on_hand > 0
        ORDER BY m.generic_name ASC, b.expiry_date ASC";
$batches = $pdo->query($sql)->fetchAll();
?>

<style>
    body {
        background-color: #fff5f5;
        font-family: "Poppins", sans-serif;
        margin: 0;
        padding: 0;
    }

    .page-header {
        background-color:  #b30000;
        color: white;
        padding: 40px 50px;
        text-align: center;
        font-weight: 600;
        font-size: 2rem;
        border-radius: 15px;
        width: 90%;
        max-width: 900px;
        margin: 40px auto 20px auto;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    /* Container */
    .stock-container {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: calc(100vh - 160px);
        padding: 0 20px 40px;
    }

    /* Form Card */
    .stock-form {
        background-color: #ffffff;
        border-radius: 15px;
        padding: 40px 50px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        width: 90%;
        max-width: 900px;
    }

    /* Labels */
    .form-label {
        font-weight: 500;
        color:  #b30000;
        margin-bottom: 6px;
        display: block;
    }

    /* Inputs */
    .form-control {
        border-radius: 10px;
        border: 1px solid #ddd;
        padding: 10px 12px;
        font-size: 1rem;
        width: 100%;
        transition: 0.2s ease-in-out;
    }

    .form-control:focus {
        border-color:  #b30000;
        box-shadow: 0 0 5px rgba(192, 57, 43, 0.4);
        outline: none;
    }

    /* Buttons */
    .btn {
        border-radius: 10px;
        font-weight: 500;
        padding: 10px 25px;
        transition: 0.3s;
    }

    .btn-primary {
        background-color:  #b30000;
        border: none;
        color: white;
    }

    .btn-primary:hover {
        background-color: #a93226;
    }

    .btn-secondary {
        background-color: #999;
        border: none;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #7a7a7a;
    }

    .button-group {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 25px;
    }

    /* Alerts */
    .alert {
        border-radius: 10px;
        padding: 12px;
        font-size: 0.95rem;
        margin-bottom: 15px;
        text-align: center;
    }

    .alert-success {
        background-color: #e9f7ef;
        color: #1e8449;
        border: 1px solid #2ecc71;
    }

    .alert-danger {
        background-color: #fdecea;
        color: #c0392b;
        border: 1px solid #e74c3c;
    }

    
    @media (max-width: 768px) {
        .page-header,
        .stock-form {
            width: 95%;
            padding: 25px 20px;
        }
    }
</style>

<div class="page-header">
    Record Outgoing Stock
</div>

<div class="stock-container">
    <div class="stock-form">
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']); ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['success'])): ?>
            <div id="successAlert" class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']); ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form method="post" id="saleForm">
            <div class="mb-3">
                <label class="form-label">Select Batch</label>
                <select name="batch_id" id="batch_id" class="form-control" required>
                    <option value="">-- Select Medicine Batch --</option>
                    <?php foreach ($batches as $b): ?>
                        <option value="<?= $b['batch_id'] ?>"
                            data-cost="<?= htmlspecialchars($b['cost_per_unit']) ?>">
                            <?= htmlspecialchars($b['generic_name']) ?> (<?= htmlspecialchars($b['brand_name']) ?>)
                            | Unit: <?= htmlspecialchars($b['unit']) ?>
                            | Batch: <?= htmlspecialchars($b['batch_no']) ?>
                            | Exp: <?= htmlspecialchars($b['expiry_date']) ?>
                            | Stock: <?= htmlspecialchars($b['stock_on_hand']) ?>
                            | Cost/Unit: ₱<?= $b['cost_per_unit'] !== null ? number_format($b['cost_per_unit'], 2) : 'N/A' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Invoice No.</label>
                <input type="text" name="invoice_no" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Customer</label>
                <input type="text" name="customer" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Price per Unit (₱)</label>
                <input type="number" step="0.01" id="price" name="price" class="form-control">
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<!-- ✅ SCRIPT -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const batchSelect = document.getElementById("batch_id");
        const priceInput = document.getElementById("price");

        batchSelect.addEventListener("change", function() {
            const selected = batchSelect.options[batchSelect.selectedIndex];
            const cost = selected.getAttribute("data-cost");

            if (cost) {
                priceInput.value = parseFloat(cost).toFixed(2);
            }
        });

        // Auto-hide success alert
        const successAlert = document.getElementById("successAlert");
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.transition = "opacity 1s";
                successAlert.style.opacity = 0;
                setTimeout(() => successAlert.remove(), 1000);
            }, 3000);
        }
    });
</script>


