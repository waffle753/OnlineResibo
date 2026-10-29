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
    $configuredNodeBinary = is_array($env) && isset($env["NODE_BINARY"])
        ? trim($env["NODE_BINARY"], " \t\n\r\0\x0B\"'")
        : "";
    $nodeBinary = $configuredNodeBinary !== "" ? $configuredNodeBinary : "node";
    $mailerPath = $appRoot . DIRECTORY_SEPARATOR . "backend" . DIRECTORY_SEPARATOR . "server.js";

    if (!function_exists("popen") || !function_exists("pclose") || !is_file($mailerPath)) {
        error_log("Node.js receipt mailer is unavailable.");
        return "failed";
    }

    try {
        $payment["to"] = $email;
        $jobJson = json_encode($payment, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        error_log("Unable to encode payment receipt email job: " . $exception->getMessage());
        return "failed";
    }

    $jobPath = tempnam(sys_get_temp_dir(), "online-resibo-email-");
    if ($jobPath === false) {
        error_log("Unable to create a temporary payment receipt email job.");
        return "failed";
    }

    if (file_put_contents($jobPath, $jobJson, LOCK_EX) === false) {
        unlink($jobPath);
        error_log("Unable to write the temporary payment receipt email job.");
        return "failed";
    }

    $errorLogPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "online-resibo-email.log";
    $command = "start \"\" /B " .
        escapeshellarg($nodeBinary) . " " .
        escapeshellarg($mailerPath) . " " .
        escapeshellarg($jobPath) .
        " >NUL 2>>" . escapeshellarg($errorLogPath);
    $process = @popen($command, "r");

    if (!is_resource($process)) {
        unlink($jobPath);
        error_log("Unable to start the background Node.js receipt mailer.");
        return "failed";
    }

    $exitCode = pclose($process);
    if ($exitCode !== 0) {
        if (is_file($jobPath)) {
            unlink($jobPath);
        }
        error_log("Unable to launch the background Node.js receipt mailer (exit code " . $exitCode . ").");
        return "failed";
    }

    return "queued";
}
