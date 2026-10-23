<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/database.php";


// Check if ID exists

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php?error=invalid_id");
    exit();
}


$id = (int) $_GET["id"];


// Delete student

$stmt = $conn->prepare("DELETE FROM students WHERE id = ?");

$stmt->bind_param("i", $id);


if ($stmt->execute()) {

    // Successfully deleted

    header("Location: index.php?success=deleted");
    exit();

} else {

    // Delete failed

    $stmt->close();
    $conn->close();
    header("Location: index.php?error=delete_failed");
    exit();

}

$stmt->close();

$conn->close();

?>