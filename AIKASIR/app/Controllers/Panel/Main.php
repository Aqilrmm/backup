<?php

namespace App\Controllers\Panel;

use App\Controllers\BaseController;

class Main extends BaseController
{
    public function index(): string
    {
        return view('Panel/Main');
    }
}
