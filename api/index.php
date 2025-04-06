<?php
declare (strict_types = 1);
namespace api;

use api\routes\Router;
use Exception;

// Load dependencies
require_once __DIR__ . '/bootstrap.php';

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

header('Content-Type: application/json');

// Parse request
$requestUri    = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];
$uriParts      = explode('/', trim(parse_url($requestUri, PHP_URL_PATH), '/'));

// Get query parameters
$queryParams = [];
if (isset($_SERVER['QUERY_STRING'])) {
    parse_str($_SERVER['QUERY_STRING'], $queryParams);
}

// Remove 'api' prefix if present
if (isset($uriParts[0]) && $uriParts[0] === 'api') {
    array_shift($uriParts);
}

$controller = $uriParts[0] ?? '';
$id         = (isset($uriParts[1]) && ! ! $uriParts[1]) ? $uriParts[1] : null;

try {
    $router   = new Router();
    $response = $router->route($controller, $requestMethod, $queryParams, $id);

    if (! empty($response) || is_array($response)) {
        echo json_encode($response);
    } else {
        http_response_code(204); // No Content
    }
} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode(['error' => $e->getMessage()]);
}
