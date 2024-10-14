<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'testing';

$con = new mysqli($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if ($con->connect_error) {
    die('Connection failed: ' . $con->connect_error);
}

$update_msg = '';
$update_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'], $_POST['email'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $id = $_SESSION['id'];

        // Check if a file is uploaded
        if (!empty($_FILES['profile_picture']['name'])) {
            $target_dir = 'uploads/';
            $target_file = $target_dir . basename($_FILES['profile_picture']['name']);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if file is an actual image or fake image
            $check = getimagesize($_FILES['profile_picture']['tmp_name']);
            if ($check === false) {
                $update_error = 'File is not an image.';
            } elseif ($_FILES['profile_picture']['size'] > 500000) { // Check file size
                $update_error = 'Sorry, your file is too large.';
            } elseif ($imageFileType != 'jpg' && $imageFileType != 'png' && $imageFileType != 'jpeg') { // Allow certain file formats
                $update_error = 'Sorry, only JPG, JPEG, PNG files are allowed.';
            } elseif (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                // Update database with uploaded file path
                $stmt = $con->prepare('UPDATE accounts SET username = ?, email = ?, profile_picture = ? WHERE id = ?');
                $stmt->bind_param('sssi', $username, $email, $target_file, $id);
                if ($stmt->execute()) {
                    $_SESSION['name'] = $username; // Update session name
                    $update_msg = 'Profile updated successfully!';
                } else {
                    $update_error = 'Error updating profile: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $update_error = 'Error uploading file.';
            }
        } else {
            // If no file is uploaded, update only username and email
            $stmt = $con->prepare('UPDATE accounts SET username = ?, email = ? WHERE id = ?');
            $stmt->bind_param('ssi', $username, $email, $id);
            if ($stmt->execute()) {
                $_SESSION['name'] = $username; // Update session name
                $update_msg = 'Profile updated successfully!';
            } else {
                $update_error = 'Error updating profile: ' . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $update_error = 'Please complete the form!';
    }
}

// Fetch current user data
$id = $_SESSION['id'];
$stmt = $con->prepare('SELECT username, email, profile_picture FROM accounts WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($username, $email, $profile_picture);
$stmt->fetch();
$stmt->close();

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .navtop {
            background-color: #333;
            color: #fff;
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
        }

        .navtop h1 {
            padding: 0 15px;
            margin: 0;
        }

        .navtop a {
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            display: flex;
            align-items: center;
        }

        .navtop a:hover {
            background-color: #555;
        }

        .content {
            background-color: #fff;
            padding: 20px;
            margin: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center; /* Align items vertically */
        }

        h2 {
            color: #333;
        }

        p {
            color: #666;
        }

        .form-group {
            margin-bottom: 10px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input[type="text"], .form-group input[type="email"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }

        .form-group input[type="file"] {
            margin-top: 5px;
        }

        .form-group input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-group input[type="submit"]:hover {
            background-color: #45a049;
        }

        .error-message {
            color: #f00;
            margin-bottom: 10px;
        }

        .success-message {
            color: #4CAF50;
            margin-bottom: 10px;
        }

        .profile-picture {
            width: 50px; /* Adjust size as needed */
            height: 50px; /* Adjust size as needed */
            border-radius: 50%; /* Make it circular */
            margin-right: 10px; /* Space between picture and button */
        }
        .navtop div {
	display: flex;
	margin: 0 auto;
	width: 1000px;
	height: 100%;
}
.navtop div h1, .navtop div a {
	display: inline-flex;
	align-items: center;
}
.navtop div h1 {
	flex: 1;
	font-size: 24px;
	padding: 0;
	margin: 0;
	color: #eaebed;
	font-weight: normal;
}
.navtop div a {
	padding: 0 20px;
	text-decoration: none;
	color: #c1c4c8;
	font-weight: bold;
}

    </style>
</head>
<body>
    <nav class="navtop">
        <div>
            <!-- Profile picture -->
            <?php if (!empty($profile_picture)) : ?>
                <a href="profile.php"> <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Profile Picture" class="profile-picture">
            <?=htmlspecialchars($_SESSION['name'], ENT_QUOTES)?></a>  
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
            <?php endif; ?>
            
        </div>
    </nav>

    <div class="content">  <form action="profile.php" method="post" enctype="multipart/form-data">
        <h2><?=htmlspecialchars($_SESSION['name'], ENT_QUOTES)?> Profile</h2>
        <?php if (isset($update_msg)) : ?>
            <p class="success-message"><?= htmlspecialchars($update_msg) ?></p>
        <?php endif; ?>
        <?php if (isset($update_error)) : ?>
            <p class="error-message"><?= htmlspecialchars($update_error) ?></p>
        <?php endif; ?>
      
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Email</label>
                <input type="password" id="password" name="password" value="<?= htmlspecialchars($password) ?>" required>
            </div>
            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                <?php if (!empty($profile_picture)) : ?>
                    <br>
                    <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Profile Picture" class="profile-picture">
                    <br>
                <?php endif; ?>
                <input type="file" id="profile_picture" name="profile_picture">
                <small>Allowed file types: JPG, JPEG, PNG. Max file size: 500KB.</small>
            </div>
            <div class="form-group">
                <input type="submit" value="Update">
            </div>
        </form>
    </div>
</body>
</html>
