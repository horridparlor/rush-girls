<?php

class  Database
{
    function connect()
    {
        $servername = "31.217.196.118";
        $username = "zdccdlji_rushgirls";
        $password = "FformatSsetters5%Laventel";
        $connection = new mysqli($servername, $username, $password, $username);
        if ($connection->connect_error) {
            die("Failed to connect DATABASE: " . $connection->connect_error);
        }
        $connection->set_charset("utf8mb4");
        return $connection;
    }
}
