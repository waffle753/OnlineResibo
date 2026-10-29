<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Manual - Online Resibo</title>
    <link rel="stylesheet" href="adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script defer src="assets/js/sidebar-icons.js"></script>
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-mini bg-body-tertiary">
<div class="app-wrapper">
    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-lte-toggle="sidebar" href="#" aria-label="Toggle navigation">
                        <i class="bi bi-list"></i>
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="nav-link">
                        <?= htmlspecialchars($_SESSION["fullname"] ?? "User") ?>
                        <span class="badge text-bg-primary"><?= htmlspecialchars($_SESSION["role"] ?? "") ?></span>
                    </span>
                </li>
                <li class="nav-item"><a href="auth/logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="dashboard.php" class="brand-link"><span class="brand-text fw-light">🏫 <strong>ONLINE RESIBO</strong></span></a>
        </div>
        <div class="sidebar-wrapper">
            <nav>
                <ul class="nav sidebar-menu flex-column">
                    <li class="nav-item"><a href="dashboard.php" class="nav-link"><p>🏠 &nbsp; Dashboard</p></a></li>
                    <li class="nav-item"><a href="students/index.php" class="nav-link"><p>👨‍🎓 &nbsp; Students</p></a></li>
                    <li class="nav-item"><a href="programs/index.php" class="nav-link"><p>📚 &nbsp; Courses / Programs</p></a></li>
                    <li class="nav-item"><a href="departments/index.php" class="nav-link"><p>🏢 &nbsp; Departments</p></a></li>
                    <li class="nav-item"><a href="payments/index.php" class="nav-link"><p>💳 &nbsp; Payments</p></a></li>
                    <li class="nav-item"><a href="payment-types/index.php" class="nav-link"><p>🏷️ &nbsp; Payment Types</p></a></li>
                    <li class="nav-item"><a href="receipts/index.php" class="nav-link"><p>🧾 &nbsp; Receipts</p></a></li>
                    <li class="nav-item"><a href="reports/index.php" class="nav-link"><p>📊 &nbsp; Reports</p></a></li>
                    <?php if (($_SESSION["role"] ?? "") === "Admin"): ?>
                        <li class="nav-item"><a href="users/index.php" class="nav-link"><p>👤 &nbsp; Users</p></a></li>
                    <?php endif; ?>
                    <li class="nav-header">SYSTEM</li>
                    <li class="nav-item"><a href="user-manual.php" class="nav-link active"><p>User Manual</p></a></li>
                    <li class="nav-item"><a href="auth/logout.php" class="nav-link text-danger"><p>🚪 &nbsp; Logout</p></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6"><h3 class="mb-0">User Manual</h3></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">User Manual</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Getting Started</h3></div>
                    <div class="card-body">
                        <p>Use the sidebar to open each module. Staff can manage students, courses/programs, departments, payments, payment types, receipts, and reports. Only Admins can manage user accounts.</p>
                        <p>Most tables include search and pagination. Use the rows-per-page dropdown to choose how many records to display, and use the page controls to browse the rest.</p>
                        <div class="accordion" id="manualSections">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#studentsHelp" aria-expanded="true" aria-controls="studentsHelp">Students</button>
                                </h2>
                                <div id="studentsHelp" class="accordion-collapse collapse show" data-bs-parent="#manualSections">
                                    <div class="accordion-body">
                                        <p>To add or edit a student, enter the required student ID, first name, last name, course/program, department, year level, and status. The middle name and email address are optional.</p>
                                        <ul>
                                            <li><strong>Student ID:</strong> enter exactly 9 digits. It must be unique; an ID already assigned to another student cannot be saved.</li>
                                            <li><strong>First, middle, and last names:</strong> use 2–50 characters for each name you enter. Names may contain letters, spaces, apostrophes, and hyphens, but must not contain numbers or other characters. First and last names are required; middle name is optional.</li>
                                            <li><strong>Email:</strong> enter a valid email address (up to 254 characters) if the student should receive receipt emails. Leave it blank if they do not have one.</li>
                                            <li><strong>Course/program and department:</strong> select from the available lists. If an option is missing, ask staff with access to maintain those lists to add it first.</li>
                                            <li><strong>Year level and status:</strong> choose an available year level and set the student to Active or Inactive as appropriate.</li>
                                        </ul>
                                        Use <strong>History</strong> in the student table to review the student's payments. The history window has its own page-size dropdown and page controls.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#paymentsHelp" aria-expanded="false" aria-controls="paymentsHelp">Payments and email receipts</button>
                                </h2>
                                <div id="paymentsHelp" class="accordion-collapse collapse" data-bs-parent="#manualSections">
                                    <div class="accordion-body">
                                        <p>Create a payment by selecting the correct student and configured payment type, then entering the amount, payment method, date, and status. Check the student and amount before saving to avoid recording the payment against the wrong account.</p>
                                        <p>Enter a positive amount and choose a valid payment date, method, and status. Use <strong>Paid</strong> only after payment has been received; use <strong>Pending</strong> or <strong>Cancelled</strong> when appropriate.</p>
                                        <p>A receipt email is queued when a payment is first recorded as <strong>Paid</strong> or changes to Paid. Saving does not wait for email delivery. The student's saved email must be valid, and the server mail configuration must be working. A queued email can still fail later; that does not undo the payment. If the payment is saved but the email fails, notify an Admin to check the mail setup.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#receiptsHelp" aria-expanded="false" aria-controls="receiptsHelp">Receipts</button>
                                </h2>
                                <div id="receiptsHelp" class="accordion-collapse collapse" data-bs-parent="#manualSections">
                                    <div class="accordion-body">
                                        Open Receipts to find, view, or print receipts for recorded payments. Check the receipt reference and student details against the transaction before printing or sharing it.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#reportsHelp" aria-expanded="false" aria-controls="reportsHelp">Reports</button>
                                </h2>
                                <div id="reportsHelp" class="accordion-collapse collapse" data-bs-parent="#manualSections">
                                    <div class="accordion-body">
                                        Filter payment reports by date, status, department, and course/program. Review the filters and results before using <strong>Export CSV</strong> to download matching transactions or <strong>Print</strong> to print the report. Report pagination changes the displayed page; CSV export includes all transactions matching the selected filters.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#setupHelp" aria-expanded="false" aria-controls="setupHelp">Courses, departments, and payment types</button>
                                </h2>
                                <div id="setupHelp" class="accordion-collapse collapse" data-bs-parent="#manualSections">
                                    <div class="accordion-body">
                                        Maintain the course/program and department lists used by student forms, and the payment-type list used by payment forms. Add or update the correct entries before using them in other modules. Use clear, recognizable names and check existing entries first to avoid duplicates. Codes identify entries; choose the matching name when selecting an item in a student or payment form.
                                    </div>
                                </div>
                            </div>
                            <?php if (($_SESSION["role"] ?? "") === "Admin"): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#usersHelp" aria-expanded="false" aria-controls="usersHelp">User accounts (Admin)</button>
                                    </h2>
                                    <div id="usersHelp" class="accordion-collapse collapse" data-bs-parent="#manualSections">
                                        <div class="accordion-body">
                                            Admins can add and manage user accounts and assign the Admin or Staff role. Give each person their own account and assign Admin access only to people who need to manage user accounts. Staff can use the operational menus but cannot access User Management.
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="app-footer"><strong>Online Resibo</strong> &copy; 2026 <span class="ms-2">Created by <strong>Kent David T. Abejo</strong></span></footer>
</div>
<script src="adminlte/dist/js/adminlte.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
