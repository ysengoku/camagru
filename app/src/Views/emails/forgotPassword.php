<?php
    $expire  = 'This link will expire in ' . Application::VERIF_TOKEN_TTL_MINUTES . ' minutes';
    $resetLink = $resetLink ?? '';
?>
<tr>
  <td style="padding:40px;">
    <h1 style="margin:0 0 16px; font-size:22px; color:#202121;">Reset your password</h1>
    <p style="margin:0 0 28px; font-size:15px; color:#5f6161; line-height:1.6;">
      You have requested to reset your password. Click the button below to proceed.
      <?= htmlspecialchars($expire, ENT_QUOTES, 'UTF-8') ?>
    </p>

    <!-- CTA Button -->
    <table role="presentation" cellpadding="0" cellspacing="0">
      <tr>
        <td align="center" style="border-radius:6px; background-color:#009689;">
          <a href="<?= htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') ?>"
             style="display:inline-block; padding:14px 32px; font-size:15px; font-weight:bold;
                    color:#f9fafa; text-decoration:none;">
            Reset Password
          </a>
        </td>
      </tr>
    </table>

    <p style="margin:32px 0 0; font-size:13px; color:#a7abab; line-height:1.6;">
      If the button doesn't work, copy and paste this link into your browser:<br>
      <a href="<?= htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') ?>"
         style="color:#009689; word-break:break-all;">
        <?= htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') ?>
      </a>
    </p>
  </td>
</tr>
