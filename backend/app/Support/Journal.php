<?php

namespace App\Support;

use App\Models\JournalAdmin;
use Illuminate\Support\Facades\Auth;

class Journal
{
    public static function journaliser(string $action, ?string $details = null): void
    {
        $admin = Auth::user();

        if (! $admin) {
            return;
        }

        JournalAdmin::create([
            'admin_id' => $admin->id,
            'action'   => $action,
            'details'  => $details,
        ]);
    }
}