<!-- Выдвижное меню -->
<div id="sidebar" class="bg-dark border-end border-secondary">
    <!-- Информация пользователя (только для авторизованных) -->
    @auth
        <div class="user-info-sidebar">
            <div class="user-name">
                <i class="bi bi-person-circle me-2"></i>{{ Auth::user()->name }}
            </div>
            <div class="user-actions-sidebar">
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm w-100">
                        <i class="bi bi-box-arrow-right me-1"></i>Выйти
                    </button>
                </form>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('platform.users.list') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-speedometer2 me-1"></i>Админка
                    </a>
                @endif
            </div>
        </div>
    @endauth

    <div class="sidebar-header border-bottom border-secondary m-3">
        <button class="btn btn-outline-light btn-sm w-100" onclick="createNewChat()">
            <i class="bi bi-plus-circle me-1"></i> Новый чат
        </button>
    </div>

    <ul class="chat-list" id="chat-list">
        @foreach($chats as $chat)
            <li class="chat-item @if($loop->first) active @endif" data-chat-id="{{ $chat->id }}">
                <div class="chat-name text-truncate">{{ $chat->name }}</div>
                <div class="chat-preview text-truncate text-small text-muted">
                    {{ Str::limit($chat->last_message, 50) }}
                </div>
            </li>
        @endforeach
    </ul>
</div>

<!-- Оверлей для закрытия меню на мобильных -->
<div id="sidebar-overlay" class="sidebar-overlay"></div>
