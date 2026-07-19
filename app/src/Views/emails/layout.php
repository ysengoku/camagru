<?php
    $logoUrl = $logoUrl ?? '';
    $emailTitle = $emailTitle ?? 'Camagru';
    $content = $content ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($emailTitle, ENT_QUOTES, 'UTF-8') ?> - Camagru</title>
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
          <?= $content ?>

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
