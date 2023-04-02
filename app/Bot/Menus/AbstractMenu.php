<?php

namespace App\Bot\Menus;

use SergiX44\Nutgram\Conversations\InlineMenu;
use SergiX44\Nutgram\Nutgram;

abstract class AbstractMenu extends InlineMenu
{
    public static function trigger(Nutgram $bot, string $step)
    {
        $instance = $bot->getContainer()->get(static::class);
        $instance->setStep($step);
        $instance($bot);
    }

    public function setStep(string $step)
    {
        $this->step = $step;
    }
}
