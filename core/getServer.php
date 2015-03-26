<?php
    function getServerName($link)
    {
        $sql = "select serverip,port from videocms_server";
        $result = fetchAll($link,$sql);
        //构造服务器地址与端口
        //$serverAddress = array();
        $i = 1;
        $serverArray = array();
        foreach($result as $value)
        {
            $item = array("id"=>$i,"text"=>$value['serverip'].":".$value['port'],"children"=>array());
            $i++;
            //array_push($serverArray,array("id"=>$i,"text"=>$value['serverip'].":".$value['port'],"children"=>array()));
            $connection=array('ip'=>$value['serverip'],'port'=>$value['port']);
            $XML = serverCommand($connection,"get videos");
            $serverInfo = parseXML($XML);
            
            foreach($serverInfo['video'] as $value)
            {
                array_push($item['children'],array("id"=>$i,"text"=>$value['id'][0]['#text']));
                $i++;                
            }
            array_push($serverArray,$item);           
        }              
        
        $serverJson = json_encode($serverArray);
        return $serverJson;
    }
    
    function serverCommand($connection,$buf)
    {
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($sock,$connection['ip'], $connection['port']);
        socket_write($sock,$buf,strlen($buf));
        $result = socket_read($sock,1024);
        socket_close($sock);
        return $result;       
    }
    
    function parseXML($XML)
    {
        //解析XML
        $dom=new DOMDocument();
        $dom->loadXML($XML);
        return getArray($dom->documentElement);
    }
    
    function getArray($node){
        $array=false;
        if($node->hasAttributes()){
            foreach ($node->attributes as $attr){
                $array[$attr->nodeName]=$attr->nodeValue;
            }
        }
        if($node->hasChildNodes()){
            if($node->childNodes->length==1){
                $array[$node->firstChild->nodeName]=getArray($node->firstChild);
            } else {
                foreach ($node->childNodes as $childNode){
                    if($childNode->nodeType!=XML_TEXT_NODE){
                        $array[$childNode->nodeName][]=getArray($childNode);
                    }
                }
            }
        } else {
            return $node->nodeValue;
        }
        return $array;
    }
    