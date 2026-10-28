<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

if (($_SESSION["role"] ?? "") !== "Admin") {
    http_response_code(403);
    exit("Only administrators can manage departments.");
}

require_once "../config/database.php";

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
    $actionInput = $_POST["action"] ?? "";
    $codeInput = $_POST["code"] ?? "";
    $nameInput = $_POST["name"] ?? "";

    if (!is_string($actionInput) || !is_string($codeInput) || !is_string($nameInput)) {
        header("Location: index.php?message=invalid_input");
        exit();
    }

    $action = $actionInput;
    $code = trim($codeInput);
    $name = trim($nameInput);

    if ($action === "add" || $action === "update") {
        if ($code === "" || mb_strlen($code) > 50) {
            header("Location: index.php?message=invalid_code");
            exit();
        }

        if ($name === "" || mb_strlen($name) > 100) {
            header("Location: index.php?message=invalid_name");
            exit();
        }

        if ($action === "add") {
            $stmt = $conn->prepare("INSERT INTO departments (code, name) VALUES (?, ?)");
            if (!$stmt) {
                header("Location: index.php?message=database");
                exit();
            }
            $stmt->bind_param("ss", $code, $name);
        } else {
            $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);
            if ($id === false || $id === null || $id <= 0) {
                header("Location: index.php?message=invalid_id");
                exit();
            }

            $existingStmt = $conn->prepare("SELECT id FROM departments WHERE id = ?");
            if (!$existingStmt) {
                header("Location: index.php?message=database");
                exit();
            }
            $existingStmt->bind_param("i", $id);
            if (!$existingStmt->execute()) {
                $existingStmt->close();
                header("Location: index.php?message=database");
                exit();
            }
            $departmentExists = $existingStmt->get_result()->num_rows > 0;
            $existingStmt->close();
            if (!$departmentExists) {
                header("Location: index.php?message=not_found");
                exit();
            }

            $stmt = $conn->prepare("UPDATE departments SET code = ?, name = ? WHERE id = ?");
            if (!$stmt) {
                header("Location: index.php?message=database");
                exit();
            }
            $stmt->bind_param("ssi", $code, $name, $id);
        }

        if (!$stmt->execute()) {
            $message = $stmt->errno === 1062 ? "duplicate_code_or_name" : "save_failed";
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

        $deleteStmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
        if (!$deleteStmt) {
            header("Location: index.php?message=database");
            exit();
        }
        $deleteStmt->bind_param("i", $id);
        if (!$deleteStmt->execute()) {
            $message = $deleteStmt->errno === 1451 ? "in_use" : "delete_failed";
            $deleteStmt->close();
            header("Location: index.php?message=" . $message);
            exit();
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

$departmentsResult = $conn->query(
    "SELECT d.id, d.code, d.name, COUNT(s.id) AS student_count
     FROM departments d
     LEFT JOIN students s ON s.department_id = d.id
     GROUP BY d.id, d.code, d.name
     ORDER BY d.name ASC"
);
if (!$departmentsResult) {
    http_response_code(500);
    exit("Unable to load departments: " . htmlspecialchars($conn->error));
}

$messages = [
    "added" => ["success", "Department added."],
    "updated" => ["success", "Department updated."],
    "deleted" => ["success", "Department deleted."],
    "duplicate_code_or_name" => ["danger", "That department code or name is already in use."],
    "invalid_code" => ["danger", "Enter a department code up to 50 characters."],
    "invalid_name" => ["danger", "Enter a department name up to 100 characters."],
    "invalid_id" => ["danger", "The selected department is invalid."],
    "invalid_action" => ["danger", "That action is not supported."],
    "invalid_input" => ["danger", "The submitted department data is invalid."],
    "database" => ["danger", "Unable to access departments in the database."],
    "save_failed" => ["danger", "Unable to save the department."],
    "delete_failed" => ["danger", "Unable to delete the department."],
    "in_use" => ["warning", "This department is used by existing students and cannot be deleted."],
    "not_found" => ["warning", "The department was not found."]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments - Online Resibo</title>
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
                <li class="nav-item"><a class="nav-link" data-lte-toggle="sidebar" href="#" aria-label="Toggle navigation"><i class="bi bi-list"></i></a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><span class="nav-link"><?= htmlspecialchars($_SESSION["fullname"] ?? "User") ?> <span class="badge text-bg-primary"><?= htmlspecialchars($_SESSION["role"] ?? "") ?></span></span></li>
                <li class="nav-item"><a href="../auth/logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">
        <div class="sidebar-brand"><a href="../dashboard.php" class="brand-link"><span class="brand-text fw-light">🏫 <strong>ONLINE RESIBO</strong></span></a></div>
        <div class="sidebar-wrapper">
            <nav>
                <ul class="nav sidebar-menu flex-column">
                    <li class="nav-item"><a href="../dashboard.php" class="nav-link"><p>🏠 &nbsp; Dashboard</p></a></li>
                    <li class="nav-item"><a href="../students/index.php" class="nav-link"><p>👨‍🎓 &nbsp; Students</p></a></li>
                    <li class="nav-item"><a href="../programs/index.php" class="nav-link"><p>📚 &nbsp; Courses / Programs</p></a></li>
                    <li class="nav-item"><a href="index.php" class="nav-link active"><p>🏢 &nbsp; Departments</p></a></li>
                    <li class="nav-item"><a href="../payments/index.php" class="nav-link"><p>💳 &nbsp; Payments</p></a></li>
                    <li class="nav-item"><a href="../payment-types/index.php" class="nav-link"><p>🏷️ &nbsp; Payment Types</p></a></li>
                    <li class="nav-item"><a href="../receipts/index.php" class="nav-link"><p>🧾 &nbsp; Receipts</p></a></li>
                    <li class="nav-item"><a href="../reports/index.php" class="nav-link"><p>📊 &nbsp; Reports</p></a></li>
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
                    <div class="col-sm-6"><h3 class="mb-0">Department Management</h3></div>
                    <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="../dashboard.php">Home</a></li><li class="breadcrumb-item active">Departments</li></ol></div>
                </div>
            </div>
        </div>

        <div class="app-content">
            <div class="container-fluid">
                <div class="card card-primary mb-4">
                    <div class="card-header"><h3 class="card-title"><strong>Add Department</strong></h3></div>
                    <form method="POST" action="index.php">
                        <input type="hidden" name="action" value="add">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="department_code" class="form-label">Department Code</label>
                                    <input type="text" name="code" id="department_code" class="form-control" maxlength="50" placeholder="e.g. CS" required>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label for="department_name" class="form-label">Department Name</label>
                                    <input type="text" name="name" id="department_name" class="form-control" maxlength="100" placeholder="e.g. Computer Science" required>
                                </div>
                                <div class="col-md-3 mb-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">+ Save Department</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <div class="card-header"><h3 class="card-title">Available Departments</h3></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="departmentTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th width="150">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($departmentsResult->num_rows > 0): ?>
                                        <?php $count = 1; ?>
                                        <?php while ($department = $departmentsResult->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $count++ ?></td>
                                                <td><?= htmlspecialchars($department["code"]) ?></td>
                                                <td><?= htmlspecialchars($department["name"]) ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-warning btn-sm edit-department-btn" data-id="<?= (int) $department["id"] ?>" data-code="<?= htmlspecialchars($department["code"], ENT_QUOTES, 'UTF-8') ?>" data-name="<?= htmlspecialchars($department["name"], ENT_QUOTES, 'UTF-8') ?>">Edit</button>
                                                    <form method="POST" action="index.php" class="department-delete-form d-inline" data-department-name="<?= htmlspecialchars($department["name"], ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?= (int) $department["id"] ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center text-muted py-4">No departments registered yet.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="index.php" data-disable-unchanged>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_department_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDepartmentModalLabel">Edit Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_department_code" class="form-label">Department Code</label>
                            <input type="text" name="code" id="edit_department_code" class="form-control" maxlength="50" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_department_name" class="form-label">Department Name</label>
                            <input type="text" name="name" id="edit_department_name" class="form-control" maxlength="100" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" data-change-submit disabled>Update Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="app-footer"><strong>Online Resibo</strong> &copy; 2026<div class="float-end d-none d-sm-inline">School Payment Management System</div></footer>
</div>
<script src="../adminlte/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/sweetalert2.all.min.js"></script>
<script>
document.querySelectorAll(".edit-department-btn").forEach(function (button) {
    button.addEventListener("click", function () {
        document.getElementById("edit_department_id").value = button.dataset.id;
        document.getElementById("edit_department_code").value = button.dataset.code;
        document.getElementById("edit_department_name").value = button.dataset.name;
        bootstrap.Modal.getOrCreateInstance(document.getElementById("editDepartmentModal")).show();
    });
});

document.querySelectorAll(".department-delete-form").forEach(function (form) {
    form.addEventListener("submit", function (event) {
        event.preventDefault();
        Swal.fire({
            title: "Delete department?",
            text: "Delete " + form.dataset.departmentName + "? This action cannot be undone.",
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
</body>
</html>
