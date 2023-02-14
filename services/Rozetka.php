<?php

define('HOST', 'api-seller.rozetka.com.ua');

class Rozetka {
    
    private $token;
    private $login;
    private $password;
    
    function __construct($login, $password) {
        $this->login = $login;
        $this->password = $password;
    }
    
    public function setToken($token) {
        $this->token = $token;
    }
    
    public function getToken() {
        $url = '/sites';
        $body = [
            'username' => $this->login,
            'password' => $this->password
        ];
        return $this->request($url, $body);
    }
    
    public function checkToken($token) {
        $this->token = $token;
        $url = '/orders/counts-new';
        $data = $this->request($url);
        return $data['success'];
    }
    
    public function getPendingOrders() {
        $url = '/orders/search?status=1';// В обробці
        return $this->request($url);
    }
    
    public function getOrderData($id) {
        $url = '/orders/'. $id;
        return $this->request($url);
    }
    
    public function getCountOrders() {
        $url = '/orders/counts-new';// В обробці
        return $this->request($url);
    }
    
    private function request($url, $body = null) {
        $headers = array (
            'Authorization: Bearer ' . $this->token,
            'Content-Language: uk',
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . HOST . $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if (!empty($body)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    
    
    
}