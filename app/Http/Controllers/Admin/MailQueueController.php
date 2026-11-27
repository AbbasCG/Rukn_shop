<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

/**
 * Lets the shop owner retry or discard emails that failed in the
 * background, without a terminal.
 */
class MailQueueController extends Controller
{
    public function retry(): RedirectResponse
    {
        Artisan::call('queue:retry', ['id' => ['all']]);

        return back()->with('success', __('admin.mail_queue.retried'));
    }

    public function discard(): RedirectResponse
    {
        Artisan::call('queue:flush');

        return back()->with('success', __('admin.mail_queue.discarded'));
    }
}
