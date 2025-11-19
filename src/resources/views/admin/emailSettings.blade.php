<div>
    @if(isset($email['login']))
        Текущий email: {{$email['login']}}
    @else
        Текущий email: отсутствует
    @endif
</div>
