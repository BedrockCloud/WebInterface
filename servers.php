<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Servers</title>
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
            if (!empty($template = $_POST["template"])) {
                if ($_POST["CSRFToken"] != $_SESSION["CSRF"]) {
                    Utils::logOut();
                } else {
                    $count = intval($_POST["count"]);
                    if (($msg = RestAPI::startServer($template, $count)) !== null) {
                        Utils::showModal("SUCCESS", "Success", $msg);
                    } else {
                        Utils::showModal("ERROR", "Action failed.", "Something went wrong... The max server count could already be reached!");
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
                <h1>Servers</h1>
                <input type="text" name="server" id="server" placeholder="Search for a server..." required>
                <div id="result"></div>
                <script>
                    $(document).ready(function() {
                        load_data();
                        function load_data(query) {
                            $.ajax({
                                url: "fetch.php?type=SEARCH_SERVERS",
                                method: "post",
                                data: {query: query},
                                success: function(data) {
                                    $('#result').html(data);
                                }
                            });
                        }

                        $('#server').keyup(function() {
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
                <form action="servers.php" method="post">
                    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                    <label>
                        <select name="template" required>
                            <?php

                            $output = "";
                            foreach (RestAPI::getCurrentTemplates() as $currentTemplate) {
                                $output .= "<option value=" . $currentTemplate . ">" . $currentTemplate . "</option>";
                            }

                            echo $output;
                            ?>
                        </select>
                    </label>
                    <br>
                    <label>
                        <input type="number" name="count" placeholder="1" value="1" required>
                    </label>
                    <button type="submit" name="submit">Start Server</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
