<div class="bg-white rounded-lg shadow-sm p-6 mt-6">
    <h3 class="text-xl font-semibold mb-4 text-gray-800">Прикрепленное изображение</h3>

    @if(!empty($report['image_url']))
        <div class="flex flex-col items-center space-y-4">
            <div class="max-w-2xl w-full">
                <img
                    src="{{ $report['image_url'] }}"
                    alt="Прикрепленное изображение"
                    class="rounded-lg shadow-md max-w-full h-auto border-2 border-gray-200 mx-auto"
                    style="max-height: 500px;"
                    onerror="this.style.display='none'; document.getElementById('image-error').style.display='block';"
                >
            </div>

            <div id="image-error" style="display: none;" class="text-red-500 text-center bg-red-50 p-4 rounded-lg">
                <p class="font-medium">Не удалось загрузить изображение</p>
                <p class="text-sm text-gray-600 mt-1">Возможно, файл был удален</p>
            </div>

            <div class="text-center">
                <a href="{{ $report['image_url'] }}"
                   target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    Открыть изображение в новой вкладке
                </a>
            </div>
        </div>
    @else
        <div class="text-center p-4 bg-gray-50 rounded-lg">
            <p class="text-gray-600">Изображение не прикреплено</p>
        </div>
    @endif
</div>
