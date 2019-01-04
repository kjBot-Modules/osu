<?php
namespace kjBotModule\kj415j45\osu\API;

class OsuAPIException extends \Exception{
    public function __construct($code){
        parent::__construct('osu! API 异常：'.$code, $code);
    }
}
