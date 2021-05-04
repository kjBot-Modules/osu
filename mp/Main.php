<?php
namespace kjBotModule\kj415j45\osu\mp;

use kjBot\Framework\Module;
use kjBot\Framework\DataStorage;
use kjBot\Framework\Event\MessageEvent;
use kjBotModule\kj415j45\CoreModule\Access;
use kjBotModule\kj415j45\CoreModule\AccessLevel;

class Main extends Module{
    public function process(array $args, MessageEvent $event){
        Access::Control($event)->requireLevel(AccessLevel::Developer);
        switch($args[0]){
            case '!osu.mp.start':
                return $this->start($args, $event);
            case '!osu.mp.refresh':
                return $this->refresh($args, $event);
            case '!osu.mp.jump':
                return $this->jump($args, $event);
            case '!osu.mp.stop':
                return $this->stop($args, $event);
            default:
                return NULL;
        }
    }

    private function start(array $args, MessageEvent $event){
        $matchID = (int)$args[1];
        $historyJson = file_get_contents(
            "https://osu.ppy.sh/community/matches/{$matchID}?after=0&limit=1",
            false, stream_context_create(Tools::REQ_HEADER)
        );
        if(false === $historyJson)q('读取mp数据失败');

        $history = json_decode($historyJson);

        $users = Tools::GetEventUsers($history->users);

        $firstEvent = $history->events[0];
        $eventTime = Tools::ConvertTimestamp($firstEvent->timestamp)->format('Y-m-d H:i:s');

        DataStorage::SetData("osu.mp/{$event->getId()}", "{$matchID} {$firstEvent->id}");

        return $event->sendBack(
            "开始监听 mp_{$matchID}\n房间由 {$users[$firstEvent->user_id]->username} 创建于 {$eventTime}"
        );
    }

    private function refresh(array $args, MessageEvent $event){
        $limit = (int)($args[1]??5);
        $data = DataStorage::GetData("osu.mp/{$event->getId()}");
        if($data === false)q('读取mp数据失败');
        sscanf(trim(DataStorage::GetData("osu.mp/{$event->getId()}")), '%d %d', $matchID, $lastEventID);

        $historyJson = file_get_contents(
            "https://osu.ppy.sh/community/matches/{$matchID}?after={$lastEventID}&limit={$limit}",
            false, stream_context_create(Tools::REQ_HEADER)
        );
        $history = json_decode($historyJson);
        $events = $history->events;
        $users = Tools::GetEventUsers($history->users);

        foreach($events as $singleEvent){
            $Queue[]= $event->sendBack(Tools::ParseEvent($singleEvent, $users, $event->msgId));
            if($singleEvent->detail->type == 'other' && $singleEvent->game->end_time == NULL){
                break;
            }
            DataStorage::SetData("osu.mp/{$event->getId()}", "{$matchID} {$singleEvent->id}");
            if($singleEvent->detail->type == 'match-disbanded'){
                unlink(DataStorage::$storagePath."data/osu.mp/{$event->getId()}");
            }
        }
        return $Queue;
    }

    private function jump(array $args, MessageEvent $event){
        $limit = (int)($args[1]??5);
        $data = DataStorage::GetData("osu.mp/{$event->getId()}");
        if($data === false)q('没有正在监听的mp');
        sscanf(trim(DataStorage::GetData("osu.mp/{$event->getId()}")), '%d %d', $matchID, $lastEventID);

        $latestEventID = json_decode(
            file_get_contents(
                "https://osu.ppy.sh/community/matches/{$matchID}?limit=1",
                false, stream_context_create(Tools::REQ_HEADER)
            )
        )->latest_event_id;

        $virtualTailEvent = $latestEventID + 1;

        $historyJson = file_get_contents(
            "https://osu.ppy.sh/community/matches/{$matchID}?before={$virtualTailEvent}&limit={$limit}",
            false, stream_context_create(Tools::REQ_HEADER)
        );
        $history = json_decode($historyJson);
        $events = $history->events;
        $users = Tools::GetEventUsers($history->users);

        foreach($events as $singleEvent){
            $Queue[]= $event->sendBack(Tools::ParseEvent($singleEvent, $users, $event->msgId));
            if($singleEvent->detail->type == 'other' && $singleEvent->game->end_time == NULL){
                break;
            }
            DataStorage::SetData("osu.mp/{$event->getId()}", "{$matchID} {$singleEvent->id}");
            if($singleEvent->detail->type == 'match-disbanded'){
                unlink(DataStorage::$storagePath."data/osu.mp/{$event->getId()}");
            }
        }

        return $Queue;
    }

    private function stop(array $args, MessageEvent $event){
        unlink(DataStorage::$storagePath."data/osu.mp/{$event->getId()}");
        return $event->sendBack('已停止监听');
    }

}