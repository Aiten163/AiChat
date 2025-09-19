<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <script src="bootstrap/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <link rel="stylesheet" href={{ asset("css/style.css") }}>
</head>
<body>
    <header class="w-100 p-3 text-bg-dark">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="h4 mb-0">Faberlic AI</h1>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn text-sm-end text-white border-1" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        Вход
                    </button>
                    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Вход</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3 align-items-center">
                                        <form action="{{route('login')}}">
                                            <div class="col-auto">
                                                <label for="password" class="col-form-label">Пароль</label>
                                            </div>
                                            <div class="col-auto">
                                                <input type="password" id="password" class="form-control" aria-describedby="passwordHelpInline">
                                            </div>
                                            <div class="col-auto">
                                                <label for="login" class="col-form-label">Логин</label>
                                            </div>
                                            <div class="col-auto">
                                                <input type="text" id="login" class="form-control" aria-describedby="passwordHelpInline">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary">Save changes</button>
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
