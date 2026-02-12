<?php
session_start();
include 'connection.php'; 

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password_raw = mysqli_real_escape_string($conn, $_POST['password']); 

    $check_sql = "SELECT user_id FROM tb_user WHERE username = ? OR email = ?";
    $stmt_check = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        $_SESSION['register_error'] = "Username or email is already taken.";
        header("Location: register.php");
        exit();
    }

    $password_hashed = password_hash($password_raw, PASSWORD_BCRYPT);
    $role = 'user'; 
    
    $insert_sql = "INSERT INTO tb_user (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())";
    
    $stmt_insert = mysqli_prepare($conn, $insert_sql);

    mysqli_stmt_bind_param($stmt_insert, "ssss", $username, $email, $password_hashed, $role);

    if (mysqli_stmt_execute($stmt_insert)) {
        header("Location: login.php?status=registered");
        exit();
    } else {
        $_SESSION['register_error'] = "An error occurred. Please try again.";
        header("Location: register.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../assets/css/login_style.css"> 
    <script type="text/javascript">
        function preventBack() {
            window.history.forward();
        }
        setTimeout("preventBack()", 0);
        window.onunload = function() { null };
    </script>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h2>Create a New Account</h2>
            <p>Join our community to read the best comics!</p>

            <?php
            if (isset($_SESSION['register_error'])) {
                echo '<div class="feedback error">' . $_SESSION['register_error'] . '</div>';
                unset($_SESSION['register_error']);
            }
            ?>

            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit">Register</button>
            </form>

            <div class="register-link">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
</body>
</html>