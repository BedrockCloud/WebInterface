<?php
session_start();
require("./util/Utils.php");
require("./rest/RestAPI.php");

use rest\RestAPI;
use util\Utils;

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (RestAPI::getAccountData($_SESSION['username'])["initialPassword"]) {
    header("Location: resetpassword.php?name=" . $_SESSION['username']);
    exit;
}

if (!RestAPI::isAdmin($_SESSION['username'])) {
    Utils::showModalRedirect("ERROR", "Unauthorized Access", "You are not authorized to view this page.", "index.php");
    exit;
}

if (!isset($_SESSION["CSRF"])) {
    $_SESSION["CSRF"] = Utils::generateString(25);  // CSRF Token erstellen, falls nicht vorhanden
}

if (isset($_POST["submit"]) && isset($_SESSION["CSRF"])) {
    if ($_POST["CSRFToken"] != $_SESSION["CSRF"]) {
        Utils::logOut();
    } else {
        $name = htmlspecialchars($_GET["name"]);
        $password = trim($_POST["password"]);
        $role = $_POST["role"];

        if (!empty($password)) {
            if (strlen($password) < 8) {
                Utils::showModal("ERROR", "Password too short", "Password must be at least 8 characters long.");
                exit;
            }

            RestAPI::updatePassword($name, $password);
        }

        RestAPI::updateRole($name, $role);

        Utils::showModalRedirect("SUCCESS", "Success!", "The account has been updated successfully.", "accounts.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Account</title>
    <link rel="stylesheet" href="css/master.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/jquery.sweet-modal.min.css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://kit.fontawesome.com/953731a208.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script src="js/jquery.sweet-modal.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <link rel="icon" type="image/x-icon" href="css/favicon.ico">
</head>
<body>
<div class="container">
    <div class="sidebar">
        <ul>
            <?php Utils::injectSideBar(); ?>
        </ul>
    </div>
    <div class="header">
        <?php Utils::injectHeader(); ?>
    </div>
    <div class="content">
        <div class="mobilenavbar">
            <nav>
                <ul class="navbar animated bounceInDown">
                    <?php Utils::injectSideBar(); ?>
                </ul>
            </nav>
        </div>

        <div class="flex-container animated fadeIn">
            <div class="flex item-1 sidebox">
                <?php
                if (!isset($_GET["name"])) {
                    Utils::showModalRedirect("ERROR", "Action failed.", "No request was sent.", "accounts.php");
                    exit;
                }

                $name = htmlspecialchars($_GET["name"]);
                ?>

                <h1>Edit Account of <?php echo $name; ?></h1>
                <form action="editaccount.php?name=<?php echo $name; ?>" method="post">
                    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                    <p>New Password</p>
                    <label>
                        <input type="password" name="password" placeholder="New Password">
                    </label>
                    <br>
                    <p>Role</p>
                    <label>
                        <select name="role">
                            <option value="default" <?php echo RestAPI::isAdmin($name) ? '' : 'selected'; ?>>Default</option>
                            <option value="admin" <?php echo RestAPI::isAdmin($name) ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </label>
                    <br><br>
                    <button type="submit" name="submit">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>