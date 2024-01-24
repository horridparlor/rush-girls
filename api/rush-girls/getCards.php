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
    $sql = "
        SELECT card.id, card.name
        FROM card
        JOIN expansion
            ON card.expansion_id = expansion.id
        LEFT JOIN card material1
            ON card.primaryMaterial_id = material1.id
        LEFT JOIN card material2
            ON card.secondaryMaterial_id = material2.id
        WHERE expansion.isReleased = 1
    ";
    if ($expansionId !== null) {
        $sql .= " AND card.expansion_id = " . $expansionId;
    }
    if ($cardTypeId !== null) {
        $sql .= " AND card.type_id = " . $cardTypeId;
    }
    if ($effectTypeId !== null) {
        $sql .= " AND (card.effectType_id = " . $effectTypeId . " OR card.effectType2_id = " . $effectTypeId . ")";
    }
    if ($costTypeId !== null) {
        $sql .= " AND (card.costType_id = " . $costTypeId . " OR card.costType2_id = " . $costTypeId . ")";
    }
    if ($classId !== null) {
        $sql .= " AND card.class_id = " . $classId;
    }
    if ($minLevel !== null) {
        $sql .= " AND (card.level >= " . $minLevel
        . " OR card.triggerMinLevel <= " . $minLevel . ")";
    }
    if ($maxLevel !== null) {
        $sql .= " AND (card.level <= " . $maxLevel
        . " OR card.triggerMaxLevel >= " . $maxLevel . ")";
    }
    if ($minAtk !== null) {
        $sql .= " AND card.atk >= " . $minAtk;
    }
    if ($maxAtk !== null) {
        $sql .= " AND card.atk <= " . $maxAtk;
    }
    if ($minDef !== null) {
        $sql .= " AND card.def >= " . $minDef;
    }
    if ($maxDef !== null) {
        $sql .= " AND card.def <= " . $maxDef;
    }
    if ($isAce !== null) {
        $sql .= " AND card.isAce = " . $isAce;
    }
    if ($legalityId !== null) {
        if ($legalityId == 0) {
            $sql .= " AND card.legality_id IS NULL";
        } else {
            $sql .= " AND card.legality_id = " . $legalityId;
        }
    }
    if ($searchString !== null) {
        $searchString = urldecode($searchString);
        $sql .= " AND (LOWER(card.name) LIKE '%" . $searchString
        . "%' OR LOWER(card.cost) LIKE '%" . $searchString
        . "%' OR LOWER(card.effect) LIKE '%" . $searchString
        . "%' OR LOWER(card.flavourText) LIKE '%" . $searchString
        . "%' OR LOWER(material1.name) LIKE '%" . $searchString
        . "%' OR LOWER(material2.name) LIKE '%" . $searchString
        . "%')";
    }
    $sql .= "
    ORDER BY card.name
    LIMIT 84
    ";
    $connection = $database->connect();
    $result = $connection->query($sql);
    if ($result->num_rows > 0) {
        $cards = array();
        while ($row = $result->fetch_assoc()) {
            $cards[] = $row;
        }
        return json_encode(array(
            "status" => "Success",
            "countOfCards" => count($cards),
            "cards" => $cards
        ));
    } else {
        return json_encode(array("status" => "No cards"));
    }
}

echo getCards();

?>


