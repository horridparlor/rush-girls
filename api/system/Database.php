<?php

class  Database
{
    function connect()
    {
        $servername = "id_address_of_the_server_here";
        $username = "username_that_i_have_as_same_as_the_database_name_here";
        $password = "user_password_here";
        $connection = new mysqli($servername, $username, $password, $username);
        if ($connection->connect_error) {
            die("Failed to connect DATABASE: " . $connection->connect_error);
        }
        return $connection;
    }
}
