<?php
class Database {
    private static $host = 'localhost';
    private static $dbName = 'moja_baza'; // Naziv baze koju si napravio
    private static $username = 'root';
    private static $password = ''; // Prazno ako koristiš XAMPP bez lozinke

    public static function connect() {
        try {
            $pdo = new PDO("mysql:host=" . self::$host . ";dbname=" . self::$dbName,
                           self::$username, self::$password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            throw new PDOException('Connection failed: ' . $e->getMessage());

        }
    }
}
?>
