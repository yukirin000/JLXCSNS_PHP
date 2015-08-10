<?php
/**
 * Created by PhpStorm.
 * User: Think
 * Date: 2015/5/4
 * Time: 23:06
 */
function testEcho(){

    return 'echo123';
}

function get_image_type(){

    return 1;
}
function get_voice_type(){

    return 2;
}
function get_video_type(){

    return 3;
}
//电话正则
define('PHONE_MATCH','/^((17[0-9])|(13[0-9])|(14[0-9])|(15[0-9])|(18[0,0-9]))\d{8}$/');
//haha号id
define('HELLOHA_ID','/^[_a-zA-Z0-9]{6,20}+$/');


/**
 * @brief json固定返回格式
 *
 */
function returnJson($status = 1, $message = '', $result){

    //日志记载
    foreach($_REQUEST as $key => $value) {
        \Think\Log::record($key.'->'.$value,'INFO');
    }
    //返回值记载 太多了
//    \Think\Log::record(json_encode($result),'INFO');

    if(isset($result)){

        $result = array_replace_null($result);//替换空字符串
        if(isset($result['list'])){
            $result['list'] = array_replace_null($result['list']);//替换空字符串
        }

    }else{
        $result = "";
    }

    $jsonMap = array();
    $jsonMap['status'] = $status;
    $jsonMap['result'] = $result;
    $jsonMap['message'] = $message;

    echo json_encode($jsonMap);
}


//过滤数组中的空元素
function array_replace_null($result)
{
//    if(is_array($result) || is_object($result)){
//        foreach($result as $key=>$val){
//            if(is_object($val)){
//                foreach($val as $k=>$v){
//                    if(!isset($v)){
//                        $result[$key]->$k = '';
//                    }else{
//                        $result[$key]->$k = array_replace_null($result[$key]->$k);
//                    }
//                }
//            }
//            if(is_array($val)){
//
//                foreach($val as $k=>$v){
//                    if(!isset($v)){
//                        $result[$key][$k] = '';
//                    }else{
//                        $result[$key]->$k = array_replace_null($result[$key]->$k);
//                    }
//                }
//            }
//
//            if(!isset($val)){
//                $result[$key] = '';
//            }
//        }
//    }
    if(is_array($result) || is_object($result))
    {
        foreach ($result as $key => $val) {

            if (!isset($val)) {
                $result[$key] = '';
            } else {
                $result[$key] = array_replace_null($result[$key]);
            }
        }
    }elseif(!isset($result)){

        $result = '';
    }

    return $result;

}

/*
 * 云巴推送
 *
 * //添加好友 1
 * //回复状态 2
 * //回复别人的回复 3
 * //点赞 4
 * */
function pushMessage($target_id, $message, $type){

    //发送的内容
//    $content = array('type'=>$type,
//                     'content'=>array(
//                         'uid'=>'',
//                         'news_id'=>'',
//                         'news_content'=>'',
//                         'news_image'=>''
//                     ));
    $content = array('type'=>$type,
                    'content'=>$message);

//      'appkey'=>'555de1ac27302bb31589369c',
//      'seckey'=>'sec-pWEmt2isYrelVhjaRvbPUcM8dRokodtpmi0Kj0Q3xQyqR76R',
    $data = array ( 'method'=>'publish',
        'appkey'=>'55c499f19477ebf5246955f3',
        'seckey'=>'sec-TsIKMMPfvHKwEM5i1Zwr12veNjMZIV86sCi8b3MO0sQ3ahfR',
        'topic'=>JLXC.$target_id,
        'msg'=>$content);
    $data_string = json_encode($data);
    $ch = curl_init('http://rest.yunba.io:8080');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
    );

//    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//            'Content-Type: application/json',
//            'Content-Length: ' . strlen($data_string))
//    );

    $result = curl_exec($ch);
    //推送记载
    \Think\Log::record(json_encode($result),'INFO');
    return $result;
}

//融云推送
function getRongConnection(){
    $rong = new \Org\Util\ServerAPI('8luwapkvufv3l','Z42GtwF2gN');
    return $rong;
}

//获取融云token
function getRongIMToken($userId, $name, $portraitUri)
{
    $rong = getRongConnection();

    $jsonObject = json_decode($rong->getRongToken($userId,$name,$portraitUri));

    $jsonObject = (array)$jsonObject;
    if($jsonObject['code'] == 200){
        return $jsonObject['token'];
    }else{
        return null;
    }
}

/**
 * 获取随机数
 * @param $length 随机数长度
 * @param model 模式 0 大小写数字 1 纯数字 2 纯小写字母 3 大写字母 4 大小写字母 5 大写字母数字 6 小写字母数字
 *
 */
function get_rand_code ($length = 6, $mode = 0)
{
    switch ($mode) {
        case '1':
            $str = '1234567890';
            break;
        case '2':
            $str = 'abcdefghijklmnopqrstuvwxyz';
            break;
        case '3':
            $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        case '4':
            $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';break;
        case '5':
            $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
            break;
        case '6':
            $str = 'abcdefghijklmnopqrstuvwxyz1234567890';
            break;
        default:
            $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
            break;
    }
    $randString = '';
    $len = strlen($str)-1;
    for($i = 0;$i < $length;$i ++){
        $num = mt_rand(0, $len);
        $randString .= $str[$num];
    }
    return $randString ;
}

/**
 * 获取比例
 * @param $arr 原来数组
 * @param $leftCount 剩余数量
 * @param $finalArr 最终数组
 */
