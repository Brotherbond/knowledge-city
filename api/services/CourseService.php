<?php
declare (strict_types = 1);

namespace api\services;

use Exception;
use PDO;

class CourseService
{
    private $db;

    public function __construct()
    {
        $this->db = $GLOBALS['db'];
    }

    public function createCourse(array $data): array
    {
        if (empty($data['name'])) {
            throw new Exception("Course name is required");
        }

        if (empty($data['category_id'])) {
            throw new Exception("Category ID is required");
        }

        $stmt = $this->db->prepare("
            INSERT INTO courses (name, description, category_id)
            VALUES (:name, :description, :category_id)
        ");

        $stmt->bindParam(':name', $data['name']);
        $description = $data['description'] ?? null;
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category_id', $data['category_id'], PDO::PARAM_INT);

        $stmt->execute();
        $id = $this->db->lastInsertId();

        return $this->getCourseById((string) $id);
    }

    public function deleteCourse(string $id): array
    {
        // Check if course exists
        $course = $this->getCourseById($id);

        $stmt = $this->db->prepare("DELETE FROM courses WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return ['message' => 'Course deleted successfully', 'id' => $id];
    }

    public function getCourseById(string $id): array
    {
        $stmt = $this->db->prepare("
            SELECT
                c.*,
                cat.name as main_category_name
            FROM
                courses c
            JOIN
                categories cat ON c.category_id = cat.id
            WHERE
                c.id = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $course = $stmt->fetch();
        if (! $course) {
            throw new Exception("Course not found");
        }

        return $course;
    }

    public function getCourses($queryParams): array
    {
        $params            = [];
        $categoryId        = $queryParams['category_id'] ?? null;
        $categoryFilterSql = "";

        if ($categoryId) {
            // Get all subcategories including the main category
            $categoryStmt = $this->db->prepare("
                WITH RECURSIVE category_tree AS (
                    SELECT id FROM categories WHERE id = :category_id
                    UNION ALL
                    SELECT c.id FROM categories c
                    INNER JOIN category_tree ct ON c.parent_id = ct.id
                )
                SELECT id FROM category_tree
            ");
            $categoryStmt->execute([':category_id' => $categoryId]);

            $categoryIds = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

            if (! empty($categoryIds)) {
                $placeholders      = implode(',', array_fill(0, count($categoryIds), '?'));
                $categoryFilterSql = "WHERE c.category_id IN ($placeholders)";
                $params            = $categoryIds;
            }
        }
        $stmt = $this->db->prepare("
            SELECT
                c.*,
                cat.name as main_category_name
            FROM
                courses c
            JOIN
                categories cat ON c.category_id = cat.id
            $categoryFilterSql
            ORDER BY
                c.name
        ");
        if (! empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    public function updateCourse(string $id, array $data): array
    {
        // Check if course exists
        $this->getCourseById($id);

        $stmt = $this->db->prepare("
            UPDATE courses
            SET name = :name, description = :description, category_id = :category_id
            WHERE id = :id
        ");

        $stmt->bindParam(':name', $data['name']);
        $description = $data['description'] ?? null;
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category_id', $data['category_id'], PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return $this->getCourseById($id);
    }
}
