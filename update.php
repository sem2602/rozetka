<?php

require_once 'services/rest.php';

if(!empty($_POST['id']) && !empty($_POST['user_id']) && !empty($_POST['site'])){
    Rest::$ID = $_POST['id'];
    $client = Rest::updateClientData($_POST);
}

header('Location: ./index.php');

?>