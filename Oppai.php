<?php
namespace kjBotModule\kj415j45\osu;

use kjBotModule\kj415j45\osu\API\V1;

class Oppai{

    //Need better method
    private static $shellType = (PHP_OS === 'WINNT'?'ps':'sh');

    public static function CountPPIf(int $bid, int $mode, string $str = ''){
        \preg_match('/[ a-z0-9%]+/', $str, $match);
        if($str !== '' && $match[0] !== $str){
            q('非法参数');
        }else{
            return static::execute($bid, "-m{$mode} ".$str);
        }
    }

    public static function COuntPPIfFC(Score $score): object{
        $modStr = Mod::GetModString($score->mod);
        return static::execute($score->bid, "+{$modStr} -m{$score->mode} {$score->count100}x100 {$score->count50}x50 0xmiss ");
    }

    public static function CountPP(Score $score): object{
        $modStr = Mod::GetModString($score->mod);
        return static::execute($score->bid, "+{$modStr} -m{$score->mode} ".number_format($score->acc, 2)."% {$score->count100}x100 {$score->count50}x50 {$score->countMiss}xmiss {$score->maxCombo}x ");
    }

    private static function execute(int $bid, string $query): object{
        if(static::$shellType === 'ps'){
            exec('powershell "(New-Object System.Net.WebClient).DownloadString(\"https://osu.ppy.sh/osu/'.$bid.'\") | oppai - -ojson '.$query, $result);
        }else{
            exec('curl https://osu.ppy.sh/osu/'.$bid.' | oppai - -ojson '.$query, $result);
        }
        $ret = json_decode($result[0]);
        if($ret->code === -4){ //查询了未实现模式导致谱面信息丢失
            $mapInfo = (new V1())->getBeatmap($bid);
            $ret->cs = $mapInfo->diff_size;
            $ret->ar = $mapInfo->diff_approach;
            $ret->hp = $mapInfo->diff_drain;
            $ret->od = $mapInfo->diff_overall;
            $ret->stars = $mapInfo->difficultyrating;
            $ret->artist = $mapInfo->artist;
            $ret->title = $mapInfo->title;
            $ret->version = $mapInfo->version;
            $ret->creator = $mapInfo->creator;
        }
        return $ret;
    }

}