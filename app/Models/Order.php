<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;    

    protected $fillable = [
        'status',
        'user_id',
        'course_id',
        'snap_url',
        'metadata',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'metadata' => 'array',
    ];

    public function getPaymentLogs()
    {
        return $this->hasMany(PaymentLog::class, 'order_id', 'id');
    }
}