<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{

    public function doctor(){
    	return $this->belongsTo(Doctor::class);
    }

    public function assistant(){
    	return $this->belongsTo(Assistant::class);
    }

    public function staff(){
    	return $this->belongsTo(Staff::class);
    }
}
