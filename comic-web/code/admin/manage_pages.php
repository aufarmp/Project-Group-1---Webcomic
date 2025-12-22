<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}
$chapter_id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['new_pages'])) {
    $komik_id = (int)$_POST['komik_id'];
    $chapter_number = (float)$_POST['chapter_number'];

    $page_count = count($_FILES['new_pages']['name']);
    $success_count = 0;

    $sql_max_page = "SELECT MAX(page_number) AS max_page FROM tb_pages WHERE chapter_id = ?";
    $stmt_max_page = mysqli_prepare($conn, $sql_max_page);
    mysqli_stmt_bind_param($stmt_max_page, "i", $chapter_id);
    mysqli_stmt_execute($stmt_max_page);
    $result_max_page = mysqli_stmt_get_result($stmt_max_page);
    $max_page_row = mysqli_fetch_assoc($result_max_page);
    $next_page_number = ($max_page_row['max_page'] !== null) ? $max_page_row['max_page'] + 1 : 1;

    for ($i = 0; $i < $page_count; $i++) {
        if ($_FILES['new_pages']['error'][$i] == 0) {
            $page_tmp_name = $_FILES['new_pages']['tmp_name'][$i];
            $original_page_name = basename($_FILES['new_pages']['name'][$i]);
            
            $upload_dir = '../../assets/comic_pages/' . $komik_id . '/' . $chapter_number . '/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = pathinfo($original_page_name, PATHINFO_EXTENSION);
            $unique_filename = uniqid('page_') . '.' . $file_extension;
            $server_path = $upload_dir . $unique_filename;
            
            if (move_uploaded_file($page_tmp_name, $server_path)) {
                $db_image_path = '/assets/comic_pages/' . $komik_id . '/' . $chapter_number . '/' . $unique_filename;

                $sql_insert_page = "INSERT INTO tb_pages (chapter_id, page_number, image_url) VALUES (?, ?, ?)";
                $stmt_insert_page = mysqli_prepare($conn, $sql_insert_page);
                mysqli_stmt_bind_param($stmt_insert_page, "iis", $chapter_id, $next_page_number, $db_image_path);
                mysqli_stmt_execute($stmt_insert_page);
                $next_page_number++;
                $success_count++;
            }
        }
    }
    if ($success_count > 0) {
        header("Location: manage_pages.php?id=" . $chapter_id . "&status=pages_added&count=" . $success_count);
        exit();
    } else {
        header("Location: manage_pages.php?id=" . $chapter_id . "&status=error_upload");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['replace_page_id']) && isset($_FILES['replace_image'])) {
    $replace_page_id = (int)$_POST['replace_page_id'];
    $komik_id = (int)$_POST['komik_id'];
    $chapter_number = (float)$_POST['chapter_number'];

    if ($_FILES['replace_image']['error'] == 0) {
        $sql_get_old_image = "SELECT image_url FROM tb_pages WHERE page_id = ?";
        $stmt_get_old_image = mysqli_prepare($conn, $sql_get_old_image);
        mysqli_stmt_bind_param($stmt_get_old_image, "i", $replace_page_id);
        mysqli_stmt_execute($stmt_get_old_image);
        $result_old_image = mysqli_stmt_get_result($stmt_get_old_image);
        $old_page = mysqli_fetch_assoc($result_old_image);

        if ($old_page) {
            $old_server_file_path = $_SERVER['DOCUMENT_ROOT'] . $old_page['image_url'];
            if (file_exists($old_server_file_path)) {
                unlink($old_server_file_path); // Menghapus file lama
            }
        }

        // Upload file baru
        $new_page_tmp_name = $_FILES['replace_image']['tmp_name'];
        $original_new_page_name = basename($_FILES['replace_image']['name']);

        $upload_dir = '../../assets/comic_pages/' . $komik_id . '/' . $chapter_number . '/';
        if (!is_dir($upload_dir)) { 
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = pathinfo($original_new_page_name, PATHINFO_EXTENSION);
        $unique_filename = uniqid('replace_') . '.' . $file_extension;
        $server_path = $upload_dir . $unique_filename;

        if (move_uploaded_file($new_page_tmp_name, $server_path)) {
            $db_image_path = '/assets/comic_pages/' . $komik_id . '/' . $chapter_number . '/' . $unique_filename;

            $sql_update_page = "UPDATE tb_pages SET image_url = ? WHERE page_id = ?";
            $stmt_update_page = mysqli_prepare($conn, $sql_update_page);
            mysqli_stmt_bind_param($stmt_update_page, "si", $db_image_path, $replace_page_id);
            mysqli_stmt_execute($stmt_update_page);

            header("Location: manage_pages.php?id=" . $chapter_id . "&status=page_replaced");
            exit();
        } else {
            header("Location: manage_pages.php?id=" . $chapter_id . "&status=error_replace_upload");
            exit();
        }
    } else {
         header("Location: manage_pages.php?id=" . $chapter_id . "&status=error_replace_no_file");
         exit();
    }
}

if (isset($_GET['delete_id'])) {
    $page_id_to_delete = (int)$_GET['delete_id'];

    $sql_get_page = "SELECT image_url FROM tb_pages WHERE page_id = ?";
    $stmt_get_page = mysqli_prepare($conn, $sql_get_page);
    mysqli_stmt_bind_param($stmt_get_page, "i", $page_id_to_delete);
    mysqli_stmt_execute($stmt_get_page);
    $result_get_page = mysqli_stmt_get_result($stmt_get_page);
    $page = mysqli_fetch_assoc($result_get_page);

    if ($page) {
        $server_file_path = $_SERVER['DOCUMENT_ROOT'] . $page['image_url'];
        if (file_exists($server_file_path)) {
            unlink($server_file_path); // Menghapus file
        }

        $sql_delete = "DELETE FROM tb_pages WHERE page_id = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, "i", $page_id_to_delete);
        mysqli_stmt_execute($stmt_delete);

        $sql_reorder = "SET @i = 0; UPDATE tb_pages SET page_number = (@i:=@i+1) WHERE chapter_id = ? ORDER BY page_number ASC;";
        mysqli_multi_query($conn, $sql_reorder);
        do {
            if ($result = mysqli_store_result($conn)) {
                mysqli_free_result($result);
            }
        } while (mysqli_next_result($conn));

        header("Location: manage_pages.php?id=" . $chapter_id . "&status=page_deleted");
        exit();
    }
}

$sql_info = "SELECT ch.chapter_number, ch.title, k.komik_id, k.title AS comic_title
             FROM tb_chapter ch
             JOIN tb_komik k ON ch.komik_id = k.komik_id
             WHERE ch.chapter_id = ?";
$stmt_info = mysqli_prepare($conn, $sql_info);
mysqli_stmt_bind_param($stmt_info, "i", $chapter_id);
mysqli_stmt_execute($stmt_info);
$result_info = mysqli_stmt_get_result($stmt_info);
$info = mysqli_fetch_assoc($result_info);

if (!$info) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pages</title>
    <link rel="stylesheet" href="../../assets/css/admin_style.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <a href="dashboard.php" class="nav-button active">Dashboard</a>
            <a href="add_comic.php" class="nav-button">Add New Comic</a>
            <a href="add_chapter.php" class="nav-button">Add New Chapter</a>
            <a href="manage_genres.php" class="nav-button">Manage Genres</a>
            <a href="manage_users.php" class="nav-button">Manage Users</a>
            <a href="../logout.php" class="nav-button logout-button" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
        </div>

        <div class="main-content">
            <h1>Manage Pages for: <strong><?php echo htmlspecialchars($info['comic_title']) . ' - Ch. ' . htmlspecialchars($info['chapter_number']); ?></strong></h1>
            <a href="manage_chapters.php?id=<?php echo $info['komik_id']; ?>" class="back-link">&laquo; Back to Chapter List</a>
            <hr>

            <?php
            if (isset($_GET['status'])) {
                if ($_GET['status'] == 'page_deleted') {
                    echo '<div class="feedback success">Page deleted successfully!</div>';
                } elseif ($_GET['status'] == 'pages_added') {
                    echo '<div class="feedback success">' . (int)$_GET['count'] . ' new pages added successfully!</div>';
                } elseif ($_GET['status'] == 'page_replaced') {
                    echo '<div class="feedback success">Page image replaced successfully!</div>';
                } elseif ($_GET['status'] == 'error_upload') {
                    echo '<div class="feedback error">Error uploading new pages. Please try again.</div>';
                } elseif ($_GET['status'] == 'error_replace_upload') {
                    echo '<div class="feedback error">Error replacing page image. Please try again.</div>';
                } elseif ($_GET['status'] == 'error_replace_no_file') {
                    echo '<div class="feedback error">No file selected for replacement.</div>';
                }
            }
            ?>

            <div class="form-container" style="margin-bottom: 30px; padding: 20px;">
                <h2>Add More Pages</h2>
                <p>Upload new image files to add more pages to this chapter. They will be added to the end.</p>
                <form action="manage_pages.php?id=<?php echo $chapter_id; ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="komik_id" value="<?php echo $info['komik_id']; ?>">
                    <input type="hidden" name="chapter_number" value="<?php echo $info['chapter_number']; ?>">
                    <div class="form-group">
                        <label for="new_pages">Select Page Images:</label>
                        <input type="file" id="new_pages" name="new_pages[]" multiple accept="image/*" required>
                    </div>
                    <button type="submit">Add Pages</button>
                </form>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Page Number</th>
                        <th>Page Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $sql_pages = "SELECT page_id, page_number, image_url FROM tb_pages WHERE chapter_id = ? ORDER BY page_number ASC";
                        $stmt_pages = mysqli_prepare($conn, $sql_pages);
                        mysqli_stmt_bind_param($stmt_pages, "i", $chapter_id);
                        mysqli_stmt_execute($stmt_pages);
                        $result_pages = mysqli_stmt_get_result($stmt_pages);

                        if (mysqli_num_rows($result_pages) > 0) {
                            while ($page = mysqli_fetch_assoc($result_pages)) {
                                echo '<tr>';
                                echo '<td>' . $page['page_number'] . '</td>';
                                echo '<td>
                                        <img src="' . htmlspecialchars($page['image_url']) . '" alt="Page ' . $page['page_number'] . '" style="width: 80px; height: 120px; object-fit: cover; border-radius: 4px; display: block; margin-bottom: 10px;">
                                        <form action="manage_pages.php?id=' . $chapter_id . '" method="POST" enctype="multipart/form-data" style="margin-top: 5px;">
                                            <input type="hidden" name="replace_page_id" value="' . $page['page_id'] . '">
                                            <input type="hidden" name="komik_id" value="' . $info['komik_id'] . '">
                                            <input type="hidden" name="chapter_number" value="' . $info['chapter_number'] . '">
                                            <input type="file" name="replace_image" accept="image/*" style="width: 150px; font-size: 12px; display: inline-block;">
                                            <button type="submit" class="action-btn edit-btn" style="background-color: #3498db; padding: 5px 10px; margin-left: 5px;">Replace</button>
                                        </form>
                                      </td>';
                                echo '<td>
                                        <a href="manage_pages.php?id=' . $chapter_id . '&delete_id=' . $page['page_id'] . '" class="action-btn delete-btn" onclick="return confirm(\'Are you sure you want to delete this page?\');">Delete</a>
                                      </td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="3" style="text-align: center;">This chapter has no pages. Use the form above to add some.</td></tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>