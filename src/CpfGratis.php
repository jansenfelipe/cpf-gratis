<?php

namespace JansenFelipe\CpfGratis;

use Exception;
use Goutte\Client;
use JansenFelipe\Utils\Utils;

class CpfGratis {

    /**
     * Metodo para capturar o captcha e viewstate para enviar no metodo
     * de consulta
     *
     * @param  string $cnpj CPF
     * @throws Exception
     * @return array Cookie e Captcha
     */
    public static function getParams()
    {
        $client = new Client();
        
        $client->getClient()->setDefaultOption('config/curl/'.CURLOPT_SSL_VERIFYPEER, false);
        $client->getClient()->setDefaultOption('config/curl/'.CURLOPT_SSL_VERIFYHOST, false);
        $client->getClient()->setDefaultOption('config/curl/'.CURLOPT_SSLVERSION, 3);

        $client->request('GET', 'https://www.receita.fazenda.gov.br/Aplicacoes/SSL/ATCTA/CPF/ConsultaPublica.asp');
        
        $internal_cookies = $client->getCookieJar()->all()[0];
        $cookie = $internal_cookies->getName().'='.$internal_cookies->getValue(). '; path='. $internal_cookies->getPath();

        $client->getClient()->setDefaultOption('config/curl/'.CURLOPT_BINARYTRANSFER, true);
        $client->request('GET', 'https://www.receita.fazenda.gov.br/Aplicacoes/SSL/ATCTA/CPF/captcha/gerarCaptcha.asp');

        $image = base64_encode($client->getResponse()->getContent());

        return array(
            'cookie' => $cookie,
            'captchaBase64' => 'data:image/png;base64,' . $image
        );
    }

    /**
     * Metodo para realizar a consulta
     *
     * @param  string $cpf CPF
     * @param  string $nascimento NASCIMENTO (DDMMYYYY)
     * @param  string $captcha CAPTCHA
     * @param  string $stringCookie COOKIE
     * @throws Exception
     * @return array  Dados da pessoa
     */
    public static function consulta($cpf, $nascimento, $captcha, $stringCookie)
    {
        $arrayCookie = explode(';', $stringCookie);

        if (!Utils::isCpf($cpf))
            throw new Exception("CPF inválido");

        $client = new Client();

        $client->getClient()->setDefaultOption('config/curl/'.CURLOPT_SSL_VERIFYPEER, false);
        $client->getClient()->setDefaultOption('config/curl/'.CURLOPT_SSL_VERIFYHOST, false);
        $client->getClient()->setDefaultOption('config/curl/'.CURLOPT_SSLVERSION, 3);

        $client->setHeader('Host', 'www.receita.fazenda.gov.br');
        $client->setHeader('User-Agent', 'Mozilla/5.0 (Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0');
        $client->setHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
        $client->setHeader('Accept-Language', 'pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3');
        $client->setHeader('Accept-Encoding', 'gzip, deflate');
        $client->setHeader('Referer', 'https://www.receita.fazenda.gov.br/Aplicacoes/SSL/ATCTA/CPF/ConsultaPublica.asp');
        $client->setHeader('Cookie', $arrayCookie[0]);
        $client->setHeader('Connection', 'keep-alive');

        $param = array(
            'txtTexto_captcha_serpro_gov_br' => $captcha,
            'tempTxtCPF' => $cpf,
            'tempTxtNascimento' => $nascimento,
            'temptxtToken_captcha_serpro_gov_br' => $captcha,
            'temptxtTexto_captcha_serpro_gov_br' => $captcha
        );

        $crawler = $client->request('POST', 'https://www.receita.fazenda.gov.br/Aplicacoes/SSL/ATCTA/CPF/ConsultaSituacao/ConsultaPublicaExibir.asp', $param);

        $error = $crawler->filter('span.mensagemErro');

        if($error->count() > 0)
            throw new Exception($error->html());

        $clConteudoDados = $crawler->filter('span.clConteudoDados');
        $clConteudoComp = $crawler->filter('span.clConteudoComp');

        return(array(
            'cpf' => Utils::unmask($cpf),
            'nome' => trim(str_replace('Nome da Pessoa Física: ', '', $clConteudoDados->eq(1)->filter('b')->html())),
            'nascimento' => trim(str_replace('Data de Nascimento: ', '', $clConteudoDados->eq(2)->filter('b')->html())),
            'situacao_cadastral' => str_replace('Situação Cadastral: ', '', $clConteudoDados->eq(3)->filter('b')->html()),
            'situacao_cadastral_data' => str_replace('Data da Inscrição: ', '', $clConteudoDados->eq(4)->filter('b')->html()),
            'digito_verificador' => str_replace('Digito Verificador: ', '', $clConteudoDados->eq(5)->filter('b')->html()),
            'hora_emissao' => str_replace('Hora de emissão: ', '', $clConteudoComp->eq(0)->filter('b')->first()->html()),
            'data_emissao' => str_replace('Data de emissão: ', '', $clConteudoComp->eq(0)->filter('b')->last()->html()),
            'codigo_controle' => str_replace('Código de controle: ', '', $clConteudoComp->eq(1)->filter('b')->html())
        ));
    }

}
