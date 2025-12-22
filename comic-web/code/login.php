<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include 'connection.php'; 

define('API_LOGIN_URL', 'http://java-api:8080/api/auth/login');

if (isset($_SESSION['access_token'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/homepage.php");
    }
    exit(); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $payload = json_encode(array(
        "username" => $username,
        "password" => $password
    ));

    $ch = curl_init(API_LOGIN_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
    ));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        $_SESSION['login_error'] = "Server Error: Tidak dapat menghubungi API.";
        header("Location: login.php");
        exit();
    }

    $result = json_decode($response, true);

    if ($http_code == 200 && isset($result['accessToken'])) {
        
        $_SESSION['access_token'] = $result['accessToken'];
        $_SESSION['token_type'] = $result['tokenType'];
        $_SESSION['username'] = $username;

        if (isset($conn)) {
            $stmt_id = mysqli_prepare($conn, "SELECT user_id FROM tb_user WHERE username = ?");
            if ($stmt_id) {
                mysqli_stmt_bind_param($stmt_id, "s", $username);
                mysqli_stmt_execute($stmt_id);
                $res_id = mysqli_stmt_get_result($stmt_id);
                
                if ($row_id = mysqli_fetch_assoc($res_id)) {
                    $_SESSION['user_id'] = $row_id['user_id'];
                }
                mysqli_stmt_close($stmt_id);
            }
        }
        
        if (!isset($_SESSION['user_id'])) {
             $_SESSION['user_id'] = 0; 
        }

        $tokenParts = explode('.', $result['accessToken']);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtData = json_decode($tokenPayload, true);

        $role = 'user'; // Default

        $roles_from_jwt = isset($jwtData['roles']) ? $jwtData['roles'] : [];
        
        if (!is_array($roles_from_jwt)) {
            $roles_from_jwt = array($roles_from_jwt);
        }

        if (in_array('admin', $roles_from_jwt) || 
            in_array('ADMIN', $roles_from_jwt) || 
            in_array('ROLE_ADMIN', $roles_from_jwt)) {
            
            $role = 'admin';
        }
        
        $_SESSION['role'] = $role;

        if ($role == 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: user/homepage.php");
        }
        exit();

    } else {

        $errorMsg = isset($result['message']) ? $result['message'] : "Username atau Password salah.";
        $_SESSION['login_error'] = $errorMsg;
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/login_style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h2>Login to Your Account</h2>
            <p>Please enter your credentials to proceed.</p>

            <?php
            if (isset($_SESSION['login_error'])) {
                echo '<div class="feedback error">' . $_SESSION['login_error'] . '</div>';
                unset($_SESSION['login_error']);
            } elseif (isset($_GET['status']) && $_GET['status'] == 'registered') {
                echo '<div class="feedback success">Registration successful! Please log in with your account.</div>';
            }
            ?>

            <form action="login.php" method="POST">
                <label for="username">Username or email</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Login</button>
            </form>

            <div class="register-link">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
            
        </div>
    </div>
</body>
</html>