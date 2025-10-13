<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="text-primary">{{ number_format($stats['total_messages']) }}</h3>
                <p class="text-muted">Всего сообщений</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="text-success">{{ $stats['active_users'] }}</h3>
                <p class="text-muted">Активных пользователей</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="text-info">{{ $stats['avg_messages_per_user'] }}</h3>
                <p class="text-muted">Сообщений на пользователя</p>
            </div>
        </div>
    </div>
</div>
