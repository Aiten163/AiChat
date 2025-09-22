<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <script src="bootstrap/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href={{ asset("css/style.css") }}>
</head>
<body>
<header class="w-100 p-3 bg-dark border-bottom border-secondary">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h4 mb-0 text-white fw-light">
                    <i class="bi bi-robot me-2"></i>Faberlic AI
                </h1>
            </div>
            <div class="col-auto">
                @auth
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-white-50 fs-6"><span class="text-white fw-medium">{{ Auth::user()->name }}</span></span>

                        <div class="vr bg-secondary" style="height: 24px;"></div>

                        <form action="{{ route('logout') }}" method="POST" class="d-inline m-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm border-0">
                                <i class="bi bi-box-arrow-right me-1"></i>Выйти
                            </button>
                        </form>

                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('platform.main') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-speedometer2 me-1"></i>Админка
                            </a>
                        @endif
                    </div>
                @else
                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Вход
                    </button>
                @endauth

                <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-dark border border-secondary">
                            <div class="modal-header border-secondary">
                                <h5 class="modal-title text-white fs-5" id="loginModalLabel">
                                    <i class="bi bi-key me-2"></i>Авторизация
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('login') }}" method="post" class="needs-validation" novalidate>
                                    @csrf

                                    <div class="mb-4">
                                        <label for="name" class="form-label text-white-50 mb-2">Логин</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-secondary border-secondary text-white">
                                                <i class="bi bi-person"></i>
                                            </span>
                                            <input type="text" name="name" id="name" class="form-control bg-dark border-secondary text-white"
                                                   placeholder="Введите ваш логин" required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="password" class="form-label text-white-50 mb-2">Пароль</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-secondary border-secondary text-white">
                                                <i class="bi bi-lock"></i>
                                            </span>
                                            <input type="password" name="password" id="password" class="form-control bg-dark border-secondary text-white"
                                                   placeholder="Введите ваш пароль" required>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>Войти в систему
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
    <div id='container' class="container-fluid">
        <div id='history-container' class="messages-container">

        </div>

        <div id='input-area'>
            <div id='input-wrapper'>
                <div id='input-model'>
                    <label>
                        <select name="model">
                            <option value="chatgpt" class="">ChatGPT</option>
                            <option value="copilot">Copilot</option>
                            <option value="deepseek">DeepSeek</option>
                        </select>
                    </label>
                </div>

                <div id="text-request">
                            <textarea
                                    placeholder="Введите ваш запрос..."
                                    rows="1"
                                    oninput="autoResize(this)"
                            ></textarea>
                </div>

                <div id="button-send">
                    <button onclick="sendMessage()">
                        <img src="{{ asset('images/send.svg') }}" alt="Отправить">
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/script.js') }}" > </script>
</body>
</html>
