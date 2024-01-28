<?php
header('Content-Type: application/json');

include("../../system/Database.php");

function uploadImages() {
    $database = new Database();
    $sql = "INSERT INTO file (name, data) VALUES ";

    $values = [];
    $replacements = [];
    foreach ($_FILES['images']['name'] as $index => $name) {
        $tmpPath = $_FILES['images']['tmp_name'][$index];
        $fileContent = file_get_contents($tmpPath);

        $values[] = "(:name{$index}, :data{$index})";
        $replacements["name{$index}"] = ['value' => $name, 'type' => PDO::PARAM_STR];
        $replacements["data{$index}"] = ['value' => $fileContent, 'type' => PDO::PARAM_LOB];
    }

    $sql .= implode(", ", $values);
    $database->query($sql, $replacements);
    return json_encode(["status" => "Success"]);
}

echo uploadImages();

?>
