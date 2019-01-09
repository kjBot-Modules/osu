# kjBot osu module

## Repo XML

```xml
<project path="modules/kj415j45/osu" name="osu" remote="kjBot-Modules" revision="0.x" />
```

## composer.json

`intervention/image` 与 `ext-imagick` 是 `Recent, Best` 依赖，`nelexa/zip` 是 `Listen` 依赖。
```json
{
    "require": {
        "ext-imagick": "*",
        "intervention/image": "^2.4",
        "nelexa/zip": "^3.1"
    }
}
```

## config.ini

```ini
osu_api_key="" ;osu! API v1 key，大部分模块都需要
osu_cookie="phpbb3_2cjk5_u=;phpbb3_2cjk5_k=;phpbb3_2cjk5_sid=;phpbb3_2cjk5_sid_check=;" ;根据实际 cookies 填空
```

## modules.php

```php
'绑定osu' => kjBotModule\kj415j45\osu\Bind::class,
'!osu.bind' => kjBotModule\kj415j45\osu\Bind::class,
'！osu.bind' => kjBotModule\kj415j45\osu\Bind::class, //Bind为核心模块，强烈建议保留
'!recent' => kjBotModule\kj415j45\osu\Recent::class,
'！recent' => kjBotModule\kj415j45\osu\Recent::class,
'!osu.setMode' => kjBotModule\kj415j45\osu\SetMode::class,
'！osu.setMode' => kjBotModule\kj415j45\osu\SetMode::class,
'!mode' => kjBotModule\kj415j45\osu\SetMode::class, //白菜兼容模式
'！mode' => kjBotModule\kj415j45\osu\SetMode::class,
'!osu.bp' => kjBotModule\kj415j45\osu\Best::class,
'！osu.bp' => kjBotModule\kj415j45\osu\Best::class,
'!bp' => kjBotModule\kj415j45\osu\Best::class, //白菜兼容模式
'！bp' => kjBotModule\kj415j45\osu\Best::class,
'!bpme' => kjBotModule\kj415j45\osu\Best::class, //bpme与bp存在读取 $args[0] 进行判定的情况，建议保留
'！bpme' => kjBotModule\kj415j45\osu\Best::class,
'!osu.profile' => kjBotModule\kj415j45\osu\Profile::class, //该模块实际上并不依赖 Bind
'！osu.profile' => kjBotModule\kj415j45\osu\Profile::class,
'!osu.me' => kjBotModule\kj415j45\osu\Profile::class,
'！osu.me' => kjBotModule\kj415j45\osu\Profile::class,
'osu资料' => kjBotModule\kj415j45\osu\Profile::class,
'!osu.listen' => kjBotModule\kj415j45\osu\Listen::class, //需要 osu_cookie
'！osu.listen' => kjBotModule\kj415j45\osu\Listen::class,
```
