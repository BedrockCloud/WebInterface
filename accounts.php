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

if (!RestAPI::isAdmin($_SESSION['username'])) {
    Utils::showModalRedirect("ERROR", "Unauthorized Access", "You are not authorized to view this page.", "index.php");
    exit;
}

$accountData = RestAPI::getAccountData($_SESSION['username']);
if ($accountData["initialPassword"]) {
    header("Location: resetpassword.php?name=" . $_SESSION['username']);
    exit;
}

if (!isset($_SESSION["CSRF"])) {
    $_SESSION["CSRF"] = Utils::generateString(25); // CSRF Token erstellen, falls nicht vorhanden
}

// Handle Account Deletion
if (isset($_GET['delete']) && isset($_GET['name']) && !isset($_GET['confirmed'])) {
    $name = htmlspecialchars($_GET['name']);
    echo "<script>
        $.sweetModal.confirm('Are you sure you want to delete the account: $name?', function() {
            window.location.href = 'accounts.php?delete&name=$name&confirmed';
        });
    </script>";
}

// Handle Account Creation
if (isset($_POST['submit'])) {
    if ($_POST['CSRFToken'] !== $_SESSION['CSRF']) {
        Utils::logOut(); // Logout wenn CSRF Token nicht stimmt
        exit;
    }

    $username = htmlspecialchars($_POST['username']);
    $role = $_POST['role'];
    if (empty($username)) {
        Utils::showModal("ERROR", "Invalid Request", "Username cannot be empty.");
        exit;
    }

    if (RestAPI::getAccountData($username) === null) {
        $initialPassword = RestAPI::createAccount($username, $role);
        if ($initialPassword === null) {
            Utils::showModal("ERROR", "Account Creation Failed", "Something went wrong while creating the account.");
            exit;
        }

        Utils::showModalRedirect("SUCCESS", "Account Created", "The account has been successfully created. Initial password: <strong>$initialPassword</strong>", "accounts.php");
        exit;
    } else {
        Utils::showModal("ERROR", "Account Already Exists", "An account with this username already exists.");
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Accounts</title>
    <link rel="stylesheet" href="css/master.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/jquery.sweet-modal.min.css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://kit.fontawesome.com/953731a208.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script src="js/jquery.sweet-modal.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            <div class="flex item-1">
                <h1>Accounts</h1>
                <table>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Fully Initialized</th>
                        <th>Actions</th>
                    </tr>
                    <?php
                    foreach (RestAPI::getAccounts() as $account) {
                        echo "<tr>";
                        echo '<td><strong>' . htmlspecialchars($account["username"]) . '</strong></td>';
                        echo "<td>" . ucfirst($account["role"]) . "</td>";
                        echo "<td>" . ($account["initialPassword"] ? "No" : "Yes") . "</td>";
                        echo '<td><a href="editaccount.php?name=' . $account["username"] . '"><i class="material-icons">edit</i></a>
                                  <a href="accounts.php?delete&name=' . $account["username"] . '"><i class="material-icons">delete</i></a></td>';
                        echo "</tr>";
                    }
                    ?>
                </table>
            </div>

            <div class="flex item-2 sidebox">
                <h1>Create Account</h1>
                <form action="accounts.php" method="post">
                    <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION['CSRF']; ?>">
                    <label>
                        <input type="text" name="username" placeholder="Username" required>
                    </label>
                    <br>
                    <label>
                        <select name="role">
                            <option value="admin">Admin</option>
                            <option value="default">Default</option>
                        </select>
                    </label>
                    <button type="submit" name="submit">Create</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>