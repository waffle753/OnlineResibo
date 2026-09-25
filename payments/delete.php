<?php

session_start();

if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");

    exit();

}

require_once "../config/database.php";


// =====================================================
// CHECK ID
// =====================================================

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {

    header(
        "Location: index.php?error=delete_failed"
    );

    exit();

}


$id = (int) $_GET["id"];


if ($id <= 0) {

    header(
        "Location: index.php?error=delete_failed"
    );

    exit();

}


// =====================================================
// CHECK PAYMENT EXISTS
// =====================================================

$check =
    $conn->prepare(
        "SELECT id
         FROM payments
         WHERE id = ?"
    );


if (!$check) {

    header(
        "Location: index.php?error=delete_failed"
    );

    exit();

}


$check->bind_param(
    "i",
    $id
);


$check->execute();


$result =
    $check->get_result();


if ($result->num_rows === 0) {

    $check->close();

    header(
        "Location: index.php?error=payment_not_found"
    );

    exit();

}


$check->close();


// =====================================================
// DELETE
// =====================================================

$stmt =
    $conn->prepare(
        "DELETE FROM payments WHERE id = ?"
    );


if (!$stmt) {

    header(
        "Location: index.php?error=delete_failed"
    );

    exit();

}


$stmt->bind_param(
    "i",
    $id
);


// =====================================================
// EXECUTE DELETE
// =====================================================

if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: index.php?success=deleted"
    );

    exit();

}


// =====================================================
// DELETE FAILED
// =====================================================

$stmt->close();

header(
    "Location: index.php?error=delete_failed"
);

exit();

?>