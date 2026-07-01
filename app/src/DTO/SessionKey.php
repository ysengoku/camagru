<?php

enum SessionKey: string {
    case PendingEmail = 'pending_email';
    case ResendEmailAction = 'resend_email_action'; // 'verify' or 'reset_password'
    case LastEmailSentTime = 'last_email_sent_time'; // timestamp of the last email sent
}
