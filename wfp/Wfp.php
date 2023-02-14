<?php

use PDO;

require_once('../config.php');


class Wfp {
    
    private $pdo;
    private $settings;
    private $time;
    
    public function __construct ()
    {
        $this->pdo = new PDO('mysql:host='.SERVERNAME.';dbname='.DBNAME, USERNAME, PASSWORD);
        $this->settings = WFP;
        $this->time = strtotime(date('Y-m-d H:i:s'));
    }
    
    public function newOrder($client_id)
    {
        $sql = 'INSERT INTO `wfp` (`client_id`, `status`) VALUES (:client_id, :status)';
        $params = [
            ':client_id' => $client_id,
            ':status' => 'new'
            ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }
    
    public function getForm($order_id, $price){
        
        $sing = $this->getSign($order_id, $price);
        
        $str = '<input type="hidden" name="serviceUrl" value="'.$this->settings['serviceUrl'].'"/>';
        $str .= '<input type="hidden" name="merchantAccount" value="'.$this->settings['merchantAccount'].'"/>';
        $str .= '<input type="hidden" name="merchantAuthType" value="'.$this->settings['merchantAuthType'].'"/>';
        $str .= '<input type="hidden" name="merchantDomainName" value="'.$this->settings['merchantDomainName'].'"/>';
        $str .= '<input type="hidden" name="orderReference" value="'.$order_id.'"/>';
        $str .= '<input type="hidden" name="orderDate" value="'.$this->time.'"/>';
        $str .= '<input type="hidden" name="amount" value="'.$price.'"/>';
        $str .= '<input type="hidden" name="currency" value="UAH"/>';
        $str .= '<input type="hidden" name="productName[]" value="'.$this->settings['productName'].'"/>';
        $str .= '<input type="hidden" name="productPrice[]" value="'.$price.'"/>';
        $str .= '<input type="hidden" name="productCount[]" value="1"/>';
        $str .= '<input type="hidden" name="merchantSignature" value="'.$sing.'"/>';
        
        return $str;
        
    }
    
    private function getSign($order_id, $price){
        
        $data = [
            "merchantAccount" => $this->settings['merchantAccount'],
    		"merchantDomainName" => $this->settings['merchantDomainName'],
    		"orderReference" => $order_id,
    		"orderDate" => $this->time,
    		"amount" => $price,
    		"currency" => "UAH",
    		"productName[]" => $this->settings['productName'],
    		"productCount[]" => "1",
    		"productPrice[]" => $price    
        ];
        
        return hash_hmac("md5", implode(';', $data), $this->settings['key']);
        
    }
    
}