<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SizeGuideRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The abaya size guide shown on product pages (lengths and heights).
 */
class SizeGuideController extends Controller
{
    public function edit()
    {
        return view('admin.size-guide.edit', ['rows' => SizeGuideRow::ordered()]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'rows' => ['nullable', 'array', 'max:30'],
            'rows.*.size' => ['required', 'string', 'max:10', 'distinct'],
            'rows.*.height_min' => ['required', 'integer', 'between:100,230'],
            'rows.*.height_max' => ['required', 'integer', 'between:100,230', 'gte:rows.*.height_min'],
        ], [], [
            'rows.*.size' => __('size_guide.admin.size'),
            'rows.*.height_min' => __('size_guide.admin.height_min'),
            'rows.*.height_max' => __('size_guide.admin.height_max'),
        ]);

        DB::transaction(function () use ($validated) {
            SizeGuideRow::query()->delete();
            foreach (array_values($validated['rows'] ?? []) as $i => $row) {
                SizeGuideRow::create([
                    'size' => trim($row['size']),
                    'height_min' => (int) $row['height_min'],
                    'height_max' => (int) $row['height_max'],
                    'position' => $i,
                ]);
            }
        });

        return redirect()->route('admin.size-guide.edit')->with('success', __('size_guide.admin.saved'));
    }
}
