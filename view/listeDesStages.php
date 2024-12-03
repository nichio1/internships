<!doctype html>
<?php
require_once 'config.php';

$db = new Database();
$conn = $db->getConnection();
// Get search query if it exists
$search = isset($_GET['search']) ? $_GET['search'] : '';
// Get sort parameters
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'asc';
$current_sort = ($sort == 'asc') ? 'desc' : 'asc';
// Get user preferences from cookie or default values
$darkMode = isset($_COOKIE['darkMode']) ? $_COOKIE['darkMode'] === 'true' : false;


// Modify SQL query to include search and sorting
$sql = "SELECT *, 
        CASE 
            WHEN Duration LIKE '%month%' THEN CAST(SUBSTRING_INDEX(Duration, ' ', 1) AS SIGNED) * 4
            WHEN Duration LIKE '%week%' THEN CAST(SUBSTRING_INDEX(Duration, ' ', 1) AS SIGNED)
            ELSE 0 
        END as weeks_count
        FROM internship 
        WHERE Title LIKE ? 
        ORDER BY weeks_count " . ($sort == 'asc' ? 'ASC' : 'DESC');

$stmt = $conn->prepare($sql);
$searchTerm = "%" . $search . "%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

// Function to standardize duration display
function formatDuration($duration) {
  // Extract number and unit
  preg_match('/(\d+)\s*(month|week)s?/i', $duration, $matches);
  if (empty($matches)) return $duration;
  
  $number = $matches[1];
  $unit = strtolower($matches[2]);
  
  if ($unit == 'month') {
      return $number . ($number > 1 ? ' months' : ' month');
  } else {
      return $number . ($number > 1 ? ' weeks' : ' week');
  }
}



?>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ajouter un Stage</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/seodashlogo.png" />
  <link rel="stylesheet" href="../../node_modules/simplebar/dist/simplebar.min.css">
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <style>
  /* Dark mode styles */
  body.dark-mode {
    background-color: #1a1a1a;
    color: #e0e0e0;
  }

  .dark-mode .card {
    background-color: #2d2d2d;
    border-color: #404040;
  }

  .dark-mode .navbar {
    background-color: #2d2d2d;
  }

  .dark-mode .left-sidebar {
    background-color: #2d2d2d;
  }

  .dark-mode .sidebar-nav .sidebar-link {
    color: #e0e0e0;
  }

  .dark-mode .table {
    color: #e0e0e0;
  }

  .dark-mode .table thead th {
    border-color: #404040;
  }

  .dark-mode .table td, 
  .dark-mode .table th {
    border-color: #404040;
  }

  .dark-mode .form-control {
    background-color: #333;
    border-color: #404040;
    color: #e0e0e0;
  }

  .dark-mode .form-control::placeholder {
    color: #888;
  }

  .dark-mode .dropdown-menu {
    background-color: #2d2d2d;
    border-color: #404040;
  }

  .dark-mode .dropdown-item {
    color: #e0e0e0;
  }

  .dark-mode .dropdown-item:hover {
    background-color: #404040;
  }
</style>
</head>

