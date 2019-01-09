<?php
namespace kjBotModule\kj415j45\osu;

use kjBotModule\kj415j45\osu\Mod;
use Intervention\Image\ImageManagerStatic as Image;

class Score{
    public $bid, $score, $userId, $username, $count300, $countGeki, $count100, $countKatu, $count50, $countMiss, $maxCombo, $perfect, $date, $pp, $mod, $rank, $mode, $acc;

    function __construct(object $obj, $mode){
        $this->bid = $obj->beatmap_id;
        $this->score = $obj->score;
        $this->userId = $obj->user_id;
        $this->username = $obj->username;
        $this->count300 = $obj->count300;
        $this->countGeki = $obj->countgeki;
        $this->count100 = $obj->count100;
        $this->countKatu = $obj->countkatu;
        $this->count50 = $obj->count50;
        $this->countMiss = $obj->countmiss;
        $this->maxCombo = $obj->maxcombo;
        $this->perfect = $obj->perfect;
        $this->date = new \DateTime($obj->date);
        $this->pp = @$obj->pp;
        $this->mod = $obj->enabled_mods;
        $this->rank = $obj->rank;
        $this->mode = $mode;
        $this->countAcc($mode);
    }

    protected function countAcc(int $mode): float{
        switch($mode){
            case Mode::osu:
                $acc = floatval(300*$this->count300 + 100*$this->count100 + 50*$this->count50)/
                       floatval(300*($this->count300 + $this->count100 + $this->count50 + $this->countMiss));
                break;
            case Mode::taiko:
                $acc = floatval(0.5*floatval($this->count100) + $this->count300)/
                       floatval($this->count300 + $this->count100 + $this->countMiss);
                break;
            case Mode::ctb:
                $acc = floatval($this->count50 + $this->count100 + $this->count300)/
                       floatval($this->count50 + $this->count100 + $this->count300 + $this->countMiss + $this->countKatu);
                break;
            case Mode::mania:
                if($this->mod & 536870912){ //ScoreV2
                    $acc = floatval($this->count50*50 + $this->count100*100 + $this->countKatu*200 + $this->count300*300 + $this->countGeki*305)/
                           floatval(($this->count50 + $this->count100 + $this->count300 + $this->countMiss + $this->countKatu + $this->countGeki)*305);
                }else{
                    $acc = floatval($this->count50*50 + $this->count100*100 + $this->countKatu*200 + ($this->count300+ $this->countGeki)*300)/
                           floatval(($this->count50 + $this->count100 + $this->count300 + $this->countMiss + $this->countKatu + $this->countGeki)*300);
                }
                break;
            default:
                throw new \Exception('Invaild osu! mode');
        }
        $this->acc = $acc;
        return $acc;
    }

    public static function GetBG($map){
        try{
            $bg = Image::make('https://bloodcat.com/osu/i/'.$map)->fit(1280, 720);
        }catch(\Exception $e){
            return Image::make(__DIR__.'/resources/bg.jpg')->resize(1280, 720); //Fallback 背景
        }
        return $bg;
    }

    public static function GetModImages($list){
        $l=@array_keys($list);
        $imgs=null;
        for($i=0;$i<@count($l);$i++){
            $imgs[$i]=Image::make(__DIR__."/resources/{$l[$i]}.png");
        }
        return $imgs;
    }

    public static function GetModImage($list){
        $modImages = static::GetModImages($list);
        $countImg = @count($modImages);
    
        if($countImg === 0){
            return Image::canvas(1,1);
        }
    
        $modImage = Image::canvas(45*$countImg, 32);
    
        for($i = 0 ; $i<$countImg ; $i++){
            $modImage->insert($modImages[$i], 'top-left', $i*45, 0);
        }
    
        return $modImage;
    }

    public function draw(){
        Image::configure(array('driver' => 'imagick')); //用GD2你要渲染半年（而且对齐还有问题）

        $resources = __DIR__.'/resources/';
        $exo2 = $resources.'Exo2-Regular.ttf';
        $exo2b = $resources.'Exo2-Bold.ttf';
        $venera = $resources.'Venera.ttf';
        $blue = '#44AADD';
        $gray = '#AAAAAA';
        $pink = '#B21679';

        $acc=sprintf('%.2f%%', $this->acc*100);

        $scoreImg = static::GetBG($this->bid);

        $sideText = imageFont($exo2b, 38, $blue, 'center', 'buttom'); //两侧字体
        $songText = imageFont($exo2b, 15, $blue, 'center', 'buttom'); //歌名、歌手
        $performText = imageFont($exo2b, 20, $blue, 'center', 'buttom'); //表现情况
        $ppText = imageFont($exo2b, 25, $blue, 'center', 'buttom'); //PP

        $mapInfo = (new Oppai())->countPP($this);

        $scoreImg
        //准备模版
        ->blur(8)
        ->insert(Image::make($resources.'template.png')->opacity(80), 'center')
        //两侧文字
        ->text($this->maxCombo.'x', 310, 350, $sideText)
        ->text($acc, 975, 350, $sideText)
        //昵称
        ->text($this->username, 640, 225, imageFont($exo2b, 30, $gray, 'center', 'buttom'))
        //Rank
        ->insert(Image::make($resources.$this->rank.'.png'), 'top-left', 580, 210)
        //MOD
        ->insert(static::GetModImage(Mod::PraseMod($this->mod)), 'top', 640, 302)
        //分数
        ->text(number_format($this->score), 640, 375, 
               imageFont($venera, $this->score > 1000000? 55: 60, $pink, 'center', 'buttom'))
        //四维
        ->text('CS: '.sprintf('%.2f', $mapInfo->cs).'   OD: '.sprintf('%.2f', $mapInfo->od).'   Stars: '.sprintf('%.2f', $mapInfo->stars).'   HP: '.sprintf('%.2f', $mapInfo->hp).'   AR: '.sprintf('%.2f', $mapInfo->ar), 640, 400, imageFont($exo2b, 15, $pink, 'center', 'buttom'))
        //歌名
        ->text($mapInfo->title, 640, 420, $songText)
        //歌手
        ->text($mapInfo->artist, 640, 435, $songText)
        //谱面难度及谱师
        ->text($mapInfo->version.' - mapped by '.$mapInfo->creator, 640, 450, imageFont($exo2b, 15, $gray, 'center', 'buttom'))
        //日期
        ->text($this->date->format('Y-m-d H:i:s'), 640, 473, imageFont($exo2, 15, $gray, 'center', 'buttom'))
        //表现情况
        ->text(sprintf('%04d', $this->count300), 553, 515, $performText)
        ->text(sprintf('%04d', $this->count100), 615, 515, $performText)
        ->text(sprintf('%04d', $this->count50), 675, 515, $performText)
        ->text(sprintf('%04d', $this->countMiss), 735, 515, $performText)
        //三维pp
        ->text(sprintf('%.2f', $this->pp??$mapInfo->pp).'PP', 605, 646, imageFont($exo2b, 30, $blue, 'right', 'buttom'))
        ->text(sprintf('%.2f', (new Oppai())->COuntPPIfFC($this)->pp).'PP', 680, 646, imageFont($exo2b, 30, $blue, 'left', 'buttom'))
        ->text(sprintf('%.2f', $mapInfo->aim_pp), 540, 680, $ppText)
        ->text(sprintf('%.2f', $mapInfo->speed_pp), 640, 680, $ppText)
        ->text(sprintf('%.2f', $mapInfo->acc_pp), 740, 680, $ppText)
        ;

        return $scoreImg;
    }

}