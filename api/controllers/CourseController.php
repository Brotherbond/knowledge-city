<?php
declare (strict_types = 1);

namespace api\controllers;

use api\services\CourseService;

class CourseController
{
    private $service;

    public function __construct()
    {
        $this->service = new CourseService();
    }

    public function create(array $data): array
    {
        return $this->service->createCourse($data);
    }

    public function delete(string $id): array
    {
        return $this->service->deleteCourse($id);
    }

    public function get(string $id): array
    {
        return $this->service->getCourseById($id);
    }

    public function getAll($queryParams): array
    {
        return $this->service->getCourses($queryParams);
    }

    public function update(string $id, array $data): array
    {
        return $this->service->updateCourse($id, $data);
    }
}
