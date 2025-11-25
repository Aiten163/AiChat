
    <form method="post"  action="{{route('platform.emailSettings.store')}}">
        <div class="w-50 inline-flex">
            <div>
                Email: <input type="email" autocomplete="off" value="{{$emailData['login'] ?? null}}" name="emailLogin" class="form-text w-100">
            </div>
            <div>
                Пароль: <input type="password" class="w-100" autocomplete="new-password" placeholder="{{isset($emailData['password']) ? "********" : '' }}"  name="emailPassword">
            </div>
            <div>
                Имя отправителя: <input type="text" class="w-100" value="{{$emailData['sender'] ?? null}}"  name="sender">
            </div>
            <div>
                Порт: <input type="number" class="w-100" value="{{$emailData['port'] ?? 465}}" name="sender">
            </div>

            <div style="margin-top: 5%">
                <div id="title" class="fw-bold">
                    Настройка для сообщений с нарушением информационной безопасности
                </div>
                <div>
                    Тема сообщения: <input type="text" class="w-100" value="{{$emailData['theme'] ?? null}}" name="messageTheme">
                </div>

                <div>
                    Приветствие сообщения: <input class="w-100" value="{{$emailData['greeting'] ?? null}}" type="text" name="messageGreeting">
                </div>

                <div class="min-h-80">
                    Текст сообщения: <textarea  class="w-100 h-100" name="messageText"> {{$emailData['text'] ?? null}} </textarea>
                </div>
            </div>
            <input type="submit">
        </div>
    </form>
