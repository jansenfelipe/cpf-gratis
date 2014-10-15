# CPF Grátis
[![Travis](https://travis-ci.org/jansenfelipe/cpf-gratis.svg?branch=1.0)](https://travis-ci.org/jansenfelipe/cpf-gratis)
[![Latest Stable Version](https://poser.pugx.org/jansenfelipe/cpf-gratis/v/stable.svg)](https://packagist.org/packages/jansenfelipe/cpf-gratis) [![Total Downloads](https://poser.pugx.org/jansenfelipe/cpf-gratis/downloads.svg)](https://packagist.org/packages/jansenfelipe/cpf-gratis) [![Latest Unstable Version](https://poser.pugx.org/jansenfelipe/cpf-gratis/v/unstable.svg)](https://packagist.org/packages/jansenfelipe/cpf-gratis) [![License](https://poser.pugx.org/jansenfelipe/cpf-gratis/license.svg)](https://packagist.org/packages/jansenfelipe/cpf-gratis)


Com esse pacote você poderá realizar consultas de CPF no site da Receita Federal do Brasil gratuitamente.

Atenção: Esse pacote não possui leitor de captcha, mas captura o mesmo para ser digitado pelo usuário

### Para utilizar

Adicione no seu arquivo `composer.json` o seguinte registro na chave `require`

    "jansenfelipe/cpf-gratis": "1.0.*@dev"

Execute

    $ composer update

## (Laravel)

Abra seu arquivo `config/app.php` e adicione `'JansenFelipe\CpfGratis\CpfGratisServiceProvider'` ao final do array `$providers`

    'providers' => array(

        'Illuminate\Foundation\Providers\ArtisanServiceProvider',
        'Illuminate\Auth\AuthServiceProvider',
        ...
        'JansenFelipe\CpfGratis\CpfGratisServiceProvider',
    ),

Adicione também `'CpfGratis' => 'JansenFelipe\CpfGratis\Facade'` no final do array `$aliases`

    'aliases' => array(

        'App'        => 'Illuminate\Support\Facades\App',
        'Artisan'    => 'Illuminate\Support\Facades\Artisan',
        ...
        'CpfGratis'    => 'JansenFelipe\CpfGratis\Facade',

    ),

Agora chame o método `getParams()` para retornas os dados necessários para enviar no método `consulta()` 

    $params = CpfGratis::getParams(); //Output: array('captcha', 'viewstate', 'cookie')

Obs: Na resposta, a chave `captcha` contém a URL da imagem.

    $dadosPessoa = CpfGratis::consulta(
        'INFORME_O_CPF',
        'INFORME_AS_LETRAS_DO_CAPTCHA',
        $params['viewstate'],
        $params['cookie']
    );


### (No-Laravel)

Adicione o autoload.php do composer no seu arquivo PHP.

    require_once 'vendor/autoload.php';  

Agora chame o método `getParams()` para retornas os dados necessários para enviar no método `consulta()` 

    $params = CpfGratis::getParams(); //Output: array('captcha', 'viewstate', 'cookie')

Obs: Na resposta, a chave `captcha` contém a URL da imagem.

    $dadosPessoa = CpfGratis::consulta(
        'INFORME_O_CPF',
        'INFORME_AS_LETRAS_DO_CAPTCHA',
        $params['viewstate'],
        $params['cookie']
    );
