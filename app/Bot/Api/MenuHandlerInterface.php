<?php

namespace App\Bot\Api;

use SergiX44\Nutgram\Nutgram;

interface MenuHandlerInterface
{
    static public function execute(Nutgram $bot, string $key = '', mixed $value = '', string $return = ''): void;

    static public function analytic(): void;
}
