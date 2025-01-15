<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $table = 'reports';
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'filter_keys',
        'interval',
    ];

    public function user():BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
