<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/database.php";

/* GET ACTIVE STUDENTS */
$studentsSql = "
    SELECT id, student_id, fullname
    FROM students
    WHERE status = 'Active'
    ORDER BY fullname ASC
";
$studentsResult = $conn->query($studentsSql);

/* GET PAYMENTS */
$paymentsSql = "
    SELECT
        p.id,
        p.student_id,
        p.payment_type,
        p.amount,
        p.payment_date,
        p.payment_method,
        p.status,
        p.reference_no,
        p.created_at,
        s.student_id AS student_number,
        s.fullname
    FROM payments p
    LEFT JOIN students s ON p.student_id = s.id
    ORDER BY p.id DESC
";
$paymentsResult = $conn->query($paymentsSql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payments - Online Resibo</title>

<link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

<div class="app-wrapper">

<!-- NAVBAR -->
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
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

<!-- SIDEBAR -->
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
                    <a href="index.php" class="nav-link active">
                        <p>💳 &nbsp; Payments</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="../receipts/index.php" class="nav-link">
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

<!-- MAIN -->
<main class="app-main">

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Payment Management</h3>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item">Home</li>
                    <li class="breadcrumb-item active">Payments</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
<div class="container-fluid">

<!-- ADD PAYMENT -->
<div class="card card-primary mb-4">

    <div class="card-header">
        <h3 class="card-title">
            <strong>Add Payment</strong>
        </h3>
    </div>

    <form action="save.php" method="POST">

        <div class="card-body">

            <div class="row">

                <!-- REFERENCE -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Payment Reference</label>

                    <input
                        type="text"
                        class="form-control"
                        value="AUTO GENERATED"
                        readonly
                    >

                    <small class="text-muted">
                        Payment reference will be generated automatically.
                    </small>
                </div>

                <!-- STUDENT -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Student</label>

                    <select name="student_id" class="form-select" required>
                        <option value="">Select Student</option>

                        <?php if ($studentsResult): ?>
                            <?php while ($student = $studentsResult->fetch_assoc()): ?>

                                <option value="<?= (int)$student["id"] ?>">
                                    <?= htmlspecialchars($student["student_id"]) ?>
                                    -
                                    <?= htmlspecialchars($student["fullname"]) ?>
                                </option>

                            <?php endwhile; ?>
                        <?php endif; ?>

                    </select>
                </div>

                <!-- PAYMENT TYPE -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Payment Type</label>

                    <select name="payment_type" class="form-select" required>
                        <option value="">Select Payment Type</option>
                        <option value="Tuition Fee">Tuition Fee</option>
                        <option value="Miscellaneous">Miscellaneous</option>
                        <option value="Laboratory Fee">Laboratory Fee</option>
                        <option value="School Uniform">School Uniform</option>
                        <option value="Books">Books</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- AMOUNT -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Amount</label>

                    <input
                        type="number"
                        name="amount"
                        class="form-control"
                        placeholder="0.00"
                        min="0.01"
                        step="0.01"
                        required
                    >
                </div>

                <!-- METHOD -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Payment Method</label>

                    <select name="payment_method" class="form-select" required>
                        <option value="">Select Payment Method</option>
                        <option value="Cash">Cash</option>
                        <option value="GCash">GCash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Online">Online</option>
                    </select>
                </div>

                <!-- DATE -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Payment Date</label>

                    <input
                        type="date"
                        name="payment_date"
                        class="form-control"
                        value="<?= date("Y-m-d") ?>"
                        required
                    >
                </div>

                <!-- STATUS -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>

                    <select name="status" class="form-select" required>
                        <option value="Paid">Paid</option>
                        <option value="Pending">Pending</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>

                <!-- SAVE -->
                <div class="col-md-4 mb-3 d-flex align-items-end">

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle"></i>
                        Save Payment
                    </button>

                </div>

            </div>

        </div>

    </form>

</div>

<!-- PAYMENT LIST -->
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Payment List</h3>
    </div>

    <div class="card-body">

        <!-- SEARCH -->
        <div class="row mb-3">

            <div class="col-md-4">

                <input
                    type="text"
                    id="paymentSearch"
                    class="form-control"
                    placeholder="Search payment..."
                >

            </div>

        </div>

        <!-- TABLE -->
        <div class="table-responsive">

            <table
                class="table table-bordered table-hover"
                id="paymentTable"
            >

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
                        <th>Action</th>
                    </tr>

                </thead>

                <tbody>

                <?php if ($paymentsResult && $paymentsResult->num_rows > 0): ?>

                    <?php $count = 1; ?>

                    <?php while ($payment = $paymentsResult->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?= $count++ ?>
                        </td>

                        <td>
                            <strong>
                                <?= htmlspecialchars($payment["reference_no"]) ?>
                            </strong>
                        </td>

                        <td>
                            <?= htmlspecialchars($payment["fullname"] ?? "Unknown Student") ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($payment["payment_type"]) ?>
                        </td>

                        <td>
                            ₱<?= number_format((float)$payment["amount"], 2) ?>
                        </td>

                        <td>
                            <span class="badge text-bg-primary">
                                <?= htmlspecialchars($payment["payment_method"]) ?>
                            </span>
                        </td>

                        <td>
                            <?= htmlspecialchars($payment["payment_date"]) ?>
                        </td>

                        <td>

                            <?php
                            $statusClass = "success";

                            if ($payment["status"] === "Pending") {
                                $statusClass = "warning";
                            }

                            if ($payment["status"] === "Cancelled") {
                                $statusClass = "danger";
                            }
                            ?>

                            <span class="badge text-bg-<?= $statusClass ?>">
                                <?= htmlspecialchars($payment["status"]) ?>
                            </span>

                        </td>

                        <td style="white-space: nowrap;">

                            <!-- VIEW -->
                            <button
                                type="button"
                                class="btn btn-info btn-sm view-payment-btn"
                                data-id="<?= (int)$payment["id"] ?>"
                                data-reference="<?= htmlspecialchars($payment["reference_no"], ENT_QUOTES, "UTF-8") ?>"
                                data-student="<?= htmlspecialchars($payment["fullname"] ?? "Unknown Student", ENT_QUOTES, "UTF-8") ?>"
                                data-student-number="<?= htmlspecialchars($payment["student_number"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                                data-type="<?= htmlspecialchars($payment["payment_type"], ENT_QUOTES, "UTF-8") ?>"
                                data-amount="<?= htmlspecialchars($payment["amount"], ENT_QUOTES, "UTF-8") ?>"
                                data-method="<?= htmlspecialchars($payment["payment_method"], ENT_QUOTES, "UTF-8") ?>"
                                data-date="<?= htmlspecialchars($payment["payment_date"], ENT_QUOTES, "UTF-8") ?>"
                                data-status="<?= htmlspecialchars($payment["status"], ENT_QUOTES, "UTF-8") ?>"
                                data-created="<?= htmlspecialchars($payment["created_at"], ENT_QUOTES, "UTF-8") ?>"
                            >
                                <i class="bi bi-eye"></i>
                                View
                            </button>

                            <!-- EDIT -->
                            <button
                                type="button"
                                class="btn btn-warning btn-sm edit-payment-btn"
                                data-id="<?= (int)$payment["id"] ?>"
                                data-reference="<?= htmlspecialchars($payment["reference_no"], ENT_QUOTES, "UTF-8") ?>"
                                data-student="<?= (int)$payment["student_id"] ?>"
                                data-type="<?= htmlspecialchars($payment["payment_type"], ENT_QUOTES, "UTF-8") ?>"
                                data-amount="<?= htmlspecialchars($payment["amount"], ENT_QUOTES, "UTF-8") ?>"
                                data-method="<?= htmlspecialchars($payment["payment_method"], ENT_QUOTES, "UTF-8") ?>"
                                data-date="<?= htmlspecialchars($payment["payment_date"], ENT_QUOTES, "UTF-8") ?>"
                                data-status="<?= htmlspecialchars($payment["status"], ENT_QUOTES, "UTF-8") ?>"
                            >
                                <i class="bi bi-pencil"></i>
                                Edit
                            </button>

                            <!-- DELETE -->
                            <a
                                href="delete.php?id=<?= (int)$payment["id"] ?>"
                                class="btn btn-danger btn-sm delete-payment-btn"
                                data-reference="<?= htmlspecialchars($payment["reference_no"], ENT_QUOTES, "UTF-8") ?>"
                            >
                                <i class="bi bi-trash"></i>
                                Delete
                            </a>

                        </td>

                    </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
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
    <strong>Online Resibo</strong>
    &copy; 2026
</footer>

</div>

<!-- =====================================================
VIEW PAYMENT MODAL
===================================================== -->

<div class="modal fade" id="viewPaymentModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header bg-info text-white">

                <h5 class="modal-title">
                    <i class="bi bi-receipt"></i>
                    Payment Details
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>

            <div class="modal-body">

                <div class="text-center mb-4">

                    <h4 id="view_reference"></h4>

                    <span
                        id="view_status"
                        class="badge"
                    ></span>

                </div>

                <div class="row">

                    <div class="col-6 mb-3">
                        <strong>Student ID</strong>
                        <div id="view_student_number">
                        </div>
                    </div>

                    <div class="col-6 mb-3">
                        <strong>Student Name</strong>
                        <div id="view_student">
                        </div>
                    </div>

                    <div class="col-6 mb-3">
                        <strong>Payment Type</strong>
                        <div id="view_type">
                        </div>
                    </div>

                    <div class="col-6 mb-3">
                        <strong>Amount</strong>
                        <div id="view_amount">
                        </div>
                    </div>

                    <div class="col-6 mb-3">
                        <strong>Payment Method</strong>
                        <div id="view_method">
                        </div>
                    </div>

                    <div class="col-6 mb-3">
                        <strong>Payment Date</strong>
                        <div id="view_date">
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <strong>Created At</strong>
                        <div id="view_created">
                        </div>
                    </div>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Close
                </button>

            </div>

        </div>

    </div>

</div>

<!-- =====================================================
EDIT PAYMENT MODAL
===================================================== -->

<div class="modal fade" id="editPaymentModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    <i class="bi bi-pencil"></i>
                    Edit Payment
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>

            <form action="update.php" method="POST">

                <div class="modal-body">

                    <input
                        type="hidden"
                        name="id"
                        id="edit_payment_id"
                    >

                    <div class="row">

                        <!-- REFERENCE -->
                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Payment Reference
                            </label>

                            <input
                                type="text"
                                id="edit_payment_reference"
                                class="form-control"
                                readonly
                            >

                        </div>

                        <!-- STUDENT -->
                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Student
                            </label>

                            <select
                                name="student_id"
                                id="edit_payment_student"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Student
                                </option>

                                <?php
                                $studentsResult2 = $conn->query($studentsSql);

                                if ($studentsResult2):
                                    while ($student = $studentsResult2->fetch_assoc()):
                                ?>

                                <option value="<?= (int)$student["id"] ?>">
                                    <?= htmlspecialchars($student["student_id"]) ?>
                                    -
                                    <?= htmlspecialchars($student["fullname"]) ?>
                                </option>

                                <?php
                                    endwhile;
                                endif;
                                ?>

                            </select>

                        </div>

                        <!-- PAYMENT TYPE -->
                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Payment Type
                            </label>

                            <select
                                name="payment_type"
                                id="edit_payment_type"
                                class="form-select"
                                required
                            >

                                <option value="Tuition Fee">
                                    Tuition Fee
                                </option>

                                <option value="Miscellaneous">
                                    Miscellaneous
                                </option>

                                <option value="Laboratory Fee">
                                    Laboratory Fee
                                </option>

                                <option value="School Uniform">
                                    School Uniform
                                </option>

                                <option value="Books">
                                    Books
                                </option>

                                <option value="Other">
                                    Other
                                </option>

                            </select>

                        </div>

                        <!-- AMOUNT -->
                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Amount
                            </label>

                            <input
                                type="number"
                                name="amount"
                                id="edit_payment_amount"
                                class="form-control"
                                min="0.01"
                                step="0.01"
                                required
                            >

                        </div>

                        <!-- METHOD -->
                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Payment Method
                            </label>

                            <select
                                name="payment_method"
                                id="edit_payment_method"
                                class="form-select"
                                required
                            >

                                <option value="Cash">
                                    Cash
                                </option>

                                <option value="GCash">
                                    GCash
                                </option>

                                <option value="Bank Transfer">
                                    Bank Transfer
                                </option>

                                <option value="Online">
                                    Online
                                </option>

                            </select>

                        </div>

                        <!-- DATE -->
                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Payment Date
                            </label>

                            <input
                                type="date"
                                name="payment_date"
                                id="edit_payment_date"
                                class="form-control"
                                required
                            >

                        </div>

                        <!-- STATUS -->
                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Status
                            </label>

                            <select
                                name="status"
                                id="edit_payment_status"
                                class="form-select"
                                required
                            >

                                <option value="Paid">
                                    Paid
                                </option>

                                <option value="Pending">
                                    Pending
                                </option>

                                <option value="Cancelled">
                                    Cancelled
                                </option>

                            </select>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-save"></i>
                        Update Payment
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../adminlte/dist/js/adminlte.min.js"></script>
<script src="../assets/js/sweetalert2.all.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {

    /* SEARCH */
    const search = document.getElementById("paymentSearch");

    if (search) {
        search.addEventListener("keyup", function() {

            const value = this.value.toLowerCase().trim();

            document.querySelectorAll("#paymentTable tbody tr").forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none";
            });

        });
    }

    /* VIEW PAYMENT */
    document.querySelectorAll(".view-payment-btn").forEach(function(button) {

        button.addEventListener("click", function() {

            document.getElementById("view_reference").textContent =
                this.dataset.reference;

            document.getElementById("view_student_number").textContent =
                this.dataset.studentNumber || "N/A";

            document.getElementById("view_student").textContent =
                this.dataset.student;

            document.getElementById("view_type").textContent =
                this.dataset.type;

            document.getElementById("view_amount").textContent =
                "₱" + parseFloat(this.dataset.amount).toLocaleString("en-PH", {
                    minimumFractionDigits: 2
                });

            document.getElementById("view_method").textContent =
                this.dataset.method;

            document.getElementById("view_date").textContent =
                this.dataset.date;

            document.getElementById("view_created").textContent =
                this.dataset.created;

            const status = document.getElementById("view_status");

            status.textContent = this.dataset.status;

            status.className = "badge";

            if (this.dataset.status === "Paid") {
                status.classList.add("text-bg-success");
            } else if (this.dataset.status === "Pending") {
                status.classList.add("text-bg-warning");
            } else {
                status.classList.add("text-bg-danger");
            }

            const modalElement = document.getElementById("viewPaymentModal");

            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

            modal.show();

        });

    });

    /* EDIT PAYMENT */
    document.querySelectorAll(".edit-payment-btn").forEach(function(button) {

        button.addEventListener("click", function() {

            document.getElementById("edit_payment_id").value =
                this.dataset.id;

            document.getElementById("edit_payment_reference").value =
                this.dataset.reference;

            document.getElementById("edit_payment_student").value =
                this.dataset.student;

            document.getElementById("edit_payment_type").value =
                this.dataset.type;

            document.getElementById("edit_payment_amount").value =
                this.dataset.amount;

            document.getElementById("edit_payment_method").value =
                this.dataset.method;

            document.getElementById("edit_payment_date").value =
                this.dataset.date;

            document.getElementById("edit_payment_status").value =
                this.dataset.status;

            const modalElement = document.getElementById("editPaymentModal");

            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

            modal.show();

        });

    });

    /* DELETE PAYMENT */
    document.querySelectorAll(".delete-payment-btn").forEach(function(button) {

        button.addEventListener("click", function(event) {

            event.preventDefault();

            const url = this.href;
            const reference = this.dataset.reference;

            Swal.fire({
                title: "Delete Payment?",
                html:
                    "Are you sure you want to delete <strong>" +
                    reference +
                    "</strong>?" +
                    "<br><br><small>This action cannot be undone.</small>",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, Delete",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                reverseButtons: true
            }).then(function(result) {

                if (result.isConfirmed) {
                    window.location.href = url;
                }

            });

        });

    });

    /* SUCCESS / ERROR */
    const params = new URLSearchParams(window.location.search);

    const success = params.get("success");
    const error = params.get("error");

    if (success === "added") {

        Swal.fire({
            icon: "success",
            title: "Payment Added Successfully!",
            text: "The payment has been recorded.",
            timer: 2500,
            timerProgressBar: true
        });

    }

    if (success === "updated") {

        Swal.fire({
            icon: "success",
            title: "Payment Updated Successfully!",
            text: "The payment information has been updated.",
            timer: 2500,
            timerProgressBar: true
        });

    }

    if (success === "deleted") {

        Swal.fire({
            icon: "success",
            title: "Payment Deleted Successfully!",
            text: "The payment has been removed.",
            timer: 2500,
            timerProgressBar: true
        });

    }

    if (error) {

        let message = "Something went wrong.";

        if (error === "missing") {
            message = "Please complete all required fields.";
        } else if (error === "invalid_amount") {
            message = "Please enter a valid payment amount.";
        } else if (error === "student_not_found") {
            message = "The selected student was not found.";
        } else if (error === "database") {
            message = "Unable to prepare the payment.";
        } else if (error === "save_failed") {
            message = "Unable to save the payment.";
        } else if (error === "update_failed") {
            message = "Unable to update the payment.";
        } else if (error === "payment_not_found") {
            message = "The payment was not found.";
        } else if (error === "delete_failed") {
            message = "Unable to delete the payment.";
        }

        Swal.fire({
            icon: "error",
            title: "Error",
            text: message,
            confirmButtonColor: "#dc3545"
        });

    }

});
</script>

</body>
</html>