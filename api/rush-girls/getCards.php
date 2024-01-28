<?php
header('Content-Type: application/json');

include("../system/Database.php");

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
        SELECT card.id, card.name
        FROM card
        JOIN expansion
            ON card.expansion_id = expansion.id
        LEFT JOIN card material1
            ON card.primaryMaterial_id = material1.id
        LEFT JOIN card material2
            ON card.secondaryMaterial_id = material2.id
        WHERE expansion.isReleased = 1
    SQL;
    $replacements = array();
    if ($expansionId !== null) {
        $sql .= " AND card.expansion_id = :expansionId";
        $replacements['expansionId'] = ['value' => $expansionId, 'type' => PDO::PARAM_INT];
    }
    if ($cardTypeId !== null) {
        $sql .= " AND card.type_id = :cardTypeId";
        $replacements['cardTypeId'] = ['value' => $cardTypeId, 'type' => PDO::PARAM_INT];
    }
    if ($effectTypeId !== null) {
        $sql .= " AND (
            card.effectType_id = :effectTypeId
            OR card.effectType2_id = :effectTypeId
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
            $sql .= " AND card.legality_id IS NULL";
        } else {
            $sql .= " AND card.legality_id = :legalityId";
            $replacements['legalityId'] = ['value' => $legalityId, 'type' => PDO::PARAM_INT];
        }
    }
    if ($searchString !== null) {
        $searchString = urldecode($searchString);
        $sql .= " AND (
            LOWER(card.name) LIKE '%:searchString%'
            OR LOWER(card.cost) LIKE '%:searchString%'
            OR LOWER(card.effect) LIKE '%:searchString%'
            OR LOWER(card.flavourText) LIKE '%:searchString%'
            OR LOWER(material1.name) LIKE '%:searchString%'
            OR LOWER(material2.name) LIKE '%:searchString%'
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


