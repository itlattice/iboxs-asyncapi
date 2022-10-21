<?php
namespace iboxs\asyncapi\command;

use iboxs\asyncapi\lib\Log;
use iboxs\http\Http;
use iboxs\redis\Redis;

class AsyncRun{
    use Log;

    public static $config=[];

    public static function handle($config=[]){
        if($config==[]){
            if(!function_exists('asyncapi')){
                die('请完成配置');
            }
            $config=config('asyncapi');
        }
        if($config==[]){
            $config=[
                'time'=>[1,3,5,10,20],
                'logpath'=>__DIR__."../log/",
                'rediskey'=>'asyncpostlist'
            ];
        }
        self::$config=$config;
        /***********************************/
        // $data=[
        //     'url'=>$url,
        //     'data'=>$data,
        //     'type'=>$type,
        //     'method'=>'post',
        //     'time'=>0
        // ];
        // return Redis::list()->rPush('asyncpostlist',$data);
        $len=Redis::list()->len(self::$config['rediskey']);
        if($len<1){
            return;
        }
        for($i=0;$i<$len;$i++){
            $data=Redis::list()->rPop(self::$config['rediskey']);
            if($data['time']<time()){  //可执行
                switch($data['method']){
                    case 'post':
                        self::Post($data);break;
                    case 'get':
                        self::Get($data);break;
                }
            } else{
                Redis::list()->lPush(self::$config['rediskey'],$data);
            }
        }
    }

    private static function File($data){
        $count=count(self::$config['time']);
        if($data['count']>=$count){  //请求超次数，自动废弃
            (new self())->writeLog("请求超次数废弃：地址[{$data['url']}],数据[".json_encode($data['data'],256)."]",self::$config['logpath']);
        }
        $time=self::$config['time'][$data['count']]??5;
        $newtime=time()+$time*60;
        $data['time']=$newtime;
        $data['count']=$data['count']+1;
        return Redis::list()->rPush(self::$config['rediskey'],$data);
    }

    private static function Post($data){
        $result=Http::Client($data['url'])->post($data['data'],$data['type'],$statusCode);
        if($result==false){
            (new self())->writeLog("请求失败：原因[网络异常],地址[{$data['url']}],数据[".json_encode($data['data'],256)."],次数[{$data['count']}]",self::$config['logpath']);
            self::File($data);
            return false;
        }
        if($statusCode!=200){
            (new self())->writeLog("请求失败失败：原因[响应状态码为{$statusCode}],地址[{$data['url']}],数据[".json_encode($data['data'],256)."],次数[{$data['count']}]",self::$config['logpath']);
            self::File($data);
            return false;
        }
        return true;
    }

    private static function Get($data){
        $result=Http::Client($data['url'])->get($data['data'],true,$statusCode);
        if($result==false){
            (new self())->writeLog("请求失败：原因[网络异常],地址[{$data['url']}],数据[".json_encode($data['data'],256)."],次数[{$data['count']}]",self::$config['logpath']);
            self::File($data);
            return false;
        }
        if($statusCode!=200){
            (new self())->writeLog("请求失败失败：原因[响应状态码为{$statusCode}],地址[{$data['url']}],数据[".json_encode($data['data'],256)."],次数[{$data['count']}]",self::$config['logpath']);
            self::File($data);
            return false;
        }
        return true;
    }
}