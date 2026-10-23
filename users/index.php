<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

if (($_SESSION["role"] ?? "") !== "Admin") {
    http_response_code(403);
    exit("Only administrators can manage users.");
}

require_once "../config/database.php";

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add" || $action === "update") {
        $firstName = trim($_POST["first_name"] ?? "");
        $middleName = trim($_POST["middle_name"] ?? "");
        $lastName = trim($_POST["last_name"] ?? "");
        $username = trim($_POST["username"] ?? "");
        $roleInput = $_POST["role"] ?? "Staff";

        if ($firstName === "" || $lastName === "" || $username === "") {
            header("Location: index.php?message=invalid_input");
            exit();
        }

        if (!is_string($roleInput) || !in_array($roleInput, ["Admin", "Staff"], true)) {
            header("Location: index.php?message=invalid_role");
            exit();
        }
        $role = $roleInput;

        if (mb_strlen($firstName) > 80 || mb_strlen($middleName) > 80 || mb_strlen($lastName) > 80 || mb_strlen($username) > 100) {
            header("Location: index.php?message=invalid_length");
            exit();
        }

        if ($action === "add") {
            $password = $_POST["password"] ?? "";
            if (!is_string($password) || strlen($password) < 6) {
                header("Location: index.php?message=weak_password");
                exit();
            }

            $existing = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            if (!$existing) {
                header("Location: index.php?message=database");
                exit();
            }
            $existing->bind_param("s", $username);
            $existing->execute();
            if ($existing->get_result()->num_rows > 0) {
                $existing->close();
                header("Location: index.php?message=username_taken");
                exit();
            }
            $existing->close();

            $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, username, password, role) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                header("Location: index.php?message=database");
                exit();
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param("ssssss", $firstName, $middleName, $lastName, $username, $hashedPassword, $role);
            if (!$stmt->execute()) {
                $stmt->close();
                header("Location: index.php?message=save_failed");
                exit();
            }
            $stmt->close();
            $conn->close();
            header("Location: index.php?message=added");
            exit();
        }

        $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id === null || $id <= 0) {
            header("Location: index.php?message=invalid_id");
            exit();
        }

        $check = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1");
        if (!$check) {
            header("Location: index.php?message=database");
            exit();
        }
        $check->bind_param("si", $username, $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $check->close();
            header("Location: index.php?message=username_taken");
            exit();
        }
        $check->close();

        $newPassword = $_POST["password"] ?? "";
        if (is_string($newPassword) && $newPassword !== "") {
            if (strlen($newPassword) < 6) {
                header("Location: index.php?message=weak_password");
                exit();
            }
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, username = ?, password = ?, role = ? WHERE id = ?");
            $stmt->bind_param("ssssssi", $firstName, $middleName, $lastName, $username, $hashedPassword, $role, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, username = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $firstName, $middleName, $lastName, $username, $role, $id);
        }

        if (!$stmt) {
            header("Location: index.php?message=database");
            exit();
        }

        if (!$stmt->execute()) {
            $stmt->close();
            header("Location: index.php?message=save_failed");
            exit();
        }

        $stmt->close();
        $conn->close();
        header("Location: index.php?message=updated");
        exit();
    }

    if ($action === "delete") {
        $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id === null || $id <= 0) {
            header("Location: index.php?message=invalid_id");
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if (!$stmt) {
            header("Location: index.php?message=database");
            exit();
        }
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            $stmt->close();
            header("Location: index.php?message=delete_failed");
            exit();
        }

        $stmt->close();
        $conn->close();
        header("Location: index.php?message=deleted");
        exit();
    }

    header("Location: index.php?message=invalid_action");
    exit();
}

$usersResult = $conn->query(
    "SELECT id, first_name, middle_name, last_name, username, role, created_at
     FROM users
     ORDER BY id DESC"
);

