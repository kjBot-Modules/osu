<?php
namespace kjBotModule\kj415j45\osu;

use kjBot\Framework\Module;
use kjBot\Framework\Message;
use kjBot\Framework\DataStorage;
use kjBotModule\kj415j45\osu\API\V1;
use kjBot\Framework\Event\MessageEvent;

class Recent extends Module{
    public function process(array $args, MessageEvent $event): Message{
        $api = new V1();
        $username = substr($event->__toString(), strlen($args[0])+1);
        if(\preg_match('/[:：](\d+)/', $event->__toString(), $match)){ //解析模式
            $mode = $match[1];
            $username = str_replace($match[0], '', $username);
        }
        if(\preg_match('/#(\d+)/', $event->__toString(), $match)){ //解析位置
            $index = $match[1];
            $username = str_replace($match[0], '', $username);
        }

        if(preg_match_all('/[A-Za-z0-9-]+/', $username)){
            $user = $api->getUser($username)[0]??q('指定的玩家不存在（或者被ban了');
            $data = $api->getUserRecent($username, ['m' => $mode??Mode::osu])[$index??0]??q('玩家最近没有成绩');
            $data->username = $user->username;
        }else{
            $id = Bind::GetID($event->getId());
            $data = $api->getUserRecentById($id??q('未提供查询目标，若需要绑定可以发送“绑定osu”'), ['m' => $mode??Bind::GetMode($event->getId())])[$index??0]??q('玩家最近没有成绩');
            $data->username = Bind::GetUsername($event->getId());
        }
        $score = new Score($data, $mode??Mode::osu);
        $score->draw()->save(DataStorage::$storagePath.'data/osu.score/'.$event->msgId.'.png');
        return $event->sendBack(\sendImg(DataStorage::GetData('osu.score/'.$event->msgId.'.png')));
    }
}