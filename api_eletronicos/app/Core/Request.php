<?php

namespace App\Core;

require_once __DIR__ . '/../../config/config.php';

class Request {
    public static function getMethod(): string {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function getUri(): string {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove a pasta public da URL caso ela esteja presente no caminho do Apache
        $base = defined('BASE_URL') ? BASE_URL : '';
        if (!empty($base) && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        
        $uri = rtrim($uri, '/');
        return empty($uri) ? '/' : $uri;
    }

    public static function getBody(): array {
        $input = json_decode(file_get_contents("php://input"), true);
        return is_array($input) ? $input : [];
    }

    public static function getQueryParams(): array {
        return $_GET;
    }
}