<?php

require_once(__DIR__ . '/../config.php');

use PDO;

class Model{
    
    public $pdo;
    
    function __construct (){
        $this->pdo = new PDO('mysql:host='.SERVERNAME.';dbname='. DBNAME, USERNAME, PASSWORD);
    }
    
    public function getClient($domain){
        
        $sql = 'SELECT * FROM users WHERE domain = :domain';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':domain', $domain);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    }
    
    public function setClient($data){
        $sql = 'INSERT INTO users (client_id, client_secret, auth, r_auth, domain) VALUES (:client_id, :client_secret, :auth, :r_auth, :domain)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':client_id', $data['client_id']);
        $stmt->bindParam(':client_secret', $data['client_secret']);
        $stmt->bindParam(':auth', json_encode($data['auth']));
        $stmt->bindParam(':r_auth', json_encode($data['r_auth']));
        $stmt->bindParam(':domain', $data['domain']);
        $stmt->execute();
    }
    
}