<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssistantLogin extends Model
{
   

    public function assistant()
    {
        return $this->belongsTo(Assistant::class);
    }
}
