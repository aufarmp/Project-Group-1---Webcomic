<?php
session_start();
include '../connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

$sql_user = "SELECT username, email, profile_picture_url FROM tb_user WHERE user_id = ?";
$stmt_user = mysqli_prepare($conn, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $user_id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user_data = mysqli_fetch_assoc($result_user);

$profile_pic = !empty($user_data['profile_picture_url']) ? $user_data['profile_picture_url'] : '/assets/profile_pictures/default.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Comic Web</title>
    <link rel="stylesheet" href="../../assets/css/user_style.css">

    <style>
        .profile-container { max-width: 800px; margin: 20px auto; background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .profile-picture { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; display: block; margin: 0 auto 20px auto; border: 3px solid #eee; }
        .form-section { border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px; }
        .form-section h3 { margin-top: 0; color: #2c3e50; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="file"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .profile-container button { padding: 10px 15px; font-size: 14px; font-weight: bold; background-color: #1abc9c; color: white; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s; }
        .profile-container button:hover { background-color: #16a085; }
    </style>

</head>
<body>

    <div class="user-container">
        <div class="sidebar">
            <h2>Comic Web</h2>
            <a href="homepage.php" class="nav-button">Browse</a>
            <a href="bookmark.php" class="nav-button">My Bookmarks</a>
            <a href="history.php" class="nav-button">History</a>
            <a href="profile.php" class="nav-button active">Profile</a>
            <a href="#" class="nav-button logout-button" onclick="showLogoutModal(event)">Logout</a>
        </div>

        <div class="main-content">
            <h2 class="page-heading">My Profile</h2>

             <?php 
             if (isset($_GET['status'])) {
                if ($_GET['status'] == 'success') echo '<div class="feedback success" style="max-width: 800px; margin: 0 auto 20px auto;">Profile updated successfully!</div>';
                if ($_GET['status'] == 'error_type') echo '<div class="feedback error" style="max-width: 800px; margin: 0 auto 20px auto;">Error: Invalid file type for profile picture.</div>';
                if ($_GET['status'] == 'error_size') echo '<div class="feedback error" style="max-width: 800px; margin: 0 auto 20px auto;">Error: Profile picture file is too large.</div>';
                if ($_GET['status'] == 'error_upload') echo '<div class="feedback error" style="max-width: 800px; margin: 0 auto 20px auto;">Error uploading profile picture.</div>';
                if ($_GET['status'] == 'error_duplicate') echo '<div class="feedback error" style="max-width: 800px; margin: 0 auto 20px auto;">Error: Username or email already exists.</div>';
                if ($_GET['status'] == 'error_db') echo '<div class="feedback error" style="max-width: 800px; margin: 0 auto 20px auto;">Database error. Please try again.</div>';
            }
            ?> 

            <div class="profile-container">
                <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" class="profile-picture">

                <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="form-section">
                    <h3>Update Profile Picture</h3>
                    <div class="form-group">
                        <label for="profile_pic_upload">Select Image:</label>
                        <input type="file" id="profile_pic_upload" name="profile_picture" accept="image/jpeg, image/png, image/gif">
                    </div>
                    <button type="submit" name="update_picture">Upload Picture</button>
                </form>

                <form action="update_profile.php" method="POST" class="form-section">
                    <h3>Update Account Details</h3>
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                    </div>
                    <button type="submit" name="update_details">Update Details</button>
                </form>

                <form action="update_profile.php" method="POST" class="form-section">
                    <h3>Change Password</h3>
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="update_password">Change Password</button>
                </form>
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