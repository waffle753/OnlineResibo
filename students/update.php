<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/database.php";


if (
    !isset($_POST["id"]) ||
    !isset($_POST["student_id"]) ||
    !isset($_POST["fullname"]) ||
    !isset($_POST["course"]) ||
    !isset($_POST["year_level"]) ||
    !isset($_POST["status"])
) {
    header("Location: index.php?success=error");
    exit();
}


$id = (int) $_POST["id"];

$student_id = trim($_POST["student_id"]);
$fullname = trim($_POST["fullname"]);
$course = trim($_POST["course"]);
$year_level = trim($_POST["year_level"]);
$status = trim($_POST["status"]);


$stmt = $conn->prepare(
    "UPDATE students
     SET student_id = ?,
         fullname = ?,
         course = ?,
         year_level = ?,
         status = ?
     WHERE id = ?"
);


$stmt->bind_param(
    "sssssi",
    $student_id,
    $fullname,
    $course,
    $year_level,
    $status,
    $id
);


if ($stmt->execute()) {

    header("Location: index.php?success=updated");
    exit();

} else {

    header("Location: index.php?success=error");
    exit();

}


$stmt->close();

$conn->close();

?><?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/database.php";

?>
if (
!isset($_POST["id"]) ||
!isset($_POST["student_id"]) ||
!isset($_POST["fullname"]) ||
!isset($_POST["course"]) ||
!isset($_POST["year_level"]) ||
!isset($_POST["status"])
) {
header("Location: index.php?success=error");
exit();
}


$id = (int) $_POST["id"];

$student_id = trim($_POST["student_id"]);
$fullname = trim($_POST["fullname"]);
$course = trim($_POST["course"]);
$year_level = trim($_POST["year_level"]);
$status = trim($_POST["status"]);


$stmt = $conn->prepare(
"UPDATE students
SET student_id = ?,
fullname = ?,
course = ?,
year_level = ?,
status = ?
WHERE id = ?"
);


$stmt->bind_param(
"sssssi",
$student_id,
$fullname,
$course,
$year_level,
$status,
$id
);


if ($stmt->execute()) {

header("Location: index.php?success=updated");
exit();

} else {

header("Location: index.php?success=error");
exit();

}


$stmt->close();

$conn->close();

?>