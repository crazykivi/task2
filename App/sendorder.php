<?php
header('Content-Type: text/html; charset=utf-8'); 
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$subject = "New application from the website";

$orderDetailsRaw = $_POST['orderDetails'] ?? '';
//file_put_contents('post_data.txt', print_r($_POST, true)); // это для логирования данных

parse_str($orderDetailsRaw, $orderDetailsArray);

$product_id = $orderDetailsArray['product'] ?? 'неизвестный продукт';
$days = $orderDetailsArray['days'] ?? 0;
$selected_services = $orderDetailsArray['services'] ?? [];

require_once 'Infrastructure/sdbh.php';
use sdbh\sdbh;
$dbh = new sdbh(); 
$product = $dbh->make_query("SELECT * FROM a25_products WHERE ID = $product_id");
if ($product) {
    $product = $product[0];
    $price = $product['PRICE'];
    $product_name = $product['NAME'];
} else {
    echo "Ошибка, товар не найден!";
    exit;
}

$orderDetailsFormatted = "Название продукта: $product_name\n" .
    "Цена продукта: $price\n" .
    "Количество дней: $days\n" .
    "Дополнительные услуги: " . implode(', ', $selected_services);

if (empty($orderDetailsFormatted)) {
    echo "Ошибка при отправке заявки: тело письма пустое";
    exit;
}

$mail = new PHPMailer(true);
try {
    // Настройки SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.yandex.com';
    $mail->SMTPAuth = true;
    $mail->Username = ''; // логин от почты
    $mail->Password = ''; // пароль от smtp почты
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->setFrom('', 'test message'); // тут указать отправителя
    $to = ''; // тут указать получателя
    $mail->addAddress($to);

    $mail->isHTML(true); 
    $mail->Subject = $subject; 
    $mail->Body = nl2br(htmlspecialchars($orderDetailsFormatted, ENT_QUOTES, 'UTF-8'));

    $mail->send();
    echo "Заявка успешно отправлена!";
} catch (Exception $e) {
    echo "Ошибка при отправке заявки: {$mail->ErrorInfo}";
}
