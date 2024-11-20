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
    <title>Supprimer un Stage</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/seodashlogo.png" />
    <link rel="stylesheet" href="../../node_modules/simplebar/dist/simplebar.min.css">
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        
        <!-- Your sidebar code here -->

        <div class="body-wrapper">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title fw-semibold mb-4">Supprimer un Stage</h1>
                        
                        <div class="alert alert-warning" role="alert">
                            Êtes-vous sûr de vouloir supprimer ce stage ? Cette action est irréversible.
                        </div>

                        <div class="internship-details mb-4">
                            <h5><?php echo htmlspecialchars($internship['Title']); ?></h5>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($internship['Location']); ?></p>
                            <p><strong>Duration:</strong> <?php echo htmlspecialchars($internship['Duration']); ?></p>
                            <p><strong>Field:</strong> <?php echo htmlspecialchars($internship['Field']); ?></p>
                            <?php if($internship['Image']): ?>
                                <div class="mb-3">
                                    <img src="<?php echo htmlspecialchars($internship['Image']); ?>" 
                                         alt="Stage image" style="max-width: 200px;">
                                </div>
                            <?php endif; ?>
                        </div>

                        <form action="InternshipManager.php" method="POST" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="internship_id" value="<?php echo htmlspecialchars($internship['id']); ?>">
                            <button type="submit" class="btn btn-danger">Confirmer la suppression</button>
                            <a href="listeDesStages.php" class="btn btn-secondary">Annuler</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
</body>
</html>