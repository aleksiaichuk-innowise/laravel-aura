<?php

declare(strict_types=1);

namespace Aura\Core;

class DataMasker
{
    /** @var string[] */
    protected array $maskFields;

    public function __construct()
    {
        $this->maskFields = config('aura.masking.fields', [
            'password', 'token', 'secret', 'authorization', 'key', 'credential', 'api_key', 'pass'
        ]);
    }

    /**
     * @param array<string, mixed>|mixed $data
     * @return array<string, mixed>|mixed
     */
    public function mask(mixed $data): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->mask($value);
                continue;
            }

            if ($this->shouldMask((string) $key)) {
                $data[$key] = '********';
            }
        }

        return $data;
    }

    /**
     * @param string $sql
     * @return string
     */
    public function maskSql(string $sql): string
    {
        // For now, we rely on masking bindings, but we could add 
        // logic here to mask literal values in queries if they bypass bindings.
        return $sql;
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function shouldMask(string $key): bool
    {
        $key = strtolower($key);
        
        foreach ($this->maskFields as $field) {
            if (str_contains($key, $field)) {
                return true;
            }
        }

        return false;
    }
}
