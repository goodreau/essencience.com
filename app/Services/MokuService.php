<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MokuService
{
    /**
     * Execute a mokucli command and return the result
     */
    public function execute(string $command, array $args = []): array
    {
        $fullCommand = ['mokucli', $command, ...$args];
        
        $process = new Process($fullCommand);
        $process->setTimeout(30);
        
        try {
            $process->mustRun();
            return [
                'success' => true,
                'output' => $process->getOutput(),
                'command' => implode(' ', $fullCommand),
            ];
        } catch (ProcessFailedException $e) {
            return [
                'success' => false,
                'output' => $process->getErrorOutput(),
                'command' => implode(' ', $fullCommand),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * List available Moku devices
     */
    public function listDevices(): array
    {
        return $this->execute('list');
    }

    /**
     * Get device info
     */
    public function deviceInfo(string $serial): array
    {
        return $this->execute('info', [$serial]);
    }

    /**
     * Get oscilloscope data
     */
    public function getOscilloscopeData(string $serial): array
    {
        return $this->execute('oscilloscope', ['read', $serial]);
    }

    /**
     * Set device mode
     */
    public function setMode(string $serial, string $mode): array
    {
        return $this->execute('mode', [$serial, $mode]);
    }

    /**
     * Parse output into structured format
     */
    public function parseOutput(string $output): array
    {
        $lines = array_filter(explode("\n", $output));
        return array_map(fn($line) => trim($line), $lines);
    }

    /**
     * Check if mokucli is installed
     */
    public function isMokuCliInstalled(): bool
    {
        $process = new Process(['which', 'mokucli']);
        $process->run();
        return $process->isSuccessful();
    }
}
