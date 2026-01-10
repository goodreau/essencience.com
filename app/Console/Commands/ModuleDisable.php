<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ModuleDisable extends Command
{
    protected $signature = 'module:disable {name}';
    protected $description = 'Disable a module by removing it from config/modules.php enabled list';

    public function handle(): int
    {
        $name = strtolower($this->argument('name'));
        $path = config_path('modules.php');
        $cfg = include $path;
        $enabled = array_filter(array_map('strtolower', $cfg['enabled'] ?? []), fn($n) => $n !== $name);
        $cfg['enabled'] = array_values($enabled);
        $export = "<?php\n\nreturn ".var_export($cfg, true).";\n";
        file_put_contents($path, $export);
        $this->info("Disabled: {$name}");
        return self::SUCCESS;
    }
}
