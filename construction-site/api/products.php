<?php
header('Content-Type: application/json');
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $result = $conn->query("SELECT * FROM products ORDER BY id DESC");
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $products]);
    exit();
}

if ($method === 'POST') {
    $title       = $conn->real_escape_string($_POST['title'] ?? '');
    $category    = $conn->real_escape_string($_POST['category'] ?? '');
    $description = $conn->real_escape_string($_POST['description'] ?? '');

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = 'uploads/' . $filename;
            $conn->query("INSERT INTO products (title, category, description, image_path) VALUES ('$title', '$category', '$description', '$imagePath')");
            echo json_encode(["status" => "success", "message" => "Product added successfully"]);
            exit();
        }
    }
    echo json_encode(["status" => "error", "message" => "Failed to upload photo"]);
    exit();
}

if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    $conn->query("DELETE FROM products WHERE id = $id");
    echo json_encode(["status" => "success"]);
    exit();
}
?>