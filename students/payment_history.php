<?php

session_start();

header("Content-Type: application/json; charset=utf-8");

$respond = static function (int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload);
    exit();
};

if (!isset($_SESSION["user_id"])) {
    $respond(401, ["error" => "Your session has expired. Please sign in again."]);
}

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    $respond(405, ["error" => "This request method is not supported."]);
}

$studentId = filter_var(
    $_GET["student_id"] ?? null,
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);
if ($studentId === false || $studentId === null) {
    $respond(400, ["error" => "A valid student is required."]);
}

require_once "../config/database.php";

$stmt = $conn->prepare(
    "SELECT
        p.id,
        p.reference_no,
        p.payment_type,
        p.amount,
        p.payment_date,
        p.payment_method,
        p.status
     FROM payments p
     WHERE p.student_id = ?
     ORDER BY p.payment_date DESC, p.id DESC"
);
if (!$stmt) {
    error_log("Unable to prepare student payment history query: " . $conn->error);
    $respond(500, ["error" => "Unable to load payment history right now."]);
}

$stmt->bind_param("i", $studentId);
if (!$stmt->execute()) {
    error_log("Unable to load student payment history: " . $stmt->error);
    $stmt->close();
    $respond(500, ["error" => "Unable to load payment history right now."]);
}

$payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

$respond(200, ["payments" => $payments]);
