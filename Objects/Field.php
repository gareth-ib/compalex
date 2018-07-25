<?php

namespace compalex\Objects;

class Field {

    public $isNew       = false;
    public $dtype       = false;
    public $changeType  = false;

    public function __construct( $data = [] ) {

        foreach( $data as $k => $v ) {
            $this->$k = $v;
        }

    }

}