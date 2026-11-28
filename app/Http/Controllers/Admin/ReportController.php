<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SalesReport;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public const RANGES = ['7d', '30d', 'this_month', 'last_month', 'this_year', 'last_12m', 'custom'];

    public function index(Request $request)
    {
        $validated = $request->validate([
            'range' => ['nullable', 'in:' . implode(',', self::RANGES)],
            'from' => ['nullable', 'required_if:range,custom', 'date_format:Y-m-d'],
            'to' => ['nullable', 'required_if:range,custom', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $range = $validated['range'] ?? '30d';
        [$from, $to] = SalesReport::period($range, $validated['from'] ?? null, $validated['to'] ?? null);

        // Keep custom periods to a sensible size (the chart switches to months).
        if ($from->diffInYears($to) >= 5) {
            $from = $to->copy()->subYears(5)->addDay();
        }

        $report = new SalesReport($from, $to);

        return view('admin.reports.index', [
            'range' => $range,
            'report' => $report,
            'data' => $report->build(),
            'ranges' => self::RANGES,
        ]);
    }
}
