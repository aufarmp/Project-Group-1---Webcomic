<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {

    if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../login.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage - Comic Web</title>
    <link rel="stylesheet" href="../../assets/css/user_style.css">
</head>
<body>

    <div class="user-container">
        
        <div class="sidebar">
            <h2>Comic Web</h2>
            <a href="homepage.php" class="nav-button">Browse</a>
            <a href="bookmark.php" class="nav-button">My Bookmarks</a>
            <a href="history.php" class="nav-button">History</a>
            <a href="profile.php" class="nav-button">Profile</a> 
            <a href="#" class="nav-button logout-button" onclick="showLogoutModal(event)">Logout</a>
        </div>

        <div class="main-content">
            <h2 class="page-heading">Welcome! Browse Our Comics</h2>

            <div class="search-container">
                <form action="homepage.php" method="GET">
                    <input type="text" name="search" class="search-input" placeholder="Search for a comic title...">
                    <button type="submit" class="search-button">Search</button>
                </form>
            </div>
            
            <div class="comic-grid">
                <?php
                if (isset($_GET['search']) && !empty($_GET['search'])) {
                    $search_term = mysqli_real_escape_string($conn, $_GET['search']);
                    
                    $search_query = "%" . $search_term . "%";
                    $sql = "SELECT * FROM tb_komik WHERE title LIKE ? OR author LIKE ?";
                    
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "ss", $search_query, $search_query);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);

                    echo '<h3 class="search-results-heading">Showing results for: "' . htmlspecialchars($search_term) . '"</h3>';

                } else {
                    $sql = "SELECT * FROM tb_komik ORDER BY title ASC";
                    $result = mysqli_query($conn, $sql);
                }

                if (mysqli_num_rows($result) > 0) {
                    while ($comic = mysqli_fetch_assoc($result)) {
                        echo '<div class="comic-card">';
                        echo '  <a href="comic_details.php?id=' . $comic['komik_id'] . '">';
                        echo '      <img src="' . htmlspecialchars($comic['cover_image']) . '" alt="' . htmlspecialchars($comic['title']) . '">';
                        echo '  </a>';
                        echo '  <div class="card-content">';
                        echo '      <h3>' . htmlspecialchars($comic['title']) . '</h3>';
                        echo '      <p>By: ' . htmlspecialchars($comic['author']) . '</p>';
                        echo '      <a href="comic_details.php?id=' . $comic['komik_id'] . '" class="read-more-btn">Read More</a>';
                        echo '  </div>';
                        echo '</div>';
                    }
                } else {
                    if (isset($_GET['search'])) {
                        echo '<div class="no-results">';
                        echo '  <p style="margin-bottom: 20px;">No comics found matching your search.</p>';
                        echo '  <a href="homepage.php" class="read-more-btn" style="text-decoration: none;">View All Comics</a>';
                        echo '</div>';
                    } else {
                        echo '<p class="no-results">No comics have been added yet.</p>';
                    }
                }
                ?>
            </div>

            <footer class="content-footer">
                <p>&copy; <?php echo date("Y"); ?> Comic Web Project</p>
            </footer>
        </div>

        <!-- Logout Confirmation Modal -->
        <div id="logoutModal" class="modal-overlay">
            <div class="modal-content">
                <h3>Confirm Logout</h3>
                <p>Are you sure you want to log out?</p>
                <div class="modal-buttons">
                    <a href="../logout.php" class="modal-btn confirm">Yes, Logout</a>
                    <button class="modal-btn cancel" onclick="closeLogoutModal()">Cancel</button>
                </div>
            </div>
        </div>

        <script>
        function showLogoutModal(event) {
            event.preventDefault();
            document.getElementById('logoutModal').style.display = 'flex';
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }

        // Close modal when clicking outside the modal content
        document.getElementById('logoutModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeLogoutModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeLogoutModal();
            }
        });
        </script>

    </div>
</body>
</html>