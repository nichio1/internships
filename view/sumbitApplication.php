<?php
require_once 'config.php';

class ApplicationManager {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    public function submitApplication($data, $files) {
        try {
            // Validate required fields
            $required_fields = ['firstName', 'lastName', 'email', 'phone', 'education', 'internship_id'];
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Missing required field: " . $field);
                }
            }

            // Handle resume upload
            $resume_path = '';
            if (isset($files['resume']) && $files['resume']['error'] == 0) {
                $target_dir = "../uploads/resumes/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                // Validate file type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $files['resume']['tmp_name']);
                finfo_close($finfo);

                if ($mime_type !== 'application/pdf') {
                    throw new Exception("Only PDF files are allowed");
                }

                $file_extension = pathinfo($files['resume']['name'], PATHINFO_EXTENSION);
                $new_filename = uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;

                if (!move_uploaded_file($files['resume']['tmp_name'], $target_file)) {
                    throw new Exception("Error uploading resume");
                }

                $resume_path = $target_file;
            } else {
                throw new Exception("Resume is required");
            }

            // Clean input data
            $first_name = $this->conn->real_escape_string($data['firstName']);
            $last_name = $this->conn->real_escape_string($data['lastName']);
            $email = $this->conn->real_escape_string($data['email']);
            $phone = $this->conn->real_escape_string($data['phone']);
            $education = $this->conn->real_escape_string($data['education']);
            $cover_letter = isset($data['coverLetter']) ? $this->conn->real_escape_string($data['coverLetter']) : '';
            $internship_id = (int)$data['internship_id'];

            // Insert application into database
            $sql = "INSERT INTO internship_applications (
                internship_id, 
                first_name, 
                last_name, 
                email, 
                phone, 
                education, 
                resume_path, 
                cover_letter,
                application_date,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')";

            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            $stmt->bind_param(
                "isssssss",
                $internship_id,
                $first_name,
                $last_name,
                $email,
                $phone,
                $education,
                $resume_path,
                $cover_letter
            );

            if (!$stmt->execute()) {
                // If file was uploaded but database insert failed, delete the uploaded file
                if (!empty($resume_path) && file_exists($resume_path)) {
                    unlink($resume_path);
                }
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $stmt->close();
            return ['success' => true, 'message' => 'Application submitted successfully'];

        } catch (Exception $e) {
            error_log("Error submitting application: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');
    
    $applicationManager = new ApplicationManager();
    $result = $applicationManager->submitApplication($_POST, $_FILES);
    
    echo json_encode($result);
    exit();
}
?>