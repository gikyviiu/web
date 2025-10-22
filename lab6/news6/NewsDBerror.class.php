<?php
require_once 'INewsDB.class.php';

class NewsDB implements INewsDB, IteratorAggregate {
    const DB_NAME = 'newserror.db';
    const RSS_NAME = 'rss.xml';
    const RSS_TITLE = 'Последние новости';
    const RSS_LINK = 'http://f1182369.xsph.ru/lab5/news5/news.php';

    private $_pdo;
    private $items = [];

    public function __construct() {
        try {
            $this->_pdo = new PDO("sqlite:" . self::DB_NAME);
            $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Ошибка подключения к БД: " . $e->getMessage());
            die("Ошибка подключения к базе данных.");
        }

        if ($this->shouldCreateDatabase()) {
            $this->createDatabaseWithTransaction();
        }

        $this->getCategories();
    }

    public function __destruct() {
        $this->_pdo = null;
    }

    /**
     * Определяет, нужно ли создавать базу данных
     * Учитывает: файл не существует ИЛИ файл существует, но его размер 0
     *
     * @return bool
     */
    private function shouldCreateDatabase(): bool {
        if (!file_exists(self::DB_NAME)) {
            return true;
        }

        $size = filesize(self::DB_NAME);
        return $size === 0;
    }

    /**
     * Создаёт базу данных в транзакции
     */
    private function createDatabaseWithTransaction() {
        try {
            $this->_pdo->beginTransaction();

            $this->createTables();
            $this->seedCategoryTable();

            $this->_pdo->commit();

            error_log("База данных успешно создана и заполнена.");

        } catch (Exception $e) {
            $this->_pdo->rollback();
            error_log("Ошибка при создании базы данных: " . $e->getMessage());

            echo "<h2 style='color:red;'>Ошибка при создании базы данных</h2>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p>Пожалуйста, удалите файл <code>" . self::DB_NAME . "</code> и перезагрузите страницу.</p>";
            exit;
        }
    }

    /**
     * Создание таблиц msgs и category, если они не существуют
     */
    private function createTables() {
        $sql = "
            CREATE TABLE IF NOT EXISTS msgs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT,
                category INTEGER,
                description TEXT,
                source TEXT,
                datetime INTEGER
            );

            CREATE TABLE IF NOT EXISTS category (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL
            );
        ";

