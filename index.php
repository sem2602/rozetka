<?php

//require_once 'services/Rozetka.php';
require_once 'services/rest.php';

$domain = $_REQUEST['DOMAIN'];

$auth = [
            'access_token' => $_REQUEST['AUTH_ID'],
    		'expires_in' => $_REQUEST['AUTH_EXPIRES'],
    		'application_token' => $_REQUEST['APP_SID'],
    		'refresh_token' => $_REQUEST['REFRESH_ID'],
    		'domain' => $domain,
    		'client_endpoint' => 'https://' . $_REQUEST['DOMAIN'] . '/rest/',
		];
		

if ($domain && !empty($auth['access_token'])) {

    $client = Rest::getClient($domain);
    
    $userList = Rest::getUsersList($auth);

    if (!empty($client)) 
    {
    	
    	Rest::$ID = $client['id'];
    	
    	$alert_pay = Rest::getPayAlert($client['payed']);
    	
    	$siteName = $client['site'];
    	$userID = $client['user_id'];
    	
    	$resultUser = Rest::call('user.get', ['ID' => $userID]);
    	
    	$fullName = $resultUser['result'][0]["NAME"].'&nbsp;'.$resultUser['result'][0]["LAST_NAME"];
        if ($fullName === '&nbsp;') {$fullName = $resultUser['result'][0]["EMAIL"];}
    
    }

} 

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8"><title>Налаштування програми:</title>
		<link rel="stylesheet" href="./css/bootstrap.min.css">
        <link rel="stylesheet" href="./css/style.css">
    </head>
<body>
    
<header class='d-flex flex-row bg-info mb-2'>

    <div class="logo mt-3">
        <img class='logo_img' src="img/logo.svg" width="200">
        <h4>to Bitrix24</h4>
    </div>
    
    <?php if($client):?>
    
        <div class="w-100 d-flex justify-content-center align-items-center">
            <?=$alert_pay?>
            <form class="mb-0" action="wfp/form.php" method="POST">
    			<input type="hidden" name="id" value="<?=$client['id']?>">
    			<input type="hidden" name="domain" value="<?=$client['domain']?>">
          		<input class="btn btn-success ms-3" type="submit" name="button" value="Продлить..." />
      	    </form></div></div>
        </div>
        
    <?php endif; ?>

</header>
	
