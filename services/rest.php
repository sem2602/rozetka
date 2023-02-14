<?php

require_once(__DIR__ . '/../config.php');
require_once 'Bitrix24.php';

class Rest extends Bitrix24 {
    
    public static $pdo;
    public static $ID;

    
    //override method
    protected static function getSettingData() {

        $sql = "SELECT client_id, client_secret, auth FROM users WHERE id = :id";
        $stmt = self::$pdo->prepare($sql);
        $params = [
            ':id' => self::$ID
        ];
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
 
        $return = json_decode($result['auth'], true);
		$return['C_REST_CLIENT_ID'] = $result['client_id'];
		$return['C_REST_CLIENT_SECRET'] = $result['client_secret'];

		return $return;
	}
	
	//override method
	protected static function setSettingData($auth) {
	    $auth = json_encode($auth);
        $sql = "UPDATE users SET auth = :auth WHERE id = :id";
        $stmt = self::$pdo->prepare($sql);
        $params = [
            ':auth' => $auth,
            ':id' => self::$ID
        ];
		return $stmt->execute($params);
	}
	
	public static function updateSettingData($auth){
	    
	    return static::setSettingData($auth);
	    
	}
	
	public static function getClients(){
	    
	    $sql = "SELECT * FROM users";
        $stmt = Rest::$pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
	    
	}
	
	public static function getClient($domain){
	    
	    $sql = "SELECT * FROM users WHERE domain = :domain";
        $stmt = self::$pdo->prepare($sql);
        $params = [
            ':domain' => $domain
        ];
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
	    
	}
	
	public static function getClientById(){
	    
	    $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = self::$pdo->prepare($sql);
        $params = [
            ':id' => self::$ID
        ];
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
	    
	}
	
	public static function updateClientData($data){
	    
	    $sql = "UPDATE users SET user_id = :user_id, site = :site WHERE id = :id";
        $stmt = self::$pdo->prepare($sql);
        $params = [
            ':id' => self::$ID,
            ':user_id' => $data['user_id'],
            ':site' => $data['site'],
        ];
        return $stmt->execute($params);
	    
	}
	
	public static function updateRAuth($r_auth){
	    
	    $sql = "UPDATE users SET r_auth = :r_auth WHERE id = :id";
        $stmt = self::$pdo->prepare($sql);
        $params = [
            ':id' => self::$ID,
            ':r_auth' => json_encode($r_auth)
        ];
        return $stmt->execute($params);
	    
	}
	
