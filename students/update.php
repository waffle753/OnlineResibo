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

if (
    !isset($_POST["id"]) ||
    !isset($_POST["program_id"]) ||
    !isset($_POST["department_id"]) ||
    !isset($_POST["year_level"]) ||
    !isset($_POST["status"])
) {
    header("Location: index.php?error=missing");
    exit();
}


$id = filter_var($_POST["id"], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
if ($id === false) {
    header("Location: index.php?error=invalid_id");
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
$program_id = filter_var($_POST["program_id"], FILTER_VALIDATE_INT);
$department_id = filter_var($_POST["department_id"], FILTER_VALIDATE_INT);
$year_level = trim($_POST["year_level"]);
$status = trim($_POST["status"]);

if ($program_id === false || $program_id <= 0) {
    header("Location: index.php?error=invalid_program");
    exit();
}

if ($department_id === false || $department_id <= 0) {
    header("Location: index.php?error=invalid_department");
    exit();
}

$studentIdExists = studentIdExists($conn, $student_id, $id);
if ($studentIdExists === null) {
    header("Location: index.php?error=database");
    exit();
}
if ($studentIdExists) {
    header("Location: index.php?error=duplicate_student_id");
    exit();
}

$stmt = $conn->prepare(
    "UPDATE students
     SET student_id = ?,
         first_name = ?,
         middle_name = NULLIF(?, ''),
         last_name = ?,
         email = NULLIF(?, ''),
         program_id = ?,
         department_id = ?,
         year_level = ?,
         status = ?
     WHERE id = ?"
);


$stmt->bind_param(
    "sssssiissi",
    $student_id,
    $first_name,
    $middle_name,
    $last_name,
    $email,
    $program_id,
    $department_id,
    $year_level,
    $status,
    $id
);


if ($stmt->execute()) {

    header("Location: index.php?success=updated");
    exit();

} else {

    $errorCode = $stmt->errno;
    $stmt->close();
    $conn->close();
    header("Location: index.php?error=" . ($errorCode === 1062 ? "duplicate_student_id" : "update_failed"));
    exit();

}


$stmt->close();

$conn->close();
?>