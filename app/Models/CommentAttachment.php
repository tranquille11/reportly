<?php

namespace App\Models;

use BeyondCode\Comments\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentAttachment extends Model
{
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}
