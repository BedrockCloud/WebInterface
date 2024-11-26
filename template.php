<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Template Overview</title>
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
    if (empty($_GET["template"]) || RestAPI:: getTemplateData($_GET["template"]) === null) {
        header("Location: templates.php");
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
                    echo $template = ($_GET["template"] ?? $_POST["template"]);

                    $data = RestAPI::getTemplateData($template);

                    if (isset($_POST["submit"])) {
                        if (isset($_POST["type"])) {
                            switch ($_POST["type"]) {
                                case "stop-template": {
                                    if (RestAPI::stopServer($_POST["template"])) {
                                        Utils::showModalRedirect("SUCCESS", "Success!", "The template has been stopped.", "templates.php");
                                    } else {
                                        Utils::showModalRedirect("ERROR", "Action failed.", "The template couldn't be stopped!", "templates.php");
                                    }

                                    exit;
                                }
                                case "remove-template": {
                                    if (RestAPI::removeTemplate($_POST["template"])) {
                                        Utils::showModalRedirect("SUCCESS", "Success!", "The template has been removed.", "templates.php");
                                    } else {
                                        Utils::showModalRedirect("ERROR", "Action failed.", "The template couldn't be removed!", "templates.php");
                                    }

                                    exit;
                                }
                            }
                        }
                    }
                    ?>
                </h1>
                <div id="dataOutput"></div>
                <script>
                    let dataOutputTimeout;
                    const updateDataOutputDiv = function () {
                        $('#dataOutput').load('fetch.php?type=TEMPLATE_DATA&template=<?php echo $template?>', function () {
                            dataOutputTimeout = window.setTimeout(updateDataOutputDiv, 2000);
                        });
                    }

                    dataOutputTimeout = window.setTimeout(updateDataOutputDiv, 100);
                </script>
                <h2>Server List</h2>
                <div id="serverListOutput"></div>
                <script>
                    let serverListOutputTimeout;
                    const serverListOutputDiv = function () {
                        $('#serverListOutput').load('fetch.php?type=SERVER_LIST&template=<?php echo $template?>', function () {
                            serverListOutputTimeout = window.setTimeout(serverListOutputDiv, 2000);
                        });
                    }

                    serverListOutputTimeout = window.setTimeout(serverListOutputDiv, 100);
                </script>
            </div>
            <div class="flex item-2 sidebox">
                <h1>Actions</h1>
                <form action="edittemplate.php?name=<?php echo $template; ?>" method="post">
                    <button type="submit">Edit Template</button>
                </form>
                <form action="template.php" method="post">
                    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                    <input type="hidden" name="type" value="stop-template">
                    <input type="hidden" name="template" value="<?php echo $template; ?>">
                    <button type="submit" name="submit">Stop Template</button>
                </form>
                <form action="template.php" method="post">
                    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                    <input type="hidden" name="type" value="remove-template">
                    <input type="hidden" name="template" value="<?php echo $template; ?>">
                    <button type="submit" name="submit">Remove Template</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>