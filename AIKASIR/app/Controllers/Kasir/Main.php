<?php

namespace App\Controllers\Kasir;

use App\Controllers\BaseController;

class Main extends BaseController
{
    public function index(): string
    {
        return view('Kasir/Main');
    }
}
