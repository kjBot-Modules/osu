<?php
namespace kjBotModule\kj415j45\osu;

use kjBot\Framework\Module;
use kjBot\Framework\Message;
use kjBot\Framework\Event\MessageEvent;
use PhpZip\ZipFile;
use kjBot\Framework\DataStorage;

class Listen extends Module{
    public function process(array $args, MessageEvent $event): Message{

        $beatmapSetID = $args[1]??q('请提供谱面集ID');
        $cache = DataStorage::GetData("osu.listen/{$beatmapSetID}.mp3");
        if($cache !== false){
            return $event->sendBack(sendRec($cache));
        }
        $webHeader = [
            "http" => [
                "header" => 'Cookie: '.Config('osu_cookie'),
            ]
        ];
        $osz = new ZipFile();
        $web = file_get_contents('https://osu.ppy.sh/d/'.intval($beatmapSetID), false, stream_context_create($webHeader));
        try{
            $osz->openFromString($web);
        }catch(\Exception $e){
            q('无法打开谱面');
        }

        $oszFiles = $osz->matcher();
        $mp3FileName = $oszFiles->match('~\S*\.mp3~')->getMatches()[0];
        $mp3 = $osz->getEntryContents($mp3FileName);

        DataStorage::SetData("osu.listen/{$beatmapSetID}.mp3", $mp3);
        return $event->sendBack(sendRec($mp3));
    }
}