<?php

use system\Database;

header('Content-Type: application/json');

include("../system/Database.php");

function getPlayers(Database $database): array
{
    $sql = <<<SQL
        SELECT id, x, y, z, r_x, r_y, r_z
        FROM players
    SQL;

    $players = $database->query($sql);

    if (count($players) > 0) {
        return array(
            "status" => "Success",
            "players" => $players
        );
    } else {
        return array(
            "status" => "No players found",
            "players" => array()
        );
    }
}

function sendOwnPosition(Array $players, Database $database): void {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $id = $data["id"];
    $x = $data["x"];
    $y = $data["y"];
    $z = $data["z"];
    $r_x = $data["r_x"];
    $r_y = $data["r_y"];
    $r_z = $data["r_z"];

    $insertNew = true;
    foreach ($players as $player) {
        if ($player['id'] == $id) {
            $insertNew = false;
            break;
        }
    }

    $sql = $insertNew ? <<<SQL
        INSERT INTO players (id, x, y, z, r_x, r_y, r_z, timestamp)
        VALUES( :id, :x, :y, :z, :r_x, :r_y, :r_z, NOW() )
    SQL : <<<SQL
        UPDATE players
        SET x = :x, y = :y, z = :z, r_x = :r_x, r_y = :r_y, r_z = :r_z, timestamp = NOW()
        WHERE id = :id;
    SQL;

    $replacements = array(
        'id' => ['value' => $id, 'type' => PDO::PARAM_INT],
        'x' => ['value' => $x, 'type' => PDO::PARAM_INT],
        'y' => ['value' => $y, 'type' => PDO::PARAM_INT],
        'z' => ['value' => $z, 'type' => PDO::PARAM_INT],
        'r_x' => ['value' => $r_x, 'type' => PDO::PARAM_INT],
        'r_y' => ['value' => $r_y, 'type' => PDO::PARAM_INT],
        'r_z' => ['value' => $r_z, 'type' => PDO::PARAM_INT]
    );

    $database->query($sql, $replacements);
}

function main() {
    $database = new Database();
    $players =  getPlayers($database);
    sendOwnPosition($players["players"], $database);
    echo json_encode($players);
}

main();