<body>
  <!-- Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->

    
    <aside class="left-sidebar">
      <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
          <a href="./index.html" class="text-nowrap logo-img">
            <img src="../assets/images/logos/logo-light.svg" alt="" />
          </a>
          <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <i class="ti ti-x fs-8"></i>
          </div>
        </div>
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
          <ul id="sidebarnav">
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
              <span class="hide-menu">Home</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="ajouterstage.php" aria-expanded="false">
                <span>
                  <iconify-icon icon="solar:home-smile-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">Ajouter stage </span>
              </a>
              <a class="sidebar-link" href="listeDesStages.php" aria-expanded="false">
                <span>
                  <iconify-icon icon="solar:home-smile-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">Listes des stages </span>
              </a>
            </li>
            
            <!-- Add more sidebar items as needed -->
          </ul>
        </nav>
      </div>
    </aside>
    <!-- Sidebar End -->

    <!-- Main Wrapper -->
    <div class="body-wrapper">
      <!-- Header Start -->
      <header class="app-header">
        <nav class="navbar navbar-expand-lg navbar-light">
          <ul class="navbar-nav flex-row ms-auto align-items-center">
          <li class="nav-item me-2">
        <button id="darkModeToggle" class="btn btn-outline-secondary">
          <i class="ti ti-moon"></i>
        </button>
      </li>
            <li class="nav-item">
              <a class="nav-link nav-icon-hover" href="javascript:void(0)">
                <i class="ti ti-bell-ringing"></i>
                <div class="notification bg-primary rounded-circle"></div>
              </a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown">
                <img src="../assets/images/profile/user-1.jpg" alt="" width="35" height="35" class="rounded-circle">
              </a>
              <div class="dropdown-menu dropdown-menu-end">
                <div class="message-body">
                  <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                    <i class="ti ti-user fs-6"></i>
                    <p class="mb-0 fs-3">My Profile</p>
                  </a>
                  <a href="./authentication-login.html" class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</a>
                </div>
              </div>
            </li>
          </ul>
        </nav>
      </header>
      <!-- Header End -->

      <!-- Content Start -->
      
      <div class="container-fluid">
        <div class="card">
        <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title m-0">Liste des Stages</h5>
                <form method="GET" class="d-flex gap-2">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        style="width: 200px;"
                        placeholder="Rechercher par titre..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-search"></i>
                    </button>
                    <?php if(!empty($search)): ?>
                        <a href="listeDesStages.php" class="btn btn-secondary">
                            <i class="ti ti-x"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
             <!-- Single table -->
             <div class="table-responsive">
                <table class="table text-nowrap align-middle mb-0">
                    <thead>
                        <tr class="border-2 border-bottom border-primary border-0">
                            <th scope="col" class="ps-0">Titre</th>
                            <th scope="col">
                                Duration
                                <a href="?sort=<?php echo $current_sort; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="text-decoration-none">
                                    <i class="ti ti-arrow-<?php echo $sort === 'asc' ? 'up' : 'down'; ?> ms-1"></i>
                                </a>
                            </th>
                            <th scope="col">Expertise</th>
                            <th scope="col">Location</th>
                            <th scope="col" class="text-center">Actions</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <th scope="row" class="ps-0 fw-medium">
                                        <span class="table-link1 text-truncate d-block">
                                            <?php echo htmlspecialchars($row['Title']); ?>
                                        </span>
                                    </th>
                                    <td class="fw-medium"><?php echo htmlspecialchars(formatDuration($row['Duration'])); ?></td>
                                    <td class="fw-medium"><?php echo htmlspecialchars($row['Field']); ?></td>
                                    <td class="fw-medium"><?php echo htmlspecialchars($row['Location']); ?></td>
                                    <td class="text-center">
                                        <a href="modifierstage.php?id=<?php echo $row['id']; ?>">
                                            <button class="btn btn-warning btn-sm me-2">Modifier</button>
                                        </a>
                                        <a href="supprimer.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">
                                            <i class="ti ti-trash"></i> Supprimer
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="5" class="text-center">
                                    <?php echo empty($search) ? 'Aucun stage trouvé' : 'Aucun résultat pour votre recherche'; ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const darkModeToggle = document.getElementById('darkModeToggle');
  const body = document.body;
  const icon = darkModeToggle.querySelector('i');
  
  // Check for saved dark mode preference
  const darkMode = localStorage.getItem('darkMode') === 'true';
  if (darkMode) {
    body.classList.add('dark-mode');
    icon.classList.remove('ti-moon');
    icon.classList.add('ti-sun');
  }
  
  // Toggle dark mode
  darkModeToggle.addEventListener('click', () => {
    body.classList.toggle('dark-mode');
    const isDarkMode = body.classList.contains('dark-mode');
    
    // Save preference
    localStorage.setItem('darkMode', isDarkMode);
    
    // Toggle icon
    if (isDarkMode) {
      icon.classList.remove('ti-moon');
      icon.classList.add('ti-sun');
    } else {
      icon.classList.remove('ti-sun');
      icon.classList.add('ti-moon');
    }
  });
});
</script>

      
      <!-- Content End -->
      
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

