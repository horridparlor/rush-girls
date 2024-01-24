<?php

class  Database
{
    function connect()
    {
        $servername = "ip_address_here";
        $username = "table_name_same_as_username_here";
        $password = "user_password_here";
        $connection = new mysqli($servername, $username, $password, $username);
        if ($connection->connect_error) {
            die("Failed to connect DATABASE: " . $connection->connect_error);
        }
        $connection->set_charset("utf8mb4");
        return $connection;
    }
}
