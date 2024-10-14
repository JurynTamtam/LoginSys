<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Register</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
    <div class="register">
        <h1>Register Form</h1>
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $DATABASE_HOST = 'localhost';
            $DATABASE_USER = 'root';
            $DATABASE_PASS = '';
            $DATABASE_NAME = 'testing';

            $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

            if (mysqli_connect_errno()) {
                exit('Failed to connect to MySQL: ' . mysqli_connect_error());
            }

            if (!isset($_POST['username'], $_POST['password'], $_POST['confirm_password'], $_POST['email'])) {
                exit('Please complete the registration form!');
            }

            if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['confirm_password']) || empty($_POST['email'])) {
                exit('Please complete the registration form');
            }

            if ($_POST['password'] !== $_POST['confirm_password']) {
                exit('Passwords do not match!');
            }

            if ($stmt = $con->prepare('SELECT id FROM accounts WHERE username = ? OR email = ?')) {
                $stmt->bind_param('ss', $_POST['username'], $_POST['email']);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    echo 'Username or email already exists, please choose another!';
                } else {
                    if ($stmt = $con->prepare('INSERT INTO accounts (username, password, email) VALUES (?, ?, ?)')) {
                        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $stmt->bind_param('sss', $_POST['username'], $password, $_POST['email']);
                        $stmt->execute();
                        echo 'You have successfully registered!';
                    } else {
                        echo 'Could not prepare statement!';
                    }
                }
                $stmt->close();
            } else {
                echo 'Could not prepare statement!';
            }
            $con->close();
        }
        ?>

        <form action="register.php" method="post">
            <label for="username"></label>
            <input type="text" name="username" placeholder="Username" id="username" required>
            <label for="password"></label>
            <input type="password" name="password" placeholder="Password" id="password" required>
            <label for="confirm_password"></label>
            <input type="password" name="confirm_password" placeholder="Confirm Password" id="confirm_password" required>
            <label for="email"></label>
            <input type="email" name="email" placeholder="Email" id="email" required>
            <input type="submit" value="Register">
            <a href="login.php">Already have an account? Login Now!</a>
        </form>
    </div>
</body>
</html>
