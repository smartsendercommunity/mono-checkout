<?php

// Данные интеграции с MonoBank
$mono_token = "";
$ss_token = "";
$logUrl = "https://log.mufiksoft.com/mono-checkout";

// Сервисные данные
$dir = dirname($_SERVER["PHP_SELF"]);
$url = ((!empty($_SERVER["HTTPS"])) ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"] . $dir;
$url = explode("?", $url);
$url = $url[0];

function send_bearer($url, $token, $type = "GET", $param = []){
  $descriptor = curl_init($url);
  curl_setopt($descriptor, CURLOPT_POSTFIELDS, json_encode($param));
  curl_setopt($descriptor, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($descriptor, CURLOPT_HTTPHEADER, array("User-Agent: M-Soft Integration", "Content-Type: application/json", "Authorization: Bearer ".$token)); 
  curl_setopt($descriptor, CURLOPT_CUSTOMREQUEST, $type);
  $itog = curl_exec($descriptor);
  curl_close($descriptor);
  return $itog;
}

function send_request($url, $header = [], $type = "GET", $param = [], $raw = "json") {
  $descriptor = curl_init($url);
  if ($type != "GET") {
    if ($raw == "json") {
      curl_setopt($descriptor, CURLOPT_POSTFIELDS, json_encode($param));
      $header[] = "Content-Type: application/json";
    } else if ($raw == "form") {
      curl_setopt($descriptor, CURLOPT_POSTFIELDS, http_build_query($param));
      $header[] = "Content-Type: application/x-www-form-urlencoded";
    } else {
      curl_setopt($descriptor, CURLOPT_POSTFIELDS, $param);
    }
  }
  $header[] = "User-Agent: M-Soft Integration(https://mufiksoft.com)";
  curl_setopt($descriptor, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($descriptor, CURLOPT_HTTPHEADER, $header); 
  curl_setopt($descriptor, CURLOPT_CUSTOMREQUEST, $type);
  $itog = curl_exec($descriptor);
  curl_close($descriptor);
  return $itog;
}
