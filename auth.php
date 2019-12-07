
<?php
    handleLogIn();

    function handleSignUp(){
        $user_email = "";
        $user_name = "";
        $user_first_name = "";
        $user_last_name = "";
        $password = "";
        $date_of_birth = "";

        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $user_email = $_POST["email_addr"];
            $user_name = $_POST["user_name"];
            $user_first_name = $_POST["first_name"];
            $user_last_name = $_POST["last_name"];
            $password = $_POST["pass"];
            $date_of_birth = $_POST["date_of_birth"];
        }

        $connection = connectToDb();
        try {
            $sql_query = $connection->prepare("INSERT INTO user_info ( user_name, user_first_name, user_last_name, date_of_birth) VALUES" .
                                                            "($user_email, $user_name, $user_first_name, $user_last_name, $date_of_birth);");
            $sql_query->execute();
            $query_result = trySelectWhere($connection, "user_info", "user_email", $user_email);
            $sql_query2 = $connection->prepare(
                "INSERT INTO user_auth (user_id, user_password, salt, pass_key, hash_key) values" .
                "($query_result[0]['user_id'], 'temp', 'temp', 'temp', 'temp');");
            $sql_query2->execute();
            generateRandomHashes($query_result[0]['user_id'], $password);
        } catch (PDOException $e) {
            echo "PDOException: " . $e->getMessage();
        }

        $connection = null;
    }

    /**
     *  Handles the authentication of the users data.
     */
    function handleLogIn(){
        $user_email = "";
        $password = "";
        $connection = connectToDb();

        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $user_email = $_POST["email_addr"];
            $password = $_POST["pass"];
        }

        $query_result = trySelectWhere($connection, "user_info", "user_email", $user_email);
        $query_result2 = trySelectWhere($connection, "user_auth", "user_id", $query_result[0]['user_id']);
        $user_password = getHash($password, $query_result2[0]['salt'], $query_result2[0]['pass_key'], $query_result2[0]['hash_key']);

        echo $user_password . "\n" . $query_result2[0]['user_password'] . "\n";
        /* Check the hash value of the password. */
        if($query_result2[0]['user_password'] == $user_password) {
            echo "Access granted.";
            generateRandomHashes($query_result2[0]['user_id'], $password);
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
    function generateRandomHashes($user_id, $password) {
        $connection = connectToDb();
        $salt = genRandomKey();
        $salt = hashItUp($salt, $salt);
        $pass_key = genRandomKey();
        $hash_key = genRandomKey();

        $hash = getHash($password, $salt, $pass_key, $hash_key);

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
    function getHash($password, $salt, $pass_key, $hash_key) {
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

        return hashItUp($user_password, $hash_key);
    }

    /**
     * Generates a hash to store the password.
     * @param $password string Password to hash.
     * @param $method string Method to encrypt.
     * @return string The hashed value.
     */
    function hashItUp($password, $method) {
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
    function genRandomKey() {
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
    function trySelectWhere($connection, $table, $on_field, $value){
        $sql_query = $connection->prepare(
            "SELECT * FROM $table WHERE $on_field='$value'");
        $sql_query->execute();
        $sql_query->setFetchMode(PDO::FETCH_ASSOC);
        $result = $sql_query->fetchAll();

        if ($result == null) {
            echo "no results for \"$value\"";
            $connection = null;
            die();
        } else {
            echo "found " . $result[0][$on_field] . "\n";
        }

        return $result;
    }

    /**
     * Try to instantiate the connection to DB.
     */
    function connectToDb() {
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
?>
