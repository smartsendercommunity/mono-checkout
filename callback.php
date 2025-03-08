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

error_reporting(E_ERROR);
ini_set('display_errors', 1);

//--------------

$body = file_get_contents('php://input');
$input = json_decode($body, true);
$hInput = getallheaders();
$xSign = $hInput["X-Sign"];
//$s1 = base64_decode($xSign);
$s2 = base64_decode($xSign, true);
include ('config.php');
$logUrl .= "-callback";
$log["input"] = $input;


$getPublicKey = json_decode(send_request("https://api.monobank.ua/personal/checkout/signature/public/key", ["X-Token: ".$mono_token]), true);
$log["getKey"] = $getPublicKey;
if ($getPublicKey["key"] == NULL) {
  $result["state"] = false;
  $result["error"]["message"][] = "failed get public key";
  $log["response"] = $result;
  send_request($logUrl, [], "POST", $log);
  echo json_encode($result);
  exit;
}
$signature = base64_decode(getallheaders()["X-Sign"]);
$publicKey = openssl_get_publickey(base64_decode($getPublicKey["key"]));
$check = openssl_verify($body, $signature, $publicKey, OPENSSL_ALGO_SHA256);
$log["sign"] = [
  "check" => $check,
];
if ($check !== 1) {
  $result["state"] = false;
  $result["error"]["message"][] = "failed signature";
  $log["response"] = $result;
  send_request($logUrl, [], "POST", $log);
  echo json_encode($result);
  exit;
}
$result["state"] = true;
if ($input["basket_id"] == NULL) {
  $result["state"] = false;
  $result["error"]["message"][] = "'basket_id' is missing";
}
if ($input["generalStatus"] != "success" && $input["generalStatus"] != "payment_on_delivery") {
  $result["state"] = false;
  $result["error"]["message"][] = "wait is success";
}
if ($result["state"] != true) {
  $log["response"] = $result;
  send_request($logUrl, [], "POST", $log);
  echo json_encode($result);
  exit;
}

// Запуск триггера в Smart Sender
$userId = (explode("-", $input["basket_id"]))[0];
$trigger["name"] = $_GET["action"];
unset($headers);
$headers[] = "Authorization: Bearer ".$ss_token;
$result["SmartSender"] = json_decode(send_request("https://api.smartsender.com/v1/contacts/".$userId."/fire", $headers, "POST", $trigger), true);
$log["response"] = $result;
send_request($logUrl, [], "POST", $log);
echo json_encode($result);












