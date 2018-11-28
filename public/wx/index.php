<?php
/**
  * wechat weather
  */
define("TOKEN", "sjj");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();

class wechatCallbackapiTest
{
    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

          //extract post data
        if (!empty($postStr)){
                
                  $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $RX_TYPE = trim($postObj->MsgType);

                switch($RX_TYPE)
                {
                    case "text":
                        $resultStr = $this->handleText($postObj);
                        break;
                    case "event":
                        $resultStr = $this->handleEvent($postObj);
                        break;
                    default:
                        $resultStr = "Unknow msg type: ".$RX_TYPE;
                        break;
                }
                echo $resultStr;
        }else {
            echo "";
            exit;
        }
    }

    public function handleText($postObj)
    {
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $time = time();
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                    </xml>";             
        if(!empty( $keyword ))
        {
            $msgType = "text";

            //天气
            $str = mb_substr($keyword,-2,2,"UTF-8");
            $str_key = mb_substr($keyword,0,-2,"UTF-8");
            if($str == '天气' && !empty($str_key)){
                $weatherData = $this->weather($str_key);                
                if(empty($weatherData)){
                    $contentStr = "抱歉，没有查到\"".$str_key."\"的天气信息！";
                } else {
                  $contentStr = "当前城市： ".$weatherData["city_name"]."\n"
                    .$weatherData["fabu_time"]."  发布"."\n"."\n"
                    ."实时天气"."\n"
                    .$weatherData["type"]." ".$weatherData["lowtem"]."℃~".$weatherData["hightem"]."℃ "."风力:".$weatherData["fengli"]."\n"."\n"
                    ."温馨提示：".$weatherData["wenxintishi"]."\n"."\n"
                    ."明天"."\n"
                    .$weatherData["type1"]." ".$weatherData["lowtem1"]."℃~".$weatherData["hightem1"]."℃ "."风力:".$weatherData["fengli1"]."\n"."\n"
                    ."后天"."\n"  
                    .$weatherData["type2"]." ".$weatherData["lowtem2"]."℃~".$weatherData["hightem2"]."℃ "."风力:".$weatherData["fengli2"]."\n"."\n";
                }
            } else {
                $contentStr = "目前该公众号只支持天气信息查询，请输入城市名称加天气（例如：北京天气），开始查询天气信息吧！其它内容，敬请期待！！！";
            }
            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            echo $resultStr;
        }else{
            echo "Input something...";
        }
    }

    public function handleEvent($object)
    {
        $contentStr = "";
        switch ($object->Event)
        {
            case "subscribe":
                $contentStr = "感谢您关注孙佳静的微信公众号，目前该公众号支持天气信息查询，请输入城市名称加天气（例如：北京天气），开始查询天气信息吧！";
                break;
            default :
                $contentStr = "Unknow Event: ".$object->Event;
                break;
        }
        $resultStr = $this->responseText($object, $contentStr);
        return $resultStr;
    }
    
    public function responseText($object, $content, $flag=0)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        return $resultStr;
    }

    private function weather($n){
       //根据城市名称通过访问接口获得城市对应的cityCode      
      $url_get='http://192.144.169.126/city/'.$n;
      $cityData= $this->https_request($url_get);
      $city_Code=$cityData['cityCode'];
            
      //根据城市的cityCode来查询该城市的天气信息
      $url_get2='http://192.144.169.126/weather/'.$city_Code;
      $weatherData=$this->https_request($url_get2);
      $result=$weatherData['weatherData'];
      return $result;
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];    
                
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    function https_request ($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $out = curl_exec($ch);
        curl_close($ch);
        return  json_decode($out,true);
    }
}

?>