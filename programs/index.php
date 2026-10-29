<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/database.php";

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
    $action = $_POST["action"] ?? "";
    $courseIdInput = $_POST["course_id"] ?? "";
    $nameInput = $_POST["name"] ?? "";
    if (!is_string($action) || !is_string($courseIdInput) || !is_string($nameInput)) {
        header("Location: index.php?message=invalid_input");
        exit();
    }
    $courseId = trim($courseIdInput);
    $name = trim($nameInput);

    if ($action === "add" || $action === "update") {
        if ($courseId === "" || mb_strlen($courseId) > 100) {
            header("Location: index.php?message=invalid_course_id");
            exit();
        }

        if ($name === "" || mb_strlen($name) > 100) {
            header("Location: index.php?message=invalid_name");
            exit();
        }

        if ($action === "add") {
            $stmt = $conn->prepare("INSERT INTO course_programs (course_id, name) VALUES (?, ?)");
        } else {
            $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);
            if ($id === false || $id === null || $id <= 0) {
                header("Location: index.php?message=invalid_id");
                exit();
            }

            $stmt = $conn->prepare("UPDATE course_programs SET course_id = ?, name = ? WHERE id = ?");
        }

        if (!$stmt) {
            die("Unable to prepare course/program update: " . $conn->error);
        }

        if ($action === "add") {
            $stmt->bind_param("ss", $courseId, $name);
        } else {
            $stmt->bind_param("ssi", $courseId, $name, $id);
        }

        if (!$stmt->execute()) {
            $message = $stmt->errno === 1062 ? "duplicate_course_program" : "save_failed";
            $stmt->close();
            header("Location: index.php?message=" . $message);
            exit();
        }

        $stmt->close();
        $conn->close();
        header("Location: index.php?message=" . ($action === "add" ? "added" : "updated"));
        exit();
    }

    if ($action === "delete") {
        $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id === null || $id <= 0) {
            header("Location: index.php?message=invalid_id");
            exit();
        }

        $usageStmt = $conn->prepare("SELECT COUNT(*) AS total FROM students WHERE program_id = ?");
        if (!$usageStmt) {
            die("Unable to check course/program usage: " . $conn->error);
        }
        $usageStmt->bind_param("i", $id);
        if (!$usageStmt->execute()) {
            die("Unable to check course/program usage: " . $usageStmt->error);
        }
        $usageCount = (int) $usageStmt->get_result()->fetch_assoc()["total"];
        $usageStmt->close();

        if ($usageCount > 0) {
            header("Location: index.php?message=in_use");
            exit();
        }

        $deleteStmt = $conn->prepare("DELETE FROM course_programs WHERE id = ?");
        if (!$deleteStmt) {
            die("Unable to prepare course/program deletion: " . $conn->error);
        }
        $deleteStmt->bind_param("i", $id);
        if (!$deleteStmt->execute()) {
            if ($deleteStmt->errno === 1451) {
                $deleteStmt->close();
                header("Location: index.php?message=in_use");
                exit();
            }
            die("Unable to delete course/program: " . $deleteStmt->error);
        }
        $deleted = $deleteStmt->affected_rows > 0;
        $deleteStmt->close();
        $conn->close();
        header("Location: index.php?message=" . ($deleted ? "deleted" : "not_found"));
        exit();
    }

    header("Location: index.php?message=invalid_action");
    exit();
}

$programsResult = $conn->query(
    "SELECT cp.id, cp.course_id, cp.name, COUNT(s.id) AS student_count
     FROM course_programs cp
     LEFT JOIN students s ON s.program_id = cp.id
     GROUP BY cp.id, cp.course_id, cp.name
     ORDER BY cp.name ASC"
);

