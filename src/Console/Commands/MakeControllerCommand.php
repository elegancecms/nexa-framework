<?php

namespace Core\Console\Commands;

use Core\Console\Command;

class MakeControllerCommand extends Command
{
    protected $description = 'Create a new controller class';

    public function handle($args)
    {
        if (empty($args[0])) {
            $this->error("Please provide a controller name. Example: php nexa make:controller UserController");
            return;
        }

        $name = $args[0];
        $path = __DIR__ . "/../../../app/Controllers/{$name}.php";

        if (file_exists($path)) {
            $this->error("Controller {$name} already exists!");
            return;
        }

        $template = "<?php\n\nnamespace App\Controllers;\n\nuse Core\Inertia;\n\nclass {$name}\n{\n    public function index()\n    {\n        // return Inertia::render('Home');\n    }\n}";

        file_put_contents($path, $template);

        $this->info("Controller [app/Controllers/{$name}.php] created successfully.");
    }
}
