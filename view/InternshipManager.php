<?php
require_once 'config.php';

class InternshipManager {
    private $db;
    private $conn;
    private const UPLOAD_DIR = "../uploads/";
    private const RESUME_DIR = "../uploads/resumes/";
    private const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
    private const MAX_FILE_SIZE = 5242880; // 5MB


    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();

        if (!$this->conn) {
            throw new Exception("Database connection failed: " . mysqli_connect_error());
        }

        $this->initializeDirectories();
    }
    
    private function initializeDirectories() {
        if (!file_exists(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0777, true);
        }
        if (!file_exists(self::RESUME_DIR)) {
            mkdir(self::RESUME_DIR, 0777, true);
        }
    }

    private function validateFile($file, $type = 'image') {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed");
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            throw new Exception("File size exceeds limit");
        }

        if ($type === 'image') {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime_type, self::ALLOWED_IMAGE_TYPES)) {
                throw new Exception("Invalid file type. Only JPG, PNG and GIF are allowed.");
            }
        } elseif ($type === 'pdf') {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if ($mime_type !== 'application/pdf') {
                throw new Exception("Invalid file type. Only PDF files are allowed.");
            }
        }

        return true;
    }

    private function uploadFile($file, $prefix = '', $type = 'image') {
        $target_dir = $type === 'image' ? self::UPLOAD_DIR : self::RESUME_DIR;
        $extension = $type === 'image' ? 
            strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) : 
            'pdf';
        
        $filename = $prefix . uniqid() . '.' . $extension;
        $target_file = $target_dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target_file)) {
            throw new Exception("Failed to move uploaded file");
        }

        return $target_file;
    }
    
    public function addInternship($data, $file) {
        try {
            // Validate required fields with specific messages
            $errors = [];
            
            if(empty($data['title']) || strlen($data['title']) < 3) {
                $errors[] = "Title must be at least 3 characters long";
            }
            if(empty($data['description']) || strlen($data['description']) < 10) {
                $errors[] = "Description must be at least 10 characters long";
            }
            if(empty($data['location'])) {
                $errors[] = "Location is required";
            }
            if(empty($data['duration'])) {
                $errors[] = "Duration is required";
            }
            if(empty($data['field'])) {
                $errors[] = "Field is required";
            }
    
            if (!empty($errors)) {
                throw new Exception(implode(", ", $errors));
            }
    
            // Clean input data
            $title = $this->db->cleanInput($data['title']);
            $description = $this->db->cleanInput($data['description']);
            $location = $this->db->cleanInput($data['location']);
            $duration = $this->db->cleanInput($data['duration']);
            $field = $this->db->cleanInput($data['field']);
            
            // Handle file upload
            $target_dir = "../uploads/";
            $image_path = "";
            
            // Create uploads directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            // Process image upload if provided
            if(isset($file["image"]) && $file["image"]["error"] == 0) {
                // Verify if it's actually an image
                $check = getimagesize($file["image"]["tmp_name"]);
                if($check === false) {
                    throw new Exception("File is not an image.");
                }
                
                // Generate unique filename
                $file_extension = pathinfo($file["image"]["name"], PATHINFO_EXTENSION);
                $new_filename = uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                // Move uploaded file
                if (!move_uploaded_file($file["image"]["tmp_name"], $target_file)) {
                    throw new Exception("Error uploading file.");
                }
                
                $image_path = $target_file;
            }
            
            // Prepare and execute SQL statement
            $sql = "INSERT INTO internship (Title, Description, Location, Duration, Field, Image) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("ssssss", $title, $description, $location, $duration, $field, $image_path);
            
            if (!$stmt->execute()) {
                // If file was uploaded but database insert failed, delete the uploaded file
                if (!empty($image_path) && file_exists($image_path)) {
                    unlink($image_path);
                }
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $stmt->close();
            return true;
    
        } catch (Exception $e) {
            error_log("Error adding internship: " . $e->getMessage());
            return false;
        }
    }

    public function submitApplication($data, $file) {
        try {
            // Validate required fields
            $errors = [];
            
            // Validate internship_id first
            if(empty($data['internship_id']) || !is_numeric($data['internship_id'])) {
                $errors[] = "Invalid or missing internship ID";
            }
            
            if(empty($data['firstName']) || strlen($data['firstName']) < 2) {
                $errors[] = "First name must be at least 2 characters long";
            }
            
            if(empty($data['lastName']) || strlen($data['lastName']) < 2) {
                $errors[] = "Last name must be at least 2 characters long";
            }
            
            if(empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email address";
            }
            
            if(empty($data['phone']) || !preg_match("/^[0-9\-\+\(\) ]{8,20}$/", $data['phone'])) {
                $errors[] = "Invalid phone number";
            }
            
            if(empty($data['education'])) {
                $errors[] = "Education level is required";
            }
            
            if(empty($file['resume']) || $file['resume']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = "Resume upload is required and must be successful";
            }
            
            if (!empty($errors)) {
                throw new Exception(implode(", ", $errors));
            }
            
            // Verify internship exists in the database
            $stmt = $this->conn->prepare("SELECT id FROM internship WHERE id = ?");
            $stmt->bind_param("i", $internship_id);
            $internship_id = intval($data['internship_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                throw new Exception("Selected internship does not exist");
            }
            $stmt->close();
            
            // Clean input data
            $first_name = $this->db->cleanInput($data['firstName']);
            $last_name = $this->db->cleanInput($data['lastName']);
            $email = $this->db->cleanInput($data['email']);
            $phone = $this->db->cleanInput($data['phone']);
            $education = $this->db->cleanInput($data['education']);
            $cover_letter = !empty($data['coverLetter']) ? $this->db->cleanInput($data['coverLetter']) : '';
            
            // Handle resume file upload
            $target_dir = "../uploads/resumes/";
            
            // Create uploads directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            // Verify if it's a PDF
            $file_extension = strtolower(pathinfo($file['resume']['name'], PATHINFO_EXTENSION));
            if ($file_extension !== 'pdf') {
                throw new Exception("Resume must be a PDF file");
            }
            
            // Generate unique filename
            $new_filename = uniqid() . '_resume_' . $first_name . '_' . $last_name . '.pdf';
            $target_file = $target_dir . $new_filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['resume']['tmp_name'], $target_file)) {
                throw new Exception("Error uploading resume file");
            }
            
            // Prepare and execute SQL statement
            $sql = "INSERT INTO internship_applications 
                    (internship_id, first_name, last_name, email, phone, 
                    education, resume_path, cover_letter, application_date, fk_internship) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?)";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("isssssssi", 
                $internship_id, 
                $first_name, 
                $last_name, 
                $email, 
                $phone, 
                $education, 
                $target_file, 
                $cover_letter,
                $internship_id
            );
            
            if (!$stmt->execute()) {
                // If database insert fails, delete the uploaded file
                if (file_exists($target_file)) {
                    unlink($target_file);
                }
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $stmt->close();
            return true;
    
        } catch (Exception $e) {
            // Log error 
            error_log("Error submitting application: " . $e->getMessage());
            
            // If file was uploaded, remove it
            if (isset($target_file) && file_exists($target_file)) {
                unlink($target_file);
            }
            
            return $e->getMessage();
        }
    }
        
    
    public function updateInternship($id, $data, $file) {
        try {
            // Clean input data
            $title = $this->db->cleanInput($data['title']);
            $description = $this->db->cleanInput($data['description']);
            $location = $this->db->cleanInput($data['location']);
            $duration = $this->db->cleanInput($data['duration']);
            $field = $this->db->cleanInput($data['field']);
            
            // Handle file upload
            $target_dir = "../uploads/";
            
            // Create uploads directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            // Get current image path
            $stmt = $this->conn->prepare("SELECT Image FROM internship WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $current_data = $result->fetch_assoc();
            $image_path = $current_data['Image'];

            // Handle new image upload if provided
            if(isset($file["image"]) && $file["image"]["error"] == 0) {
                $check = getimagesize($file["image"]["tmp_name"]);
                if($check === false) {
                    throw new Exception("File is not an image.");
                }

                $file_extension = pathinfo($file["image"]["name"], PATHINFO_EXTENSION);
                $new_filename = uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                if (!move_uploaded_file($file["image"]["tmp_name"], $target_file)) {
                    throw new Exception("Error uploading file.");
                }
                
                // Delete old image if exists
                if(!empty($current_data['Image']) && file_exists($current_data['Image'])) {
                    unlink($current_data['Image']);
                }
                
                $image_path = $target_file;
            }
            
            // Prepare and execute SQL statement
            $sql = "UPDATE internship SET 
                    Title = ?, 
                    Description = ?, 
                    Location = ?, 
                    Duration = ?, 
                    Field = ?, 
                    Image = ?
                    WHERE id = ?";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("ssssssi", $title, $description, $location, $duration, $field, $image_path, $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $stmt->close();
            return true;

        } catch (Exception $e) {
            // Log error and return false
            error_log("Error updating internship: " . $e->getMessage());
            return false;
        }
    }

    
    public function deleteInternship($id) {
        try {
            // First, get the image path to delete the file
            $stmt = $this->conn->prepare("SELECT Image FROM internship WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $internship = $result->fetch_assoc();
            
            // Delete the image file if it exists
            if ($internship && !empty($internship['Image']) && file_exists($internship['Image'])) {
                unlink($internship['Image']);
            }
            
            // Delete the database record
            $stmt = $this->conn->prepare("DELETE FROM internship WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $stmt->close();
            return true;

        } catch (Exception $e) {
            error_log("Error deleting internship: " . $e->getMessage());
            return false;
        }
    }




    public function __destruct() {
        if ($this->db) {
            $this->db->closeConnection();
        }
    }
}


header('Content-Type: application/json');

if(isset($_POST['action'])) {
    error_log("Received POST action: " . $_POST['action']);
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));

    $internshipManager = new InternshipManager();
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    switch($_POST['action']) {
        case 'submit_application':
            $result = $internshipManager->submitApplication($_POST, $_FILES);
            if($isAjax) {
                if ($result === true) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Application submitted successfully!'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => $result // This will be the error message
                    ]);
                }
            } else {
                // Handle non-AJAX submission if needed
                header("Location: index.php?" . ($result === true ? "success=1" : "error=1"));
            }
            break;

        case 'add':
            $result = $internshipManager->addInternship($_POST, $_FILES);
            error_log("Add result: " . ($result ? "success" : "failure"));
            
            if($isAjax) {
                echo json_encode([
                    'success' => $result,
                    'message' => $result ? 'Stage ajouté avec succès!' : 'Erreur lors de l\'ajout du stage',
                    'redirect' => 'listeDesStages.php'
                ]);
            } else {
                header("Location: listeDesStages.php?" . ($result ? "success=1" : "error=1"));
            }
            break;
            
        case 'update':
            if(isset($_POST['internship_id'])) {
                $result = $internshipManager->updateInternship($_POST['internship_id'], $_POST, $_FILES);
                if($isAjax) {
                    echo json_encode([
                        'success' => $result,
                        'message' => $result ? 'Stage mis à jour avec succès!' : 'Erreur lors de la mise à jour',
                        'redirect' => 'listeDesStages.php'
                    ]);
                } else {
                    header("Location: " . ($result ? 
                        "listeDesStages.php?success=1" : 
                        "modifier.php?id=" . $_POST['internship_id'] . "&error=1"));
                }
            }
            break;

        case 'delete':
            if(isset($_POST['internship_id'])) {
                $result = $internshipManager->deleteInternship($_POST['internship_id']);
                if($isAjax) {
                    echo json_encode([
                        'success' => $result,
                        'message' => $result ? 'Stage supprimé avec succès!' : 'Erreur lors de la suppression',
                        'redirect' => 'listeDesStages.php'
                    ]);
                } else {
                    header("Location: listeDesStages.php?" . ($result ? "success=2" : "error=2"));
                }
            }
            break;
    }
    exit();
}