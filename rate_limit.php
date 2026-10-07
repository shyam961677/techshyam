<?php

/**
 * Rate limiting via Redis.
 * Falls back silently when Redis is unavailable (e.g. local XAMPP without
 * the Redis extension, or when the Redis container isn't reachable).
 */

function redisConnection(): ?Redis
{
    // Redis PHP extension not loaded → skip
    if (!extension_loaded('redis')) {
        return null;
    }

    try {
        $redis = new Redis();
        $host  = getenv('REDIS_HOST') ?: '127.0.0.1';
        $port  = (int)(getenv('REDIS_PORT') ?: 6379);

        // 1-second connect timeout so a missing server fails fast
        if (!$redis->connect($host, $port, 1.0)) {
            return null;
        }

        return $redis;
    } catch (Throwable $e) {
        // Redis server unreachable or any other error → degrade gracefully
        return null;
    }
}

function rateLimit(
    string $keyPrefix = 'api',
    int    $limit     = 60,
    int    $window    = 60
): void {
    $redis = redisConnection();

    // No Redis available — skip rate limiting entirely
    if ($redis === null) {
        return;
    }

    $ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = "rate_limit:{$keyPrefix}:{$ip}";

    try {
        $count = $redis->incr($key);

        if ($count === 1) {
            $redis->expire($key, $window);
        }

        $ttl = $redis->ttl($key);

        if ($count > $limit) {
            header('HTTP/1.1 429 Too Many Requests');
            header('Content-Type: application/json');
            header('Retry-After: ' . max($ttl, 1));

            echo json_encode([
                'success'     => false,
                'message'     => 'Too many requests. Please try again later.',
                'retry_after' => max($ttl, 1) . ' sec.',
            ]);

            exit;
        }
    } catch (Throwable $e) {
        // Redis command failed mid-flight → degrade gracefully
    }
}
