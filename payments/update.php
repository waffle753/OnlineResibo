<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/database.php";


// =====================================================
// CHECK REQUEST
// =====================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: index.php");

    exit();

}


// =====================================================
// GET DATA
// =====================================================

$id = (int) ($_POST["id"] ?? 0);

$student_id =
    trim($_POST["student_id"] ?? "");

$payment_type =
    trim($_POST["payment_type"] ?? "");

$amount =
    trim($_POST["amount"] ?? "");

$payment_date =
    trim($_POST["payment_date"] ?? "");

$payment_method =
    trim($_POST["payment_method"] ?? "");

$status =
    trim($_POST["status"] ?? "Paid");


// =====================================================
// VALIDATION
// =====================================================

if (
    $id <= 0 ||
    empty($student_id) ||
    empty($payment_type) ||
    empty($amount) ||
    empty($payment_date) ||
    empty($payment_method) ||
    empty($status)
) {

    header(
        "Location: index.php?error=missing"
    );

    exit();

}


// =====================================================
// CHECK AMOUNT
// =====================================================

if (
    !is_numeric($amount) ||
    (float) $amount <= 0
) {

    header(
        "Location: index.php?error=invalid_amount"
    );

    exit();

}

$amount = (float) $amount;


// =====================================================
// CHECK PAYMENT EXISTS
// =====================================================

$checkPayment =
    $conn->prepare(
        "SELECT id
         FROM payments
         WHERE id = ?"
    );


if (!$checkPayment) {

    header(
        "Location: index.php?error=database"
    );

    exit();

}


$checkPayment->bind_param(
    "i",
    $id
);


$checkPayment->execute();


$paymentResult =
    $checkPayment->get_result();


if ($paymentResult->num_rows === 0) {

    $checkPayment->close();

    header(
        "Location: index.php?error=payment_not_found"
    );

    exit();

}


$checkPayment->close();


// =====================================================
// CHECK STUDENT EXISTS
// =====================================================

$checkStudent =
    $conn->prepare(
        "SELECT id
         FROM students
         WHERE id = ?"
    );


if (!$checkStudent) {

    header(
        "Location: index.php?error=database"
    );

    exit();

}


$checkStudent->bind_param(
    "i",
    $student_id
);


$checkStudent->execute();


$studentResult =
    $checkStudent->get_result();


if ($studentResult->num_rows === 0) {

    $checkStudent->close();

    header(
        "Location: index.php?error=student_not_found"
    );

    exit();

}


$checkStudent->close();


// =====================================================
// UPDATE PAYMENT
// =====================================================
//
// EXACT DATABASE COLUMNS:
//
// student_id
// payment_type
// amount
// payment_date
// payment_method
// status
//
// reference_no is NOT changed.
// created_at is NOT changed.
// =====================================================

$sql = "
    UPDATE payments
    SET
        student_id = ?,
        payment_type = ?,
        amount = ?,
        payment_date = ?,
        payment_method = ?,
        status = ?
    WHERE id = ?
";


$stmt =
    $conn->prepare($sql);


if (!$stmt) {

    header(
        "Location: index.php?error=database"
    );

    exit();

}


// =====================================================
// BIND
// =====================================================

$stmt->bind_param(
    "isdsssi",
    $student_id,
    $payment_type,
    $amount,
    $payment_date,
    $payment_method,
    $status,
    $id
);


// =====================================================
// UPDATE
// =====================================================

if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: index.php?success=updated"
    );

    exit();

}


// =====================================================
// FAILED
// =====================================================

$stmt->close();

header(
    "Location: index.php?error=update_failed"
);

exit();

?>