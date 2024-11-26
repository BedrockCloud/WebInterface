<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Server Overview</title>
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

if (!isset($_POST["submit"])) {
    if (empty($_GET["server"]) || RestAPI:: getServerData($_GET["server"]) === null) {
        header("Location: servers.php");
        exit;
    }
}
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
        <div class="flex-container animated fadeIn">
            <div class="flex item-1">
                <h1>
                    <?php
                    echo $server = ($_GET["server"] ?? $_POST["server"]);

                    $data = RestAPI::getServerData($server);
                    $templateType = RestAPI::getTemplateData($data["template"])["templateType"];

                    if (isset($_POST["submit"])) {
                        if (isset($_POST["command"])) {
                            RestAPI::sendCommandTo($server, $_POST["command"], $_SESSION["username"]);
                        } else if (isset($_POST["type"])) {
                            switch ($_POST["type"]) {
                                case "stop": {
                                    if (RestAPI::stopServer($_POST["server"])) {
                                        Utils::showModalRedirect("SUCCESS", "Success!", "The server has been stopped.", "servers.php");
                                    } else {
                                        Utils::showModalRedirect("ERROR", "Action failed.", "The server couldn't be stopped!", "servers.php");
                                    }

                                    exit;
                                }
                                case "stop-template": {
                                    if (RestAPI::stopServer($_POST["template"])) {
                                        Utils::showModalRedirect("SUCCESS", "Success!", "The template has been stopped.", "servers.php");
                                    } else {
                                        Utils::showModalRedirect("ERROR", "Action failed.", "The template couldn't be stopped!", "servers.php");
                                    }

                                    exit;
                                }
                                case "save": {
                                    if (RestAPI::saveServer($_POST["server"])) {
                                        Utils::showModal("SUCCESS", "Success!", "The server was saved!");
                                    } else {
                                        Utils::showModal("ERROR", "Action failed.", "The server couldn't be saved!");
                                    }
                                }
                            }
                        }
                    }
                    ?>
                </h1>
                <div id="dataOutput"></div>
                <div id="consoleOutput" class="flex-console"></div>
                <script>
                    let dataOutputTimeout;
                    const updateDataOutputDiv = function () {
                        $('#dataOutput').load('fetch.php?type=SERVER_DATA&server=<?php echo $server?>', function () {
                            dataOutputTimeout = window.setTimeout(updateDataOutputDiv, 2000);
                        });
                    }

                    dataOutputTimeout = window.setTimeout(updateDataOutputDiv, 100);

                    let firstScroll = true;
                    let consoleOutputTimeout;
                    const consoleOutputElement = $('#consoleOutput');
                    const updateConsoleOutputDiv = function () {
                        consoleOutputElement.load('fetch.php?type=SERVER_CONSOLE&server=<?php echo $server ?>&templateType=<?php echo $templateType; ?>', function () {
                            if (firstScroll) {
                                firstScroll = false;
                                consoleOutputElement.animate({"scrollTop": consoleOutputElement[0].scrollHeight}, "fast");
                            }

                            consoleOutputTimeout = window.setTimeout(updateConsoleOutputDiv, 2000);
                        });
                    };

                    consoleOutputTimeout = window.setTimeout(updateConsoleOutputDiv, 100);
                </script>
                <form action="server.php" method="post">
                    <label>
                        <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                        <input id="server" type="hidden" name="server" value="<?php echo $server; ?>">
                        <input id="command" type="text" name="command" placeholder="Type command..." required>
                        <button name="submit" type="submit">Send</button>
                    </label>
                </form>
            </div>
            <div class="flex item-2 sidebox">
                <h1>Actions</h1>
                <form action="server.php" method="post">
                    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                    <input type="hidden" name="type" value="save">
                    <input type="hidden" name="server" value="<?php echo $server; ?>">
                    <button type="submit" name="submit">Save Server</button>
                </form>
                <form action="server.php" method="post">
                    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                    <input type="hidden" name="type" value="stop">
                    <input type="hidden" name="server" value="<?php echo $server; ?>">
                    <button type="submit" name="submit">Stop Server</button>
                </form>
                <form action="server.php" method="post">
                    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                    <input type="hidden" name="type" value="stop-template">
                    <input type="hidden" name="server" value="<?php echo $server; ?>">
                    <input type="hidden" name="template" value="<?php echo $data["template"]; ?>">
                    <button type="submit" name="submit">Stop Template</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>