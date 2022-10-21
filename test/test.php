<?php
require_once '../vendor/autoload.php';

$config=[
    'time'=>[1,3,5,10,20],
    'logpath'=>__DIR__."../log/",
    'rediskey'=>'asyncpostlist'
];

use iboxs\asyncapi\Api;
Api::query()->addPost('hhh',['sss']);