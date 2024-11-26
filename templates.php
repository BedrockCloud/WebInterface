<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Templates</title>
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
            if (isset($_POST["type"]) && $_POST["type"] == "create-template") {
                if (
                    isset($_POST["template"]) &&
                    isset($_POST["templateType"]) &&
                    isset($_POST["lobby"]) &&
                    isset($_POST["maintenance"]) &&
                    isset($_POST["static"]) &&
                    isset($_POST["autoStart"]) &&
                    isset($_POST["startNewWhenFull"]) &&
                    isset($_POST["maxPlayerCount"]) &&
                    isset($_POST["minServerCount"]) &&
                    isset($_POST["maxServerCount"])
                ) {
                    if (RestAPI::createTemplate($_POST["template"],
                        $_POST["templateType"],
                        $_POST["lobby"] == "true", $_POST["maintenance"] == "true", $_POST["static"] == "true",
                        intval($_POST["maxPlayerCount"]), intval($_POST["minServerCount"]), intval($_POST["maxServerCount"]),
                        $_POST["startNewWhenFull"] == "true", $_POST["autoStart"] == "true"
                    )) {
                        Utils::showModal("SUCCESS", "Success!", "The template has been created!");
                    } else Utils::showModal("ERROR", "Action failed.", "A template with that name already exists!");
                }
            }
        }
        ?>
        <div class="flex-container animated fadeIn">
            <div class="flex item-1">
                <h1>Templates</h1>
                <label for="server"></label><input type="text" name="server" id="server" placeholder="Search for a template..." required>
                <div id="result"></div>
                <script>
                    $(document).ready(function() {
                        load_data();
                        function load_data(query) {
                            $.ajax({
                                url: "fetch.php?type=SEARCH_TEMPLATES",
                                method: "post",
                                data: {query: query},
                                success: function(data)  {
                                    $('#result').html(data);
                                }
                            });
                        }

                        $('#server').keyup(function(){
                            var search = $(this).val();
                            if (search !== '')  {
                                load_data(search);
                            } else  {
                                load_data();
                            }
                        });
                    });
                </script>
            </div>
            <div class="flex item-2 sidebox">
                <h1>Actions</h1>
                <form action="templates.php" method="post">
                    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                    <input type="hidden" name="type" value="create-template">
                    <label>
                        <input type="text" name="template" placeholder="Lobby" required>
                    </label>
                    <label>
                        <select name="templateType">
                            <option value="SERVER">This is not a proxy template</option>
                            <option value="PROXY">This is a proxy template</option>
                        </select>
                    </label>
                    <label>
                        <select name="lobby">
                            <option value="false">This is not lobby template</option>
                            <option value="true">This is a lobby template</option>
                        </select>
                    </label>
                    <label>
                        <select name="maintenance">
                            <option value="true">This template is in maintenance</option>
                            <option value="false">This template is not maintenance</option>
                        </select>
                    </label>
                    <label>
                        <select name="static">
                            <option value="false">This template is not static</option>
                            <option value="true">This template is static</option>
                        </select>
                    </label>
                    <label>
                        <select name="autoStart">
                            <option value="true">Servers should start automatically</option>
                            <option value="false">Servers shouldn't start automatically</option>
                        </select>
                    </label>
                    <label>
                        <select name="startNewWhenFull">
                            <option value="true">New servers should start automatically when a server is full</option>
                            <option value="false">New servers shouldn't start automatically when a server is full</option>
                        </select>
                    </label>
                    <p>Max Player Count</p>
                    <label>
                        <input type="number" name="maxPlayerCount" value="20" required>
                    </label>
                    <p>Min Server Count</p>
                    <label>
                        <input type="number" name="minServerCount" value="0" required>
                    </label>
                    <p>Max Server Count</p>
                    <label>
                        <input type="number" name="maxServerCount" value="2" required>
                    </label>
                    <button type="submit" name="submit">Create Template</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
