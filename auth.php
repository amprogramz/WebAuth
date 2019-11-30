<?php
    $user_email = "";
    $user_password = "";

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $user_email = $_POST('email');
        $user_password = $_POST('password');
    }

    echo $user_email;

?>