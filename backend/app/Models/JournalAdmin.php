<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalAdmin extends Model
{
    protected $table = 'journal_admin';

    protected $fillable = ['admin_id', 'action', 'details'];
    
    public function admin()
{
    return $this->belongsTo(User::class, 'admin_id');
}
}