<?php
require_once 'INewsDB.class.php';

/**
 * Класс NewsDB реализует интерфейс INewsDB для работы с новостной лентой
 * Использует SQLite3 для хранения данных и генерирует RSS-ленту
 */
class NewsDB implements INewsDB, IteratorAggregate {
    const DB_NAME = 'news.db';

    const RSS_NAME = 'rss.xml';
    const RSS_TITLE = 'Последние новости';
    const RSS_LINK = 'http://f1182369.xsph.ru/lab5/news5/news.php'; 

    private $_db;

    private $items = [];

    /**
     * Конструктор класса
     * Устанавливает соединение с базой данных SQLite
     * Если базы нет — создаёт её и таблицы + заполняет category
     */
    public function __construct() {
        if (!file_exists(self::DB_NAME)) {
            $this->_db = new SQLite3(self::DB_NAME);
            $this->createTables();
            $this->seedCategoryTable();
        } else {
            $this->_db = new SQLite3(self::DB_NAME);
        }

        $this->getCategories();
    }

    /**
     * Деструктор класса
     * Закрывает соединение с базой данных
     */
    public function __destruct() {
        if ($this->_db) {
            $this->_db->close();
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
                id INTEGER,
                name TEXT
            );
        ";
        $this->_db->exec($sql);
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
        $this->_db->exec($sql);
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

        $catStmt = $this->_db->prepare("SELECT id FROM category WHERE name = ?");
        $catStmt->bindValue(1, $category, SQLITE3_TEXT);
        $res = $catStmt->execute();
        $catRow = $res->fetchArray(SQLITE3_ASSOC);

        if (!$catRow) {
            $categoryId = 0; 
        } else {
            $categoryId = $catRow['id'];
        }

        $stmt = $this->_db->prepare("
            INSERT INTO msgs (title, category, description, source, datetime)
            VALUES (?, ?, ?, ?, ?)");

        $stmt->bindValue(1, $title, SQLITE3_TEXT);
        $stmt->bindValue(2, $categoryId, SQLITE3_INTEGER);
        $stmt->bindValue(3, $description, SQLITE3_TEXT);
        $stmt->bindValue(4, $source, SQLITE3_TEXT);
        $stmt->bindValue(5, $dt, SQLITE3_INTEGER);

        $result = $stmt->execute();

        
        if ($result !== false) {
            $this->createRss();
        }

        return $result !== false;
    }

    /**
     * Удаление записи из новостной ленты
     *
     * @param integer $id - идентификатор удаляемой записи
     *
     * @return boolean - результат успех/ошибка
     */
    public function deleteNews($id) {
        $stmt = $this->_db->prepare("DELETE FROM msgs WHERE id = ?");
        $stmt->bindValue(1, $id, SQLITE3_INTEGER);
        $result = $stmt->execute();

        
        if ($result !== false) {
            $this->createRss();
        }

        return $result !== false;
    }

    /**
     * Получение всех записей из новостной ленты
     *
     * @return array - результат выборки в виде массива
     */
    public function getNews() {
        $result = [];
        $query = "
        SELECT msgs.id as id, msgs.title, category.name as category, msgs.description, msgs.source, msgs.datetime
        FROM msgs
        JOIN category ON category.id = msgs.category
        ORDER BY msgs.id DESC
        ";

        $stmt = $this->_db->prepare($query);
        $res = $stmt->execute();

        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Закрытый метод для получения категорий из БД и заполнения свойства $items
     */
    private function getCategories(): void {
        $query = "SELECT id, name FROM category";
        $stmt = $this->_db->prepare($query);
        $res = $stmt->execute();

        $categories = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $categories[$row['id']] = $row['name'];
        }

        $this->items = $categories;
    }

    /**
     * Метод, требуемый интерфейсом IteratorAggregate
     * Возвращает итератор для свойства $items
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
    }
}
