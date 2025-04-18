<?php

use App\Models\Comment;
use App\Actions\Comments\AttachFiles;
use App\Actions\Comments\CreateComment;
use Illuminate\Support\Facades\Storage;

use function Livewire\Volt\{computed, rules, state, usesFileUploads, usesPagination};

usesFileUploads();
usesPagination();

state([
    'comment',
    'agent', 
    'files' => [],
]);

rules(['comment' => 'required']);

$comments = computed(fn () => $this->agent->comments()->with('user')->latest()->simplePaginate(5));


$postComment = function () {
    $this->validate();

    $comment = app(CreateComment::class)->handle(body: $this->comment, agentId: $this->agent->id);
    app(AttachFiles::class)->handle(comment: $comment, files: $this->files);
    
    $this->reset('files', 'comment');
};

$deleteComment = fn ($comment) => Comment::find($comment)->delete(); 

$downloadAttachment = fn ($name, $commentId) => Storage::download("comments/{$commentId}/{$name}");

?>
<div>
    <div class="relative mb-4">
        <div class="flex items-center gap-4">
            <flux:textarea wire:model="comment" rows="4" resize="none" placeholder="Leave a comment..." class="pr-28 pl-14 pb-14"/>
        </div>

        <div class="absolute flex items-center gap-4 right-2 bottom-2">
            @if($files)
                <flux:link class="text-purple-400 text-xs"> {{ count($files) }} attachment(s)</flux:link>
            @endif
            <label>
                <flux:icon.paper-clip variant="mini" class="hover:cursor-pointer"/>
                <flux:input wire:model="files" type="file" class="hidden" multiple/>
            </label>
            <flux:button wire:click="postComment" size="sm" variant="primary">Post</flux:button>
        </div>

        <div class="absolute left-2 top-2">
            <flux:avatar color="auto" name="{{ auth()->user()->name }}" />
        </div>
    </div>

        @foreach($this->comments as $comment)
        <div class="flex gap-x-3">
            <div class="relative last:after:hidden after:absolute after:top-7 after:bottom-0 after:start-3.5 after:w-px after:-translate-x-[0.5px] after:bg-gray-200">
            <div class="relative z-10 size-7 flex justify-center items-center">
                <div class="size-2 rounded-full bg-gray-400"></div>
            </div>
            
            </div>
            <div class="grow pt-0.5 pb-8">
                <div class="flex items-center gap-2">
                    <flux:avatar color="auto" name="{{ auth()->user()->name }}" size="xs"/>
                    <flux:heading class="flex items-center gap-1">
                        {{ $comment->user->name }} 
                        <flux:text class="text-xs">&#8226; 
                            {{ $comment->created_at->diffForHumans() }}
                        </flux:text>
                    </flux:heading>
                </div>
                <flux:text class="mt-4">
                {!!  nl2br($comment->body) !!}
                </flux:text>

                @if ($comment->attachments)
                <div class="flex flex-wrap col-span-full mt-2 gap-x-2">
                    @forelse($comment?->attachments as $attachment)
                        <flux:button-or-link
                            wire:click="downloadAttachment('{{$attachment}}', '{{$comment->id}}')"
                            class="inline-flex items-center gap-1 text-sm hover:underline cursor-pointer text-purple-400">
                            <flux:icon.paper-clip variant="outline" class="size-3.5"/>

                            <p>{{$attachment}}</p>
                        </flux:button-or-link>
                    @empty
                    @endforelse
                </div>
                @endif

            </div>
        </div>
        @endforeach

        <flux:pagination :paginator="$this->comments" class="border-none"/>

</div>

