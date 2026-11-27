<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Moderation: reviews appear in the shop only after the owner publishes them.
 */
class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), Review::STATUSES, true) ? $request->query('status') : 'pending';

        $reviews = Review::query()
            ->with(['product', 'order'])
            ->where('status', $status)
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $counts = Review::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.reviews.index', compact('reviews', 'status', 'counts'));
    }

    public function update(Request $request, string $locale, Review $review): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', 'in:approved,rejected']]);

        $review->update([
            'status' => $validated['status'],
            'approved_at' => $validated['status'] === 'approved' ? ($review->approved_at ?? now()) : null,
        ]);

        return back()->with('success', __('reviews.admin.' . $validated['status']));
    }

    public function destroy(string $locale, Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', __('reviews.admin.deleted'));
    }
}
