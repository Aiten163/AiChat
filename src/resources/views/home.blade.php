<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <script src="bootstrap/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="{{ asset('js/lib/highlight/styles/github-dark.min.css') }}">
    <script src="{{ asset('js/lib/marked/marked.min.js') }}"></script>
    <script src="{{ asset('js/lib/highlight/highlight.min.js') }}"></script>

    <script src="{{ asset('js/lib/highlight/languages/python.min.js') }}"></script>
    <script src="{{ asset('js/lib/highlight/languages/javascript.min.js') }}"></script>
    <script src="{{ asset('js/lib/highlight/languages/php.min.js') }}"></script>
    <script src="{{ asset('js/lib/highlight/languages/java.min.js') }}"></script>
    <script src="{{ asset('js/lib/highlight/languages/cpp.min.js') }}"></script>
    <script src="{{ asset('js/lib/highlight/languages/sql.min.js') }}"></script>
    <script src="{{ asset('js/lib/highlight/languages/bash.min.js') }}"></script>
    <script src="{{ asset('js/lib/highlight/languages/json.min.js') }}"></script>
    <script src="{{ asset('js/lib/highlight/languages/xml.min.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    <link rel="stylesheet" href="{{ asset('css/input.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
</head>
<body class="bg-dark-custom">
<header class="w-100 bg-dark border-bottom border-secondary fixed-top py-2">
    @include('header')
</header>

@include('menu')

@auth
    <div id='container' class="container-fluid px-0">
        <div id='history-container' class="messages-container">
        </div>

        <div id='input-area' class="bg-dark-custom border-top border-secondary">
            <div class="container-fluid">
                <div id='input-wrapper' class="bg-darker rounded-3 p-2 p-md-3">
                    <div class="input-top-row">
                        <div id="text-request" class="flex-grow-1 w-100">
                            <textarea
                                name="promt"
                                class="w-100 border-0 bg-transparent text-white"
                                placeholder="Введите ваш запрос..."
                                rows="1"
                            ></textarea>
                        </div>
                    </div>

                    <div class="input-bottom-row">
                        <div id='input-model' class="flex-shrink-0">
                            <label class="mb-0">
                                <select name="model" class="form-select form-select-sm border-secondary bg-dark text-white">
                                    @if(!$neurals->isNotEmpty())
                                        <option>Нейросети не загружены</option>
                                    @endif
                                    @foreach($neurals as $neural)
                                        <option value="{{$neural['name']}}">{{$neural['show_name']}}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>

                        <div id="button-send" class="flex-shrink-0">
                            <button class="btn btn-primary p-2 d-flex align-items-center">
                                <img src="{{ asset('images/send.svg') }}" alt="Отправить" width="20" height="20">
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endauth

@auth
    <script type="module" src="{{ asset('js/app.js') }}"></script>
@else
    <!-- Emergency fix script -->
    <script>
        function emergencyModalFix() {
            console.log('Running emergency modal fix...');
            const backdrops = document.querySelectorAll('.modal-backdrop');
            if (backdrops.length > 1) {
                for (let i = 1; i < backdrops.length; i++) {
                    backdrops[i].remove();
                }
            }
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (!modal.classList.contains('show')) {
                    modal.style.display = 'none';
                }
            });
            if (!document.querySelector('.modal.show')) {
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
        }
        document.addEventListener('DOMContentLoaded', emergencyModalFix);
        setInterval(emergencyModalFix, 3000);
        document.addEventListener('click', emergencyModalFix);
    </script>
@endauth
</body>
</html>
