<?php

use rest\RestAPI;
use util\Utils;

require("./util/Utils.php");
require("./rest/RestAPI.php");

if ($_GET["type"] == "SEARCH") {
	$onlinePlayers = RestAPI::getCurrentPlayers();
	$output = '';
	$players = [];

	if (isset($_POST["query"])) {
        foreach ($onlinePlayers as $allPlayer) {
            if (str_starts_with($allPlayer, $_POST["query"])) {
                if (count($players) < 10) $players[] = $allPlayer;
            }
        }
	} else {
		$players = array_slice($onlinePlayers, 0, 10);
	}

	if (count($players) > 0) {
		$output .= '<table class="highlight">
				<tr>
					<th>Name</th>
					<th>Server</th>
					<th>Proxy</th>
				</tr>';
		foreach ($players as $player) {
            $playerInfo = RestAPI::getPlayerData($player);
			$output .= '<tr>
	 				<td><a href="player.php?player=' . $player . '">' . $player . '</a></td>';

            if (($currentServer = $playerInfo["currentServer"]) === null) {
                $output .= '<td style="color: red;">None</td>';
            } else {
                $output .= '<td><a href="server.php?server=' . $currentServer . '">' . $currentServer . '</a></td>';
            }

            if (($currentProxy = $playerInfo["currentProxy"]) === null) {
                $output .= '<td style="color: red;">None</td>';
            } else {
                $output .= '<td><a href="server.php?server=' . $currentProxy . '">' . $currentProxy . '</a></td>';
            }

            $output .= '</tr>';
        }
		echo $output;
	} else {
		if (isset($_POST["query"])) {
			echo '<h3 style="color: red;">No results.</h3>';
		} else {
			echo '<h3 style="color: red;">There are no players!</h3>';
		}
	}
} else if ($_GET["type"] == "SEARCH_SERVERS") {
    $onlineServers = RestAPI::getCurrentServers();
    $output = '';
    $servers = [];
    if (isset($_POST["query"])) {
        if (isset($onlineServers[$_POST["query"]])) {
            $servers[] = $_POST["query"];
        } else {
            foreach ($onlineServers as $name) {
                if (str_starts_with($name, $_POST["query"])) {
                    if (count($servers) < 10) $servers[] = $name;
                }
            }
        }
    } else {
        $servers = array_slice($onlineServers, 0, 10);
    }

    if (count($servers) > 0) {
        $output .= '<table class="highlight">
				<tr>
					<th>Name</th>
					<th>Port</th>
					<th>Players</th>
	        		<th>Template</th>
					<th>Status</th>
				</tr>';
        foreach ($servers as $server) {
            $data = RestAPI::getServerData($server);
            $output .= '<tr>
	 				<td><a href="server.php?server=' . $data["name"] . '">' . $data["name"] . '</a></td>';
            $output .= "<td>" . $data["port"] . "</td>";
            $output .= "<td>" . $data["playerCount"] . "/" . $data["maxPlayers"] . "</td>";
            $output .= "<td><a href='template.php?template=" . $data["template"] . "'>" . $data["template"] . "</a></td>";
            $output .= "<td><strong><p style='color: " . match ($data["serverStatus"]) {
                    "STARTING" => "lime",
                    "FULL" => "orangered",
                    "IN_GAME" => "orange",
                    "STOPPING" => "darkred",
                    "OFFLINE" => "red",
                    default => "green"
            } . ";'>" . $data["serverStatus"] . "</p></strong></td>";
            $output .= "</tr>";
        }
        echo $output;
    } else {
        if (isset($_POST["query"])) {
            echo '<h3 style="color: red;">No results.</h3>';
        } else {
            echo '<h3 style="color: red;">There are no servers!</h3>';
        }
    }
} else if ($_GET["type"] == "SEARCH_TEMPLATES") {
    $templates = RestAPI::getCurrentTemplates();
    $output = '';
    $finalTemplates = [];
    if (isset($_POST["query"])) {
        if (isset($onlineServers[$_POST["query"]])) {
            $finalTemplates[] = $_POST["query"];
        } else {
            foreach ($templates as $name) {
                if (str_starts_with($name, $_POST["query"])) {
                    if (count($finalTemplates) < 10) $finalTemplates[] = $name;
                }
            }
        }
    } else {
        $finalTemplates = array_slice($templates, 0, 10);
    }

    if (count($finalTemplates) > 0) {
        $output .= '<table class="highlight">
				<tr>
					<th>Name</th>
					<th>Maintenance</th>
	        		<th>Players</th>
					<th>Online Servers</th>
				</tr>';
        foreach ($finalTemplates as $template) {
            $data = RestAPI::getTemplateData($template);
            $output .= '<tr><td><a href="template.php?template=' . $data["name"] . '">' . $data["name"] . '</a></td>';
            $output .= "<td>" . ($data["maintenance"] ? "<p style='color: red;'>Yes</p>" : "<p style='color: limegreen;'>No</p>") . "</td>";
            $output .= "<td>" . $data["playerCount"] . ($data["playerCount"] == 1 ? " Player" : " Players") . "</td>";
            $output .= "<td>" . $data["serverCount"] . ($data["serverCount"] == 1 ? " Server" : " Servers") . "</td></tr>";
        }

        echo $output;
    } else {
        if (isset($_POST["query"])) {
            echo '<h3 style="color: red;">No results.</h3>';
        } else {
            echo '<h3 style="color: red;">There are no templates!</h3>';
        }
    }
} else if ($_GET["type"] == "SERVER_CONSOLE") {
    if (!empty($_GET["server"]) && !empty($_GET["templateType"])) {
        $output = "";
        foreach (Utils::getServerLogs($_GET["server"], $_GET["templateType"] == "PROXY" ? 1 : 0) as $log) {
            $output .= "<p style='padding: 2px; margin: 2px;'>" . clean(str_replace(["<", ">"], ["&lt;", "&gt;"], $log)) . "</p>";
        }

        echo $output;
    }
} else if ($_GET["type"] == "SERVER_DATA") {
    if (!empty($_GET["server"])) {
        $data = RestAPI::getServerData($_GET["server"]);
        $output = '<table class="highlight">
				<tr>
					<th>Port</th>
					<th>Players</th>
	        		<th>Template</th>
					<th>Status</th>
				</tr>';
        $output .= "<td>" . $data["port"] . "</td>";
        $output .= "<td>" . $data["playerCount"] . "/" . $data["maxPlayers"] . "</td>";
        $output .= "<td><a href='template.php?template=" . $data["template"] . "'>" . $data["template"] . "</a></td>";
        $output .= "<td><strong><p style='color: " . match ($data["serverStatus"]) {
                "STARTING" => "lime",
                "FULL" => "orangered",
                "IN_GAME" => "orange",
                "STOPPING" => "darkred",
                "OFFLINE" => "red",
                default => "green"
            } . ";'>" . $data["serverStatus"] . "</p></strong></td>";

        echo $output;
    }
} else if ($_GET["type"] == "TEMPLATE_DATA") {
    if (!empty($_GET["template"])) {
        $data = RestAPI::getTemplateData($_GET["template"]);
        $output = "<p>Currently, the template is <strong>" . ($data["maintenance"] ? " in maintenance" : " not in maintenance") . "</strong>, <strong>" . ($data["static"] ? " static" : " not static") . "</strong> and <strong>" . ($data["lobby"] ? " a lobby" : " not a lobby") . "</strong> template with the template type <strong>" . $data["templateType"] . "</strong>.</p>";
        $output .= "<table class='highlight'>
				<tr>
	        		<th>Players</th>
					<th>Servers</th>
					<th>MaxPlayerCount</th>
					<th>MinServerCount</th>
					<th>MaxServerCount</th>
					<th>StartNewWhenFull</th>
					<th>AutoStart</th>
				</tr>";
        $output .= "<td>" . $data["playerCount"] . ($data["playerCount"] == 1 ? " Player" : " Players") . "</td>";
        $output .= "<td>" . $data["serverCount"] . ($data["serverCount"] == 1 ? " Server" : " Servers") . "</td>";
        $output .= "<td>" . $data["maxPlayerCount"] . ($data["maxPlayerCount"] == 1 ? " Player" : " Players") . "</td>";
        $output .= "<td>" . $data["minServerCount"] . ($data["minServerCount"] == 1 ? " Server" : " Servers") . "</td>";
        $output .= "<td>" . $data["maxServerCount"] . ($data["maxServerCount"] == 1 ? " Server" : " Servers") . "</td>";
        $output .= "<td>" . ($data["startNewWhenFull"] ? "<p style='color: limegreen;'>Yes</p>" : "<p style='color: red;'>No</p>") . "</td>";
        $output .= "<td>" . ($data["autoStart"] ? "<p style='color: limegreen;'>Yes</p>" : "<p style='color: red;'>No</p>") . "</td>";
        echo $output;
    }
} else if ($_GET["type"] == "PLAYER_COUNT") {
    $output = "<h1>Online Players<div class='flex-icon'><i class='fas fa-users fa-2x'></i></div></h1>";
    $output .= "<h1 class='count'>" . count(RestAPI::getCurrentPlayers()) . "</h1>";
    echo $output;
} else if ($_GET["type"] == "SERVER_COUNT") {
    $output = "<h1>Online Servers<div class='flex-icon'><i class='fas fa-server fa-2x'></i></div></h1>";
    $output .= "<h1 class='count'>" . count(RestAPI::getCurrentServers()) . "</h1>";
    echo $output;
} else if ($_GET["type"] == "TEMPLATE_COUNT") {
    $output = "<h1>Templates<div class='flex-icon'><i class='fas fa-folder fa-2x'></i></div></h1>";
    $output .= "<h1 class='count'>" . count(RestAPI::getCurrentTemplates()) . "</h1>";
    echo $output;
} else if ($_GET["type"] == "PLUGIN_OVERVIEW") {
    $output = "<h1>Plugins<div class='flex-icon'><i class='fas fa-code fa-2x'></i></div></h1>";
    $output .= "<table class='highlight'>
                <tr>
					<th>Plugin</th>
					<th>Author</th>
					<th>Version</th>
					<th>Status</th>
					<th>FullName</th>
				</tr>";
    foreach (RestAPI::getLoadedPlugins() as $plugin) {
        $data = RestAPI::getPluginData($plugin);
        $output .= "<tr><td>" . $data["name"] . "</td>";
        $output .= "<td>" . implode(", ", $data["authors"]) . "</td>";
        $output .= "<td>v" . $data["version"] . "</td>";
        $output .= "<td><strong><p style='color: " . match ($data["enabled"]) {
                 false => "red",
                default => "lime"
            } . ";'>" . ($data["enabled"] ? "ENABLED" : "DISABLED") . "</p></strong></td>";
        $output .= "<td><strong>" . $data["name"] . "@v" . $data["version"] . "</strong></td>";
    }
    echo $output;
} else if ($_GET["type"] == "SERVER_LIST") {
    if (!empty($_GET["template"])) {
        $output = '<table class="highlight">
				<tr>
				    <th>Server</th>
					<th>Port</th>
					<th>Players</th>
					<th>Status</th>
				</tr>';
        foreach (RestAPI::getCurrentServers($_GET["template"]) as $server) {
            $data = RestAPI::getServerData($server);
            $output .= '<tr>
	 				<td><a href="server.php?server=' . $data["name"] . '">' . $data["name"] . '</a></td>';
            $output .= "<td>" . $data["port"] . "</td>";
            $output .= "<td>" . $data["playerCount"] . "/" . $data["maxPlayers"] . "</td>";
            $output .= "<td><strong><p style='color: " . match ($data["serverStatus"]) {
                    "STARTING" => "lime",
                    "FULL" => "orangered",
                    "IN_GAME" => "orange",
                    "STOPPING" => "darkred",
                    "OFFLINE" => "red",
                    default => "green"
                } . ";'>" . $data["serverStatus"] . "</p></strong></td>";
            $output .= "</tr>";
        }

        echo $output;
    }
}

function clean(string $string): string {
    return str_replace("§", "", preg_replace("/§[0-9a-gk-or]/u", "", $string));
}