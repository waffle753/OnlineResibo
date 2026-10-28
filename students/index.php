<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/database.php";

$sql = "SELECT students.id, students.student_id, students.first_name,
                       students.middle_name, students.last_name,
                       CONCAT_WS(' ', students.first_name, students.middle_name, students.last_name) AS fullname,
                       c.course_id, c.name AS course, students.program_id,
                       d.code AS department_code, d.name AS department, students.department_id,
                       students.email, students.year_level, students.status
                    FROM students
                    JOIN course_programs c ON c.id = students.program_id
                    LEFT JOIN departments d ON d.id = students.department_id
                    ORDER BY students.id DESC";

$result = $conn->query($sql);
$programsResult = $conn->query("SELECT id, course_id, name FROM course_programs ORDER BY name ASC");
$programs = $programsResult ? $programsResult->fetch_all(MYSQLI_ASSOC) : [];

$departmentsExistsResult = $conn->query("SHOW TABLES LIKE 'departments'");
$departmentsTableExists = $departmentsExistsResult && $departmentsExistsResult->num_rows > 0;
$departmentsResult = $departmentsTableExists
    ? $conn->query("SELECT id, code, name FROM departments ORDER BY name ASC")
    : false;
$departments = $departmentsResult ? $departmentsResult->fetch_all(MYSQLI_ASSOC) : [];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Students - Online Resibo</title>

    <link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <script defer src="../assets/js/sidebar-icons.js"></script>
    <link rel="stylesheet" href="../assets/css/table-gridlines.css">
    <link rel="stylesheet" href="../assets/css/sweetalert2.css">

</head>

