<?php

use system\Database;

header('Content-Type: application/json');

include("system/Database.php");
include("system/cardTypes.php");

function getCards()
{
    $expansionId = Database::getIntParam("expansionId");
    $cardTypeId = Database::getIntParam("cardTypeId");
    $deckId = Database::getIntParam("deckId");
    $effectTypeId = Database::getIntParam("effectTypeId");
    $costTypeId = Database::getIntParam("costTypeId");
    $classId = Database::getIntParam("classId");
    $minLevel = Database::getIntParam("minLevel");
    $maxLevel = Database::getIntParam("maxLevel");
    $minAtk = Database::getIntParam("minAtk");
    $maxAtk = Database::getIntParam("maxAtk");
    $minDef = Database::getIntParam("minDef");
    $maxDef = Database::getIntParam("maxDef");
    $specialId = Database::getIntParam("specialId");
    $isErrata = Database::getIntParam("legalityId");
    $searchString = Database::getStringParam("searchString");
    $sortId = Database::getIntParam("sortId");
    $orderId = Database::getIntParam("orderId");
    
    
    $database = new Database();
    $sql = <<<SQL
        SELECT card.id, card.name, type.name as type,
               class.name as class, card.level, card.atk,
               card.def, material1.id as material1_id, material1.name as material1_name,
               material2.id as material2_id, material2.name as material2_name,
               material3.id as material3_id, material3.name as material3_name,
               card.cost, card.effect, card.flavourText, expansion.name AS expansion
        FROM card
        JOIN expansion
            ON card.expansion_id = expansion.id
        LEFT JOIN card material1
            ON card.primaryMaterial_id = material1.id
        LEFT JOIN card material2
            ON card.secondaryMaterial_id = material2.id
        LEFT JOIN card material3
            ON card.tertiaryMaterial_id = material3.id
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
        switch ($cardTypeId) {
            case CARD_TYPE_MONSTER:
                $sql .= CARD_TYPE_IN . Database::arrify(array(
                        CARD_TYPE_NORMAL,
                        CARD_TYPE_EFFECT,
                        CARD_TYPE_FUSION,
                        CARD_TYPE_REVENGE,
                        CARD_TYPE_ROYAL
                    ));
                break;
            case CARD_TYPE_BACKROW:
                $sql .= CARD_TYPE_IN . Database::arrify(array(
                        CARD_TYPE_SPELL,
                        CARD_TYPE_TRAP,
                        CARD_TYPE_REVENGE
                    ));
                break;
            case CARD_TYPE_TRAP:
                $sql .= CARD_TYPE_IN . Database::arrify(array(
                        CARD_TYPE_TRAP,
                        CARD_TYPE_RITUAL
                    ));
                break;
            default:
                $sql .= " AND card.type_id = :typeId";
                $replacements['typeId'] = ['value' => $cardTypeId, 'type' => PDO::PARAM_INT];
        }
    }
    if ($deckId !== null) {
        switch ($deckId) {
            case DECK_MAIN:
                $sql .= CARD_TYPE_IN . Database::arrify(array(
                        CARD_TYPE_NORMAL,
                        CARD_TYPE_EFFECT,
                        CARD_TYPE_SPELL,
                        CARD_TYPE_TRAP
                    ));
                break;
            case DECK_EXTRA:
                $sql .= CARD_TYPE_IN . Database::arrify(array(
                        CARD_TYPE_FUSION,
                        CARD_TYPE_REVENGE,
                        CARD_TYPE_ROYAL,
                        CARD_TYPE_RITUAL
                    ));
                break;
        }
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
        $sql .= " AND (
            card.class_id = :classId
            OR card.class2_id = :classId
        )";
        $replacements['classId'] = ['value' => $classId, 'type' => PDO::PARAM_INT];
    }
    if ($minLevel !== null) {
        $sql .= " AND (
            card.level >= :minLevel
            OR (
                card.triggerMinLevel <= :minLevel
                AND card.triggerMaxLevel >= :minLevel
            )
        )";
        $replacements['minLevel'] = ['value' => $minLevel, 'type' => PDO::PARAM_INT];
    }
    if ($maxLevel !== null) {
        $sql .= " AND (
            card.level <= :maxLevel
            OR (
                card.triggerMaxLevel >= :maxLevel
                AND card.triggerMinLevel <= :maxLevel
            )
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
    if ($specialId !== null) {
        if ($specialId == NOT_ACE) {
            $sql .= " AND (
                card.special_id IS NULL
                OR card.special_id != " . IS_ACE .
            ") ";
        }
        else {
            $sql .= $specialId == IS_ACE ?
                " AND (
                    card.special_id = :specialId
                    OR card.isAce = 1
                )"
                : " AND card.special_id = :specialId";
            $replacements['specialId'] = ['value' => $specialId, 'type' => PDO::PARAM_INT];
        }
    }
    if ($isErrata !== null) {
        $sql .= " AND card.isErrata = :isErrata";
        $replacements['isErrata'] = ['value' => $isErrata, 'type' => PDO::PARAM_INT];
    }
    if ($searchString !== null) {
        $sql .= " AND (
            LOWER(card.name) LIKE CONCAT('%', :searchString, '%')
            OR LOWER(card.cost) LIKE CONCAT('%', :searchString, '%')
            OR LOWER(card.effect) LIKE CONCAT('%', :searchString, '%')
            OR LOWER(card.flavourText) LIKE CONCAT('%', :searchString, '%')
            OR LOWER(material1.name) LIKE CONCAT('%', :searchString, '%')
            OR LOWER(material2.name) LIKE CONCAT('%', :searchString, '%')
            OR LOWER(material3.name) LIKE CONCAT('%', :searchString, '%')
        )";
        $replacements['searchString'] = ['value' => $searchString, 'type' => PDO::PARAM_STR];
    }
    $order = $orderId == ORDER_ASC ? 'ASC' : 'DESC';
    $orderExtend = ' ' . $order . ',';
    $sql .= " ORDER BY ";
    switch ($sortId) {
        case SORT_ATK:
            $sql .= "card.atk" . $orderExtend;
            break;
        case SORT_DEF:
            $sql .= "card.def" . $orderExtend;
            break;
        case SORT_LEVEL:
            $sql .= "card.level" . $orderExtend;
            break;
        case SORT_CLASS:
            $sql .= "card.class_id" . $orderExtend;
            break;
        case SORT_TYPE:
            $sql .= "card.type_id" . $orderExtend;
            break;
        case SORT_EXPANSION:
            $sql .= "card.expansion_id" . $orderExtend;
            break;
        case SORT_RANDOM:
            $sql .= "RAND()" . $orderExtend;
            break;
    }
    $sql .= " card.name";
    $sql .= " LIMIT 9999";
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


