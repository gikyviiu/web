<?php if ($error): ?>
    <h1>Ошибка</h1>
    <p><?= htmlspecialchars($error) ?></p>
    <a href="<?= BASE_PATH ?>/products/all/">Назад к списку продуктов</a>
<?php else: ?>
    <h1>Продукт "<?= htmlspecialchars($product['name']) ?>"</h1>
    <p>
        Цена: <?= number_format($product['price'], 2) ?> руб., количество: <?= $product['quantity'] ?> шт.
    </p>
    <p>
        Стоимость (цена × количество): <?= number_format($cost, 2) ?> руб.
    </p>
    <p>
        Описание: <?= htmlspecialchars($product['description']) ?>
    </p>
    <a href="<?= BASE_PATH ?>/products/all/">Назад к списку продуктов</a>
<?php endif; ?>