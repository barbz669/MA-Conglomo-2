<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(1); // Only admin
require_once __DIR__ . '/../config/database.php';

$medrep_id = $_GET['id'] ?? null;
$log_type = $_GET['log'] ?? 'clients';
$filterDate = $_GET['date'] ?? null;

if (!$medrep_id) {
    die("MedRep not specified.");
}

// Fetch MedRep info
$stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ? AND role_id = 3");
$stmt->execute([$medrep_id]);
$medrep = $stmt->fetch();

if (!$medrep) {
    die("MedRep not found.");
}

// Fetch logs
if ($log_type === "clients") {
    $sql = "
        SELECT c.id, c.client_name, c.hospital_clinic,
               c.products_covered, c.proof_image, c.created_at, c.date
        FROM client_logs c
        WHERE c.medrep_id = ?
    ";
} else {
    $sql = "
        SELECT p.id, p.date, p.client_name, p.precall_notes, 
               p.postcall_notes, p.created_at
        FROM call_logs p
        WHERE p.medrep_id = ?
    ";
}

$params = [$medrep_id];

if ($filterDate) {
    $sql .= " AND date = ?";
    $params[] = $filterDate;
}

$sql .= " ORDER BY created_at DESC";
$logs = $pdo->prepare($sql);
$logs->execute($params);

include __DIR__ . '/../inc/header_back_medrep.php';
?>

<div class="container mt-5 mb-5">
    <div class="card shadow-lg p-4">
        <h2 class="text-danger fw-bold mb-3 text-center text-md-start">
            <?= htmlspecialchars($medrep['full_name']) ?> — <?= ucfirst($log_type) ?> Logs
        </h2>
        <p class="text-muted text-center text-md-start">
            <strong>Email:</strong> <?= htmlspecialchars($medrep['email']) ?>
        </p>

        <!-- Date Filter -->
        <form method="get" class="row g-3 mb-4 align-items-center" id="filterForm">
            <input type="hidden" name="id" value="<?= htmlspecialchars($medrep_id) ?>">
            <input type="hidden" name="log" value="<?= htmlspecialchars($log_type) ?>">
            <div class="col-12 col-sm-6 col-md-4">
                <input type="date" name="date" class="form-control border-danger"
                    value="<?= htmlspecialchars($filterDate ?? '') ?>"
                    onchange="document.getElementById('filterForm').submit();">
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <a href="?id=<?= htmlspecialchars($medrep_id) ?>&log=<?= htmlspecialchars($log_type) ?>"
                    class="btn btn-outline-danger w-100">Reset</a>
            </div>
        </form>

        <!-- Export Buttons -->
        <div class="d-flex flex-wrap gap-2 mb-4 justify-content-center justify-content-md-start">
            <?php if ($log_type === "clients"): ?>
                <a href="client_coverage_pdf.php?id=<?= $medrep_id ?>&date=<?= urlencode($filterDate ?? '') ?>"
                    target="_blank" class="btn btn-danger d-flex align-items-center">
                    <i class="bi bi-file-earmark-pdf-fill me-2"></i> Export Client Coverage PDF
                </a>
            <?php else: ?>
                <a href="pre_post_calls_pdf.php?id=<?= $medrep_id ?>&date=<?= urlencode($filterDate ?? '') ?>"
                    target="_blank" class="btn btn-danger d-flex align-items-center">
                    <i class="bi bi-file-earmark-pdf-fill me-2"></i> Export Pre/Post Call PDF
                </a>
            <?php endif; ?>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-danger text-white">
                    <tr>
                        <?php if ($log_type === "clients"): ?>
                            <th>Client Name</th>
                            <th>Hospital/Clinic</th>
                            <th>Products Covered</th>
                            <th>Proof</th>
                            <th>Date</th>
                        <?php else: ?>
                            <th>Client/Doctor</th>
                            <th>Pre-call Notes</th>
                            <th>Post-call Notes</th>
                            <th>Date</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <?php if ($log_type === "clients"): ?>
                                <td><?= htmlspecialchars($log['client_name']) ?></td>
                                <td><?= htmlspecialchars($log['hospital_clinic']) ?></td>
                                <td><?= nl2br(htmlspecialchars($log['products_covered'])) ?></td>
                                <td>
                                    <?php if (!empty($log['proof_image'])): ?>
                                        <img src="../<?= htmlspecialchars($log['proof_image']) ?>"
                                            alt="Proof Image"
                                            class="img-thumbnail proof-thumb"
                                            data-bs-toggle="modal"
                                            data-bs-target="#imageModal<?= $log['id'] ?>">

                                        <!-- Modal -->
                                        <div class="modal fade" id="imageModal<?= $log['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">Proof Image</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <img src="../<?= htmlspecialchars($log['proof_image']) ?>"
                                                            class="img-fluid rounded shadow" alt="Proof Image">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No Proof</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($log['created_at']) ?></td>
                            <?php else: ?>
                                <td><?= htmlspecialchars($log['client_name']) ?></td>
                                <td><?= nl2br(htmlspecialchars($log['precall_notes'])) ?></td>
                                <td><?= nl2br(htmlspecialchars($log['postcall_notes'])) ?></td>
                                <td><?= htmlspecialchars($log['created_at']) ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
body {
    background-color: #fff5f5;
    font-family: "Poppins", sans-serif;
}

/* Card */
.card {
    border: 2px solid #c0392b;
    border-radius: 20px;
    padding: 2rem;
}

/* Table styling */
.table {
    border-radius: 12px;
    overflow: hidden;
    font-size: 0.95rem;
}

.table th {
    background-color: #c0392b;
    color: white;
    font-weight: 600;
    white-space: nowrap;
}

.table td {
    vertical-align: middle;
    font-size: 0.9rem;
    word-wrap: break-word;
}

/* Thumbnail images */
.proof-thumb {
    max-width: 80px;
    cursor: pointer;
    border: 2px solid #c0392b;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.proof-thumb:hover {
    transform: scale(1.1);
}

/* Buttons */
.btn-danger {
    background-color: #c0392b;
    border: none;
    transition: 0.3s ease;
}

.btn-danger:hover {
    background-color: #a93226;
    transform: scale(1.03);
}

.btn-outline-danger {
    border: 2px solid #c0392b;
    color: #c0392b;
}

.btn-outline-danger:hover {
    background-color: #c0392b;
    color: white;
}

/* Responsive tweaks */
@media (max-width: 1024px) {
    .card {
        padding: 1.5rem;
    }

    h2 {
        font-size: 1.5rem;
    }

    .table td, .table th {
        font-size: 0.85rem;
        white-space: nowrap;
    }

    .proof-thumb {
        max-width: 70px;
    }
}

@media (max-width: 768px) {
    body {
        padding: 0 10px;
    }

    .card {
        padding: 1.2rem;
        border-radius: 15px;
    }

    h2 {
        font-size: 1.3rem;
        text-align: center;
    }

    .table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
        font-size: 0.85rem;
    }

    .proof-thumb {
        max-width: 60px;
    }

    .btn {
        font-size: 0.9rem;
        padding: 8px 12px;
    }

    form .col-12 {
        margin-bottom: 8px;
    }
}

@media (max-width: 480px) {
    h2 {
        font-size: 1.2rem;
    }

    .btn {
        width: 100%;
        font-size: 0.85rem;
    }

    .proof-thumb {
        max-width: 55px;
    }
}
</style>

<?php include __DIR__ . '/../inc/footer.php'; ?>
