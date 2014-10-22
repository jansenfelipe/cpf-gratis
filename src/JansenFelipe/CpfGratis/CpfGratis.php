<?php

namespace JansenFelipe\CpfGratis;

use JansenFelipe\Utils\Utils as Utils;

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
        $ch = curl_init('http://www.receita.fazenda.gov.br/aplicacoes/atcta/cpf/ConsultaPublica.asp');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        $out = preg_split("|(?:\r?\n){1}|m", $header);

        foreach ($out as $line) {
            @list($key, $val) = explode(": ", $line, 2);
            if ($val != null) {
                if (!array_key_exists($key, $headers))
                    $headers[$key] = trim($val);
            } else
                $headers[] = $key;
        }
        
        if (!method_exists('phpQuery', 'newDocumentHTML'))
            require_once __DIR__ . DIRECTORY_SEPARATOR . 'phpQuery-onefile.php';
        
        \phpQuery::newDocumentHTML($body, $charset = 'utf-8');

        $viewstate = \phpQuery::pq("#viewstate")->val();

        if ($viewstate == "")
            throw new Exception('Erro ao recuperar viewstate');

        $imgcaptcha = \phpQuery::pq("#imgcaptcha")->attr('src');
        $urlCaptcha = 'http://www.receita.fazenda.gov.br' . $imgcaptcha;

        $captchaBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($urlCaptcha));
        
        return array(
            'captcha' => $urlCaptcha,
            'captchaBase64' => $captchaBase64,
            'viewstate' => $viewstate,
            'cookie' => $headers['Set-Cookie']
        );
    }

    /**
     * Metodo para realizar a consulta
     *
     * @param  string $cpf CPF
     * @param  string $captcha CAPTCHA
     * @param  string $viewstate VIEWSTATE
     * @param  string $stringCookie COOKIE
     * @throws Exception
     * @return array  Dados da pessoa
     */
    public static function consulta($cpf, $captcha, $viewstate, $stringCookie) {
        $arrayCookie = explode(';', $stringCookie);

        if (!Utils::isCpf($cpf))
            throw new \Exception('O CPF informado não é válido');

        $ch = curl_init("http://www.receita.fazenda.gov.br/aplicacoes/atcta/cpf/ConsultaPublicaExibir.asp");

        $param = array(
            'viewstate' => $viewstate,
            'captcha' => $captcha,
            'captchaAudio' => '',
            'Enviar' => 'Consultar',
            'txtCPF' => Utils::unmask($cpf)
        );

        $options = array(
            CURLOPT_COOKIEJAR => 'cookiejar',
            CURLOPT_HTTPHEADER => array(
                "Host: www.receita.fazenda.gov.br",
                "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0",
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                "Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3",
                "Accept-Encoding: gzip, deflate",
                "Referer: http://www.receita.fazenda.gov.br/aplicacoes/atcta/cpf/ConsultaPublica.asp",
                "Cookie: ' . $arrayCookie[0] . '",
                "Connection: keep-alive"
            ),
            CURLOPT_POSTFIELDS => http_build_query($param),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => 1
        );

        curl_setopt_array($ch, $options);
        $html = curl_exec($ch);
        curl_close($ch);

        if (!method_exists('phpQuery', 'newDocumentHTML'))
            require_once __DIR__ . DIRECTORY_SEPARATOR . 'phpQuery-onefile.php';

        \phpQuery::newDocumentHTML($html, $charset = 'utf-8');

        $class = pq('#F_Consultar > div > div.caixaConteudo > div > div:nth-child(3) > p > span.clConteudoDados');

        $result = array();
        foreach ($class as $clConteudoDados)
            $result[] = trim(pq($clConteudoDados)->html());

        if (isset($result[0])) {
            $result[0] = str_replace('N<sup>o</sup> do CPF: ', '', $result[0]);

            if (!Utils::isCpf($result[0]))
                throw new \Exception('O CPF informado não é válido');

            return(array(
                'cnpj' => Utils::unmask($result[0]),
                'nome' => str_replace('Nome da Pessoa Física: ', '', $result[1]),
                'situacao_cadastral' => str_replace('Situação Cadastral: ', '', $result[2]),
                'digito_verificador' => str_replace('Digito Verificador: ', '', $result[3])
            ));
        } else
            throw new \Exception('Aconteceu um erro ao fazer a consulta. Envie os dados novamente.');
    }

}
