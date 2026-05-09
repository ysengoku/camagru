<?php if (!isSet($avatarPath) ): ?>
    <span class="letter-avatar avatar-<?php echo htmlspecialchars("$size"); ?>">
        <?php echo htmlspecialchars(strtoupper(substr($displayName, 0, 1))); ?>
    </span>
<?php else: ?>  
    <img
      class="avatar avatar-<?php echo htmlspecialchars("$size"); ?>"
      src="<?php echo htmlspecialchars($avatarPath); ?>" alt="User Avatar"
    >
<?php endif; ?>
