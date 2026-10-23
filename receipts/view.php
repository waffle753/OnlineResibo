<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/database.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id <= 0) {
    header("Location: ../payments/index.php?error=payment_not_found");
    exit();
}

$sql = "
    SELECT
        p.id,
        p.reference_no,
        p.student_id,
        p.payment_type,
        p.amount,
        p.payment_date,
        p.payment_method,
        p.status,
        p.created_at,
        s.student_id AS student_number,
        CONCAT_WS(' ', s.first_name, s.middle_name, s.last_name) AS fullname
    FROM payments p
    LEFT JOIN students s
        ON p.student_id = s.id
    WHERE p.id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header("Location: ../payments/index.php?error=payment_not_found");
    exit();
}

$payment = $result->fetch_assoc();

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Receipt - <?= htmlspecialchars($payment["reference_no"]) ?></title>

    <link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../assets/css/table-gridlines.css">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f4f6f9;
        }

        .receipt-container {
            max-width: 800px;
            margin: 40px auto;
        }

        .receipt {
            background: #fff;
            border: 1px solid #ddd;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,.1);
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .receipt-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }

        .receipt-header p {
            margin: 5px 0;
            color: #666;
        }

        .receipt-title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 25px;
        }

        .info-table td {
            padding: 8px 0;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 35%;
        }

        .amount-box {
            border: 2px solid #222;
            padding: 15px;
            text-align: center;
            margin-top: 20px;
        }

        .amount-box .label {
            display: block;
            width: 100%;
            font-size: 15px;
        }

        .amount {
            font-size: 30px;
            font-weight: bold;
            margin-top: 5px;
        }

        .status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 5px;
            font-weight: bold;
        }

        .status-paid {
            background: #198754;
            color: white;
        }

        .status-pending {
            background: #ffc107;
            color: #000;
        }

        .status-cancelled {
            background: #dc3545;
            color: white;
        }

        .signature-area {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }

        .signature {
            width: 40%;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #222;
            padding-top: 8px;
        }

        .buttons {
            margin-top: 20px;
            text-align: center;
        }

        @media print {
            body {
                background: white;
            }

            .receipt-container {
                margin: 0;
                max-width: 100%;
            }

            .receipt {
                border: none;
                box-shadow: none;
                padding: 20px;
            }

            .buttons {
                display: none;
            }
        }
    </style>
</head>

<body>

<div class="receipt-container">

    <div class="receipt">

        <!-- HEADER -->
        <div class="receipt-header">

            <h1>🏫 ONLINE RESIBO</h1>

            <p>Payment Management System</p>

            <p>Official Payment Receipt</p>

        </div>

        <!-- RECEIPT TITLE -->
        <div class="receipt-title">
            PAYMENT RECEIPT
        </div>

        <!-- PAYMENT INFORMATION -->
        <table class="info-table">

            <tr>
                <td class="label">
                    Receipt Reference:
                </td>

                <td>
                    <strong>
                        <?= htmlspecialchars($payment["reference_no"]) ?>
                    </strong>
                </td>
            </tr>

            <tr>
                <td class="label">
                    Student ID:
                </td>

                <td>
                    <?= htmlspecialchars($payment["student_number"] ?? "N/A") ?>
                </td>
            </tr>

            <tr>
                <td class="label">
                    Student Name:
                </td>

                <td>
                    <strong>
                        <?= htmlspecialchars($payment["fullname"] ?? "Unknown Student") ?>
                    </strong>
                </td>
            </tr>

            <tr>
                <td class="label">
                    Payment Type:
                </td>

                <td>
                    <?= htmlspecialchars($payment["payment_type"]) ?>
                </td>
            </tr>

            <tr>
                <td class="label">
                    Payment Date:
                </td>

                <td>
                    <?= htmlspecialchars($payment["payment_date"]) ?>
                </td>
            </tr>

            <tr>
                <td class="label">
                    Payment Method:
                </td>

                <td>
                    <?= htmlspecialchars($payment["payment_method"]) ?>
                </td>
            </tr>

            <tr>
                <td class="label">
                    Status:
                </td>

                <td>

                    <?php
                    $status = strtolower($payment["status"]);

                    if ($status === "paid") {
                        $statusClass = "status-paid";
                    } elseif ($status === "pending") {
                        $statusClass = "status-pending";
                    } elseif ($status === "cancelled") {
                        $statusClass = "status-cancelled";
                    } else {
                        $statusClass = "status-pending";
                    }
                    ?>

                    <span class="status <?= $statusClass ?>">
                        <?= htmlspecialchars($payment["status"]) ?>
                    </span>

                </td>
            </tr>

        </table>

        <!-- AMOUNT -->
        <div class="amount-box">

            <span class="label">
                TOTAL AMOUNT PAID
            </span>

            <div class="amount">
                ₱<?= number_format((float) $payment["amount"], 2) ?>
            </div>

        </div>

        <!-- SIGNATURES -->
        <div class="signature-area">

            <div class="signature">

                <div class="signature-line">
                    Student / Payer
                </div>

            </div>

            <div class="signature">

                <div class="signature-line">
                    Authorized Personnel
                </div>

            </div>

        </div>

        <!-- BUTTONS -->
        <div class="buttons">

            <a href="../payments/index.php"
               class="btn btn-secondary">

                <i class="bi bi-arrow-left"></i>
                Back to Payments

            </a>

            <a href="print.php?id=<?= (int) $payment["id"] ?>"
               class="btn btn-primary">

                <i class="bi bi-printer"></i>
                Print Receipt

            </a>

        </div>

    </div>

</div>

</body>
</html>