<?php
namespace kjBotModule\kj415j45\osu\API;

class V1{
    private $key;
    public $base = 'https://osu.ppy.sh/api';

    public function __construct(string $key = NULL){
        if($key === NULL){
            $this->key = Config('osu_api_key');
        }else{
            $this->key = $key;
        }
    }


    public function getBeatmaps(array $param): array{
        $api = APIv1::getBeatmaps;
        return $this->query($api, $param);
    }

    public function getBeatmapSet(int $sid, array $param = []): array{
        return $this->getBeatmaps(array_merge($param, [
            's' => $sid,
        ]));
    }

    public function getBeatmap(int $bid, array $param = []): ?object{
        return $this->getBeatmaps(array_merge($param, [
            'b' => $bid,
        ]))[0];
    }

    public function getUserBeatmaps(int $id, array $param = []): array{
        return $this->getBeatmaps(array_merge($param, [
            'u' => $id,
        ]));
    }

    public function getBeatmapFromHash(string $hash): ?object{
        return $this->getBeatmaps([
            'h' => $hash,
        ])[0];
    }


    public function getUser($id, array $param = []): array{
        $api = APIv1::getUser;
        return $this->query($api, array_merge($param, [
            'u' => $id,
        ]));
    }

    public function getUserById(int $uid, array $param = []): ?object{
        return $this->getUser($uid, array_merge($param, [
            'type' => 'id',
        ]))[0];
    }

    public function getUserByName(string $name, array $param = []): ?object{
        return $this->getUser($name, array_merge($param, [
            'type' => 'string',
        ]))[0];
    }


    public function getScores(int $bid, array $param = []): array{
        $api = APIv1::getScores;
        return $this->query($api, array_merge($param, [
            'b' => $bid,
        ]));
    }
    //TODO extend it

    public function getUserBest($id, array $param = []): array{
        $api = APIv1::getUserBest;
        return $this->query($api, array_merge($param, [
            'u' => $id,
        ]));
    }

    public function getUserBestById(int $uid, array $param = []): array{
        return $this->getUserBest($uid, array_merge($param, [
            'type' => 'id',
        ]));
    }

    public function getUserBestByName(string $name, array $param = []): array{
        return $this->getUserBest($name, array_merge($param, [
            'type' => 'string',
        ]));
    }

    public function getUserBP($uid, int $index, array $param = []): ?object{
        return $this->getUserBestById($uid, array_merge($param, [
            'limit' => $index,
        ]))[$index-1];
    }

    public function getUserRecent($id, array $param = []): array{
        $api = APIv1::getUserRecent;
        return $this->query($api, array_merge($param, [
            'u' => $id,
        ]));
    }

    public function getUserRecentById(int $uid, array $param = []): array{
        return $this->getUserRecent($uid, array_merge($param, [
            'type' => 'id',
        ]));
    }

    public function getUserRecentByName(string $name, array $param = []): array{
        return $this->getUserRecent($name, array_merge($param, [
            'type' => 'string',
        ]));
    }

    private function query(string $api, array $param){
        $queryStr = '?';
        $param['k'] = $this->key;
        foreach($param as $key => $value){
            $queryStr.= ($key.'='.urlencode(is_bool($value)?((int)$value):$value).'&');
        }
        $result = json_decode(file_get_contents($this->base.$api.$queryStr));
        $retCode = parseHeaders($http_response_header)['reponse_code'];

        if($result == NULL && $retCode !== 200){
            throw new OsuAPIException($retCode);
        }else{
            return $result;
        }
    }
}