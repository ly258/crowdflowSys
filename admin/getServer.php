<?php
    function getServerName($link)
    {
        $sql = "select serverip,port from videocms_server";
        $result = fetchAll($link,$sql);
        //构造服务器地址与端口
        $serverAddress = array();
        foreach($result as $value)
        {
            array_push($serverAddress,$value['serverip'].":".$value['port']);
        }
        
        $serverArray = array();
        $i = 1;
        foreach($serverAddress as $value)
        {
            array_push($serverArray,array("id"=>$i,"text"=>$value));
            $i++;
        }
        $serverJson = json_encode($serverArray);
        return $serverJson;
    }
    