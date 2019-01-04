<?php
namespace kjBotModule\kj415j45\osu;

use kjBot\Framework\Module;
use kjBot\Framework\Message;
use kjBot\Framework\DataStorage;
use kjBotModule\kj415j45\osu\API\V1;
use kjBot\Framework\Event\MessageEvent;
use kjBotModule\kj415j45\CoreModule\Access;
use kjBotModule\kj415j45\CoreModule\AccessLevel;

class Recent extends Module{
    public function process(array $args, MessageEvent $event): Message{
        Access::Control($event)->requireLevel(AccessLevel::User);

        $api = new V1();
        if(\preg_match('/:(\d+)/', $event->__toString(), $match)){ //解析模式
            $mode = $match[1];
        }

        if(isset($args[1])){
            $user = $api->getUser($args[1])[0];
            if($user === NULL){
                q('指定的玩家不存在（或者被ban了');
            }
            $data = $api->getUserRecent($args[1], ['m' => $mode??Mode::osu])[0];
            if($data === NULL){
                q('玩家最近没有成绩');
            }
            $data->username = $user->username;
        }else{
            $id = Bind::GetID($event->getId());
            if($id === false){
                q('未提供查询目标，若需要绑定可以发送“绑定osu”');
            }
            $data = $api->getUserRecentById($id, ['m' => $mode??Bind::GetMode($event->getId())])[0];
            $data->username = Bind::GetUsername($event->getId());
        }
        $score = new Score($data, $mode??Mode::osu);
        $score->draw()->save(DataStorage::$storagePath.'data/osu.score/'.$event->msgId.'.png');
        return $event->sendBack(\sendImg(DataStorage::GetData('osu.score/'.$event->msgId.'.png')));
    }
}