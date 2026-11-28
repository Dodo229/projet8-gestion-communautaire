<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        $users = User::select('id', 'name', 'email', 'role', 'actif', 'created_at')
            ->when($request->query('recherche'), function ($query, $recherche) {
                $query->where(function ($q) use ($recherche) {
                    $q->where('name', 'like', "%{$recherche}%")
                      ->orWhere('email', 'like', "%{$recherche}%");
                });
            })
            ->latest()
            ->paginate(20);

        return response()->json($users);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'role'  => 'sometimes|in:admin,usager',
            'actif' => 'sometimes|boolean',
        ]);

        $user = User::findOrFail($id);
        $user->forceFill($data)->save();

        return response()->json($user->only('id', 'name', 'email', 'role', 'actif'));
    }
}