<?php
session_start();

$source = imagecreatefromjpeg('noise.jpg');
if (!$source) {
    die('Не удалось загрузить noise.jpg');
}

$symbols_count = rand(5, 6);
$font_size_min = 18;
$font_size_max = 30;
$spacing = 10;

$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
$random_string = '';
for ($i = 0; $i < $symbols_count; $i++) {
    $random_string .= $chars[rand(0, strlen($chars) - 1)];
}
$_SESSION['captcha_code'] = $random_string;

$fonts_dir = 'fonts/';
$available_fonts = glob($fonts_dir . '*.ttf');
if (empty($available_fonts)) {
    die('Папка fonts/ пуста или не содержит .ttf файлов.');
}

$symbol_data = [];
$total_width = 0;

for ($i = 0; $i < strlen($random_string); $i++) {
    $char = $random_string[$i];
    $font = $available_fonts[array_rand($available_fonts)];
    $size = rand($font_size_min, $font_size_max);
    $angle = rand(-20, 20);

    $bbox = imagettfbbox($size, $angle, $font, $char);
    if ($bbox === false) continue;

    $char_width = max($bbox[2], $bbox[4]) - min($bbox[0], $bbox[6]);

    $r = rand(0, 100);
    $g = rand(0, 100);
    $b = rand(0, 100);
    $color = imagecolorallocate($source, $r, $g, $b);

    $symbol_data[] = [
        'char' => $char,
        'font' => $font,
        'size' => $size,
        'angle' => $angle,
        'width' => $char_width,
        'color' => $color
    ];

    $total_width += $char_width + $spacing;
}

$total_width -= $spacing;

// 6. Центрирование
$image_width = imagesx($source);
$image_height = imagesy($source);
$start_x = max(0, ($image_width - $total_width) / 2);
$y = $image_height / 2 + 13;

// 7. Отрисовка
$current_x = $start_x;
foreach ($symbol_data as $data) {
    imagettftext(
        $source,
        $data['size'],
        $data['angle'],
        $current_x,
        $y,
        $data['color'],
        $data['font'],
        $data['char']
    );
    $current_x += $data['width'] + $spacing;
}

// 8. Сжатие на 50%
$compressed_width = $image_width / 2;
$compressed_height = $image_height / 2;
$compressed = imagecreatetruecolor($compressed_width, $compressed_height);
imagecopyresampled(
    $compressed,
    $source,
    0, 0, 0, 0,
    $compressed_width, $compressed_height,
    $image_width, $image_height
);

// 9. Вывод
imagedestroy($source);
header('Content-Type: image/jpeg');
imagejpeg($compressed, null, 80);
imagedestroy($compressed);
