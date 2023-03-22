<?php

namespace App\Http\Controllers;

use SergiX44\Nutgram\Nutgram;

class FrontController extends Controller
{
    public function handle(Nutgram $bot)
    {
        $bot->run();
    }
}
