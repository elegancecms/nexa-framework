<?php

namespace Core\Console\Commands;

use Core\Console\Command;

class ServeCommand extends Command
{
    protected $description = 'Serve the application on the PHP development server';

    public function handle($args)
    {
        $host = '127.0.0.1';
        $port = '8000';

        foreach ($args as $arg) {
            if (strpos($arg, '--port=') === 0) $port = substr($arg, 7);
            if (strpos($arg, '--host=') === 0) $host = substr($arg, 7);
        }

        $this->info("Nexa development server started: <http://{$host}:{$port}>");
        $this->info("Press Ctrl+C to stop.");

        // Start PHP server process
        $descriptors = [
            0 => ['file', 'php://stdin', 'r'],
            1 => ['file', 'php://stdout', 'w'],
            2 => ['pipe', 'w'] // Read stderr (where PHP logs usually go)
        ];

        $process = proc_open("php -S {$host}:{$port} -t public", $descriptors, $pipes);

        if (is_resource($process)) {
            // Read stderr line by line
            while (!feof($pipes[2])) {
                $line = fgets($pipes[2]);
                if ($line) {
                    $this->processLog($line);
                }
            }
            fclose($pipes[2]);
            proc_close($process);
        }
    }

    protected function processLog($line)
    {
        // Ignore assets and irrelevant logs
        if (strpos($line, '.') !== false && (
            strpos($line, '.css') !== false ||
            strpos($line, '.js') !== false ||
            strpos($line, '.map') !== false ||
            strpos($line, '.json') !== false ||
            strpos($line, '.ico') !== false ||
            strpos($line, 'Accepted') !== false ||
            strpos($line, 'Closing') !== false
        )) {
            return;
        }

        // Catch request lines like: [Tue Dec 16 23:45:00 2025] [::1]:56645 [200]: GET /
        if (preg_match('/\] (?:.*) \[(.*)\]: (GET|POST|PUT|DELETE|PATCH) (.*)/', $line, $matches)) {
            $status = $matches[1];
            $method = $matches[2];
            $url = trim($matches[3]);
            
            $color = '32'; // Green default
            if ($status >= 400) $color = '33'; // Yellow
            if ($status >= 500) $color = '31'; // Red

            $date = date('H:i:s');
            $dots = str_repeat('.', 50 - strlen($url) - strlen($method));
            
            echo "  \033[90m{$date}\033[0m {$status} {$method} {$url} \033[90m{$dots}\033[0m \033[{$color}mDONE\033[0m\n";
        } 
        // Print other errors/warnings
        elseif (strpos($line, 'PHP Warning') !== false || strpos($line, 'PHP Fatal') !== false) {
             echo $line;
        }
    }
}