<div class="container max-width">
    
    <?php if($domain && !empty($auth['access_token'])): ?>
        <?php if(!empty($client)): ?>	
	
	    
	        <h3 class="mt-3">Поточні параметри:</h3>
	        
	        <p class="">Ім'я сайту: &nbsp; <span class="lead"><?= $client['site'] ?></span></p>
        	<p class="">Відповідальний за угоди: &nbsp; <span class="lead"><?= $fullName ?></span></p>
        	
        	<div class="d-flex">
           		
           		<button class="btn btn-info mt-2 me-4" type="button" data-bs-toggle="modal" data-bs-target="#changeModal">Змінити запис</button>
           		
            </div>
    		
     		<hr>
     		
     		<!-- Modal -->
            <div class="modal fade" id="changeModal" tabindex="-1" aria-labelledby="changeModal" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="changeModal">Зміна налаштувань</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  
                  <form action="update.php" method="POST">
                  
                      <div class="modal-body">
                        
                        <?php if($userList): ?>
                      	    <div class="form-floating mb-2">
                                <select class="form-select pb-2" name="user_id" id="userId">
                                    <option value="1" hidden=""></option>
                    		        <?= $userList ?>
                    		    </select>
                    		    <label for="userId">Відповідальний за угоди</label>
                            </div>
                      	<?php else: ?>
                      	    <div class="mb-2">
                    	        <input class="form-control user-input" type="text" name="user_id" id="name" placeholder="Введіть ID відповідального за угоди..."  />
                  	        </div>
                      	<?php endif; ?>
                    
                        <div class="form-floating mb-2">
                	  	    <input class="form-control" type="text" name="site" id="name" value="<?=$client['site']?>" required="Має бути заповненим!"/>
                	  	    <label for="name">Бажане ім'я сайту ROZETKA в системі Бітрікс24...</label>
                        </div>
                        
                      </div>
                      
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрити</button>
                        <button type="submit" class="btn btn-primary">Зберегти налаштування</button>
                      </div>
                      
                      <input type="hidden" name="id" value="<?=$client['id']?>">
                  
                  </form>
                  
                </div>
              </div>
            </div>
	    
	
    	<?php else: ?>
    	    <h3 class="mt-3">Необхідно налаштувати програму</h3>
           	<small id="output_alert"></small>
           	
           	<form action="reg_settings.php" method="POST">
           	
               	<div class="form-floating mb-2">
              	    <input class="form-control" id="clientId" type="text" name="client_id" required="Має бути заповненим!"/>
              	    <label for="clientId">CLIENT_ID...</label>
              	</div>
            
                <div class="form-floating mb-2">
                    <input class="form-control" id="clientSecret" type="text" name="client_secret" required="Має бути заповненим!"/>
                  	<label for="clientSecret">CLIENT_SECRET...</label>
              	</div>
               	
              	<div class="form-floating mb-2">    
                    <input class="form-control" type="text" name="username" required="Має бути заповненим!"/>
                    <label>Логін від кабінету продавця rozetka.com.ua...</label>
              	</div>
              	
              	<div class="form-floating mb-2">    
                    <input class="form-control" type="password" name="password" required="Має бути заповненим!"/>
                    <label>Пароль від кабінету продавця rozetka.com.ua...</label>
              	</div>
              	
              	<?php if($userList): ?>
              	    <div class="form-floating mb-2">
                        <select class="form-select pb-2" name="user_id" id="userId">
                            <option value="1" hidden=""></option>
            		        <?= $userList ?>
            		    </select>
            		    <label for="userId">Відповідальний за угоди</label>
                    </div>
              	<?php else: ?>
              	    <div class="mb-2">
            	        <input class="form-control user-input" type="text" name="user_id" id="name" placeholder="Введіть ID відповідального за угоди..."  />
          	        </div>
              	<?php endif; ?>
              	
           <!--   	<div class="form-floating mb-2">-->
           <!--         <select class="form-select" name="category_id" id="direction">-->
           <!--             <option value="0" hidden=""></option>-->
        			<!--    <?= $categoryList ?>-->
        			<!--</select>-->
        			<!--<label for="direction">Напрямок угод</label>-->
           <!--     </div>-->
                
                <div class="form-floating mb-2">
        	  	    <input class="form-control" type="text" name="site_name" id="name" required="Має бути заповненим!"/>
        	  	    <label for="name">Бажане ім'я сайту ROZETKA в системі Бітрікс24...</label>
                </div>
        	  	
        	 <!-- 	<div class="form-floating mb-2">-->
          <!--          <select class="form-select pb-2" name="deal_new_id" id="dealNew">-->
          <!--              <option value="NEW" hidden=""></option>-->
          <!--              <?= $categoryList ?>-->
        		<!--	</select>-->
        		<!--	<label for="dealNew">Стадія "тільки створених угод" в Бітрікс24 (зазвичай "Нова")</label>-->
        		<!--</div>-->
        		
        		<?php foreach($auth as $key => $item):?>
        		    <input type="text" name="auth[<?= $key ?>]" value="<?= $item ?>" hidden />
        		<?php endforeach; ?>
        		
        		<button class="btn btn-primary mt-4" type="submit">Зберегти налаштування</button>
              	
            </form>
    	<?php endif; ?>
    <?php endif;?>
		
	

</div>

<footer>
		<a href='mailto:localtech.dev@gmail.com?subject="Підтримка користувачів"'>localtech.dev@gmail.com</a>
        <a href="https://t.me/localtech_dev" target="_blank"><img src="./img/telegram.png" alt=""></a>
	</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>

<script>
async function sendRequest(url, body = null) {
    if (body) {
        let response = await fetch(url, {
            method: 'POST',
            body: JSON.stringify(body),
            headers: {"Content-type": "application/json"}
        });
        return await response.json();
    } else {
        let response = await fetch(url, {method: 'GET'});
        return await response.json();
    }
}


</script>
</body></html>