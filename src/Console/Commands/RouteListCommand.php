<?php

namespace Core\Console\Commands;

use Core\Console\Command;
use Core\Router;

class RouteListCommand extends Command
{
    protected $description = 'List all registered routes';

    public function handle($args)
    {
        // Uygulamayı oluştur
        $app = require __DIR__ . '/../../../bootstrap/app.php';
        
        $router = Router::getInstance();
        $routes = $router->getRoutes();

        usort($routes, fn($a, $b) => strcmp($a->uri, $b->uri));

        $this->info("");

        $terminalWidth = $this->getTerminalWidth();

        foreach ($routes as $route) {
            $this->displayRoute($route, $terminalWidth);
        }

        $this->info("");
        $count = count($routes);
        // "Showing [N] routes" sağa yaslı deği, ortada veya sağa yakın. Laravel'de genelde sağa yaslıdır.
        // Ama ekran görüntüsünde en altta.
        $msg = "Showing [{$count}] routes";
        $padding = max(0, $terminalWidth - strlen($msg) - 2);
        echo str_repeat(' ', $padding) . "\033[90m{$msg}\033[0m\n";
        $this->info("");
    }

    protected function displayRoute($route, $terminalWidth)
    {
        $method = $route->method === 'GET' ? 'GET|HEAD' : $route->method;
        $uri = $route->uri;
        $name = $route->name ?? '';
        
        // Action Determine
        $action = 'Closure';
        if (is_array($route->action)) {
            $class = $route->action[0];
            $func = $route->action[1];
            $class = str_replace('App\\Controllers\\', '', $class);
            $action = "{$class}@{$func}";
        }

        // --- SOL TARAF ---
        $methodColor = $this->getMethodColor($route->method);
        // Method 10 char width
        $methodStr = sprintf("\033[%sm%-10s\033[0m", $methodColor, $method);
        
        // URI Renklendirme ({param} -> Sarı)
        $uriStyled = preg_replace('/\{.*?\}/', "\033[33m$0\033[0m", $uri);
        
        $left = "  {$methodStr} {$uriStyled}";
        // Uzunluk hesaplarken renkleri temizle
        $leftLen = 2 + 10 + 1 + strlen($uri); 

        // --- SAĞ TARAF ---
        $right = "";
        if ($name) {
            $right .= $name;
        }
        if ($name && $action) {
            $right .= " \033[90m›\033[0m "; // Gri ok
        }
        $right .= "\033[90m{$action}\033[0m"; // Gri Action

        // Sağ tarafın ham uzunluğu (renksiz)
        $rightRaw = "";
        if ($name) $rightRaw .= $name;
        if ($name && $action) $rightRaw .= " › ";
        $rightRaw .= $action;
        $rightLen = strlen($rightRaw);

        // --- NOKTALAR ---
        // Terminal genişliğinden sol ve sağ uzunluğu çıkar
        // En az 3 nokta olsun
        $dotsCount = max(3, $terminalWidth - $leftLen - $rightLen - 4); // Margin sapması
        $dots = str_repeat('.', $dotsCount);

        echo "{$left} \033[90m{$dots}\033[0m {$right}\n";
    }

    protected function getTerminalWidth()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = [];
            exec('mode con', $output);
            foreach ($output as $line) {
                if (preg_match('/Columns:\s+(\d+)/', $line, $matches)) {
                    return (int)$matches[1];
                }
            }
        } else {
            $cols = shell_exec('tput cols');
            if ($cols) return (int)$cols;
        }
        return 120; // Fallback
    }

    protected function getMethodColor($method)
    {
        return match ($method) {
            'GET' => '34',     // Mavi
            'POST' => '33',    // Sarı
            'PUT', 'PATCH' => '33', // Sarı
            'DELETE' => '31',  // Kırmızı
            default => '37',   // Beyaz
        };
    }
}
