<div class="container-fluid">
    <div class="row align-items-center">
        <div class="col d-flex align-items-center">
            <!-- Кнопка меню -->
            <button id="menu-toggle" class="btn btn-outline-light btn-sm me-2">
                <i class="bi bi-list"></i>
            </button>

            <h1 class="h5 mb-0 text-white fw-light d-none d-sm-block">
                <i class="bi bi-robot me-2"></i> AI Chat
            </h1>
            <h1 class="h6 mb-0 text-white fw-light d-block d-sm-none">
                <i class="bi bi-robot me-1"></i> AI Chat
            </h1>
        </div>
        <div class="col-auto">
            @auth
                <div class="d-flex align-items-center gap-2 d-none d-md-flex">
                    <!-- На десктопе показываем имя пользователя -->
                    <span class="text-white-50 fs-6">
                        <span class="text-white fw-medium">{{ Auth::user()->name }}</span>
                    </span>

                    <div class="vr bg-secondary" style="height: 20px;"></div>

                    <!-- На десктопе оставляем кнопку выхода в хедере -->
                    <form action="{{ route('logout') }}" method="POST" class="d-inline m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm border-0">
                            <i class="bi bi-box-arrow-right me-1"></i>Выйти
                        </button>
                    </form>

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('platform.users.list') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-speedometer2 me-1"></i>Админка
                        </a>
                    @endif
                </div>

                <!-- На мобильных показываем только иконку пользователя -->
                <div class="d-flex align-items-center gap-1 d-md-none">
                    <span class="text-white-50 small">
                        <i class="bi bi-person-circle"></i>
                    </span>
                </div>
            @else
                <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal" id="loginButton">
                    <i class="bi bi-box-arrow-in-right me-1"></i>
                    <span class="d-none d-sm-inline">Вход</span>
                </button>
            @endauth
        </div>
    </div>
</div>

<!-- Модальное окно авторизации -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-white fs-5" id="loginModalLabel">
                    <i class="bi bi-key me-2"></i>Авторизация
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="closeLoginModal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('login') }}" method="post" class="needs-validation" novalidate id="loginForm">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="form-label text-white-50 mb-2">Логин</label>
                        <div class="input-group">
                            <span class="input-group-text bg-secondary border-secondary text-white">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" name="name" id="name" class="form-control bg-dark border-secondary text-white"
                                   placeholder="Введите ваш логин" required autocomplete="username">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label text-white-50 mb-2">Пароль</label>
                        <div class="input-group">
                            <span class="input-group-text bg-secondary border-secondary text-white">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" name="password" id="password" class="form-control bg-dark border-secondary text-white"
                                   placeholder="Введите ваш пароль" required autocomplete="current-password">
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg" id="loginSubmit">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Войти в систему
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
