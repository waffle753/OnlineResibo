<?php

require_once "../config/database.php";

$first_name = "System";
$middle_name = "";
$last_name = "Administrator";
$username = "admin";
$password = "admin123";
$role = "Admin";

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (first_name, middle_name, last_name, username, password, role)
        VALUES (?, NULLIF(?, ''), ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log("Admin setup prepare failed: " . $conn->error);
    $resultIcon = "error";
    $resultTitle = "Unable to create administrator";
    $resultText = "The administrator account could not be created.";
} else {
    $stmt->bind_param(
        "ssssss",
        $first_name,
        $middle_name,
        $last_name,
        $username,
        $hashedPassword,
        $role
    );

    if ($stmt->execute()) {
        $resultIcon = "success";
        $resultTitle = "Administrator created";
        $resultText = "Username: admin | Password: admin123";
    } else {
        error_log("Admin setup failed: " . $stmt->error);
        $resultIcon = "error";
        $resultTitle = "Unable to create administrator";
        $resultText = $stmt->errno === 1062
            ? "The admin account already exists."
            : "The administrator account could not be created.";
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Setup - Online Resibo</title>
    <link rel="stylesheet" href="../assets/css/sweetalert2.css">
</head>
<body>
<script src="../assets/js/sweetalert2.all.min.js"></script>
<script>
Swal.fire({
    icon: <?= json_encode($resultIcon) ?>,
    title: <?= json_encode($resultTitle, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    text: <?= json_encode($resultText, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    confirmButtonColor: "#0d6efd"
}).then(function () {
    window.location.href = "login.php";
});
</script>
</body>
</html>