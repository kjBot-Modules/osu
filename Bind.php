<?php
namespace kjBotModule\kj415j45\osu;

use kjBot\Framework\Module;
use kjBot\Framework\Message;
use kjBot\Framework\DataStorage;
use kjBot\Framework\Event\MessageEvent;
use kjBotModule\kj415j45\osu\API\V1;
use kjBotModule\kj415j45\CoreModule\Access;
use kjBotModule\kj415j45\CoreModule\Session;
use kjBotModule\kj415j45\CoreModule\AccessLevel;

class Bind extends Module{

    public function process(array $args, MessageEvent $event): Message{
        return $event->sendBack(
            Session::Start($event->getId())
                   ->prompt('发送 osu!ID', __CLASS__, 'continue')
        );
    }

    public function continue(MessageEvent $event){
        $osuId = $event->getRawMsg();
        $api = new V1();
        $data = @$api->getUser($osuId)[0]??false;
        if($data){
            DataStorage::SetData('osu.Bind.v1.ID/'.$event->getId(), $data->user_id);
            DataStorage::SetData('osu.Bind.v1.username/'.$event->getId(), $data->username);
            DataStorage::SetData('osu.Bind.v1.mode/'.$event->getId(), Mode::osu);
            Session::Stop($event->getId());
            return $event->sendBack("成功绑定 {$osuId}({$data->user_id}) 到 {$event->getId()}");
        }else{
            Session::Stop($event->getId());
            q('绑定失败，没有获取到信息。请重新进行绑定');
        }
    }

    public static function GetID(int $qq): ?int{
        $osuId = DataStorage::GetData('osu.Bind.v1.ID/'.$qq);
        if($osuId === false){
            return NULL;
        }else{
            return intval($osuId);
        }
    }

    public static function GetUsername(int $qq): ?string{
        $username = DataStorage::GetData('osu.Bind.v1.username/'.$qq);
        if($username === false){
            return NULL;
        }else{
            return $username;
        }
    }

    public static function GetMode(int $qq): ?string{
        $mode = DataStorage::GetData('osu.Bind.v1.mode/'.$qq);
        if($mode === false){
            return NULL;
        }else{
            return $mode;
        }
    }
}