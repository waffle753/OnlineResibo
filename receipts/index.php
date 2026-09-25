<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/database.php";

$sql = "
    SELECT
        p.id,
        p.reference_no,
        p.student_id,
        p.payment_type,
        p.amount,
        p.payment_date,
        p.payment_method,
        p.status,
        s.student_id AS student_number,
        s.fullname
    FROM payments p
    LEFT JOIN students s ON p.student_id = s.id
    ORDER BY p.id DESC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipts - Online Resibo</title>
    <link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

    <div class="app-wrapper">

        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#">
                            <i class="bi bi-list"></i>
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">
                            <?= htmlspecialchars($_SESSION["fullname"] ?? "User") ?>
                            <span class="badge text-bg-primary">
                                <?= htmlspecialchars($_SESSION["role"] ?? "") ?>
                            </span>
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
                    <span class="brand-text fw-light">
                        🏫 <strong>ONLINE RESIBO</strong>
                    </span>
                </a>
            </div>

            <div class="sidebar-wrapper">
                <nav>
                    <ul class="nav sidebar-menu flex-column">

                        <li class="nav-item">
                            <a href="../dashboard.php" class="nav-link">
                                <p>🏠 &nbsp; Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="../students/index.php" class="nav-link">
                                <p>👨‍🎓 &nbsp; Students</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="../payments/index.php" class="nav-link">
                                <p>💳 &nbsp; Payments</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="index.php" class="nav-link active">
                                <p>🧾 &nbsp; Receipts</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="../reports/index.php" class="nav-link">
                                <p>📊 &nbsp; Reports</p>
                            </a>
                        </li>

                        <?php if (($_SESSION["role"] ?? "") === "Admin"): ?>
                            <li class="nav-item">
                                <a href="../users/index.php" class="nav-link">
                                    <p>👤 &nbsp; Users</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <li class="nav-header">SYSTEM</li>

                        <li class="nav-item">
                            <a href="../auth/logout.php" class="nav-link text-danger">
                                <p>🚪 &nbsp; Logout</p>
                            </a>
                        </li>

                    </ul>
                </nav>
            </div>
        </aside>

        <main class="app-main">

            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0">Receipts</h3>
                        </div>

                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item">Home</li>
                                <li class="breadcrumb-item active">Receipts</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">

                    <div class="card">

                        <div class="card-header">
                            <h3 class="card-title">
                                <strong>Payment Receipts</strong>
                            </h3>
                        </div>

                        <div class="card-body">

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <input type="text" id="receiptSearch" class="form-control"
                                        placeholder="Search receipt...">
                                </div>
                            </div>

                            <div class="table-responsive">

                                <table class="table table-bordered table-hover" id="receiptTable">

                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Reference</th>
                                            <th>Student</th>
                                            <th>Payment Type</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th width="180">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <?php if ($result && $result->num_rows > 0): ?>

                                            <?php $count = 1; ?>

                                            <?php while ($receipt = $result->fetch_assoc()): ?>

                                                <tr>

                                                    <td>
                                                        <?= $count++ ?>
                                                    </td>

                                                    <td>
                                                        <strong>
                                                            <?= htmlspecialchars($receipt["reference_no"]) ?>
                                                        </strong>
                                                    </td>

                                                    <td>
                                                        <?= htmlspecialchars($receipt["fullname"] ?? "Unknown Student") ?>
                                                    </td>

                                                    <td>
                                                        <?= htmlspecialchars($receipt["payment_type"]) ?>
                                                    </td>

                                                    <td>
                                                        ₱<?= number_format((float) $receipt["amount"], 2) ?>
                                                    </td>

                                                    <td>
                                                        <span class="badge text-bg-primary">
                                                            <?= htmlspecialchars($receipt["payment_method"]) ?>
                                                        </span>
                                                    </td>

                                                    <td>
                                                        <?= htmlspecialchars($receipt["payment_date"]) ?>
                                                    </td>

                                                    <td>

                                                        <?php if ($receipt["status"] === "Paid"): ?>

                                                            <span class="badge text-bg-success">
                                                                Paid
                                                            </span>

                                                        <?php else: ?>

                                                            <span class="badge text-bg-secondary">
                                                                <?= htmlspecialchars($receipt["status"]) ?>
                                                            </span>

                                                        <?php endif; ?>

                                                    </td>

                                                    <td>
                                                        <button type="button" class="btn btn-info btn-sm view-receipt-btn"
                                                            data-id="<?= (int) $receipt["id"] ?>"
                                                            data-reference="<?= htmlspecialchars($receipt["reference_no"], ENT_QUOTES, "UTF-8") ?>"
                                                            data-student="<?= htmlspecialchars($receipt["fullname"] ?? "Unknown Student", ENT_QUOTES, "UTF-8") ?>"
                                                            data-student-number="<?= htmlspecialchars($receipt["student_number"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                                                            data-type="<?= htmlspecialchars($receipt["payment_type"], ENT_QUOTES, "UTF-8") ?>"
                                                            data-amount="<?= htmlspecialchars($receipt["amount"], ENT_QUOTES, "UTF-8") ?>"
                                                            data-method="<?= htmlspecialchars($receipt["payment_method"], ENT_QUOTES, "UTF-8") ?>"
                                                            data-date="<?= htmlspecialchars($receipt["payment_date"], ENT_QUOTES, "UTF-8") ?>"
                                                            data-status="<?= htmlspecialchars($receipt["status"], ENT_QUOTES, "UTF-8") ?>">
                                                            <i class="bi bi-eye"></i>
                                                            View
                                                        </button>

                                                        <a href="print.php?id=<?= (int) $receipt["id"] ?>"
                                                            class="btn btn-primary btn-sm" target="_blank">
                                                            <i class="bi bi-printer"></i>
                                                            Print
                                                        </a>
                                                    </td>

                                                </tr>

                                            <?php endwhile; ?>

                                        <?php else: ?>

                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">
                                                    No receipts available.
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

        <footer class="app-footer">
            <strong>Online Resibo</strong>
            &copy; 2026
        </footer>

    </div>

    <div class="modal fade" id="viewReceiptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-receipt"></i>
                        Payment Receipt
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body">

                    <div class="text-center mb-4">
                        <h4 class="mb-1">🏫 ONLINE RESIBO</h4>
                        <small class="text-muted">
                            Payment Receipt
                        </small>
                    </div>

                    <div class="border rounded p-3">

                        <div class="row mb-2">
                            <div class="col-5">
                                <strong>Reference:</strong>
                            </div>
                            <div class="col-7" id="view_reference"></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-5">
                                <strong>Student ID:</strong>
                            </div>
                            <div class="col-7" id="view_student_number"></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-5">
                                <strong>Student:</strong>
                            </div>
                            <div class="col-7" id="view_student"></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-5">
                                <strong>Payment Type:</strong>
                            </div>
                            <div class="col-7" id="view_type"></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-5">
                                <strong>Amount:</strong>
                            </div>
                            <div class="col-7">
                                ₱<span id="view_amount"></span>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-5">
                                <strong>Method:</strong>
                            </div>
                            <div class="col-7" id="view_method"></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-5">
                                <strong>Date:</strong>
                            </div>
                            <div class="col-7" id="view_date"></div>
                        </div>

                        <div class="row">
                            <div class="col-5">
                                <strong>Status:</strong>
                            </div>
                            <div class="col-7">
                                <span class="badge text-bg-success" id="view_status"></span>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>

                    <a href="#" id="modalPrintButton" class="btn btn-primary" target="_blank">
                        <i class="bi bi-printer"></i>
                        Print Receipt
                    </a>

                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../adminlte/dist/js/adminlte.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const search = document.getElementById("receiptSearch");

            if (search) {
                search.addEventListener("keyup", function () {
                    const value = this.value.toLowerCase().trim();
                    const rows = document.querySelectorAll("#receiptTable tbody tr");

                    rows.forEach(function (row) {
                        row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none";
                    });
                });
            }
        });

        const viewButtons = document.querySelectorAll(".view-receipt-btn");

        viewButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                document.getElementById("view_reference").textContent = this.dataset.reference;
                document.getElementById("view_student_number").textContent = this.dataset.studentNumber;
                document.getElementById("view_student").textContent = this.dataset.student;
                document.getElementById("view_type").textContent = this.dataset.type;
                document.getElementById("view_amount").textContent = parseFloat(this.dataset.amount).toFixed(2);
                document.getElementById("view_method").textContent = this.dataset.method;
                document.getElementById("view_date").textContent = this.dataset.date;
                document.getElementById("view_status").textContent = this.dataset.status;

                document.getElementById("modalPrintButton").href =
                    "print.php?id=" + this.dataset.id;

                const modal = bootstrap.Modal.getOrCreateInstance(
                    document.getElementById("viewReceiptModal")
                );

                modal.show();
            });
        });
    </script>

</body>

</html>