<?php
require_once __DIR__ . '/../inc/auth.php';
require_role(1); // Admin only
include __DIR__ . '/../inc/header.php';
?>

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
  background-color: #fff5f5;
  font-family: "Poppins", "Segoe UI", Arial, sans-serif;
  margin: 0;
  padding: 0;
}

/* Page wrapper centers content and keeps spacing below your header */
.page-wrapper {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 60px 20px; /* distance from top */
}

/* Main dashboard card */
.dashboard-card {
  width: 100%;
  max-width: 800px;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

/* Red header bar */
.dashboard-header {
  background-color: #b30000;
  color: #fff;
  padding: 25px 20px;
  text-align: center;
}

.dashboard-header h1 {
  margin: 0;
  font-size: 1.9rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 10px;
}

/* Body content */
.card-body {
  padding: 30px 40px;
}

/* Success / error messages */
.card-alert {
  border-radius: 10px;
  padding: 12px 16px;
  font-size: 0.95rem;
  margin-bottom: 18px;
}
.alert-success {
  background-color: #e7f8ef;
  color: #2d7a45;
  border-left: 4px solid #2d7a45;
}
.alert-danger {
  background-color: #fde8e8;
  color: #b30000;
  border-left: 4px solid #b30000;
}

/* List group styling */
.list-group {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.list-group-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 18px 20px;
  border: none;
  background-color: #fff8f8;
  border-radius: 12px;
  text-decoration: none;
  color: #4a1f1f;
  font-weight: 600;
  transition: transform 0.18s ease, background-color 0.18s ease, color 0.18s ease;
}

.list-group-item i {
  font-size: 1.3rem;
  color: #b30000;
  transition: color 0.18s ease;
}

.list-group-item:hover {
  transform: translateY(-4px);
  background-color: #b30000;
  color: #fff;
}
.list-group-item:hover i {
  color: #fff;
}

/* RESPONSIVE DESIGN */
@media (max-width: 1024px) { /* iPads and tablets */
  .page-wrapper {
    padding: 40px 15px;
  }
  .dashboard-header h1 {
    font-size: 1.6rem;
  }
  .card-body {
    padding: 24px 20px;
  }
}

@media (max-width: 768px) { /* smaller tablets and large phones */
  .dashboard-card {
    max-width: 90%;
  }
  .dashboard-header {
    padding: 20px 15px;
  }
  .dashboard-header h1 {
    font-size: 1.4rem;
  }
  .list-group-item {
    padding: 16px 16px;
    font-size: 0.95rem;
  }
}

@media (max-width: 480px) { /* phones */
  .page-wrapper {
    padding: 30px 10px;
  }
  .dashboard-card {
    max-width: 100%;
    border-radius: 12px;
  }
  .dashboard-header h1 {
    font-size: 1.2rem;
  }
  .list-group-item {
    padding: 14px 14px;
    font-size: 0.9rem;
  }
}
</style>

<div class="page-wrapper">
  <div class="dashboard-card">
    <div class="dashboard-header">
      <h1><i class=""></i> Admin Dashboard</h1>
    </div>

    <div class="card-body">
      <?php if (!empty($_SESSION['success'])): ?>
        <div class="card-alert alert-success">
          <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($_SESSION['error'])): ?>
        <div class="card-alert alert-danger">
          <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>

      <div class="list-group">
        <a class="list-group-item" href="approve_users.php">
          <i class="bi bi-person-check-fill"></i> Approve / Reject Users
        </a>
        <a class="list-group-item" href="list.php">
          <i class="bi bi-box-seam"></i> Inventory Dashboard
        </a>
        <a class="list-group-item" href="medreps.php">
          <i class="bi bi-people-fill"></i> MedRep Dashboard
        </a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../inc/footer.php'; ?>
