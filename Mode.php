<?php
namespace kjBotModule\kj415j45\osu;

class Mode{
    const osu = 0;
    const taiko = 1;
    const ctb = 2;
    const mania = 3;

    public static final function Parse(?string $str): ?int{
        switch($str){
            case 'mania': case ':3': case '3': case '敲键盘': case '2dx':
                return Mode::mania;
            case 'ctb': case ':2': case '2': case '接水果': case '接屎':
                return Mode::ctb;
            case 'taiko': case ':1': case '1': case '打太鼓': case '太鼓': case '太尻': case '打鼓':
                return Mode::taiko;
            case 'std': case ':0': case '0': case '戳泡泡': case 'osu':
                return Mode::osu;
            default:
                return NULL;
        }
    }
}