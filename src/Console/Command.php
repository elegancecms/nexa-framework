<?php

namespace Core\Console;

abstract class Command
{
    protected $description = '';

    abstract public function handle($args);

    public function getDescription()
    {
        return $this->description;
    }

    protected function info($message)
    {
        echo "\033[32m" . $message . "\033[0m\n";
    }

    protected function error($message)
    {
        echo "\033[31m" . $message . "\033[0m\n";
    }
}
