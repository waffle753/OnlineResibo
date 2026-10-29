const fs = require("node:fs");
const path = require("node:path");
const dotenv = require("dotenv");
const nodemailer = require("nodemailer");
const PDFDocument = require("pdfkit");

dotenv.config({ path: path.join(__dirname, "..", ".env") });

function readPayment() {
    const jobPath = process.argv[2];
    if (jobPath) {
        return fs.promises.readFile(jobPath, "utf8")
            .then(input => JSON.parse(input))
            .finally(() => fs.promises.unlink(jobPath));
    }

    let input = "";
    process.stdin.setEncoding("utf8");
    process.stdin.on("data", chunk => {
        input += chunk;
    });

    return new Promise((resolve, reject) => {
        process.stdin.on("end", () => {
            try {
                resolve(JSON.parse(input));
            } catch {
                reject(new Error("Invalid payment data supplied to the email server."));
            }
        });
        process.stdin.on("error", reject);
    });
}

function createReceiptPdf(payment) {
    return new Promise((resolve, reject) => {
        const document = new PDFDocument({
            size: "A5",
            layout: "landscape",
            margin: 42
        });
        const chunks = [];

        document.on("data", chunk => chunks.push(chunk));
        document.on("error", reject);
        document.on("end", () => resolve(Buffer.concat(chunks)));

        const pageWidth = document.page.width;
        const logoPath = path.join(__dirname, "..", "image", "snnmmcs.png");
        if (fs.existsSync(logoPath)) {
            document.image(logoPath, (pageWidth - 76) / 2, 14, { fit: [76, 58] });
        }

        document.font("Helvetica-Bold").fontSize(20).text("PAYMENT RECEIPT", 42, 77, {
            align: "center",
            width: pageWidth - 84
        });
        document.font("Helvetica").fontSize(10).text("Online Resibo", 42, 101, {
            align: "center",
            width: pageWidth - 84
        });

        document.moveTo(42, 128).lineTo(pageWidth - 42, 128).stroke("#64748b");

        const leftX = 48;
        const rightX = pageWidth / 2 + 12;
        const labelWidth = 112;
        const rowStart = 145;
        const rowGap = 30;
        const leftDetails = [
            ["Reference No.", payment.reference_no],
            ["Student ID", payment.student_id],
            ["Student Name", payment.student_name],
            ["Payment Type", payment.payment_type]
        ];
        const rightDetails = [
            ["Payment Method", payment.payment_method],
            ["Payment Date", payment.payment_date],
            ["Status", payment.status]
        ];

        function drawDetails(details, x) {
            details.forEach(([label, value], index) => {
                const y = rowStart + index * rowGap;
                document.font("Helvetica-Bold").fontSize(10).text(`${label}:`, x, y, {
                    width: labelWidth
                });
                document.font("Helvetica").text(String(value ?? ""), x + labelWidth, y, {
                    width: pageWidth / 2 - labelWidth - 24
                });
            });
        }

        drawDetails(leftDetails, leftX);
        drawDetails(rightDetails, rightX);

        const amountY = 255;
        document.roundedRect(42, amountY, pageWidth - 84, 58, 7).fill("#eff6ff");
        document.font("Helvetica-Bold").fontSize(12).fillColor("#1e3a8a")
            .text("AMOUNT PAID", 58, amountY + 21);
        document.font("Helvetica-Bold").fontSize(22).fillColor("#1d4ed8")
            .text(`PHP ${Number(payment.amount).toLocaleString("en-PH", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}`, 190, amountY + 15, {
                width: pageWidth - 250,
                align: "right"
            });

        document.fillColor("#000000").font("Helvetica").fontSize(9)
            .text("Thank you for your payment.", 42, 330, {
                align: "center",
                width: pageWidth - 84
            });
        document.end();
    });
}

async function main() {
    const payment = await readPayment();
    const requiredFields = [
        "to",
        "student_name",
        "student_id",
        "reference_no",
        "payment_type",
        "payment_method",
        "payment_date",
        "status"
    ];
    if (
        requiredFields.some(field => typeof payment[field] !== "string" || payment[field].trim() === "") ||
        !Number.isFinite(Number(payment.amount)) ||
        Number(payment.amount) <= 0 ||
        payment.status !== "Paid"
    ) {
        throw new Error("Payment receipt data is incomplete or not marked Paid.");
    }

    if (!process.env.GMAIL_USER || !process.env.GMAIL_APP_PASSWORD) {
        throw new Error("GMAIL_USER and GMAIL_APP_PASSWORD must be configured in .env.");
    }

    const pdf = await createReceiptPdf(payment);
    const formattedAmount = `₱${Number(payment.amount).toLocaleString("en-PH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })}`;
    const safeReference = payment.reference_no.replace(/[^a-zA-Z0-9_-]/g, "_");
    const senderName = process.env.MAIL_FROM_NAME || "Online Resibo";
    const transporter = nodemailer.createTransport({
        service: "gmail",
        auth: {
            user: process.env.GMAIL_USER,
            pass: process.env.GMAIL_APP_PASSWORD.replace(/\s/g, "")
        }
    });

    await transporter.sendMail({
        from: `"${senderName.replace(/["\\]/g, "")}" <${process.env.GMAIL_USER}>`,
        to: payment.to,
        subject: "Payment received - Online Resibo",
        text: `Hello ${payment.student_name},\n\nYou have successfully paid ${formattedAmount} for ${payment.payment_type}.\n\nPayment reference: ${payment.reference_no}\nA printable PDF receipt is attached to this email.\n\nThank you,\n${senderName}`,
        html: `
            <div style="font-family:Arial,sans-serif;color:#1f2937;max-width:600px;margin:0 auto;padding:24px">
                <h2 style="color:#1d4ed8">Payment received successfully</h2>
                <p>Hello ${escapeHtml(payment.student_name)},</p>
                <p>You have successfully paid:</p>
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:18px 22px;margin:20px 0;text-align:center">
                    <div style="font-size:13px;color:#475569">AMOUNT PAID</div>
                    <strong style="display:block;color:#1d4ed8;font-size:30px;margin-top:6px">${formattedAmount}</strong>
                </div>
                <p><strong>Payment type:</strong> ${escapeHtml(payment.payment_type)}<br>
                <strong>Reference:</strong> ${escapeHtml(payment.reference_no)}<br>
                <strong>Payment date:</strong> ${escapeHtml(payment.payment_date)}</p>
                <p>Your printable PDF receipt is attached. You can view or print it for your records.</p>
                <p>Thank you,<br>${escapeHtml(senderName)}</p>
            </div>
        `,
        attachments: [{
            filename: `Receipt-${safeReference}.pdf`,
            content: pdf,
            contentType: "application/pdf"
        }]
    });

    process.stdout.write(JSON.stringify({ sent: true }));
}

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, character => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        "\"": "&quot;",
        "'": "&#39;"
    })[character]);
}

if (require.main === module) {
    if (process.stdin.isTTY && !process.argv[2]) {
        process.stderr.write(
            "Online Resibo email worker is running and waiting for a payment job on stdin. Press Ctrl+C to stop.\n"
        );
    }

    main().catch(error => {
        process.stderr.write(`${error.message}\n`);
        process.stdout.write(JSON.stringify({ sent: false }));
        process.exitCode = 1;
    });
}

module.exports = { createReceiptPdf };
