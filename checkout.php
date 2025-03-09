<?php

// v1   19.11.2021
// Powered by Smart Sender
// https://smartsender.com

ini_set('max_execution_time', '1700');
set_time_limit(1700);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Content-Type: application/json; charset=utf-8');

http_response_code(200);

//--------------

$input = json_decode(file_get_contents('php://input'), true);
include ('config.php');

if ($input["userId"] == NULL) {
  $result["state"] = false;
  $result["message"]["userId"] = "userId is missing";
}
if ($input["action"] == NULL) {
  $result["state"] = false;
  $result["message"]["action"] = "action is missing";
}
if ($result["state"] === false) {
  http_response_code(422);
  echo json_encode($result);
  exit;
}

// Формирование данных
$sendData["order_ref"] = $input["userId"]."-".mt_rand(1000000, 9999999);
if ($input["description"] != NULL) {
  $sendData["destination"] = $input["description"];
}
if ($input["redirectUrl"] != NULL) {
  $sendData["return_url"] = $input["redirectUrl"];
}
$sendData["callback_url"] = $url."/callback.php?action=".$input["action"];

// Получение списка товаров в корзине пользователя
$headers[] = "Authorization: Bearer ".$ss_token;
$cursor = json_decode(send_request("https://api.smartsender.com/v1/contacts/".$input["userId"]."/checkout?page=1&limitation=20", $headers), true);
if ($cursor["error"] != NULL && $cursor["error"] != 'undefined') {
  $result["status"] = "error";
  $result["message"][] = "Ошибка получения данных из SmartSender";
  if ($cursor["error"]["code"] == 404 || $cursor["error"]["code"] == 400) {
    $result["message"][] = "Пользователь не найден. Проверте правильность идентификатора пользователя и приналежность токена к текущему проекту.";
  } else if ($cursor["error"]["code"] == 403) {
    $result["message"][] = "Токен проекта SmartSender указан неправильно. Проверте правильность токена.";
  }
  echo json_encode($result);
  exit;
} else if (empty($cursor["collection"])) {
  $result["status"] = "error";
  $result["message"][] = "Корзина пользователя пустая. Для тестирования добавте товар в корзину.";
  echo json_encode($result);
  exit;
}
$pages = $cursor["cursor"]["pages"];
for ($i = 1; $i <= $pages; $i++) {
  $checkout = json_decode(send_request("https://api.smartsender.com/v1/contacts/".$input["userId"]."/checkout?page=".$i."&limitation=20", $headers), true);
  $essences = $checkout["collection"];
  $currency = $essences[0]["cash"]["currency"];
  foreach ($essences as $product) {
    $items["name"] = $product["product"]["name"]." ".$product["name"];
    $items["cnt"] = $product["pivot"]["quantity"];
    $items["price"] = $product["cash"]["amount"];
    $sum[] = $product["cash"]["amount"] * $product["pivot"]["quantity"];
    $count[] = $items["cnt"];
    if (file_exists("media/".$product["product"]["id"]."/".$product["id"].".jpg")) {
      $items["product_img_src"] = $url."/media/".$product["product"]["id"]."/".$product["id"].".jpg";
    } else if (file_exists("media/".$product["product"]["id"].".jpg")) {
      $items["product_img_src"] = $url."/media/".$product["product"]["id"].".jpg";
    } else if (file_exists("media/default.jpg")) {
      $items["product_img_src"] = $url."/media/default.jpg";
    }
    $sendData["products"][] = $items;
    unset($items);
  }
}
$sendData["amount"] = array_sum($sum);
if ($currency == "USD") {
  $sendData["ccy"] = 840;
} else if ($currency = "EUR") {
  $sendDara["ccy"] = 978;
}
$sendData["count"] = array_sum($count);
if ($input["delivery"] != NULL && is_array($input["delivery"])) {
  if (in_array("pickup", $input["delivery"])) {
    $sendData["dlv_method_list"][] = "pickup";
  }
  if (in_array("np_brnm", $input["delivery"])) {
    $sendData["dlv_method_list"][] = "np_brnm";
  }
  if (in_array("courier", $input["delivery"])) {
    $sendData["dlv_method_list"][] = "courier";
  }
  if (in_array("np_box", $input["delivery"])) {
    $sendData["dlv_method_list"][] = "np_box";
  }
}
if ($input["payments"] != NULL && is_array($input["payments"])) {
  if (in_array("card", $input["payments"])) {
    $sendData["payment_method_list"][] = "card";
  }
  if (in_array("payment_on_delivery", $input["payments"])) {
    $sendData["payment_method_list"][] = "payment_on_delivery";
  }
  if (in_array("part_purchase", $input["payments"])) {
    if ($input["partCount"] != NULL && $input["partCount"] >= 3) {
      $sendData["payment_method_list"][] = "part_purchase";
      $sendData["payments_number"] = $input["partCount"];
      settype($sendData["payments_number"], "int");
    }
  }
}
if ($sendData["payment_method_list"] == NULL) {
  $sendData["payment_method_list"][] = "card";
}
if ($input["merchantDeliveryPay"] === true || $input["merchantDeliveryPay"] === "true" || $input["merchantDeliveryPay"] === 1 || $input["merchantDeliveryPay"] === "1") {
  $sendData["dlv_pay_merchant"] = true;
}
unset($headers);
$headers[] = "X-Token: ".$mono_token;
$headers[] = "X-Cms: SmartSender";

$response = json_decode(send_request("https://api.monobank.ua/personal/checkout/order", $headers, "POST", $sendData), true);
if ($response["result"] != NULL) {
  $result["mono"] = $response["result"];
} else {
  $result["mono"] = $response;
}
$result["sendData"] = $sendData;
$result["headers"] = $headers;


echo json_encode($result);






