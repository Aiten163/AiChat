<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <script src="bootstrap/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href={{ asset("css/style.css") }}>
</head>
<body>
<header class="w-100 p-3 bg-dark border-bottom border-secondary fixed-top">
    @include('header')
</header>

@include('menu')

@auth
    <div id='container' class="container-fluid">
        <div id='history-container' class="messages-container">
            <!-- История сообщений -->
        </div>

        <div id='input-area'>
            <div id='input-wrapper'>
                <div id='input-model'>
                    <label>
                        <select name="model">
                            @if(!$neurals->isNotEmpty())
                                <option>Нейросети не загружены</option>
                            @endif

                            @foreach($neurals as $neural)
                                <option value="{{$neural['name']}}">{{$neural['show_name']}}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div id="text-request">
                    <textarea
                        placeholder="Введите ваш запрос..."
                        rows="1"
                    ></textarea>
                </div>

                <div id="button-send">
                    <button>
                        <img src="{{ asset('images/send.svg') }}" alt="Отправить">
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script type="module" src="{{ asset('js/app.js') }}"></script>

@endauth
@auth
    <script>
        // Отладочная информация
        console.log('Current chats from server:', @json($chats));
        console.log('Current neurals from server:', @json($neurals));
        console.log('Current URL:', window.location.href);
        console.log('Current path:', window.location.pathname);
    </script>
@endauth
</body>
</html>
