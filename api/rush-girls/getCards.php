<?php
header('Content-Type: application/json');

include("../system/Database.php");
include("../system/cardTypes.php");

function getCards()
{
    $expansionId = $_GET["expansionId"];
    $cardTypeId = $_GET["cardTypeId"];
    $effectTypeId = $_GET["effectTypeId"];
    $costTypeId = $_GET["costTypeId"];
    $classId = $_GET["classId"];
    $minLevel = $_GET["minLevel"];
    $maxLevel = $_GET["maxLevel"];
    $minAtk = $_GET["minAtk"];
    $maxAtk = $_GET["maxAtk"];
    $minDef = $_GET["minDef"];
    $maxDef = $_GET["maxDef"];
    $isAce = $_GET["isAce"];
    $legalityId = $_GET["legalityId"];
    $searchString = $_GET["searchString"];
    
    
    $database = new Database();
    $sql = <<<SQL
        SELECT card.id, card.name, type.name as type,
               class.name as class, card.level, card.atk, card.isAce,
               card.def, material1.id as material1_id, material1.name as material1_name,
               material2.id as material2_id, material2.name as material2_name,
               card.cost, card.effect, card.flavourText
        FROM card
        JOIN expansion
            ON card.expansion_id = expansion.id
        LEFT JOIN card material1
            ON card.primaryMaterial_id = material1.id
        LEFT JOIN card material2
            ON card.secondaryMaterial_id = material2.id
        JOIN cardType type
            ON card.type_id = type.id
        LEFT JOIN class
            ON card.class_id = class.id
        WHERE (
            expansion.isReleased = 1 
            OR expansion.id = :expansionId
        )
    SQL;
    $replacements = array(
        'expansionId' => ['value' => $expansionId ?? -1, 'type' => PDO::PARAM_INT]
    );
    if ($expansionId !== null) {
        $sql .= " AND card.expansion_id = :expansionId";
    }
    if ($cardTypeId !== null) {
        $sql .= " AND (
            card.type_id = :cardTypeId
            OR card.type_id = :equalTypeId
        )";
        $cardTypeId = intval($cardTypeId);
        $equalTypeId = match ($cardTypeId) {
            CARD_TYPE_NORMAL => CARD_TYPE_PENDULUM,
            CARD_TYPE_TRAP => CARD_TYPE_RITUAL,
            CARD_TYPE_MONSTER => CARD_TYPE_EFFECT,
            default => $cardTypeId,
        };
        $cardTypeId = match ($cardTypeId) {
            CARD_TYPE_MONSTER => CARD_TYPE_NORMAL,
            CARD_TYPE_NORMAL_TRAP => CARD_TYPE_TRAP
        };
        $replacements = array_merge($replacements, array(
            'cardTypeId' => ['value' => $cardTypeId, 'type' => PDO::PARAM_INT],
            'equalTypeId' => ['value' => $equalTypeId, 'type' => PDO::PARAM_INT]
        ));
    }
    if ($effectTypeId !== null) {
        $sql .= " AND (
            card.effectType_id = :effectTypeId
            OR card.effectType2_id = :effectTypeId
            OR card.effectType3_id = :effectTypeId
        )";
        $replacements['effectTypeId'] = ['value' => $effectTypeId, 'type' => PDO::PARAM_INT];
    }
    if ($costTypeId !== null) {
        $sql .= " AND (
            card.costType_id = :costTypeId
            OR card.costType2_id = :costTypeId
        )";
        $replacements['costTypeId'] = ['value' => $costTypeId, 'type' => PDO::PARAM_INT];
    }
    if ($classId !== null) {
        $sql .= " AND card.class_id = :classId";
        $replacements['classId'] = ['value' => $classId, 'type' => PDO::PARAM_INT];
    }
    if ($minLevel !== null) {
        $sql .= " AND (
            card.level >= :minLevel
            OR card.triggerMinLevel <= :minLevel
        )";
        $replacements['minLevel'] = ['value' => $minLevel, 'type' => PDO::PARAM_INT];
    }
    if ($maxLevel !== null) {
        $sql .= " AND (
            card.level <= :maxLevel
            OR card.triggerMaxLevel >= :maxLevel
        )";
        $replacements['maxLevel'] = ['value' => $maxLevel, 'type' => PDO::PARAM_INT];
    }
    if ($minAtk !== null) {
        $sql .= " AND card.atk >= :minAtk";
        $replacements['minAtk'] = ['value' => $minAtk, 'type' => PDO::PARAM_INT];
    }
    if ($maxAtk !== null) {
        $sql .= " AND card.atk <= :maxAtk";
        $replacements['maxAtk'] = ['value' => $maxAtk, 'type' => PDO::PARAM_INT];
    }
    if ($minDef !== null) {
        $sql .= " AND card.def >= :minDef";
        $replacements['minDef'] = ['value' => $minDef, 'type' => PDO::PARAM_INT];
    }
    if ($maxDef !== null) {
        $sql .= " AND card.def <= :maxDef";
        $replacements['maxDef'] = ['value' => $maxDef, 'type' => PDO::PARAM_INT];
    }
    if ($isAce !== null) {
        $sql .= " AND card.isAce = :isAce";
        $replacements['isAce'] = ['value' => $isAce, 'type' => PDO::PARAM_BOOL];
    }
    if ($legalityId !== null) {
        if ($legalityId == 0) {
            $sql .= " AND 1 = 1";
        } else {
            $sql .= " AND 1 = 2";
        }
    }
    if ($searchString !== null) {
        $searchString = urldecode($searchString);
        $sql .= " AND (
            LOWER(card.name) LIKE CONCAT('%', :searchString, '%')
            OR LOWER(card.cost) LIKE CONCAT('%', :searchString, '%')
            OR LOWER(card.effect) LIKE CONCAT('%', :searchString, '%')
            OR LOWER(card.flavourText) LIKE CONCAT('%', :searchString, '%')
            OR LOWER(material1.name) LIKE CONCAT('%', :searchString, '%')
            OR LOWER(material2.name) LIKE CONCAT('%', :searchString, '%')
        )";
        $replacements['searchString'] = ['value' => $searchString, 'type' => PDO::PARAM_STR];
    }
    $sql .= "
    ORDER BY card.name
    LIMIT 9999
    ";
    $result = $database->query($sql, $replacements);
    if (count($result) > 0) {
        return json_encode(array(
            "status" => "Success",
            "countOfCards" => count($result),
            "cards" => $result
        ));
    } else {
        return json_encode(array("status" => "No cards"));
    }
}

echo getCards();

?>


