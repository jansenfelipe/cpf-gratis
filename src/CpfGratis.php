<?php

namespace JansenFelipe\CpfGratis;

use Exception;
use Goutte\Client;
use JansenFelipe\Utils\Utils;
use Symfony\Component\DomCrawler\Crawler;

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

        $client->setHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8');
        $client->setHeader('Accept-Encoding', 'gzip, deflate, sdch');
        $client->setHeader('Accept-Language', 'pt-BR,pt;q=0.8,en-US;q=0.6,en;q=0.4,es;q=0.2,it;q=0.2');
        $client->setHeader('Cache-Control', 'max-age=0');
        $client->setHeader('Connection', 'keep-alive');
        $client->setHeader('Host', 'cpf.receita.fazenda.gov.br');
        $client->setHeader('Upgrade-Insecure-Requests', '1');
        $client->setHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36');

        $crawler = $client->request('GET', 'http://cpf.receita.fazenda.gov.br/situacao');

        $payload = $crawler->filter('#idCaptchaInput')->attr('data-clienteid');

        $internal_cookies = $client->getCookieJar()->all()[0];
        $cookie = $internal_cookies->getName().'='.$internal_cookies->getValue(). '; path='. $internal_cookies->getPath();

        $client->request('POST', 'http://captcha2.servicoscorporativos.serpro.gov.br/captcha/1.0.0/imagem', [], [], [], $payload);

        $response = explode('@', $client->getResponse()->getContent());

        return array(
            'cookie' => $cookie,
            'captchaToken' => $response[0],
            'captchaBase64' => 'data:image/png;base64,' . $response[1]
        );
    }

    /**
     * Metodo para realizar a consulta
     *
     * @param  string $cpf CPF
     * @param  string $nascimento NASCIMENTO (DDMMYYYY)
     * @param  string $captcha CAPTCHA
     * @param  string $stringCookie COOKIE
     * @param  string $token CAPTCHA TOKEN
     * @throws Exception
     * @return array  Dados da pessoa
     */
    public static function consulta($cpf, $nascimento, $captcha, $stringCookie, $token)
    {
        $arrayCookie = explode(';', $stringCookie);

        if (!Utils::isCpf($cpf))
            throw new Exception("CPF inválido");

        $client = new Client(['allow_redirects' => false]);

        $client->setHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8');
        $client->setHeader('Accept-Encoding', 'gzip, deflate, sdch');
        $client->setHeader('Accept-Language', 'pt-BR,pt;q=0.8,en-US;q=0.6,en;q=0.4,es;q=0.2,it;q=0.2');
        $client->setHeader('Cache-Control', 'max-age=0');
        $client->setHeader('Connection', 'keep-alive');
        $client->setHeader('Cookie', $arrayCookie[0]);
        $client->setHeader('Host', 'cpf.receita.fazenda.gov.br');
        $client->setHeader('Origin', 'http://cpf.receita.fazenda.gov.br');
        $client->setHeader('Referer', 'http://cpf.receita.fazenda.gov.br/situacao/');
        $client->setHeader('Upgrade-Insecure-Requests', '1');
        $client->setHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36');

        $param = array(
            'txtToken_captcha_serpro_gov_br' => $token,
            'txtTexto_captcha_serpro_gov_br' => $captcha,
            'txtCPF' => $cpf,
            'txtDataNascimento' => $nascimento
        );

        $crawler = $client->request('POST', 'http://cpf.receita.fazenda.gov.br/situacao/ConsultaSituacao.asp', $param);

        /*
         * Verificando erros
         */
        $idMessageError = $crawler->filter('#idMessageError');

        if($idMessageError->count() > 0)
            throw new Exception(trim($idMessageError->html()));

        $clConteudoCompBold = $crawler->filter('span.clConteudoCompBold');

        if($clConteudoCompBold->count() > 0)
            throw new Exception(trim($clConteudoCompBold->html()));

        /*
         * Buscando dados
         */
        $nome = $crawler->filter('#idCnt05 span.clBold')->html();
        $nascimento = $crawler->filter('#idCnt13 span.clBold')->html();
        $situacao_cadastral = $crawler->filter('#idCnt06 span.clBold')->html();
        $situacao_cadastral_data = $crawler->filter('#idCnt14 span.clBold')->html();
        $digito_verificador = $crawler->filter('#idCnt07 span.clBold')->html();

        $hora_emissao = $crawler->filter('#idCnt08 span.clBold')->eq(0)->html();
        $data_emissao = $crawler->filter('#idCnt08 span.clBold')->eq(1)->html();
        $codigo_controle = $crawler->filter('#idCnt09 span.clBold')->html();

        return compact('nome', 'nascimento', 'situacao_cadastral', 'situacao_cadastral_data', 'digito_verificador', 'hora_emissao', 'data_emissao', 'codigo_controle');
    }

}
