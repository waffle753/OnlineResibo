<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/database.php";

$sql = "SELECT id, student_id, fullname, course, year_level, status
                    FROM students
                    ORDER BY id DESC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Students - Online Resibo</title>

    <link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../assets/css/sweetalert2.css">

</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

    <div class="app-wrapper">

        <!-- NAVBAR -->
        <nav class="app-header navbar navbar-expand bg-body">

            <div class="container-fluid">

                <ul class="navbar-nav">

                    <li class="nav-item">

                        <a class="nav-link" data-lte-toggle="sidebar" href="#">

                            ☰

                        </a>

                    </li>

                </ul>

                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">

                        <span class="nav-link">

                            <?= htmlspecialchars($_SESSION["fullname"]) ?>

                            <span class="badge text-bg-primary">

                                <?= htmlspecialchars($_SESSION["role"]) ?>

                            </span>

                        </span>

                    </li>

                    <li class="nav-item">

                        <a href="../auth/logout.php" class="nav-link text-danger">

                            Logout

                        </a>

                    </li>

                </ul>

            </div>

        </nav>


        <!-- SIDEBAR -->
        <aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">

            <!-- BRAND -->
            <div class="sidebar-brand">
                <a href="../dashboard.php" class="brand-link">
                    <span class="brand-text fw-light">
                        🏫 ONLINE RESIBO
                    </span>
                </a>
            </div>

            <!-- SIDEBAR MENU -->
            <div class="sidebar-wrapper">
                <nav>
                    <ul class="nav sidebar-menu flex-column">

                        <!-- DASHBOARD -->
                        <li class="nav-item">
                            <a href="../dashboard.php" class="nav-link">
                                <p>🏠 &nbsp; Dashboard</p>
                            </a>
                        </li>

                        <!-- STUDENTS -->
                        <li class="nav-item">
                            <a href="index.php" class="nav-link active">
                                <p>👨‍🎓 &nbsp; Students</p>
                            </a>
                        </li>

                        <!-- PAYMENTS -->
                        <li class="nav-item">
                            <a href="../payments/index.php" class="nav-link">
                                <p>💳 &nbsp; Payments</p>
                            </a>
                        </li>

                        <!-- RECEIPTS -->
                        <li class="nav-item">
                            <a href="../receipts/index.php" class="nav-link">
                                <p>🧾 &nbsp; Receipts</p>
                            </a>
                        </li>

                        <!-- REPORTS -->
                        <li class="nav-item">
                            <a href="../reports/index.php" class="nav-link">
                                <p>📊 &nbsp; Reports</p>
                            </a>
                        </li>

                        <!-- USERS -->
                        <?php if ($_SESSION["role"] === "Admin"): ?>
                            <li class="nav-item">
                                <a href="../users/index.php" class="nav-link">
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
                            <a href="../auth/logout.php" class="nav-link text-danger">
                                <p>🚪 &nbsp; Logout</p>
                            </a>
                        </li>

                    </ul>
                </nav>
            </div>

        </aside><!-- SIDEBAR -->
        <aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">

            <!-- BRAND -->
            <div class="sidebar-brand">
                <a href="../dashboard.php" class="brand-link">
                    <span class="brand-text fw-light">
                        🏫 ONLINE RESIBO
                    </span>
                </a>
            </div>

            <!-- SIDEBAR MENU -->
            <div class="sidebar-wrapper">
                <nav>
                    <ul class="nav sidebar-menu flex-column">

                        <!-- DASHBOARD -->
                        <li class="nav-item">
                            <a href="../dashboard.php" class="nav-link">
                                <p>🏠 &nbsp; Dashboard</p>
                            </a>
                        </li>

                        <!-- STUDENTS -->
                        <li class="nav-item">
                            <a href="index.php" class="nav-link active">
                                <p>👨‍🎓 &nbsp; Students</p>
                            </a>
                        </li>

                        <!-- PAYMENTS -->
                        <li class="nav-item">
                            <a href="../payments/index.php" class="nav-link">
                                <p>💳 &nbsp; Payments</p>
                            </a>
                        </li>

                        <!-- RECEIPTS -->
                        <li class="nav-item">
                            <a href="../receipts/index.php" class="nav-link">
                                <p>🧾 &nbsp; Receipts</p>
                            </a>
                        </li>

                        <!-- REPORTS -->
                        <li class="nav-item">
                            <a href="../reports/index.php" class="nav-link">
                                <p>📊 &nbsp; Reports</p>
                            </a>
                        </li>

                        <!-- USERS -->
                        <?php if ($_SESSION["role"] === "Admin"): ?>
                            <li class="nav-item">
                                <a href="../users/index.php" class="nav-link">
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
                            <a href="../auth/logout.php" class="nav-link text-danger">
                                <p>🚪 &nbsp; Logout</p>
                            </a>
                        </li>

                    </ul>
                </nav>
            </div>

        </aside>

        <!-- MAIN CONTENT -->
        <main class="app-main">

            <!-- HEADER -->
            <div class="app-content-header">

                <div class="container-fluid">

                    <div class="row">

                        <div class="col-sm-6">

                            <h3 class="mb-0">
                                Student Management
                            </h3>

                        </div>

                        <div class="col-sm-6">

                            <ol class="breadcrumb float-sm-end">

                                <li class="breadcrumb-item">
                                    Home
                                </li>

                                <li class="breadcrumb-item active">
                                    Students
                                </li>

                            </ol>

                        </div>

                    </div>

                </div>

            </div>


            <!-- CONTENT -->
            <div class="app-content">

                <div class="container-fluid">


                    <!-- =========================
                                ADD STUDENT
                            ========================== -->

                    <div class="card card-primary mb-4">

                        <div class="card-header">

                            <h3 class="card-title">

                                <strong>
                                    Add Student
                                </strong>

                            </h3>

                        </div>


                        <form action="save.php" method="POST">

                            <div class="card-body">

                                <div class="row">


                                    <!-- STUDENT ID -->

                                    <div class="col-md-4 mb-3">

                                        <label class="form-label">
                                            Student ID
                                        </label>

                                        <input type="text" name="student_id" class="form-control"
                                            placeholder="e.g. 2026-0001" required>

                                    </div>


                                    <!-- FULL NAME -->

                                    <div class="col-md-4 mb-3">

                                        <label class="form-label">
                                            Full Name
                                        </label>

                                        <input type="text" name="fullname" class="form-control"
                                            placeholder="Last Name, First Name" required>

                                    </div>


                                    <!-- COURSE -->

                                    <div class="col-md-4 mb-3">

                                        <label class="form-label">
                                            Course / Program
                                        </label>

                                        <input type="text" name="course" class="form-control" placeholder="e.g. BSIT"
                                            required>

                                    </div>


                                    <!-- YEAR LEVEL -->

                                    <div class="col-md-4 mb-3">

                                        <label class="form-label">
                                            Year Level
                                        </label>

                                        <select name="year_level" class="form-select" required>

                                            <option value="">
                                                Select Year Level
                                            </option>

                                            <option value="1st Year">
                                                1st Year
                                            </option>

                                            <option value="2nd Year">
                                                2nd Year
                                            </option>

                                            <option value="3rd Year">
                                                3rd Year
                                            </option>

                                            <option value="4th Year">
                                                4th Year
                                            </option>

                                            <option value="5th Year">
                                                5th Year
                                            </option>

                                        </select>

                                    </div>


                                    <!-- STATUS -->

                                    <div class="col-md-4 mb-3">

                                        <label class="form-label">
                                            Status
                                        </label>

                                        <select name="status" class="form-select">

                                            <option value="Active">
                                                Active
                                            </option>

                                            <option value="Inactive">
                                                Inactive
                                            </option>

                                        </select>

                                    </div>


                                    <!-- BUTTON -->

                                    <div class="col-md-4 mb-3 d-flex align-items-end">

                                        <button type="submit" class="btn btn-primary w-100">

                                            + Save Student

                                        </button>

                                    </div>

                                </div>

                            </div>

                        </form>

                    </div>


                    <!-- =========================
                                STUDENT LIST
                            ========================== -->

                    <div class="card">

                        <div class="card-header">

                            <h3 class="card-title">
                                Student List
                            </h3>

                        </div>


                        <div class="card-body">


                            <!-- SEARCH -->

                            <div class="row mb-3">

                                <div class="col-md-4">

                                    <input type="text" id="studentSearch" class="form-control"
                                        placeholder="Search student...">

                                </div>

                            </div>


                            <!-- TABLE -->

                            <div class="table-responsive">

                                <table class="table table-bordered table-hover" id="studentTable">

                                    <thead>

                                        <tr>

                                            <th>#</th>

                                            <th>Student ID</th>

                                            <th>Name</th>

                                            <th>Course</th>

                                            <th>Year Level</th>

                                            <th>Status</th>

                                            <th width="150">
                                                Action
                                            </th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        <?php if ($result->num_rows > 0): ?>

                                            <?php $count = 1; ?>

                                            <?php while ($student = $result->fetch_assoc()): ?>

                                                <tr>

                                                    <td>
                                                        <?= $count++ ?>
                                                    </td>

                                                    <td>
                                                        <?= htmlspecialchars($student["student_id"]) ?>
                                                    </td>

                                                    <td>
                                                        <?= htmlspecialchars($student["fullname"]) ?>
                                                    </td>

                                                    <td>
                                                        <?= htmlspecialchars($student["course"]) ?>
                                                    </td>

                                                    <td>
                                                        <?= htmlspecialchars($student["year_level"]) ?>
                                                    </td>

                                                    <td>

                                                        <?php if ($student["status"] === "Active"): ?>

                                                            <span class="badge text-bg-success">
                                                                Active
                                                            </span>

                                                        <?php else: ?>

                                                            <span class="badge text-bg-secondary">
                                                                Inactive
                                                            </span>

                                                        <?php endif; ?>

                                                    </td>

                                                    <td>

                                                        <button type="button" class="btn btn-warning btn-sm edit-btn"
                                                            data-id="<?= (int) $student["id"] ?>"
                                                            data-student-id="<?= htmlspecialchars($student["student_id"], ENT_QUOTES, 'UTF-8') ?>"
                                                            data-fullname="<?= htmlspecialchars($student["fullname"], ENT_QUOTES, 'UTF-8') ?>"
                                                            data-course="<?= htmlspecialchars($student["course"], ENT_QUOTES, 'UTF-8') ?>"
                                                            data-year-level="<?= htmlspecialchars($student["year_level"], ENT_QUOTES, 'UTF-8') ?>"
                                                            data-status="<?= htmlspecialchars($student["status"], ENT_QUOTES, 'UTF-8') ?>">
                                                            Edit
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm delete-btn"
                                                            data-id="<?= $student["id"] ?>"
                                                            data-name="<?= htmlspecialchars($student["fullname"], ENT_QUOTES) ?>">

                                                            Delete

                                                        </button>

                                                    </td>

                                                </tr>

                                            <?php endwhile; ?>

                                        <?php else: ?>

                                            <tr>

                                                <td colspan="7" class="text-center text-muted py-4">

                                                    No students registered yet.

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

        </footer>

    </div>

    <!-- =========================
                EDIT STUDENT MODAL
            ========================== -->

    <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel"
        aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered modal-lg">

            <div class="modal-content">

                <!-- HEADER -->

                <div class="modal-header">

                    <h5 class="modal-title" id="editStudentModalLabel">

                        Edit Student

                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>

                </div>


                <!-- FORM -->

                <form action="update.php" method="POST">

                    <div class="modal-body">

                        <!-- Hidden database ID -->

                        <input type="hidden" name="id" id="edit_id">


                        <div class="row">

                            <!-- STUDENT ID -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Student ID
                                </label>

                                <input type="text" name="student_id" id="edit_student_id" class="form-control" required>

                            </div>


                            <!-- FULL NAME -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Full Name
                                </label>

                                <input type="text" name="fullname" id="edit_fullname" class="form-control" required>

                            </div>


                            <!-- COURSE -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Course / Program
                                </label>

                                <input type="text" name="course" id="edit_course" class="form-control" required>

                            </div>


                            <!-- YEAR LEVEL -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Year Level
                                </label>

                                <select name="year_level" id="edit_year_level" class="form-select" required>

                                    <option value="1st Year">
                                        1st Year
                                    </option>

                                    <option value="2nd Year">
                                        2nd Year
                                    </option>

                                    <option value="3rd Year">
                                        3rd Year
                                    </option>

                                    <option value="4th Year">
                                        4th Year
                                    </option>

                                    <option value="5th Year">
                                        5th Year
                                    </option>

                                </select>

                            </div>


                            <!-- STATUS -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Status
                                </label>

                                <select name="status" id="edit_status" class="form-select" required>

                                    <option value="Active">
                                        Active
                                    </option>

                                    <option value="Inactive">
                                        Inactive
                                    </option>

                                </select>

                            </div>

                        </div>

                    </div>


                    <!-- FOOTER -->

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                            Cancel

                        </button>

                        <button type="submit" class="btn btn-primary">

                            Update Student

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>


    <!-- JAVASCRIPT LIBRARIES -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="../adminlte/dist/js/adminlte.min.js"></script>
    <script src="../assets/js/sweetalert2.all.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            // =====================================================
            // SEARCH STUDENT
            // =====================================================

            const search = document.getElementById("studentSearch");

            if (search) {

                search.addEventListener("keyup", function () {

                    const searchValue = this.value.toLowerCase().trim();

                    const rows = document.querySelectorAll(
                        "#studentTable tbody tr"
                    );

                    rows.forEach(function (row) {

                        const rowText = row.textContent.toLowerCase();

                        row.style.display =
                            rowText.includes(searchValue) ? "" : "none";

                    });

                });

            }


            // =====================================================
            // EDIT STUDENT
            // =====================================================

            const editButtons = document.querySelectorAll(".edit-btn");

            editButtons.forEach(function (button) {

                button.addEventListener("click", function () {

                    const id =
                        this.getAttribute("data-id");

                    const studentId =
                        this.getAttribute("data-student-id");

                    const fullname =
                        this.getAttribute("data-fullname");

                    const course =
                        this.getAttribute("data-course");

                    const yearLevel =
                        this.getAttribute("data-year-level");

                    const status =
                        this.getAttribute("data-status");


                    // Put data into edit form

                    document.getElementById("edit_id").value =
                        id;

                    document.getElementById("edit_student_id").value =
                        studentId;

                    document.getElementById("edit_fullname").value =
                        fullname;

                    document.getElementById("edit_course").value =
                        course;

                    document.getElementById("edit_year_level").value =
                        yearLevel;

                    document.getElementById("edit_status").value =
                        status;


                    // Open modal

                    const modalElement =
                        document.getElementById("editStudentModal");


                    if (typeof bootstrap === "undefined") {

                        alert("Bootstrap JavaScript is not loaded.");

                        return;

                    }


                    const modal =
                        bootstrap.Modal.getOrCreateInstance(
                            modalElement
                        );


                    modal.show();

                });

            });


            // =====================================================
            // DELETE STUDENT
            // =====================================================

            const deleteButtons =
                document.querySelectorAll(".delete-btn");


            deleteButtons.forEach(function (button) {

                button.addEventListener("click", function () {

                    const studentId =
                        this.getAttribute("data-id");

                    const studentName =
                        this.getAttribute("data-name");


                    // Check SweetAlert

                    if (typeof Swal === "undefined") {

                        const confirmed =
                            confirm(
                                "Are you sure you want to delete " +
                                studentName +
                                "?"
                            );


                        if (confirmed) {

                            window.location.href =
                                "delete.php?id=" +
                                encodeURIComponent(studentId);

                        }

                        return;

                    }


                    // SweetAlert confirmation

                    Swal.fire({

                        title: "Delete Student?",

                        html:
                            "Are you sure you want to delete " +
                            "<strong>" +
                            studentName +
                            "</strong>?" +
                            "<br><br>" +
                            "<small>This action cannot be undone.</small>",

                        icon: "warning",

                        showCancelButton: true,

                        confirmButtonText: "Yes, Delete",

                        cancelButtonText: "Cancel",

                        confirmButtonColor: "#dc3545",

                        cancelButtonColor: "#6c757d",

                        reverseButtons: true

                    }).then(function (result) {

                        if (result.isConfirmed) {

                            window.location.href =
                                "delete.php?id=" +
                                encodeURIComponent(studentId);

                        }

                    });

                });

            });


            // =====================================================
            // SUCCESS ALERTS
            // =====================================================

            const urlParams =
                new URLSearchParams(window.location.search);

            const success =
                urlParams.get("success");


            if (typeof Swal !== "undefined") {

                // ---------------------------------------------
                // STUDENT ADDED
                // ---------------------------------------------

                if (success === "added") {

                    Swal.fire({

                        icon: "success",

                        title: "Student Added Successfully!",

                        text: "The student has been registered.",

                        confirmButtonText: "OK",

                        confirmButtonColor: "#0d6efd",

                        timer: 2500,

                        timerProgressBar: true

                    });

                }


                // ---------------------------------------------
                // STUDENT UPDATED
                // ---------------------------------------------

                if (success === "updated") {

                    Swal.fire({

                        icon: "success",

                        title: "Student Updated Successfully!",

                        text: "The student information has been updated.",

                        confirmButtonText: "OK",

                        confirmButtonColor: "#0d6efd",

                        timer: 2500,

                        timerProgressBar: true

                    });

                }


                // ---------------------------------------------
                // STUDENT DELETED
                // ---------------------------------------------

                if (success === "deleted") {

                    Swal.fire({

                        icon: "success",

                        title: "Student Deleted Successfully!",

                        text: "The student has been removed from the system.",

                        confirmButtonText: "OK",

                        confirmButtonColor: "#0d6efd",

                        timer: 2500,

                        timerProgressBar: true

                    });

                }


                // ---------------------------------------------
                // ERROR
                // ---------------------------------------------

                if (success === "error") {

                    Swal.fire({

                        icon: "error",

                        title: "Something Went Wrong",

                        text: "The operation could not be completed.",

                        confirmButtonText: "OK",

                        confirmButtonColor: "#dc3545"

                    });

                }

            }

        });
    </script>

</body>

</html>