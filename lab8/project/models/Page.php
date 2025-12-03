<?php

namespace Project\Models;

use Core\Model;

class Page extends Model
{
    
    public function getById($id)
    {
        
        return $this->findOne("SELECT * FROM pages WHERE id=$id");
    }
    
   
    public function getByRange($from, $to)
    {
        // Метод findMany() возвращает массив всех найденных записей
        return $this->findMany("SELECT * FROM pages WHERE id>=$from AND id<=$to");
    }
    
    /**
     * Получить все страницы (только id и title)
     * Возвращает список страниц для отображения навигации
     * 
     * @return array Массив страниц с полями id и title
     */
    public function getAll()
    {
        // Выбираем только id и title для списка
        return $this->findMany("SELECT id, title FROM pages ORDER BY id");
    }
    

     // Доп. методы
    /**
     * Получить полную информацию о всех страницах
     * 
     * @return array Массив всех страниц со всеми полями
     */
    public function getAllFull()
    {
        return $this->findMany("SELECT * FROM pages ORDER BY id");
    }
    
    
    
    /**
     * Получить количество страниц
     * 
     * @return int Количество страниц в БД
     */
    public function getCount()
    {
        $result = $this->findOne("SELECT COUNT(*) as count FROM pages");
        return $result ? (int)$result['count'] : 0;
    }
}