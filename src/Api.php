<?php
/**
 * 接口开发从这里开始
 * laravel、thinkphp等框架可使用本类进行快速调用
 * @author  zqu zqu1016@qq.com
 */
namespace iboxs\asyncapi;

class Api
{
    /**
     * 创建一个请求对象
     * @param string $apiName 请求接口名称
     * @return Client 请求对象
     */
    public static function query($config=[]){
        if($config==[]){
            if(!function_exists('asyncapi')){
                die('请完成配置');
            }
            $config=config('asyncapi');
            if($config){
                return (new Client($config));
            }
        }
        return (new Client($config));
    }
}