<?php
    include_once('userAuthMethods.php');
    userAuthMethods::checkIfAvailable('email_addr', "user_info", "user_email");