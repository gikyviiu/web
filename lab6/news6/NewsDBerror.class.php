<?php
require_once 'INewsDB.class.php';

/**
 * Класс NewsDB реализует интерфейс INewsDB для работы с новостной лентой
 * Использует SQLite через PDO для хранения данных и генерирует RSS-ленту
 */
class NewsDB implements INewsDB, IteratorAggregate {
    const DB_NAME = 'news.db';
    const RSS_NAME = 'rss.xml';
    const RSS_TITLE = 'Последние новости';
    const RSS_LINK = 'http://f1182369.xsph.ru/lab5/news5/news.php';

    private $_pdo;
    private $items = [];

    /**
     * Конструктор класса
     * Устанавливает соединение с базой данных SQLite через PDO
     * Если базы нет — создаёт её и таблицы + заполняет category
     */
    public function __construct() {
        try {
            $this->_pdo = new PDO("sqlite:" . self::DB_NAME);
            $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Ошибка подключения к БД: " . $e->getMessage());
            die("Ошибка подключения к базе данных.");
        }

        if (!file_exists(self::DB_NAME)) {
            $this->createTables();
            $this->seedCategoryTable();
        }

        $this->getCategories();
    }

    /**
     * Деструктор класса
     * Закрывает соединение с базой данных
     */
    public function __destruct() {
        $this->_pdo = null;
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
                id INTEGER,
                name TEXT
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
            throw new Exception("Не удалось создать таблицы.");
        }
    }

    /**
     * Заполнение таблицы category начальными данными
     */
    private function seedCategoryTable() {
        $sql = "
            INSERT INTO category(id, name)
            SELECT 1 as id, 'Политика' as name
            UNION SELECT 2 as id, 'Культура' as name
            UNION SELECT 3 as id, 'Спорт' as name;
        ";

        try {
            $this->_pdo->exec($sql);
        } catch (PDOException $e) {
            $errorCode = $this->_pdo->errorCode();
            $errorInfo = $this->_pdo->errorInfo();
            error_log("Ошибка заполнения категорий: " . $e->getMessage());
            error_log("Код ошибки: $errorCode");
            error_log("Детали: " . print_r($errorInfo, true));
        }
    }

    /**
     * Добавление новой записи в новостную ленту
     *
     * @param string $title - заголовок новости
     * @param string $category - категория новости (имя категории, например 'Политика')
     * @param string $description - текст новости
     * @param string $source - источник новости
     *
     * @return boolean - результат успех/ошибка
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
     *
     * @param integer $id - идентификатор удаляемой записи
     *
     * @return boolean - результат успех/ошибка
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
     *
     * @return array - результат выборки в виде массива
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
     *
     * @return ArrayIterator
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