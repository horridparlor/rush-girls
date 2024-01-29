<?php
header('Content-Type: application/json');

include("../system/Database.php");
include("../system/cardTypes.php");

function getCard()
{
    $cardId = $_GET["cardId"];

    if ($cardId === null) {
        return json_encode(array("status" => "Error", "message" => "No cardId provided"));
    }

    $database = new Database();
    $sql = <<<SQL
        SELECT card.id, card.name, type.name as type,
               class.name as class, card.level, card.atk, card.isAce,
               card.def, material1.id as material1_id, material1.name as material1_name,
               material2.id as material2_id, material2.name as material2_name,
               card.cost, card.effect, card.flavourText
        FROM card
        LEFT JOIN card material1
            ON card.primaryMaterial_id = material1.id
        LEFT JOIN card material2
            ON card.secondaryMaterial_id = material2.id
        JOIN cardType type
            ON card.type_id = type.id
        LEFT JOIN class
            ON card.class_id = class.id
        WHERE card.id = :cardId
    SQL;

    $replacements = array(
        'cardId' => ['value' => $cardId, 'type' => PDO::PARAM_INT]
    );

    $result = $database->query($sql, $replacements);
    if (count($result) > 0) {
        return json_encode(array(
            "status" => "Success",
            "card" => $result[0]
        ));
    } else {
        return json_encode(array("status" => "No card found"));
    }
}

echo getCard();
