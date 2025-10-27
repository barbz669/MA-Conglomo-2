<?php
// inc/header_back_reg.php
require_once __DIR__ . '/../config/session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MAConglomo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      background: linear-gradient(135deg, #fff7f7, #ffeaea);
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
    }

    /* 🔙 Floating Red Back Button */
    .top-back-btn {
      position: fixed;
      top: 20px;
      left: 20px;
      color:  #b30000;
      font-size: 2.5rem;
      text-decoration: none;
      z-index: 9999;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .top-back-btn:hover {
      color: #a93226;
      transform: scale(1.1);
    }

    /* 🧭 Centered container for content */
    .page-content {
      width: 100%;
      max-width: 450px;
      background-color: #fff;
      border-radius: 20px;
      padding: 40px 30px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
      text-align: center;
      box-sizing: border-box;
      margin-top: 50px; /* ensures button doesn’t overlap */
    }

    @media (max-width: 1024px) {
      .top-back-btn {
        top: 15px;
        left: 15px;
        font-size: 2.2rem;
      }

      .page-content {
        margin-top: 80px;
        max-width: 90%;
        padding: 35px 25px;
      }
    }
  </style>
</head>

<body>
  <!-- 🔙 Floating Back Button -->
  <a href="login.php" class="top-back-btn" title="Go Back">
    <i class="bi bi-arrow-left-circle-fill"></i>
  </a>

  <!-- ✅ Centered Registration Container -->
  <div class="page-content">
