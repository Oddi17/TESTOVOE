<?php
//userid не включено было в формулировке задания, в самой таблице я его видел, конечно
//event_date может тип со строки на datetime поменять можно было
//event_date изменил размер строки

define('DB_PERSISTENCY', 'true');
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'test');
define('PDO_DSN', 'mysql:host=' . DB_SERVER . ';dbname=' . DB_DATABASE);
define('PDO_START','mysql:host='.DB_SERVER.';');


class DataBase{
    const CREATE_TABLE = "CREATE TABLE IF NOT EXISTS `order` (
                                                    `id` INTEGER PRIMARY KEY AUTO_INCREMENT,
                                                    `event_id` TINYINT,
                                                    `event_date` VARCHAR(20),
                                                    `ticket_adult_price` SMALLINT,
                                                    `ticket_adult_quantity` SMALLINT,
                                                    `ticket_kid_price` SMALLINT,
                                                    `ticket_kid_quantity` SMALLINT,
                                                    `barcode` VARCHAR(120) UNIQUE,
                                                    `equal_price` SMALLINT,
                                                    `created` DATETIME
                                                )";
    private static $db;

    public static function Close()
    {
        self::$db = null;
    }

    public function openDataBase()
    {
        if (!isset(self::$db)) {
            try {
                self::$db = new PDO(PDO_START, DB_USERNAME, DB_PASSWORD);
                self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$db->exec("CREATE DATABASE IF NOT EXISTS `".DB_DATABASE."`");
                echo "База данных '".DB_DATABASE."' успешно создана или уже существует."."\n";
                self::Close();
                self::$db = new PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD);
                self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$db->exec(DataBase::CREATE_TABLE);
                echo "Подключение к базе данных '".DB_DATABASE."' установлено."."\n";
                echo "Таблица 'order' успешно создана или уже существует."."\n";
            } catch (PDOException $e) {
                self::Close();
                trigger_error($e->getMessage(), E_USER_ERROR);
            }
        }
        return self::$db;
    }
    public function setDataBase($string)
    {
        $db = $this->openDataBase();
        $db->exec($string);
    }

    public function setBasePrepare($query, $data)
    {
        $db = $this->openDataBase();
        $stmt = $db->prepare($query);
        $stmt->execute($data);

    }

    public function getDataBase($string)
    {
        $db = $this->openDataBase();
        $query = $db->query($string);
        if ($query) {
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
        
    }

    public function getBasePrepare($query, $data)
    {
        $db = $this->openDataBase();
        $stmt = $db->prepare($query);
        $stmt->execute($data);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}