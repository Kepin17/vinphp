<?php

namespace App\Controllers;

use App\Core\View;

class HomeController
{
    public function index(): void
    {
        View::render('pages/home', [
            'title' => 'Home',
        ]);
    }
}
