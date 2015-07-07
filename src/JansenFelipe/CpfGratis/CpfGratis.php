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
     * @return array Link para ver o Captcha, Viewstate e Cookie
     */
    public static function getParams() {
        $client = new Client();

        $crawler = $client->request('GET', 'http://www.receita.fazenda.gov.br/aplicacoes/atcta/cpf/ConsultaPublica.asp');

        $response = $client->getResponse();

        $headers = $response->getHeaders();
        $cookie = $headers['Set-Cookie'][0];


        $ch = curl_init("http://www.receita.fazenda.gov.br/aplicacoes/atcta/cpf/captcha/gerarCaptcha.asp");
        $options = array(
            CURLOPT_COOKIEJAR => 'cookiejar',
            CURLOPT_HTTPHEADER => array(
                "Pragma: no-cache",
                "Origin: http://www.receita.fazenda.gov.br",
                "Host: www.receita.fazenda.gov.br",
                "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0",
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3",
                "Accept-Encoding: gzip, deflate",
                "Referer: http://www.receita.fazenda.gov.br/aplicacoes/atcta/cpf/ConsultaPublica.asp",
                "Cookie: $cookie",
                "Connection: keep-alive"
            ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_BINARYTRANSFER => TRUE
        );

        curl_setopt_array($ch, $options);
        $img = curl_exec($ch);
        curl_close($ch);

        $resource = curl_init('http://www.receita.fazenda.gov.br/aplicacoes/atcta/cpf/captcha/gerarSom.asp');
        curl_setopt_array($resource, $options);
        $file = curl_exec($resource);
        curl_close($resource);

        return array(
            'cookie' => $cookie,
            'audio' => $file,
            'captchaBase64' => 'data:image/png;base64,' . base64_encode($img)
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
    public static function consulta($cpf, $nascimento, $captcha, $stringCookie) {
        try {
            $arrayCookie = explode(';', $stringCookie);

            if (!Utils::isCpf($cpf))
                throw new Exception();

            $client = new Client();
            $client->setHeader('Host', 'www.receita.fazenda.gov.br');
            $client->setHeader('User-Agent', 'Mozilla/5.0 (Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0');
            $client->setHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
            $client->setHeader('Accept-Language', 'pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3');
            $client->setHeader('Accept-Encoding', 'gzip, deflate');
            $client->setHeader('Referer', 'http://www.receita.fazenda.gov.br/aplicacoes/atcta/cpf/ConsultaPublica.asp');
            $client->setHeader('Cookie', $arrayCookie[0]);
            $client->setHeader('Connection', 'keep-alive');

            $param = array(
                'tempTxtCPF' => Utils::unmask($cpf),
                'tempTxtNascimento' => $nascimento,
                'temptxtTexto_captcha_serpro_gov_br' => $captcha,
                'txtTexto_captcha_serpro_gov_br' => $captcha,
                'Enviar' => 'Consultar'
            );

            $crawler = $client->request('POST', 'http://www.receita.fazenda.gov.br/aplicacoes/atcta/cpf/ConsultaPublicaExibir.asp', $param);

            $clConteudoDados = $crawler->filter('span.clConteudoDados');

            return(array(
                'cpf' => Utils::unmask($cpf),
                'nome' => trim(str_replace('Nome da Pessoa Física: ', '', $clConteudoDados->eq(1)->filter('b')->html())),
                'nascimento' => trim(str_replace('Data de Nascimento: ', '', $clConteudoDados->eq(2)->filter('b')->html())),
                'situacao_cadastral' => str_replace('Situação Cadastral: ', '', $clConteudoDados->eq(3)->filter('b')->html()),
                'situacao_cadastral_data' => str_replace('Data da Inscrição: ', '', $clConteudoDados->eq(4)->filter('b')->html()),
                'digito_verificador' => str_replace('Digito Verificador: ', '', $clConteudoDados->eq(5)->filter('b')->html())
            ));
        } catch (Exception $e) {
            throw new Exception('Aconteceu um erro ao fazer a consulta. Envie os dados novamente.');
        }
    }

}