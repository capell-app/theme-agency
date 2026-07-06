@php
    use Capell\Comments\Livewire\CommentThreadComponent;
@endphp

@livewire (CommentThreadComponent::class, ['threadKey' => $threadKey])
