<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit();
}

require_once "config/database.php";

$fullname = $_SESSION["fullname"];
$role = $_SESSION["role"];

$studentCountResult = $conn->query("SELECT COUNT(*) AS total FROM students");
$studentCount = (int) $studentCountResult->fetch_assoc()["total"];

$paymentSummaryResult = $conn->query(
    "SELECT
        COALESCE(SUM(CASE
            WHEN status = 'Paid' AND payment_date = CURRENT_DATE() THEN amount
            ELSE 0
        END), 0) AS today_collection,
        COUNT(CASE WHEN payment_date = CURRENT_DATE() THEN 1 END) AS today_transactions,
        COALESCE(SUM(CASE WHEN status = 'Paid' THEN amount ELSE 0 END), 0) AS total_collection,
        COUNT(*) AS total_transactions
     FROM payments"
);
$paymentSummary = $paymentSummaryResult->fetch_assoc();
$todayCollection = (float) $paymentSummary["today_collection"];
$todayTransactions = (int) $paymentSummary["today_transactions"];
$totalCollection = (float) $paymentSummary["total_collection"];
$totalTransactions = (int) $paymentSummary["total_transactions"];

$recentPaymentsResult = $conn->query(
    "SELECT
        p.id,
        p.reference_no,
        p.amount,
        p.payment_date,
        CONCAT_WS(' ', s.first_name, s.middle_name, s.last_name) AS student_name
     FROM payments p
     LEFT JOIN students s ON p.student_id = s.id
     ORDER BY p.id DESC
     LIMIT 5"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Online Resibo</title>

    <link rel="stylesheet" href="adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="assets/css/table-gridlines.css">
    <style>
        .student-summary-card {
            display: flex;
            flex-direction: column;
        }

        .student-summary-card > .inner {
            flex: 1;
        }

        .student-summary-card .student-count {
            font-size: 3rem;
        }

        .student-summary-card .student-label {
            font-size: 1.25rem;
        }

        .student-summary-card > .small-box-footer {
            margin-top: auto;
        }
    </style>

</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

    <div class="app-wrapper">


        <!-- =========================
         NAVBAR
    ========================== -->

        <nav class="app-header navbar navbar-expand bg-body">

            <div class="container-fluid">

                <ul class="navbar-nav">

                    <li class="nav-item">

                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">

                            ☰

                        </a>

                    </li>

                </ul>


                <ul class="navbar-nav ms-auto">

                    <li class="nav-item dropdown">

                        <a class="nav-link" href="#">

                            <?= htmlspecialchars($fullname) ?>

                            <span class="badge text-bg-primary ms-1">

                                <?= htmlspecialchars($role) ?>

                            </span>

                        </a>

                    </li>

                    <li class="nav-item">

                        <a class="nav-link text-danger" href="auth/logout.php">

                            Logout

                        </a>

                    </li>

                </ul>

            </div>

        </nav>



        <!-- =========================
         SIDEBAR
    ========================== -->

        <!-- SIDEBAR -->
        <aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">

            <div class="sidebar-brand">
                <a href="dashboard.php" class="brand-link">
                    <span class="brand-text fw-light">
                        🏫 <strong>ONLINE RESIBO</strong>
                    </span>
                </a>
            </div>

            <div class="sidebar-wrapper">
                <nav>
                    <ul class="nav sidebar-menu flex-column">

                        <!-- DASHBOARD -->
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link active">
                                <p>🏠 &nbsp; Dashboard</p>
                            </a>
                        </li>

                        <!-- STUDENTS -->
                        <li class="nav-item">
                            <a href="students/index.php" class="nav-link">
                                <p>👨‍🎓 &nbsp; Students</p>
                            </a>
                        </li>

                        <?php if (($_SESSION["role"] ?? "") === "Admin"): ?>
                            <li class="nav-item">
                                <a href="programs/index.php" class="nav-link">
                                    <p>📚 &nbsp; Courses / Programs</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (($_SESSION["role"] ?? "") === "Admin"): ?>
                            <li class="nav-item">
                                <a href="departments/index.php" class="nav-link">
                                    <p>🏢 &nbsp; Departments</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- PAYMENTS -->
                        <li class="nav-item">
                            <a href="payments/index.php" class="nav-link">
                                <p>💳 &nbsp; Payments</p>
                            </a>
                        </li>

                        <?php if (($_SESSION["role"] ?? "") === "Admin"): ?>
                            <li class="nav-item">
                                <a href="payment-types/index.php" class="nav-link">
                                    <p>🏷️ &nbsp; Payment Types</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- RECEIPTS -->
                        <li class="nav-item">
                            <a href="receipts/index.php" class="nav-link">
                                <p>🧾 &nbsp; Receipts</p>
                            </a>
                        </li>

                        <!-- REPORTS -->
                        <li class="nav-item">
                            <a href="reports/index.php" class="nav-link">
                                <p>📊 &nbsp; Reports</p>
                            </a>
                        </li>

                        <!-- USERS -->
                        <?php if ($_SESSION["role"] === "Admin"): ?>
                            <li class="nav-item">
                                <a href="users/index.php" class="nav-link">
                                    <p>👤 &nbsp; Users</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- SYSTEM -->
                        <li class="nav-header">
                            SYSTEM
                        </li>

                        <!-- LOGOUT -->
                        <li class="nav-item">
                            <a href="auth/logout.php" class="nav-link text-danger">
                                <p>🚪 &nbsp; Logout</p>
                            </a>
                        </li>

                    </ul>
                </nav>
            </div>

        </aside>



        <!-- =========================
         MAIN CONTENT
    ========================== -->

        <main class="app-main">


            <!-- CONTENT HEADER -->

            <div class="app-content-header">

                <div class="container-fluid">

                    <div class="row">

                        <div class="col-sm-6">

                            <h3 class="mb-0">
                                Dashboard
                            </h3>

                        </div>

                        <div class="col-sm-6">

                            <ol class="breadcrumb float-sm-end">

                                <li class="breadcrumb-item">

                                    Home

                                </li>

                                <li class="breadcrumb-item active">

                                    Dashboard

                                </li>

                            </ol>

                        </div>

                    </div>

                </div>

            </div>



            <!-- CONTENT -->

            <div class="app-content">

                <div class="container-fluid">


                    <!-- DASHBOARD CARDS -->

                    <div class="row">


                        <!-- Students -->

                        <div class="col-lg-4 col-md-6 d-flex">

                            <div class="small-box text-bg-primary w-100 student-summary-card">

                                <div class="inner">

                                    <h3 class="student-count"><?= number_format($studentCount) ?></h3>

                                    <p class="student-label">
                                        Total Students
                                    </p>

                                </div>

                                <div class="small-box-icon">

                                    👨‍🎓

                                </div>

                                <a href="students/index.php" class="small-box-footer">

                                    Manage Students
                                    →

                                </a>

                            </div>

                        </div>



                        <!-- Payments -->

                        <div class="col-lg-4 col-md-6 d-flex">

                            <div class="small-box text-bg-success w-100">

                                <div class="inner">

                                    <h3>₱<?= number_format($totalCollection, 2) ?></h3>

                                    <p>
                                        Total Collection
                                    </p>
                                    <small>Today: ₱<?= number_format($todayCollection, 2) ?></small>

                                </div>

                                <div class="small-box-icon">

                                    💰

                                </div>

                                <a href="payments/index.php" class="small-box-footer">

                                    Manage Payments
                                    →

                                </a>

                            </div>

                        </div>



                        <!-- Transactions -->

                        <div class="col-lg-4 col-md-6 d-flex">

                            <div class="small-box text-bg-warning w-100">

                                <div class="inner">

                                    <h3><?= number_format($totalTransactions) ?></h3>

                                    <p>
                                        Total Transactions
                                    </p>
                                    <small>Today: <?= number_format($todayTransactions) ?></small>

                                </div>

                                <div class="small-box-icon">

                                    🧾

                                </div>

                                <a href="receipts/index.php" class="small-box-footer">

                                    View Receipts
                                    →

                                </a>

                            </div>

                        </div>

                    </div>

                    <!-- RECENT PAYMENTS -->

                    <div class="card">

                        <div class="card-header">

                            <h3 class="card-title">

                                Recent Payments

                            </h3>

                            <div class="card-tools">

                                <a href="payments/index.php" class="btn btn-primary btn-sm">

                                    View All

                                </a>

                            </div>

                        </div>


                        <div class="card-body p-0">

                            <div class="table-responsive">

                                <table class="table table-hover mb-0">

                                    <thead>

                                        <tr>

                                            <th>ID</th>
                                            <th>Receipt No.</th>

                                            <th>Student</th>

                                            <th>Amount</th>

                                            <th>Date</th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        <?php if ($recentPaymentsResult->num_rows > 0): ?>
                                            <?php while ($payment = $recentPaymentsResult->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= (int) $payment["id"] ?></td>
                                                    <td>
                                                        <a href="receipts/view.php?id=<?= (int) $payment["id"] ?>">
                                                            <?= htmlspecialchars($payment["reference_no"]) ?>
                                                        </a>
                                                    </td>
                                                    <td><?= htmlspecialchars($payment["student_name"] ?? "Unknown Student") ?></td>
                                                    <td>₱<?= number_format((float) $payment["amount"], 2) ?></td>
                                                    <td><?= htmlspecialchars($payment["payment_date"]) ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    No payments recorded yet.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>



        <!-- FOOTER -->

        <footer class="app-footer">

            <strong>
                Online Resibo
            </strong>

            &copy; 2026

            <div class="float-end d-none d-sm-inline">

                School Payment Management System

            </div>

        </footer>


    </div>


    <script src="adminlte/dist/js/adminlte.min.js"></script>
</body>

</html>