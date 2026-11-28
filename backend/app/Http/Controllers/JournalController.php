<?php

namespace App\Http\Controllers;

use App\Models\JournalAdmin;

class JournalController extends Controller
{
    public function index()
    {
        $journal = JournalAdmin::with('admin:id,name')
            ->latest()
            ->paginate(20);

        return response()->json($journal);
    }
}