<?php

namespace JansenFelipe\CpfGratis;

class Facade extends \Illuminate\Support\Facades\Facade {

    protected static function getFacadeAccessor() {
        return 'cpf_gratis';
    }

}
