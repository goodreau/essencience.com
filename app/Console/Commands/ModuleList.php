<?php

namespace App\Console\Commands;

use App\Services\Modules\ModuleRepository;
use Illuminate\Console\Command;

class ModuleList extends Command
{
    protected $signature = 'module:list';
    protected $description = 'List discovered modules and their status';

    public function handle(ModuleRepository $repo): int
    {
        $rows = [];
        foreach ($repo->discover() as $m) {
            $rows[] = [
                $m['name'],
                $m['enabled'] ? 'enabled' : 'disabled',
                $m['path'],
                implode("\n", $m['providers']),
            ];
        }
        $this->table(['Name','Status','Path','Providers'], $rows);
        return self::SUCCESS;
    }
}
