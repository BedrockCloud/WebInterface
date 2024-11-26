<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Player Overview</title>
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

if (!isset($_POST["submit"]) || !isset($_POST["type"])) {
    if (empty($_GET["player"]) || !RestAPI::isOnline($_GET["player"])) {
        header("Location: players.php");
        exit;
    }
}
?>

<body>
<?php

if (!Utils::checkCloudStatus()) {
    Utils::showModal("ERROR", "Cloud status...", "The Cloud seems to be not running right now, try again later.");
}

$playerData = RestAPI::getPlayerData($_GET["player"] ?? $_POST["player"]);
$currentServer = $playerData["currentServer"];
$currentProxy = $playerData["currentProxy"];
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
            <?php
            if (isset($_POST["player"]) && isset($_POST["submit"])) {
                $player = $_POST["player"];
                switch ($_POST["type"]) {
                    case "kick": {
                        $reason = "";
                        if (!empty($_POST["reason"])) $reason = $_POST["reason"];

                        if (RestAPI::kickPlayer($player, $reason)) {
                            Utils::showModalRedirect("SUCCESS", "Success!", "The player was kicked successfully!", "players.php");
                        } else {
                            Utils::showModalRedirect("ERROR", "Failure!", "The player wasn't kicked successfully!", "players.php");
                        }

                        exit;
                    }
                    case "text": {
                        $text = $_POST["text"];
                        $textType = strtolower($_POST["text_type"]);

                        if (RestAPI::textPlayer($player, $textType, $text)) {
                            Utils::showModal("SUCCESS", "Success!", "The message was sent to the player!");
                            break;
                        } else {
                            Utils::showModalRedirect("SUCCESS", "Failure!", "The message wasn't send to the player!", "players.php");
                            exit;
                        }
                    }
                    case "add-maintenance": {
                        if (RestAPI::addToMaintenanceList($player)) {
                            Utils::showModal("SUCCESS", "Success!", "The player has been added to the maintenance list!");
                        } else {
                            Utils::showModal("ERROR", "Action failed.", "The player might already be on the maintenance list!");
                        }

                        break;
                    }
                    case "remove-maintenance": {
                        if (RestAPI::removeFromMaintenanceList($player)) {
                            Utils::showModal("SUCCESS", "Success!", "The player has been removed from the maintenance list!");
                        } else {
                            Utils::showModal("ERROR", "Action failed.", "The player might not be on the maintenance list!");
                        }

                        break;
                    }
                    default: {
                        header("Location: players.php");
                        exit;
                    }
                }
            }
            ?>
            <div class="flex item-1">
                <h1>
                    <?php
                    echo $player = ($_GET["player"] ?? $_POST["player"]);
                    ?>
                </h1>
                <h2>General Data</h2>
                <table class="highlight">
                    <tr>
                        <th>Current-Server</th>
                        <th>Current-Proxy</th>
                        <th>XUID</th>
                        <th>UUID</th>
                    </tr>
                    <tr>
                        <td><?php echo ($currentServer === null ? "None" : "<a href='server.php?server=" . $currentServer . "'>" . $currentServer . "</a>") ?></td>
                        <td><?php echo ($currentProxy === null ? "None" : "<a href='server.php?server=" . $currentProxy . "'>" . $currentProxy . "</a>") ?></td>
                        <td><?php echo $playerData["xboxUserId"]; ?></td>
                        <td><?php echo $playerData["uniqueId"]; ?></td>
                    </tr>
                </table>
            </div>
            <div class="flex item-2 sidebox">
                <h1>Actions</h1>
                <br> <br>
                <form action="player.php" method="post">
                    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                    <input type="hidden" name="type" value="kick">
                    <input type="hidden" name="player" value="<?php echo $player; ?>">
                    <label>
                        <input type="text" name="reason" placeholder="Reason">
                    </label>
                    <button type="submit" name="submit">Kick Player</button>
                </form>
                <br>
                <form action="player.php" method="post">
                    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                    <input type="hidden" name="type" value="text">
                    <input type="hidden" name="player" value="<?php echo $player; ?>">
                    <label>
                        <input type="text" name="text" placeholder="Hi!">
                        <select name="text_type">
                            <option value="message">Message</option>
                            <option value="popup">Popup</option>
                            <option value="tip">Tip</option>
                            <option value="title">Title</option>
                            <option value="action_bar">Actionbar Message</option>
                            <option value="toast_notification">Toast Notification</option>
                        </select>
                    </label>
                    <button type="submit" name="submit">Text Player</button>
                </form>
                <form action="player.php" method="post">
                    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                    <input type="hidden" name="player" value="<?php echo $player; ?>">
                    <label>
                        <br>
                        <select name="type" required>
                            <option value="add-maintenance">Add to Maintenance List</option>
                            <option value="remove-maintenance">Remove from Maintenance List</option>
                        </select>
                        <br>
                    </label>
                    <button type="submit" name="submit">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>