<?php

require_once '../vendor/autoload.php';

use JansenFelipe\CpfGratis\CpfGratis;

if(isset($_POST['captcha']) && isset($_POST['cookie']) && isset($_POST['token']) && isset($_POST['cpf']) && isset($_POST['data_nascimento'])){
    $dados = CpfGratis::consulta($_POST['cpf'], $_POST['data_nascimento'], $_POST['captcha'], $_POST['cookie'], $_POST['token']);
    var_dump($dados);
    die;
}else
    $params = CpfGratis::getParams();
?>

<img src="<?php echo $params['captchaBase64'] ?>" />

<form method="POST">
    <input type="hidden" name="cookie" value="<?php echo $params['cookie'] ?>" />
    <input type="hidden" name="token" value="<?php echo $params['captchaToken'] ?>" />
    
    <input type="text" name="captcha" placeholder="Captcha" />
    <input type="text" name="cpf" placeholder="CPF" />
    <input type="text" name="data_nascimento" placeholder="Nascimento (DDMMYYYY)" />
    
    <button type="submit">Consultar</button>
</form>