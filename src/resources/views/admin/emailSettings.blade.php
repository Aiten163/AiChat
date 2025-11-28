<form method="POST" action="{{ route('platform.emailSettings.store') }}" id="emailSettingsForm">
    @csrf
    @method('POST')

    <div class="w-50 inline-flex">
        <div>
            Email: <input type="email" autocomplete="off" value="{{ $emailData['login'] ?? '' }}" name="emailLogin" class="form-text w-100">
        </div>
        <div>
            Пароль: <input type="password" class="w-100" autocomplete="new-password" placeholder="{{ isset($emailData['password']) ? '********' : '' }}" name="emailPassword">
        </div>
        <div>
            Имя отправителя: <input type="text" class="w-100" value="{{ $emailData['sender'] ?? '' }}" name="sender">
        </div>
        <div>
            Порт: <input type="number" class="w-100" value="{{ $emailData['port'] ?? '' }}" name="port">
        </div>

        <div style="margin-top: 5%">
            <div class="fw-bold">
                Настройка для сообщений с нарушением информационной безопасности
            </div>
            <div>
                Тема сообщения: <input type="text" class="w-100" value="{{ $emailData['theme'] ?? '' }}" name="messageTheme">
            </div>
            <div>
                Приветствие сообщения: <input class="w-100" value="{{ $emailData['greeting'] ?? '' }}" type="text" name="messageGreeting">
            </div>
            <div class="min-h-80">
                Текст сообщения: <textarea class="w-100 h-100" name="messageText">{{ $emailData['text'] ?? '' }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Сохранить настройки</button>
    </div>
</form>
