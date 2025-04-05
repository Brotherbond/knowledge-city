<?php

declare(strict_types=1);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../../bootstrap.php';

try {
    // Load JSON data
    $categoriesFile = __DIR__ . '/data/categories.json';
    $coursesFile = __DIR__ . '/data/course_list.json';
    
    if (!file_exists($categoriesFile)) {
        die("Categories file not found: $categoriesFile\n");
    }
    
    if (!file_exists($coursesFile)) {
        die("Courses file not found: $coursesFile\n");
    }
    
    $categoriesJson = file_get_contents($categoriesFile);
    $categories = json_decode($categoriesJson, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Error parsing categories JSON: " . json_last_error_msg() . "\n");
    }
    
    $coursesJson = file_get_contents($coursesFile);
    $courses = json_decode($coursesJson, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Error parsing courses JSON: " . json_last_error_msg() . "\n");
    }
    
    echo "JSON data loaded successfully.\n";
    echo "Found " . count($categories) . " top-level categories.\n";
    echo "Found " . count($courses) . " courses.\n";
    
    // Start transaction
    $db->beginTransaction();
    
    // Import categories
    importCategories($categories, $db);
    echo "Categories imported successfully.\n";
    
    // Import courses
    importCourses($courses, $db);
    echo "Courses imported successfully.\n";
    
    // Commit transaction
    $db->commit();
    
    echo "All data imported successfully!\n";
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if (isset($db)) {
        $db->rollBack();
    }
    die("Database error: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($db)) {
        $db->rollBack();
    }
    die("Error: " . $e->getMessage() . "\n");
}

/**
 * Import categories
 */
function importCategories(array $categories, PDO $db): void {
    foreach ($categories as $category) {
        // Check if category has required fields
        if (!isset($category['id']) || !isset($category['name'])) {
            echo "Warning: Skipping category with missing required fields.\n";
            continue;
        }
        
        try {
            // Check if category already exists
            $checkStmt = $db->prepare("SELECT id FROM categories WHERE id = :id");
            $checkStmt->bindParam(':id', $category['id']);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                // Update existing category
                $stmt = $db->prepare("
                    UPDATE categories 
                    SET name = :name, parent_id = :parent_id, description = :description
                    WHERE id = :id
                ");
                echo "Updating category: {$category['name']} (ID: {$category['id']})\n";
            } else {
                // Insert new category
                $stmt = $db->prepare("
                    INSERT INTO categories (id, name, parent_id, description) 
                    VALUES (:id, :name, :parent_id, :description)
                ");
                echo "Inserting category: {$category['name']} (ID: {$category['id']})\n";
            }
            
            $stmt->bindParam(':id', $category['id']);
            $stmt->bindParam(':name', $category['name']);
            $parentId = $category['parent'] ?? null;
            $stmt->bindParam(':parent_id', $parentId, $parentId === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $description = $category['description'] ?? null;
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Error processing category {$category['id']}: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}

/**
 * Import courses
 */
function importCourses(array $courses, PDO $db): void {
    foreach ($courses as $course) {
        // Check if course has required fields
        if (!isset($course['course_id']) || !isset($course['title']) || !isset($course['category_id'])) {
            echo "Warning: Skipping course with missing required fields.\n";
            continue;
        }
        
        try {
            // Check if course already exists
            $checkStmt = $db->prepare("SELECT id FROM courses WHERE id = :id");
            $checkStmt->bindParam(':id', $course['course_id']);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                // Update existing course
                $stmt = $db->prepare("
                    UPDATE courses 
                    SET name = :name, description = :description, preview = :preview, category_id = :category_id
                    WHERE id = :id
                ");
                echo "Updating course: {$course['title']} (ID: {$course['course_id']})\n";
            } else {
                // Insert new course
                $stmt = $db->prepare("
                    INSERT INTO courses (id, name, description, preview, category_id) 
                    VALUES (:id, :name, :description, :preview, :category_id)
                ");
                echo "Inserting course: {$course['title']} (ID: {$course['course_id']})\n";
            }
            
            $stmt->bindParam(':id', $course['course_id']);
            $stmt->bindParam(':name', $course['title']);
            $description = $course['description'] ?? null;
            $stmt->bindParam(':description', $description);
            $preview = $course['image_preview'] ?? null;
            $stmt->bindParam(':preview', $preview);
            $stmt->bindParam(':category_id', $course['category_id']);
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Error processing course {$course['course_id']}: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}