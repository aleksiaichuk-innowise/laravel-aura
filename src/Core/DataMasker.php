<?php

declare(strict_types=1);

namespace Aura\Core;

class DataMasker
{
    protected array $maskFields = ['password', 'token', 'secret', 'authorization', 'key', 'credential'];

    /**
     * @param array $data
     * @return array
     */
    public function mask(array $data): array
    {
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
        // Simple regex to mask values in common SQL patterns if needed.
        // For this showcase, we mostly mask bindings, but we can add more logic here.
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
