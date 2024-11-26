<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Edit Template</title>
    <link rel="stylesheet" href="css/master.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/jquery.sweet-modal.min.css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://kit.fontawesome.com/953731a208.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script src="js/jquery.sweet-modal.min.js"></script>
    <meta name="viewport" content="width=device-width,
            initial-scale=1.0,
            minimum-scale=1.0">
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

if (RestAPI::getAccountData($_SESSION['username'])["initialPassword"]) {
    header("Location: resetpassword.php?name=" . $_SESSION['username']);
    exit;
}

Utils::validateSession();
?>

<body>
<?php

if (!RestAPI::isAdmin($_SESSION['username'])) {
    Utils::showModalRedirect("ERROR", "Action failed.", "You are not able to view this page.", "index.php");
    exit;
}

?>
<div class="container">
    <div class="sidebar">
        <ul>
            <?php
            Utils::injectSideBar();
            ?>
        </ul>
    </div>
    <div class="header">
        <?php
        Utils::injectHeader();
        ?>
    </div>
    <div class="content">
        <div class="mobilenavbar">
            <nav>
                <ul class="navbar animated bounceInDown">
                    <?php
                    Utils::injectSideBar();
                    ?>
                </ul>
            </nav>
        </div>
        <script type="text/javascript">
            $(document).ready(function() {
                $('.menu').click(function() {
                    $('ul').toggleClass("navactive");
                })
            })
        </script>
        <div class="flex-container animated fadeIn">
            <div class="flex item-1 sidebox">
                <?php
                if (!isset($_GET["name"])) {
                    Utils::showModalRedirect("ERROR", "Action failed.", "No request was sent.", "accounts.php");
                    exit;
                }

                $data = RestAPI::getTemplateData($name = $_GET["name"]);

                if (isset($_POST["submit"]) && isset($_SESSION["CSRF"])) {
                    if ($_POST["CSRFToken"] != $_SESSION["CSRF"]) {
                        Utils::logOut();
                    } else {
                        if (
                            isset($_POST["lobby"]) &&
                            isset($_POST["maintenance"]) &&
                            isset($_POST["static"]) &&
                            isset($_POST["autoStart"]) &&
                            isset($_POST["startNewWhenFull"]) &&
                            isset($_POST["maxPlayerCount"]) &&
                            isset($_POST["minServerCount"]) &&
                            isset($_POST["maxServerCount"])
                        ) {
                            if (RestAPI::editTemplate($name,
                                $_POST["lobby"] == "true", $_POST["maintenance"] == "true", $_POST["static"] == "true",
                                intval($_POST["maxPlayerCount"]), intval($_POST["minServerCount"]), intval($_POST["maxServerCount"]),
                                $_POST["startNewWhenFull"] == "true", $_POST["autoStart"] == "true"
                            )) {
                                Utils::showModalRedirect("SUCCESS", "Success!", "The template has been edited!", "template.php?template=" . $name);
                            } else Utils::showModalRedirect("ERROR", "Action failed.", "A template couldn't be edited!", "template.php?template=" . $name);

                            exit;
                        }
                    }
                } else {
                    $_SESSION["CSRF"] = Utils::generateString(25);
                }
                ?>
                <h1><?php echo "Edit Template: " . $_GET["name"]; ?></h1>
                <form action="edittemplate.php?name=<?php echo $_GET["name"]; ?>" method="post">
                    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                    <form action="templates.php" method="post">
                        <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                        <label>
                            <select name="lobby">
                                <?php
                                if ($data["lobby"]) {
                                    ?>
                                    <option value="true">This is a lobby template</option>
                                    <option value="false">This is not lobby template</option>
                                    <?php
                                } else {
                                    ?>
                                    <option value="false">This is not lobby template</option>
                                    <option value="true">This is a lobby template</option>
                                    <?php
                                }
                                ?>
                            </select>
                        </label>
                        <label>
                            <select name="maintenance">
                                <?php
                                if ($data["maintenance"]) {
                                    ?>
                                    <option value="true">This template is in maintenance</option>
                                    <option value="false">This template is not maintenance</option>
                                    <?php
                                } else {
                                    ?>
                                    <option value="false">This template is not maintenance</option>
                                    <option value="true">This template is in maintenance</option>
                                    <?php
                                }
                                ?>
                            </select>
                        </label>
                        <label>
                            <select name="static">
                                <?php
                                if ($data["static"]) {
                                    ?>
                                    <option value="true">This template is static</option>
                                    <option value="false">This template is not static</option>
                                    <?php
                                } else {
                                    ?>
                                    <option value="false">This template is not static</option>
                                    <option value="true">This template is static</option>
                                    <?php
                                }
                                ?>
                            </select>
                        </label>
                        <label>
                            <select name="autoStart">
                                <?php
                                if ($data["autoStart"]) {
                                    ?>
                                    <option value="true">Servers should start automatically</option>
                                    <option value="false">Servers shouldn't start automatically</option>
                                    <?php
                                } else {
                                    ?>
                                    <option value="false">Servers shouldn't start automatically</option>
                                    <option value="true">Servers should start automatically</option>
                                    <?php
                                }
                                ?>
                            </select>
                        </label>
                        <label>
                            <select name="startNewWhenFull">
                                <?php
                                if ($data["startNewWhenFull"]) {
                                    ?>
                                    <option value="true">New servers should start automatically when a server is full</option>
                                    <option value="false">New servers shouldn't start automatically when a server is full</option>
                                    <?php
                                } else {
                                    ?>
                                    <option value="false">New servers shouldn't start automatically when a server is full</option>
                                    <option value="true">New servers should start automatically when a server is full</option>
                                    <?php
                                }
                                ?>
                            </select>
                        </label>
                        <p>Max Player Count</p>
                        <label>
                            <input type="number" name="maxPlayerCount" value="<?php echo $data["maxPlayerCount"]; ?>" required>
                        </label>
                        <p>Min Server Count</p>
                        <label>
                            <input type="number" name="minServerCount" value="<?php echo $data["minServerCount"]; ?>" required>
                        </label>
                        <p>Max Server Count</p>
                        <label>
                            <input type="number" name="maxServerCount" value="<?php echo $data["maxServerCount"]; ?>" required>
                        </label>
                        <button type="submit" name="submit">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>