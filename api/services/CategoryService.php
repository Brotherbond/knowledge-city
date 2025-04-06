<?php
declare (strict_types = 1);

namespace api\services;

use Exception;
use PDO;

class CategoryService
{
    private $db;

    public function __construct()
    {
        $this->db = $GLOBALS['db'];
    }

    /**
     * Calculate the depth of a category in the tree
     */
    private function calculateCategoryDepth(array &$category, array $map): int
    {
        // If depth is already calculated, return it
        if (isset($category['depth']) && $category['depth'] > 0) {
            return $category['depth'];
        }

        // If this is a root category (no parent), depth is 1
        if ($category['parent_id'] === null) {
            $category['depth'] = 1;
            return 1;
        }

        // If parent exists, depth is parent's depth + 1
        if (isset($map[$category['parent_id']])) {
            $parentDepth       = $this->calculateCategoryDepth($map[$category['parent_id']], $map);
            $category['depth'] = $parentDepth + 1;
            return $category['depth'];
        }

        // If parent doesn't exist, treat as root category
        $category['depth'] = 1;
        return 1;
    }

    private function calculateTotalCourses(array &$categories): int
    {
        $total = 0;

        foreach ($categories as &$category) {
            if (! empty($category['children'])) {
                $childTotal = $this->calculateTotalCourses($category['children']);
                $category['count_of_courses'] += $childTotal;
            }

            $total += $category['count_of_courses'];
        }

        return $total;
    }

    public function createCategory(array $data): array
    {
        if (empty($data['name'])) {
            throw new Exception("Category name is required");
        }

        $stmt = $this->db->prepare("
            INSERT INTO categories (name, parent_id)
            VALUES (:name, :parent_id)
        ");

        $stmt->bindParam(':name', $data['name']);
        $parent_idId = $data['parent_id'] ?? null;
        $stmt->bindParam(':parent_id', $parent_idId, $parent_idId ? PDO::PARAM_STR : PDO::PARAM_NULL);

        $stmt->execute();
        $id = $this->db->lastInsertId();

        return $this->getCategoryById((string) $id);
    }

    public function deleteCategory(string $id): array
    {
        // Check if category exists
        $category = $this->getCategoryById($id);

        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();

        return ['message' => 'Category deleted successfully', 'id' => $id];
    }

    public function getAllCategories(): array
    {
        $stmt = $this->db->prepare("
            SELECT
                c.*,
                COUNT(DISTINCT co.id) as count_of_courses
            FROM
                categories c
            LEFT JOIN
                courses co ON co.category_id = c.id
            GROUP BY
                c.id
            ORDER BY
                c.name
        ");
        $stmt->execute();
        $categories = $stmt->fetchAll();
    
        // Build the tree
        $tree = [];
        $map  = [];
    
        // First pass: create a map of id => category and add depth information
        foreach ($categories as $category) {
            $category['children'] = [];
            $category['depth'] = 0; // Initialize depth for all categories
            $category['count_of_courses'] = (int)$category['count_of_courses']; // Ensure count is an integer
            $map[$category['id']] = $category;
        }
    
        // Second pass: calculate depth for each category
        foreach ($map as $id => &$category) {
            $this->calculateCategoryDepth($category, $map);
        }
        unset($category);
    
        // Create a copy of the map to avoid reference issues
        $mapCopy = [];
        foreach ($map as $id => $category) {
            $mapCopy[$id] = $category;
        }
    
        // Third pass: build the tree (only include categories up to depth 4)
        foreach ($mapCopy as $id => $category) {
            if ($category['parent_id'] === null) {
                $tree[] = &$map[$id];
            } else if (isset($map[$category['parent_id']]) && $category['depth'] <= 4) {
                $map[$category['parent_id']]['children'][] = &$map[$id];
            }
        }
    
        // Fourth pass: calculate total courses (including children)
        $this->calculateTotalCourses($tree);
    
        return $tree;
    }
    
    public function getCategoryById(string $id): array
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();

        $category = $stmt->fetch();
        if (! $category) {
            throw new Exception("Category not found");
        }

        return $category;
    }

    public function updateCategory(string $id, array $data): array
    {
        if (empty($data['name'])) {
            throw new Exception("Category name is required");
        }

        // Check if category exists
        $this->getCategoryById($id);

        $stmt = $this->db->prepare("
            UPDATE categories
            SET name = :name, parent_id = :parent_id
            WHERE id = :id
        ");

        $stmt->bindParam(':name', $data['name']);
        $parent_idId = $data['parent_id'] ?? null;
        $stmt->bindParam(':parent_id', $parent_idId, $parent_idId ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);

        $stmt->execute();

        return $this->getCategoryById($id);
    }
}
