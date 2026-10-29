# Payment receipt emails

Paid receipts are emailed to the student's saved email address when a payment is first recorded as **Paid** or changes from **Pending/Cancelled** to **Paid**. Editing an already-paid payment does not send another email. Email sending runs in the background so the payment form does not wait for Gmail; the Payments page confirms that the email was queued, not that Gmail has delivered it. The email includes the amount paid and a printable PDF receipt attachment.

## Gmail setup

1. Install Node.js and npm on the machine running Apache/PHP.
2. Run `npm install` from the project root.
3. Apply `migrations/student_email.sql` to the `online_resibo` database.
4. Copy `.env.example` to `.env` and set:
   - `GMAIL_USER` to the Gmail account that will send receipts.
   - `GMAIL_APP_PASSWORD` to a Google App Password for that account. Do not use the account's normal password.
   - `MAIL_FROM_NAME` to the sender name displayed in the email.
   - `NODE_BINARY` to the Node executable if Apache cannot find `node` on its PATH (for example, `C:\Program Files\nodejs\node.exe`).
5. Enable 2-Step Verification on the Gmail account and create an App Password in the Google Account security settings.
6. Add a valid email address to the student's record. Email delivery is skipped if it is missing or invalid.

The local `.env` file is ignored by Git and blocked from direct Apache requests. If the payment is saved but the mailer cannot be started, the Payments page reports that separately and the failure details are written to the PHP error log. Errors occurring after the background mailer starts are written to `online-resibo-email.log` in the Windows temporary directory.