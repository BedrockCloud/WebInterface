<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Modules</title>
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

if (!Utils::checkCloudStatus()) {
    Utils::showModal("ERROR", "Cloud status...", "The Cloud seems to be not running right now, try again later.");
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
        <?php

        if (isset($_POST["submit"])) {
            if (isset($_POST["module"]) && isset($_POST["value"])) {
                if (RestAPI::editModule($_POST["module"], $_POST["value"] == "true")) {
                    Utils::showModal("SUCCESS", "Success!", "The module has been " . ($_POST["value"] == "true" ? "enabled" : "disabled") . "!");
                } else {
                    Utils::showModal("ERROR", "Action failed.", "The module couldn't be edited!");
                }
            }
        }
        ?>
        <div class="flex-container animated fadeIn">
            <div class="flex item-1">
                <h1>Modules</h1>
                <table>
                    <tr>
                        <th>Module</th>
                        <th>Status</th>
                    </tr>
                    <?php

                    foreach (RestAPI::getModules() as $module) {
                        $data = RestAPI::getModuleData($module);
                        $output = "<tr><td>" . ucfirst($module) . "</td>";
                        $output .= "<td><strong><p style='color: " . match ($data["enabled"]) {
                                false => "red",
                                default => "lime"
                            } . ";'>" . ($data["enabled"] ? "ENABLED" : "DISABLED") . "</p></strong></td></tr>";
                        echo $output;
                    }
                    ?>
                </table>
            </div>
            <div class="flex item-2 sidebox">
                <h1>Actions</h1>
                <form action="modules.php" method="post">
                    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                    <label>
                        <select name="module" required>
                            <?php
                            foreach (RestAPI::getModules() as $module) {
                                echo "<option value='" . $module . "'>" . ucfirst($module) . "</option>";
                            }
                            ?>
                        </select>
                        <br>
                        <select name="value" required>
                            <option value="true">Enabled</option>
                            <option value="false">Disabled</option>
                        </select>
                        <br>
                    </label>
                    <button type="submit" name="submit">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
