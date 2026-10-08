<?php

$redis = new Redis();

$redisUrl = getenv('REDIS_URL');

if (!$redisUrl) {
  die('REDIS_URL is not configured');
}

$redis->connect(
  parse_url($redisUrl, PHP_URL_HOST),
  parse_url($redisUrl, PHP_URL_PORT) ?: 6379
);

$redis->auth(parse_url($redisUrl, PHP_URL_PASS));

$redis->set('name', 'TechShyam');

echo $redis->get('name');
