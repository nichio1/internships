<?php
require_once 'InternshipManager.php';

// Check if form was submitted
if(isset($_POST['submit'])) {
    // Create new instance of InternshipManager
    $internshipManager = new InternshipManager();
    
    // Try to add the internship
    if($internshipManager->addInternship($_POST, $_FILES)) {
        // Success - redirect with success message
        header("Location: listeDesStages.php?success=1");
        exit();
    } else {
        // Error - redirect back with error message
        header("Location: ajouterstage.php?error=1");
        exit();
    }
} else {
    // If someone tries to access this file directly without submitting the form
    header("Location: ajouterstage.php");
    exit();
}
?>