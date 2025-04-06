<?php
declare (strict_types = 1);

namespace api\controllers;

use api\services\CategoryService;

class CategoryController
{
    private $service;

    public function __construct()
    {
        $this->service = new CategoryService();
    }

    public function create(array $data): array
    {
        return $this->service->createCategory($data);
    }

    public function get(string $id): array
    {
        return $this->service->getCategoryById($id);
    }

    public function getAll(): array
    {
        return $this->service->getAllCategories();
    }

    public function update(string $id, array $data): array
    {
        return $this->service->updateCategory($id, $data);
    }

    public function delete(string $id): array
    {
        return $this->service->deleteCategory($id);
    }
}
