<?php
    $pass1 = $pass2 = "";
    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $pass1 = $_POST['pass'];
        $pass2 = $_POST['pass2'];
    }
    if($pass1 != $pass2) {
        echo true;
    } else {
        echo false;
    }