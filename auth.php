<head><body>
<?php
    $user_email = "";
    $user_password = "";

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $user_email = $_POST["email_addr"];
        $user_password = $_POST["pass"];
    }

    echo $user_email;

?>

<?php echo $user_email?>
</body></head>
