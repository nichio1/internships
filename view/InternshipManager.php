<?php
require_once 'config.php';

class InternshipManager {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();

        if (!$this->conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
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