# Email

The app sends transactional email for three cases: signup verification, password reset, and new-comment notifications.   
This document describes how email delivery is implemented and how it is configured for development and production.

## Implementation

Email is sent through `EmailService` (`app/src/Services/EmailService.php`) that communicates directly with an SMTP server over a raw socket, using `fsockopen()` and `STARTTLS` via `stream_socket_enable_crypto()`. No external mail library is used.

`send()` walks through the standard SMTP conversation by hand: it opens the socket, waits for the server's welcome response, issues `EHLO`, upgrades the connection to TLS with `STARTTLS`, authenticates with `AUTH LOGIN` using the configured credentials, then sends the message with `MAIL FROM`, `RCPT TO`, and `DATA`.

Three helper functions in `app/src/helper/mailer.php` build and send the actual messages:

- `sendVerificationLinkEmail()`: signup and email-change verification
- `sendPasswordResetEmail()`: forgot-password flow
- `sendNewCommentNotificationEmail()`: notifies a post's author of a new comment

Each function renders an HTML template through `renderEmailTemplate()` (`app/src/helper/renderer.php`). Rendering happens in two steps: the template-specific body (`app/src/Views/emails/*.php`) is rendered first with `extract($vars)` and output buffering, then that rendered content is passed into the shared layout (`app/src/Views/emails/layout.php`), which wraps it with the logo header and footer common to every email.

## Environments

Camagru relies on [Mailtrap](https://mailtrap.io) in both environments, but through two different products:

| Environment | Mailtrap product | Host | Behavior |
|---|---|---|---|
| Dev | Email Testing (sandbox) | `sandbox.smtp.mailtrap.io:2525` | Emails never leave Mailtrap. They are captured in a sandbox inbox for inspection, so they can be triggered freely during development. |
| Production | Email Sending (live) | `live.smtp.mailtrap.io:587` | Emails are delivered to real recipients. |
<br />      

Credentials are read from `.env.dev` in development and `.env` in production, via `EmailService`'s constructor.

## Testing

PHPUnit tests never send real email. `app/tests/bootstrap.php` sets `MAIL_DISABLED=true`, which `EmailService` checks before doing any socket work and before validating SMTP configuration. Both `send()` and the constructor become no-ops when this flag is set.

## Mailtrap Setup

Both environments use the same Mailtrap account but two separate products, each with its own credentials.

### Email Testing using sandbox (development)

No domain and no DNS configuration are required: everything sent to the sandbox host is captured by Mailtrap regardless of the recipient address.

#### In Mailtrap:

Go to `Sandboxes` and select My Sandbox.   
Open the Integration tab and select the plain SMTP credentials.

#### In `.env.dev`:

Add the following variables.
```bash
SMTP_HOST=sandbox.smtp.mailtrap.io
SMTP_PORT=2525
SMTP_USER=<inbox username>
SMTP_PASS=<inbox password>
MAIL_FROM=<your_email_address>
```

Credentials are tied to the Sandbox, so resetting or deleting it invalidates them. `MAIL_FROM` can be any address, since nothing is actually delivered.

Source: [MailTrap documentation - Email Sandbox](https://docs.mailtrap.io/getting-started/email-sandbox)

### Email Sending (production)

Camagru does not use SDK, since EmailService speaks SMTP directly.   
Live sending requires a verified domain. Mailtrap only accepts a `MAIL_FROM` on a domain whose ownership has been proven, and recipients' mail servers use the SPF and DKIM records to decide whether the message is trustworthy rather than spam.    

#### Prerequisites

- A domain you own
- A DNS provider. This project uses Cloudflare, and the domain must already use Cloudflare's nameservers (*.ns.cloudflare.com) whether it was registered with Cloudflare or delegated to it from another registrar.

#### In Mailtrap:

1. Create a new API token with Admin access to your account (`Settings` → `API Tokens`). This token is the value used as `SMTP_PASS` below.  

2. Go to `Domains / Sending Domains` → `Add Domain` and enter the domain.

3. Mailtrap opens the Domain Verification page listing the records to publish. Typically this is two DKIM CNAME records plus TXT records for SPF and DMARC, though the exact set varies by account and plan.

#### In Cloudflare:

The records are added under `DNS` → `Records` → `Add record` in the Cloudflare dashboard.    
A few Cloudflare-specific points matter here:
- Field names differ. Cloudflare calls Mailtrap's Value field Target on CNAME records and Content on TXT records.
- Turn the proxy off. Proxy status defaults to enabled (orange cloud) and must be set to DNS only (grey cloud). A proxied record resolves to Cloudflare's IPs, which breaks the DKIM and verification lookups.
- Check the name preview. Cloudflare treats the Name field as relative to the zone and shows the resulting full record name beneath the field. Confirm it matches what Mailtrap displays before saving.
- Only one SPF record per domain. If Mailtrap gives an SPF record and the zone already has a v=spf1 ... TXT record, merge Mailtrap's include: into the existing one. Two SPF records is a permanent failure.
- Leave TTL on the default.
- If Cloudflare's `Email Security` → `DMARC Management` is enabled, check that it does not already publish a conflicting `_dmarc` record.

Propagation usually takes minutes but can take up to 48 hours.

Then return to Mailtrap. It re-checks automatically every hour, and the button on the domain page forces a check. Each record's status changes from Missing to Verified.

After DNS verification, new domains go through a compliance review. If the domain's compliance status stays at awaiting_questionnaire, open the domain details and look for a Fill in Compliance Form button. Sending stays blocked until it is completed, even though DNS shows as verified.

See also: [MailTrap documentation - Sending Domain Setup](https://docs.mailtrap.io/email-api-smtp/setup/sending-domain)

#### In `.env`:

After verification, open the domain's Integration → SMTP tab and copy the credentials into `.env`:
```bash
SMTP_HOST=live.smtp.mailtrap.io
SMTP_PORT=587
SMTP_USER=api
SMTP_PASS=<mailtrap token>
MAIL_FROM=<email_address@verified_domain>

# Port 587 with STARTTLS matches the handshake implemented in EmailService.
# MAIL_FROM must belong to the verified domain, otherwise Mailtrap rejects the message at submission time.
# Without a domain, Mailtrap's demo sending domain can be used instead, but it only delivers to the account owner's own address. 
```

