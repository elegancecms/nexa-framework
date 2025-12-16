<?php

namespace Core\Console;

class Application
{
    protected $commands = [
        'serve' => \Core\Console\Commands\ServeCommand::class,
        'make:controller' => \Core\Console\Commands\MakeControllerCommand::class,
        'route:list' => \Core\Console\Commands\RouteListCommand::class,
    ];

    public function run($argv)
    {
        $commandName = $argv[1] ?? 'list';

        if ($commandName === 'list') {
            $this->listCommands();
            return;
        }

        if (!array_key_exists($commandName, $this->commands)) {
            echo "Command '{$commandName}' not found.\n";
            exit(1);
        }

        $commandClass = $this->commands[$commandName];
        $command = new $commandClass();
        $command->handle(array_slice($argv, 2));
    }

    protected function listCommands()
    {
        echo "Nexa Framework CLI\n\n";
        echo "Available commands:\n";
        foreach ($this->commands as $name => $class) {
            $instance = new $class();
            echo "  \033[32m{$name}\033[0m    {$instance->getDescription()}\n";
        }
    }
}
