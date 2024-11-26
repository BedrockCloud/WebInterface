<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Search</title>
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

        if (isset($_POST["submit"]) && isset($_SESSION["CSRF"])) {
            if (!empty($player = $_POST["player"])) {
                if ($_POST["CSRFToken"] != $_SESSION["CSRF"]) {
                    Utils::logOut();
                } else {
                    if (isset($_POST["action"])) {
                        switch ($_POST["action"]) {
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
                            }
                        }
                    }
                }
            } else {
                Utils::showModal("ERROR", "Action failed.", "The request is invalid.");
            }
        } else {
            $_SESSION["CSRF"] = Utils::generateString(25);
        }

        ?>
        <div class="flex-container animated fadeIn">
            <div class="flex item-1">
                <h1>Players</h1>
                <label for="username"></label><input type="text" name="username" id="username" placeholder="Search for a player..." required>
                <div id="result"></div>
                <script>
                    $(document).ready(function() {
                        load_data();
                        function load_data(query) {
                            $.ajax({
                                url: "fetch.php?type=SEARCH",
                                method: "post",
                                data: {query: query},
                                success: function(data) {
                                    $('#result').html(data);
                                }
                            });
                        }

                        $('#username').keyup(function() {
                            var search = $(this).val();
                            if (search !== '') {
                                load_data(search);
                            } else {
                                load_data();
                            }
                        });
                    });
                </script>
            </div>
            <div class="flex item-2 sidebox">
                <h1>Actions</h1>
                <form action="players.php" method="post">
                    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                    <label>
                        <input type="text" name="player" placeholder="r3pt1s" required>
                    </label>
                    <br>
                    <label>
                        <select name="action" required>
                            <option value="add-maintenance">Add to Maintenance List</option>
                            <option value="remove-maintenance">Remove from Maintenance List</option>
                        </select>
                    </label>
                    <br>
                    <button type="submit" name="submit">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
