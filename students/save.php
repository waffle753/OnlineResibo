<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/database.php";

$departmentsTableExists = $conn->query("SHOW TABLES LIKE 'departments'");
if (!$departmentsTableExists || $departmentsTableExists->num_rows === 0) {
    header("Location: index.php?error=department_missing");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: add.php");
    exit();
}

$student_id = trim($_POST["student_id"] ?? "");
$first_name = trim($_POST["first_name"] ?? "");
$middle_name = trim($_POST["middle_name"] ?? "");
$last_name = trim($_POST["last_name"] ?? "");
$emailInput = $_POST["email"] ?? "";
if (!is_string($emailInput)) {
    header("Location: index.php?error=invalid_email");
    exit();
}
$email = trim($emailInput);
$program_id = filter_var($_POST["program_id"] ?? null, FILTER_VALIDATE_INT);
$department_id = filter_var($_POST["department_id"] ?? null, FILTER_VALIDATE_INT);
$year_level = trim($_POST["year_level"] ?? "");
$status = $_POST["status"] ?? "Active";

if (
    $student_id === "" ||
    $first_name === "" ||
    $last_name === "" ||
    $program_id === false ||
    $program_id === null ||
    $program_id <= 0 ||
    $department_id === false ||
    $department_id === null ||
    $department_id <= 0 ||
    $year_level === ""
) {
    header("Location: index.php?error=missing");
    exit();
}

if (!in_array($status, ["Active", "Inactive"], true)) {
    header("Location: index.php?error=invalid_status");
    exit();
}

if ($email !== "" && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    header("Location: index.php?error=invalid_email");
    exit();
}

$sql = "INSERT INTO students
        (student_id, first_name, middle_name, last_name, email, program_id, department_id, year_level, status)
        VALUES (?, ?, NULLIF(?, ''), ?, NULLIF(?, ''), ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log("Unable to prepare student insert: " . $conn->error);
    header("Location: index.php?error=database");
    exit();
}

$stmt->bind_param(
    "sssssiiss",
    $student_id,
    $first_name,
    $middle_name,
    $last_name,
    $email,
    $program_id,
    $department_id,
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

    header("Location: index.php?error=duplicate_student_id");
    exit();
}

$error = $stmt->error;

$stmt->close();
$conn->close();

error_log("Unable to save student: " . $error);
header("Location: index.php?error=save_failed");
exit();

?>