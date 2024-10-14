<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
    <div class="login">
        <h1>Login</h1>
        <?php
        session_start();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $DATABASE_HOST = 'localhost';
            $DATABASE_USER = 'root';
            $DATABASE_PASS = '';
            $DATABASE_NAME = 'testing';
            $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

            if (mysqli_connect_errno()) {
                exit('Failed to connect to MySQL: ' . mysqli_connect_error());
            }

            if (!isset($_POST['username'], $_POST['password'])) {
                exit('Please fill both the username and password fields!');
            }

            if ($stmt = $con->prepare('SELECT id, password FROM accounts WHERE username = ?')) {
                $stmt->bind_param('s', $_POST['username']);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($id, $password);
                    $stmt->fetch();
                    if (password_verify($_POST['password'], $password)) {
                        session_regenerate_id();
                        $_SESSION['loggedin'] = TRUE;
                        $_SESSION['name'] = $_POST['username'];
                        $_SESSION['id'] = $id;

                        // Debugging output
                        echo 'Login successful! Redirecting to home page...';
                        
                        // Ensure no further output
                        ob_start();
                        header('Location: home.php');
                        ob_end_flush();
                        exit(); // Ensure the script stops executing after the redirect
                    } else {
                        echo 'Incorrect username and/or password!';
                    }
                } else {
                    echo 'Incorrect username and/or password!';
                }
                $stmt->close();
            }
            $con->close();
        }
        ?>
        <form action="login.php" method="post">
            <label for="username"></label>
            <input type="text" name="username" placeholder="Username" id="username" required>
            <label for="password"></label>
            <input type="password" name="password" placeholder="Password" id="password" required>
            <input type="submit" value="Login">
            <a href="register.php">Don't have an account? Register Now!</a>
        </form>
    </div>
</body>
</html>
