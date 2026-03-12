<?php

namespace App\Services\Filter;

use App\Models\Neural;

class MessageFilterService
{
    private FilterService $filterService;

    public function __construct()
    {
        $this->filterService = new FilterService();
    }

    public function validateMessage(string $message, string $modelName): bool
    {
        return $this->filterService->filter($message, $modelName);
    }
}
