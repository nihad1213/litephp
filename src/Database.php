<?php 

/*
* -----------------------------------------------------------------------------
* Class: Database
* -----------------------------------------------------------------------------
* This class handles establishing a secure and flexible database connection 
* using PHP Data Objects (PDO). It supports multiple database drivers including:
*  - MySQL
*  - PostgreSQL
*  - SQLite
*
* Configuration is dynamic and suitable for .env-based loading.
*
* Constructor Parameters:
*  - string $driver      : Database driver (e.g., mysql, pgsql, sqlite)
*  - string $host        : Hostname or IP address (ignored for SQLite)
*  - string $port        : Port number (optional for SQLite)
*  - string $name        : Database name (ignored for SQLite)
*  - string $user        : Username for authentication (optional for SQLite)
*  - string $password    : Password for authentication (optional for SQLite)
*  - string $sqlitePath  : Full path to the SQLite database file
*
* Created by: Nihad Namatli
* -----------------------------------------------------------------------------
*/

class Database 
{

    /*
    * -------------------------------------------------------------------------
    * Constructor: __construct
    * -------------------------------------------------------------------------
    * Initializes the Database object with flexible parameters to support 
    * different types of databases.
    *
    * Parameters:
    *  - string $driver      : Database driver name ('mysql', 'pgsql', 'sqlite')
    *  - string $host        : Hostname for MySQL/PostgreSQL
    *  - string $port        : Port for MySQL/PostgreSQL
    *  - string $name        : Database name (MySQL/PostgreSQL)
    *  - string $user        : Username for authentication
    *  - string $password    : Password for authentication
    *  - string $sqlitePath  : Path to SQLite file if using SQLite
    *
    * Return Type:
    *  - void
    * -------------------------------------------------------------------------
    */
    public function __construct(
        private string $driver,
        private string $host,
        private string $port,
        private string $name,
        private string $user,
        private string $password,
        private string $sqlitePath = ''
    ) {}

    /*
    * -------------------------------------------------------------------------
    * Function: getConnect
    * -------------------------------------------------------------------------
    * Creates and returns a PDO connection based on the configured driver.
    *
    * Supported Drivers:
    *  - mysql   : Uses charset utf8mb4, includes port
    *  - pgsql   : Uses default PostgreSQL format
    *  - sqlite  : Requires valid file path
    *
    * PDO Attributes Set:
    *  - ERRMODE_EXCEPTION     : Converts errors to exceptions
    *  - EMULATE_PREPARES      : Native prepared statements
    *  - STRINGIFY_FETCHES     : Native types (e.g. integers as int)
    *
    * Return Type:
    *  - PDO : Configured PDO instance ready for database interaction
    * -------------------------------------------------------------------------
    */
    public function getConnect(): PDO 
    {
        $dsn = '';

        switch ($this->driver) {
            case 'mysql':
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->name};charset=utf8mb4";
                break;

            case 'pgsql':
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->name}";
                break;

            case 'sqlite':
                if (empty($this->sqlitePath)) {
                    throw new InvalidArgumentException("SQLite path must be provided.");
                }
                $dsn = "sqlite:{$this->sqlitePath}";
                break;

            default:
                throw new InvalidArgumentException("Unsupported database driver: {$this->driver}");
        }

        return new PDO($dsn, $this->user, $this->password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false
        ]);
    }
}
