<?php

namespace App\Http\Controllers;

use App\Services\Modules\ModuleRepository;
use Illuminate\Http\Request;

class ModulesController extends Controller
{
    public function index(ModuleRepository $repo)
    {
        $mods = $repo->discover();
        return view('modules.index', compact('mods'));
    }

    public function enable(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        $name = strtolower($request->string('name'));
        $path = config_path('modules.php');
        $cfg = include $path;
        $enabled = array_map('strtolower', $cfg['enabled'] ?? []);
        if (!in_array($name, $enabled, true)) {
            $enabled[] = $name;
        }
        $cfg['enabled'] = array_values(array_unique($enabled));
        $export = "<?php\n\nreturn ".var_export($cfg, true).";\n";
        file_put_contents($path, $export);
        return back()->with('status', "Enabled {$name}");
    }

    public function disable(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        $name = strtolower($request->string('name'));
        $path = config_path('modules.php');
        $cfg = include $path;
        $enabled = array_filter(array_map('strtolower', $cfg['enabled'] ?? []), fn($n) => $n !== $name);
        $cfg['enabled'] = array_values($enabled);
        $export = "<?php\n\nreturn ".var_export($cfg, true).";\n";
        file_put_contents($path, $export);
        return back()->with('status', "Disabled {$name}");
    }

    public function install(Request $request)
    {
        $data = $request->validate([
            'git' => 'required|string',
            'name' => 'nullable|string',
        ]);
        $git = $data['git'];
        $nameOpt = $data['name'] ?? null;
        $modulesPath = config('modules.path', base_path('modules'));
        if (!is_dir($modulesPath)) @mkdir($modulesPath, 0775, true);
        $tmp = $modulesPath.'/.tmp_'.uniqid('mod_', true);
        @mkdir($tmp, 0775, true);
        $out = shell_exec(sprintf('git clone --depth 1 %s %s 2>&1', escapeshellarg($git), escapeshellarg($tmp)));
        if (!is_dir($tmp.'/.git')) {
            return back()->with('status', 'Clone failed: '.($out ?? ''));
        }
        $composer = $tmp.'/composer.json';
        $name = $nameOpt;
        if (is_file($composer)) {
            $json = json_decode((string) @file_get_contents($composer), true);
            if (isset($json['name'])) $name = $json['name'];
        }
        if (!$name || !str_contains($name, '/')) {
            @shell_exec(sprintf('rm -rf %s', escapeshellarg($tmp)));
            return back()->with('status', 'Unable to determine package name.');
        }
        [$vendor, $package] = explode('/', $name, 2);
        $dest = $modulesPath.'/'.$vendor.'/'.$package;
        @mkdir(dirname($dest), 0775, true);
        if (is_dir($dest)) {
            @shell_exec(sprintf('rm -rf %s', escapeshellarg($tmp)));
            return back()->with('status', 'Destination already exists.');
        }
        rename($tmp, $dest);
        return back()->with('status', "Installed {$name} to {$dest}");
    }
}
