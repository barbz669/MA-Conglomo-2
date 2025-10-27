<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../inc/auth.php';
require_role(1); // Admin only
include __DIR__ . '/../inc/header_back.php';

// Handle Approve/Reject actions
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    if ($userId > 0 && in_array($action, ['APPROVE', 'REJECT'], true)) {
        try {
            // Log the action
            $logStmt = $pdo->prepare(
                "INSERT INTO user_approvals (user_id, action, admin_id) VALUES (?, ?, ?)"
            );
            $logStmt->execute([$userId, $action, $_SESSION['user_id']]);

            // Apply action
            if ($action === 'APPROVE') {
                $stmt = $pdo->prepare("UPDATE users SET is_approved = 1 WHERE id = ?");
                $stmt->execute([$userId]);
                $success = "✅ User has been successfully approved.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $success = "🗑️ User has been rejected and removed.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $error = "Invalid action or user.";
    }
}

// Fetch pending users
$pendingUsers = $pdo->query("
    SELECT u.id, u.email, u.full_name, r.name AS role
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE is_approved = 0
")->fetchAll();
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
        background-color: #b30000;
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
    .approve-container {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: calc(100vh - 160px);
        padding: 0 20px 40px;
    }

    /* Card */
    .approve-card {
        background-color: #ffffff;
        border-radius: 15px;
        padding: 40px 50px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        width: 90%;
        max-width: 900px;
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

    /* Table */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    thead {
        background-color: #b30000; /* ✅ Green header bar */
        color: white;
    }


    th, td {
        padding: 12px 15px;
        border-bottom: 1px solid #f2f2f2;
        text-align: left;
    }

    tbody tr:hover {
        background-color: #fff0f0;
    }

    td.text-center {
        text-align: center;
    }

    /* Buttons */
    .btn {
        border-radius: 10px;
        font-weight: 500;
        padding: 8px 20px;
        font-size: 0.9rem;
        transition: 0.3s;
        border: none;
        cursor: pointer;
    }

    .btn-approve {
        background-color: #b30000;
        color: #fff;
    }

    .btn-approve:hover {
        background-color: #a93226;
    }

    .btn-reject {
        background-color: #008000; /* ✅ Green reject button */
        color: #fff;
    }

    .btn-reject:hover {
        background-color: #006400; /* Darker green on hover */
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header,
        .approve-card {
            width: 95%;
            padding: 25px 20px;
        }

        table thead {
            display: none;
        }

        table, tbody, tr, td {
            display: block;
            width: 100%;
        }

        tr {
            margin-bottom: 1rem;
            border: 1px solid #f2f2f2;
            border-radius: 10px;
            background-color: #fff;
            padding: 0.5rem;
        }

        td {
            padding: 10px;
            text-align: right;
            position: relative;
        }

        td::before {
            content: attr(data-label);
            position: absolute;
            left: 10px;
            width: 50%;
            text-align: left;
            font-weight: 600;
            color: #c0392b;
        }
    }
</style>

<!-- ✅ HEADER -->
<div class="page-header">
    Approve Users
</div>

<!-- ✅ CONTENT -->
<div class="approve-container">
    <div class="approve-card">

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Full Name</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pendingUsers)): ?>
                    <?php foreach ($pendingUsers as $u): ?>
                        <tr>
                            <td data-label="Email"><?= htmlspecialchars($u['email']) ?></td>
                            <td data-label="Full Name"><?= htmlspecialchars($u['full_name']) ?></td>
                            <td data-label="Role"><?= htmlspecialchars($u['role']) ?></td>
                            <td data-label="Action">
                                <form method="post" style="display:inline-block">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" name="action" value="APPROVE" class="btn btn-approve">Approve</button>
                                </form>
                                <form method="post" style="display:inline-block" onsubmit="return confirm('Are you sure you want to reject and delete this user? This cannot be undone.');">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" name="action" value="REJECT" class="btn btn-reject">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No pending users to approve.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../inc/footer.php'; ?>
