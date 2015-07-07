# CPF Grátis
[![Travis](https://travis-ci.org/jansenfelipe/cpf-gratis.svg?branch=2.0)](https://travis-ci.org/jansenfelipe/cpf-gratis)
[![Latest Stable Version](https://poser.pugx.org/jansenfelipe/cpf-gratis/v/stable.svg)](https://packagist.org/packages/jansenfelipe/cpf-gratis) [![Total Downloads](https://poser.pugx.org/jansenfelipe/cpf-gratis/downloads.svg)](https://packagist.org/packages/jansenfelipe/cpf-gratis) [![Latest Unstable Version](https://poser.pugx.org/jansenfelipe/cpf-gratis/v/unstable.svg)](https://packagist.org/packages/jansenfelipe/cpf-gratis) [![License](https://poser.pugx.org/jansenfelipe/cpf-gratis/license.svg)](https://packagist.org/packages/jansenfelipe/cpf-gratis)


Com esse pacote você poderá realizar consultas de CPF no site da Receita Federal do Brasil gratuitamente.

Atenção: Esse pacote não possui leitor de captcha, mas captura o mesmo para ser digitado pelo usuário

### Como usar

Adicione a library

    $ composer require jansenfelipe/cpf-gratis
    
Adicione o autoload.php do composer no seu arquivo PHP.

    require_once 'vendor/autoload.php';  

Primeiro chame o método `getParams()` para retornar os dados necessários para enviar no método `consulta()` 

    $params = JansenFelipe\CpfGratis\CpfGratis::getParams(); 

Agora basta chamar o método `consulta()`

    $dadosPessoa = JansenFelipe\CpfGratis\CpfGratis::consulta(
        'INFORME_O_CPF',
        'INFORME_A_DATA_DE_NASCIMENTO',
        'INFORME_AS_LETRAS_DO_CAPTCHA',
        $params['cookie']
    );
