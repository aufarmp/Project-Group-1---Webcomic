<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_picture'])) {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024;

        $file_info = $_FILES['profile_picture'];
        $file_type = $file_info['type'];
        $file_size = $file_info['size'];

        if (!in_array($file_type, $allowed_types)) {
            header("Location: profile.php?status=error_type"); exit();
        }
        if ($file_size > $max_size) {
            header("Location: profile.php?status=error_size"); exit();
        }

        $upload_dir = '../../assets/profile_pictures/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
        
        $file_extension = pathinfo($file_info['name'], PATHINFO_EXTENSION);
        $unique_filename = 'user_' . $user_id . '_' . time() . '.' . $file_extension;
        $server_path = $upload_dir . $unique_filename;
        $db_path = '/assets/profile_pictures/' . $unique_filename;

        if (move_uploaded_file($file_info['tmp_name'], $server_path)) {

            $sql_update_pic = "UPDATE tb_user SET profile_picture_url = ? WHERE user_id = ?";
            $stmt_update_pic = mysqli_prepare($conn, $sql_update_pic);
            mysqli_stmt_bind_param($stmt_update_pic, "si", $db_path, $user_id);
            if (mysqli_stmt_execute($stmt_update_pic)) {
                header("Location: profile.php?status=success"); exit();
            } else {
                header("Location: profile.php?status=error_db"); exit();
            }
        } else {
            header("Location: profile.php?status=error_upload"); exit();
        }
    } else {
        header("Location: profile.php?status=error_upload"); exit();
    }
}

elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_details'])) {
    $new_username = mysqli_real_escape_string($conn, $_POST['username']);
    $new_email = mysqli_real_escape_string($conn, $_POST['email']);

    $sql_check = "SELECT user_id FROM tb_user WHERE (username = ? OR email = ?) AND user_id != ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "ssi", $new_username, $new_email, $user_id);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        header("Location: profile.php?status=error_duplicate"); exit();
    }

    $sql_update_details = "UPDATE tb_user SET username = ?, email = ? WHERE user_id = ?";
    $stmt_update_details = mysqli_prepare($conn, $sql_update_details);
    mysqli_stmt_bind_param($stmt_update_details, "ssi", $new_username, $new_email, $user_id);
    if (mysqli_stmt_execute($stmt_update_details)) {
        header("Location: profile.php?status=success"); exit();
    } else {
        header("Location: profile.php?status=error_db"); exit();
    }
}

elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($new_password) || $new_password !== $confirm_password) {
        header("Location: profile.php?status=error_password_match"); exit(); 
    }

    $sql_update_pass = "UPDATE tb_user SET password = ? WHERE user_id = ?";
    $stmt_update_pass = mysqli_prepare($conn, $sql_update_pass);
    mysqli_stmt_bind_param($stmt_update_pass, "si", $new_password, $user_id);
    if (mysqli_stmt_execute($stmt_update_pass)) {
        header("Location: profile.php?status=success"); exit();
    } else {
        header("Location: profile.php?status=error_db"); exit();
    }
}

else {
    header("Location: profile.php");
    exit();
}
?>