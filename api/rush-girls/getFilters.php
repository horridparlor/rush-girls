<?php
header('Content-Type: application/json');

include("../system/Database.php");

function getFilters()
{
    $database = new Database();
    $sql = <<<SQL
        SELECT id,
            CONCAT(
                UPPER(SUBSTRING(REPLACE(name, '-', ' '), 1, 1)), 
                LOWER(SUBSTRING(REPLACE(name, '-', ' '), 2))
            ) AS name
        FROM :tableName
        ORDER BY name
    SQL;
    $costTypeQuery = str_replace(':tableName', 'costType', $sql);
    $effectTypeQuery = str_replace(':tableName', 'effectType', $sql);

    $costTypes = $database->query($costTypeQuery);
    $effectTypes = $database->query($effectTypeQuery);
    if (count($costTypes) > 0 && count($effectTypes) > 0) {
        return json_encode(array(
            "status" => "Success",
            "costTypes" => $costTypes,
            "effectTypes" => $effectTypes
        ));
    } else {
        return json_encode(array("status" => "Did not find all filters"));
    }
}

echo getFilters();
