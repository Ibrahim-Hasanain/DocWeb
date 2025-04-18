<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{


    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
