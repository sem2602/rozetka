<?php

require_once 'services/rest.php';
require_once 'services/Rozetka.php';

$clients = Rest::getClients();

foreach ($clients as $client){

    Rest::$ID = $client['id'];
    $r_auth = json_decode($client['r_auth'], 1);
    $rozetka = new Rozetka($r_auth['username'], $r_auth['password']);

    if(empty($r_auth['token'])){
        $token = $rozetka->getToken();
        if($token['success']){
            $r_auth['token'] = $token['content']['access_token'];
            Rest::updateRAuth($r_auth);
        } else {
            file_put_contents('errors/getToken_' . $client['id'] . '.txt', print_r($token, true));
            continue;
        }
        
    }
    
    $currentToken = $rozetka->checkToken($r_auth['token']);
    
    if(!$currentToken){
        $token = $rozetka->getToken();
        if($token['success']){
            $r_auth['token'] = $token['content']['access_token'];
            Rest::updateRAuth($r_auth);
        } else {
            file_put_contents('errors/checkToken_' . $client['id'] . '.txt', print_r($token, true));
            continue;
        }
    }
    
    $orders = $rozetka->getPendingOrders();
    
    // echo '<pre>';
    // var_dump($orders);
    // exit;
  
    if(!empty($orders['content']['orders'])){
        
        foreach ($orders['content']['orders'] as $order){
            
            $check = Rest::checkOrderLog($order['id']);
   
            if(!$check){
                
                $lead = Rest::createLead($order, $client['site'], $client['user_id']);

                $products = Rest::setLeadProducts($order['items_photos'], $lead);
     
                if($lead){
                    $res = Rest::setOrderLog($order['id'], $lead);
                }
                
            }
            
        }
        
    }
 
}

?>