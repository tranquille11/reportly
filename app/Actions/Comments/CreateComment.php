<?php

namespace App\Actions\Comments;

use App\Models\Comment;
use Illuminate\Container\Attributes\CurrentUser;

class CreateComment
{
    public function __construct(#[CurrentUser] private $user) {}

    public function handle(string $body, int $agentId): Comment
    {
        return $this->user->comments()->create([
            'body' => $body,
            'agent_id' => $agentId,
        ]);
    }
}
