<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class EnvFileService
{
    public function path(): string
    {
        return base_path('.env');
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $values = $this->readAll();

        return array_key_exists($key, $values) ? $values[$key] : $default;
    }

    public function getMany(array $keys): array
    {
        $all = $this->readAll();
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $all[$key] ?? '';
        }

        return $result;
    }

    public function getTabValues(string $tab): array
    {
        $tabConfig = config("website_settings.tabs.{$tab}");

        if (!$tabConfig) {
            return [];
        }

        $keys = array_keys($tabConfig['keys']);
        $values = $this->getMany($keys);

        foreach ($tabConfig['keys'] as $key => $meta) {
            if (!empty($meta['sensitive']) && ($values[$key] ?? '') !== '') {
                $values[$key . '_masked'] = $this->maskSecret($values[$key]);
                $values[$key] = '';
            }
        }

        return $values;
    }

    /**
     * @param  array<string, string|null>  $updates
     */
    public function setMany(array $updates, array $sensitiveKeys = []): void
    {
        $this->backup();

        $lines = file($this->path(), FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            throw new RuntimeException('Unable to read .env file.');
        }

        $updatedKeys = [];

        foreach ($lines as $index => $line) {
            if ($this->isCommentOrEmpty($line)) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key] = explode('=', $line, 2);
            $key = trim($key);

            if (!array_key_exists($key, $updates)) {
                continue;
            }

            if (in_array($key, config('website_settings.readonly_keys', []), true)) {
                continue;
            }

            if (in_array($key, $sensitiveKeys, true) && ($updates[$key] === null || $updates[$key] === '')) {
                continue;
            }

            $lines[$index] = $key . '=' . $this->formatValue($updates[$key]);
            $updatedKeys[] = $key;
        }

        foreach ($updates as $key => $value) {
            if (in_array($key, $updatedKeys, true)) {
                continue;
            }

            if (in_array($key, config('website_settings.readonly_keys', []), true)) {
                continue;
            }

            if (in_array($key, $sensitiveKeys, true) && ($value === null || $value === '')) {
                continue;
            }

            $lines[] = $key . '=' . $this->formatValue($value);
        }

        $written = file_put_contents($this->path(), implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX);

        if ($written === false) {
            throw new RuntimeException('Unable to write .env file. Check file permissions.');
        }
    }

    public function backup(): string
    {
        $backupDir = storage_path('app/env-backups');

        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filename = '.env.' . now()->format('Y-m-d_His') . '.bak';
        $destination = $backupDir . DIRECTORY_SEPARATOR . $filename;

        if (!File::copy($this->path(), $destination)) {
            throw new RuntimeException('Failed to create .env backup.');
        }

        Log::info('Env file backed up', ['path' => $destination, 'user_id' => auth()->id()]);

        return $destination;
    }

    public function listBackups(): array
    {
        $backupDir = storage_path('app/env-backups');

        if (!File::isDirectory($backupDir)) {
            return [];
        }

        return collect(File::files($backupDir))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'size' => $this->formatBytes($file->getSize()),
                'modified' => date('d M Y, h:i A', $file->getMTime()),
            ])
            ->values()
            ->all();
    }

    public function readAll(): array
    {
        if (!File::exists($this->path())) {
            return [];
        }

        $values = [];

        foreach (file($this->path(), FILE_IGNORE_NEW_LINES) as $line) {
            if ($this->isCommentOrEmpty($line) || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $values[$key] = $this->parseValue(trim($value));
        }

        return $values;
    }

    protected function isCommentOrEmpty(string $line): bool
    {
        $trimmed = trim($line);

        return $trimmed === '' || str_starts_with($trimmed, '#');
    }

    protected function parseValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            return stripcslashes(substr($value, 1, -1));
        }

        return $value;
    }

    protected function formatValue(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (in_array(strtolower($value), ['true', 'false'], true)) {
            return strtolower($value);
        }

        if (is_numeric($value) && !str_contains($value, ' ')) {
            return (string) $value;
        }

        if (preg_match('/[\s#"\'\\\\]/', $value) || str_contains($value, '${')) {
            return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
        }

        return $value;
    }

    protected function maskSecret(string $value): string
    {
        $length = strlen($value);

        if ($length <= 4) {
            return str_repeat('•', $length);
        }

        return substr($value, 0, 2) . str_repeat('•', max(4, $length - 4)) . substr($value, -2);
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $pow = $bytes > 0 ? floor(log($bytes) / log(1024)) : 0;
        $pow = min((int) $pow, count($units) - 1);

        return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
    }
}
