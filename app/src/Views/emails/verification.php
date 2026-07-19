<?php
    $message = $message ?? '';
    $verificationLink = $verificationLink ?? '';
?>
<tr>
  <td style="padding:40px;">
    <h1 style="margin:0 0 16px; font-size:22px; color:#202121;">Verify your email address</h1>
    <p style="margin:0 0 28px; font-size:15px; color:#5f6161; line-height:1.6;">
      <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
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
