<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->view('home', ['title' => 'Welcome to Camagru']);
    }
}
