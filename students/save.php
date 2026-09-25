<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: add.php");
    exit();
}

$student_id = trim($_POST["student_id"] ?? "");
$fullname = trim($_POST["fullname"] ?? "");
$course = trim($_POST["course"] ?? "");
$year_level = trim($_POST["year_level"] ?? "");
$status = $_POST["status"] ?? "Active";

if (
    $student_id === "" ||
    $fullname === "" ||
    $course === "" ||
    $year_level === ""
) {
    die("Please complete all required fields.");
}

if (!in_array($status, ["Active", "Inactive"], true)) {
    die("Invalid student status.");
}

$sql = "INSERT INTO students
        (student_id, fullname, course, year_level, status)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "sssss",
    $student_id,
    $fullname,
    $course,
    $year_level,
    $status
);

if ($stmt->execute()) {

    $stmt->close();
    $conn->close();

   header("Location: index.php?success=added");
    exit();

}

if ($stmt->errno === 1062) {
    $stmt->close();
    $conn->close();

    die("Student ID already exists.");
}

$error = $stmt->error;

$stmt->close();
$conn->close();

die("Unable to save student: " . htmlspecialchars($error));

?>