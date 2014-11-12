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

        $clientId = \phpQuery::pq("div[data-clienteid]:first")->attr('data-clienteid');
        
        /*
         * Enviando post para obter base64 da imagem
         */    
        $ch = curl_init();
        ob_start();
        curl_setopt($ch,CURLOPT_URL, 'http://captcha2.servicoscorporativos.serpro.gov.br/captcha/1.0.0/imagem');
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $clientId);
        curl_setopt($ch, CURLOPT_ENCODING ,"");
        curl_exec($ch);
        $result = ob_get_contents();
        curl_close($ch);
        ob_end_clean();
        $result = explode('@', $result);

        if (sizeof($result) < 2){
            exit('Não foi possível obter o token, tente novamente');
        }
                        
        return array(
            'token'  => $result[0],
            'image'  => $result[1],
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
    public static function consulta($cpf, $token, $captcha, $stringCookie) {
        $arrayCookie = explode(';', $stringCookie);

        if (!Utils::isCpf($cpf))
            exit('O CPF informado não é válido');

        $ch = curl_init("http://www.receita.fazenda.gov.br/aplicacoes/atcta/cpf/ConsultaPublicaExibir.asp");

        $param = array(
            //'viewstate' => $viewstate,
            'txtToken_captcha_serpro_gov_br' => $token,
            'txtTexto_captcha_serpro_gov_br' => $captcha,
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
        $result = array();

        // Nome
        $nome = explode('Nome da Pessoa Física: ', utf8_encode($html));
        $nome = $nome[1];
        $nome = explode('</span>', $nome);
        $nome = $nome[0];
        $result['nome'] = $nome;

        // Situação
        $situacao = explode(' Cadastral: ', $html);
        $situacao = $situacao[1];
        $situacao = explode('</span>', $situacao);
        $situacao = $situacao[0];
        $result['situacao'] = $situacao;

        return $result;
    }

}
