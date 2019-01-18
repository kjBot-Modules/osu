<?php
namespace kjBotModule\kj415j45\osu;

use kjBot\Framework\Module;
use kjBot\Framework\Message;
use kjBot\Framework\DataStorage;
use kjBot\Framework\Event\MessageEvent;

class SetMode extends Module{
    public function process(array $args, MessageEvent $event): Message{
        $bind = Bind::GetID($event->getId());
        if($bind !== NULL){
            if(DataStorage::SetData('osu.Bind.v1.mode/'.$event->getId(), Mode::Parse($args[1]??q('未指定模式'))??q('无法解析模式名')))
            return $event->sendBack('成功更改模式为 '.$args[1]);
            else q('更改模式失败，请联系master');
        }else{
            q('请先发送“绑定osu”以绑定');
        }
    }
}