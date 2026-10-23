<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/database.php";

$fromDate = $_GET["from_date"] ?? "";
$toDate = $_GET["to_date"] ?? "";
$status = $_GET["status"] ?? "All";
$export = $_GET["export"] ?? "";

if (!is_string($fromDate) || !is_string($toDate) || !is_string($status) || !is_string($export)) {
    header("Location: index.php?report_error=invalid_filters");
    exit();
}

$validDate = static function (string $date): bool {
    if ($date === "") {
        return true;
    }

    $parsed = DateTime::createFromFormat("!Y-m-d", $date);
    $errors = DateTime::getLastErrors();

    return $parsed !== false
        && $parsed->format("Y-m-d") === $date
        && ($errors === false || ($errors["warning_count"] === 0 && $errors["error_count"] === 0));
};

if (!$validDate($fromDate) || !$validDate($toDate)) {
    header("Location: index.php?report_error=invalid_dates");
    exit();
}

if ($fromDate !== "" && $toDate !== "" && $fromDate > $toDate) {
    header("Location: index.php?report_error=date_order");
    exit();
}

if (!in_array($status, ["All", "Paid", "Pending", "Cancelled"], true)) {
    header("Location: index.php?report_error=invalid_status");
    exit();
}

if ($export !== "" && $export !== "csv") {
    header("Location: index.php?report_error=invalid_export");
    exit();
}

$filterSql = "
    WHERE (? IS NULL OR p.payment_date >= ?)
      AND (? IS NULL OR p.payment_date <= ?)
      AND (? = 'All' OR p.status = ?)
";
$fromDateFilter = $fromDate === "" ? null : $fromDate;
$toDateFilter = $toDate === "" ? null : $toDate;

$summaryStmt = $conn->prepare(
    "SELECT
        COUNT(*) AS total_transactions,
        COALESCE(SUM(CASE WHEN p.status = 'Paid' THEN p.amount ELSE 0 END), 0) AS paid_total,
        COALESCE(SUM(CASE WHEN p.status = 'Pending' THEN p.amount ELSE 0 END), 0) AS pending_total,
        COUNT(CASE WHEN p.status = 'Paid' THEN 1 END) AS paid_transactions,
        COUNT(CASE WHEN p.status = 'Pending' THEN 1 END) AS pending_transactions,
        COUNT(CASE WHEN p.status = 'Cancelled' THEN 1 END) AS cancelled_transactions
     FROM payments p
     $filterSql"
);

if (!$summaryStmt) {
    die("Unable to prepare report summary: " . $conn->error);
}

$summaryStmt->bind_param(
    "ssssss",
    $fromDateFilter,
    $fromDate,
    $toDateFilter,
    $toDate,
    $status,
    $status
);

if (!$summaryStmt->execute()) {
    die("Unable to load report summary: " . $summaryStmt->error);
}

$summary = $summaryStmt->get_result()->fetch_assoc();
$summaryStmt->close();

$paymentsStmt = $conn->prepare(
    "SELECT
        p.reference_no,
        p.payment_date,
        s.student_id AS student_number,
        CONCAT_WS(' ', s.first_name, s.middle_name, s.last_name) AS student_name,
        p.payment_type,
        p.payment_method,
        p.amount,
        p.status
     FROM payments p
     LEFT JOIN students s ON p.student_id = s.id
     $filterSql
     ORDER BY p.payment_date DESC, p.id DESC"
);

if (!$paymentsStmt) {
    die("Unable to prepare report details: " . $conn->error);
}

$paymentsStmt->bind_param(
    "ssssss",
    $fromDateFilter,
    $fromDate,
    $toDateFilter,
    $toDate,
    $status,
    $status
);

if (!$paymentsStmt->execute()) {
    die("Unable to load report details: " . $paymentsStmt->error);
}

$paymentsResult = $paymentsStmt->get_result();

