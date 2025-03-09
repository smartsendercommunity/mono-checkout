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
$result["state"] = true;


if ($input["userId"] == NULL) {
  $result["state"] = false;
  $result["error"]["message"][] = "'userId' is missing";
}
if ($input["amount"] == NULL) {
  $result["state"] = false;
  $result["error"]["message"][] = "'amount' is missing";
}
if ($input["action"] == NULL) {
  $result["state"] = false;
  $result["error"]["message"][] = "'action' is missing";
}
if ($input["productName"] == NULL) {
  $result["state"] = false;
  $result["error"]["message"][] = "'productName' is missing";
}
if ($result["state"] === false) {
  http_response_code(422);
  echo json_encode($result);
  exit;
}

// Формирование данных
$sendData["order_ref"] = $input["userId"]."-".mt_rand(1000000, 9999999);
$sendData["amount"] = str_replace(array(",", " "), array(".", ""), $input["amount"]);
// $sendData["amount"] = $amount * 100;
settype($sendData["amount"], "float");
if ($input["currency"] == "USD") {
  $sendData["ccy"] = 840;
} else if ($input["currency"] == "EUR") {
  $sendData["ccy"] = 978;
}
$sendData["products"][0]["name"] = $input["productName"];
if ($input["productImage"] != NULL) {
  $input["products"][0]["product_img_src"] = $input["productImage"];
}
if ($input["productCount"] != NULL) {
  settype($input["productCount"], "int");
  if ($input["productCount"] >= 1) {
    $sendData["products"][0]["cnt"] = $input["productCount"];
  } else {
    $sendData["products"][0]["cnt"] = 1;
  }
} else {
  $sendData["products"][0]["cnt"] = 1;
}
$sendData["count"] = $sendData["products"][0]["cnt"];
$sendData["products"][0]["price"] = $sendData["amount"] / $sendData["products"][0]["cnt"];
settype($sendData["products"][0]["price"], "float");
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
$sendData["callback_url"] = $url."/callback.php?action=".$input["action"];
if ($input["redirectUrl"] != NULL) {
  $sendData["return_url"] = $input["redirectUrl"];
}
if ($input["description"] != NULL) {
  $sendData["destination"] = $input["description"];
}
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


echo json_encode($result, JSON_UNESCAPED_UNICODE);




