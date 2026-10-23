<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/database.php";

$id = (int) ($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: ../payments/index.php?error=payment_not_found");
    exit();
}

$stmt = $conn->prepare("
    SELECT
        p.id,
        p.reference_no,
        p.payment_type,
        p.amount,
        p.payment_date,
        p.payment_method,
        p.status,
        s.student_id,
        CONCAT_WS(' ', s.first_name, s.middle_name, s.last_name) AS fullname
    FROM payments p
    LEFT JOIN students s ON p.student_id = s.id
    WHERE p.id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Receipt not found.");
}

$receipt = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Receipt - <?= htmlspecialchars($receipt["reference_no"]) ?></title>

    <style>
        @page {
            size: 8.5in 5.5in landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: white;
            font-size: 13px;
        }

        .receipt {
            width: 7.2in;
            height: 3.8in;
            margin: 0.15in;
            padding: 0.25in 0.3in;
            border: 1px solid #000;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .header h1 {
            margin: 0;
            font-size: 23px;
        }

        .header p {
            margin: 4px 0 0;
            font-size: 12px;
        }

        .line {
            border-top: 1px solid #000;
            margin: 10px 0 12px;
        }

        .info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 30px;
        }

        .row {
            display: flex;
            margin: 7px 0;
            font-size: 12px;
            line-height: 1.4;
        }

        .label {
            width: 105px;
            font-weight: bold;
        }

        .value {
            flex: 1;
        }

        .amount-box {
            margin-top: 15px;
            border-top: 1px solid #000;
            padding-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-label {
            font-weight: bold;
            font-size: 13px;
        }

        .amount {
            font-size: 21px;
            font-weight: bold;
        }

        .status {
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 14px;
            font-size: 9px;
            line-height: 1.4;
        }

        .logo {
            height: 80px;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .receipt {
                width: 9.0in;
                height: 3.8in;
                margin: 0.15in;
                padding: 0.25in 0.3in;
                border: 1px solid #000;
            }

        }
    </style>
</head>

<body>

    <div class="receipt">

        <div class="header">
            <img class="logo" src="../image/snnmmcs.png" alt="LOGO">
        </div>

        <div class="line"></div>

        <div class="info">

            <div>
                <div class="row">
                    <div class="label">Reference No.:</div>
                    <div class="value">
                        <?= htmlspecialchars($receipt["reference_no"]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="label">Student ID:</div>
                    <div class="value">
                        <?= htmlspecialchars($receipt["student_id"] ?? "") ?>
                    </div>
                </div>

                <div class="row">
                    <div class="label">Student Name:</div>
                    <div class="value">
                        <?= htmlspecialchars($receipt["fullname"] ?? "Unknown Student") ?>
                    </div>
                </div>

                <div class="row">
                    <div class="label">Payment Type:</div>
                    <div class="value">
                        <?= htmlspecialchars($receipt["payment_type"]) ?>
                    </div>
                </div>

            </div>

            <div>

                <div class="row">
                    <div class="label">Payment Method:</div>
                    <div class="value">
                        <?= htmlspecialchars($receipt["payment_method"]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="label">Payment Date:</div>
                    <div class="value">
                        <?= htmlspecialchars($receipt["payment_date"]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="label">Status:</div>
                    <div class="value status">
                        <?= htmlspecialchars($receipt["status"]) ?>
                    </div>
                </div>

            </div>

        </div>

        <div class="amount-box">

            <div class="total-label">
                TOTAL AMOUNT:
            </div>

            <div class="amount">
                ₱<?= number_format((float) $receipt["amount"], 2) ?>
            </div>

        </div>

        <div class="footer">
            Thank you for your payment.<br>
            Online Resibo © 2026
        </div>

    </div>

    <script>
        window.onload = function () {
            window.print();
        };

        window.onafterprint = function () {
            window.close();
        };
    </script>

</body>

</html>