$messages = [
    "added" => ["success", "User added."],
    "updated" => ["success", "User updated."],
    "deleted" => ["success", "User deleted."],
    "invalid_input" => ["danger", "Please complete all required user fields."],
    "invalid_role" => ["danger", "Select either Admin or Staff as the user role."],
    "invalid_length" => ["danger", "User names and username are too long."],
    "weak_password" => ["danger", "Password must be at least 6 characters long."],
    "username_taken" => ["warning", "That username is already in use."],
    "invalid_id" => ["danger", "The selected user is invalid."],
    "invalid_action" => ["danger", "That action is not supported."],
    "database" => ["danger", "Unable to access users in the database."],
    "save_failed" => ["danger", "Unable to save the user."],
    "delete_failed" => ["danger", "Unable to delete the user."],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Online Resibo</title>
    <link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../assets/css/table-gridlines.css">
    <link rel="stylesheet" href="../assets/css/sweetalert2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
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
                    <li class="nav-item"><a href="../departments/index.php" class="nav-link"><p>🏢 &nbsp; Departments</p></a></li>
                    <li class="nav-item"><a href="../payments/index.php" class="nav-link"><p>💳 &nbsp; Payments</p></a></li>
                    <li class="nav-item"><a href="../payment-types/index.php" class="nav-link"><p>🏷️ &nbsp; Payment Types</p></a></li>
                    <li class="nav-item"><a href="../receipts/index.php" class="nav-link"><p>🧾 &nbsp; Receipts</p></a></li>
                    <li class="nav-item"><a href="../reports/index.php" class="nav-link"><p>📊 &nbsp; Reports</p></a></li>
                    <li class="nav-item"><a href="index.php" class="nav-link active"><p>👤 &nbsp; Users</p></a></li>
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
                    <div class="col-sm-6"><h3 class="mb-0">User Management</h3></div>
                    <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="../dashboard.php">Home</a></li><li class="breadcrumb-item active">Users</li></ol></div>
                </div>
            </div>
        </div>

        <div class="app-content">
            <div class="container-fluid">
                <?php
                $messageKey = $_GET["message"] ?? null;
                if (isset($messages[$messageKey])) {
                    [$type, $text] = $messages[$messageKey];
                    echo '<div class="alert alert-' . htmlspecialchars($type) . ' alert-dismissible fade show" role="alert">' . htmlspecialchars($text) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                }
                ?>

                <div class="card card-primary mb-4">
                    <div class="card-header"><h3 class="card-title"><strong>Add User</strong></h3></div>
                    <form method="POST" action="index.php">
                        <input type="hidden" name="action" value="add">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" name="first_name" id="first_name" class="form-control" maxlength="80" placeholder="e.g. Juan" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="middle_name" class="form-label">Middle Name</label>
                                    <input type="text" name="middle_name" id="middle_name" class="form-control" maxlength="80" placeholder="e.g. Santos">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" name="last_name" id="last_name" class="form-control" maxlength="80" placeholder="e.g. Dela Cruz" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <select name="role" id="role" class="form-select" required>
                                        <option value="Staff">Staff</option>
                                        <option value="Admin">Admin</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" name="username" id="username" class="form-control" maxlength="100" placeholder="e.g. juan.delacruz" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" name="password" id="password" class="form-control" minlength="6" placeholder="At least 6 characters" required>
                                </div>
                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">+ Save User</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <div class="card-header"><h3 class="card-title">System Users</h3></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="userTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Full Name</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Created</th>
                                        <th width="160">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($usersResult && $usersResult->num_rows > 0): ?>
                                        <?php $count = 1; ?>
                                        <?php while ($user = $usersResult->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $count++ ?></td>
                                                <td><?= htmlspecialchars(trim(($user["first_name"] ?? "") . " " . ($user["middle_name"] ?? "") . " " . ($user["last_name"] ?? ""))) ?></td>
                                                <td><?= htmlspecialchars($user["username"] ?? "") ?></td>
                                                <td><?= htmlspecialchars(($user["role"] ?? "") === "Admin" ? "Admin" : "Staff") ?></td>
                                                <td><?= htmlspecialchars($user["created_at"] ?? "") ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-warning btn-sm edit-user-btn"
                                                        data-id="<?= (int) ($user["id"] ?? 0) ?>"
                                                        data-first-name="<?= htmlspecialchars($user["first_name"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                                                        data-middle-name="<?= htmlspecialchars($user["middle_name"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                                                        data-last-name="<?= htmlspecialchars($user["last_name"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                                                        data-username="<?= htmlspecialchars($user["username"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                                                        data-role="<?= htmlspecialchars(($user["role"] ?? "") === "Admin" ? "Admin" : "Staff", ENT_QUOTES, "UTF-8") ?>">
                                                        Edit
                                                    </button>
                                                    <form method="POST" action="index.php" class="d-inline user-delete-form">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?= (int) ($user["id"] ?? 0) ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?');">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted py-4">No users registered yet.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="index.php">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_user_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_first_name" class="form-label">First Name</label>
                            <input type="text" name="first_name" id="edit_first_name" class="form-control" maxlength="80" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_middle_name" class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" id="edit_middle_name" class="form-control" maxlength="80">
                        </div>
                        <div class="mb-3">
                            <label for="edit_last_name" class="form-label">Last Name</label>
                            <input type="text" name="last_name" id="edit_last_name" class="form-control" maxlength="80" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username</label>
                            <input type="text" name="username" id="edit_username" class="form-control" maxlength="100" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_role" class="form-label">Role</label>
                            <select name="role" id="edit_role" class="form-select" required>
                                <option value="Staff">Staff</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">New Password</label>
                            <input type="password" name="password" id="edit_password" class="form-control" minlength="6" placeholder="Leave blank to keep current password">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editButtons = document.querySelectorAll('.edit-user-btn');
        editButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const userId = button.dataset.id;
                document.getElementById('edit_user_id').value = userId;
                document.getElementById('edit_first_name').value = button.dataset.firstName || '';
                document.getElementById('edit_middle_name').value = button.dataset.middleName || '';
                document.getElementById('edit_last_name').value = button.dataset.lastName || '';
                document.getElementById('edit_username').value = button.dataset.username || '';
                document.getElementById('edit_role').value = button.dataset.role || 'Staff';
                document.getElementById('edit_password').value = '';
                const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                modal.show();
            });
        });
    });
</script>
</body>
</html>
