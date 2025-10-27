<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(2);
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../inc/header_back2.php';

$batch_id = isset($_GET['batch_id']) ? (int)$_GET['batch_id'] : 0;
if ($batch_id <= 0) {
    $_SESSION['error'] = "Batch ID is required.";
    header("Location: list.php");
    exit;
}

// fetch batch + medicine info
$stmt = $pdo->prepare("
    SELECT b.*, m.generic_name, m.brand_name, m.unit
    FROM medicine_batches b
    JOIN medicines m ON m.id = b.medicine_id
    WHERE b.id = ?
");
$stmt->execute([$batch_id]);
$batch = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$batch) {
    $_SESSION['error'] = "Batch not found.";
    header("Location: list.php");
    exit;
}

// detect transaction column
$ttCol = (function ($pdo) {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `stock_transactions` LIKE 'transaction_type'");
    $stmt->execute();
    if ($stmt->fetch()) return 'transaction_type';
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `stock_transactions` LIKE 'type'");
    $stmt->execute();
    return $stmt->fetch() ? 'type' : 'transaction_type';
})($pdo);

// fetch transactions
$stmt = $pdo->prepare("SELECT * FROM stock_transactions WHERE batch_id = ? ORDER BY date ASC, id ASC");
$stmt->execute([$batch_id]);
$txs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// compute running stock
$running = 0;
$rows = [];
foreach ($txs as $t) {
    $tt = strtoupper($t[$ttCol] ?? '');
    if ($tt === 'IN') $running += (int)$t['quantity'];
    else $running -= (int)$t['quantity'];
    $t['running'] = $running;
    $rows[] = $t;
}
$currentStock = $running;

// expiry classification
$expiryDate = new DateTime($batch['expiry_date']);
$today = new DateTime();
$diffDays = (int)$today->diff($expiryDate)->format('%r%a');
$rowClass = $diffDays < 0 ? 'table-danger' : ($diffDays <= 60 ? 'table-warning' : '');

// sorting
$sort = $_GET['sort'] ?? 'desc';
if ($sort === 'desc') $rows = array_reverse($rows);
?>

<style>
    body {
        background-color: #fff5f5;
    }

    .page-header {
        background-color: #b00020;
        color: white;
        border-radius: 14px;
        padding: 18px 22px;
        font-weight: 700;
        font-size: 1.8rem;
        box-shadow: 0 3px 10px rgba(176, 0, 32, 0.3);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .page-header i {
        margin-right: 10px;
        font-size: 1.6rem;
    }

    .batch-details {
        background: #fff;
        border-left: 6px solid #b00020;
        border-radius: 12px;
        padding: 16px 20px;
        margin-top: 20px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    .batch-details strong {
        color: #b00020;
    }

    .table {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    .table th {
        background-color: #b00020;
        color: white;
        font-weight: 600;
    }

    .table td {
        vertical-align: middle;
    }

    select {
        border-radius: 8px;
        padding: 6px 10px;
    }

    /* White outlined pill button (Export PDF) */
    .btn-outline-red {
        background-color: transparent;
        color: white;
        border: 2px solid white;
        border-radius: 50px;
        padding: 8px 18px;
        font-weight: 500;
        transition: all 0.25s ease, box-shadow 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-outline-red:hover {
        background-color: white;
        color: #b00020;
        border-color: white;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(176, 0, 32, 0.4);
    }

    /* Solid red pill button (Back to Inventory) */
    .btn-red {
        background-color: #b00020;
        color: white;
        border: none;
        border-radius: 50px;
        padding: 8px 18px;
        font-weight: 500;
        transition: all 0.25s ease, box-shadow 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-red:hover {
        background-color: #8c001a; /* darker red */
        color: white; /* keep text white */
        box-shadow: 0 4px 12px rgba(176, 0, 32, 0.4);
        text-decoration: none;
    }

    @media (max-width: 768px) {
        .page-header {
            font-size: 1.4rem;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .batch-details {
            font-size: 0.95rem;
        }

        .btn-outline-red, .btn-red {
            width: 100%; /* full-width buttons on mobile */
            justify-content: center;
        }
    }
</style>

<div class="stockcard-page container mt-4">
   
    <div class="page-header mb-4">
        <div><i class="bi bi-box-seam-fill"></i> Stock Card</div>
        <a href="export_stockcard.php?batch_id=<?= $batch_id ?>" class="btn btn-outline-red mb-4">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export PDF
        </a>
    </div>

    <div class="batch-details">
        <h5 class="mb-2">
            <?= htmlspecialchars($batch['generic_name']) ?> 
            (<?= htmlspecialchars($batch['brand_name']) ?>)
        </h5>
        <p class="mb-0">
            Unit: <strong><?= htmlspecialchars($batch['unit'] ?? '') ?></strong> |
            Batch #: <strong><?= htmlspecialchars($batch['batch_no'] ?? '') ?></strong> |
            Expiry: <strong><?= htmlspecialchars($batch['expiry_date'] ?? '') ?></strong> |
            Current Stock: <strong><?= $currentStock ?></strong> |
            Cost/Unit: <strong>₱<?= number_format($batch['cost_per_unit'] ?? 0, 2) ?></strong>
        </p>
    </div>

    <!-- Sorting -->
    <form method="get" class="mt-3 d-flex align-items-center gap-2 flex-wrap">
        <input type="hidden" name="batch_id" value="<?= $batch_id ?>">
        <label for="sort" class="fw-semibold text-dark">Sort by Date:</label>
        <select name="sort" id="sort" onchange="this.form.submit()">
            <option value="asc" <?= $sort === 'asc' ? 'selected' : '' ?>>Ascending (oldest first)</option>
            <option value="desc" <?= $sort === 'desc' ? 'selected' : '' ?>>Descending (latest first)</option>
        </select>
    </form>

    <!-- Transaction Table -->
    <div class="table-responsive mt-3">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Received (IN)</th>
                    <th>Sale (OUT)</th>
                    <th>Stock on Hand</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">No transactions for this batch.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($rows as $t): ?>
                    <tr class="<?= $rowClass ?>">
                        <td><?= htmlspecialchars($t['date'] ?? $t['created_at'] ?? '') ?></td>
                        <td>
                            <?php if (strtoupper($t[$ttCol] ?? '') === 'IN'): ?>
                                Qty: <?= (int)$t['quantity'] ?><br>
                                <?= isset($t['cost']) ? 'Cost: ₱' . number_format($t['cost'], 2) : '' ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (strtoupper($t[$ttCol] ?? '') === 'OUT'): ?>
                                <?= isset($t['invoice_no']) ? 'Invoice: ' . htmlspecialchars($t['invoice_no']) . '<br>' : '' ?>
                                <?= isset($t['customer']) ? 'Customer: ' . htmlspecialchars($t['customer']) . '<br>' : '' ?>
                                Qty: <?= (int)$t['quantity'] ?><br>
                                <?= isset($t['price']) ? 'Price: ₱' . number_format($t['price'], 2) . '<br>' : '' ?>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= (int)$t['running'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Back button with bottom space -->
    <a href="list.php" class="btn btn-red mt-3 mb-5">
        <i class="bi bi-arrow-left-circle me-1"></i> Back to Inventory
    </a>
</div> 

<?php include __DIR__ . '/../inc/footer.php'; ?>
