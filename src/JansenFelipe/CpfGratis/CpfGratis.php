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
        /*
         * gets base64 image captcha
         */    
        $ch = curl_init();
        ob_start();
        curl_setopt($ch,CURLOPT_URL, 'http://www.receita.fazenda.gov.br/aplicacoes/atcta/cpf/captcha/gerarCaptcha.asp');
        curl_setopt($ch, CURLOPT_ENCODING ,"");
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $result = ob_get_contents();        
        curl_close($ch);
        $header = substr($result, 0, $header_size);
        ob_end_clean();

        /*
         * Gets cookie
         */        
        $stringCookie = explode('Set-Cookie: ', $header);
        $stringCookie = explode(';', $stringCookie[1]);
        $stringCookie = $stringCookie[0];

        $result = substr($result, $header_size);
        $result = base64_encode($result);
                        
        return array(
            'image'  => $result,
            'cookie' => $stringCookie
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
    public static function consulta($cpf, $captcha, $stringCookie) {
        
        if (!Utils::isCpf($cpf))
            exit('O CPF informado não é válido');

        $ch = curl_init("http://www.receita.fazenda.gov.br/aplicacoes/atcta/cpf/ConsultaPublicaExibir.asp");

        $param = array(
            //'viewstate' => $viewstate,
            //'txtToken_captcha_serpro_gov_br' => $token,
            'txtTexto_captcha_serpro_gov_br' => $captcha,
            'Enviar' => 'Consultar',
            'txtCPF' => Utils::unmask($cpf)
        );

        var_dump($stringCookie);

        $options = array(
            CURLOPT_COOKIEJAR => 'cookiejar',
            CURLOPT_HTTPHEADER => array(                
                "Referer: http://www.receita.fazenda.gov.br/aplicacoes/atcta/cpf/ConsultaPublica.asp",
                "Cookie: ".$stringCookie,
                "Connection: keep-alive",
                "Host: www.receita.fazenda.gov.br",
                "Origin: http://www.receita.fazenda.gov.br",
                "Cache-Control: max-age=0",
                "Content-Type: application/x-www-form-urlencoded"
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
