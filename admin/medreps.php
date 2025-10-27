<?php
// admin/medreps.php
require_once __DIR__ . '/../inc/auth.php';
require_role(1);
require_once __DIR__ . '/../config/database.php';

try {
    $stmt = $pdo->prepare("
        SELECT id, full_name, email
        FROM users
        WHERE role_id = :role_id AND is_approved = 1
        ORDER BY full_name ASC
    ");
    $stmt->execute(['role_id' => 3]);
    $medreps = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

include __DIR__ . '/../inc/header_back_dashboard.php';
?>

<style>
body {
    background-color: #fff6f6;
    font-family: 'Poppins', sans-serif;
}

/* Header box */
.header-box {
    background-color:  #b30000;
    color: #fff;
    text-align: center;
    padding: 40px 0;
    border-radius: 20px;
    margin: 40px auto 20px;
    width: 90%;
    max-width: 1200px;
    box-shadow: 0 5px 20px rgba(192, 57, 43, 0.3);
}

.header-box h1 {
    font-size: 2rem;
    margin: 0;
    font-weight: 600;
}

/* Container */
.container-box {
    background: #fff;
    border-radius: 20px;
    padding: 30px;
    width: 90%;
    max-width: 1200px;
    margin: 0 auto 50px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

/* Table header */
thead {
    background-color:  #b30000; /* 🔴 Red header bar */
    color: white;
}


/* Table cells */
th, td {
    padding: 12px 15px;
    border-bottom: 1px solid #f2f2f2;
    text-align: left;
}

tbody tr:hover {
    background-color: #fff0f0; /* Light pink hover */
}

/* Center text when needed */
td.text-center {
    text-align: center;
}

/* Buttons */
.btn-primary {
    background-color:  #b30000;
    border: none;
    transition: 0.3s;
}

.btn-primary:hover {
    background-color: #c0392b;
}

.btn-secondary {
    background-color:  #b30000;
    border: none;
    transition: 0.3s;
}

.btn-secondary:hover {
    background-color: #c0392b;
}
</style>

<div class="header-box">
    <h1>Medical Representatives</h1>
</div>

<div class="container-box">
    <?php if (empty($medreps)): ?>
        <div class="alert alert-info">No approved medical representatives found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 30%;">Name</th>
                        <th style="width: 35%;">Email</th>
                        <th style="width: 35%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medreps as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['full_name']) ?></td>
                            <td><?= htmlspecialchars($m['email']) ?></td>
                            <td class="text-center">
                                <a href="view_medrep.php?id=<?= urlencode($m['id']) ?>&log=clients"
                                    class="btn btn-primary btn-sm me-2 mb-1">
                                    Client Coverage Logs
                                </a>
                                <a href="view_medrep.php?id=<?= urlencode($m['id']) ?>&log=calls"
                                    class="btn btn-secondary btn-sm mb-1">
                                    Pre/Post Call Logs
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../inc/footer.php'; ?>