        try {
            $this->_pdo->exec($sql);
        } catch (PDOException $e) {
            $errorCode = $this->_pdo->errorCode();
            $errorInfo = $this->_pdo->errorInfo();
            error_log("Ошибка создания таблиц: " . $e->getMessage());
            error_log("Код ошибки: $errorCode");
            error_log("Детали: " . print_r($errorInfo, true));
            throw new Exception("Не удалось создать таблицы: " . $e->getMessage());
        }
    }

    /**
     * Заполнение таблицы category начальными данными
     */
    private function seedCategoryTable() {
        try {
            $this->_pdo->exec("INS3ERT OR IGNORE INTO category(id, name) VALUES (1, 'Политика');");
            $this->_pdo->exec("INSERT OR IGNORE INTO category(id, name) VALUES (2, 'Культура');");
            $this->_pdo->exec("INSERT OR IGNORE INTO category(id, name) VALUES (3, 'Спорт');");
        } catch (PDOException $e) {
            $errorCode = $this->_pdo->errorCode();
            $errorInfo = $this->_pdo->errorInfo();
            error_log("Ошибка заполнения категорий: " . $e->getMessage());
            error_log("Код ошибки: $errorCode");
            error_log("Детали: " . print_r($errorInfo, true));
            throw new Exception("Ошибка заполнения категорий: " . $e->getMessage());
        }
    }

    /**
     * Добавление новой записи в новостную ленту
     */
    public function saveNews($title, $category, $description, $source) {
        $dt = time();

        try {
            $stmt = $this->_pdo->prepare("SELECT id FROM category WHERE name = ?");
            $stmt->execute([$category]);
            $catRow = $stmt->fetch(PDO::FETCH_ASSOC);

            $categoryId = $catRow ? (int)$catRow['id'] : 0;

            $stmt = $this->_pdo->prepare("
                INSERT INTO msgs (title, category, description, source, datetime)
                VALUES (?, ?, ?, ?, ?)
            ");

            $result = $stmt->execute([$title, $categoryId, $description, $source, $dt]);

            if ($result) {
                $this->createRss();
                return true;
            }

            $errorCode = $this->_pdo->errorCode();
            $errorInfo = $this->_pdo->errorInfo();
            error_log("saveNews: execute вернул false. Код: $errorCode, Детали: " . print_r($errorInfo, true));
            return false;

        } catch (PDOException $e) {
            $errorCode = $this->_pdo->errorCode();
            $errorInfo = $this->_pdo->errorInfo();
            error_log("saveNews: PDOException — " . $e->getMessage());
            error_log("Код ошибки: $errorCode");
            error_log("Детали ошибки: " . print_r($errorInfo, true));
            return false;
        }
    }

    /**
     * Удаление записи из новостной ленты
     */
    public function deleteNews($id) {
        try {
            $stmt = $this->_pdo->prepare("DELETE FROM msgs WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
                $this->createRss();
                return true;
            }

            $errorCode = $this->_pdo->errorCode();
            $errorInfo = $this->_pdo->errorInfo();
            error_log("deleteNews: execute вернул false. Код: $errorCode, Детали: " . print_r($errorInfo, true));
            return false;

        } catch (PDOException $e) {
            $errorCode = $this->_pdo->errorCode();
            $errorInfo = $this->_pdo->errorInfo();
            error_log("deleteNews: PDOException — " . $e->getMessage());
            error_log("Код ошибки: $errorCode");
            error_log("Детали ошибки: " . print_r($errorInfo, true));
            return false;
        }
    }

    /**
     * Получение всех записей из новостной ленты
     */
    public function getNews() {
        try {
            $query = "
                SELECT msgs.id as id, msgs.title, category.name as category,
                       msgs.description, msgs.source, msgs.datetime
                FROM msgs
                JOIN category ON category.id = msgs.category
                ORDER BY msgs.id DESC
            ";

            $stmt = $this->_pdo->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $errorCode = $this->_pdo->errorCode();
            $errorInfo = $this->_pdo->errorInfo();
            error_log("getNews: PDOException — " . $e->getMessage());
            error_log("Код ошибки: $errorCode");
            error_log("Детали ошибки: " . print_r($errorInfo, true));
            return [];
        }
    }

    /**
     * Получение категорий из БД и заполнение свойства $items
     */
    private function getCategories(): void {
        try {
            $query = "SELECT id, name FROM category";
            $stmt = $this->_pdo->prepare($query);
            $stmt->execute();

            $categories = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $categories[(int)$row['id']] = $row['name'];
            }

            $this->items = $categories;

        } catch (PDOException $e) {
            $errorCode = $this->_pdo->errorCode();
            $errorInfo = $this->_pdo->errorInfo();
            error_log("getCategories: PDOException — " . $e->getMessage());
            error_log("Код ошибки: $errorCode");
            error_log("Детали ошибки: " . print_r($errorInfo, true));
            $this->items = [];
        }
    }

    /**
     * Метод, требуемый интерфейсом IteratorAggregate
     */
    public function getIterator(): ArrayIterator {
        return new ArrayIterator($this->items);
    }

    /**
     * Метод для создания RSS-документа с помощью DOM
     */
    public function createRss(): void {
        try {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->formatOutput = true;
            $dom->preserveWhiteSpace = false;

            $rss = $dom->createElement('rss');
            $dom->appendChild($rss);

            $version = $dom->createAttribute('version');
            $version->value = '2.0';
            $rss->appendChild($version);

            $channel = $dom->createElement('channel');
            $rss->appendChild($channel);

            $title = $dom->createElement('title');
            $title->appendChild($dom->createTextNode(self::RSS_TITLE));
            $channel->appendChild($title);

            $link = $dom->createElement('link');
            $link->appendChild($dom->createTextNode(self::RSS_LINK));
            $channel->appendChild($link);

            $newsList = $this->getNews();

            foreach ($newsList as $item) {
                $itemElement = $dom->createElement('item');
                $channel->appendChild($itemElement);

                $titleItem = $dom->createElement('title');
                $titleItem->appendChild($dom->createTextNode($item['title']));
                $itemElement->appendChild($titleItem);

                $linkItem = $dom->createElement('link');
                $linkItem->appendChild($dom->createTextNode(self::RSS_LINK . '?id=' . $item['id']));
                $itemElement->appendChild($linkItem);

                $descriptionItem = $dom->createElement('description');
                $cdata = $dom->createCDATASection($item['description']);
                $descriptionItem->appendChild($cdata);
                $itemElement->appendChild($descriptionItem);

                $pubDateItem = $dom->createElement('pubDate');
                $pubDateItem->appendChild($dom->createTextNode(date(DATE_RFC2822, $item['datetime'])));
                $itemElement->appendChild($pubDateItem);

                $categoryItem = $dom->createElement('category');
                $categoryItem->appendChild($dom->createTextNode($item['category']));
                $itemElement->appendChild($categoryItem);
            }

            $dom->save(self::RSS_NAME);

        } catch (Exception $e) {
            error_log("Ошибка при создании RSS: " . $e->getMessage());
        }
    }
}