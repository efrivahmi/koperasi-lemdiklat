<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

trait HandlesReportErrors
{
    /**
     * Handle report errors with consistent logging and response
     *
     * @param \Exception $e
     * @param string $view
     * @param array $emptyData
     * @param array $filters
     * @param string $logContext
     * @return \Inertia\Response
     */
    protected function handleReportError(
        \Exception $e,
        string $view,
        array $emptyData,
        array $filters = [],
        string $logContext = 'Report error'
    ) {
        Log::error("{$logContext}: " . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'user_id' => auth()->id(),
        ]);

        return Inertia::render($view, array_merge($emptyData, [
            'filters' => $filters,
            'error' => 'Terjadi kesalahan saat memuat data. Silakan coba lagi.',
        ]));
    }
}
