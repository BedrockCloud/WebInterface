<?php

namespace rest;

use util\Utils;

class RestAPI {

    /**
     * @throws \Exception
     */
    public static function createRequest(string $route, string $requestMethod, array $queries = []): ?array {
        if (!Utils::checkCloudStatus()) return null;
        $url = "http://" . Utils::cloudHttpHost() . ":" . Utils::cloudHttpPort() . "/" . $route . (count($queries) > 0 ? "?" . http_build_query($queries) : "");
        $authKey = Utils::cloudHttpAuthKey();

        $extraOptions = [];
        if ($requestMethod == "POST") $extraOptions = [CURLOPT_POSTFIELDS => http_build_query($queries), CURLOPT_POST => true];
        else if ($requestMethod == "PATCH") $extraOptions = [CURLOPT_POSTFIELDS => http_build_query($queries), CURLOPT_CUSTOMREQUEST => "PATCH"];
        else if ($requestMethod == "DELETE") $extraOptions = [CURLOPT_CUSTOMREQUEST => "DELETE"];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_HTTPHEADER => ["auth-key: " . $authKey],
                CURLOPT_TIMEOUT => 3
            ] + $extraOptions
        );

        $result = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if (!$result || str_starts_with($code, "40")) throw new \Exception("HTTP Request to " . $requestMethod . " " . $url . " failed: " . curl_error($ch), $code ?? 0);

        $header = explode("\r\n", substr($result, 0, ($len = curl_getinfo($ch, CURLINFO_HEADER_SIZE))));
        $body = substr($result, $len);

        if (($decoded = json_decode($body, true)) === null && $decoded === false) return [$code, $requestMethod, $header, $body];
        return [$code, $requestMethod, $decoded];
    }

    public static function createAccount(string $username, string $role): ?string {
        try {
            $response = self::createRequest("webaccount/create/", "POST", ["name" => $username, "role" => $role])[2] ?? [];
            return is_array($response) && isset($response["initial_password"]) ? $response["initial_password"] : null;
        } catch (\Exception) {}
        return null;
    }

    public static function updatePassword(string $username, string $newPassword): void {
        try {
            self::createRequest("webaccount/update/", "PATCH", ["name" => $username, "action" => "password", "value" => password_hash($newPassword, PASSWORD_BCRYPT)]);
        } catch (\Exception) {}
    }

    public static function updateRole(string $username, string $newRole): void {
        try {
            self::createRequest("webaccount/update/", "PATCH", ["name" => $username, "action" => "role", "value" => $newRole]);
        } catch (\Exception) {}
    }

    public static function deleteAccount(string $username): bool {
        try {
            $response = self::createRequest("webaccount/delete/", "DELETE", ["name" => $username])[2] ?? null;
            return is_array($response) && isset($response["success"]);
        } catch (\Exception) {}
        return false;
    }

    public static function startServer(string $template, int $count = 1): ?string {
        try {
            $response = self::createRequest("server/start/", "POST", ["template" => $template, "count" => $count])[2] ?? [];
            return is_array($response) && isset($response["success"]) ? $response["success"] : null;
        } catch (\Exception) {}
        return null;
    }

    public static function saveServer(string $server): bool {
        try {
            $response = self::createRequest("server/save/", "POST", ["server" => $server])[2] ?? [];
            return is_array($response) && isset($response["success"]);
        } catch (\Exception) {}
        return false;
    }

    public static function stopServer(string $id): bool {
        try {
            $response = self::createRequest("server/stop/", "POST", ["identifier" => $id])[2] ?? [];
            return is_array($response) && isset($response["success"]);
        } catch (\Exception) {}
        return false;
    }

    public static function createTemplate(string $name, string $type, bool $lobby, bool $maintenance, bool $static, int $maxPlayerCount, int $minServerCount, int $maxServerCount, bool $startNewWhenFull, bool $autoStart): bool {
        try {
            $response = self::createRequest("template/create/", "POST", [
                "name" => $name,
                "type" => $type,
                "lobby" => ($lobby ? "true" : "false"),
                "maintenance" => ($maintenance ? "true" : "false"),
                "static" => ($static ? "true" : "false"),
                "maxPlayerCount" => $maxPlayerCount,
                "minServerCount" => $minServerCount,
                "maxServerCount" => $maxServerCount,
                "startNewWhenFull" => ($startNewWhenFull ? "true" : "false"),
                "autoStart" => ($autoStart ? "true" : "false")
            ])[2] ?? [];
            return is_array($response) && isset($response["success"]);
        } catch (\Exception) {}
        return false;
    }

    public static function editTemplate(string $template, ?bool $lobby, ?bool $maintenance, ?bool $static, ?int $maxPlayerCount, ?int $minServerCount, ?int $maxServerCount, ?bool $startNewWhenFull, ?bool $autoStart): bool {
        $queries = ["name" => $template];
        if ($lobby !== null) $queries["lobby"] = ($lobby ? "true" : "false");
        if ($maintenance !== null) $queries["maintenance"] = ($maintenance ? "true" : "false");
        if ($static !== null) $queries["static"] = ($static ? "true" : "false");
        if ($maxPlayerCount !== null) $queries["maxPlayerCount"] = $maxPlayerCount;
        if ($minServerCount !== null) $queries["minServerCount"] = $minServerCount;
        if ($maxServerCount !== null) $queries["maxServerCount"] = $maxServerCount;
        if ($startNewWhenFull !== null) $queries["startNewWhenFull"] = ($startNewWhenFull ? "true" : "false");
        if ($autoStart !== null) $queries["autoStart"] = ($autoStart ? "true" : "false");
        try {
            $response = self::createRequest("template/edit/", "PATCH", $queries)[2] ?? [];
            return is_array($response) && isset($response["success"]);
        } catch (\Exception) {}
        return false;
    }

    public static function removeTemplate(string $template): bool {
        try {
            $response = self::createRequest("template/delete/", "DELETE", ["name" => $template])[2] ?? [];
            return is_array($response) && isset($response["success"]);
        } catch (\Exception) {}
        return false;
    }

    public static function sendCommandTo(string $server, string $command): bool {
        try {
            $response = self::createRequest("server/execute/", "POST", ["server" => $server, "command" => $command])[2] ?? [];
            return is_array($response) && isset($response["success"]);
        } catch (\Exception) {}
        return false;
    }

    public static function textPlayer(string $player, string $type, string $text): bool {
        try {
            $response = self::createRequest("player/text/", "POST", ["identifier" => $player, "text_type" => strtolower($type), "text" => $text])[2] ?? [];
            return is_array($response) && isset($response["success"]);
        } catch (\Exception) {}
        return false;
    }

    public static function kickPlayer(string $player, string $reason = ""): bool {
        try {
            $response = self::createRequest("player/kick/", "POST", ["identifier" => $player, "reason" => $reason])[2] ?? [];
            return is_array($response) && isset($response["success"]);
        } catch (\Exception) {}
        return false;
    }

    public static function addToMaintenanceList(string $player): bool {
        try {
            $response = self::createRequest("maintenance/add/", "POST", ["player" => $player])[2] ?? [];
            return is_array($response) && isset($response["success"]);
        } catch (\Exception) {}
        return false;
    }

    public static function removeFromMaintenanceList(string $player): ?string {
        try {
            $response = self::createRequest("maintenance/remove/", "DELETE", ["player" => $player])[2] ?? [];
            return is_array($response) && isset($response["success"]);
        } catch (\Exception) {}
        return false;
    }

    public static function editModule(string $module, bool $value): bool {
        try {
            $response = self::createRequest("module/edit/", "PATCH", ["module" => $module, "value" => ($value ? "true" : "false")])[2] ?? [];
            return is_array($response) && isset($response["success"]);
        } catch (\Exception) {}
        return false;
    }

    public static function getAccountData(string $username): ?array {
        try {
            $response = self::createRequest("webaccount/get/", "GET", ["name" => $username])[2] ?? null;
            return is_array($response) && count($response) > 0 && !isset($response["error"]) ? $response : null;
        } catch (\Exception) {}
        return null;
    }

    public static function getServerData(string $server): ?array {
        try {
            $response = self::createRequest("server/get/", "GET", ["identifier" => $server])[2] ?? [];
            return is_array($response) && count($response) > 0 && !isset($response["error"]) ? $response : null;
        } catch (\Exception) {}
        return null;
    }

    public static function getPlayerData(string $player): ?array {
        try {
            $response = self::createRequest("player/get/", "GET", ["identifier" => $player])[2] ?? [];
            return is_array($response) && count($response) > 0 && !isset($response["error"]) ? $response : null;
        } catch (\Exception) {}
        return null;
    }

    public static function getTemplateData(string $template): ?array {
        try {
            $response = self::createRequest("template/get/", "GET", ["name" => $template])[2] ?? [];
            return is_array($response) && count($response) > 0 && !isset($response["error"]) ? $response : null;
        } catch (\Exception) {}
        return null;
    }

    public static function getPluginData(string $name): ?array {
        try {
            $response = self::createRequest("plugin/get/", "GET", ["plugin" => $name])[2] ?? [];
            return is_array($response) && count($response) > 0 && !isset($response["error"]) ? $response : null;
        } catch (\Exception) {}
        return null;
    }

    public static function getModuleData(string $name): ?array {
        try {
            $response = self::createRequest("module/get/", "GET", ["module" => $name])[2] ?? [];
            return is_array($response) && count($response) > 0 && !isset($response["error"]) ? $response : null;
        } catch (\Exception) {}
        return null;
    }

    public static function getAccounts(): array {
        try {
            return self::createRequest("webaccount/list/", "GET")[2] ?? [];
        } catch (\Exception) {}
        return [];
    }

    public static function getModules(): array {
        try {
            return self::createRequest("module/list/", "GET")[2] ?? [];
        } catch (\Exception) {}
        return [];
    }
    public static function isAdmin(string $username): bool {
        $data = self::getAccountData($username);
        if ($data === null) return false;
        return strtolower($data["role"]) == "admin";
    }

    public static function isOnline(string $player): bool {
        try {
            $response = self::createRequest("player/get/", "GET", ["identifier" => $player])[2] ?? [];
            return is_array($response) && !isset($response["error"]);
        } catch (\Exception) {}
        return false;
    }

    public static function getCurrentPlayers(): array {
        try {
            return self::createRequest("player/list/", "GET")[2] ?? [];
        } catch (\Exception) {}
        return [];
    }

    public static function getCurrentServers(?string $template = null): array {
        try {
            $response = self::createRequest("server/list/", "GET", ($template === null ? [] : ["template" => $template]))[2] ?? [];
            return is_array($response) && !isset($response["error"]) ? $response : [];
        } catch (\Exception) {}
        return [];
    }

    public static function getCurrentTemplates(): array {
        try {
            return self::createRequest("template/list/", "GET")[2] ?? [];
        } catch (\Exception) {}
        return [];
    }

    private static function getPlugins(): array {
        try {
            return self::createRequest("plugin/list/", "GET")[2] ?? [];
        } catch (\Exception) {}
        return [];
    }

    public static function getLoadedPlugins(): array {
        return self::getPlugins()["loadedPlugins"] ?? [];
    }

    public static function getEnabledPlugins(): array {
        return self::getPlugins()["enabledPlugins"] ?? [];
    }

    public static function getDisabledPlugins(): array {
        return self::getPlugins()["disabledPlugins"] ?? [];
    }

    public static function getServerLogs(string $server, int $type = 0): array {
        try {
            $response = self::createRequest(
                "server/logs/",
                "GET",
                ["server" => $server, "type" => $type]
            );

            return $response[2]["logs"] ?? [];
        } catch (\Exception $e) {
            error_log("Error fetching server logs: " . $e->getMessage());
            return [];
        }
    }
}