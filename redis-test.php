<?php

$redis = new Redis();

$redis->connect(
    getenv('REDIS_HOST') ?: 'redis',
    (int)(getenv('REDIS_PORT') ?: 6379)
);

$redis->set('name', 'TechShyam');

echo $redis->get('name');