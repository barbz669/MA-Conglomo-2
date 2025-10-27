<?php
// inc/header_back_inventory.php
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
        .title {
            font-family: 'Lato', sans-serif;
        }

        /* --- Table layout --- */
        .table {
            table-layout: fixed;
            width: 100%;
        }

        .table th,
        .table td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: center;
        }

        .table th:nth-child(1),
        .table td:nth-child(1) {
            text-align: left;
            width: 20%;
        }

        .table th:nth-child(2),
        .table td:nth-child(2) {
            width: 20%;
        }

        .table th:nth-child(3),
        .table td:nth-child(3) {
            width: 10%;
        }

        .table th:nth-child(4),
        .table td:nth-child(4) {
            width: 15%;
        }

        .table th:nth-child(5),
        .table td:nth-child(5) {
            width: 15%;
        }

        .table th:nth-child(6),
        .table td:nth-child(6) {
            width: 10%;
        }

        .table th:nth-child(7),
        .table td:nth-child(7) {
            width: 10%;
        }

        /* --- Floating Red Back Button --- */
        .top-back-btn {
            position: fixed;
            top: 25px;
            left: 25px;
            color: #b30000;
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

        @media (max-width: 768px) {
            .top-back-btn {
                top: 15px;
                left: 15px;
                font-size: 2.2rem;
            }
        }
    </style>
</head>

<body>
    <!-- 🔙 Floating Back Button to Inventory -->
   <a href="dashboard.php" class="top-back-btn" title="Back to Inventory">
    <i class="bi bi-arrow-left-circle-fill"></i>
</a>


    <!-- Keeps page structure identical to header.php -->
    <div class="container">
