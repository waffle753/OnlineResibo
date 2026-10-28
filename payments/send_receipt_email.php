<?php

function sendPaymentReceiptEmail(mysqli $conn, int $paymentId): string
{
    $stmt = $conn->prepare(
        "SELECT
            p.reference_no,
            p.payment_type,
            p.amount,
            p.payment_date,
            p.payment_method,
            p.status,
            s.student_id,
            CONCAT_WS(' ', s.first_name, s.middle_name, s.last_name) AS student_name,
            s.email
         FROM payments p
         JOIN students s ON s.id = p.student_id
         WHERE p.id = ?"
    );

    if (!$stmt) {
        error_log("Unable to prepare payment receipt email data: " . $conn->error);
        return "failed";
    }

    $stmt->bind_param("i", $paymentId);
    if (!$stmt->execute()) {
        error_log("Unable to load payment receipt email data: " . $stmt->error);
        $stmt->close();
        return "failed";
    }

    $payment = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$payment || $payment["status"] !== "Paid") {
        error_log("Payment receipt email requested for a missing or unpaid payment.");
        return "failed";
    }

    $email = trim((string) ($payment["email"] ?? ""));
    if ($email === "" || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return "skipped";
    }

    $appRoot = dirname(__DIR__);
    $envPath = $appRoot . DIRECTORY_SEPARATOR . ".env";
    $env = is_file($envPath) ? parse_ini_file($envPath, false, INI_SCANNER_RAW) : false;
    $nodeBinary = is_array($env) && isset($env["NODE_BINARY"]) && $env["NODE_BINARY"] !== ""
        ? $env["NODE_BINARY"]
        : "node";
    $mailerPath = $appRoot . DIRECTORY_SEPARATOR . "mailer" . DIRECTORY_SEPARATOR . "send-receipt.js";

    if (!function_exists("proc_open") || !is_file($mailerPath)) {
        error_log("Node.js receipt mailer is unavailable.");
        return "failed";
    }

    $process = proc_open(
        [$nodeBinary, $mailerPath],
        [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ],
        $pipes,
        $appRoot
    );

    if (!is_resource($process)) {
        error_log("Unable to start the Node.js receipt mailer.");
        return "failed";
    }

    $payment["to"] = $email;
    fwrite($pipes[0], json_encode($payment, JSON_THROW_ON_ERROR));
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);
    $result = json_decode($stdout, true);

    if ($exitCode !== 0 || !is_array($result) || ($result["sent"] ?? false) !== true) {
        error_log("Payment receipt email failed: " . trim($stderr ?: "Mailer returned an unsuccessful result."));
        return "failed";
    }

    return "sent";
}
