<!doctype html>
<html lang="en">
<?php
require_once 'config.php';

// Get the internship ID from URL parameter
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Fetch internship details
    $sql = "SELECT * FROM internship WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $internship = $result->fetch_assoc();
    
    if (!$internship) {
        // Redirect if internship not found
        header("Location: listeDesStages.php");
        exit();
    }
    $db->closeConnection();
}
?>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier un Stage</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/seodashlogo.png" />
    <link rel="stylesheet" href="../../node_modules/simplebar/dist/simplebar.min.css">
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
</head>

<body>
    <!-- Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        <aside class="left-sidebar">
            <!-- Previous sidebar content remains the same -->
            <!-- ... -->
        </aside>
        <!-- Sidebar End -->

        <!-- Main Wrapper -->
        <div class="body-wrapper">
            <!-- Header content remains the same -->
            <!-- ... -->

            <!-- Content Start -->

            <div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h1 class="card-title fw-semibold mb-4">Modifier un Stage</h1>
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Erreur lors de la modification du stage. Veuillez réessayer.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form action="InternshipManager.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="internship_id" value="<?php echo htmlspecialchars($internship['id']); ?>">

                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" 
                           value="<?php echo htmlspecialchars($internship['Title']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" 
                              required rows="4"><?php echo htmlspecialchars($internship['Description']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" class="form-control" id="location" name="location" 
                           value="<?php echo htmlspecialchars($internship['Location']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="duration" class="form-label">Duration</label>
                    <input type="text" class="form-control" id="duration" name="duration" 
                           value="<?php echo htmlspecialchars($internship['Duration']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="field" class="form-label">Field</label>
                    <input type="text" class="form-control" id="field" name="field" 
                           value="<?php echo htmlspecialchars($internship['Field']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Image</label>
                    <?php if($internship['Image']): ?>
                        <div class="mb-2">
                            <img src="<?php echo htmlspecialchars($internship['Image']); ?>" 
                                 alt="Current image" style="max-width: 200px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="listeDesStages.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

            <div class="py-6 px-6 text-center">
                <p class="mb-0 fs-4">Design and Developed by <a href="https://adminmart.com/" target="_blank" class="text-primary text-decoration-underline">Learnify</a> Distributed by <a href="https://themewagon.com/" target="_blank" class="text-primary text-decoration-underline">Learnify</a></p>
            </div>
        </div>
    </div>

    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>
</html>