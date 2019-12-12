<?php
class userAuthMethods
{
    public static function checkIfAvailable($postField, $table, $field){
        $user_entry = "";
        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $user_entry = htmlspecialchars($_POST[$postField]);
        }
        $connection = self::connectToDb();
        $query_result = self::trySelectWhere($connection, $table, $field, $user_entry);
        if($query_result != null) {
            echo true; // record exists.
        } else {
            echo false; // no record.
        }
        $connection = null;
    }

    public static function handleSignUp(){
        $user_email = "";
        $user_name = "";
        $user_first_name = "";
        $user_last_name = "";
        $password = "";
        $password_two = "";
        $date_of_birth = "";


        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $user_email = htmlspecialchars($_POST["email_addr"]);
            $user_name = htmlspecialchars($_POST["user_name"]);
            $user_first_name = htmlspecialchars($_POST["first_name"]);
            $user_last_name = htmlspecialchars($_POST["last_name"]);
            $password = htmlspecialchars($_POST["pass"]);
            $password_two = htmlspecialchars($_POST['pass2']);
            $date_of_birth = htmlspecialchars($_POST["date_of_birth"]);
        }

        $connection = self::connectToDb();

        /* Validate form data.*/
        $continue = true;
        $query_result = self::trySelectWhere($connection, "user_info", "user_email", $user_email);
        if($query_result != null || $user_email == null) {
            $continue = false;
        }
        $query_result_two = self::trySelectWhere($connection, "user_info", "user_name", $user_name);
        if($query_result_two != null || $user_name == null) {
            $continue = false;
        }
        if ($password != $password_two || $password == null) {
            $continue = false;
        }
        if($date_of_birth == null) {
            $continue = false;
        }

        if($continue == false) {
            echo true;
            $connection = null;
            die();
        }

        try {
            $sql_query = $connection->prepare(
                "INSERT INTO user_info (user_email, user_name, user_first_name, user_last_name, date_of_birth)" .
                "VALUES ('$user_email', '$user_name', '$user_first_name', '$user_last_name', '$date_of_birth');");
            $sql_query->execute();
            $query_result = self::trySelectWhere($connection, "user_info", "user_email", $user_email);
            $user_id = $query_result[0]['user_id'];
            $sql_query2 = $connection->prepare(
                "INSERT INTO user_auth (user_id, user_password, salt, pass_key, hash_key) values" .
                "('$user_id', 'temp', 'temp', 'temp', 'temp');");
            $sql_query2->execute();
            self::generateRandomHashes($query_result[0]['user_id'], $password);
        } catch (PDOException $e) {
            echo "PDOException: " . $e->getMessage();
        }
        echo false; // Everything was successful.
        $connection = null;
    }

    /**
     *  Handles the authentication of the users data.
     */
    public static function handleLogIn(){
        $user_email = "";
        $password = "";
        $connection = self::connectToDb();

        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $user_email = htmlspecialchars($_POST["email_addr"]);
            $password = htmlspecialchars($_POST["pass"]);
        }

        $query_result = self::trySelectWhere($connection, "user_info", "user_email", $user_email);
        if ($query_result == null) {
            echo "User '" . $user_email . "' does not exist...";
            $connection = null;
            die();
        }

        $query_result2 = self::trySelectWhere($connection, "user_auth", "user_id", $query_result[0]['user_id']);
        if ($query_result2 == null) {
            $connection = null;
            die();
        }

        $user_password = self::getHash($password, $query_result2[0]['salt'], $query_result2[0]['pass_key'], $query_result2[0]['hash_key']);

        echo $user_password . "\n" . $query_result2[0]['user_password'] . "\n";
        /* Check the hash value of the password. */
        if($query_result2[0]['user_password'] == $user_password) {
            echo "Access granted.";
            self::generateRandomHashes($query_result2[0]['user_id'], $password);
        } else {
            echo "Access Denied... Invalid password...";
            $connection = null;
            die();
        }

        /* Close connection. */
        $connection = null;
    }

    /**
     * Generates random hashes and sets them to the database.
     * @param $user_id int The id of the user to update.
     * @param $password string The users password to generate new hashes for.
     */
    private static function generateRandomHashes($user_id, $password) {
        $connection = self::connectToDb();
        $salt = self::genRandomKey();
        $salt = self::hashItUp($salt, $salt);
        $pass_key = self::genRandomKey();
        $hash_key = self::genRandomKey();

        $hash = self::getHash($password, $salt, $pass_key, $hash_key);

        try {

            $sql_query = $connection->prepare(
                "UPDATE user_auth SET user_password='$hash', salt='$salt', pass_key='$pass_key', hash_key='$hash_key' WHERE user_id='$user_id'");
            $sql_query->execute();
        } catch (PDOException $e) {
            echo "PDOException: " . $e->getMessage();
        }
        $connection = null;
    }

    /**
     * Function to get the hash with all credentials.
     * @param $password string Entered password.
     * @param $salt string Salt.
     * @param $pass_key int Key for password combination.
     * @param $hash_key int Key for hash.
     * @return string The hash.
     */
    private static function getHash($password, $salt, $pass_key, $hash_key) {
        $user_password = $salt . $password . $salt;
        for($index=0;$index<strlen($pass_key);$index++){
            switch ((int)$pass_key[$index]) {
                case 0:
                    $user_password = md5($user_password . $password);
                    break;
                case 1:
                    $user_password = md5($user_password . $salt);
                    break;
                case 2:
                    $user_password = sha1($user_password . $password);
                    break;
                case 3:
                    $user_password = sha1($user_password . $salt);
                    break;
            }
        }

        return self::hashItUp($user_password, $hash_key);
    }

    /**
     * Generates a hash to store the password.
     * @param $password string Password to hash.
     * @param $method string Method to encrypt.
     * @return string The hashed value.
     */
    private static function hashItUp($password, $method) {
        for ($index = 0; $index < strlen($method); $index++){
            switch ((int)$method[$index]) {
                case 0:
                    $password = md5($password);
                    break;
                case 1:
                    $password = sha1($password);
                    break;
                case 2:
                    $password = sha1($password);
                    $password = md5($password);
                    break;
                case 3:
                    $password = md5($password);
                    $password = sha1($password);
                    break;
            }
        }

        return $password;
    }

    /**
     * Generates a random hashing process.
     * @return string The random key.
     */
    private static function genRandomKey() {
        $iterations = rand(5,20);
        $key = '';
        for ($index = 0; $index < $iterations; $index++) {
            $key = $key . strval(rand(0,3));
        }
        return $key;
    }

    /**
     * Fetches the User row from the Users table in the database.
     * @param $connection PDO The connection.
     * @param $table String Table to look up.
     * @param $on_field String Field to look up.
     * @param $value String Where value is true.
     * @return mixed User row in DB;
     */
    private static function trySelectWhere($connection, $table, $on_field, $value){
        $sql_query = $connection->prepare(
            "SELECT * FROM $table WHERE $on_field='$value'");
        $sql_query->execute();
        $sql_query->setFetchMode(PDO::FETCH_ASSOC);

        return $sql_query->fetchAll();
    }

    /**
     * Try to instantiate the connection to DB.
     */
    private static function connectToDb() {
        /* Unsecure method to connect to the DB. */
        $servername = "localhost";
        $username = "dave";
        $password = "Passw0rd123";
        $db_name = "user_database";

        try {
            $connection = new PDO("mysql:host=$servername;dbname=$db_name;port=3306;charset=utf8mb4", $username, $password);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e)
        {
            echo "Connection Failed... " . $e->getMessage();
            die();
        }

        return $connection;
    }
}