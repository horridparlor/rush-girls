<?php
header('Content-Type: application/json');

include("../system/Database.php");

function getImage()
{
    $cardId = $_GET["cardId"];
    
    $database = new Database();
    $sql = <<<SQL
        SELECT file.data
        FROM card
        JOIN file
            ON card.image_id = file.id
        WHERE card.id = :cardId
    SQL;
    $replacements = array(
      'cardId' => ['value' => $cardId, 'type' => PDO::PARAM_INT]
    );
    $result = $database->query($sql, $replacements);
    if (count($result) > 0) {
        return json_encode(array(
            "status" => "Success",
            "imageData" => base64_encode($result[0]['data'])
        ));
    } else {
        return json_encode(array("status" => "No image"));
    }
}

echo getImage();

?>
