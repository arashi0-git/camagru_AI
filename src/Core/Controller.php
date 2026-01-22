<?php

namespace App\Core;

class Controller {
    public function view($view, $data = []) {
        extract($data);
        $viewFile = __DIR__ . "/../Views/$view.php";
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            echo "View $view not found";
        }
    }

    public function redirect($url) {
        header("Location: $url");
        exit;
    }
}
