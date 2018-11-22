<?php
    //获得参数 signature nonce token timestamp echostr
    $nonce     = $_GET['nonce'];
    $token     = 'sjj';
    $timestamp = $_GET['timestamp'];
    $echostr   = $_GET['echostr'];
    $signature = $_GET['signature'];
    //形成数组，然后按字典序排序
    $array = array();
    $array = array($nonce, $timestamp, $token);
    sort($array);
    //拼接成字符串,sha1加密 ，然后与signature进行校验
    $str = sha1( implode( $array ) );
    if( $str == $signature && $echostr ){
        //第一次接入weixin api接口的时候
        echo  $echostr;
        exit;
    }else{

        //1.获取到微信推送过来post数据（xml格式）
        $postArr = $GLOBALS['HTTP_RAW_POST_DATA'];
        //2.处理消息类型，并设置回复类型和内容
        $postObj = simplexml_load_string( $postArr );
        //判断该数据包是否是订阅的事件推送
        if( strtolower( $postObj->MsgType) == 'event'){
            //如果是关注 subscribe 事件
            if( strtolower($postObj->Event == 'subscribe') ){
                //回复用户消息(纯文本格式)
                $toUser   = $postObj->FromUserName;
                $fromUser = $postObj->ToUserName;
                $time     = time();
                $msgType  =  'text';
                $content  = '欢迎关注孙佳静的微信公众账号，以此公众号为测试号';
                $template = "<xml>
                                <ToUserName><![CDATA[%s]]></ToUserName>
                                <FromUserName><![CDATA[%s]]></FromUserName>
                                <CreateTime>%s</CreateTime>
                                <MsgType><![CDATA[%s]]></MsgType>
                                <Content><![CDATA[%s]]></Content>
                                </xml>";
                $info     = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
                echo $info;
            }
        }
      //判断是否是文本信息
        if( strtolower( $postObj->MsgType) == 'text'){
          //接收文本信息
          $content  = $postObj->Content;
          //回复用户消息（纯文本格式）
          $toUser   = $postObj->FromUserName;
          $fromUser = $postObj->ToUserName;
          $time     = time();
          $msgType  =  'text';
          $str = mb_substr($content,-2,2,"UTF-8");
          if($str == '天气'){
            $content = "当前城市：北京"."\n"
              		."2018年11月21日 8时发布"."\n"."\n"
                    ."实时天气"."\n"
              		."晴 3℃~9℃ 南风<3级"."\n"."\n"
                    ."温馨提示：天气寒冷，建议羽绒服、棉衣、棉靴等冬季保暖服装"."\n"."\n"
                    ."明天"."\n"."晴 3℃~8℃ 南风<3级"."\n"."\n"
                    ."后天"."\n"."晴 0℃~7℃ 西北风3~4级";       
            $template = "<xml>
            				<ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>";
            $info = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
            echo $info;            
          }else{
            $content  = "您发送的内容是：".$content."\n"
              		."如需查询天气信息："."\n"
              		."请在发送消息中包含 天气 关键字";
            $template = "<xml>
                          	<ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>";
            $info     = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
            echo $info;        
          }

        }      

    }