<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    const NAME = 'name';
    const TELEGRAM_ID = 'telegram_id';
    const TOKEN = 'token';
    const TOKEN_UPDATED = 'token_updated';
    const LAST_MESSAGE = 'last_message';
    const NOTIFY = 'notify';

    protected $fillable = [self::NAME, self::TELEGRAM_ID];

    public static function checkAuth(string $chatId)
    {
        $user = self::where(User::TELEGRAM_ID, $chatId)->first();
        $currentDate = Carbon::now();
        if ($user
            && $currentDate->diffInSeconds(Carbon::parse($user->token_updated)) < config('global.token_lifetime')
            && $user->token
        ) {
            return true;
        }

        return false;
    }
}
