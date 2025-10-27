<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(2); // Inventory Keeper only
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../inc/header_back2_addmed.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Normalize casing before saving
    $generic = strtoupper(trim($_POST['generic_name'] ?? '')); // ALL CAPS
    $brand   = ucwords(strtolower(trim($_POST['brand_name'] ?? ''))); // Title Case
    $unit    = strtoupper(trim($_POST['unit'] ?? '')); // ALL CAPS

    if ($generic && $brand && $unit) {
        try {
            // Check if exact combination already exists
            $checkSql = "SELECT COUNT(*) FROM medicines WHERE generic_name = ? AND brand_name = ? AND unit = ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$generic, $brand, $unit]);
            $exists = $checkStmt->fetchColumn();

            if ($exists > 0) {
                $error = "⚠️ Medicine with Generic: <strong>$generic</strong>, Brand: <strong>$brand</strong>, and Unit: <strong>$unit</strong> already exists.";
            } else {
                // Insert only if unique combination
                $sql = "INSERT INTO medicines (generic_name, brand_name, unit) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$generic, $brand, $unit]);

                $success = "✅ Medicine <strong>$generic ($brand, $unit)</strong> added successfully.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<!-- ✅ STYLE -->
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

    /* Container */
    .medicine-container {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: calc(100vh - 160px);
        padding: 0 20px 40px;
    }

    /* Card */
    .medicine-form {
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
        text-align: center;
        border-radius: 10px;
        padding: 12px;
        font-size: 0.95rem;
        margin-bottom: 15px;
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

    /* Responsive */
    @media (max-width: 768px) {
        .page-header,
        .medicine-form {
            width: 95%;
            padding: 25px 20px;
        }
    }
</style>

<!-- ✅ HEADER -->
<div class="page-header">
    Add New Medicine
</div>

<!-- ✅ FORM -->
<div class="medicine-container">
    <div class="medicine-form">
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label">Generic Name</label>
                <input type="text" name="generic_name" class="form-control text-uppercase" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Brand Name</label>
                <input type="text" name="brand_name" class="form-control brand-case" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Unit</label>
                <input type="text" name="unit" class="form-control text-uppercase" placeholder="e.g., TABLET, CAPSULE, BOTTLE" required>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">Save Medicine</button>
                
            </div>
        </form>
    </div>
</div>

<script>
    // Force uppercase for generic name & unit as user types
    document.querySelectorAll('.text-uppercase').forEach(el => {
        el.addEventListener('input', () => {
            el.value = el.value.toUpperCase();
        });
    });

    // Auto Title Case for brand name
    document.querySelectorAll('.brand-case').forEach(el => {
        el.addEventListener('input', () => {
            el.value = el.value
                .toLowerCase()
                .replace(/\b\w/g, char => char.toUpperCase());
        });
    });
</script>

<?php include __DIR__ . '/../inc/footer.php'; ?>
