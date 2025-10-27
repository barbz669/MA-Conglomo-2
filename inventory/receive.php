<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(2); // Inventory Keeper only
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../inc/header_back.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medicine_id   = $_POST['medicine_id'] ?? null;
    $batch_no      = trim($_POST['batch_no'] ?? '');
    $expiry_date   = $_POST['expiry_date'] ?? null;
    $quantity      = (int) ($_POST['quantity'] ?? 0);
    $cost_per_unit = (float) ($_POST['cost'] ?? 0);
    $received_at   = $_POST['received_at'] ?? date('Y-m-d');

    if ($medicine_id && $batch_no && $expiry_date && $quantity > 0) {
        $total_cost = $cost_per_unit > 0 ? $cost_per_unit * $quantity : null;
        try {
            $total_cost = $cost_per_unit * $quantity;

            // Check if batch already exists
            $sql = "SELECT id FROM medicine_batches 
                    WHERE medicine_id = ? AND batch_no = ? AND expiry_date = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$medicine_id, $batch_no, $expiry_date]);
            $existingBatch = $stmt->fetch();

            if ($existingBatch) {
                $batch_id = $existingBatch['id'];

                $sql = "UPDATE medicine_batches 
                        SET quantity = quantity + ?, 
                            stock_on_hand = stock_on_hand + ?, 
                            cost_per_unit = ?, 
                            cost = cost + ?
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $quantity,
                    $quantity,
                    $cost_per_unit > 0 ? $cost_per_unit : null,
                    $total_cost ?? 0,
                    $batch_id
                ]);
            } else {
                $sql = "INSERT INTO medicine_batches 
                        (medicine_id, batch_no, expiry_date, quantity, stock_on_hand, cost_per_unit, cost, received_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $medicine_id,
                    $batch_no,
                    $expiry_date,
                    $quantity,
                    $quantity,
                    $cost_per_unit > 0 ? $cost_per_unit : null,
                    $total_cost ?? 0,
                    $received_at
                ]);
                $batch_id = $pdo->lastInsertId();
            }

            // Insert transaction log
            $sql = "INSERT INTO stock_transactions 
                    (medicine_id, batch_id, transaction_type, date, quantity, cost) 
                    VALUES (?, ?, 'IN', ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $medicine_id,
                $batch_id,
                $received_at,
                $quantity,
                $total_cost ?? 0
            ]);

            $_SESSION['success'] = "Incoming stock recorded successfully.";
            $_POST = [];
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Please fill in all required fields.";
    }
}

$meds = $pdo->query("SELECT id, generic_name, brand_name, unit FROM medicines ORDER BY generic_name ASC")->fetchAll();
?>

<!-- ✅ STYLES INSIDE PHP -->

   <style>
    body {
        background-color: #fff5f5;
        font-family: "Poppins", sans-serif;
        margin: 0;
        padding: 0;
    }

    /* Header */
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

    /* Container wraps everything */
    .stock-container {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: calc(100vh - 160px);
        padding: 0 20px 40px;
    }

    /* White card */
    .stock-form {
        background-color: #ffffff;
        border-radius: 15px;
        padding: 40px 50px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        width: 90%;
        max-width: 900px; /* ✅ same as red header */
        margin: 0 auto;
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
        border-color: # #b30000;
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
        background-color: #c0392b;
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
    text-decoration: none; /* ✅ removes underline */
    border-radius: 10px;
    font-weight: 500;
    padding: 10px 25px;
    transition: 0.3s;
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
        text-align: center;
        border-radius: 10px;
        padding: 12px;
        font-size: 0.95rem;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .page-header,
        .stock-form {
            width: 95%;
            padding: 25px 20px;
        }
    }
</style>




<!-- ✅ HEADER -->
<div class="page-header">
    Record Incoming Stock
</div>

<!-- ✅ FORM -->
<div class="stock-container">
    <div class="stock-form">
        <form method="post" id="stockForm">
            <div class="mb-3">
                <label class="form-label">Medicine</label>
                <select name="medicine_id" class="form-control" required>
                    <option value="">-- Select Medicine --</option>
                    <!-- options -->
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Batch #</label>
                <input type="text" name="batch_no" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Received At</label>
                <input type="date" name="received_at" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Expiry Date</label>
                <input type="date" name="expiry_date" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Cost per Unit (₱)</label>
                <input type="number" step="0.01" name="cost" class="form-control">
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>


<script>
    // Format Quantity
    document.querySelector('.number-input').addEventListener('input', function(e) {
        let value = e.target.value.replace(/,/g, '');
        if (!isNaN(value) && value !== '') {
            e.target.value = parseInt(value, 10).toLocaleString();
        }
    });

    // Format Price
    document.querySelector('.currency-input').addEventListener('blur', function(e) {
        let value = e.target.value.replace(/,/g, '');
        if (!isNaN(value) && value !== '') {
            e.target.value = parseFloat(value).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    });

    // Remove commas before submit
    document.getElementById('stockForm').addEventListener('submit', function() {
        let qtyInput = document.querySelector('.number-input');
        let costInput = document.querySelector('.currency-input');
        qtyInput.value = qtyInput.value.replace(/,/g, '');
        costInput.value = costInput.value.replace(/,/g, '');
    });
</script>
