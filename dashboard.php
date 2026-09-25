<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit();
}

$fullname = $_SESSION["fullname"];
$role = $_SESSION["role"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Online Resibo</title>

    <link rel="stylesheet" href="adminlte/dist/css/adminlte.min.css">

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

                        <!-- PAYMENTS -->
                        <li class="nav-item">
                            <a href="payments/index.php" class="nav-link">
                                <p>💳 &nbsp; Payments</p>
                            </a>
                        </li>

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


                    <!-- Welcome -->

                    <div class="alert alert-primary">

                        <h5 class="mb-1">

                            Welcome,
                            <?= htmlspecialchars($fullname) ?>!

                        </h5>

                        <p class="mb-0">

                            You are logged in as
                            <strong><?= htmlspecialchars($role) ?></strong>.

                        </p>

                    </div>



                    <!-- DASHBOARD CARDS -->

                    <div class="row">


                        <!-- Students -->

                        <div class="col-lg-4 col-md-6">

                            <div class="small-box text-bg-primary">

                                <div class="inner">

                                    <h3>0</h3>

                                    <p>
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

                        <div class="col-lg-4 col-md-6">

                            <div class="small-box text-bg-success">

                                <div class="inner">

                                    <h3>₱0.00</h3>

                                    <p>
                                        Today's Collection
                                    </p>

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

                        <div class="col-lg-4 col-md-6">

                            <div class="small-box text-bg-warning">

                                <div class="inner">

                                    <h3>0</h3>

                                    <p>
                                        Today's Transactions
                                    </p>

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

                                            <th>Receipt No.</th>

                                            <th>Student</th>

                                            <th>Amount</th>

                                            <th>Date</th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">

                                                No payments recorded yet.
                                            </td>
                                        </tr>
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