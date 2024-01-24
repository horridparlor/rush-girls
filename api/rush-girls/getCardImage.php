<?php
header('Content-Type: application/json');

include("../system/Database.php");

function getImage($connection)
{
    $cardId = $_GET["cardId"];
    
    $database = new Database();
    $sql = "
        SELECT file.data
        FROM card
        JOIN file
            ON card.image_id = file.id
        WHERE card.id = " . $cardId
    ;
    $connection = $database->connect();
    $result = $connection->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return json_encode(array(
            "status" => "Success",
            "imageData" => base64_encode($row['data'])
        ));
    } else {
        return json_encode(array("status" => "No image"));
    }
}

echo getImage($connection);

?>
