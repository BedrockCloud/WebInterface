<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Overview</title>
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

<?php
use rest\RestAPI;
use util\Utils;

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

require("./util/Utils.php");
require("./rest/RestAPI.php");

Utils::validateSession();
?>

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
        <script>
            $(document).ready(function () {
                $('.menu').click(function () {
                    $('ul').toggleClass("navactive");
                });
            });
        </script>
        <div class="flex-container animated fadeIn">
            <div class="flex item-1">
                <?php echo "<h1>Welcome, " . htmlspecialchars($_SESSION["username"]) . "!</h1>"; ?>
            </div>
        </div>
        <div class="flex-container animated fadeIn">
            <div class="flex item-1" id="playerCountOutput"></div>
            <div class="flex item-2" id="serverCountOutput"></div>
            <div class="flex item-3" id="templateCountOutput"></div>
        </div>
        <script>
            function updateDiv(id, type) {
                const update = function () {
                    $(`#${id}`).load(`fetch.php?type=${type}`, function () {
                        setTimeout(update, 2000);
                    });
                };
                setTimeout(update, 100);
            }

            $(document).ready(function () {
                updateDiv("playerCountOutput", "PLAYER_COUNT");
                updateDiv("serverCountOutput", "SERVER_COUNT");
                updateDiv("templateCountOutput", "TEMPLATE_COUNT");
            });
        </script>
        <div class="flex-container animated fadeIn">
            <div class="flex item-2">
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION["CSRF"])) {
                    if ($_POST["CSRFToken"] !== $_SESSION["CSRF"]) {
                        Utils::logOut();
                        exit;
                    }

                    $data = RestAPI::getAccountData($_SESSION["username"]);
                    if (!$data || !password_verify($_POST["current_password"], $data["password"])) {
                        Utils::showModal("ERROR", "Action failed.", "Incorrect current password!");
                        exit;
                    }

                    if ($_POST["new_password"] !== $_POST["new_password_repeat"]) {
                        Utils::showModal("ERROR", "Action failed.", "Passwords do not match!");
                        exit;
                    }

                    if (strlen($_POST["new_password"]) < 12) {
                        Utils::showModal("ERROR", "Action failed.", "Password must be at least 12 characters!");
                        exit;
                    }

                    RestAPI::updatePassword($data["username"], $_POST["new_password"]);
                    Utils::showModal("SUCCESS", "Success", "Your password has been changed!");
                    exit;
                } else {
                    $_SESSION["CSRF"] = Utils::generateString(25);
                }
                ?>
                <h1>Change Password</h1>
                <form action="index.php" method="post">
                    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                    <label>
                        <input type="password" name="current_password" placeholder="Current Password" autocomplete="current-password" required>
                    </label>
                    <br>
                    <label>
                        <input type="password" name="new_password" placeholder="New Password" autocomplete="new-password" required>
                    </label>
                    <br>
                    <label>
                        <input type="password" name="new_password_repeat" placeholder="Repeat new Password" autocomplete="new-password" required>
                    </label>
                    <br>
                    <button type="submit" name="submit">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
