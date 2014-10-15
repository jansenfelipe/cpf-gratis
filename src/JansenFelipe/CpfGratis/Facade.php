<?php

namespace JansenFelipe\CnpjGratis;

class Facade extends \Illuminate\Support\Facades\Facade {

    protected static function getFacadeAccessor() {
        return 'cnpj_gratis';
    }

}
