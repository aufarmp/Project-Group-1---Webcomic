<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $komik_id = (int)$_POST['komik_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $db_image_path = null;

    $sql_delete_genres = "DELETE FROM tb_komik_genre WHERE komik_id = ?";
    $stmt_delete_genres = mysqli_prepare($conn, $sql_delete_genres);
    mysqli_stmt_bind_param($stmt_delete_genres, "i", $komik_id);
    mysqli_stmt_execute($stmt_delete_genres);

    if (!empty($_POST['genres'])) {
        $sql_genre_link = "INSERT INTO tb_komik_genre (komik_id, genre_id) VALUES (?, ?)";
        $stmt_genre_link = mysqli_prepare($conn, $sql_genre_link);
        
        foreach ($_POST['genres'] as $genre_id) {
            mysqli_stmt_bind_param($stmt_genre_link, "ii", $komik_id, $genre_id);
            mysqli_stmt_execute($stmt_genre_link);
        }
    }

    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        
        $image_info = $_FILES['cover_image'];
        $image_name = $image_info['name'];
        $image_tmp_name = $image_info['tmp_name'];

        $upload_dir = '../../assets/comic_cover/'; 
        $unique_filename = time() . '_' . basename($image_name);
        $server_path = $upload_dir . $unique_filename;

        if (move_uploaded_file($image_tmp_name, $server_path)) {
            $db_image_path = '/assets/comic_cover/' . $unique_filename;
        }
    }

    if ($db_image_path) {
        $sql = "UPDATE tb_komik SET title=?, author=?, description=?, status=?, cover_image=? WHERE komik_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssi", $title, $author, $description, $status, $db_image_path, $komik_id);
    } 

    else {
        $sql = "UPDATE tb_komik SET title=?, author=?, description=?, status=? WHERE komik_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $title, $author, $description, $status, $komik_id);
    }

    if (mysqli_stmt_execute($stmt)) {
        header("Location: dashboard.php?status=updated");
        exit();
    } else {
        echo "Error: Could not update the database.";
    }
}
?>