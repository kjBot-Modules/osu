<?php
namespace kjBotModule\kj415j45\osu;

use kjBot\Framework\Module;
use kjBot\Framework\Message;
use kjBot\Framework\DataStorage;
use kjBotModule\kj415j45\osu\API\V1;
use kjBot\Framework\Event\MessageEvent;
use Intervention\Image\ImageManagerStatic as Image;

class Profile extends Module{
    public function process(array $args, MessageEvent $event): Message{
        if(isset($args[1])){
            $uid = ((new V1())->getUser($args[1])??q('指定的用户不存在（或者被ban了'))[0]->user_id;
        }else{
            $uid = Bind::GetID($event->getId())??q('未提供查询目标，若需要绑定可以发送“绑定osu”');
        }
        $mode = Mode::Parse($args[2])??Mode::osu;
        $img = static::Draw($uid, $mode);
        $img->save(DataStorage::$storagePath.'data/osu.profile/'.$uid.'.png');
        return $event->sendBack(\sendImg(DataStorage::GetData('osu.profile/'.$uid.'.png')));
    }

    public static function Draw($uid, int $mode = Mode::osu){
        Image::configure(array('driver' => 'imagick')); //用GD2你要渲染半年（而且对齐还有问题）
        $mode = Mode::ToStr($mode);
        $web = file_get_contents('https://osu.ppy.sh/users/'.$uid.'/'.$mode);
        $target = '<script id="json-user" type="application/json">';
        $start = strpos($web, $target);
        if($start === false)q('指定的用户不存在（或者被ban了');
        $end = strpos(substr($web, $start), '</script>');
        $userJson = substr($web, $start+strlen($target), $end-strlen($target));
        $user = json_decode($userJson);
        //初始化绘图环境
        $resources = __DIR__.'/resources/';
        $exo2 = $resources.'Exo2-Regular.ttf';
        $exo2_italic = $resources.'Exo2-Italic.ttf';
        $exo2_bold = $resources.'Exo2-Bold.ttf';
        $yahei = $resources.'Yahei.ttf';
        $white = '#ffffff';
        $badges = $user->badges;
        $badge = $badges[rand(0, count($badges)-1)];
        $flag = file_exists($resources."flags/{$user->country->code}.png")?($resources."flags/{$user->country->code}.png"):($resources.'flags/__.png');
        $stats_key = imageFont($yahei, 12, $white);
        $statics = $user->statistics;
        try{
            $avatar = Image::make('https://a.ppy.sh/'.$user->id);
        }catch(\Exception $e){
            $avatar = Image::make($resources.'avatar-guest.png');
        }
        $playtime = [
            'hours' => sprintf('%d', $statics->play_time/3600),
            'minutes' => sprintf('%d', ($statics->play_time%3600)/60),
            'seconds' => sprintf('%d', $statics->play_time%60),
        ];
        $stat = [
            'Ranked 谱面总分' => number_format($statics->ranked_score),
            '准确率' => sprintf('%.2f%%', $statics->hit_accuracy),
            '游戏次数' => number_format($statics->play_count),
            '总分' => number_format($statics->total_score),
            '总命中次数' => number_format($statics->total_hits),
            '最大连击' => number_format($statics->maximum_combo),
            '回放被观看次数' => number_format($statics->replays_watched_by_others),
        ];
        $grade = [
            'XH' => $statics->grade_counts->ssh,
            'X' => $statics->grade_counts->ss,
            'SH' => $statics->grade_counts->sh,
            'S' => $statics->grade_counts->s,
            'A' => $statics->grade_counts->a,
        ];
        //开始绘图
        $img = Image::make($user->cover_url);
        $img->fit(1000, 350)
            ->insert(Image::canvas(1000, 350)->fill([0, 0, 0, 0.5])) //背景暗化50%
            ->insert($avatar->resize(110, 110), 'top-left', 40, 220) //插入头像
            ->text($user->username, 170, 256, imageFont($exo2_italic, 24, $white, 'left', 'top')) //插入用户名
            ->text($user->title, 170, 285, imageFont($exo2_italic, 15, $white, 'left', 'top')) //插入title
            ;
        if($badge!=NULL)$img->insert(Image::make($badge->image_url), 'top-left', 40, 168); //插入狗牌
        if($user->is_supporter){
            $img->insert(Image::make($resources.'heart.png')->resize(28, 28), 'top-left', 170, 223) //插入支持者标志
                ->insert(Image::make($resources."{$mode}.png")->resize(28, 28), 'top-left', 210, 223) //插入模式标志
                ;
        }else{
            $img->insert(Image::make($resources."{$mode}.png")->resize(28, 28), 'top-left', 170, 223); //插入模式标志
        }
        $img->insert(Image::make($flag)->resize(30, 20), 'top-left', 170, 310) //插入国旗
            ->insert(Image::canvas(280, 323)->fill([0, 0, 0, 0.3]), 'top-left', 670, 27) //绘制右侧暗化
            ->text('游戏时间', 690, 50, $stats_key)
            ->text("{$playtime['hours']}小时 {$playtime['minutes']}分钟 {$playtime['seconds']}秒", 690, 72, imageFont($yahei, 18, '#ffcc22'))
            ->insert(Image::make($resources.'levelbadge.png')->resize(50, 50), 'top-left', 880, 30)
            ->text($statics->level->current, 905, 45, imageFont($exo2_bold, 18, $white, 'center', 'middle'))
        ;
        $yIndex = 120;
        foreach($stat as $key => $value){
            $img->text($key, 690, $yIndex, $stats_key);
            $img->text($value, 930, $yIndex, imageFont($exo2_bold, 16, $white, 'right'));
            $yIndex+=20;
        }
        $img->text(sprintf('%.2f', $statics->pp), 690, 280, imageFont($exo2_bold, 40, $white));
        $img->text('PP', 740, 300, imageFont($exo2_bold, 20, $white));
        $img->text('#'.number_format($statics->rank->global), 930, 280, imageFont($exo2_bold, 20, $white, 'right'));
        $img->text($user->country->code.' '.'#'.number_format($statics->rank->country), 930, 300, imageFont($exo2_bold, 20, $white, 'right'));
        $xIndex = 675;
        foreach($grade as $key => $value){
            $img->insert(Image::make($resources."{$key}.png")->resize(50, 50), 'top-left', $xIndex, 300);
            $img->text($value, $xIndex+20, 350, imageFont($exo2_bold, 16, $white, 'center', 'buttom'));
            $xIndex+=55;
        }
        return $img;
    }
}