<?php

namespace App\Support;

class ReservedAccountNames
{
    public static function isBlocked(?string $name): bool
    {
        $normalized = self::normalize($name);

        if ($normalized === '') {
            return false;
        }

        return in_array($normalized, self::blockedNames(), true);
    }

    public static function sanitize(string $name): string
    {
        if (! self::isBlocked($name)) {
            return $name;
        }

        return 'user-'.strtolower(substr(md5($name.microtime(true)), 0, 6));
    }

    public static function blockedNames(): array
    {
        $defaults = [
            'admin',
            'administrator',
            'root',
            'superadmin',
            'owner',
            'support',
            'staff',
            'moderator',
            'mod',
            'system',
            'null',
            'undefined',
        ];

        $configValues = (array) config('services.account.blocked_names', []);
        $normalized = array_map(fn ($item) => self::normalize((string) $item), array_merge($defaults, $configValues));

        return array_values(array_unique(array_filter($normalized)));
    }

    private static function normalize(?string $value): string
    {
        $value = mb_strtolower((string) $value);

        return preg_replace('/[^a-z0-9]/', '', $value) ?? '';
    }
}
