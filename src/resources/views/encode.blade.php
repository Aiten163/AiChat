<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Кодирование Фано</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1em;
        }

        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 16px;
            resize: vertical;
            min-height: 120px;
            transition: border-color 0.3s;
            font-family: inherit;
        }

        textarea:focus {
            outline: none;
            border-color: #3498db;
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

        .btn-encode {
            background: #27ae60;
            color: white;
        }

        .btn-encode:hover:not(:disabled) {
            background: #219a52;
            transform: translateY(-2px);
        }

        .btn-decode-page {
            background: #e67e22;
            color: white;
        }

        .btn-decode-page:hover {
            background: #d35400;
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
            border-left: 5px solid #3498db;
        }

        .result-section h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.3em;
        }

        .encoded-text {
            background: #2c3e50;
            color: #27ae60;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 14px;
            line-height: 1.4;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .stat-value {
            font-size: 1.5em;
            font-weight: bold;
            color: #2c3e50;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9em;
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
            color: #27ae60;
            font-weight: bold;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
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
        <h1>🔒 Кодирование Фано</h1>
        <p>Алгоритм сжатия данных без потерь</p>
    </div>

    <div class="content">
        <div class="form-group">
            <label for="inputText">Введите текст для кодирования:</label>
            <textarea
                id="inputText"
                placeholder="Введите текст здесь... Например: 'мама мыла раму'"
                oninput="toggleEncodeButton()"
            ></textarea>
        </div>

        <div class="button-group">
            <button id="encodeBtn" class="btn-encode" onclick="encodeText()" disabled>
                Закодировать текст
            </button>
            <button class="btn-decode-page" onclick="window.location.href='{{ route('decode') }}'">
                Перейти к декодированию →
            </button>
            <button class="btn-clear" onclick="clearData()">
                Очистить данные
            </button>
        </div>

        <div id="loading" class="loading">
            <div class="spinner"></div>
            <p id="loadingText">Кодирование текста...</p>
        </div>

        <div id="error" class="error"></div>

        <div id="success" class="success"></div>

        <div id="resultSection" class="result-section">
            <h3>✅ Текст успешно закодирован!</h3>

            <div class="stats">
                <div class="stat-card">
                    <div class="stat-value" id="compressionRatio">0%</div>
                    <div class="stat-label">Коэффициент сжатия</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="originalLength">0</div>
                    <div class="stat-label">Исходных символов</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="encodedLength">0</div>
                    <div class="stat-label">Бит после кодирования</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="uniqueChars">0</div>
                    <div class="stat-label">Уникальных символов</div>
                </div>
            </div>

            <h4>Закодированный текст:</h4>
            <div id="encodedText" class="encoded-text"></div>

            <h4>Таблица кодов Фано:</h4>
            <table id="codesTable" class="codes-table">
                <thead>
                <tr>
                    <th>Символ</th>
                    <th>Код Фано</th>
                    <th>Длина кода</th>
                </tr>
                </thead>
                <tbody id="codesTableBody">
                </tbody>
            </table>
        </div>

        <div class="nav-links">
            <a href="{{ route('decode') }}" class="nav-link">Перейти к декодированию</a>
            <a href="#" onclick="clearForm()" class="nav-link">Очистить форму</a>
        </div>
    </div>
</div>

<script>
    // CSRF токен для AJAX запросов
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function toggleEncodeButton() {
        const inputText = document.getElementById('inputText').value.trim();
        document.getElementById('encodeBtn').disabled = inputText.length === 0;
    }

    async function encodeText() {
        const inputText = document.getElementById('inputText').value.trim();
        const loading = document.getElementById('loading');
        const loadingText = document.getElementById('loadingText');
        const error = document.getElementById('error');
        const success = document.getElementById('success');
        const resultSection = document.getElementById('resultSection');

        // Сброс предыдущих результатов
        error.style.display = 'none';
        success.style.display = 'none';
        resultSection.style.display = 'none';
        loading.style.display = 'block';
        loadingText.textContent = 'Кодирование текста...';

        try {
            const response = await fetch('{{ route('fano.encode') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json; charset=utf-8',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ text: inputText })
            });

            // Проверяем статус ответа
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                displayResults(data, inputText);
                showSuccess('Текст успешно закодирован!');
            } else {
                showError(data.error || 'Произошла ошибка при кодировании');
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
                document.getElementById('inputText').value = '';
                document.getElementById('resultSection').style.display = 'none';
                toggleEncodeButton();
            } else {
                showError(data.error || 'Ошибка при удалении данных');
            }
        } catch (err) {
            showError('Ошибка сети: ' + err.message);
        } finally {
            loading.style.display = 'none';
        }
    }

    function displayResults(data, originalText) {
        const resultSection = document.getElementById('resultSection');
        const encodedText = document.getElementById('encodedText');
        const codesTableBody = document.getElementById('codesTableBody');
        const compressionRatio = document.getElementById('compressionRatio');
        const originalLength = document.getElementById('originalLength');
        const encodedLength = document.getElementById('encodedLength');
        const uniqueChars = document.getElementById('uniqueChars');

        // Обновляем статистику
        compressionRatio.textContent = data.compression_ratio + '%';
        originalLength.textContent = data.stats.original_length;
        encodedLength.textContent = data.stats.encoded_length;
        uniqueChars.textContent = data.stats.unique_chars;

        // Показываем закодированный текст
        encodedText.textContent = data.encoded_text;

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

            const lengthCell = document.createElement('td');
            lengthCell.textContent = code.length + ' бит';

            row.appendChild(charCell);
            row.appendChild(codeCell);
            row.appendChild(lengthCell);
            codesTableBody.appendChild(row);
        }

        resultSection.style.display = 'block';
        resultSection.scrollIntoView({ behavior: 'smooth' });
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

    function clearForm() {
        document.getElementById('inputText').value = '';
        document.getElementById('resultSection').style.display = 'none';
        document.getElementById('error').style.display = 'none';
        document.getElementById('success').style.display = 'none';
        document.getElementById('encodeBtn').disabled = true;
    }

    // Инициализация
    document.addEventListener('DOMContentLoaded', function() {
        toggleEncodeButton();
    });
</script>
</body>
</html>
