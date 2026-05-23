<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Receipt scan preview lives in session; drop it when user leaves the scan page.
 */
class ClearReceiptScanSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->routeIs('expenses.receipt-scan*')) {
            $request->session()->forget(['receipt_scan', 'receipt_scan_preview']);
        }

        return $next($request);
    }
}
