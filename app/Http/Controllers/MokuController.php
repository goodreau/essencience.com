<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MokuService;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class MokuController
{
    public function __construct(private MokuService $moku)
    {
    }

    /**
     * Display Moku dashboard
     */
    public function index(): View
    {
        $installed = $this->moku->isMokuCliInstalled();
        $devices = $installed ? $this->moku->listDevices() : [];

        return view('moku.dashboard', [
            'installed' => $installed,
            'devices' => $devices,
        ]);
    }

    /**
     * List available Moku devices
     */
    public function devices(): View
    {
        $result = $this->moku->listDevices();

        return view('moku.devices', [
            'success' => $result['success'],
            'devices' => $result['success'] ? $this->moku->parseOutput($result['output']) : [],
            'error' => $result['error'] ?? null,
        ]);
    }

    /**
     * Display device info
     */
    public function deviceInfo(Request $request): View
    {
        $serial = $request->query('serial');
        $result = $serial ? $this->moku->deviceInfo($serial) : [];

        return view('moku.device-info', [
            'serial' => $serial,
            'success' => $result['success'] ?? false,
            'info' => $result['output'] ?? null,
            'error' => $result['error'] ?? null,
        ]);
    }

    /**
     * Execute custom command
     */
    public function executeCommand(Request $request): View
    {
        $command = $request->input('command', '');
        $result = $command ? $this->moku->execute($command) : [];

        return view('moku.execute', [
            'command' => $command,
            'success' => $result['success'] ?? false,
            'output' => $result['output'] ?? null,
            'error' => $result['error'] ?? null,
        ]);
    }
}
