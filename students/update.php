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

if (
    !isset($_POST["id"]) ||
    !isset($_POST["student_id"]) ||
    !isset($_POST["first_name"]) ||
    !isset($_POST["middle_name"]) ||
    !isset($_POST["last_name"]) ||
    !isset($_POST["email"]) ||
    !isset($_POST["program_id"]) ||
    !isset($_POST["department_id"]) ||
    !isset($_POST["year_level"]) ||
    !isset($_POST["status"])
) {
    header("Location: index.php?error=missing");
    exit();
}


$id = (int) $_POST["id"];

$student_id = trim($_POST["student_id"]);
$first_name = trim($_POST["first_name"]);
$middle_name = trim($_POST["middle_name"]);
$last_name = trim($_POST["last_name"]);
$emailInput = $_POST["email"];
if (!is_string($emailInput)) {
    header("Location: index.php?error=invalid_email");
    exit();
}
$email = trim($emailInput);
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

if ($email !== "" && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    header("Location: index.php?error=invalid_email");
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