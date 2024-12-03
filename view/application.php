<?php
// submit_application.php
header('Content-Type: application/json');

try {
    // Handle file upload
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $resumePath = $uploadDir . basename($_FILES['resume']['name']);
    move_uploaded_file($_FILES['resume']['tmp_name'], $resumePath);

    // Here you would typically save the application data to your database
    // For this example, we'll just return a success response
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>