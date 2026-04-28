<?php

if (!function_exists('messaging_feature')) {
    function messaging_feature(string $key, bool $default = false): bool
    {
        if ($key !== 'enabled' && !config('messaging.features.enabled', true)) {
            return false;
        }

        return (bool) config("messaging.features.{$key}", $default);
    }
}

if (!function_exists('messaging_features')) {
    function messaging_features(): array
    {
        $features = config('messaging.features', []);

        if (!($features['enabled'] ?? true)) {
            return array_map(fn () => false, $features);
        }

        return $features;
    }
}
