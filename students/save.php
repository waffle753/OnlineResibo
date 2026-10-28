<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/database.php";
require_once "validation.php";

$departmentsTableExists = $conn->query("SHOW TABLES LIKE 'departments'");
if (!$departmentsTableExists || $departmentsTableExists->num_rows === 0) {
    header("Location: index.php?error=department_missing");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: add.php");
    exit();
}

$validation = validateStudentIdentity($_POST);
if ($validation["error"] !== null) {
    header("Location: index.php?error=" . urlencode($validation["error"]));
    exit();
}

$student = $validation["values"];
$student_id = $student["student_id"];
$first_name = $student["first_name"];
$middle_name = $student["middle_name"];
$last_name = $student["last_name"];
$email = $student["email"];
$program_id = filter_var($_POST["program_id"] ?? null, FILTER_VALIDATE_INT);
$department_id = filter_var($_POST["department_id"] ?? null, FILTER_VALIDATE_INT);
$year_level = trim($_POST["year_level"] ?? "");
$status = $_POST["status"] ?? "Active";

if (
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

$studentIdExists = studentIdExists($conn, $student_id);
if ($studentIdExists === null) {
    header("Location: index.php?error=database");
    exit();
}
if ($studentIdExists) {
    header("Location: index.php?error=duplicate_student_id");
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