if ($export === "csv") {
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=online-resibo-report-" . date("Y-m-d") . ".csv");

    $output = fopen("php://output", "w");

    if ($output === false) {
        die("Unable to create CSV report.");
    }

    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, ["Reference", "Date", "Student ID", "Student", "Payment Type", "Method", "Amount", "Status"]);

    $safeCsvText = static function (?string $value): string {
        $value = $value ?? "";

        if (preg_match('/^[\s]*[=+\-@]/', $value) === 1) {
            return "'" . $value;
        }

        return $value;
    };

    while ($payment = $paymentsResult->fetch_assoc()) {
        fputcsv($output, [
            $safeCsvText($payment["reference_no"]),
            $payment["payment_date"],
            $safeCsvText($payment["student_number"]),
            $safeCsvText($payment["student_name"]),
            $safeCsvText($payment["payment_type"]),
            $safeCsvText($payment["payment_method"]),
            $payment["amount"],
            $safeCsvText($payment["status"])
        ]);
    }

    fclose($output);
    $paymentsStmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Online Resibo</title>
    <link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../assets/css/table-gridlines.css">
    <link rel="stylesheet" href="../assets/css/sweetalert2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @media print {
            .app-header,
            .app-sidebar,
            .app-footer,
            .report-filters,
            .report-actions {
                display: none !important;
            }

            .app-main {
                margin-left: 0 !important;
            }

            .card {
                box-shadow: none !important;
            }
        }
    </style>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
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
                <li class="nav-item">
                    <a href="../auth/logout.php" class="nav-link text-danger">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="../dashboard.php" class="brand-link">
                <span class="brand-text fw-light">🏫 <strong>ONLINE RESIBO</strong></span>
            </a>
        </div>
        <div class="sidebar-wrapper">
            <nav>
                <ul class="nav sidebar-menu flex-column">
                    <li class="nav-item"><a href="../dashboard.php" class="nav-link"><p>🏠 &nbsp; Dashboard</p></a></li>
                    <li class="nav-item"><a href="../students/index.php" class="nav-link"><p>👨‍🎓 &nbsp; Students</p></a></li>
                    <?php if (($_SESSION["role"] ?? "") === "Admin"): ?>
                        <li class="nav-item"><a href="../programs/index.php" class="nav-link"><p>📚 &nbsp; Courses / Programs</p></a></li>
                    <?php endif; ?>
                    <?php if (($_SESSION["role"] ?? "") === "Admin"): ?>
                        <li class="nav-item"><a href="../departments/index.php" class="nav-link"><p>🏢 &nbsp; Departments</p></a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a href="../payments/index.php" class="nav-link"><p>💳 &nbsp; Payments</p></a></li>
                    <?php if (($_SESSION["role"] ?? "") === "Admin"): ?>
                        <li class="nav-item"><a href="../payment-types/index.php" class="nav-link"><p>🏷️ &nbsp; Payment Types</p></a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a href="../receipts/index.php" class="nav-link"><p>🧾 &nbsp; Receipts</p></a></li>
                    <li class="nav-item"><a href="index.php" class="nav-link active"><p>📊 &nbsp; Reports</p></a></li>
                    <?php if (($_SESSION["role"] ?? "") === "Admin"): ?>
                        <li class="nav-item"><a href="../users/index.php" class="nav-link"><p>👤 &nbsp; Users</p></a></li>
                    <?php endif; ?>
                    <li class="nav-header">SYSTEM</li>
                    <li class="nav-item"><a href="../auth/logout.php" class="nav-link text-danger"><p>🚪 &nbsp; Logout</p></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6"><h3 class="mb-0">Payment Report</h3></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="../dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Reports</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-content">
            <div class="container-fluid">
                <div class="card report-filters mb-4">
                    <div class="card-header" style="background: #0d6efd; border-bottom: 1px solid #0d6efd;">
                        <h3 class="card-title text-white mb-0">Filter Report</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="index.php">
                            <div class="row align-items-end">
                                <div class="col-md-3 mb-3">
                                    <label for="from_date" class="form-label">From date</label>
                                    <input type="date" id="from_date" name="from_date" class="form-control"
                                        value="<?= htmlspecialchars($fromDate) ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="to_date" class="form-label">To date</label>
                                    <input type="date" id="to_date" name="to_date" class="form-control"
                                        value="<?= htmlspecialchars($toDate) ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="status" class="form-label">Payment status</label>
                                    <select id="status" name="status" class="form-select">
                                        <?php foreach (["All", "Paid", "Pending", "Cancelled"] as $statusOption): ?>
                                            <option value="<?= htmlspecialchars($statusOption) ?>"
                                                <?= $status === $statusOption ? "selected" : "" ?>>
                                                <?= $statusOption === "All" ? "All statuses" : htmlspecialchars($statusOption) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-fill">
                                        <i class="bi bi-search"></i> Generate
                                    </button>
                                    <a href="index.php" class="btn btn-outline-secondary">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row align-items-stretch">
                    <div class="col-lg-4 col-md-6 d-flex">
                        <div class="small-box text-bg-primary w-100">
                            <div class="inner">
                                <h3><?= number_format((int) $summary["total_transactions"]) ?></h3>
                                <p>Transactions in report</p>
                            </div>
                            <div class="small-box-icon"><i class="bi bi-receipt"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 d-flex">
                        <div class="small-box text-bg-success w-100">
                            <div class="inner">
                                <h3>₱<?= number_format((float) $summary["paid_total"], 2) ?></h3>
                                <p>Paid collection</p>
                                <small><?= number_format((int) $summary["paid_transactions"]) ?> paid</small>
                            </div>
                            <div class="small-box-icon"><i class="bi bi-cash-stack"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 d-flex">
                        <div class="small-box text-bg-warning w-100">
                            <div class="inner">
                                <h3>₱<?= number_format((float) $summary["pending_total"], 2) ?></h3>
                                <p>Pending amount</p>
                                <small><?= number_format((int) $summary["pending_transactions"]) ?> pending</small>
                            </div>
                            <div class="small-box-icon"><i class="bi bi-hourglass-split"></i></div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center flex-wrap p-3" style="gap: 5px;">
                        <h3 class="card-title mb-0">Payment Details</h3>
                        <div class="report-actions d-flex" style="gap: 5px; margin-left: 0;">
                            <a class="btn btn-success btn-sm"
                                href="?from_date=<?= urlencode($fromDate) ?>&amp;to_date=<?= urlencode($toDate) ?>&amp;status=<?= urlencode($status) ?>&amp;export=csv">
                                <i class="bi bi-download"></i> Export CSV
                            </a>
                            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                                <i class="bi bi-printer"></i> Print
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Reference</th>
                                        <th>Date</th>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Payment Type</th>
                                        <th>Method</th>
                                        <th class="text-end">Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($paymentsResult->num_rows > 0): ?>
                                        <?php while ($payment = $paymentsResult->fetch_assoc()): ?>
                                            <?php
                                            $statusClass = match ($payment["status"]) {
                                                "Paid" => "success",
                                                "Pending" => "warning",
                                                default => "danger"
                                            };
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($payment["reference_no"]) ?></td>
                                                <td><?= htmlspecialchars($payment["payment_date"]) ?></td>
                                                <td><?= htmlspecialchars($payment["student_number"] ?? "—") ?></td>
                                                <td><?= htmlspecialchars($payment["student_name"] ?? "Unknown Student") ?></td>
                                                <td><?= htmlspecialchars($payment["payment_type"]) ?></td>
                                                <td><?= htmlspecialchars($payment["payment_method"]) ?></td>
                                                <td class="text-end">₱<?= number_format((float) $payment["amount"], 2) ?></td>
                                                <td><span class="badge text-bg-<?= $statusClass ?>"><?= htmlspecialchars($payment["status"]) ?></span></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">No payments match the selected filters.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        <?= number_format((int) $summary["cancelled_transactions"]) ?> cancelled transaction(s) are included in the report.
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="app-footer">
        <strong>Online Resibo</strong> &copy; 2026
        <div class="float-end d-none d-sm-inline">School Payment Management System</div>
    </footer>
</div>

<script src="../adminlte/dist/js/adminlte.min.js"></script>
<script src="../assets/js/sweetalert2.all.min.js"></script>
<script>
const reportErrors = {
    invalid_filters: "The submitted report filters are invalid.",
    invalid_dates: "Enter valid dates for the report.",
    date_order: "The start date cannot be after the end date.",
    invalid_status: "Choose a valid payment status.",
    invalid_export: "The requested report export format is not supported."
};
const reportError = new URLSearchParams(window.location.search).get("report_error");

if (reportError && reportErrors[reportError]) {
    Swal.fire({
        icon: "error",
        title: "Unable to generate report",
        text: reportErrors[reportError],
        confirmButtonColor: "#dc3545"
    }).then(function () {
        window.history.replaceState({}, document.title, window.location.pathname);
    });
}
</script>
</body>
</html>