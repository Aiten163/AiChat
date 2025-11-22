<div>
    <form method="post" class="d-flex">
        @if(isset($email['login']))
            Текущий email: {{$email['login']}}
        @else
            Текущий email: отсутствует
        @endif
        <div>
            Email: <input type="email" name="emailLogin" class="form-text">
        </div>
        <div>
            Пароль: <input type="password" name="emailPassword">
        </div>
        <div>
            <div id="title" class="fw-bold">
                Настройка для сообщений с нарушением информационной безопасности
            </div>
            <div>
                Тема сообщения: <input type="text" name="messageTheme">
            </div>

            <div>
                Приветствие сообщения: <input type="text" name="messageGreeting">
            </div>

            <div>
                Текст сообщения: <textarea  name="messageText"> </textarea>
            </div>
            </div>

    </form>
</div>
