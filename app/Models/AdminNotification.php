<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $table = 'admin_notifications';

    protected $fillable = [
        'type',
        'title',
        'message',
        'related_id',
        'is_read',
    ];

    public static function unreadCount(): int
    {
        return self::where('is_read', false)->count();
    }

    public static function latest10(): \Illuminate\Database\Eloquent\Collection
    {
        return self::orderByDesc('created_at')->limit(10)->get();
    }
}
