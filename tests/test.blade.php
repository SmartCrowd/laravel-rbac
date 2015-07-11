@allowed('article.edit', ['article' => ['id' => 1]])
@else
@endallowed

@allowedArticleEdit(['article' => ['id' => 1]])
@endallowed

@allowedArticlePublic
@endallowed

@allowedWrongPermissionName
@endallowed