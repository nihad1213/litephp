<?php 

/*
* -----------------------------------------------------------------------------
* Class: Database
* -----------------------------------------------------------------------------
* This class is responsible for establishing a secure and reliable connection 
* to a MySQL database using PHP Data Objects (PDO).
*
* It encapsulates database configuration details and provides a method for 
* retrieving a configured PDO instance, ensuring consistent database access 
* throughout the application.
*
* Constructor Parameters:
*  - string $host     : The hostname or IP address of the database server.
*  - string $name     : The name of the database.
*  - string $user     : The username used for authentication.
*  - string $password : The password used for authentication.
*
* Created by: Nihad Namatli
* -----------------------------------------------------------------------------
*/

class Database {

    /*
    * -------------------------------------------------------------------------
    * Constructor: __construct
    * -------------------------------------------------------------------------
    * Initializes the Database object with connection details.
    *
    * Parameters:
    *  - string $host     : The hostname of the MySQL server.
    *  - string $name     : The name of the MySQL database.
    *  - string $user     : The MySQL user name.
    *  - string $password : The MySQL user password.
    *
    * Return Type:
    *  - void
    * -------------------------------------------------------------------------
    */
    public function __construct(
        private string $host, 
        private string $name, 
        private string $user, 
        private string $password) 
    {}

    /*
    * -------------------------------------------------------------------------
    * Function: getConnect
    * -------------------------------------------------------------------------
    * Creates and returns a new PDO connection instance configured to interact 
    * with a MySQL database using UTF-8 encoding. It sets several important 
    * attributes for error handling and performance:
    *  - PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION (throws exceptions on errors)
    *  - PDO::ATTR_EMULATE_PREPARES   => false (uses native prepared statements)
    *  - PDO::ATTR_STRINGIFY_FETCHES  => false (fetches native data types)
    *
    * Return Type:
    *  - PDO : A configured PDO instance ready for executing SQL queries.
    * -------------------------------------------------------------------------
    */
    public function getConnect(): PDO {
        
        $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8";

        return new PDO($dsn, $this->user, $this->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false
        ]);
        
    }

}
