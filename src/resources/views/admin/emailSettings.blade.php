<div>
    <form>
        @if(isset($email['login']))
            Текущий email: {{$email['login']}}
        @else
            Текущий email: отсутствует
        @endif
        <input type="email">
    </form>
</div>
