<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ModuleEnable extends Command
{
    protected $signature = 'module:enable {name}';
    protected $description = 'Enable a module by adding it to config/modules.php enabled list';

    public function handle(): int
    {
        $name = strtolower($this->argument('name'));
        $path = config_path('modules.php');
        $cfg = include $path;
        $enabled = array_map('strtolower', $cfg['enabled'] ?? []);
        if (!in_array($name, $enabled, true)) {
            $enabled[] = $name;
        }
        $cfg['enabled'] = array_values(array_unique($enabled));
        $export = "<?php\n\nreturn ".var_export($cfg, true).";\n";
        file_put_contents($path, $export);
        $this->info("Enabled: {$name}");
        return self::SUCCESS;
    }
}
