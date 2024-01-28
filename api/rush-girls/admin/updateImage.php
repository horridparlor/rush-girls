<?php
header('Content-Type: application/json');

include("../../system/Database.php");

function updateImage()
{
    $cardId = $_POST['cardId'];
    $imageFile = $_FILES['imageData']['tmp_name'];
    $imageData = file_get_contents($imageFile);

    $database = new Database();
    $sql = <<<SQL
        UPDATE file
        SET data = :imageData
        WHERE id IN (
            SELECT image_id
            FROM card
            WHERE id = :cardId
        )
    SQL;
    $replacements = array(
        'cardId' => ['value' => $cardId, 'type' => PDO::PARAM_INT],
        'imageData' => ['value' => $imageData, 'type' => PDO::PARAM_LOB]
    );
    $database->query($sql, $replacements);
    return json_encode(array(
        "status" => "Success"
    ));
}

echo updateImage();

?>
