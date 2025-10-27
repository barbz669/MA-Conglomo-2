<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(1);
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../inc/header_back2.php';

$batch_id = isset($_GET['batch_id']) ? (int)$_GET['batch_id'] : 0;
if ($batch_id <= 0) {
    $_SESSION['error'] = "Batch ID is required.";
    header("Location: list.php");
    exit;
}

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

$ttCol = (function ($pdo) {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `stock_transactions` LIKE 'transaction_type'");
    $stmt->execute();
    if ($stmt->fetch()) return 'transaction_type';
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `stock_transactions` LIKE 'type'");
    $stmt->execute();
    return $stmt->fetch() ? 'type' : 'transaction_type';
})($pdo);

$sql = "SELECT * FROM stock_transactions WHERE batch_id = ? ORDER BY date ASC, id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$batch_id]);
$txs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$running = 0;
$rows = [];
foreach ($txs as $t) {
    $col = $ttCol;
    $tt = isset($t[$col]) ? strtoupper($t[$col]) : (isset($t['type']) ? strtoupper($t['type']) : (isset($t['transaction_type']) ? strtoupper($t['transaction_type']) : ''));
    if ($tt === 'IN') {
        $running += (int)$t['quantity'];
    } else {
        $running -= (int)$t['quantity'];
    }
    $t['running'] = $running;
    $rows[] = $t;
}

$sort = $_GET['sort'] ?? 'desc';
if ($sort === 'desc') {
    $rows = array_reverse($rows);
}

$currentStock = 0;
if (isset($batch['stock_on_hand'])) {
    $currentStock = (int)$batch['stock_on_hand'];
} elseif (isset($batch['quantity'])) {
    $currentStock = (int)$batch['quantity'];
} else {
    $currentStock = $rows ? (int)end($rows)['running'] : 0;
}

$rowClass = '';
if (!empty($batch['expiry_date'])) {
    try {
        $expiryDate = new DateTime($batch['expiry_date']);
        $today = new DateTime();
        $diffDays = (int)$today->diff($expiryDate)->format('%r%a');
        $rowClass = $diffDays < 0 ? 'table-danger' : ($diffDays <= 60 ? 'table-warning' : '');
    } catch (Exception $e) {
        $rowClass = '';
    }
}
?>

<style>
    body {
        background-color: #fff5f5;
    }

    .page-header {
        background-color: #b30000;
        color: white;
        border-radius: 14px;
        padding: 18px 22px;
        font-weight: 700;
        font-size: 1.8rem;
        box-shadow: 0 3px 10px rgba(179, 0, 0, 0.3);
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
        border-left: 6px solid #b30000;
        border-radius: 12px;
        padding: 16px 20px;
        margin-top: 20px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    .batch-details strong {
        color: #b30000;
    }

    .table {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    .table th {
        background-color: #b30000;
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
        color: #b30000;
        border-color: white;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(179, 0, 0, 0.4);
    }

    .btn-red {
        background-color: #b30000;
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
        background-color: #8b0000;
        color: white;
        box-shadow: 0 4px 12px rgba(179, 0, 0, 0.4);
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
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="container mt-4">
    <div class="page-header mb-4">
        <div><i class="bi bi-box-seam-fill"></i> Stock Card</div>
        <a href="export_stockcard.php?batch_id=<?= (int)$batch_id ?>" class="btn btn-outline-red mb-4" target="_blank">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export PDF
        </a>
    </div>

    <div class="batch-details">
        <h5 class="mb-2">
            <?= htmlspecialchars((string)($batch['generic_name'] ?? '')) ?> 
            (<?= htmlspecialchars((string)($batch['brand_name'] ?? '')) ?>)
        </h5>
        <p class="mb-0">
            Unit: <strong><?= htmlspecialchars((string)($batch['unit'] ?? '')) ?></strong> |
            Batch #: <strong><?= htmlspecialchars((string)($batch['batch_no'] ?? '')) ?></strong> |
            Expiry: <strong><?= htmlspecialchars((string)($batch['expiry_date'] ?? '')) ?></strong> |
            Current Stock: <strong><?= (int)$currentStock ?></strong> |
            Cost/Unit: <strong>₱<?= number_format((float)($batch['cost_per_unit'] ?? 0), 2) ?></strong>
        </p>
    </div>

    <form method="get" class="mb-3 mt-3 d-flex align-items-center gap-2 flex-wrap">
        <input type="hidden" name="batch_id" value="<?= (int)$batch_id ?>">
        <label for="sort" class="fw-semibold text-dark">Sort by Date:</label>
        <select name="sort" id="sort" onchange="this.form.submit()">
            <option value="asc" <?= ($sort === 'asc') ? 'selected' : '' ?>>Ascending (oldest first)</option>
            <option value="desc" <?= ($sort === 'desc') ? 'selected' : '' ?>>Descending (latest first)</option>
        </select>
    </form>

    <div class="table-responsive mt-3">
        <table class="table table-bordered table-sm mt-3">
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
                    <tr class="<?= htmlspecialchars((string)$rowClass) ?>">
                        <td><?= htmlspecialchars((string)($t['date'] ?? $t['created_at'] ?? '')) ?></td>
                        <td>
                            <?php if ((strtoupper($t[$ttCol] ?? ($t['type'] ?? ($t['transaction_type'] ?? '')))) === 'IN'): ?>
                                Qty: <?= (int)$t['quantity'] ?><br>
                                <?= isset($t['cost']) ? 'Cost: ₱' . number_format((float)$t['cost'], 2) : '' ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ((strtoupper($t[$ttCol] ?? ($t['type'] ?? ($t['transaction_type'] ?? '')))) === 'OUT'): ?>
                                <?= isset($t['invoice_no']) ? 'Invoice: ' . htmlspecialchars((string)$t['invoice_no']) . '<br>' : '' ?>
                                <?= isset($t['customer']) ? 'Customer: ' . htmlspecialchars((string)$t['customer']) . '<br>' : '' ?>
                                Qty: <?= (int)$t['quantity'] ?><br>
                                <?= isset($t['price']) ? 'Price: ₱' . number_format((float)$t['price'], 2) . '<br>' : '' ?>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= (int)$t['running'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <a href="list.php" class="btn btn-red mt-3 mb-5">
        <i class="bi bi-arrow-left-circle me-1"></i> Back to Inventory
    </a>
</div>

<?php include __DIR__ . '/../inc/footer.php'; ?>
