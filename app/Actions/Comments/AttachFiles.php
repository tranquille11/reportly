<?php

namespace App\Actions\Comments;

use App\Models\Comment;

class AttachFiles
{
    public function handle(Comment $comment, ?array $files): void
    {
        if (! $files) {
            return;
        }

        foreach ($files as $file) {
            $path = "comments/{$comment->id}";
            $name = $file->getClientOriginalName();

            $file->storeAs($path, $name);
            $attachments[] = $name;
        }

        $comment->update(['attachments' => $attachments]);

    }
}
