<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/TodoController.php';
require_once __DIR__ . '/../src/HealthController.php';
require_once __DIR__ . '/../src/MetricsController.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$db = new Database();
$todoController = new TodoController($db);
$healthController = new HealthController($db);
$metricsController = new MetricsController($db);

// Gyökér API endpoint
if ($uri === '/api' || $uri === '/api/') {
    echo json_encode([
        'message' => 'DevOps TODO API - Plain PHP',
        'version' => '1.0.0',
        'endpoints' => [
            'GET /api/health' => 'Health check',
            'GET /api/metrics' => 'Prometheus metrics',
            'GET /api/todos' => 'List all todos',
            'GET /api/todos/{id}' => 'Get single todo',
            'POST /api/todos' => 'Create new todo',
            'PUT /api/todos/{id}' => 'Update todo',
            'PATCH /api/todos/{id}/toggle' => 'Toggle todo completion',
            'DELETE /api/todos/{id}' => 'Delete todo'
        ]
    ]);
    exit;
}

// Health endpoint
if ($uri === '/api/health') {
    $healthController->check();
    exit;
}

// Metrics endpoint
if ($uri === '/api/metrics') {
    $metricsController->export();
    exit;
}

// Todos endpointok
if (preg_match('/^\/api\/todos(\/(\d+))?(\/toggle)?$/', $uri, $matches)) {
    $id = isset($matches[2]) ? (int)$matches[2] : null;
    $toggle = isset($matches[3]);

    if ($id && $toggle && $method === 'PATCH') {
        $todoController->toggle($id);
    } elseif ($id && $method === 'GET') {
        $todoController->show($id);
    } elseif ($id && $method === 'PUT') {
        $todoController->update($id);
    } elseif ($id && $method === 'DELETE') {
        $todoController->delete($id);
    } elseif (!$id && $method === 'GET') {
        $todoController->index();
    } elseif (!$id && $method === 'POST') {
        $todoController->create();
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
    exit;
}

// 404 Nem található
http_response_code(404);
echo json_encode(['error' => 'Endpoint not found']);
