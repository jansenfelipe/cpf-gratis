<?php

namespace JansenFelipe\CpfGratis;

use PHPUnit_Framework_TestCase;

class CpfGratisTest extends PHPUnit_Framework_TestCase {

    private $params;

    public function testGetParams() {

        $this->params = CpfGratis::getParams();

        $this->assertEquals(true, isset($this->params['captchaBase64']));
        $this->assertEquals(true, isset($this->params['cookie']));
    }

}
