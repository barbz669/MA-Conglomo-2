<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(2); // Inventory Keeper only
include __DIR__ . '/../inc/header.php';
?>

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<!-- CSS -->
<link rel="stylesheet" href="css/dashboard.css">

<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <!-- Header -->
        <div class="dashboard-header">
            <h1><i class=""></i> Inventory Dashboard</h1>
        </div>

        <!-- Content -->
        <div class="dashboard-content">
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="list-group">
                <a class="list-group-item list-group-item-action" href="list.php">
                    <i class="bi bi-box-seam"></i> View Inventory
                </a>
                <a class="list-group-item list-group-item-action" href="add_medicine.php">
                    <i class="bi bi-plus-circle"></i> Add New Medicine
                </a>
                <a class="list-group-item list-group-item-action" href="receive.php">
                    <i class="bi bi-arrow-down-circle"></i> Record Incoming Stock
                </a>
                <a class="list-group-item list-group-item-action" href="sale.php">
                    <i class="bi bi-arrow-up-circle"></i> Record Outgoing Stock
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../inc/footer.php'; ?>