<body class="layout-fixed sidebar-expand-lg sidebar-mini bg-body-tertiary">

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
                        🏫 <strong>ONLINE RESIBO</strong>
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

                        <?php if (($_SESSION["role"] ?? "") === "Admin"): ?>
                            <li class="nav-item">
                                <a href="../programs/index.php" class="nav-link">
                                    <p>📚 &nbsp; Courses / Programs</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (($_SESSION["role"] ?? "") === "Admin"): ?>
                            <li class="nav-item">
                                <a href="../departments/index.php" class="nav-link">
                                    <p>🏢 &nbsp; Departments</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- PAYMENTS -->
                        <li class="nav-item">
                            <a href="../payments/index.php" class="nav-link">
                                <p>💳 &nbsp; Payments</p>
                            </a>
                        </li>

                        <?php if (($_SESSION["role"] ?? "") === "Admin"): ?>
                            <li class="nav-item">
                                <a href="../payment-types/index.php" class="nav-link">
                                    <p>🏷️ &nbsp; Payment Types</p>
                                </a>
                            </li>
                        <?php endif; ?>

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
                        🏫 <strong>ONLINE RESIBO</strong>
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

                        <?php if (($_SESSION["role"] ?? "") === "Admin"): ?>
                            <li class="nav-item">
                                <a href="../programs/index.php" class="nav-link">
                                    <p>📚 &nbsp; Courses / Programs</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (($_SESSION["role"] ?? "") === "Admin"): ?>
                            <li class="nav-item">
                                <a href="../departments/index.php" class="nav-link">
                                    <p>🏢 &nbsp; Departments</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- PAYMENTS -->
                        <li class="nav-item">
                            <a href="../payments/index.php" class="nav-link">
                                <p>💳 &nbsp; Payments</p>
                            </a>
                        </li>

                        <?php if (($_SESSION["role"] ?? "") === "Admin"): ?>
                            <li class="nav-item">
                                <a href="../payment-types/index.php" class="nav-link">
                                    <p>🏷️ &nbsp; Payment Types</p>
                                </a>
                            </li>
                        <?php endif; ?>

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

                                    <div class="col-md-3 mb-3">

                                        <label class="form-label">
                                            Student ID
                                        </label>

                                        <input type="text" name="student_id" class="form-control"
                                            placeholder="e.g. 202500483" inputmode="numeric" maxlength="9" required>

                                    </div>


                                    <!-- NAME -->

                                    <div class="col-md-3 mb-3">

                                        <label class="form-label">
                                            First Name
                                        </label>

                                        <input type="text" name="first_name" class="form-control" placeholder="e.g. Juan" minlength="2" maxlength="50" required>

                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" name="middle_name" class="form-control" placeholder="e.g. Santos" maxlength="50">
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="last_name" class="form-control" placeholder="e.g. Dela Cruz" minlength="2" maxlength="50" required>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Email Address <span class="text-muted">(optional)</span></label>
                                        <input type="text" name="email" class="form-control" inputmode="email" maxlength="254"
                                            placeholder="student@example.com">
                                    </div>


                                    <!-- COURSE -->

                                    <div class="col-md-3 mb-3">

                                        <label class="form-label">
                                            Course / Program
                                        </label>

                                        <select name="program_id" class="form-select" required>
                                            <option value="">Select Course / Program</option>
                                            <?php foreach ($programs as $program): ?>
                                                <option value="<?= (int) $program["id"] ?>">
                                                    <?= htmlspecialchars($program["course_id"] . " - " . $program["name"]) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                    </div>

                                    <!-- DEPARTMENT -->
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">
                                            Department
                                        </label>
                                        <select name="department_id" class="form-select" <?= $departmentsTableExists ? 'required' : '' ?>>
                                            <option value="">Select Department</option>
                                            <?php foreach ($departments as $department): ?>
                                                <option value="<?= (int) $department["id"] ?>">
                                                    <?= htmlspecialchars($department["code"] . " - " . $department["name"]) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (!$departmentsTableExists): ?>
                                            <small class="text-warning d-block mt-1">Department table is not configured yet. Run the migration first.</small>
                                        <?php endif; ?>
                                    </div>

                                    <!-- YEAR LEVEL -->

                                    <div class="col-md-3 mb-3">

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

                                    <div class="col-md-3 mb-3">

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

                                    <div class="col-md-3 mb-3 d-flex align-items-end">

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
                                            <th>Email</th>
                                            <th>Course</th>
                                            <th>Department</th>

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

                                                    <td><?= htmlspecialchars($student["email"] ?: "—") ?></td>

                                                    <td>
                                                        <?= htmlspecialchars($student["course_id"] . " - " . $student["course"]) ?>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars(($student["department_code"] ?? "") !== "" ? $student["department_code"] . " - " . ($student["department"] ?? "") : "—") ?>
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
                                                            data-first-name="<?= htmlspecialchars($student["first_name"], ENT_QUOTES, 'UTF-8') ?>"
                                                            data-middle-name="<?= htmlspecialchars($student["middle_name"] ?? "", ENT_QUOTES, 'UTF-8') ?>"
                                                            data-last-name="<?= htmlspecialchars($student["last_name"], ENT_QUOTES, 'UTF-8') ?>"
                                                            data-email="<?= htmlspecialchars($student["email"] ?? "", ENT_QUOTES, 'UTF-8') ?>"
                                                            data-program-id="<?= (int) $student["program_id"] ?>"
                                                            data-department-id="<?= (int) ($student["department_id"] ?? 0) ?>"
                                                            data-course-id="<?= htmlspecialchars($student["course_id"], ENT_QUOTES, 'UTF-8') ?>"
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

                                                <td colspan="8" class="text-center text-muted py-4">

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

                <form action="update.php" method="POST" data-disable-unchanged>

                    <div class="modal-body">

                        <!-- Hidden database ID -->

                        <input type="hidden" name="id" id="edit_id">


                        <div class="row">

                            <!-- STUDENT ID -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Student ID
                                </label>

                                <input type="text" name="student_id" id="edit_student_id" class="form-control" inputmode="numeric" maxlength="9" required>

                            </div>


                            <!-- NAME -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    First Name
                                </label>

                                <input type="text" name="first_name" id="edit_first_name" class="form-control" minlength="2" maxlength="50" required>

                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" id="edit_middle_name" class="form-control" maxlength="50">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" id="edit_last_name" class="form-control" minlength="2" maxlength="50" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address <span class="text-muted">(optional)</span></label>
                                <input type="text" name="email" id="edit_email" class="form-control" inputmode="email" maxlength="254">
                            </div>


                            <!-- COURSE -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Course / Program
                                </label>

                                <select name="program_id" id="edit_program_id" class="form-select" required>
                                    <option value="">Select Course / Program</option>
                                    <?php foreach ($programs as $program): ?>
                                        <option value="<?= (int) $program["id"] ?>">
                                            <?= htmlspecialchars($program["course_id"] . " - " . $program["name"]) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Department</label>
                                <select name="department_id" id="edit_department_id" class="form-select" <?= $departmentsTableExists ? 'required' : '' ?>>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $department): ?>
                                        <option value="<?= (int) $department["id"] ?>">
                                            <?= htmlspecialchars($department["code"] . " - " . $department["name"]) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (!$departmentsTableExists): ?>
                                    <small class="text-warning d-block mt-1">Department table is not configured yet. Run the migration first.</small>
                                <?php endif; ?>
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

                        <button type="submit" class="btn btn-primary" data-change-submit disabled>

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

                    const firstName =
                        this.getAttribute("data-first-name");

                    const middleName =
                        this.getAttribute("data-middle-name");

                    const lastName =
                        this.getAttribute("data-last-name");

                    const email =
                        this.getAttribute("data-email");

                    const programId =
                        this.getAttribute("data-program-id");

                    const departmentId =
                        this.getAttribute("data-department-id");

                    const yearLevel =
                        this.getAttribute("data-year-level");

                    const status =
                        this.getAttribute("data-status");


                    // Put data into edit form

                    document.getElementById("edit_id").value =
                        id;

                    document.getElementById("edit_student_id").value =
                        studentId;

                    document.getElementById("edit_first_name").value =
                        firstName;

                    document.getElementById("edit_middle_name").value =
                        middleName;

                    document.getElementById("edit_last_name").value =
                        lastName;

                    document.getElementById("edit_email").value =
                        email;

                    document.getElementById("edit_program_id").value =
                        programId;

                    document.getElementById("edit_department_id").value =
                        departmentId;

                    document.getElementById("edit_year_level").value =
                        yearLevel;

                    document.getElementById("edit_status").value =
                        status;


                    // Open modal

                    const modalElement =
                        document.getElementById("editStudentModal");


                    if (typeof bootstrap === "undefined") {

                        Swal.fire({
                            icon: "error",
                            title: "Unable to open form",
                            text: "The form component could not be loaded."
                        });

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
            const error =
                urlParams.get("error");


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

            const studentErrors = {
                missing: "Complete all required student fields.",
                invalid_status: "Choose a valid student status.",
                invalid_program: "Choose a valid course/program.",
                invalid_department: "Choose a valid department.",
                department_missing: "The department table is not configured yet. Run the migration first.",
                student_id_required: "Student ID is required.",
                student_id_format: "Student ID must be exactly 9 digits.",
                duplicate_student_id: "This Student ID already exists.",
                first_name_required: "First name is required.",
                first_name_length: "First name must be between 2 and 50 characters.",
                first_name_characters: "First name contains invalid characters.",
                middle_name_length: "Middle name must be between 2 and 50 characters.",
                middle_name_characters: "Middle name contains invalid characters.",
                last_name_required: "Last name is required.",
                last_name_length: "Last name must be between 2 and 50 characters.",
                last_name_characters: "Last name contains invalid characters.",
                invalid_email: "Email address must be valid.",
                email_too_long: "Email address must not exceed 254 characters.",
                database: "Unable to prepare the student record.",
                save_failed: "Unable to save the student.",
                update_failed: "Unable to update the student.",
                delete_failed: "Unable to delete the student.",
                invalid_id: "The selected student is invalid."
            };

            if (error && studentErrors[error]) {
                Swal.fire({
                    icon: "error",
                    title: "Unable to complete",
                    text: studentErrors[error],
                    confirmButtonColor: "#dc3545"
                });
            }

            if (success || error) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }

        });
    </script>
    <script src="../assets/js/student-validation.js"></script>
    <script src="../assets/js/disable-unchanged-submit.js"></script>

</body>

</html>