<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'jobs';
    
    public function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }
}
