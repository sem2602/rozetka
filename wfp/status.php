<?php

require_once('../config.php');

$json = file_get_contents('php://input');
$obj = json_decode($json, true);

file_put_contents(__DIR__ . '/RESPONSE_'. $obj["orderReference"] .'.txt', print_r($obj, true));


$responce = [
    'orderReference' => $obj['orderReference'],
    'status' => 'accept',
    'time' => strtotime(date('Y-m-d H:i:s')),
    ];

$sign = getSign($responce, WPF['key']);
$responce['signature'] = $sign;

//file_put_contents(__DIR__ . '/ODD.txt', print_r($responce, true));
    
function getSign($dataSet, $key){
    $signString = implode(';', $dataSet); // конкатенируем значения через символ ";"
    $sign = hash_hmac("md5",$signString,$key);
    return $sign; // возвращаем результат
}


if($obj['transactionStatus'] != 'Approved'){
    echo json_encode($responce);
    exit;
}


$order_id = $obj['orderReference'];
$amount = $obj['amount'];
$status = $obj['transactionStatus'];
    
    
try {
    $pdo = new PDO('mysql:host='.SERVERNAME.';dbname='.DBNAME, USERNAME, PASSWORD);
    
    $stmt = $pdo->prepare("SELECT client_id FROM wfp WHERE id = :id");
    $params = [':id' => $order_id];
    $stmt->execute($params);
    $client_id = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT status FROM wfp WHERE id = :id");
    $params = [':id' => $order_id];
    $stmt->execute($params);
    $old_status = $stmt->fetchColumn();
    if($old_status == 'Approved'){
        $pdo = NULL;
        exit;
    }
  
    $stmt = $pdo->prepare("SELECT payed FROM users WHERE id = :id");
    $params = [':id' => $client_id];
    $stmt->execute($params);
    $oldDate = $stmt->fetchColumn();
    
    if(strtotime(date('Y-m-d H:i:s')) > strtotime($oldDate)){
        $oldDate = date('Y-m-d H:i:s');
    }
    
    
    switch ($amount) {
    
    case '200':
        $oldDate = strtotime($oldDate." +30 days");
        break;
    case '500':
        $oldDate = strtotime($oldDate." +90 days");
        break;
    case '900':
        $oldDate = strtotime($oldDate." +180 days");
        break;
    case '1700':
        $oldDate = strtotime($oldDate." +360 days");
        break;
    
    default:
        // code...
        break;
}
    
    $newDate = date('Y-m-d H:i:s', $oldDate);
    
    $stmt = $pdo->prepare("UPDATE users SET payed = :payed WHERE id = :id");
    $params = [
        ':id' => $client_id,
        ':payed' => $newDate,
        ];
    $stmt->execute($params);
    
    
    $stmt = $pdo->prepare("UPDATE wfp SET amount = :amount, status = :status, updated = :updated WHERE id = :id");
    $params = [
        ':id' => $order_id,
        ':amount' => $amount,
        ':status' => $status,
        ':updated' => date('Y-m-d H:i:s')
        ];
    $result = $stmt->execute($params);
 
    if($result){
        echo json_encode($responce);
    }
    
    
    $pdo = NULL;
  
} catch (PDOException $e) {
    file_put_contents(__DIR__ . '/ERROR_SQL.txt', print_r($e->getMessage(), true));
    die();
}
