<?php
namespace iboxs\asyncapi\lib;
trait Log{
    public function writeLog($message,$config){
        $message="[".date('Y-m-d H:i:s')."] ".$message.PHP_EOL;
        file_put_contents($config."/".date('Ymd').".log",$message);
        return true;
    }
}