$messages = [
    "added" => ["success", "Course/program added."],
    "updated" => ["success", "Course/program updated."],
    "deleted" => ["success", "Course/program deleted."],
    "duplicate_name" => ["danger", "That course/program already exists."],
    "duplicate_course_program" => ["danger", "That Course ID or course/program name is already in use."],
    "invalid_course_id" => ["danger", "Enter a Course ID up to 100 characters."],
    "invalid_name" => ["danger", "Enter a name up to 100 characters."],
    "invalid_id" => ["danger", "The selected course/program is invalid."],
    "invalid_action" => ["danger", "That action is not supported."],
    "invalid_input" => ["danger", "The submitted course/program data is invalid."],
    "save_failed" => ["danger", "Unable to save the course/program."],
    "in_use" => ["warning", "This course/program is assigned to students and cannot be deleted."],
    "not_found" => ["warning", "The course/program was not found."]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses &amp; Programs - Online Resibo</title>
    <link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <script defer src="../assets/js/sidebar-icons.js"></script>
    <link rel="stylesheet" href="../assets/css/table-gridlines.css">
    <link rel="stylesheet" href="../assets/css/sweetalert2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
                    <li class="nav-item"><a href="index.php" class="nav-link active"><p>📚 &nbsp; Courses / Programs</p></a></li>
                    <li class="nav-item"><a href="../departments/index.php" class="nav-link"><p>🏢 &nbsp; Departments</p></a></li>
                    <li class="nav-item"><a href="../payments/index.php" class="nav-link"><p>💳 &nbsp; Payments</p></a></li>
                    <li class="nav-item"><a href="../payment-types/index.php" class="nav-link"><p>🏷️ &nbsp; Payment Types</p></a></li>
                    <li class="nav-item"><a href="../receipts/index.php" class="nav-link"><p>🧾 &nbsp; Receipts</p></a></li>
                    <li class="nav-item"><a href="../reports/index.php" class="nav-link"><p>📊 &nbsp; Reports</p></a></li>
                    <?php if (($_SESSION["role"] ?? "") === "Admin"): ?>
                        <li class="nav-item"><a href="../users/index.php" class="nav-link"><p>👤 &nbsp; Users</p></a></li>
                    <?php endif; ?>
                    <li class="nav-header">SYSTEM</li>
                    <li class="nav-item"><a href="../user-manual.php" class="nav-link"><p>User Manual</p></a></li>
                    <li class="nav-item"><a href="../auth/logout.php" class="nav-link text-danger"><p>🚪 &nbsp; Logout</p></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6"><h3 class="mb-0">Course / Program Management</h3></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="../dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Courses / Programs</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-content">
            <div class="container-fluid">
                <div class="card mb-4">
                    <div class="card-header" style="background: #0d6efd; border-bottom: 1px solid #0d6efd;">
                        <h3 class="card-title text-white mb-0">Add Course / Program</h3>
                    </div>
                    <form method="POST" action="index.php">
                        <input type="hidden" name="action" value="add">
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-4 mb-3">
                                    <label for="program_course_id" class="form-label">Course ID</label>
                                    <input type="text" id="program_course_id" name="course_id" class="form-control"
                                        maxlength="100" placeholder="e.g. BSIT-001" required>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label for="program_name" class="form-label">Course / Program Name</label>
                                    <input type="text" id="program_name" name="name" class="form-control"
                                        maxlength="100" placeholder="e.g. BS Information Technology" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-plus-lg"></i> Add Program
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <div class="card-header"><h3 class="card-title">Available Courses / Programs</h3></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <colgroup>
                                    <col style="width: 30%;">
                                    <col style="width: 45%;">
                                    <col style="width: 25%;">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th class="ps-3">Course ID</th>
                                        <th class="ps-3">Name</th>
                                        <th style="min-width: 180px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($programsResult->num_rows > 0): ?>
                                        <?php while ($program = $programsResult->fetch_assoc()): ?>
                                            <tr>
                                                <td class="ps-3"><?= htmlspecialchars($program["course_id"]) ?></td>
                                                <td><?= htmlspecialchars($program["name"]) ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-warning btn-sm edit-program-btn"
                                                        data-id="<?= (int) $program["id"] ?>"
                                                        data-course-id="<?= htmlspecialchars($program["course_id"], ENT_QUOTES, "UTF-8") ?>"
                                                        data-name="<?= htmlspecialchars($program["name"], ENT_QUOTES, "UTF-8") ?>">
                                                        Edit
                                                    </button>
                                                    <form method="POST" action="index.php" class="d-inline program-delete-form"
                                                        data-program-name="<?= htmlspecialchars($program["course_id"] . " - " . $program["name"], ENT_QUOTES, "UTF-8") ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?= (int) $program["id"] ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                No courses or programs yet. Add one above to use it in student records.
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

    <div class="modal fade" id="editProgramModal" tabindex="-1" aria-labelledby="editProgramModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="index.php" data-disable-unchanged>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_program_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProgramModalLabel">Edit Course / Program</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_course_id" class="form-label">Course ID</label>
                            <input type="text" name="course_id" id="edit_course_id" class="form-control"
                                maxlength="100" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_program_name" class="form-label">Course / Program Name</label>
                            <input type="text" name="name" id="edit_program_name" class="form-control"
                                maxlength="100" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" data-change-submit disabled>Update Program</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="app-footer">
        <strong>Online Resibo</strong> &copy; 2026
        <span class="ms-2">Created by <strong>Kent David T. Abejo</strong></span>
        <div class="float-end d-none d-sm-inline">School Payment Management System</div>
    </footer>
</div>
<script src="../adminlte/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/sweetalert2.all.min.js"></script>
<script>
document.querySelectorAll(".edit-program-btn").forEach(function (button) {
    button.addEventListener("click", function () {
        document.getElementById("edit_program_id").value = button.dataset.id;
        document.getElementById("edit_course_id").value = button.dataset.courseId;
        document.getElementById("edit_program_name").value = button.dataset.name;

        bootstrap.Modal.getOrCreateInstance(
            document.getElementById("editProgramModal")
        ).show();
    });
});

document.querySelectorAll(".program-delete-form").forEach(function (form) {
    form.addEventListener("submit", function (event) {
        event.preventDefault();

        Swal.fire({
            title: "Delete course/program?",
            text: "Delete " + form.dataset.programName + "? This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#dc3545",
            reverseButtons: true
        }).then(function (result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});

const messages = <?= json_encode($messages, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const messageKey = new URLSearchParams(window.location.search).get("message");

if (messageKey && messages[messageKey]) {
    const [type, text] = messages[messageKey];
    Swal.fire({
        icon: type === "danger" ? "error" : type,
        title: type === "success" ? "Success" : type === "warning" ? "Notice" : "Unable to complete",
        text: text,
        confirmButtonColor: type === "danger" ? "#dc3545" : "#0d6efd"
    }).then(function () {
        window.history.replaceState({}, document.title, window.location.pathname);
    });
}
</script>
<script src="../assets/js/disable-unchanged-submit.js"></script>
<script src="../assets/js/table-pagination.js"></script>
</body>
</html>
