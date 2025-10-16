<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Декодирование Фано</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: #2c3e50;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.8;
            font-size: 1.1em;
        }

        .content {
            padding: 40px;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        button {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            flex: 1;
            min-width: 200px;
        }

        .btn-decode {
            background: #e67e22;
            color: white;
        }

        .btn-decode:hover:not(:disabled) {
            background: #d35400;
            transform: translateY(-2px);
        }

        .btn-encode-page {
            background: #27ae60;
            color: white;
        }

        .btn-encode-page:hover {
            background: #219a52;
            transform: translateY(-2px);
        }

        .btn-clear {
            background: #e74c3c;
            color: white;
        }

        .btn-clear:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        button:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
            transform: none;
        }

        .result-section {
            display: none;
            margin-top: 30px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 5px solid #e67e22;
        }

        .result-section h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.3em;
        }

        .decoded-text {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #e1e8ed;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .encoded-text {
            background: #2c3e50;
            color: #e67e22;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 14px;
        }

        .codes-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .codes-table th {
            background: #34495e;
            color: white;
            padding: 12px;
            text-align: left;
        }

        .codes-table td {
            padding: 12px;
            border-bottom: 1px solid #ecf0f1;
        }

        .codes-table tr:hover {
            background: #f8f9fa;
        }

        .char-cell {
            font-weight: bold;
            color: #2c3e50;
        }

        .code-cell {
            font-family: 'Courier New', monospace;
            color: #e67e22;
            font-weight: bold;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #e67e22;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .error {
            background: #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .success {
            background: #27ae60;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }

        .no-data h3 {
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .nav-links {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
        }

        .nav-link {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            margin: 0 15px;
            padding: 10px 20px;
            border: 2px solid #3498db;
            border-radius: 8px;
            transition: all 0.3s;
            display: inline-block;
        }

        .nav-link:hover {
            background: #3498db;
            color: white;
        }

        .special-char {
            color: #e74c3c;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🔓 Декодирование Фано</h1>
        <p>Восстановление исходного текста из закодированных данных</p>
    </div>

    <div class="content">
        <div class="button-group">
            <button id="decodeBtn" class="btn-decode" onclick="decodeText()">
                Декодировать текст
            </button>
            <button class="btn-encode-page" onclick="window.location.href='{{ route('encode') }}'">
                ← Перейти к кодированию
            </button>
            <button class="btn-clear" onclick="clearData()">
                Очистить данные
            </button>
        </div>

        <div id="loading" class="loading">
            <div class="spinner"></div>
            <p id="loadingText">Загрузка данных...</p>
        </div>

        <div id="error" class="error"></div>

        <div id="success" class="success"></div>

        <div id="noData" class="no-data">
            <h3>📭 Данные не найдены</h3>
            <p>Сначала закодируйте текст на странице кодирования</p>
            <button class="btn-encode-page" onclick="window.location.href='{{ route('encode') }}'" style="margin-top: 15px;">
                Перейти к кодированию
            </button>
        </div>

        <div id="resultSection" class="result-section">
            <h3>✅ Текст успешно декодирован!</h3>

            <h4>Закодированный текст:</h4>
            <div id="encodedText" class="encoded-text"></div>

            <h4>Исходный текст:</h4>
            <div id="decodedText" class="decoded-text"></div>

            <h4>Таблица кодов Фано:</h4>
            <table id="codesTable" class="codes-table">
                <thead>
                <tr>
                    <th>Символ</th>
                    <th>Код Фано</th>
                </tr>
                </thead>
                <tbody id="codesTableBody">
                </tbody>
            </table>
        </div>

        <div class="nav-links">
            <a href="{{ route('encode') }}" class="nav-link">Перейти к кодированию</a>
            <a href="#" onclick="location.reload()" class="nav-link">Обновить страницу</a>
        </div>
    </div>
</div>

<script>
    // CSRF токен для AJAX запросов
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    async function decodeText() {
        const loading = document.getElementById('loading');
        const loadingText = document.getElementById('loadingText');
        const error = document.getElementById('error');
        const success = document.getElementById('success');
        const resultSection = document.getElementById('resultSection');
        const noData = document.getElementById('noData');

        // Сброс предыдущих результатов
        error.style.display = 'none';
        success.style.display = 'none';
        resultSection.style.display = 'none';
        noData.style.display = 'none';
        loading.style.display = 'block';
        loadingText.textContent = 'Загрузка данных...';

        try {
            const response = await fetch('{{ route('fano.encoded-data') }}', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json; charset=utf-8'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                displayDecodedResults(data.data);
                showSuccess('Текст успешно декодирован!');
            } else {
                if (data.error && data.error.includes('не найден')) {
                    noData.style.display = 'block';
                } else {
                    showError(data.error || 'Произошла ошибка при загрузке данных');
                }
            }
        } catch (err) {
            showError('Ошибка сети: ' + err.message);
        } finally {
            loading.style.display = 'none';
        }
    }

    async function clearData() {
        if (!confirm('Вы уверены, что хотите удалить все закодированные данные?')) {
            return;
        }

        const loading = document.getElementById('loading');
        const loadingText = document.getElementById('loadingText');
        const error = document.getElementById('error');
        const success = document.getElementById('success');

        loading.style.display = 'block';
        loadingText.textContent = 'Удаление данных...';
        error.style.display = 'none';
        success.style.display = 'none';

        try {
            const response = await fetch('{{ route('fano.clear') }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                showSuccess('Данные успешно удалены!');
                document.getElementById('resultSection').style.display = 'none';
                document.getElementById('noData').style.display = 'block';
            } else {
                showError(data.error || 'Ошибка при удалении данных');
            }
        } catch (err) {
            showError('Ошибка сети: ' + err.message);
        } finally {
            loading.style.display = 'none';
        }
    }

    function displayDecodedResults(data) {
        const resultSection = document.getElementById('resultSection');
        const decodedText = document.getElementById('decodedText');
        const encodedText = document.getElementById('encodedText');
        const codesTableBody = document.getElementById('codesTableBody');

        // Декодируем текст на клиенте
        const decoded = decodeFano(data.encoded_text, data.codes);

        // Показываем закодированный и декодированный текст
        encodedText.textContent = data.encoded_text;
        decodedText.textContent = decoded;

        // Заполняем таблицу кодов
        codesTableBody.innerHTML = '';
        for (const [char, code] of Object.entries(data.codes)) {
            const row = document.createElement('tr');

            const charCell = document.createElement('td');
            charCell.className = 'char-cell';

            // Отображаем специальные символы
            if (char === ' ') {
                charCell.innerHTML = '<span class="special-char">[ПРОБЕЛ]</span>';
            } else if (char === '\n') {
                charCell.innerHTML = '<span class="special-char">[ПЕРЕНОС]</span>';
            } else if (char === '\t') {
                charCell.innerHTML = '<span class="special-char">[ТАБ]</span>';
            } else if (char === '\r') {
                charCell.innerHTML = '<span class="special-char">[ВОЗВРАТ]</span>';
            } else {
                charCell.textContent = char;
            }

            const codeCell = document.createElement('td');
            codeCell.className = 'code-cell';
            codeCell.textContent = code;

            row.appendChild(charCell);
            row.appendChild(codeCell);
            codesTableBody.appendChild(row);
        }

        resultSection.style.display = 'block';
        resultSection.scrollIntoView({ behavior: 'smooth' });
    }

    function decodeFano(encodedText, codes) {
        // Создаем обратный словарь для быстрого поиска
        const reverseCodes = {};
        for (const [char, code] of Object.entries(codes)) {
            reverseCodes[code] = char;
        }

        let decoded = '';
        let currentCode = '';

        // Последовательно обрабатываем каждый бит
        for (let i = 0; i < encodedText.length; i++) {
            currentCode += encodedText[i];

            if (reverseCodes[currentCode]) {
                decoded += reverseCodes[currentCode];
                currentCode = '';
            }
        }

        return decoded;
    }

    function showError(message) {
        const error = document.getElementById('error');
        error.textContent = message;
        error.style.display = 'block';
        error.scrollIntoView({ behavior: 'smooth' });
    }

    function showSuccess(message) {
        const success = document.getElementById('success');
        success.textContent = message;
        success.style.display = 'block';
    }

    // Показываем сообщение о необходимости нажать кнопку
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('noData').style.display = 'block';
    });
</script>
</body>
</html>
