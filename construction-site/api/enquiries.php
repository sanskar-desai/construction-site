<?php
header('Content-Type: application/json');
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $product_name   = $conn->real_escape_string($data['product_name'] ?? '');
    $product_type   = $conn->real_escape_string($data['product_type'] ?? '');
    $specifications = $conn->real_escape_string($data['specifications'] ?? '');
    $customer_name = $conn->real_escape_string($data['customer_name'] ?? '');
    $phone          = $conn->real_escape_string($data['phone'] ?? '');
    $alt_phone      = $conn->real_escape_string($data['alt_phone'] ?? '');
    $email          = $conn->real_escape_string($data['email'] ?? '');
    $address        = $conn->real_escape_string($data['address'] ?? '');
    $pincode        = $conn->real_escape_string($data['pincode'] ?? '');

    $sql = "INSERT INTO enquiries (product_name, product_type, specifications, customer_name, phone, alt_phone, email, address, pincode)
            VALUES ('$product_name', '$product_type', '$specifications', '$customer_name', '$phone', '$alt_phone', '$email', '$address', '$pincode')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
    exit();
}

if ($method === 'GET') {
    $result = $conn->query("SELECT * FROM enquiries ORDER BY created_at DESC");
    $enquiries = [];
    while ($row = $result->fetch_assoc()) {
        $enquiries[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $enquiries]);
    exit();
}

if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    $conn->query("DELETE FROM enquiries WHERE id = $id");
    echo json_encode(["status" => "success"]);
    exit();
}
?>