<?php
declare (strict_types = 1);
namespace api\routes;

use api\controllers\CategoryController;
use api\controllers\CourseController;
use Exception;

class Router
{
    private array $controllerMap = [
        'categories' => CategoryController::class,
        'courses'    => CourseController::class,
    ];

    public function route(string $controllerName, string $method, array $queryParams, ?string $id): array
    {
        // Handle preflight OPTIONS request
        if ($method === 'OPTIONS') {
            http_response_code(204);
            return [];
        }

        if (! array_key_exists($controllerName, $this->controllerMap)) {
            throw new Exception('Controller not found', 404);
        }

        $controllerClass = $this->controllerMap[$controllerName];
        $controller      = new $controllerClass();

        return $this->routeController($controller, $method, $queryParams, $id);
    }

    private function routeController($controller, string $method, array $queryParams, ?string $id): array
    {
        $inputData = file_get_contents('php://input');
        $data      = $inputData ? json_decode($inputData, true) : [];

        switch ($method) {
            case 'GET':
                if ($id) {
                    return $controller->get((string) $id);
                }
                return $controller->getAll($queryParams);
            case 'POST':
                return $controller->create($data);

            case 'PUT':
                if (! $id) {
                    throw new Exception('ID is required for update', 400);
                }
                return $controller->update((string) $id, $data);

            case 'DELETE':
                if (! $id) {
                    throw new Exception('ID is required for delete', 400);
                }
                return $controller->delete((string) $id);
            default:
                throw new Exception('HTTP Method not supported', 405);
        }
    }
}
