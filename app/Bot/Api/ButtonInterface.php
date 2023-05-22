<?php

namespace App\Bot\Api;

use App\Bot\Menus\AbstractMenu;

interface ButtonInterface
{
    public function render(AbstractMenu $menu);

    public function create(array $callbackParams = []);

    public function generateCallback(array $callbackParams);
}
