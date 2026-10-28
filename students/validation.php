<?php

function validateStudentIdentity(array $input): array
{
    $getString = static function (string $field) use ($input): ?string {
        if (!array_key_exists($field, $input)) {
            return null;
        }

        return is_string($input[$field]) ? $input[$field] : null;
    };

    if (!array_key_exists("student_id", $input) || $input["student_id"] === "") {
        return ["error" => "student_id_required"];
    }
    $studentId = $getString("student_id");
    if ($studentId === null || preg_match('/\A[0-9]{9}\z/', $studentId) !== 1) {
        return ["error" => "student_id_format"];
    }

    $firstName = trim($getString("first_name") ?? "");
    $middleName = trim($getString("middle_name") ?? "");
    $lastName = trim($getString("last_name") ?? "");

    $validateName = static function (string $value, string $field, bool $required): ?string {
        if ($value === "") {
            return $required ? "{$field}_required" : null;
        }

        if (preg_match('//u', $value) !== 1) {
            return "{$field}_characters";
        }

        $length = preg_match_all('/./us', $value);
        if ($length === false) {
            return "{$field}_characters";
        }
        if ($length < 2 || $length > 50) {
            return "{$field}_length";
        }
        if (preg_match("/\\A\\p{L}[\\p{L} '-]*\\p{L}\\z/u", $value) !== 1) {
            return "{$field}_characters";
        }

        return null;
    };

    foreach ([
        ["first_name", $firstName, true],
        ["middle_name", $middleName, false],
        ["last_name", $lastName, true]
    ] as [$field, $value, $required]) {
        $error = $validateName($value, $field, $required);
        if ($error !== null) {
            return ["error" => $error];
        }
    }

    $emailInput = $getString("email");
    if ($emailInput === null && array_key_exists("email", $input)) {
        return ["error" => "invalid_email"];
    }

    $email = trim($emailInput ?? "");
    if (strlen($email) > 254) {
        return ["error" => "email_too_long"];
    }
    if ($email !== "" && (preg_match('/\s/u', $email) === 1 || filter_var($email, FILTER_VALIDATE_EMAIL) === false)) {
        return ["error" => "invalid_email"];
    }

    return [
        "error" => null,
        "values" => [
            "student_id" => $studentId,
            "first_name" => $firstName,
            "middle_name" => $middleName,
            "last_name" => $lastName,
            "email" => $email
        ]
    ];
}

function studentIdExists(mysqli $conn, string $studentId, ?int $excludeId = null): ?bool
{
    $sql = "SELECT id FROM students WHERE student_id = ?";
    if ($excludeId !== null) {
        $sql .= " AND id <> ?";
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Unable to prepare student ID duplicate check: " . $conn->error);
        return null;
    }

    if ($excludeId === null) {
        $stmt->bind_param("s", $studentId);
    } else {
        $stmt->bind_param("si", $studentId, $excludeId);
    }

    if (!$stmt->execute() || !$stmt->store_result()) {
        error_log("Unable to check for duplicate student ID: " . $stmt->error);
        $stmt->close();
        return null;
    }

    $exists = $stmt->num_rows > 0;
    $stmt->close();

    return $exists;
}
