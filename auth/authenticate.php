<?php

session_start();

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

if ($username === "" || $password === "") {
    header("Location: login.php?error=empty");
    exit();
}

$sql = "SELECT id, fullname, username, password, role
        FROM users
        WHERE username = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $user = $result->fetch_assoc();

    // Check the hashed password
    if (password_verify($password, $user["password"])) {

        // Prevent session fixation
        session_regenerate_id(true);

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["fullname"] = $user["fullname"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["role"] = $user["role"];

        $stmt->close();
        $conn->close();

        header("Location: ../dashboard.php");
        exit();
    }
}

$stmt->close();
$conn->close();

header("Location: login.php?error=invalid");
exit();

?>