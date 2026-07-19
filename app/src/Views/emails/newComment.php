<?php
    $commenterName = $commenterName ?? '';
    $commentContent = $commentContent ?? '';
    $postLink = $postLink ?? '';
?>
<tr>
  <td style="padding:40px;">
    <h1 style="margin:0 0 16px; font-size:22px; color:#202121;">New comment on your post</h1>
    <p style="margin:0 0 28px; font-size:15px; color:#5f6161; line-height:1.6;">
      <strong><?= htmlspecialchars($commenterName, ENT_QUOTES, 'UTF-8') ?></strong> commented on your post:<br>
      "<?= htmlspecialchars($commentContent, ENT_QUOTES, 'UTF-8') ?>"
    </p>

    <!-- CTA Button -->
    <table role="presentation" cellpadding="0" cellspacing="0">
      <tr>
        <td align="center" style="border-radius:6px; background-color:#009689;">
          <a href="<?= htmlspecialchars($postLink, ENT_QUOTES, 'UTF-8') ?>"
             style="display:inline-block; padding:14px 32px; font-size:15px; font-weight:bold;
                    color:#f9fafa; text-decoration:none;">
            View Comment
          </a>
        </td>
      </tr>
    </table>
  </td>
</tr>
