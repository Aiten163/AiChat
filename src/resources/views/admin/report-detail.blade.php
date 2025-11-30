<div class="bg-white rounded-lg shadow-sm p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Обращение в техподдержку</h2>

    <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
            <div class="text-gray-600 font-medium">Пользователь:</div>
            <div class="md:col-span-2 font-semibold">{{ $report['user_name'] }}</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
            <div class="text-gray-600 font-medium">ID пользователя:</div>
            <div class="md:col-span-2">{{ $report['user_id'] ?? 'Не указан' }}</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
            <div class="text-gray-600 font-medium">Время обращения:</div>
            <div class="md:col-span-2">{{ $report['created_at'] }}</div>
        </div>

        <div class="p-4 bg-gray-50 rounded-lg">
            <div class="text-gray-600 font-medium mb-2">Сообщение:</div>
            <div class="bg-white rounded p-4 whitespace-pre-wrap border">{{ $report['message'] }}</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
            <div class="text-gray-600 font-medium">Изображение:</div>
            <div class="md:col-span-2">
                @if($report['image_path'])
                    <span class="text-green-600 font-semibold">Прикреплено</span>
                @else
                    <span class="text-gray-500">Нет изображения</span>
                @endif
            </div>
        </div>
    </div>
</div>
