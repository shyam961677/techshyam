<?php
/**
 * Simple REST-ish JSON API for portfolio data
 * Routes: /api/profile, /api/skills, /api/services, /api/projects, /api/blogs
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Cache-Control: public, max-age=300'); // 5 min cache

define('DATA_PATH', __DIR__ . '/../data/');

function readJson(string $file): mixed {
    $path = DATA_PATH . $file;
    if (!file_exists($path)) return null;
    return json_decode(file_get_contents($path), true);
}

// Route from query string or PATH_INFO
$route = trim($_GET['endpoint'] ?? ltrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/'));
$route = basename($route); // safety

$data = match ($route) {
    'profile'  => (function() {
        $p = readJson('profile.json');
        // Remove sensitive-ish fields if needed
        return $p;
    })(),
    'skills'   => array_map(static function ($skill) {
        unset($skill['percent']);
        return $skill;
    }, readJson('skills.json') ?? []),
    'services' => array_values(array_filter(readJson('services.json') ?? [], fn($s) => !empty($s['active']))),
    'projects' => array_values(array_filter(readJson('projects.json') ?? [], fn($p) => !empty($p['active']))),
    'blogs'    => array_values(array_filter(readJson('blogs.json') ?? [], fn($b) => !empty($b['published']))),
    default    => null,
};

if ($data === null) {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found. Available: profile, skills, services, projects, blogs']);
    exit;
}

echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
