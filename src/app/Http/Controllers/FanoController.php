<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FanoController extends Controller
{
    /**
     * Отображает страницу кодирования
     */
    public function showEncodePage()
    {
        return view('encode');
    }

    /**
     * Отображает страницу декодирования
     */
    public function showDecodePage()
    {
        return view('decode');
    }

    /**
     * API endpoint для кодирования текста
     */
    public function encode(Request $request)
    {
        // Валидация входных данных
        $request->validate([
            'text' => 'required|string'
        ]);

        $text = $request->input('text');

        Log::info('Начало кодирования текста', ['length' => mb_strlen($text, 'UTF-8')]);

        try {
            // Получаем коды Фано
            $fanoData = $this->buildFanoCodes($text);
            $encodedText = $this->encodeText($text, $fanoData['codes']);

            // Подготавливаем данные для сохранения (только самое необходимое)
            $fileData = [
                'encoded_text' => $encodedText,
                'codes' => $fanoData['codes'],
                'timestamp' => now()->toISOString(),
            ];

            // Сохраняем в файл (перезаписываем если существует)
            $this->saveToFile($fileData);

            Log::info('Текст успешно закодирован');

            return response()->json([
                'success' => true,
                'encoded_text' => $encodedText,
                'codes' => $fanoData['codes'],
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('Ошибка при кодировании текста', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка при кодировании: ' . $e->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * API endpoint для получения закодированных данных
     */
    public function getEncodedData()
    {
        try {
            if (!Storage::exists('fano_encoding.json')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Файл с закодированными данными не найден. Сначала закодируйте текст.'
                ], 404, [], JSON_UNESCAPED_UNICODE);
            }

            $fileContent = Storage::get('fano_encoding.json');
            $data = json_decode($fileContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Ошибка при чтении файла с закодированными данными: ' . json_last_error_msg());
            }

            Log::info('Данные успешно загружены из файла');

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('Ошибка при получении закодированных данных', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * API endpoint для удаления закодированных данных
     */
    public function clearData()
    {
        try {
            if (Storage::exists('fano_encoding.json')) {
                Storage::delete('fano_encoding.json');
                Log::info('Закодированные данные успешно удалены');
            }

            return response()->json([
                'success' => true,
                'message' => 'Данные успешно удалены'
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('Ошибка при удалении данных', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Построение кодов Фано с поддержкой UTF-8
     */
    private function buildFanoCodes($text)
    {
        if (empty($text)) {
            throw new \Exception('Текст для кодирования не может быть пустым');
        }

        // Подсчет частот символов с поддержкой UTF-8
        $frequencies = [];
        $length = mb_strlen($text, 'UTF-8');

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            $frequencies[$char] = ($frequencies[$char] ?? 0) + 1;
        }

        // Сортируем символы по убыванию частоты
        arsort($frequencies);

        $codes = [];
        $this->generateFanoCodes(array_keys($frequencies), array_values($frequencies), '', $codes);

        return [
            'codes' => $codes,
            'frequencies' => $frequencies
        ];
    }

    /**
     * Рекурсивная генерация кодов Фано
     */
    private function generateFanoCodes($chars, $frequencies, $currentCode, &$codes)
    {
        $n = count($chars);

        if ($n == 1) {
            $codes[$chars[0]] = $currentCode;
            return;
        }

        if ($n == 0) {
            return;
        }

        // Находим оптимальное разделение
        $splitIndex = $this->findSplitIndex($frequencies);

        // Рекурсивно генерируем коды для левой и правой частей
        $this->generateFanoCodes(
            array_slice($chars, 0, $splitIndex + 1),
            array_slice($frequencies, 0, $splitIndex + 1),
            $currentCode . '0',
            $codes
        );

        $this->generateFanoCodes(
            array_slice($chars, $splitIndex + 1),
            array_slice($frequencies, $splitIndex + 1),
            $currentCode . '1',
            $codes
        );
    }

    /**
     * Поиск индекса разделения для алгоритма Фано
     */
    private function findSplitIndex($frequencies)
    {
        $total = array_sum($frequencies);
        $leftSum = 0;
        $minDiff = PHP_INT_MAX;
        $splitIndex = 0;

        for ($i = 0; $i < count($frequencies) - 1; $i++) {
            $leftSum += $frequencies[$i];
            $rightSum = $total - $leftSum;
            $diff = abs($leftSum - $rightSum);

            if ($diff < $minDiff) {
                $minDiff = $diff;
                $splitIndex = $i;
            }
        }

        return $splitIndex;
    }

    /**
     * Кодирование текста с использованием кодов Фано (UTF-8)
     */
    private function encodeText($text, $codes)
    {
        $encoded = '';
        $length = mb_strlen($text, 'UTF-8');

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            if (isset($codes[$char])) {
                $encoded .= $codes[$char];
            } else {
                throw new \Exception("Не найден код для символа: '" . $char . "'");
            }
        }

        return $encoded;
    }

    /**
     * Сохранение данных в файл с поддержкой UTF-8
     */
    private function saveToFile($data)
    {
        $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($jsonData === false) {
            throw new \Exception('Ошибка при кодировании данных в JSON: ' . json_last_error_msg());
        }

        $result = Storage::put('fano_encoding.json', $jsonData);

        if (!$result) {
            throw new \Exception('Не удалось сохранить данные в файл');
        }
    }
}