	public static function addClient($data){
	    
    	$payDate = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . " +6 days"));
	    
	    $sql = "INSERT INTO users (client_id, client_secret, auth, r_auth, user_id, site, domain, payed) VALUES (:client_id, :client_secret, :auth, :r_auth, :user_id, :site, :domain, :payed)";
        $stmt = self::$pdo->prepare($sql);
        $params = [
            ':client_id' => $data['client_id'],
            ':client_secret' => $data['client_secret'],
            ':auth' => json_encode($data['auth']),
            ':r_auth' => json_encode(['username' => $data['username'], 'password' => base64_encode($data['password'])]),
            ':user_id' => $data['user_id'],
            ':site' => $data['site_name'],
            ':domain' => $data['auth']['domain'],
            ':payed' => $payDate
        ];
        return $stmt->execute($params);
	}
	
	public static function checkContact($phone){
	    $resultContact = self::call("crm.contact.list",['filter' => ["PHONE" => $phone ], 'select' => [ "ID", "NAME", "LAST_NAME"]]);

		$resultContact2 = self::call("crm.contact.list", ['filter' => [ "PHONE" =>  substr($phone, 1)], 'select' => [ "ID", "NAME", "LAST_NAME" ]]);

		if (!empty($resultContact['result'])) {
			$contactID = $resultContact['result'][0]['ID'];
		} elseif (!empty($resultContact2['result'])) {
			$contactID = $resultContact2['result'][0]['ID'];
		} else {
		    $contactID = false;
		}
		
		return $contactID;
	}
	
	public static function createLead($order, $site, $user_id){
	    
	    $leadId = self::call('crm.lead.add',[
         	'fields' => [
        		"TITLE" => $site . '#' . $order['id'],
        		"NAME" => $order['user_title']['first_name'], 
                "SECOND_NAME" => $order['user_title']['last_name'], 
                "LAST_NAME" => $order['user_title']['second_name'],
        		//"CONTACT_ID" => 2,
        		"STATUS_ID" => "NEW",
        		"COMMENTS" => $order['comment'],
        		"OPENED" => "Y",
        		"ASSIGNED_BY_ID" => $user_id,
        		"CURRENCY_ID" => "UAH", 
                "OPPORTUNITY" => (float)$order['cost'],
                "PHONE" => [ [ "VALUE" => $order['user_phone'], "VALUE_TYPE" => "WORK" ] ]
        	], 'params' => ["REGISTER_SONET_EVENT" => "Y"],
        ]);
        
        return $leadId['result'];
	    
	}
	
	public static function setLeadProducts($products, $leadId){
	    
	    foreach ($products as $product){
	        $arr[] = ["PRODUCT_ID" => NULL, "PRODUCT_NAME" => $product['item_name'], "PRICE" => $product['item_price'], "QUANTITY" => 1];
	    }
	    
	    return self::call('crm.lead.productrows.set', ['id' => $leadId, 'rows' => $arr]);
	    
	}
	
	public static function checkOrderLog($order_id){
	    $sql = "SELECT * FROM orders WHERE client_id = :client_id AND order_id = :order_id";
        $stmt = self::$pdo->prepare($sql);
        $params = [
            ':client_id' => self::$ID,
            ':order_id' => $order_id,
        ];
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	public static function setOrderLog($order_id, $lead_id){
	    $sql = "INSERT INTO orders (client_id, order_id, bitrix_order_id) VALUES (:client_id, :order_id, :bitrix_order_id)";
        $stmt = self::$pdo->prepare($sql);
        $params = [
            ':client_id' => self::$ID,
            ':order_id' => $order_id,
            ':bitrix_order_id' => $lead_id,
        ];
        return $stmt->execute($params);
	}
	
// 	public static function updateDataTime($datetime){
	    
// 	    $datetime = json_encode($datetime);
// 	    $sql = "UPDATE marketplace SET DATETIME_UPDATE=:DATETIME_UPDATE WHERE ID=:ID";
//         $stmt = Rest::$pdo->prepare($sql);
//         $params = [
//             ':DATETIME_UPDATE' => $datetime,
//             ':ID' => Rest::$ID
//         ];
//         $stmt->execute($params);
	    
// 	}
	
	public function getUsersList($auth){
	    
	    $resultUser = self::query('user.get', ['sort' => 'ID', 'order' => 'ASC'], $auth);
        $userCount = $resultUser['total'];
    
    	if ($userCount >= 50) {
    
    	    for ($y = 0; $y < 50; $y++) {
    			$fullName = $resultUser['result'][$y]["NAME"].'&nbsp;'.$resultUser['result'][$y]["LAST_NAME"];
    			if ($fullName === '&nbsp;') {
    				$fullName = $resultUser['result'][$y]["EMAIL"];
    			}
    			$userOutputID = $resultUser['result'][$y]["ID"];
    			$userOutput .= '<option value="'.$userOutputID.'">
    			'. $fullName .'</option>';
    		}
    
        	$count_x = intdiv($userCount, 50);
        	$start = 50;
        	$x = 0;
        	
    	    while ($x < $count_x) {
    	        
        		$resultUser = self::query('user.get', ['sort' => 'ID', 'order' => 'ASC', 'start' => $start], $auth);
        
        		for ($q = 0; $q < 50; $q++) {
        			$t_fullName = $resultUser['result'][$q]["NAME"].'&nbsp;'.$resultUser['result'][$q]["LAST_NAME"];
        			if ($t_fullName === '&nbsp;') {
        				$t_fullName = $resultUser['result'][$q]["EMAIL"];
        			}
        			$t_userOutputID = $resultUser['result'][$q]["ID"];
        			$userOutput .= '<option value="'.$t_userOutputID.'">
        			'. $t_fullName .'</option>';
    		    }
    
    
        		$start += 50;
        		$x++;
    	    }
    	    
        } else {
            
        	for ($y = 0; $y < $userCount; $y++) {
        		$fullName = $resultUser['result'][$y]["NAME"].'&nbsp;'.$resultUser['result'][$y]["LAST_NAME"];
        		if ($fullName === '&nbsp;') {
        			$fullName = $resultUser['result'][$y]["EMAIL"];
        		}
        		$userOutputID = $resultUser['result'][$y]["ID"];
        		$userOutput .= '<option value="'.$userOutputID.'">
        		'. $fullName .'</option>';
        	}
        	
        }
        
        return $userOutput;
	}
	
	public function getCategoriesList($auth){
	    $arCategory = [];
    	$result = self::query('crm.dealcategory.list', [], $auth);
    	if (!empty($result['result']))	{
    		$totalCategory = $result['total'];
    		for ($xy = 0; $xy < $totalCategory; $xy++) {
    			if ($result['result'][$xy]['IS_LOCKED'] == 'N') {
    				$arCategory[$result['result'][$xy]['ID']] .= $result['result'][$xy]['NAME'];
    			}
    		}
    	}
    	$result = self::query('crm.dealcategory.default.get', [], $auth);
    	if (!empty($result['result']))	{
    		$arCategory[$result['result']['ID']] = $result['result']['NAME'];
    	}
    	ksort($arCategory);
    	foreach ($arCategory as $id_category => $name) {
    		if ($id_category > 0) {
    			$entity_id = 'DEAL_STAGE_' . $id_category;
    		} else {
    			$entity_id = 'DEAL_STAGE';
    		}
    		$categoryOutput .= '<option value="'.$id_category.'">'.$name.'</option>';
    		$resultDeal = self::query('crm.status.list', ['filter' => ['ENTITY_ID' => $entity_id]], $auth);
    		$output .= '<optgroup label="'.$name.'">';
    		for ($temp = 0; $temp < $resultDeal['total']; $temp++) {
    			$output .= '<option value="'.$resultDeal['result'][$temp]['STATUS_ID'].'">'.$resultDeal['result'][$temp]['NAME'].'</option>';
    		}
    		$output .= '</optgroup>';
    	}
    	
    	return $output;
	}
	
	public function getPayAlert($payed){
	    
	    $datediff = strtotime($payed) - strtotime(date('Y-m-d H:i:s'));
    	$datediff = round($datediff / (60 * 60 * 24)) + 1;

    	if(strtotime($payed) < strtotime(date('Y-m-d H:i:s'))){
    	    $alert_pay = '<div class="text-center ms-4"><p class="text-danger">Необходима оплата для дальнейшего использования приложения!!!</p>';
    	    $alert_pay .= '<div class="d-flex align-items-center ms-4"><span>Тестовый период закончился!</span>';
    	} elseif($datediff < 7){
    	    $alert_pay = '<div class="text-center ms-4"><p class="text-secondary">Заканчивается срок использования приложения!!! (кол. дней - <b>'.$datediff.'</b>)</p>';
    	    $alert_pay .= '<div class="d-flex align-items-center text-secondary  ms-4"><span>Активно до: '.$payed.'</span>';
    	} else {
    	    $alert_pay = '<div class="text-center ms-4"><div class="d-flex align-items-center text-success  ms-4"><span>Активно до: '.$payed.'</span>';
    	}
    	
    	return $alert_pay;
	}
	
	public function query($method, $params = [], $auth = []){
	    $queryUrl = "https://".$auth["domain"]."/rest/".$method;
    	$queryData = http_build_query(array_merge($params, array("auth" => $auth["access_token"])));
    	
    	$curl = curl_init();
    	
    	curl_setopt_array($curl, array(
    		CURLOPT_POST => 1,
    		CURLOPT_HEADER => 0,
    		CURLOPT_RETURNTRANSFER => 1,
    		CURLOPT_SSL_VERIFYPEER => 1,
    		CURLOPT_URL => $queryUrl,
    		CURLOPT_POSTFIELDS => $queryData,
    	));
    	
    	$result = curl_exec($curl);
    	curl_close($curl);
    	$result = json_decode($result, 1);
    	
    	return $result;
	}
    
}

Rest::$pdo = new PDO('mysql:host='.SERVERNAME.';dbname='. DBNAME, USERNAME, PASSWORD);