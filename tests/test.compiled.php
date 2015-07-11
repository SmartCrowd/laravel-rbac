<?php if (app('rbac')->checkAccess(\Auth::user(), 'article.edit', ['article' => ['id' => 1]])): ?>
<?php else: ?>
<?php endif; ?>

<?php if (app('rbac')->checkAccess(\Auth::user(), 'article.edit', ['article' => ['id' => 1]])): ?>
<?php endif; ?>

<?php if (app('rbac')->checkAccess(\Auth::user(), 'article.public')): ?>
<?php endif; ?>

@allowedWrongPermissionName
<?php endif; ?>