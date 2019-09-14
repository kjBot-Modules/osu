<?php
namespace kjBotModule\kj415j45\osu;

class Mod{
    
    public static function PraseMod($mod){
        $list=NULL;
        if($mod & 1)$list['NF']=1;
        if($mod & 2)$list['EZ']=1;
        if($mod & 4)$list['TD']=1;
        if($mod & 8)$list['HD']=1;
        if($mod & 16)$list['HR']=1;
        if($mod & 32)$list['SD']=1;
        if($mod & 64)$list['DT']=1;
        if($mod & 128)$list['RX']=1;
        if($mod & 256)$list['HT']=1;
        if($mod & 512){unset($list['DT']);$list['NC']=1;}
        if($mod & 1024)$list['FL']=1;
        if($mod & 2048)$list['AU']=1;
        if($mod & 4096)$list['SO']=1;
        if($mod & 8192)$list['AP']=1;
        if($mod & 16384){unset($list['SD']);$list['PF']=1;}
        if($mod & 1073741824)$list['MR']=1;

        return $list;
    }

    public static function GetModString($mod){
        $mods=NULL;
        $modList=@array_keys(static::PraseMod($mod));
        for($i=0;$i<@count($modList);$i++){
            $mods.=$modList[$i];
        }
        return $mods;
    }
}
