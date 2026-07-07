<?php

enum SessionKey: string {
    case UserId = 'user_id';
    case CsrfToken = 'csrf_token';
    case PendingEmail = 'pending_email';
    case ResendEmailAction = 'resend_email_action'; // 'verify' or 'reset_password'
    case LastEmailSentTime = 'last_email_sent_time'; // timestamp of the last email sent
}

enum EmailAction: string {
    case Signup = 'signup';
    case EmailChange = 'email_change';
    case ResetPassword = 'reset_password';
}
