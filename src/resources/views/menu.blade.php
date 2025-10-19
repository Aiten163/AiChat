<!-- Выдвижное меню -->
<div id="sidebar">
    <div class="sidebar-header">
        <h5 class="text-white mb-3">Чаты</h5>
        <button class="btn btn-primary btn-sm w-100" onclick="createNewChat()">
            <i class="bi bi-plus-circle me-1"></i> Новый чат
        </button>
    </div>

    <ul class="chat-list" id="chat-list">
        @foreach($chats as $chat)
            <li class="chat-item @if($loop->first) active @endif" data-chat-id="{{ $chat->id }}">
                <div class="chat-name">{{ $chat->name }}</div>
                <div class="chat-preview">{{ Str::limit($chat->last_message, 50) }}</div>
            </li>
        @endforeach
    </ul>
</div>

<!-- Оверлей для закрытия меню на мобильных -->
<div id="sidebar-overlay" class="sidebar-overlay"></div>
