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
        <button class="btn btn-outline-light btn-sm w-100" id="new-chat-btn">
            <i class="bi bi-plus-circle me-1"></i> Новый чат
        </button>
    </div>

    <div class="sidebar-header border-bottom border-secondary m-3">
        <button class="btn btn-outline-light btn-danger btn-sm w-100" id="report-btn" data-bs-toggle="modal" data-bs-target="#reportModal">
            <i class="bi bi-wrench me-1"></i> Сообщить о проблеме
        </button>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true" style="z-index: 10000">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="reportModalLabel">
                        <i class="bi bi-headset me-2"></i>Техподдержка
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="supportForm">
                        <div class="mb-3 position-relative">
                            <label for="messageText" class="form-label">Сообщение *</label>
                            <textarea class="form-control bg-dark text-light border-secondary" id="messageText" rows="4" placeholder="Опишите вашу проблему..." required></textarea>

                            <!-- Иконка прикрепления изображения -->
                            <div class="position-absolute" style="bottom: 10px; right: 10px;">
                                <input type="file" class="d-none" id="imageInput" accept="image/*">
                                <button type="button" class="btn btn-sm btn-outline-light border-0" id="attachImageIcon" title="Прикрепить изображение">
                                    <i class="bi bi-image"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Контейнер для превью изображения -->
                        <div id="imagePreviewContainer"></div>
                    </form>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" id="sendSupportBtn">
                        <i class="bi bi-send me-2"></i>Отправить
                    </button>
                </div>
            </div>
        </div>
    </div>

    <ul class="chat-list text-white" id="chat-list">
        @foreach($chats as $chat)
            <li class="chat-item @if($loop->first) active @endif" data-chat-id="{{ $chat['id'] }}">
                <div class="chat-name-container">
                    <span class="chat-name">{{ $chat['name'] }}</span>
                    <input type="text" class="chat-name-edit" value="{{ $chat['name'] }}" style="display: none;">
                </div>
                <div class="chat-time">
                    {{ \Carbon\Carbon::parse($chat['lastMessage'])->diffForHumans() }}
                </div>
                <div class="chat-actions">
                    <button class="chat-action-btn rename" title="Переименовать чат" data-chat-id="{{ $chat['id'] }}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="chat-action-btn delete" title="Удалить чат" data-chat-id="{{ $chat['id'] }}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </li>
        @endforeach
    </ul>
</div>

<!-- Оверлей для закрытия меню на мобильных -->
<div id="sidebar-overlay" class="sidebar-overlay"></div>
