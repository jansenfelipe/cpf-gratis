# CPF Grátis
[![Travis](https://travis-ci.org/jansenfelipe/cpf-gratis.svg?branch=2.0)](https://travis-ci.org/jansenfelipe/cpf-gratis)
[![Latest Stable Version](https://poser.pugx.org/jansenfelipe/cpf-gratis/v/stable.svg)](https://packagist.org/packages/jansenfelipe/cpf-gratis) 
[![Total Downloads](https://poser.pugx.org/jansenfelipe/cpf-gratis/downloads.svg)](https://packagist.org/packages/jansenfelipe/cpf-gratis) 
[![Latest Unstable Version](https://poser.pugx.org/jansenfelipe/cpf-gratis/v/unstable.svg)](https://packagist.org/packages/jansenfelipe/cpf-gratis)
[![MIT license](https://poser.pugx.org/jansenfelipe/nfephp-serialize/license.svg)](http://opensource.org/licenses/MIT)

Com esse pacote você poderá realizar consultas de CPF no site da Receita Federal do Brasil gratuitamente.

Atenção: Esse pacote não possui leitor de captcha, mas captura o mesmo para ser digitado pelo usuário

### Changelog

* 2.0.4 - 07/07/2015 Necessário informar a data de nascimento
* 2.0.3 - 21/01/2015 Retornar binário de áudio
* 2.0.2 - 29/12/2014 Remoção do token
* 2.0.1 - 19/11/2014 Bugfix. Set PHP >=5.4
* 2.0.0 - 18/11/2014 Alteração do site

### Como utilizar

Adicione a library

```sh
$ composer require jansenfelipe/cpf-gratis
```

Adicione o autoload.php do composer no seu arquivo PHP.

```php
require_once 'vendor/autoload.php';  
```

Primeiro chame o método `getParams()` para retornar os dados necessários para enviar no método `consulta()` 

```php
$params = JansenFelipe\CpfGratis\CpfGratis::getParams(); 
```

Agora basta chamar o método `consulta()`

```php
$dadosPessoa = JansenFelipe\CpfGratis\CpfGratis::consulta(
    'INFORME_O_CPF',
    'INFORME_A_DATA_DE_NASCIMENTO', //DDMMYYYY
    'INFORME_AS_LETRAS_DO_CAPTCHA',
    $params['cookie']
);
```
### Gostou? Conheça também

* [CnpjGratis](https://github.com/jansenfelipe/cnpj-gratis)
* [CepGratis](https://github.com/jansenfelipe/cep-gratis)
* [Nfephp-serialize](https://github.com/jansenfelipe/nfephp-serialize)

### License

The MIT License (MIT)
