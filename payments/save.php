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
// GET FORM DATA
// =====================================================

$student_id = trim($_POST["student_id"] ?? "");

$payment_type = trim($_POST["payment_type"] ?? "");

$amount = trim($_POST["amount"] ?? "");

$payment_date = trim($_POST["payment_date"] ?? "");

$payment_method = trim($_POST["payment_method"] ?? "");

$status = trim($_POST["status"] ?? "Paid");


// =====================================================
// VALIDATION
// =====================================================

if (
    empty($student_id) ||
    empty($payment_type) ||
    empty($amount) ||
    empty($payment_date) ||
    empty($payment_method)
) {

    header("Location: index.php?error=missing");

    exit();

}


// =====================================================
// CHECK AMOUNT
// =====================================================

if (!is_numeric($amount) || $amount <= 0) {

    header("Location: index.php?error=invalid_amount");

    exit();

}


$amount = (float) $amount;


// =====================================================
// CHECK STUDENT
// =====================================================

$checkStudent = $conn->prepare(
    "SELECT id FROM students WHERE id = ?"
);


if (!$checkStudent) {

    header("Location: index.php?error=database");

    exit();

}


$checkStudent->bind_param(
    "i",
    $student_id
);


$checkStudent->execute();


$studentResult = $checkStudent->get_result();


if ($studentResult->num_rows === 0) {

    $checkStudent->close();

    header("Location: index.php?error=student_not_found");

    exit();

}


$checkStudent->close();


// =====================================================
// GENERATE PAYMENT REFERENCE AUTOMATICALLY
// =====================================================
//
// Example:
// PAY-2026-0001
// PAY-2026-0002
// PAY-2026-0003
//
// The reference is NOT entered by the user.
// =====================================================

$year = date("Y");


// Get the latest payment ID

$referenceQuery = $conn->query(
    "SELECT id
     FROM payments
     ORDER BY id DESC
     LIMIT 1"
);


if ($referenceQuery && $referenceQuery->num_rows > 0) {

    $lastPayment = $referenceQuery->fetch_assoc();

    $nextNumber = ((int) $lastPayment["id"]) + 1;

} else {

    $nextNumber = 1;

}


$reference_no = "PAY-" .
                $year .
                "-" .
                str_pad(
                    $nextNumber,
                    4,
                    "0",
                    STR_PAD_LEFT
                );


// =====================================================
// INSERT PAYMENT
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
// reference_no
//
// created_at is NOT included because MySQL can
// automatically insert it if DEFAULT CURRENT_TIMESTAMP
// is configured.
// =====================================================

$sql = "
    INSERT INTO payments
    (
        student_id,
        payment_type,
        amount,
        payment_date,
        payment_method,
        status,
        reference_no
    )
    VALUES
    (?, ?, ?, ?, ?, ?, ?)
";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    header("Location: index.php?error=database");

    exit();

}


// =====================================================
// BIND PARAMETERS
// =====================================================

$stmt->bind_param(
    "isdssss",
    $student_id,
    $payment_type,
    $amount,
    $payment_date,
    $payment_method,
    $status,
    $reference_no
);


// =====================================================
// SAVE PAYMENT
// =====================================================

if ($stmt->execute()) {

    $stmt->close();

    // Send generated reference to index.php

    header(
        "Location: index.php?success=added&reference=" .
        urlencode($reference_no)
    );

    exit();

}


// =====================================================
// SAVE FAILED
// =====================================================

$stmt->close();


header("Location: index.php?error=save_failed");

exit();

?>