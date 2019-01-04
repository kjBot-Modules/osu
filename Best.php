<?php
namespace kjBotModule\kj415j45\osu;

use kjBot\Framework\Module;
use kjBot\Framework\Message;
use kjBot\Framework\DataStorage;
use kjBotModule\kj415j45\osu\API\V1;
use kjBot\Framework\Event\MessageEvent;
use kjBotModule\kj415j45\CoreModule\Access;
use kjBotModule\kj415j45\CoreModule\AccessLevel;


class Best extends Module{
    public function process(array $args, MessageEvent $event): Message{
        $api = new V1();
        $username = substr($event->__toString(), strlen($args[0])+1);
        if(\preg_match('/:(\d+)/', $event->__toString(), $match)){ //解析模式
            $mode = $match[1];
            $username = str_replace($match[0], '', $username);
        }
        if(\preg_match('/#(\d+)/', $event->__toString(), $match)){ //解析位置
            $index = $match[1];
            $username = str_replace($match[0], '', $username);
        }
        $username = trim($username);
        if(strpos($args[0], 'me') !== false){ //如果是在查自己的成绩则不询问参数
            $id = Bind::GetID($event->getId());
            if($id === NULL){
                q('请发送“绑定osu”进行绑定');
            }
            $data = $api->getUserBP($id, $index??1, ['m' => $mode??Bind::GetMode($event->getId())]);
            $data->username = Bind::GetUsername($event->getId());
        }else{
            if(!preg_match_all('/[A-Za-z0-9-]+/', $username)){
                q('请提供 osu!ID');
            }
            $user = $api->getUser($username)[0];
            if($user === NULL){
                q('指定的玩家不存在（或者被ban了');
            }
            $data = $api->getUserBP($user->user_id, $index??1, ['m' => $mode??Mode::osu]);
            if($data === NULL){
                q('玩家最近没有成绩');
            }
            $data->username = $user->username;
        }
        $score = new Score($data, $mode??Mode::osu);
        $score->draw()->save(DataStorage::$storagePath.'data/osu.score/'.$event->msgId.'.png');
        return $event->sendBack(\sendImg(DataStorage::GetData('osu.score/'.$event->msgId.'.png')));
    }
}