<?php

require_once "../config/database.php";

$fullname = "System Administrator";
$username = "admin";
$password = "admin123";
$role = "Admin";

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (fullname, username, password, role)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param(
    "ssss",
    $fullname,
    $username,
    $hashedPassword,
    $role
);

if ($stmt->execute()) {

    echo "<h2>Admin account created successfully!</h2>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";

} else {

    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();

?>