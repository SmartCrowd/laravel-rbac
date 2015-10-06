<?php if (\Rbac::checkAccess(\Auth::user(), 'article.edit', ['article' => ['id' => 1]])): ?>
<?php else: ?>
<?php endif; ?>

<?php if (\Rbac::checkAccess(\Auth::user(), 'article.edit', ['article' => ['id' => 1]])): ?>
<?php endif; ?>

<?php if (\Rbac::checkAccess(\Auth::user(), 'article.public')): ?>
<?php endif; ?>

@allowedWrongPermissionName
<?php endif; ?>