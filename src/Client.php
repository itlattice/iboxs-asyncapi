<?php
/**
 * 接口开发从这里开始
 * @author  zqu zqu1016@qq.com
 */
namespace iboxs\asyncapi;

use iboxs\asyncapi\lib\Base;
use iboxs\asyncapi\lib\Log;
use iboxs\redis\Redis;

class Client extends Base
{
    use Log;
    public function __construct($config)
    {
        $this->config=$config;
    }


    public function addPost($url,$data,$type='form'){
        $data=[
            'url'=>$url,
            'data'=>$data,
            'type'=>$type,
            'method'=>'post',
            'time'=>0,
            'count'=>0
        ];
        $this->writeLog('写入队列【post】：'.json_encode($data,256),$this->config['logpath']);
        return Redis::list()->rPush($this->config['rediskey'],$data);
    }

    public function addGet($url,$data){
        $data=[
            'url'=>$url,
            'data'=>$data,
            'method'=>'get',
            'time'=>0,
            'count'=>0
        ];
        $this->writeLog('写入队列【get】：'.json_encode($data,256),$this->config['logpath']);
        return Redis::list()->rPush($this->config['rediskey'],$data);
    }

    public static function install(){
        if(function_exists('root_path')){
            $path=root_path('config')."/asyncpost.php";
            $text=__DIR__."/../test/asyncpost.php";
            if(file_exists($text)){
                $text=file_get_contents($text);
            }
            file_put_contents($path,$text);

            $shellFile=root_path('resource/shell')."/asyncpost.sh";
            if(file_exists($shellFile)){
                $shell=file_get_contents(__DIR__."/../test/asyncpost.sh");
                file_put_contents($shellFile,$shell);
            }
        }
    }
}