<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify your email - Camagru</title>
</head>
<body style="margin:0; padding:0; background-color:#f2fcfa; font-family:Arial, sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td align="center" style="padding:40px 16px;">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0"
               style="max-width:600px; width:100%; background:#f9fafa; border-radius:8px; overflow:hidden;">

          <!-- Header -->
          <tr>
            <td align="center" style="padding:28px 40px;">
              <img 
                src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>"
                alt="Camagru"
                width="120"
                style="display:block;" />
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:40px;">
              <h1 style="margin:0 0 16px; font-size:22px; color:#202121;">Verify your email address</h1>
              <p style="margin:0 0 28px; font-size:15px; color:#5f6161; line-height:1.6;">
                Thank you for signing up! Click the button below to verify your email address and activate your account.
                This link will expire in <strong>1 hour</strong>.
              </p>

              <!-- CTA Button -->
              <table role="presentation" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="border-radius:6px; background-color:#009689;">
                    <a href="<?= htmlspecialchars($verificationLink, ENT_QUOTES, 'UTF-8') ?>"
                       style="display:inline-block; padding:14px 32px; font-size:15px; font-weight:bold;
                              color:#f9fafa; text-decoration:none;">
                      Verify Email Address
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:32px 0 0; font-size:13px; color:#a7abab; line-height:1.6;">
                If the button doesn't work, copy and paste this link into your browser:<br>
                <a href="<?= htmlspecialchars($verificationLink, ENT_QUOTES, 'UTF-8') ?>"
                   style="color:#009689; word-break:break-all;">
                  <?= htmlspecialchars($verificationLink, ENT_QUOTES, 'UTF-8') ?>
                </a>
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:24px 40px; border-top:1px solid #f2fcfa;">
              <p style="margin:0; font-size:12px; color:#a7abab; line-height:1.6;">
                If you did not create a Camagru account, you can safely ignore this email.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