function getFriendProportion ($arr, $leftCount=0, $finalArr=array(0,0,0,0))
{
    //获取当前数量
    $nowSchoolCount = $arr[0];
    $nowFriendCount = $arr[1];
    $nowDistrictCount = $arr[2];
    $nowCityCount = $arr[3];
    if ($nowSchoolCount<=0 && $nowFriendCount<=0 && $nowDistrictCount<=0 && $nowCityCount<=0) {
        return $finalArr;
    }

    //需要取的用户数组
    $useArr = array();
    //基数
    $baseNum = 0;

    if ($nowSchoolCount <= 0) {
        $nowSchoolCount = 0;
    }else{
        array_push($useArr, 5);
        $baseNum += 5;
    }

    if ($nowFriendCount <= 0) {
        $nowFriendCount = 0;
    }else{
        array_push($useArr, 4);
        $baseNum += 4;
    }

    if ($nowDistrictCount <= 0) {
        $nowDistrictCount = 0;
    }else{
        array_push($useArr, 3);
        $baseNum += 3;
    }

    if ($nowCityCount <= 0) {
        $nowCityCount = 0;
    }else{
        array_push($useArr, 2);
        $baseNum += 2;
    }

    if ($baseNum <= 0) {
        return $finalArr;
    }

    //应该取的数量
    //学校
    $shouldSchoolCount   = 0;
    //好友的好友
    $shouldFriendCount   = 0;
    //同区
    $shouldDistrictCount = 0;
    //同城
    $shouldCityCount     = 0;

    foreach ($useArr as $num ) {
        switch ($num) {
            case 5:
                $shouldSchoolCount   = (int)($leftCount*5/$baseNum);
                break;
            case 4:
                $shouldFriendCount   = (int)($leftCount*4/$baseNum);
                break;
            case 3:
                $shouldDistrictCount = (int)($leftCount*3/$baseNum);
                break;
            case 2:
                $shouldCityCount     = (int)($leftCount*2/$baseNum);
                break;
            default:
                break;
        }

    }

    //剩余学校数量
    $leftSchoolCount     = $shouldSchoolCount-$nowSchoolCount;
    //剩余好友的好友数量
    $leftFriendCount     = $shouldFriendCount-$nowFriendCount;
    //剩余同区数量
    $leftDistrictCount   = $shouldDistrictCount-$nowDistrictCount;
    //剩余同城数量
    $leftCityCount       = $shouldCityCount-$nowCityCount;

//      //最终学校数量
//    $finalSchoolCount    = 0;
//      //最终好友的好友数量
//     $finalFriendCount    = 0;
//      //最终同区数量
//     $finalDistrictCount  = 0;
//      //最终同城数量
//     $finalCityCount      = 0;

      //最终不够的量
     $totalNoEnough       = 0;
    //最终学校数量
    if ($leftSchoolCount > 0) {
        $finalSchoolCount = $nowSchoolCount;
        $totalNoEnough += $leftSchoolCount;
    }else{
        $finalSchoolCount = $shouldSchoolCount;
    }
    //最终好友数量
    if ($leftFriendCount > 0) {
        $finalFriendCount = $nowFriendCount;
        $totalNoEnough += $leftFriendCount;
    }else{
       $finalFriendCount = $shouldFriendCount;
    }
    //最终同区数量
    if ($leftDistrictCount > 0) {
        $finalDistrictCount = $nowDistrictCount;
        $totalNoEnough += $leftDistrictCount;
    }else{
        $finalDistrictCount = $shouldDistrictCount;
    }
    //最终同城数量
    if ($leftCityCount > 0) {
        $finalCityCount = $nowCityCount;
        $totalNoEnough += $leftCityCount;
    }else{
        $finalCityCount = $shouldCityCount;
    }

    $nowFinalArr = array($finalSchoolCount+$finalArr[0],$finalFriendCount+$finalArr[1],$finalDistrictCount+$finalArr[2],$finalCityCount+$finalArr[3]);

    if ($finalSchoolCount<=0 && $finalFriendCount<=0 && $finalDistrictCount<=0 && $finalCityCount<=0) {
        return $nowFinalArr;
    }

    if ($totalNoEnough <= 0) {
        return $nowFinalArr;
    }

    return getFriendProportion(array(-$leftSchoolCount, -$leftFriendCount, -$leftDistrictCount, -$leftCityCount), $totalNoEnough, $nowFinalArr);

}


///*短信发送*/
//function send_sms($data) {
//    $rand = get_rand_code(6,1);
//    $content = $rand.'短信验证码，1分钟内有效【优生宝】';
//    $content = rawurlencode($content);
//    if(count($data['mobile'])>0){
//        $mobile = implode(',',$data['mobile']);
//    }
//
//    $post_data = "action=send&userid=&account=ysbao&password=ubaby001&mobile=".$mobile."&sendTime=&content=".$content;
//
//    $target = "http://sms.chanzor.com:8001/sms.aspx";
//    $url_info = parse_url($target);
//    $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
//    $httpheader .= "Host:" . $url_info['host'] . "\r\n";
//    $httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
//    $httpheader .= "Content-Length:" . strlen($post_data) . "\r\n";
//    $httpheader .= "Connection:close\r\n\r\n";
//    $httpheader .= $post_data;
//
//    $fd = fsockopen($url_info['host'], 80);
//    fwrite($fd, $httpheader);
//    $gets = "";
//    while(!feof($fd)) {
//        $gets .= fread($fd, 128);
//    }
//    fclose($fd);
//    $start=strpos($gets,"<?xml");
//    $data=substr($gets,$start);
//    $xml=simplexml_load_string($data);
//    $result = json_decode(json_encode($xml),TRUE);
//
//    return array(
//        'status' => $result['returnstatus'],
//        'message' => $result['message'],
//        'rand'=>$rand
//    );
//}