<?php

require_once('Wfp.php');

$client_id = $_REQUEST['id'];
$domain = $_REQUEST['domain'];

if(empty($client_id) || empty($domain)){exit;}

$wfp = new Wfp();

$order_id = $wfp->newOrder($client_id);

?>

<!doctype html>
<html lang="ru">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.css">
    <script src="//api.bitrix24.com/api/v1/"></script>

    <title>Продление работы!</title>
  </head>
  <body>
      
        <header class='d-flex flex-row bg-info mb-2'>
            <div class="logo mt-3">
                <img class='logo_img' src="../img/logo.svg" width="200">
                <h4>to Bitrix24</h4>
            </div>
            <div class="errors_alert">
                <span id="alert"></span>
            </div> 

        </header>
      
        <h2 class="h2 text-center">Продление работы приложения Rozetka to Bitrix24!</h2>
        <p class="fs-5 text-center">Стоимость продления не зависит от колличества сайтов prom!</p>
        
        <hr>

        <div class="d-flex justify-content-between">
            
            <form method="post" action="https://secure.wayforpay.com/pay" accept-charset="utf-8">
                <?= $wfp->getForm($order_id, '200.00') ?>
                <input class="btn btn-success m-2" type="submit" value="200 грн / 30 дней">
            </form>

            <form method="post" action="https://secure.wayforpay.com/pay" accept-charset="utf-8">
                <?= $wfp->getForm($order_id, '500.00') ?>
                <input class="btn btn-success m-2" type="submit" value="500 грн / 90 дней">
            </form>

            <form method="post" action="https://secure.wayforpay.com/pay" accept-charset="utf-8">
                <?= $wfp->getForm($order_id, '900.00') ?>
                <input class="btn btn-success m-2" type="submit" value="900 грн / 180 дней">
            </form>
            
            <form method="post" action="https://secure.wayforpay.com/pay" accept-charset="utf-8">
                <?= $wfp->getForm($order_id, '1700.00') ?>
                <input class="btn btn-success m-2" type="submit" value="1700 грн / 360 дней">
            </form>
            
        </div>
        
        <hr>
        
        <input class="btn btn-dark m-3" type="submit" onclick="openApplication()" value="На главную..." />
        
           <hr>
    
    <footer>
    <a href='mailto:localtech.dev@gmail.com?subject="Поддержка пользователя"'>localtech.dev@gmail.com</a>
    <a href="https://t.me/localtech_dev" target="_blank"><img src="../img/telegram.png" alt=""></a>
</footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    
<script>
function openApplication() {
	if(BX24){
        BX24.closeApplication();
	    BX24.openApplication(); 
    } else {
        document.location.href = "http://localtech.kr.ua/home.html";
    }
}

</script>
  </body>
</html>