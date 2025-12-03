<?php
namespace Project\Controllers;
use \Core\Controller;
use \Project\Models\Product;

class ProductController extends Controller
{
    /**
     * Действие show - показывает информацию об одном продукте из БД
     * Обрабатывает адреса вида: /product/:id/
     */
    public function show($params)
    {
        $id = isset($params['id']) ? (int)$params['id'] : 0;

        if ($id <= 0) {
            $this->title = 'Ошибка';
            return $this->render('product/show', [
                'error' => 'Некорректный ID продукта',
                'product' => null
            ]);
        }

        // Получаем продукт из БД
        $model = new Product();
        $product = $model->getById($id);

        if (!$product) {
            $this->title = 'Продукт не найден';
            return $this->render('product/show', [
                'error' => 'Продукт с ID=' . $id . ' не найден в базе данных',
                'product' => null
            ]);
        }

        // Вычисляем стоимость
        $cost = $product['price'] * $product['quantity'];

        $this->title = 'Продукт "' . $product['name'] . '"';
        
        return $this->render('product/show', [
            'product' => $product,
            'cost' => $cost,
            'error' => null
        ]);
    }

    /**
     * Действие all - показывает список всех продуктов из БД
     */
    public function all()
    {
        $this->title = 'Список всех продуктов';

        try {
            $model = new Product();
            $products = $model->getAll();

            // Вычисляем стоимость для каждого продукта
            foreach ($products as &$product) {
                $product['cost'] = $product['price'] * $product['quantity'];
            }

            return $this->render('product/all', [
                'products' => $products,
                'h1' => $this->title
            ]);
        } catch (\Exception $e) {
            $this->title = 'Ошибка подключения к базе данных';
            return $this->render('error/dbError', [
                'message' => $e->getMessage()
            ]);
        }
    }
}