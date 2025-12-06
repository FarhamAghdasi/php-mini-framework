<?php
if (!function_exists('app')) {
    function app() {
        return \App\Core\Application::getInstance();
    }
}

if (!function_exists('config')) {
    function config(string $key, $default = null) {
        $app = app();
        $keys = explode('.', $key);

        $value = $app->getConfig() ?? [];
        foreach ($keys as $segment) {
            if (isset($value[$segment])) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }

        return $value;
    }
}

if (!function_exists('view')) {
    function view(string $template, array $data = []) {
        $app = app();
        $view = $app->getService('view');
        return $view->render($template, $data);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $statusCode = 302) {
        $response = new \App\Core\Response();
        $response->redirect($url, $statusCode);
    }
}

if (!function_exists('session')) {
    function session(?string $key = null, $value = null) {
        $app = app();
        $session = $app->getService('session');

        if ($key === null) {
            return $session;
        }

        if ($value === null) {
            return $session->get($key);
        }

        $session->set($key, $value);
        return null;
    }
}

if (!function_exists('cache')) {
    function cache(?string $key = null, $value = null, int $ttl = 3600) {
        $app = app();
        $cache = $app->getService('cache');

        if ($key === null) {
            return $cache;
        }

        if ($value === null) {
            return $cache->get($key);
        }

        if ($value === false) {
            return $cache->delete($key);
        }

        return $cache->set($key, $value, $ttl);
    }
}

if (!function_exists('logger')) {
    function logger() {
        $app = app();
        return $app->getService('logger');
    }
}

if (!function_exists('env')) {
    function env(string $key, $default = null) {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('dd')) {
    function dd(...$args) {
        foreach ($args as $arg) {
            echo '<pre>';
            var_dump($arg);
            echo '</pre>';
        }
        die();
    }
}