<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
    <script src="https://kit.fontawesome.com/953731a208.js" crossorigin="anonymous"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="css/favicon.ico">
</head>
<body>
<form class="login" action="login.php" method="post">
    <?php
    use rest\RestAPI;
    use util\Utils;

    require("./util/Utils.php");
    require("./rest/RestAPI.php");

    session_start();

    if (!isset($_SESSION['CSRF'])) {
        $_SESSION['CSRF'] = bin2hex(random_bytes(32));
    }

    if (isset($_POST["submit"])) {
        if (!isset($_POST['CSRFToken']) || $_POST['CSRFToken'] !== $_SESSION['CSRF']) {
            echo '<div class="error"><h4>Invalid request. Please try again.</h4></div>';
            exit;
        }

        $username = trim($_POST["username"]);
        $password = $_POST["password"];

        if (!empty($username) && !empty($password)) {
            if (!Utils::checkCloudStatus()) {
                echo '<div class="error"><h4>The Cloud seems to be not running right now, try again later.</h4></div>';
                return;
            }

            $data = RestAPI::getAccountData($username);

            if ($data !== null && password_verify($password, $data["password"])) {
                $_SESSION['username'] = $data["username"];

                if ($data["initialPassword"]) {
                    header("Location: resetpassword.php?name=" . urlencode($data["username"]));
                    exit;
                }

                header("Location: index.php");
                exit;
            } else {
                echo '<div class="error"><h4>Login failed. Incorrect username or password.</h4></div>';
            }
        } else {
            echo '<div class="error"><h4>Please enter both username and password.</h4></div>';
        }
    }

    if (isset($_GET["forgot_password"]) && $_GET["forgot_password"]) {
        echo '<div class="error"><h4>Please contact an administrator to reset your password.</h4></div>';
    }
    ?>
    <h1><i class="fas fa-user"></i> Login</h1>
    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION['CSRF']; ?>">
    <label>
        <input type="text" name="username" placeholder="Username" autocomplete="username" required>
    </label>
    <label>
        <input type="password" name="password" placeholder="Password" autocomplete="current-password" required>
    </label>
    <br>
    <button type="submit" name="submit">Login</button>
    <br><br><br><br>
    <a href="login.php?forgot_password=true"><i class="fas fa-key"></i> Forgot Password?</a>
</form>
</body>
</html>