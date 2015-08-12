<?php
namespace Home\Controller;
use Org\Util\Date;
use Org\Util\Date1;
use Org\Util\Haha;
use Org\Util\QRcode;
use Org\Util\ServerAPI;
use Org\Util\TDea;
use Org\Util\Yunba;
use Think\Controller;
use Think\Exception;
use Think\Log;
use Think\Model;

define("JLXC","jlxc");
define("JLXC_CHATROOM","jlxcChatroom");

class MobileApiController extends Controller {

    private $attachmentUrl = 'http://192.168.1.101/jlxc_php/Uploads/';

    public function index(){
		echo JLXC."haha\n";


        //http://rest.yunba.io:8080?method=publish&appkey=555de1ac27302bb31589369c&seckey=sec-pWEmt2isYrelVhjaRvbPUcM8dRokodtpmi0Kj0Q3xQyqR76R&topic=jlxc19&msg="Thistest"
//        $rong = getRongConnection();
//        $message = '{"message":"gaga","extra":""}';
//        echo 'fff'.$rong->messageGroupPublish(JLXC.'20',array(JLXC_CHATROOM.'24'),'RC:TxtMsg',$message);
        //$fromUserId,$toUserId = array(),$objectName,$content
//        echo $rong->messageSystemPublish(JLXC.'20', array(JLXC.'19'),'RC:ContactNtf',$message);

////        echo C('TestConfig');
////        $this->display();
//        echo U("Index/add");
//        echo '<br>';
//        echo U("Test/get");
//        echo U("Index/testImage");
//        echo phpinfo();

        try {

        }catch (Exception $e){

        }
    }
/////////////////////////////////////////////登录注册部分////////////////////////////////////////////////////////////
    /**
     * @brief 获取手机验证码
     * 接口地址 http://localhost/jlxc_php/index.php/Home/MobileApi/getMobileVerify?phone_num=15810710447
     * @param phone_num 电话号码
     */
    public function getMobileVerify(){
        try{
            $phone_num = $_REQUEST['phone_num'];
            if(empty($phone_num)){

                returnJson(0,"手机号不能为空！");
                return;
            }else{
                //正则判断
                if(preg_match(PHONE_MATCH,$phone_num)) {
//                    //判断是否被注册
//                    $findUser = M('jlxc_user');
//                    $user = $findUser->where(array('username='.$phone_num))->find();
//                    if($user){
//                        returnJson(0 ,'该手机已被申请T_T');
//                        return;
//                    }

                    $add = D('jlxc_sms');
                    $data = array();
                    $data['phone_num'] = $phone_num;
                    $data['verify_code']  = '123456';
                    $data['add_date'] = time();
                    $result = $add->add($data);
                    //成功返回
                    if($result == true) {

                        returnJson(1,'验证码已发送至您的手机！','');
                    }else{
                        returnJson(0 ,'验证码请求失败!');
                    }
                }else {
                    returnJson(0 ,'手机格式不符合!');
                }
                return;
            }

        }catch (Exception $e){

            returnJson(0,"数据异常！");
        }
    }

    /**
     * @brief 注册用户
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/registerUser?username=15810710447&password=123456&verify_code=123456
     * @param username 用户名(手机号码)
     * @param password 密码 6-24位
     * ////@param verify_code 验证码 没了
     */
    public function registerUser(){
        try{
            $username = $_REQUEST['username'];
            $password = $_REQUEST['password'];
//            $verify_code = $_REQUEST['verify_code'];
               //判断是否被注册
            $findUser = M('jlxc_user');
            $user = $findUser->where(array('username='.$username))->find();
            if($user){
                returnJson(0 ,'该手机已被申请T_T');
                return;
            }

            //正则判断
            if(!preg_match(PHONE_MATCH,$username)) {
                returnJson(0 ,'手机格式不符');
                return;
            }

            //长度不对 因为是md5 所以不判断上限
            if(strlen($password) < 6) {
                returnJson(0 ,'请输入6-24位密码=_=');
                return;
            }

//            $verifyModel = M('jlxc_sms');
//               //查看是否验证成功
//            $sql = 'SELECT * FROM jlxc_sms WHERE phone_num='.$username.'
//            and verify_code='.$verify_code.' and delete_flag=0 and add_date>'.(time()-60);
//            $data = $verifyModel->query($sql);
//
//            if(count($data) > 0){
            //因为使用mob.com的验证码 所以自己的机制取消
            $registerModel = D('jlxc_user');
            $data = array();
            $data['username'] = $username;
            $data['password'] = $password;
            $data['name']     = '学僧';//默认姓名
            $data['login_token'] = base64_encode($username.time());
            $data['add_date'] = time();
            //默认学校
            $data['school'] = 'HelloHa校园';
            $data['school_code'] = '99999999';

            //获取imtoken
            $result = $registerModel->add($data);
            $data['im_token'] = getRongIMToken('jlxc'.$result, $data['name'], $this->attachmentUrl.$user['head_image']);
            $data['id'] = $result;
            $ret = $registerModel->save($data);
//            if($ret) {
            $loginModel = M('jlxc_user');
            $user = $loginModel->where('username='.$username)->find();
            returnJson(1,"注册成功", $user);
//            }else{
//                returnJson(0,"注册失败T_T");
//            }
//            }else{
//                returnJson(0,"无效的验证码T_T");
//            }
            return;

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

    /**
     * @brief 找回密码
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/findPwd?username=15810710447&password=123456&verify_code=123456
     * @param username 用户名(手机号码)
     * @param password 密码 6-24位
     * @param verify_code 验证码
     */
    public function findPwd(){
        try{
            $username = $_REQUEST['username'];
            $password = $_REQUEST['password'];
//            $verify_code = $_REQUEST['verify_code'];

            //长度不对 因为是md5 所以不判断上限
            if(strlen($password) < 6) {
                returnJson(0 ,'请输入6-24位密码=_=');
                return;
            }

            //判断是否有该手机
            $findUser = M('jlxc_user');
            $user = $findUser->where(array('username='.$username))->find();
            if(!$user){
                returnJson(0 ,'该手机不存在T_T');
                return;
            }

//            $verifyModel = M('jlxc_sms');
//            //查看是否验证成功
//            $sql = 'SELECT * FROM jlxc_sms WHERE phone_num='.$username.'
//            and verify_code='.$verify_code.' and delete_flag=0 and add_date>'.(time()-60);
//            $data = $verifyModel->query($sql);
//            if(count($data) > 0){
            $userModel = D('jlxc_user');
            $data = array();
            $data['username'] = $username;
            $data['password'] = $password;
            $data['login_token'] = base64_encode($username.time());
            $data['update_date'] = time();
            //获取imtoken
            if(empty($user['im_token'])){
                $data['im_token'] = getRongIMToken('jlxc'.$user['id'], $user['name'], $this->attachmentUrl.$user['head_image']);
            }
            $result = $userModel->where('username="'.$username.'"')->save($data);
            if($result) {
                $loginModel = M('jlxc_user');
                $user = $loginModel->where('username='.$username)->find();
                returnJson(1,"密码修改成功", $user);
            }else{
                returnJson(0,"密码修改失败T_T");
            }
//            }else{
//                returnJson(0,"无效的验证码T_T");
//            }
            return;

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

    /**
     * @brief 修改密码
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/alterPwd?username=15810710447&password=123456
     * @param username 用户名(手机号码)
     * @param password 密码 6-24位
     * @param originPassword
     */
    public function alterPwd(){
        try{
//            $username = $_REQUEST['username'];
//            $password = $_REQUEST['password'];
//            $verify_code = $_REQUEST['verify_code'];
//
//            //长度不对 因为是md5 所以不判断上限
//            if(strlen($password) < 6) {
//                returnJson(0 ,'请输入6-24位密码=_=');
//                return;
//            }
//
//            //判断是否有该手机
//            $findUser = M('jlxc_user');
//            $user = $findUser->where(array('username='.$username))->find();
//            if(!$user){
//                returnJson(0 ,'该手机不存在T_T');
//                return;
//            }
//
//            $verifyModel = M('jlxc_sms');
//            //查看是否验证成功
//            $sql = 'SELECT * FROM jlxc_sms WHERE phone_num='.$username.'
//            and verify_code='.$verify_code.' and delete_flag=0 and add_date>'.(time()-60);
//            $data = $verifyModel->query($sql);
//            if(count($data) > 0){
//                $userModel = D('jlxc_user');
//                $data = array();
//                $data['username'] = $username;
//                $data['password'] = $password;
//                $data['login_token'] = base64_encode($username.time());
//                $data['update_date'] = time();
//                //获取imtoken
//                if(empty($user['im_token'])){
//                    $data['im_token'] = getRongIMToken('jlxc'.$user['id'], $user['name'], $this->attachmentUrl.$user['head_image']);
//                }
//                $result = $userModel->where('username="'.$username.'"')->save($data);
//                if($result) {
//                    $loginModel = M('jlxc_user');
//                    $user = $loginModel->where('username='.$username)->find();
//                    returnJson(1,"密码修改成功", $user);
//                }else{
//                    returnJson(0,"密码修改失败T_T");
//                }
//            }else{
//                returnJson(0,"无效的验证码T_T");
//            }
//            return;

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

    /**
     * @brief 是否存在用户
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/isUser?username=15810710447
     * @param username 用户名(手机号码)
     */
    public function isUser(){
        try{
            $username = $_REQUEST['username'];
            if(empty($username)) {
                returnJson(0, "用户名不能为空！");
                return;
            }
            //判断是否被注册
            $findUser = M('jlxc_user');
            $user = $findUser->where(array('username='.$username))->find();
            //1跳转到填写密码 2跳转到注册页面
            if($user){
                returnJson(1 ,'已有用户',array('direction'=>'1'));
            }else{
                returnJson(1 ,'注册用户',array('direction'=>'2'));
            }
            return;
        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

    /**
     * @brief 用户登录
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/loginUser?username=13736661241&password=123456
     * @param username 用户名(手机号码)
     * @param password 密码 6-24位
     */
    public function loginUser(){
        try{
            $username = $_REQUEST['username'];
            $password = $_REQUEST['password'];
            //判断用户名密码
            $findUser = M('jlxc_user');
            $user = $findUser->where(array('delete_flag=0 and username='.$username ,'password="'.$password.'"'))->find();

            if($user){

                $user['login_token'] = base64_encode($username.time());
                if(empty($user['im_token'])){
                    $user['im_token'] = getRongIMToken('jlxc'.$user['id'], $user['name'],$this->attachmentUrl.$user['head_image']);
                }

                $registerModel = D('jlxc_user');
                $registerModel->save($user);
                returnJson(1,"登录成功", $user);
                return;
            }else{
                returnJson(0,"用户名或密码错误!(￣▽￣”)");
            }
            return;

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

/////////////////////////////////////////////个人信息部分////////////////////////////////////////////////////////////

    /**
     * @brief 获取学校列表 全国范围搜索 或者 区域搜索
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getSchoolList
     * @param district_code 区域id
     * @param school_name 学校名字
     */
    public function getSchoolList(){
        try{

            $district_code = $_REQUEST['district_code'];
            $school_name = $_REQUEST['school_name'];
            $page = $_REQUEST['page'];
            $size = $_REQUEST['size'];
            if(empty($page)){
                $page = 1;
            }
            if(empty($size)){
                $size = 20;
            }

            //学校为空
            if(empty($school_name) && empty($district_code)){
                returnJson(0,"没有查询条件..");
                return;
            }

            $start = ($page-1)*$size;
            $end   = $size;
            //最近访问列表
            $schoolsModel = M();
            //默认使用 区域查询
            $sql='SELECT code,name,district_name,city_name,level FROM jlxc_school
                  WHERE district_code='.$district_code.' LIMIT '.$start.','.$end;
            //关键字不为空用关键字
            if(!empty($school_name)){
                $sql='SELECT  code,name,district_name,city_name,level FROM jlxc_school
                     WHERE name LIKE "%'.$school_name.'%" ';
                if(!empty($district_code)){
                    $sql = $sql.' order by find_in_set(city_code,"'.substr($district_code,0,4).'") desc';
                }
                $sql = $sql.' LIMIT '.$start.','.$end;
            }

            $schools= $schoolsModel->query($sql);

            $result = array();
            $result['list'] = $schools;
            //是否是最后一页
            if(count($schools) < $size){
                $result['is_last'] = '1';
            }else{
                $result['is_last'] = '0';
            }

            returnJson(1,"查询成功", $result);
            return;

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

    /**
     * @brief 修改学校
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/changeSchool?
     * @param uid 用户id
     * @param school 学校名
     * @param school_code 学校码
     */
    public function changeSchool(){
        try{
            $uid = $_REQUEST['uid'];
            $school = $_REQUEST['school'];
            $school_code = $_REQUEST['school_code'];
            //获取用户详细信息
            $findUser = M('jlxc_user');
            $user = $findUser->where(array('id='.$uid))->find();
            $user['school'] = $school;
            $user['school_code'] = $school_code;
            $user['update_date'] = time();
            $updateModel = D('jlxc_user');
            $ret = $updateModel->save($user);
            if($ret){
                returnJson(1,"保存成功");
                return;
            }else{
                returnJson(0,"保存失败!");
            }
            return;

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

    /**
     * @brief 获取用户二维码
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getUserQRCode?
     * @param uid 用户id
     */
    public function getUserQRCode(){
        try{

            $uid = $_REQUEST['uid'];
            if(empty($uid)){
                returnJson(0,"用户不能为空=_=");
                return;
            }

            $userModel = M('jlxc_user');
            $user = $userModel->find($uid);
            if(empty($user)){
                returnJson(0,"没有该用户=_=");
                return;
            }

            $qrcodeModel = M('jixc_user_qrcode');
            $qrcode = $qrcodeModel->where('user_id='.$uid)->find();
            if($qrcode){
                //存在
                if(file_exists($qrcode['user_qrcode'])){
                    returnJson(1,"查询成功。",substr($qrcode['user_qrcode'],2));
                }else{
                    //生成二维码
                    $data = JLXC.base64_encode($uid);
                    // 纠错级别：L、M、Q、H
                    $level = 'L';
                    // 点的大小：1到10,用于手机端4就可以了
                    $size = 6;
                    $PNG_WEB_DIR = './HelloHaQRCode/';
                    $filename = $PNG_WEB_DIR.$data.'.png';
                    QRcode::png($data, $filename, $level, $size);
                    //生成失败
                    if(!file_exists($filename)){
                        returnJson(0,"查询失败。");
                        return;
                    }
                    $qrcode['user_id'] = $uid;
                    $qrcode['user_qrcode'] = $filename;
                    $qrcode['update_date'] = time();
                    $ret = $qrcodeModel->save($qrcode);
                    if($ret){

                        returnJson(1,"查询成功。",substr($qrcode['user_qrcode'],2));
                    }else{
                        returnJson(0,"查询失败。");
                    }
                }
                return;
            }else{
                //生成二维码
                $data = JLXC.base64_encode($uid);
                 // 纠错级别：L、M、Q、H
                $level = 'L';
                 // 点的大小：1到10,用于手机端4就可以了
                $size = 6;
                $PNG_WEB_DIR = './HelloHaQRCode/';
                $filename = $PNG_WEB_DIR.$data.'.png';
                QRcode::png($data, $filename, $level, $size);
                //生成失败返回
                if(!file_exists($filename)){
                    returnJson(0,"查询失败。");
                    return;
                }

                $qrcode = array();
                $qrcode['user_id'] = $uid;
                $qrcode['user_qrcode'] = $filename;
                $qrcode['add_date'] = time();;
                $ret = $qrcodeModel->add($qrcode);
                if($ret){
                    returnJson(1,"查询成功。",substr($qrcode['user_qrcode'],2));
                }else{
                    returnJson(0,"查询失败。");
                }

                return;
            }

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }


    /**
     * @brief 保存个人信息 注册用
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/savePersonalInfo?
     * @param uid 用户id
     * @param sex 性别
     * @param name 姓名
     * @param head_image 头像 //file
     */
    public function savePersonalInfo(){
        try{

            $uid = $_REQUEST['uid'];
            $sex = $_REQUEST['sex'];
            $name = $_REQUEST['name'];
            //获取用户详细信息
            $findUser = M('jlxc_user');
            $user = $findUser->where(array('id='.$uid))->find();

            $info = null;
            $upload = null;

            if(!empty($_FILES)){
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize   =     10*1024*1024 ;// 设置附件上传大小
                $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
                $upload->savePath  =     ''; // 设置附件上传（子）目录
                $upload->saveName  =     '';
                // 上传文件
                $info   =   $upload->upload();
            }
            //上传成功
            if($info) {
                $image = new \Think\Image();
                foreach($info as $file){
                    $user['head_image'] = $file['savepath'].$file['savename'];
                    $path = $file['savepath'].$file['savename'];
//                    //返回值添加
//                    $retJson[$file['savename']] = $path;
                    $image->open('./Uploads/'.$path);
                    //缩略图地址前半部分
                    $preffix = substr($path, 0, strlen($path)-4);
                    //后缀
                    $suffix  = substr($path, strlen($path)-4);
                    //拼接
                    $subpath = $preffix.'_sub'.$suffix;
                    $user['head_sub_image'] = $subpath;
                    $image->thumb(270, 270)->save('./Uploads/'.$subpath);
                }
            }

            $user['sex'] = $sex;
            if(strlen($name) < 1){
                $user['name'] = '学僧';
            }else{
                $user['name'] = $name;
            }

            $user['update_date'] = time();
            $updateModel = D('jlxc_user');
            $ret = $updateModel->save($user);
            if($ret){
                returnJson(1,"保存成功", $user);
                return;
            }else{
                returnJson(0,"保存失败!");
            }

            return;

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

    /**
     * @brief 获取用户图片组
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getNewsImages?uid=19
     * @param uid 用户id
     */
    public function getNewsImages(){
        try{
            $uid = $_REQUEST['uid'];
            //附件列表
            $findImagesModel = M('jlxc_attachment');
            $images = $findImagesModel->field('sub_url')->where(array('delete_flag=0 and type=1 and user_id='.$uid))->limit('3')->order('add_date desc')->select();
            $list = array();

            if(empty($images)){
                $list['list'] = array();
            }else{
                $list['list'] = $images;
            }

            if($images){
                returnJson(1,"查询成功", $list);
            }else{
                returnJson(1,"还没有动态", $list);
            }
            return;

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

    /**
     * @brief 获取最近来访图片组
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getVisitImages?uid=19
     * @param uid 用户id
     */
    public function getVisitImages(){
        try{
            $uid = $_REQUEST['uid'];
            //附件列表
            $visitImagesModel = M();
            $sql = 'SELECT u.head_sub_image FROM jlxc_user u,jlxc_visit v WHERE
                    v.user_id='.$uid.' AND v.visitor_id=u.id AND v.delete_flag=0 ORDER BY v.visit_time DESC LIMIT 4';
            $images = $visitImagesModel->query($sql);

            //来访数量
            $visitModel = M('jlxc_visit');
            $visitCount = $visitModel->field('count(1) count')->where('delete_flag=0 AND user_id='.$uid)->find();
            $list = array();
            if($visitCount){
                $list['visit_count'] = $visitCount['count'];
            }else{
                $list['visit_count'] = '0';
            }


            if(empty($images)){
                $list['list'] = array();
            }else{
                $list['list'] = $images;
            }

            if($images){
                returnJson(1,"查询成功", $list);
            }else{
                returnJson(1,"还没有动态", $list);
            }
            return;

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }


    /**
     * @brief 获取好友列表
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getFriendsImage
     * @param user_id 用户id
     *
     */
    public function getFriendsImage(){
        try{
            $user_id = $_REQUEST['user_id'];

            $friendModel = M('jlxc_relationship');
            $sql = 'SELECT u.id uid,u.head_sub_image,r.friend_remark from jlxc_user u,jlxc_relationship r
                    WHERE r.delete_flag=0 and r.user_id='.$user_id.' and r.friend_id=u.id  order by r.add_date DESC LIMIT 4';
            $friendList = $friendModel->query($sql);

            //好友数量
            $friendCount = $friendModel->field('count(1) count')->where('delete_flag=0 AND user_id='.$user_id)->find();
            $list = array();
            if($friendCount){
                $list['friend_count'] = $friendCount['count'];
            }else{
                $list['friend_count'] = '0';
            }

            if(empty($friendList)){
                $list['list'] = array();
            }else{
                $list['list'] = $friendList;
            }

            //添加过
            if($friendList){
                returnJson(1,"获取成功", $list);
            }else{
                returnJson(0,"本来就没有");
            }
            return;
        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }


    /**
     * @brief 获取最近来访图片组
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getVisitList?uid=19
     * @param uid 用户id
     *
     */
    public function getVisitList(){
        try{
            $uid = $_REQUEST['uid'];
            $page = $_REQUEST['page'];
            $size = $_REQUEST['size'];
            if(empty($page)){
                $page = 1;
            }
            if(empty($size)){
                $size = 10;
            }

            $start = ($page-1)*$size;
            $end   = $size;
            //最近访问列表
            $visitImagesModel = M();
            $sql = 'SELECT u.id uid, u.name, u.head_sub_image, v.visit_time, u.sign FROM jlxc_user u,jlxc_visit v
                    WHERE v.user_id='.$uid.' AND v.visitor_id=u.id AND v.delete_flag=0 ORDER BY v.visit_time DESC LIMIT '.$start.','.$end;
            $visits = $visitImagesModel->query($sql);
            //格式化日期
            for($i=0; $i<count($visits); $i++) {
                $visits[$i]['visit_time'] = date('Y-m-d H:i:s', $visits[$i]['visit_time']);
            }

            $result = array();
            $result['list'] = $visits;
            //如果没有内容了
//            if(count($visits) < 1){
//                $result = array();
//            }
            //是否是最后一页
            if(count($visits) < $size){
                $result['is_last'] = '1';
            }else{
                $result['is_last'] = '0';
            }

            returnJson(1,"查询成功", $result);
            return;

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

    /**
     * @brief 删除最近来访
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/deleteVisit
     * @param uid 用户id
     * @param current_id 要删除的id
     */
    public function deleteVisit(){
        try{
            $uid = $_REQUEST['uid'];
            $current_id = $_REQUEST['current_id'];

            //最近访问
            $visitModel = M('jlxc_visit');
            $visit = $visitModel->where('user_id='.$uid.' and visitor_id='.$current_id)->find();
            if($visit) {
                $visit['delete_date'] = time();
                $visit['delete_flag'] = 1;
                $ret = $visitModel->save($visit);
                returnJson(1,"删除成功");
//                if($ret){
//                    returnJson(1,"删除成功");
//                }else{
//                    returnJson(0,"删除失败");
//                }

            }else{
                returnJson(0,"没有这条");
            }

            return;

        }catch (Exception $e){

            returnJson(0,"数据异常！", $e);
        }
    }

    /**
     * @brief 获取所选用户好友列表
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getFriendsList
     * @param user_id 用户id
     *
     */
    public  function getOtherFriendsList(){
        try{
            $uid = $_REQUEST['uid'];
            $page = $_REQUEST['page'];
            $size = $_REQUEST['size'];
            if(empty($page)){
                $page = 1;
            }
            if(empty($size)){
                $size = 10;
            }

            $start = ($page-1)*$size;
            $end   = $size;
            //好友列表
            $friendsModel = M();

            $sql = 'SELECT u.id uid, u.name,u.head_sub_image,u.school from jlxc_user u,jlxc_relationship r
                    WHERE r.delete_flag=0 and r.user_id='.$uid.' and r.friend_id=u.id ORDER BY r.add_date DESC LIMIT '.$start.','.$end;

            $friends = $friendsModel->query($sql);

            $result = array();

            $result['list'] = $friends;
            //是否是最后一页
            if(count($friends) < $size){
                $result['is_last'] = '1';
            }else{
                $result['is_last'] = '0';
            }

            returnJson(1,"查询成功", $result);
            return;

        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 获取所选用户好友列表
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getCommonFriendsList
     * @param user_id 用户id
     * @param current_id 当前访问用户id
     */
    public  function getCommonFriendsList(){
        try{
            $uid = $_REQUEST['uid'];
            $current_id = $_REQUEST['current_id'];

            //共同好友列表
            $friendsModel = M();
            $sql = 'SELECT r1.friend_id, u.head_sub_image FROM (SELECT * FROM jlxc_relationship WHERE user_id='.$uid.' AND delete_flag=0) r1,
                    (SELECT * FROM jlxc_relationship WHERE user_id='.$current_id.' AND delete_flag=0) r2,
                    jlxc_user u WHERE r1.friend_id=r2.friend_id AND r1.friend_id=u.id ORDER BY r2.add_date DESC';

            $friends = $friendsModel->query($sql);

            $result = array();

            $result['list'] = $friends;

            returnJson(1,"查询成功", $result);
            return;

        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }


    /**
     * @brief 获取用户个人信息
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/personalInformation?uid=19
     * @param uid 查看的用户id
     * @param current_id 访问的人id
     */
    public function personalInformation(){
        try{
            $uid = $_REQUEST['uid'];
            $current_id = $_REQUEST['current_id'];
            $userModel = M('jlxc_user');
            $user = $userModel->find($uid);
            if($user){
                //附件列表
                $findImagesModel = M('jlxc_attachment');
                $images = $findImagesModel->field('sub_url')->where(array('delete_flag=0 and type=1 and user_id='.$uid))->limit('3')->order('add_date desc')->select();
                if(isset($images)){
                    $user['image_list'] = $images;
                }else{
                    $user['image_list'] = array();
                }
                //是否已经是好友
                $relationModel = M('jlxc_relationship');
                $relation = $relationModel->where('user_id='.$current_id.' and friend_id='.$uid.' and delete_flag=0')->find();
                if($relation){
                    $user['isFriend'] = '1';
                }else{
                    $user['isFriend'] = '0';
                }
                //最近访问
                $visitModel = M('jlxc_visit');
                //自己不添加
                if($uid != $current_id){
                    $visit = $visitModel->where('user_id='.$uid.' and visitor_id='.$current_id)->find();
                    if($visit){
                        if($visit['delete_flag']==0){
                            $visit['update_date'] = time();
                            $visit['visit_time'] = time();
                        }else{

                            $visit['update_date'] = time();
                            $visit['visit_time'] = time();
                            $visit['resume_date'] = time();
                            $visit['delete_flag'] = 0;
                        }
                        $visitModel->save($visit);

                    }else{
                        $visit = array();
                        $visit['user_id'] = $uid;
                        $visit['visitor_id'] = $current_id;
                        $visit['visit_time'] = time();
                        $visit['add_date'] = time();
                        $visitModel->add($visit);
                    }
                }
                //来访数量
                $visitCount = $visitModel->field('count(1) count')->where('delete_flag=0 AND user_id='.$uid)->find();
                if($visitCount){
                    $user['visit_count'] = $visitCount['count'];
                }else{
                    $user['visit_count'] = '0';
                }
                //好友数量
                $friendCount = $relationModel->field('count(1) count')->where('delete_flag=0 AND user_id='.$uid)->find();
                if($friendCount){
                    $user['friend_count'] = $friendCount['count'];
                }else{
                    $user['friend_count'] = '0';
                }

                //好友列表
                $friendSql = 'SELECT r.friend_id, u.head_sub_image FROM jlxc_user u,jlxc_relationship r
                              WHERE r.delete_flag=0 AND u.id=r.friend_id AND r.user_id="'.$uid.'" ORDER BY r.add_date DESC LIMIT 3';
                $friends = $relationModel->query($friendSql);
                if(isset($friends)){
                    $user['friend_list'] = $friends;
                }else{
                    $user['friend_list'] = array();
                }

//                //共同好友 弃用
//                $sql = 'SELECT r1.friend_id, u.head_sub_image FROM (SELECT * FROM jlxc_relationship WHERE user_id='.$uid.' AND delete_flag=0) r1,
//                    (SELECT * FROM jlxc_relationship WHERE user_id='.$current_id.' AND delete_flag=0) r2,
//                    jlxc_user u WHERE r1.friend_id=r2.friend_id AND r1.friend_id=u.id ORDER BY r2.add_date DESC LIMIT 3';// LIMIT 3
//                $friends = $visitModel->query($sql);
//                if(isset($friends)){
//                    $user['common_friend_list'] = $friends;
//                }else{
//                    $user['common_friend_list'] = array();
//                }

                //共同好友数量
                $sql = 'SELECT count(1) count FROM (SELECT * FROM jlxc_relationship WHERE user_id='.$uid.' AND delete_flag=0) r1,
                    (SELECT * FROM jlxc_relationship WHERE user_id='.$current_id.' AND delete_flag=0) r2,
                    jlxc_user u WHERE r1.friend_id=r2.friend_id AND r1.friend_id=u.id';
                $friends = $visitModel->query($sql);
                if(isset($friends)){
                    $user['common_friend_count'] = $friends[0]['count'];
                }else{
                    $user['common_friend_count'] = array();
                }

                if($images){
                    returnJson(1,"查询成功", $user);
                }else{
                    returnJson(1,"还没有动态", $user);
                }
            }else{
                returnJson(0,"查询失败");
            }
            return;

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

    /**
     * @brief 用户自己的新闻数组
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/userNewsList?user_id=19
     * @param page 页码 默认1
     * @param size 每页数量 默认10
     * @param user_id 用户id
     */
    public function userNewsList(){
        try{

            $page = $_REQUEST['page'];
            $size = $_REQUEST['size'];
            $user_id = $_REQUEST['user_id'];
            if(empty($user_id)){
                returnJson(0,"用户id不能为空");
                return;
            }
            if(empty($page)){
                $page = 1;
            }
            if(empty($size)){
                $size = 10;
            }

            $start = ($page-1)*$size;
            $end   = $size;
            $sql = 'SELECT user.id uid, user.name, user.school, user.head_image,user.head_sub_image, news.id ,
                    news.content_text, news.location, news.comment_quantity,
                    news.browse_quantity, news.like_quantity, news.add_date
                    FROM jlxc_news_content news,jlxc_user user WHERE user.id='.$user_id.' and news.uid=user.id and news.delete_flag=0
                    ORDER BY news.add_date DESC LIMIT '.$start.','.$end;
            //获取用户详细信息
            $findNews = M();
            $newsList = $findNews->query($sql);

            if(isset($newsList)){
                //SELECT id,type,sub_url,url,size,add_date from jlxc_attachment WHERE entity_id=7 and delete_flag = 0
                if(count($newsList) > 0){
                    //处理图片
                    for($i=0; $i<count($newsList); $i++) {
                        $news = $newsList[$i];
                        //该状态发的图片
                        $imageSql = 'SELECT id,type,sub_url,url,size,add_date
                                      from jlxc_attachment WHERE delete_flag = 0 and entity_id='.$news['id'].' ORDER BY url';
                        $images = $findNews->query($imageSql);
                        //返回尺寸
                        for($j=0; $j<count($images); $j++) {
                            $image = new \Think\Image();
                            $path = $images[$j]['url'];
                            $image->open('./Uploads/'.$path);
                            $images[$j]['width']  = $image->size()[0];
                            $images[$j]['height'] = $image->size()[1];
                        }

                        //获取该状态是否这个人赞了
                        $likeModel = M('jlxc_news_like');
                        $oldLike = $likeModel->where('delete_flag=0 and news_id='.$news['id'].' and user_id='.$user_id)->find();
                        if($oldLike){
                            $newsList[$i]['is_like'] = '1';
                        }else{
                            $newsList[$i]['is_like'] = '0';
                        }
                        $newsList[$i]['images'] = $images;
                        $newsList[$i]['comments'] = array();
                        $newsList[$i]['likes'] = array();
                        $newsList[$i]['add_date'] = date('Y-m-d H:i:s', $newsList[$i]['add_date']);
                    }
                }

                $result = array();
                $result['list'] = $newsList;

                //如果没有内容了
//                if(count($newsList) < 1){
//                    $result = array();
//                }
                //是否是最后一页
                if(count($newsList) < $size){
                    $result['is_last'] = '1';
                }else{
                    $result['is_last'] = '0';
                }

                returnJson(1,"查询成功", $result);
                return;
            }else{
                returnJson(0,"查询失败T_T");
            }

            return;

        }catch (Exception $e){

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 删除发布的状态
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/deleteSecondComment
     * @param news_id 新闻id
     *
     */
    public  function deleteNews(){
        try{
            $news = array();
            $news['id'] = $_REQUEST['news_id'];
            $news['delete_date'] = time();
            $news['delete_flag'] = 1;
            //修改状态为删除
            $newsModel = M('jlxc_news_content');
            $ret = $newsModel->save($news);

            $imageModel = M('jlxc_attachment');
            $images = $imageModel->where('entity_id='.$news['id'])->select();
            for($i=0; $i<count($images); $i++){
                $images[$i]['delete_flag'] = 1;
                $images[$i]['delete_date'] = time();
                $imageModel->save($images[$i]);
            }

            if($ret){
                returnJson(1,"删除成功!");
                return;
            }else{
                returnJson(0,"删除失败!");
            }

        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 修改个人信息
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/changePersonalInformation?
     * @param uid 用户id
     * @param field 参数名
     * @param value 参数值
     */
    public function changePersonalInformation(){
        try{
            $uid = $_REQUEST['uid'];
            $field = $_REQUEST['field'];
            $value = $_REQUEST['value'];
            //获取用户详细信息
            $findUser = M('jlxc_user');
            $user = $findUser->where(array('id='.$uid))->find();
            $user[$field] = $value;
            $user['update_date'] = time();
            $updateModel = D('jlxc_user');
            $ret = $updateModel->save($user);
            if($ret){
                returnJson(1,"保存成功");
                return;
            }else{
                returnJson(0,"保存失败!");
            }
            return;

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }


    /**
     * @brief 设置HelloHaID
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/setHelloHaId?
     * @param uid 用户id
     * @param helloha_id helloha_id
     *
     */
    public function setHelloHaId(){
        try{
            $uid = $_REQUEST['uid'];
            $helloha_id = $_REQUEST['helloha_id'];
            if(empty($helloha_id)){
                returnJson(0,"号码不能为空!",array('flag'=>0));
                return;
            }

            //获取用户详细信息
            $findUser = M('jlxc_user');
            //helloaID默认大小写敏感
            $find = $findUser->where('helloha_id="'.$helloha_id.'"')->find();
            if($find){
                returnJson(0,"该hellohaId已经存在!",array('flag'=>0));
                return;
            }

            $user = $findUser->where(array('id='.$uid))->find();
            if($user){
                //如果没有则添加
                if(empty($user['helloha_id'])){

                    //必须是字母数字下划线
                    if(preg_match(HELLOHA_ID, $helloha_id)) {

                        $user['helloha_id'] = $helloha_id;
                        $user['update_date'] = time();
                        $ret = $findUser->save($user);
                        if($ret){
                            returnJson(1,"设置成功~",array('helloha_id'=>$user['helloha_id']));
                        }else{
                            returnJson(0,"保存失败!",array('flag'=>0));
                        }
                        return;
                    }else{
                        //不是字母数字下划线就不行
                        returnJson(0,"格式不对！",array('flag'=>0));
                        return;
                    }

                }else{
                    //有了不让改
                    returnJson(0,"已经设置过了！不能重复设置",array('flag'=>1,'helloha_id'=>$user['helloha_id']));
                    return;
                }
            }else{
                returnJson(0,"不存在这个人。。!",array('flag'=>0));
                return;
            }

        }catch (Exception $e){

            returnJson(0,"数据异常！",array('flag'=>0));
        }
    }

    /**
     * @brief 修改个人信息中的图片:头像 背景图
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/changeInformationImage?
     * @param uid 用户id
     * @param field 参数名
     */
    public function changeInformationImage(){
        try{

            $uid = $_REQUEST['uid'];
            $field = $_REQUEST['field'];
            if(strlen($field) < 1){
                returnJson(0,"保存失败!");
                return;
            }
            //获取用户详细信息
            $findUser = M('jlxc_user');
            $user = $findUser->where(array('id='.$uid))->find();

            $info = null;
            $upload = null;

            if(!empty($_FILES)){
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize   =     10*1024*1024 ;// 设置附件上传大小
                $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
                $upload->savePath  =     ''; // 设置附件上传（子）目录
                $upload->saveName  =     '';
                // 上传文件
                $info   =   $upload->upload();
            }
            $retPath = '';
            //上传成功
            if($info) {
                $image = new \Think\Image();
                foreach($info as $file){

                    $user[$field] = $file['savepath'].$file['savename'];
                    $path = $file['savepath'].$file['savename'];
                    //如果是头像则制作缩略图
                    if($field == 'head_image'){
                        $image->open('./Uploads/'.$path);
                        //缩略图地址前半部分
                        $preffix = substr($path, 0, strlen($path)-4);
                        //后缀
                        $suffix  = substr($path, strlen($path)-4);
                        //拼接
                        $subpath = $preffix.'_sub'.$suffix;
                        $user['head_sub_image'] = $subpath;
                        $image->thumb(270, 270)->save('./Uploads/'.$subpath);
                    }
                    $retPath = $path;
                }
            }else{
                returnJson(0,"保存失败!");
                return;
            }

            $user['update_date'] = time();
            $ret = $findUser->save($user);

            $image = array('image'=>$user[$field]);
            if($field == 'head_image'){
                $image = array('image'=>$retPath, 'subimage'=>$user['head_sub_image']);
            }

            if($ret){
                returnJson(1,"保存成功", $image);
                return;
            }else{
                returnJson(0,"保存失败!");
            }

            return;

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

    /**
     * @brief 举报功能
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/reportOffence?
     * @param uid 举报人的id
     * @param report_uid 要举报的用户id
     * @param report_content 举报内容
     */
    public function reportOffence(){
        try{

            $uid = $_REQUEST['uid'];
            $report_uid = $_REQUEST['report_uid'];
            $report_content = $_REQUEST['report_content'];

            if(empty($uid)){
                returnJson(0,"举报人不能为空");
                return;
            }

            if(empty($report_uid)){
                returnJson(0,"被举报人不能为空");
                return;
            }

            if(empty($report_content)){
                returnJson(0,"举报内容不能为空");
                return;
            }

            $report = array('uid'=>$uid,'report_uid'=>$report_uid, 'report_content'=>$report_content);

            $reportModel = M('jlxc_report');
            $ret = $reportModel->add($report);

            if($ret){
                returnJson(1,"举报成功,我们会尽快为您处理！");
                return;
            }else{
                returnJson(0,"举报失败!");
            }

            return;

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

    /**
     * @brief 举报功能
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getLastestVersion
     * @param sys 系统 1为安卓 2为iOS
     */
    public function getLastestVersion(){
        try{
            $sys = $_REQUEST['sys'];
            if(empty($sys)){
                $sys = '1';
            }

            $versionModel = M('jlxc_version');
            $sysModel = $versionModel->where('device_code='.$sys)->find();

            if(count($sysModel)>0){
                returnJson(1,"获取成功！", $sysModel);
                return;
            }else{
                returnJson(0,"获取失败!");
            }

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

/////////////////////////////////////////////首页状态流部分////////////////////////////////////////////////////////////
    /**
     * @brief 发布状态
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/publishNews?
     * @param uid 用户id
     * @param content_text 内容
     * @param location 地理位置
     * @param  //file
     */
    public function publishNews(){
        try{

            $uid = $_REQUEST['uid'];
            $content_text = $_REQUEST['content_text'];
            $location = $_REQUEST['location'];

            $findUser = M('jlxc_user');
            $user = $findUser->find($uid);
            if(!$user){
                returnJson(0 ,'该用户不存在T_T');
                return;
            }

            $info = null;
            $upload = null;
            if(!empty($_FILES)){
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize   =     10*1024*1024 ;// 设置附件上传大小
                $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
                $upload->savePath  =     ''; // 设置附件上传（子）目录
                $upload->saveName  =     '';
                // 上传文件
                $info   =   $upload->upload();
            }

            //获取用户详细信息
            $news = array();
            $news['uid'] = $uid;
            $news['content_text'] = $content_text;
            $news['location'] = $location;
            $news['add_date'] = time();
            if(!empty($_FILES)) {
                $news['has_picture'] = 1;
            }
            //添加数据
            $newsModel = D('jlxc_news_content');
            $attachmentModel = D('jlxc_attachment');

            $newsModel->startTrans();
            $ret = $newsModel->add($news);

            if($ret){
                $attachment = array();
                //返回值
                $retJson = array();
                //上传成功
                if($info) {
                    $image = new \Think\Image();
                    foreach($info as $file){
//                    $user['head_image'] = $file['savepath'].$file['savename'];
                        $path = $file['savepath'].$file['savename'];
                        //返回值添加
                        $retJson[$file['savename']] = $path;
                        $image->open('./Uploads/'.$path);
                        //缩略图地址前半部分
                        $preffix = substr($path, 0, strlen($path)-4);
                        //后缀
                        $suffix  = substr($path, strlen($path)-4);
                        //拼接
                        $subpath = $preffix.'_sub'.$suffix;

                        $single_file = array();
                        $single_file['user_id'] = $uid;
                        $single_file['entity_id'] = $ret;
                        $single_file['type'] = get_image_type();
                        $single_file['sub_url'] = $subpath;
                        $single_file['url'] = $path;
                        $single_file['size'] = filesize('./Uploads/'.$path);
                        $single_file['add_date'] = time();
                        $image->thumb(270, 270)->save('./Uploads/'.$subpath);

                        array_push($attachment, $single_file);

                    }
                    $aret = $attachmentModel->addAll($attachment);
                    if($aret){
                        $newsModel->commit();
                        returnJson(1,'保存成功', $retJson);
                        return;
                    }else{
                        $newsModel->rollback();
                        returnJson(0,'保存失败!');
                        return;
                    }
                }

                $newsModel->commit();
                returnJson(1,'保存成功', '');
                return;

            }else{
                $newsModel->rollback();
                returnJson(0,'保存失败!');
            }

            return;

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }


    /**
     * @brief 新闻列表
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/newsList
     * @param page 页码 默认1
     * @param size 每页数量 默认10
     * @param user_id 用户id
     * @param frist_time 第一条数据的时间
     *
     */
    public function newsList(){
        try{

            $page = $_REQUEST['page'];
            $size = $_REQUEST['size'];
            $user_id = $_REQUEST['user_id'];
            $frist_time = $_REQUEST['frist_time'];

            if(empty($user_id)){
                returnJson(0,"用户Id不能为空");
                return;
            }
            if(empty($page)){
                $page = 1;
            }
            if(empty($size)){
                $size = 10;
            }
            if(empty($frist_time)){
                $frist_time = time();
            }

            /////////////////////////////////查找人群处理-开始/////////////////////////////////////
            $userModel = M('jlxc_user');
            $user=$userModel->where('id='.$user_id)->find();
            $ratio = 0.8;
            if($user['sex']=1){
                $ratio = 0.2;
            }
            //我的好友
            $relationshipModel = M('jlxc_relationship');
            $friendList = $relationshipModel->where('delete_flag=0 and user_id='.$user_id)->select();
            $leftCount = 150-count($friendList);

            $friends = array();
            foreach($friendList as $friend){
                array_push($friends, $friend['friend_id']);
            }
            $inFriendFriends = implode(',',$friends);
            if(empty($inFriendFriends)){
                $inFriendFriends = '0';
            }
            array_push($friends, $user_id);
            $notInFriends = implode(',',$friends);
            if(empty($notInFriends)){
                $notInFriends = '0';
            }

            //先计算人数 然后计算比例
            //学校人数
            $schoolCount = $userModel->field('count(1) count')->where('delete_flag=0 and school_code="'.$user['school_code'].'"')->find();
            //好友的好友人数
            $friendFriendCount = $relationshipModel->where('delete_flag=0 and user_id in ('.$inFriendFriends.')'.'
                             ')->group('friend_id')->select();
            //同区不同校的人数
            $schoolModel = M('jlxc_school');
            $school = $schoolModel->where('code='.$user['school_code'])->find();
            $districtSql = 'SELECT count(1) count FROM jlxc_user u,jlxc_school s
                    WHERE u.school_code=s.code AND s.code<>'.$user['school_code'].' AND s.district_code='.$school['district_code'];
            $districtCount = $schoolModel->query($districtSql);
            //同城不同区的人数
            $citySql = 'SELECT count(1) count FROM jlxc_user u,jlxc_school s
                    WHERE u.school_code=s.code AND s.district_code<>'.$school['district_code'].' AND s.city_code='.$school['city_code'];
            $cityCount = $schoolModel->query($citySql);

            //学校
            $nowSchoolCount = $schoolCount['count'];
            //好友的好友
            $nowFriendCount = count($friendFriendCount);
            //同区
            $nowDistrictCount = $districtCount[0]['count'];
            //同城
            $nowCityCount = $cityCount[0]['count'];

            $oriArr = array($nowSchoolCount, $nowFriendCount, $nowDistrictCount, $nowCityCount);
            $leftCount = (int)($leftCount*14/15.0);
            //比例数组
            $countArr = getFriendProportion($oriArr, $leftCount);

            //学校的人
            $schoolGirlList = $userModel->where('delete_flag=0 and sex=1 and school_code="'.$user['school_code'].'"'.' and id not in ('.$notInFriends.')')
                ->limit(0,(int)($countArr[0]*$ratio))->select();
            $schoolBoyList = $userModel->where('delete_flag=0 and sex=0 and school_code="'.$user['school_code'].'"'.' and id not in ('.$notInFriends.')')
                ->limit(0,$countArr[0]-count($schoolGirlList))->select();

            //好友in
            $friendFriends = array();
            foreach($friendList as $friend){
                array_push($friendFriends, $friend['friend_id']);
            }
            $inFriendFriends = implode(',',$friendFriends);
            if(empty($inFriendFriends)){
                $inFriendFriends = '0';
            }

            //好友not in
            $noFriendFriends = array();
            foreach($friendList as $friend){
                array_push($noFriendFriends, $friend['friend_id']);
            }
            //学校女孩
            foreach($schoolGirlList as $friend){
                array_push($noFriendFriends, $friend['id']);
            }
            //学校男孩
            foreach($schoolBoyList as $friend){
                array_push($noFriendFriends, $friend['id']);
            }
            array_push($noFriendFriends, $user_id);
            $notInFriendFriends = implode(',',$noFriendFriends);
            if(empty($inFriendFriends)){
                $inFriendFriends = '0';
            }

            //好友的好友
            //男
            $girlSql = 'SELECT u.id,r.user_id fid FROM jlxc_relationship r, jlxc_user u
                  WHERE u.sex=1 AND u.id=r.friend_id and r.delete_flag=0 and r.user_id in ('.$inFriendFriends.')'.'
                  and r.friend_id not in ('.$notInFriendFriends.') GROUP BY r.friend_id LIMIT 0,'.((int)($countArr[1]*$ratio));
            $girlFriendFriendList = $relationshipModel->query($girlSql);
            //女
            $boySql = 'SELECT u.id,r.user_id fid FROM jlxc_relationship r, jlxc_user u
                  WHERE u.sex=0 AND u.id=r.friend_id and r.delete_flag=0 and r.user_id in ('.$inFriendFriends.')'.'
                  and r.friend_id not in ('.$notInFriendFriends.') GROUP BY r.friend_id LIMIT 0,'.($countArr[1]-count($girlFriendFriendList));
            $boyFriendFriendList = $relationshipModel->query($boySql);

            $schoolModel = M('jlxc_school');
            $school = $schoolModel->where('code='.$user['school_code'])->find();

            //同区的人
            //朋友的朋友女孩
            foreach($girlFriendFriendList as $friend){
                array_push($noFriendFriends, $friend['id']);
            }
            //朋友的朋友男孩
            foreach($boyFriendFriendList as $friend){
                array_push($noFriendFriends, $friend['id']);
            }
            $notInDistrictFriends = implode(',',$noFriendFriends);
            if(empty($notInDistrictFriends)){
                $notInDistrictFriends = '0';
            }

            //女
            $girlSql = 'SELECT u.id,u.school_code FROM jlxc_user u,jlxc_school s
                    WHERE u.sex=1 AND u.school_code=s.code AND s.district_code='.$school['district_code'].'
                    AND u.id not in ('.$notInDistrictFriends.') LIMIT 0,'.((int)($countArr[2]*$ratio));
            $girlDistrictList = $schoolModel->query($girlSql);
            //男
            $boySql = 'SELECT u.id,u.school_code FROM jlxc_user u,jlxc_school s
                  WHERE u.sex=0 AND u.school_code=s.code AND s.district_code='.$school['district_code'].'
                  AND u.id not in ('.$notInDistrictFriends.') LIMIT 0,'.($countArr[2]-count($girlDistrictList));
            $boyDistrictList = $schoolModel->query($boySql);

            //同城的人
            //同区女孩
            foreach($girlDistrictList as $friend){
                array_push($noFriendFriends, $friend['id']);
            }
            //同区男孩
            foreach($boyDistrictList as $friend){
                array_push($noFriendFriends, $friend['id']);
            }
            $notInDistrictFriends = implode(',',$noFriendFriends);
            if(empty($notInDistrictFriends)){
                $notInDistrictFriends = '0';
            }
            //女
            $girlSql = 'SELECT u.id,u.school_code FROM jlxc_user u,jlxc_school s
                    WHERE u.sex=1 AND u.school_code=s.code AND s.city_code='.$school['city_code'].'
                    AND u.id not in ('.$notInDistrictFriends.') LIMIT 0,'.((int)($countArr[3]*$ratio));
            $girlCityList = $schoolModel->query($girlSql);
            //男
            $boySql = 'SELECT u.id,u.school_code FROM jlxc_user u,jlxc_school s
                  WHERE u.sex=0 AND u.school_code=s.code AND s.city_code='.$school['city_code'].'
                  AND u.id not in ('.$notInDistrictFriends.') LIMIT 0,'.($countArr[3]-count($girlCityList));
            $boyCityList = $schoolModel->query($boySql);

            //完全版集合
            $selectList = array();
            //查询集合
            $inList = array();
            //type 1自己 2好友 3同校 4朋友的朋友 5同区 6同城
            $selectList[$user_id]=array('type'=>'1');
            array_push($inList, $user_id);
            //你的好友
            foreach($friendList as $friend){
                $selectList[$friend['friend_id']]=array('type'=>'2');
                array_push($inList, $friend['friend_id']);
            }
            //学校女孩
            foreach($schoolGirlList as $friend){
                $selectList[$friend['id']]=array('type'=>'3');
                array_push($inList, $friend['id']);
            }
            //学校男孩
            foreach($schoolBoyList as $friend){
                $selectList[$friend['id']]=array('type'=>'3');
                array_push($inList, $friend['id']);
            }
            //朋友的朋友女孩
            foreach($girlFriendFriendList as $friend){
                $selectList[$friend['id']]=array('type'=>'4','fid'=>$friend['fid']);
                array_push($inList, $friend['id']);
            }
            //朋友的朋友男孩
            foreach($boyFriendFriendList as $friend){
                $selectList[$friend['id']]=array('type'=>'4','fid'=>$friend['fid']);
                array_push($inList, $friend['id']);
            }
            //同区女孩
            foreach($girlDistrictList as $friend){
                $selectList[$friend['id']]=array('type'=>'5','school_code'=>$friend['school_code']);
                array_push($inList, $friend['id']);
            }
            //同区男孩
            foreach($boyDistrictList as $friend){
                $selectList[$friend['id']]=array('type'=>'5','school_code'=>$friend['school_code']);
                array_push($inList, $friend['id']);
            }
            //同城女孩
            foreach($girlCityList as $friend){
                $selectList[$friend['id']]=array('type'=>'6','school_code'=>$friend['school_code']);
                array_push($inList, $friend['id']);
            }
            //同城男孩
            foreach($boyCityList as $friend){
                $selectList[$friend['id']]=array('type'=>'6','school_code'=>$friend['school_code']);
                array_push($inList, $friend['id']);
            }

            //其他随机填充
            foreach($girlCityList as $friend){
                array_push($noFriendFriends, $friend['id']);
            }
            foreach($boyCityList as $friend){
                array_push($noFriendFriends, $friend['id']);
            }
            $notInDistrictFriends = implode(',',$noFriendFriends);
            if(empty($notInDistrictFriends)){
                $notInDistrictFriends = '0';
            }
            $sql = 'SELECT * FROM jlxc_user WHERE delete_flag=0 AND id NOT IN('.$notInDistrictFriends.') ORDER BY RAND() limit '.(150-count($noFriendFriends));
            $leftList = $userModel->query($sql);
            //剩余填充
            foreach($leftList as $friend){
                $selectList[$friend['id']]=array('type'=>'7');
                array_push($inList, $friend['id']);
            }

            $inStr = implode(',',$inList);
            /////////////////////////////////查找人群处理-结束/////////////////////////////////////


            $start = ($page-1)*$size;
            $end   = $size;
            $sql = 'SELECT user.id uid, user.name, user.school, user.head_image,user.head_sub_image, news.id ,
                    news.content_text, news.location, news.comment_quantity,
                    news.browse_quantity, news.like_quantity, news.add_date
                    FROM jlxc_news_content news,jlxc_user user WHERE news.add_date<='.$frist_time.' and news.uid = user.id and news.delete_flag = 0
                    and user.id in ('.$inStr.') ORDER BY news.add_date DESC LIMIT '.$start.','.$end;

            //获取用户详细信息
            $findNews = M();
            $newsList = $findNews->query($sql);

            if(isset($newsList)){
                //SELECT id,type,sub_url,url,size,add_date from jlxc_attachment WHERE entity_id=7 and delete_flag = 0
                if(count($newsList) > 0){
                    //处理图片
                    for($i=0; $i<count($newsList); $i++) {
                        $news = $newsList[$i];
                        //该状态发的图片
                        $imageSql = 'SELECT id,type,sub_url,url,size,add_date
                                      from jlxc_attachment WHERE delete_flag = 0 and entity_id='.$news['id'].' ORDER BY url';
                        $images = $findNews->query($imageSql);
                        //返回尺寸
                        for($j=0; $j<count($images); $j++) {
                            $image = new \Think\Image();
                            $path = $images[$j]['url'];
                            $image->open('./Uploads/'.$path);
                            $images[$j]['width']  = $image->size()[0];
                            $images[$j]['height'] = $image->size()[1];
                        }

                        //获取该状态的评论
                        $commentSql = 'SELECT c.id, u.name, u.head_image, u.head_sub_image, c.add_date, c.user_id,c.comment_content, c.like_quantity,
                                       c.like_quantity from jlxc_news_comment c, jlxc_user u WHERE c.user_id=u.id and c.delete_flag = 0
                                        and c.news_id='.$news['id'].' ORDER BY c.like_quantity DESC LIMIT 3';
                        $comments = $findNews->query($commentSql);
                        $comments = array_replace_null($comments);
                        //获取该状态点赞的人
                        //SELECT * FROM jlxc_news_like WHERE news_id=22 AND delete_flag = 0 LIMIT 8
                        $likeSql = 'SELECT l.user_id,u.head_image, u.head_sub_image FROM jlxc_news_like l,jlxc_user u
                                    WHERE l.user_id = u.id and l.news_id='.$news['id'].' AND l.delete_flag = 0 order by l.add_date DESC LIMIT 12';
                        $likes = $findNews->query($likeSql);
                        //获取该状态是否这个人赞了
                        $likeModel = M('jlxc_news_like');
                        $oldLike = $likeModel->where('delete_flag=0 and news_id='.$news['id'].' and user_id='.$user_id)->find();
                        if($oldLike){
                            $newsList[$i]['is_like'] = '1';
                        }else{
                            $newsList[$i]['is_like'] = '0';
                        }
                        $newsList[$i]['images'] = $images;
                        $newsList[$i]['comments'] = $comments;
                        $newsList[$i]['likes'] = $likes;
                        $newsList[$i]['add_time'] = $newsList[$i]['add_date'];
                        $newsList[$i]['add_date'] = date('Y-m-d H:i:s', $newsList[$i]['add_date']);

                        //设置类型
                        $newsList[$i]['type'] = $selectList[$news['uid']];
                        //type 1自己 2好友 3同校 4朋友的朋友 5同区 6同城
                        //默认为空
                        $recommendlList[$i]['type']['content'] = '';
                        //朋友的朋友 姓名
                        if($selectList[$news['uid']]['type']==4){
                            $friendUser = $userModel->where('id='.$selectList[$news['uid']]['fid'])->find();
                            $newsList[$i]['type']['content'] = $friendUser['name'].'的朋友';
                        }
                        //同区的
                        if($selectList[$news['uid']]['type']==5){
                            $friendUser = $schoolModel->where('code='.$selectList[$news['uid']]['school_code'])->find();
                            $newsList[$i]['type']['content'] = $friendUser['district_name'].'的同学';
                        }
                        //同城的
                        if($selectList[$news['uid']]['type']==6){
                            $friendUser = $schoolModel->where('code='.$selectList[$news['uid']]['school_code'])->find();
                            $newsList[$i]['type']['content'] = $friendUser['city_name'].'的同学';
                        }
                    }
                }

                $result = array();
                $result['list'] = $newsList;

                //是否是最后一页
                if(count($newsList) < $size){
                    $result['is_last'] = '1';
                }else{
                    $result['is_last'] = '0';
                }

                returnJson(1,"查询成功", $result);
                return;
            }else{
                returnJson(0,"查询失败T_T");
            }

            return;

        }catch (Exception $e){

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 新闻列表
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/schoolNewsList
     * @param page 页码 默认1
     * @param size 每页数量 默认10
     * @param user_id 用户id
     * @param school_code 学校代码
     * @param frist_time 第一条状态的时间
     */
    public function schoolNewsList(){
        try{

            $page = $_REQUEST['page'];
            $size = $_REQUEST['size'];
            $user_id = $_REQUEST['user_id'];
            $school_code = $_REQUEST['school_code'];
            $frist_time = $_REQUEST['frist_time'];

            if(empty($user_id)){
                returnJson(0,"用户Id不能为空");
                return;
            }
            if(empty($school_code)){
                returnJson(0,"学校代码不能为空");
                return;
            }
            if(empty($page)){
                $page = 1;
            }
            if(empty($size)){
                $size = 10;
            }
            if(empty($frist_time)){
                $frist_time = time();
            }

            $start = ($page-1)*$size;
            $end   = $size;
            $sql = 'SELECT user.id uid, user.name, user.school, user.head_image,user.head_sub_image, news.id ,
                    news.content_text, news.location, news.comment_quantity,
                    news.browse_quantity, news.like_quantity, news.add_date
                    FROM jlxc_news_content news,jlxc_user user WHERE news.add_date<='.$frist_time.' and user.school_code='.$school_code.' and news.uid = user.id and news.delete_flag = 0
                    ORDER BY news.add_date DESC LIMIT '.$start.','.$end;

            //获取用户详细信息
            $findNews = M();
            $newsList = $findNews->query($sql);

            if(isset($newsList)){
                //SELECT id,type,sub_url,url,size,add_date from jlxc_attachment WHERE entity_id=7 and delete_flag = 0
                if(count($newsList) > 0){

                    //处理图片
                    for($i=0; $i<count($newsList); $i++) {
                        $news = $newsList[$i];
                        //该状态发的图片
                        $imageSql = 'SELECT id,type,sub_url,url,size,add_date
                                      from jlxc_attachment WHERE delete_flag = 0 and entity_id='.$news['id'].' ORDER BY url';
                        $images = $findNews->query($imageSql);
                        //返回尺寸
                        for($j=0; $j<count($images); $j++) {
                            $image = new \Think\Image();
                            $path = $images[$j]['url'];
                            $image->open('./Uploads/'.$path);
                            $images[$j]['width']  = $image->size()[0];
                            $images[$j]['height'] = $image->size()[1];
                        }

                        //获取该状态的评论
                        $commentSql = 'SELECT c.id, u.name, u.head_image, u.head_sub_image, c.add_date, c.user_id,c.comment_content, c.like_quantity,
                                       c.like_quantity from jlxc_news_comment c, jlxc_user u WHERE c.user_id=u.id and c.delete_flag = 0
                                        and c.news_id='.$news['id'].' ORDER BY c.like_quantity DESC LIMIT 3';
                        $comments = $findNews->query($commentSql);
                        $comments = array_replace_null($comments);
                        //获取该状态点赞的人
                        //SELECT * FROM jlxc_news_like WHERE news_id=22 AND delete_flag = 0 LIMIT 8
                        $likeSql = 'SELECT l.user_id,u.head_image, u.head_sub_image FROM jlxc_news_like l,jlxc_user u
                                    WHERE l.user_id = u.id and l.news_id='.$news['id'].' AND l.delete_flag = 0 order by l.add_date DESC LIMIT 12';
                        $likes = $findNews->query($likeSql);
                        //获取该状态是否这个人赞了
                        $likeModel = M('jlxc_news_like');
                        $oldLike = $likeModel->where('delete_flag=0 and news_id='.$news['id'].' and user_id='.$user_id)->find();
                        if($oldLike){
                            $newsList[$i]['is_like'] = '1';
                        }else{
                            $newsList[$i]['is_like'] = '0';
                        }

                        //赋值
                        $newsList[$i]['images'] = $images;
                        $newsList[$i]['comments'] = $comments;
                        $newsList[$i]['likes'] = $likes;
                        $newsList[$i]['add_time'] = $newsList[$i]['add_date'];
                        $newsList[$i]['add_date'] = date('Y-m-d H:i:s', $newsList[$i]['add_date']);
                    }
                }

                $result = array();
                $result['list'] = $newsList;

                //是否是最后一页
                if(count($newsList) < $size){
                    $result['is_last'] = '1';
                }else{
                    $result['is_last'] = '0';
                }

                //如果是首页 找出该学校活跃度最高的前五人
                if($page == 1){
                    $studentSql = 'SELECT u.id uid, u.head_sub_image FROM jlxc_user u LEFT JOIN jlxc_news_content n ON(n.uid=u.id)
                                    WHERE u.school_code='.$school_code.' GROUP BY u.id ORDER BY RAND() DESC LIMIT 10';
                    $students = $findNews->query($studentSql);
                    $result['info'] = $students;
                }

                returnJson(1,"查询成功", $result);
                return;
            }else{
                returnJson(0,"查询失败T_T");
            }

            return;

        }catch (Exception $e){

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 新闻列表
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getSchoolStudentList
     * @param school_code 学校代码
     */
    public function getSchoolStudentList(){
        try{

            $school_code = $_REQUEST['school_code'];

            if(empty($school_code)){
                returnJson(0,"学校代码不能为空");
                return;
            }

            //获取用户详细信息
            $studentModel = M();
            $studentSql = 'SELECT u.id uid, u.name, u.sex, u.head_sub_image,COUNT(1) count FROM jlxc_user u LEFT JOIN jlxc_news_content n ON(n.uid=u.id)
                                    WHERE u.school_code='.$school_code.' GROUP BY u.id ORDER BY count DESC';
            $students = $studentModel->query($studentSql);
            returnJson(1,"查询成功", array('list'=>$students));

            return;

        }catch (Exception $e){

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 点赞列表
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getNewsLikeList?uid=19
     * @param news_id 新闻id
     *
     */
    public function getNewsLikeList(){
        try{
            $news_id = $_REQUEST['news_id'];
            $page = $_REQUEST['page'];
            $size = $_REQUEST['size'];
            if(empty($page)){
                $page = 1;
            }
            if(empty($size)){
                $size = 10;
            }

            $start = ($page-1)*$size;
            $end   = $size;
            //最近访问列表
            $visitImagesModel = M();
            $likeSql = 'SELECT l.user_id, u.name, u.head_image, u.head_sub_image FROM jlxc_news_like l,jlxc_user u
                        WHERE l.user_id = u.id and l.news_id='.$news_id.' AND l.delete_flag = 0 order by l.add_date DESC LIMIT '.$start.','.$end;
            $likes = $visitImagesModel->query($likeSql);

            $result = array();
            $result['list'] = $likes;

            //是否是最后一页
            if(count($likes) < $size){
                $result['is_last'] = '1';
            }else{
                $result['is_last'] = '0';
            }

            returnJson(1,"查询成功", $result);
            return;

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

    /**
     * @brief 新闻详情
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/newsDetail
     * @param news_id 新闻id
     * @param user_id 用户id
     */
    public function newsDetail(){
        try{

            $news_id = $_REQUEST['news_id'];
            $user_id = $_REQUEST['user_id'];
            $newsModel = M('jlxc_news_content');
            $news = $newsModel->where('id='.$news_id.' and delete_flag = 0')->find();
            if(!$news){
                returnJson(0,"该状态不存在TAT!");
                return;
            }

            //最近浏览次数处理
            //最近访问
            $visitModel = M('jlxc_news_visit');
            //自己不添加
            if($user_id != $news_id){
                $visit = $visitModel->where('news_id='.$news_id.' and visitor_id='.$user_id)->find();
                if($visit){
//                    //被删除就从新处理
//                    if($visit['delete_flag']==0){
//                        $visit['update_date'] = time();
//                        $visit['visit_time'] = time();
//                    }else{
//
//                        $visit['update_date'] = time();
//                        $visit['visit_time'] = time();
//                        $visit['resume_date'] = time();
//                        $visit['delete_flag'] = 0;
//                    }
                    //存在就更新一次
                    $visit['update_date'] = time();
                    $visit['visit_time'] = time();
                    $visitModel->save($visit);

                }else{
                    //新的访问
                    $visit = array();
                    $visit['news_id'] = $news_id;
                    $visit['visitor_id'] = $user_id;
                    $visit['visit_time'] = time();
                    $visit['add_date'] = time();
                    $ret = $visitModel->add($visit);

                    //保存成功
                    if($ret){
                        //新的访问 浏览次数+1
                        $news['browse_quantity']++;
                        $ret = $newsModel->save($news);
                        if($ret){
                            //加不加成功都无所谓
                        }
                    }

                }
            }

            //从新查出关联信息
            $sql = 'SELECT user.id uid, user.name, user.school, user.head_image,user.head_sub_image, news.id ,
                    news.content_text, news.location, news.comment_quantity,
                    news.browse_quantity, news.like_quantity, news.add_date
                    FROM jlxc_news_content news,jlxc_user user WHERE news.id='.$news_id.' and news.uid = user.id and news.delete_flag = 0';
            $news = $newsModel->query($sql)[0];

            $findNews = M();
            //获取该状态的评论
            $commentSql = 'SELECT c.id, u.name, u.head_image, u.head_sub_image, c.add_date, c.user_id,c.comment_content,c.like_quantity
                            from jlxc_news_comment c, jlxc_user u WHERE c.user_id=u.id and c.delete_flag = 0
                            and c.news_id='.$news['id'].' ORDER BY c.add_date';
            $comments = $findNews->query($commentSql);
            $comments = array_replace_null($comments);
//
            $secondCommentModel = M('jlxc_news_second_comment');
            for($i=0; $i<count($comments); $i++) {
                $second_sql = 'SELECT s.id, s.name, s.top_comment_id, s.reply_uid, s.reply_comment_id, su.name reply_name ,s.add_date, s.user_id,
                              s.comment_content from (SELECT c.id, u.name, c.top_comment_id, c.reply_uid, c.reply_comment_id, c.add_date,
                              c.user_id,c.comment_content from jlxc_news_second_comment c, jlxc_user u WHERE c.top_comment_id='.$comments[$i]['id'].'
                              and c.user_id=u.id and c.delete_flag = 0) as s, jlxc_user su WHERE s.reply_uid = su.id ORDER BY s.add_date';

                $secondComment = $secondCommentModel->query($second_sql);
                $comments[$i]['secondComment'] = $secondComment;
            }

            //该状态发的图片
            $imageSql = 'SELECT id,type,sub_url,url,size,add_date
                                      from jlxc_attachment WHERE delete_flag = 0 and entity_id='.$news['id'].' ORDER BY url';
            $images = $findNews->query($imageSql);
            //返回尺寸
            for($j=0; $j<count($images); $j++) {
                $image = new \Think\Image();
                $path = $images[$j]['url'];
                $image->open('./Uploads/'.$path);
                $images[$j]['width']  = $image->size()[0];
                $images[$j]['height'] = $image->size()[1];
            }
            //获取该状态点赞的人
            //SELECT * FROM jlxc_news_like WHERE news_id=22 AND delete_flag = 0 LIMIT 8
            $likeSql = 'SELECT l.user_id,u.head_image ,u.head_sub_image FROM jlxc_news_like l,jlxc_user u
                        WHERE l.user_id = u.id and l.news_id='.$news['id'].' AND l.delete_flag = 0 order by l.add_date DESC LIMIT 12';
            $likes = $findNews->query($likeSql);
            //获取该状态是否这个人赞了
            $likeModel = M('jlxc_news_like');
            $oldLike = $likeModel->where('delete_flag=0 and news_id='.$news['id'].' and user_id='.$user_id)->find();
            if($oldLike){
                $news['is_like'] = '1';
            }else{
                $news['is_like'] = '0';
            }
            for($i=0; $i<count($comments); $i++){
                $comments[$i]['add_date'] = date('Y-m-d H:i:s', $comments[$i]['add_date']);
            }

            $news['images'] = $images;
            $news['comments'] = $comments;
            $news['likes'] = $likes;
            $news['add_date'] = date('Y-m-d H:i:s', $news['add_date']);
            returnJson(1,"查询成功", $news);
            return;

        }catch (Exception $e){

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 发布一级评论
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/sendComment
     * @param news_id 状态id
     * @param user_id 用户id
     * @param comment_content 评论的内容
     *
     */
    public  function sendComment(){
        try{
            $comment = array();
            $comment['news_id'] = $_REQUEST['news_id'];
            $comment['user_id'] = $_REQUEST['user_id'];
            $comment['comment_content'] = $_REQUEST['comment_content'];
            $comment['add_date'] = time();

            $newsModel = M('jlxc_news_content');
            $news = $newsModel->where('id='.$comment['news_id'].' and delete_flag = 0')->find();
            if(!$news){
                returnJson(0,"该状态不存在TAT!");
                return;
            }
            if(empty($comment['comment_content'])){
                returnJson(0,"评论内容不能为空");
                return;
            }

            $commentModel = D('jlxc_news_comment');
            $ret = $commentModel->add($comment);
            if($ret){

                $news['comment_quantity'] ++;
                $newsModel->save($news);
                $comment = $commentModel->find($ret);
                $comment['add_date'] = date('Y-m-d H:i:s', $comment['add_date']);
                returnJson(1,"发送成功", $comment);

                //如果评论的自己 则推送通知
                if($news['uid'] != $comment['user_id']){
                    $imagePath = '';
                    if($news['has_picture']){
                        //该状态发的图片
                        $imageSql = 'SELECT sub_url
                                      from jlxc_attachment WHERE delete_flag = 0 and entity_id='.$news['id'].' ORDER BY id DESC';
                        $images = $commentModel->query($imageSql);
                        if(!empty($images)){
                            $imagePath = $images[0]['sub_url'];
                        }
                    }

                    //获取头像
                    $userModel = M('jlxc_user');
                    //发的人
                    $user = $userModel->field('name,head_sub_image')->where('id='.$comment['user_id'])->find();
                    //主人
                    $newsUser = $userModel->field('name')->where('id='.$news['uid'])->find();
                    //要发送的内容
                    $content = array(
                        'uid'=>$comment['user_id'],
                        'name'=>$user['name'],
                        'head_image'=>$user['head_sub_image'],
                        'comment_content'=>$comment['comment_content'],
                        'news_id'=>$news['id'],
                        'news_content'=>$news['content_text'],
                        'news_image'=>$imagePath,
                        'news_user_name'=>$newsUser['name'],
                        'push_time'=>date('Y-m-d H:i:s', time())
                    );
                    //推送通知
                    pushMessage($news['uid'],$content,2);
                }

                return;
            }else{
                returnJson(0,"发送失败!");
            }

        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }

    }

    /**
     * @brief 发布二级评论
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/sendSecondComment
     * @param news_id 状态id
     * @param user_id 用户id
     * @param reply_uid 被回复的用户id
     * @param reply_comment_id 被回复的id
     * @param top_comment_id 最上级回复的id
     * @param comment_content 评论的内容
     *
     */
    public  function sendSecondComment(){
        try{
            $secondComment = array();
            $secondComment['news_id'] = $_REQUEST['news_id'];
            $secondComment['user_id'] = $_REQUEST['user_id'];
            $secondComment['reply_uid'] = $_REQUEST['reply_uid'];
            $secondComment['reply_comment_id'] = $_REQUEST['reply_comment_id'];
            $secondComment['top_comment_id'] = $_REQUEST['top_comment_id'];
            $secondComment['comment_content'] = $_REQUEST['comment_content'];
            $secondComment['add_date'] = time();

            $newsModel = M('jlxc_news_content');
            $news = $newsModel->where('id='.$secondComment['news_id'].' and delete_flag = 0')->find();
            if(!$news){
                returnJson(0,"该状态不存在TAT!");
                return;
            }
            if(empty($secondComment['comment_content'])){
                returnJson(0,"评论内容不能为空");
                return;
            }

            $commentModel = M('jlxc_news_comment');
            $comment = $commentModel->where('id='.$secondComment['top_comment_id'].' and delete_flag = 0')->find();
            if(!$comment){
                returnJson(0,"该条评论不存在TAT!");
                return;
            }

            $secondCommentModel = D('jlxc_news_second_comment');
            $ret = $secondCommentModel->add($secondComment);
            if($ret){

                $news['comment_quantity'] ++;
                $newsModel->save($news);
                $secondComment = $secondCommentModel->find($ret);
                $secondComment['add_date'] = date('Y-m-d H:i:s', $secondComment['add_date']);
                returnJson(1,"发送成功", $secondComment);

                //二级评论推送
                //获取头像
                $userModel = M('jlxc_user');
                $user = $userModel->field('name, head_sub_image')->where('id='.$secondComment['user_id'])->find();

                //主人
                $newsUser = $userModel->field('name')->where('id='.$news['uid'])->find();

                $imagePath = '';
                if($news['has_picture']){
                    //该状态发的图片
                    $imageSql = 'SELECT sub_url
                                      from jlxc_attachment WHERE delete_flag = 0 and entity_id='.$news['id'].' ORDER BY id DESC';
                    $images = $commentModel->query($imageSql);
                    if(!empty($images)){
                        $imagePath = $images[0]['sub_url'];
                    }
                }

                //要发送的内容
                $content = array(
                    'uid'=>$secondComment['user_id'],
                    'head_image'=>$user['head_sub_image'],
                    'name'=>$user['name'],
                    'comment_content'=>$secondComment['comment_content'],
                    'news_id'=>$news['id'],
                    'news_content'=>$news['content_text'],
                    'news_image'=>$imagePath,
                    'news_user_name'=>$newsUser['name'],
                    'push_time'=>date('Y-m-d H:i:s', time())
                );
                //推送通知
                pushMessage($secondComment['reply_uid'],$content,3);

                //如果不是评论的自己 则推送通知
                if($news['uid'] != $secondComment['user_id']){

                    //要发送的内容
                    $content = array(
                        'uid'=>$secondComment['user_id'],
                        'head_image'=>$user['head_sub_image'],
                        'name'=>$user['name'],
                        'comment_content'=>$secondComment['comment_content'],
                        'news_id'=>$news['id'],
                        'news_content'=>$news['content_text'],
                        'news_image'=>$imagePath,
                        'news_user_name'=>$newsUser['name'],
                        'push_time'=>date('Y-m-d H:i:s', time())
                    );
                    //推送通知
                    pushMessage($news['uid'],$content,2);
                }

                return;
            }else{
                returnJson(0,"发送失败!");
            }

        }catch (Exception $e) {

            returnJson(0,"数据异常=_=",$e);
        }

    }

    /**
     * @brief 删除评论
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/deleteComment
     * @param comment_id 评论id
     *
     */
    public  function deleteComment(){
        try{
            $comment = array();
            $comment['id'] = $_REQUEST['cid'];
            $comment['delete_date'] = time();
            $comment['delete_flag'] = 1;

            $commentModel = M('jlxc_news_comment');
            $commentModel->startTrans();
            $ret = $commentModel->save($comment);
            $secondCommentModel = M('jlxc_news_second_comment');
            $secondeComment = $secondCommentModel->field('count(1) count')->where('delete_flag=0 and top_comment_id='.$comment['id'])->find();
            $count = $secondeComment['count'];

            //那条新闻
            $news_id = $_REQUEST['news_id'];
            $newsModel = M('jlxc_news_content');
            //评论数减一
            $news = $newsModel->where('id='.$news_id)->find();
            if($news['comment_quantity'] > 0){
                $news['comment_quantity'] = $news['comment_quantity']-$count-1;
            }
            //不能为负数
            if($news['comment_quantity'] < 0){
                $news['comment_quantity'] = 0;
            }

            $news['update_date'] = time();
            $nret = $newsModel->save($news);

            if($ret && $nret){
                $commentModel -> commit();
                returnJson(1,"删除成功!");
                return;
            }else{
                $commentModel -> rollback();
                returnJson(0,"删除失败!");
            }

        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 删除二级评论
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/deleteSecondComment
     * @param comment_id 评论id
     *
     */
    public  function deleteSecondComment(){
        try{
            $comment = array();
            $comment['id'] = $_REQUEST['cid'];
            $comment['delete_date'] = time();
            $comment['delete_flag'] = 1;

            $commentModel = M('jlxc_news_second_comment');
            $commentModel->startTrans();
            $ret = $commentModel->save($comment);

            //那条新闻
            $news_id = $_REQUEST['news_id'];
            $newsModel = M('jlxc_news_content');
            //评论数减一
            $news = $newsModel->where('id='.$news_id)->find();
            if($news['comment_quantity'] > 0){
                $news['comment_quantity'] --;
            }else{
                $news['comment_quantity'] = 0;
            }
            $news['update_date'] = time();
            $nret = $newsModel->save($news);

            if($ret && $nret){
                $commentModel -> commit();
                returnJson(1,"删除成功!");
                return;
            }else{
                $commentModel -> rollback();
                returnJson(0,"删除失败!");
            }

        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 点赞或者取消赞
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/likeOrCancel
     * @param isLike 点赞还是取消 1是赞 0是取消
     * @param news_id 状态id
     * @param user_id 用户id
     * @param is_second 是不是二级点赞 //暂时不用
     * @param comment_id 如果是二级评论 评论的id //暂时不用
     *http://192.168.1.105/jlxc_php/index.php/Home/MobileApi/likeOrCancel?comment_content=10101111132123&news_id=23&user_id=1&is_second=0&isLike=1
     */
    public  function likeOrCancel(){
        try{
            $like = array();
            $like['news_id'] = $_REQUEST['news_id'];
            $like['user_id'] = $_REQUEST['user_id'];
            $like['is_second'] = $_REQUEST['is_second'];
            $like['comment_id'] = $_REQUEST['comment_id'];
            $like['add_date'] = time();
            $isLike = $_REQUEST['isLike'];

            $likeModel = M('jlxc_news_like');
            $likeModel->startTrans();

            $newsModel = M('jlxc_news_content');
            //状态
            $news = $newsModel->where('id='.$like['news_id'].' and delete_flag = 0')->find();
            if(!$news){
                returnJson(0,"该状态不存在TAT!");
                $likeModel->rollback();
                return;
            }

            $oldLike = $likeModel->where('news_id='.$like['news_id'].' and user_id='.$like['user_id'])->find();

            if($oldLike){

                if($oldLike['delete_flag'] == !$isLike){
                    returnJson(0,"点过了");
                    $likeModel->rollback();
                    return;
                }

                $oldLike['delete_flag'] = !$isLike;
                //将状态点赞数减一或者加以
                if($isLike) {
                    $news['like_quantity'] ++;
                    $oldLike['resume_date'] = time();
                }else{
                    $news['like_quantity'] --;
                    $oldLike['delete_date'] = time();
                }
                //保存点赞
                $ret = $likeModel->save($oldLike);
                if($ret) {
                    $ret = $newsModel->save($news);
                    if($ret){
                        returnJson(1,"操作成功");
                        $likeModel->commit();
                        return;
                    }else{
                        returnJson(0,"操作失败");
                        $likeModel->rollback();
                        return;
                    }
                }else{
                    returnJson(0,"点赞失败");
                    $likeModel->rollback();
                    return;
                }
            }else{
                if($isLike){
                    //保存点赞
                    $ret = $likeModel->add($like);
                    if($ret){
                        $news['like_quantity'] ++;
                        $ret = $newsModel->save($news);
                        if($ret){

                            returnJson(1,"点赞成功");
                            $likeModel->commit();

                            //如果不是自己点赞 则推送通知
                            if($news['uid'] != $like['user_id']){
                                $imagePath = '';
                                if($news['has_picture']){
                                    //该状态发的图片
                                    $imageSql = 'SELECT sub_url
                                      from jlxc_attachment WHERE delete_flag = 0 and entity_id='.$news['id'].' ORDER BY id DESC';
                                    $images = $likeModel->query($imageSql);
                                    if(!empty($images)){
                                        $imagePath = $images[0]['sub_url'];
                                    }
                                }

                                //获取头像
                                $userModel = M('jlxc_user');
                                $user = $userModel->field('name, head_sub_image')->where('id='.$like['user_id'])->find();

                                //主人
                                $newsUser = $userModel->field('name')->where('id='.$news['uid'])->find();

                                //要发送的内容
                                $content = array(
                                    'uid'=>$like['user_id'],
                                    'name'=>$user['name'],
                                    'comment_content'=>'',
                                    'head_image'=>$user['head_sub_image'],
                                    'news_id'=>$news['id'],
                                    'news_content'=>$news['content_text'],
                                    'news_image'=>$imagePath,
                                    'news_user_name'=>$newsUser['name'],
                                    'push_time'=>date('Y-m-d H:i:s', time())
                                );
                                //推送通知
                                pushMessage($news['uid'],$content,4);
                            }

                            return;
                        }else{
                            returnJson(0,"点赞失败");
                            $likeModel->rollback();
                            return;
                        }

                    }else{
                        returnJson(0,"点赞失败");
                        $likeModel->rollback();
                        return;
                    }

                }else{
                    returnJson(0,"本来就没点");
                    $likeModel->rollback();
                    return;
                }
            }

        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 获取查看过该状态的用户列表
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getNewsVisitList?uid=19
     * @param news_id 用户id
     *
     */
    public function getNewsVisitList(){
        try{
            $news_id = $_REQUEST['news_id'];
            $page = $_REQUEST['page'];
            $size = $_REQUEST['size'];
            if(empty($page)){
                $page = 1;
            }
            if(empty($size)){
                $size = 10;
            }

            $start = ($page-1)*$size;
            $end   = $size;
            //最近访问列表
            $visitImagesModel = M();
            $sql = 'SELECT u.id uid, u.name, u.head_sub_image, v.visit_time, u.sign FROM jlxc_user u,jlxc_news_visit v
                    WHERE v.news_id='.$news_id.' AND v.visitor_id=u.id AND v.delete_flag=0 ORDER BY v.visit_time DESC LIMIT '.$start.','.$end;
            $visits = $visitImagesModel->query($sql);
            //格式化日期
            for($i=0; $i<count($visits); $i++) {
                $visits[$i]['visit_time'] = date('Y-m-d H:i:s', $visits[$i]['visit_time']);
            }

            $result = array();
            $result['list'] = $visits;
            //如果没有内容了
//            if(count($visits) < 1){
//                $result = array();
//            }
            //是否是最后一页
            if(count($visits) < $size){
                $result['is_last'] = '1';
            }else{
                $result['is_last'] = '0';
            }

            returnJson(1,"查询成功", $result);
            return;

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

/////////////////////////////////////////////好友部分////////////////////////////////////////////////////////////
    /**
     * @brief 添加好友
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/addFriend
     * @param user_id 用户id
     * @param friend_id 用户id
     *
     */
    public  function addFriend(){
        try{
            $addFriend = array();
            $addFriend['user_id'] = $_REQUEST['user_id'];
            $addFriend['friend_id'] = $_REQUEST['friend_id'];
            $addFriend['add_date'] = time();

            if($addFriend['user_id'] == $addFriend['friend_id']) {
                returnJson(0,"不能添加自己");
                return;
            }

            $addModel = M('jlxc_relationship');
            $isAdd = $addModel->where('user_id='.$addFriend['user_id'].' and friend_id='.$addFriend['friend_id'])->find();

            $friendModel = M('jlxc_user');
            $friend = $friendModel->where('id='.$addFriend['user_id'])->find();

            //添加过
            if($isAdd){
                if($isAdd['delete_flag'] == 0){
                    returnJson(1,"添加过了=_=");
                    return;
                }else{
                    $isAdd['delete_flag'] = 0;
                    $isAdd['resume_date'] = time();
                    $ret = $addModel->save($isAdd);
                    if($ret){

                        //要发送的内容
                        $content = array(
                            'type'=>'1',
                            'uid'=>JLXC.$friend['id'],
                            'name'=>$friend['name'],
                            'time'=>date('Y-m-d H:i:s', time()),
                            'avatar'=>$friend['head_image']
                        );
                        //推送通知
                        pushMessage($addFriend['friend_id'],$content,1);

                        returnJson(1,"添加成功！");
                    }else{
                        returnJson(0,"添加失败=.=");
                    }
                }
            }else{
                $ret = $addModel->add($addFriend);
                if($ret){

                    //要发送的内容
                    $content = array(
                        'type'=>'1',
                        'uid'=>JLXC.$friend['id'],
                        'name'=>$friend['name'],
                        'time'=>date('Y-m-d H:i:s', time()),
                        'avatar'=>$friend['head_image']
                    );
                    //推送通知
                    pushMessage($addFriend['friend_id'],$content,1);

                    returnJson(1,"添加成功！");
                }else{
                    returnJson(0,"添加失败=.=");
                }
            }



            return;
        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 添加好友
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/deleteFriend
     * @param user_id 用户id
     * @param friend_id 用户id
     *
     */
    public  function deleteFriend(){
        try{
            $deleteFriend = array();
            $deleteFriend['user_id'] = $_REQUEST['user_id'];
            $deleteFriend['friend_id'] = $_REQUEST['friend_id'];

            if($deleteFriend['user_id'] == $deleteFriend['friend_id']) {
                returnJson(0,"不能删除自己");
                return;
            }

            $deleteModel = M('jlxc_relationship');
            $delete = $deleteModel->where('user_id='.$deleteFriend['user_id'].' and friend_id='.$deleteFriend['friend_id'])->find();
            //添加过
            if($delete){
                if($delete['delete_flag'] == 1){
                    returnJson(0,"删除过了=_=");
                    return;
                }else{
                    $delete['delete_flag'] = 1;
                    $delete['delete_date'] = time();
                    $ret = $deleteModel->save($delete);
                    if($ret){
                        returnJson(1,"删除成功！");
                    }else{
                        returnJson(0,"删除失败=.=");
                    }
                }

            }else{
                returnJson(0,"没有该好友=.=");
            }

            return;
        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 获取图片和姓名
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getImageAndName
     * @param user_id 用户id
     *
     */
    public  function getImageAndName(){
        try{
            $user_id = $_REQUEST['user_id'];

            $userModel = M('jlxc_user');
            $user = $userModel->field('head_image,name')->where('delete_flag = 0 and id='.$user_id)->find();

            if($user){
                returnJson(1,"添加成功", $user);
            }else{
                returnJson(0,"没这人=_=");
            }

            return;
        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 是否同步好友
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/ifSyncFriends
     * @param user_id 用户id
     * @param friends_count 当前好友数量
     *
     */
    public  function needSyncFriends(){
        try{
            $user_id = $_REQUEST['user_id'];
            $friends_count = $_REQUEST['friends_count'];

            $friendModel = M('jlxc_relationship');
            $friend = $friendModel->field('count(1) count')->where('delete_flag=0 and user_id='.$user_id)->find();
            //添加过
            if($friend['count'] <= $friends_count){
                $needUpdate = array('needUpdate'=>'0');
                returnJson(1,"好友不少。", $needUpdate);
            }else{
                $needUpdate = array('needUpdate'=>'1');
                returnJson(1,"好友有毒。", $needUpdate);
            }
            return;
        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 获取好友列表
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getFriendsList
     * @param user_id 用户id
     *
     */
    public  function getFriendsList(){
        try{
            $user_id = $_REQUEST['user_id'];

            $page = $_REQUEST['page'];
            $size = $_REQUEST['size'];
            if(empty($page)){
                $page = 1;
            }
            if(empty($size)){
                $size = 10;
            }

            $start = ($page-1)*$size;
            $end   = $size;

            $friendModel = M('jlxc_relationship');
            $sql = 'SELECT u.id uid, u.name,u.head_sub_image,u.school,u.head_image,r.friend_remark from jlxc_user u,jlxc_relationship r
                    WHERE r.delete_flag=0 and r.user_id='.$user_id.' and r.friend_id=u.id  order by r.add_date DESC LIMIT '.$start.','.$end;
            $friendList = $friendModel->query($sql);

            $result = array();
            $result['list'] = $friendList;
            //是否是最后一页
            if(count($friendList) < $size){
                $result['is_last'] = '1';
            }else{
                $result['is_last'] = '0';
            }

            //添加过
            if($friendList){
                returnJson(1,"获取成功", $result);
            }else{
                returnJson(1,"本来就没有", $result);
            }
            return;
        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 获取好友列表 旧版 全部
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getFriendsList
     * @param user_id 用户id
     *
     */
    public  function getAllFriendsList(){
        try{
            $user_id = $_REQUEST['user_id'];

            $friendModel = M('jlxc_relationship');
            $sql = 'SELECT u.id uid, u.name,u.head_image,r.friend_remark from jlxc_user u,jlxc_relationship r
                    WHERE r.delete_flag=0 and r.user_id='.$user_id.' and r.friend_id=u.id';
            $friendList = $friendModel->query($sql);
            //添加过
            if($friendList){
                returnJson(1,"获取成功", array('list'=>$friendList));
            }else{
                returnJson(0,"本来就没有");
            }
            return;
        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 修改备注
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/addRemark
     * @param user_id 用户id
     * @param friend_id 好友id
     * @param 备注 friend_remark
     */
    public  function addRemark(){
        try{
            $user_id = $_REQUEST['user_id'];
            $friend_id = $_REQUEST['friend_id'];
            $friend_remark = $_REQUEST['friend_remark'];

            $friendModel = M('jlxc_relationship');
            $friend = $friendModel->where('delete_flag =0 and user_id='.$user_id.' and friend_id='.$friend_id)->find();
            //添加过
            if($friend){
                $friend['friend_remark'] = $friend_remark;
                $friend['update_date'] = time();
                $ret = $friendModel->save($friend);
                if($ret){
                    returnJson(1,"修改成功");
                }else{
                    returnJson(0,"修改失败");
                }

            }else{
                returnJson(0,"没有该好友");
            }
            return;
        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

/////////////////////////////////////////////聊天室部分////////////////////////////////////////////////////////////
    /**
     * @brief 修改备注
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/createChatRoom
     * @param user_id 用户id
     * @param friend_id 好友id
     * @param user_name 名字
     */
    public  function createChatRoom(){
        try{
            $user_id = $_REQUEST['user_id'];
            $chatroom_title = $_REQUEST['chatroom_title'];
            $user_name = $_REQUEST['user_name'];
            $tags = $_REQUEST['tags'];
            $tagsArr = json_decode($tags);
            //用户为空
            if(empty($user_id)){
                returnJson(0,"用户为空");
                return;
            }

            //用户标题
            if(empty($chatroom_title)){
                returnJson(0,"聊天室标题为空");
                return;
            }

            //同时最多只能创建三个
            $chatroomModel = M('jlxc_chatroom');
            //这里 以后需要做'到期处理'
            $roomAmount = $chatroomModel->field('count(1) count')->where(time().'<chatroom_create_time+chatroom_duration and delete_flag=0 and user_id='.$user_id)->find();
            if($roomAmount['count'] >= 3){
                returnJson(0,"同时最多只能创建三个聊天室");
                return;
            }

            $info = null;
            //存图片
            if(!empty($_FILES)){
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize   =     10*1024*1024 ;// 设置附件上传大小
                $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
                $upload->savePath  =     ''; // 设置附件上传（子）目录
                $upload->saveName  =     '';
                // 上传文件
                $info   =   $upload->upload();
            }

            $path = '';
            //上传成功
            if($info) {
                $image = new \Think\Image();
                foreach($info as $file){
                    $user['head_image'] = $file['savepath'].$file['savename'];
                    $path = $file['savepath'].$file['savename'];
                }
            }

            //开启事务
            $chatroomModel->startTrans();

            $chatroom = array();
            $chatroom['user_id'] = $user_id;
            $chatroom['chatroom_title'] = $chatroom_title;
            $chatroom['chatroom_background'] = $path;
            $chatroom['chatroom_create_time'] = time();
            $chatroom['chatroom_duration'] = 3600*12;
            $chatroom['max_quantity'] = 20;
            $chatroom['add_date'] = time();
            $chatroomId = $chatroomModel->add($chatroom);

            //添加成功
            if($chatroomId){
                //添加创建者为该聊天室成员
                $roomMemberModel = M('jlxc_chatroom_member');
                $roomMember = array();
                $roomMember['chatroom_id'] = $chatroomId;
                $roomMember['user_id'] = $user_id;
                $roomMember['join_time'] = time();
                $roomMember['add_date'] = time();

                $memberRet = $roomMemberModel->add($roomMember);
                if($memberRet){
                    //创建融云聊天室
                    $rong = getRongConnection();
                    $joinRet = $rong->groupJoin(JLXC.$user_id, JLXC_CHATROOM.$chatroomId, $chatroom_title);
                    //创建失败就算失败
                    if(!$joinRet){
                        returnJson(0,"添加失败");
                        $chatroomModel->rollback();
                        return;
                    }
                    $message = '{"content":"'.$user_name.'加入了聊天室哟","extra":""}';
                    //发送
                    $rong->messageGroupPublish(JLXC.$user_id,array(JLXC_CHATROOM.$chatroomId),'RC:TxtMsg',$message);

                    ////创建聊天室成功 成员添加成功就算成功
                    $chatroomModel->commit();

                    //添加标签
                    if(count($tagsArr) > 0){

                        $roomTagModel = M('jlxc_chatroom_tag');
                        $tagModel = M('jlxc_tag');
                        //标签数组
                        $roomTags = array();
                        foreach($tagsArr as $tagContent){
                            //该聊天室使用的标签
                            $roomTag = array();
                            $roomTag['chatroom_id'] = $chatroomId;
                            $roomTag['add_date'] = time();

                            $tag = $tagModel->where('tag_content="'.$tagContent.'"')->find();
                            if($tag){
                                //存在直接用
                                $roomTag['tag_id'] = $tag['id'];
                                array_push($roomTags, $roomTag);
                                $tag['use_amount'] = $tag['use_amount']+1;
                                $tagModel->save($tag);

                            }else{
                                //不存在添加一条
                                $tag = array();
                                $tag['user_id'] = $user_id;
                                $tag['tag_content'] = $tagContent;
                                $tag['add_date'] = time();
                                $tagId = $tagModel->add($tag);
                                if($tagId){
                                    $roomTag['tag_id'] = $tagId;
                                    array_push($roomTags, $roomTag);
                                }
                            }
                        }
                        $roomTagModel->addAll($roomTags);
                    }
                    $info = array();
                    $info['image_path'] = $path;
                    $info['chatroom_id'] = $chatroomId;
                    returnJson(1,"添加成功",$info);

                }else{
                    returnJson(0,"添加失败");
                    $chatroomModel->rollback();
                }

            }else{
                $chatroomModel->rollback();
                returnJson(0,"添加失败");
            }

            return;
        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 获取好友列表
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getChatRoomList
     * @param user_id 用户id
     *
     */
    public function getChatRoomList(){
        try{
            $page = $_REQUEST['page'];
            $size = $_REQUEST['size'];
            if(empty($page)){
                $page = 1;
            }
            if(empty($size)){
                $size = 10;
            }

            $start = ($page-1)*$size;
            $end   = $size;
            //最近访问列表
            $chatroomsModel = M();
            $sql = 'SELECT * from jlxc_chatroom WHERE '.time().' <chatroom_create_time+chatroom_duration and  delete_flag=0 LIMIT '.$start.','.$end;
            $chatrooms = $chatroomsModel->query($sql);
            //循环
            $chatroomMemberModel = M('jlxc_chatroom_member');
            for($i=0; $i<count($chatrooms); $i++) {
                //人数
                $memberCount = $chatroomMemberModel->field('count(1) count')->where('delete_flag=0 and chatroom_id='.$chatrooms[$i]['id'])->find();
                $chatrooms[$i]['current_quantity'] = $memberCount['count'];
                $tagsSql = 'SELECT t.tag_content FROM jlxc_chatroom_tag ct,jlxc_tag t
                            WHERE ct.delete_flag=0 AND ct.tag_id = t.id AND ct.chatroom_id='.$chatrooms[$i]['id'];
                $tags = $chatroomsModel->query($tagsSql);
                $chatrooms[$i]['tags'] = $tags;
            }

            $result = array();
            $result['list'] = $chatrooms;

            //是否是最后一页
            if(count($chatrooms) < $size){
                $result['is_last'] = '1';
            }else{
                $result['is_last'] = '0';
            }

            returnJson(1,"查询成功", $result);

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

    /**
     * @brief 加入聊天室 //忘做人数最大限制了
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/joinChatRoom
     * @param user_id 用户id
     * @param chatroom_id 聊天室id
     * @param user_name 名字
     */
    public  function joinChatRoom(){
        try{
            $user_id = $_REQUEST['user_id'];
            $chatroom_id = $_REQUEST['chatroom_id'];
            $chatroomModel = M('jlxc_chatroom');
            $user_name = $_REQUEST['user_name'];
            $chatroom = $chatroomModel->where(time().'<chatroom_create_time+chatroom_duration and delete_flag=0 and id='.$chatroom_id)->find();
            if(empty($chatroom)){
                returnJson(0,"不存在该聊天室或已到期...");
                return;
            }
            //添加为成员
            $roomMemberModel = M('jlxc_chatroom_member');
            $roomMember = $roomMemberModel->where('user_id='.$user_id.' and chatroom_id='.$chatroom_id)->find();
            //如果用户存在
            if($roomMember){
                if($roomMember['delete_flag']==0){
                    returnJson(0,"你已经加入过啦~\\(≧▽≦)/~");
                    return;
                }else{

                    $roomMemberModel->startTrans();
                    $roomMember['join_time'] = time();
                    $roomMember['update_date'] = time();
                    $roomMember['delete_flag'] = 0;
                    $memberRet = $roomMemberModel->save($roomMember);

                    if($memberRet){

                        //如果存在加入融云聊天室
                        $rong = getRongConnection();
                        $joinRet = $rong->groupJoin(JLXC.$user_id, JLXC_CHATROOM.$chatroom_id, $chatroom['chatroom_title']);
                        //加入失败就算失败
                        if(!$joinRet){
                            returnJson(0,"加入失败=_=");
                            $chatroomModel->rollback();
                            return;
                        }

                        //发送加入了消息
                        $message = '{"content":"'.$user_name.'加入了聊天室哟","extra":""}';
                        //发送
                        $rong->messageGroupPublish(JLXC.$user_id,array(JLXC_CHATROOM.$chatroom_id),'RC:TxtMsg',$message);
                        $chatroomModel->commit();
                        returnJson(1,"加入成功~");
                    }else{
                        $chatroomModel->rollback();
                        returnJson(0,"加入失败=_=");
                    }
                    return;
                }
            }

            $roomMemberModel->startTrans();
            $roomMember = array();
            $roomMember['chatroom_id'] = $chatroom_id;
            $roomMember['user_id'] = $user_id;
            $roomMember['join_time'] = time();
            $roomMember['add_date'] = time();
            $memberRet = $roomMemberModel->add($roomMember);
            if($memberRet){
                //加入融云聊天室
                $rong = getRongConnection();
                $joinRet = $rong->groupJoin(JLXC.$user_id, JLXC_CHATROOM.$chatroom_id, $chatroom['chatroom_title']);
                //加入失败就算失败
                if(!$joinRet){
                    returnJson(0,"加入失败=_=");
                    $chatroomModel->rollback();
                    return;
                }
                //发送加入了消息
                $message = '{"content":"'.$user_name.'加入了聊天室哟","extra":""}';
                //发送
                $rong->messageGroupPublish(JLXC.$user_id,array(JLXC_CHATROOM.$chatroom_id),'RC:TxtMsg',$message);

                $chatroomModel->commit();
                returnJson(1,"加入成功~");
            }else{

                $chatroomModel->rollback();
                returnJson(0,"加入失败=_=");
            }
            return;
        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 退出聊天室
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/leaveChatRoom
     * @param user_id 用户id
     * @param kick_id 要退出的人id
     * @param chatroom_id 聊天室id
     */
    public  function leaveChatRoom(){
        try{
            $user_id = $_REQUEST['user_id'];
            $kick_id = $_REQUEST['kick_id'];
            $chatroom_id = $_REQUEST['chatroom_id'];
            $chatroomModel = M('jlxc_chatroom');
            $chatroom = $chatroomModel->where(time().'<chatroom_create_time+chatroom_duration and delete_flag=0 and id='.$chatroom_id)->find();
            if(empty($chatroom)){
                returnJson(0,"不存在该聊天室或已到期...");
                return;
            }
            //添加为成员
            $roomMemberModel = M('jlxc_chatroom_member');
            $roomMemberModel->startTrans();
            $roomMember = $roomMemberModel->where('user_id='.$kick_id.' and chatroom_id='.$chatroom_id)->find();
            //如果用户存在
            if($roomMember){
                if($roomMember['delete_flag']==1){
                    returnJson(1,"已经离开了=_=");
                    return;
                }else{

                    $roomMember['delete_date'] = time();
                    $roomMember['delete_flag'] = 1;
                    $memberRet = $roomMemberModel->save($roomMember);
                    if($memberRet){
                        $rong = getRongConnection();
                        //退出聊天室
                        $quitRet = $rong->groupQuit(JLXC.$kick_id, JLXC_CHATROOM.$chatroom_id);
                        //退出失败就算失败了
                        if(!$quitRet){
                            $roomMemberModel->rollback();
                            returnJson(0,"退出失败=_=");
                            return;
                        }
                        $roomMemberModel->commit();

                        if($user_id==$kick_id){
                            returnJson(1,"退出成功~");

                        }else{
                            returnJson(1,"已经踢了~");
                            //给被踢的人发一条通知
//                            $message = '{"message":"你已被群主踢出了讨论组'.$chatroom['chatroom_title'].'T_T","extra":JLXC_CHATROOM.$chatroom_id}';
                            $message = '{"message":"'.'kick_'.JLXC_CHATROOM.$chatroom_id.'","extra":""}';
                            $rong->messageSystemPublish(JLXC.$user_id, array(JLXC.$kick_id),'RC:ContactNtf',$message);
                        }

                    }else{
                        $roomMemberModel->rollback();
                        returnJson(0,"退出失败=_=");
                    }
                    return;
                }
            }else{
                returnJson(0,"你本身就没加入=_=");
            }

            return;
        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 聊天室详情
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/jlxc_chatroom
     * @param chatroom_id 聊天室id
     */
    public  function chatRoomDetail(){
        try{
            $chatroom_id = $_REQUEST['chatroom_id'];
            $info = array();
            $chatroomModel = M('jlxc_chatroom');
            //信息
            $chatroom = $chatroomModel->where(time().'<chatroom_create_time+chatroom_duration and delete_flag=0 and id='.$chatroom_id)->find();
            if(empty($chatroom)){
                returnJson(0,"不存在该聊天室或已到期...");
                return;
            }
            $info['info'] = $chatroom;

            //标签
            $tagsSql = 'SELECT t.tag_content FROM jlxc_chatroom_tag ct,jlxc_tag t
                            WHERE ct.delete_flag=0 AND ct.tag_id = t.id AND ct.chatroom_id='.$chatroom['id'];
            $tags = $chatroomModel->query($tagsSql);
            $info['info']['tags'] = $tags;

            $chatroomMemberModel = M();
            $memberSql = 'SELECT u.id uid, u.head_sub_image, u.name, u.school FROM jlxc_chatroom_member c,jlxc_user u
                          WHERE c.chatroom_id='.$chatroom_id.' AND c.delete_flag=0 AND u.id=c.user_id';
            //人数
            $members = $chatroomMemberModel->query($memberSql);
            $info['list'] = $members;

            returnJson(1,"查询成功", $info);
            return;

        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 获取我的聊天记录列表
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getChatRoomList
     * @param user_id 用户id
     *
     */
    public function getMyChatRoomList(){
        try{

            $user_id = $_REQUEST['user_id'];
            //最近访问列表
            $chatroomsModel = M();
            //SELECT * from jlxc_chatroom c, jlxc_chatroom_member cm WHERE c.delete_flag=0 AND c.delete_flag=0 AND c.id=cm.chatroom_id AND cm.user_id=19
            $sql = 'SELECT c.chatroom_title,c.chatroom_background,c.max_quantity,c.chatroom_create_time,c.id,c.chatroom_duration,c.user_id
                    from jlxc_chatroom c, jlxc_chatroom_member cm
                    WHERE '.time().'<c.chatroom_create_time+c.chatroom_duration
                    AND c.delete_flag=0 AND cm.delete_flag=0 AND c.id=cm.chatroom_id AND cm.user_id='.$user_id;
            $chatrooms = $chatroomsModel->query($sql);
            //循环
            $chatroomMemberModel = M('jlxc_chatroom_member');
            for($i=0; $i<count($chatrooms); $i++) {
                //人数
                $memberCount = $chatroomMemberModel->field('count(1) count')->where('delete_flag=0 and chatroom_id='.$chatrooms[$i]['id'])->find();
                $chatrooms[$i]['current_quantity'] = $memberCount['count'];
                $tagsSql = 'SELECT t.tag_content FROM jlxc_chatroom_tag ct,jlxc_tag t
                            WHERE ct.delete_flag=0 AND ct.tag_id = t.id AND ct.chatroom_id='.$chatrooms[$i]['id'];
                $tags = $chatroomsModel->query($tagsSql);
                $chatrooms[$i]['tags'] = $tags;
            }

            $result = array();
            $result['list'] = $chatrooms;
            $result['is_last'] = '1';
            returnJson(1,"查询成功", $result);

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }


    /**
     * @brief 获取聊天室背景图和标题
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getChatRoomTitleAndBack
     * @param chatroom_id 聊天室id
     *
     */
    public  function getChatRoomTitleAndBack(){
        try{
            $chatroom_id = $_REQUEST['chatroom_id'];

            $chatroomModel = M('jlxc_chatroom');
            $chatroom = $chatroomModel->field('chatroom_background,chatroom_title')->where('delete_flag = 0 and id='.$chatroom_id)->find();

            if($chatroom){
                returnJson(1,"查询成功", $chatroom);
            }else{
                returnJson(0,"没这人=_=");
            }

            return;
        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 获取聊天室的剩余时间
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getChatRoomLeftTime
     * @param chatroom_id 聊天室id
     *
     */
    public  function getChatRoomLeftTime(){
        try{
            $chatroom_id = $_REQUEST['chatroom_id'];

            $chatroomModel = M('jlxc_chatroom');
            $chatroom = $chatroomModel->field('chatroom_create_time,chatroom_duration')->where('delete_flag = 0 and id='.$chatroom_id)->find();

            $leftTime = $chatroom['chatroom_create_time']+$chatroom['chatroom_duration']-time();

            if($chatroom){
                returnJson(1,"查询成功", $leftTime);
            }else{
                returnJson(0,"没这群=_=");
            }
            return;

        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

/////////////////////////////////////////////发现部分////////////////////////////////////////////////////////////
    /**
     * @brief 获取当前在使用的通讯录用户
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getContactUser
     * @param user_id 用户id
     * @param contact 通讯录//["13745365657","13745365657"]
     */
    public  function getContactUser(){
        try{
            $user_id = $_REQUEST['user_id'];
            $contact = json_decode($_REQUEST['contact']);
            if(empty($user_id)){
                returnJson(0,"用户Id不能为空");
                return;
            }
            if(empty($contact)){
                returnJson(0,"查询号码不能为空");
                return;
            }

            $contact = json_decode($_REQUEST['contact']);
            $inContact = implode(',',$contact);

            $contactModel = M('jlxc_user');
            $sql = 'SELECT u.id uid, u.name, u.username phone, u.head_image,u.head_sub_image, CASE r.delete_flag WHEN 0 THEN 1 ELSE 0 END AS is_friend
                    FROM jlxc_user u LEFT JOIN jlxc_relationship r ON( u.id=r.friend_id AND r.user_id='.$user_id.')
                    WHERE u.username in ('.$inContact.') ORDER BY is_friend';
            $contactArr = $contactModel->query($sql);
            returnJson(1,"查询成功", array('list'=>$contactArr));

            return;

        }catch (Exception $e) {

            returnJson(0,"数据异常=_=", $e);
        }
    }

    /**
     * @brief 获取同校的人列表
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/getSameSchoolList
     * @param user_id 用户id
     * @param school_code 学校代码
     */
    public function getSameSchoolList(){
        try{

            $page = $_REQUEST['page'];
            $size = $_REQUEST['size'];
            $user_id = $_REQUEST['user_id'];
            $school_code = $_REQUEST['school_code'];
            if(empty($user_id)){
                returnJson(0,"用户Id不能为空");
                return;
            }
            if(empty($school_code)){
                returnJson(0,"查询学校不能为空");
                return;
            }
            if(empty($page)){
                $page = 1;
            }
            if(empty($size)){
                $size = 10;
            }

            $start = ($page-1)*$size;
            $end   = $size;
            //最近访问列表
            $sameSchoolModel = M();
            $sql = 'SELECT u.id uid, u.name, u.sign, u.sex, u.head_image,u.head_sub_image, CASE r.delete_flag WHEN 0 THEN 1 ELSE 0 END AS is_friend
                    FROM jlxc_user u LEFT JOIN jlxc_relationship r ON( u.id=r.friend_id AND r.user_id='.$user_id.')
                    WHERE u.id<>'.$user_id.' AND u.school_code='.$school_code.' ORDER BY is_friend LIMIT '.$start.','.$end;
            $sameSchools = $sameSchoolModel->query($sql);

            $result = array();
            $result['list'] = $sameSchools;

            //是否是最后一页
            if(count($sameSchools) < $size){
                $result['is_last'] = '1';
            }else{
                $result['is_last'] = '0';
            }

            returnJson(1,"查询成功", $result);
        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

    /**
     * @brief 获取同校的人列表
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/findUserList
     * @param user_id 用户id
     * @param content 搜索内容
     */
    public function findUserList(){
        try{

            $page = $_REQUEST['page'];
            $size = $_REQUEST['size'];
            $user_id = $_REQUEST['user_id'];
            $content = $_REQUEST['content'];
            if(empty($user_id)){
                returnJson(0,"用户Id不能为空");
                return;
            }
            if(empty($content)){
                returnJson(0,"查询内容不能为空");
                return;
            }
            if(empty($page)){
                $page = 1;
            }
            if(empty($size)){
                $size = 10;
            }

            $start = ($page-1)*$size;
            $end   = $size;
            //最近访问列表
            $sameSchoolModel = M();
            $sql = 'SELECT u.id uid, u.name, u.head_image,u.head_sub_image, CASE r.delete_flag WHEN 0 THEN 1 ELSE 0 END AS is_friend
                    FROM jlxc_user u LEFT JOIN jlxc_relationship r ON( u.id=r.friend_id AND r.user_id='.$user_id.')
                    WHERE u.id<>'.$user_id.' AND u.name LIKE "%'.$content.'%" ORDER BY is_friend LIMIT '.$start.','.$end;
            $sameSchools = $sameSchoolModel->query($sql);

            $result = array();
            $result['list'] = $sameSchools;

            //是否是最后一页
            if(count($sameSchools) < $size){
                $result['is_last'] = '1';
            }else{
                $result['is_last'] = '0';
            }

            returnJson(1,"查询成功", $result);
        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

    /**
     * @brief helloHaId是否存在
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/helloHaIdExists
     * @param helloha_id hello_haId
     */
    public function helloHaIdExists(){
        try{

            $helloha_id = $_REQUEST['helloha_id'];

            if(empty($helloha_id)){
                returnJson(0,"哈哈号不能为空");
                return;
            }

            //查询是否存在该哈哈号
            $helloHaModel = M('jlxc_user');
            $user = $helloHaModel->field('id uid')->where('helloha_id="'.$helloha_id.'"')->find();
            //存在
            if($user){
                returnJson(1,"查询成功", array('uid'=>$user['uid']));
            }else{
                returnJson(0,"不存在该用户T_T");
            }

        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }
    //recommend
    /**
     * @brief 推荐的人列表
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/recommendFriendsList
     * @param user_id 用户id
     */
    public function recommendFriendsList(){
        try{

            $page = $_REQUEST['page'];
            $size = $_REQUEST['size'];
            $user_id = $_REQUEST['user_id'];
            if(empty($user_id)){
                returnJson(0,"用户Id不能为空");
                return;
            }
            if(empty($page)){
                $page = 1;
            }
            if(empty($size)){
                $size = 10;
            }

            /////////////////////////////////查找人群处理-开始/////////////////////////////////////
            $userModel = M('jlxc_user');
            $user=$userModel->where('id='.$user_id)->find();
            $ratio = 0.8;
            if($user['sex']=1){
                $ratio = 0.2;
            }
            //我的好友
            $relationshipModel = M('jlxc_relationship');
            $friendList = $relationshipModel->where('delete_flag=0 and user_id='.$user_id)->select();
            $leftCount = 150;

            $friends = array();
            foreach($friendList as $friend){
                array_push($friends, $friend['friend_id']);
            }
            $inFriendFriends = implode(',',$friends);
            if(empty($inFriendFriends)){
                $inFriendFriends = '0';
            }
            array_push($friends, $user_id);
            $notInFriends = implode(',',$friends);
            if(empty($notInFriends)){
                $notInFriends = '0';
            }
            //先计算人数 然后计算比例
            //学校人数
            $schoolCount = $userModel->field('count(1) count')->where('delete_flag=0 and school_code="'.$user['school_code'].'"')->find();
            //好友的好友人数
            $friendFriendCount = $relationshipModel->where('delete_flag=0 and user_id in ('.$inFriendFriends.')'.'
                             ')->group('friend_id')->select();
            //同区不同校的人数
            $schoolModel = M('jlxc_school');
            $school = $schoolModel->where('code='.$user['school_code'])->find();
            $districtSql = 'SELECT count(1) count FROM jlxc_user u,jlxc_school s
                    WHERE u.school_code=s.code AND s.code<>'.$user['school_code'].' AND s.district_code='.$school['district_code'];
            $districtCount = $schoolModel->query($districtSql);
            //同城不同区的人数
            $citySql = 'SELECT count(1) count FROM jlxc_user u,jlxc_school s
                    WHERE u.school_code=s.code AND s.district_code<>'.$school['district_code'].' AND s.city_code='.$school['city_code'];
            $cityCount = $schoolModel->query($citySql);

            //学校
            $nowSchoolCount = $schoolCount['count'];
            //好友的好友
            $nowFriendCount = count($friendFriendCount);
            //同区
            $nowDistrictCount = $districtCount[0]['count'];
            //同城
            $nowCityCount = $cityCount[0]['count'];

            $oriArr = array($nowSchoolCount, $nowFriendCount, $nowDistrictCount, $nowCityCount);
            $leftCount = (int)($leftCount*14/15.0);
            //比例数组
            $countArr = getFriendProportion($oriArr, $leftCount);

            //学校的人
            $schoolGirlList = $userModel->where('delete_flag=0 and sex=1 and school_code="'.$user['school_code'].'"'.' and id not in ('.$notInFriends.')')
                ->limit(0,(int)($countArr[0]*$ratio))->select();
            $schoolBoyList = $userModel->where('delete_flag=0 and sex=0 and school_code="'.$user['school_code'].'"'.' and id not in ('.$notInFriends.')')
                ->limit(0,$countArr[0]-count($schoolGirlList))->select();

            //好友in
            $friendFriends = array();
            foreach($friendList as $friend){
                array_push($friendFriends, $friend['friend_id']);
            }
            $inFriendFriends = implode(',',$friendFriends);
            if(empty($inFriendFriends)){
                $inFriendFriends = '0';
            }
            //好友not in
            $noFriendFriends = array();
            foreach($friendList as $friend){
                array_push($noFriendFriends, $friend['friend_id']);
            }
            //学校女孩
            foreach($schoolGirlList as $friend){
                array_push($noFriendFriends, $friend['id']);
            }
            //学校男孩
            foreach($schoolBoyList as $friend){
                array_push($noFriendFriends, $friend['id']);
            }
            array_push($noFriendFriends, $user_id);
            $notInFriendFriends = implode(',',$noFriendFriends);
            if(empty($notInFriendFriends)){
                $notInFriendFriends = '0';
            }
            //好友的好友
            //男
            $girlSql = 'SELECT u.id,r.user_id fid FROM jlxc_relationship r, jlxc_user u
                  WHERE u.sex=1 AND u.id=r.friend_id and r.delete_flag=0 and r.user_id in ('.$inFriendFriends.')'.'
                  and r.friend_id not in ('.$notInFriendFriends.') GROUP BY r.friend_id LIMIT 0,'.((int)($countArr[1]*$ratio));
            $girlFriendFriendList = $relationshipModel->query($girlSql);
            //女
            $boySql = 'SELECT u.id,r.user_id fid FROM jlxc_relationship r, jlxc_user u
                  WHERE u.sex=0 AND u.id=r.friend_id and r.delete_flag=0 and r.user_id in ('.$inFriendFriends.')'.'
                  and r.friend_id not in ('.$notInFriendFriends.') GROUP BY r.friend_id LIMIT 0,'.($countArr[1]-count($girlFriendFriendList));
            $boyFriendFriendList = $relationshipModel->query($boySql);

            $schoolModel = M('jlxc_school');
            $school = $schoolModel->where('code='.$user['school_code'])->find();

            //同区的人
            //朋友的朋友女孩
            foreach($girlFriendFriendList as $friend){
                array_push($noFriendFriends, $friend['id']);
            }
            //朋友的朋友男孩
            foreach($boyFriendFriendList as $friend){
                array_push($noFriendFriends, $friend['id']);
            }
            $notInDistrictFriends = implode(',',$noFriendFriends);
            if(empty($notInDistrictFriends)){
                $notInDistrictFriends = '0';
            }
            //女
            $girlSql = 'SELECT u.id,u.school_code FROM jlxc_user u,jlxc_school s
                    WHERE u.sex=1 AND u.school_code=s.code AND s.district_code='.$school['district_code'].'
                    AND u.id not in ('.$notInDistrictFriends.') LIMIT 0,'.((int)($countArr[2]*$ratio));
            $girlDistrictList = $schoolModel->query($girlSql);
            //男
            $boySql = 'SELECT u.id,u.school_code FROM jlxc_user u,jlxc_school s
                  WHERE u.sex=0 AND u.school_code=s.code AND s.district_code='.$school['district_code'].'
                  AND u.id not in ('.$notInDistrictFriends.') LIMIT 0,'.($countArr[2]-count($girlDistrictList));
            $boyDistrictList = $schoolModel->query($boySql);

            //同城的人
            //同区女孩
            foreach($girlDistrictList as $friend){
                array_push($noFriendFriends, $friend['id']);
            }
            //同区男孩
            foreach($boyDistrictList as $friend){
                array_push($noFriendFriends, $friend['id']);
            }
            $notInDistrictFriends = implode(',',$noFriendFriends);
            if(empty($notInDistrictFriends)){
                $notInDistrictFriends = '0';
            }
            //女
            $girlSql = 'SELECT u.id,u.school_code FROM jlxc_user u,jlxc_school s
                    WHERE u.sex=1 AND u.school_code=s.code AND s.city_code='.$school['city_code'].'
                    AND u.id not in ('.$notInDistrictFriends.') LIMIT 0,'.((int)($countArr[3]*$ratio));
            $girlCityList = $schoolModel->query($girlSql);
            //男
            $boySql = 'SELECT u.id,u.school_code FROM jlxc_user u,jlxc_school s
                  WHERE u.sex=0 AND u.school_code=s.code AND s.city_code='.$school['city_code'].'
                  AND u.id not in ('.$notInDistrictFriends.') LIMIT 0,'.($countArr[3]-count($girlCityList));
            $boyCityList = $schoolModel->query($boySql);

            //完全版集合
            $selectList = array();
            //type 1自己 2好友 3同校 4朋友的朋友 5同区 6同城   推荐算法没有自己和好友
//            $selectList[$user_id]=array('type'=>'1');
//            //你的好友
//            foreach($friendList as $friend){
//                $selectList[$friend['friend_id']]=array('type'=>'2');
//            }
            //学校女孩
            foreach($schoolGirlList as $friend){
                $selectList[$friend['id']]=array('type'=>'3');
            }
            //学校男孩
            foreach($schoolBoyList as $friend){
                $selectList[$friend['id']]=array('type'=>'3');
            }
            //朋友的朋友女孩
            foreach($girlFriendFriendList as $friend){
                $selectList[$friend['id']]=array('type'=>'4','fid'=>$friend['fid']);
            }
            //朋友的朋友男孩
            foreach($boyFriendFriendList as $friend){
                $selectList[$friend['id']]=array('type'=>'4','fid'=>$friend['fid']);
            }
            //同区女孩
            foreach($girlDistrictList as $friend){
                $selectList[$friend['id']]=array('type'=>'5','school_code'=>$friend['school_code']);
            }
            //同区男孩
            foreach($boyDistrictList as $friend){
                $selectList[$friend['id']]=array('type'=>'5','school_code'=>$friend['school_code']);
            }
            //同城女孩
            foreach($girlCityList as $friend){
                $selectList[$friend['id']]=array('type'=>'6','school_code'=>$friend['school_code']);
            }
            //同城男孩
            foreach($boyCityList as $friend){
                $selectList[$friend['id']]=array('type'=>'6','school_code'=>$friend['school_code']);
            }

            //其他随机填充
            foreach($girlCityList as $friend){
                array_push($noFriendFriends, $friend['id']);
            }
            foreach($boyCityList as $friend){
                array_push($noFriendFriends, $friend['id']);
            }
            $notInDistrictFriends = implode(',',$noFriendFriends);
            if(empty($notInDistrictFriends)){
                $notInDistrictFriends = '0';
            }
            $sql = 'SELECT * FROM jlxc_user WHERE delete_flag=0 AND id NOT IN('.$notInDistrictFriends.') ORDER BY RAND() limit '.(150-count($selectList));
            $leftList = $userModel->query($sql);
            //剩余填充
            foreach($leftList as $friend){
                $selectList[$friend['id']]=array('type'=>'7');
            }
            //随机抽取15个人
            $randArr = null;
            if(count($selectList) > 15){
                $randArr = array_rand($selectList,15);
            }else{
                $tmpArr = array_rand($selectList,count($selectList));
                if(is_array($tmpArr)){
                    $randArr = $tmpArr;
                }else{
                    if(isset($tmpArr)){
                        $randArr = array($tmpArr);
                    }else{
                        $randArr = array();
                    }
                }
            }
            $inList = array();
            foreach($randArr as $key){
                array_push($inList, $key);
            }

            $inStr = implode(',',$inList);
            if(count($inList) < 1){
                $inStr = '0';
            }

            /////////////////////////////////查找人群处理-结束/////////////////////////////////////

            $start = ($page-1)*$size;
            $end   = $size;
            //最近访问列表
            $recommendModel = M();
            $sql = 'SELECT u.id uid, u.name, u.school, u.school_code, u.head_image,u.head_sub_image
                    FROM jlxc_user u WHERE u.id in ('.$inStr.')';
            $recommendlList = $recommendModel->query($sql);

            for($i=0; $i<count($recommendlList); $i++){
                //附件列表
                $findImagesModel = M('jlxc_attachment');
                $images = $findImagesModel->field('sub_url')->where(array('delete_flag=0 and type=1 and user_id='.$recommendlList[$i]['uid']))->
                limit('3')->order('add_date desc')->select();
                if(empty($images)){
                    $images = array();
                }
                $recommendlList[$i]['images']=$images;

                //设置类型
                $recommend = $recommendlList[$i];
                $recommendlList[$i]['type'] = $selectList[$recommend['uid']];
                //type 1自己 2好友 3同校 4朋友的朋友 5同区 6同城
                //默认内容为空
                $recommendlList[$i]['type']['content'] = '';
                //朋友的朋友 姓名
                if($selectList[$recommend['uid']]['type']==4){
                    $friendUser = $userModel->where('id='.$selectList[$recommend['uid']]['fid'])->find();
                    $recommendlList[$i]['type']['content'] = $friendUser['name'].'的朋友';
                }
                //同区的
                if($selectList[$recommend['uid']]['type']==5){
                    $friendUser = $schoolModel->where('code='.$selectList[$recommend['uid']]['school_code'])->find();
                    $recommendlList[$i]['type']['content'] = $friendUser['district_name'].'的同学';
                }
                //同城的
                if($selectList[$recommend['uid']]['type']==6){
                    $friendUser = $schoolModel->where('code='.$selectList[$recommend['uid']]['school_code'])->find();
                    $recommendlList[$i]['type']['content'] = $friendUser['city_name'].'的同学';
                }
            }

            $result = array();
            $result['list'] = $recommendlList;

            //是否是最后一页 最多显示八页
            if($page > 8 || $page*($size-1) > count($selectList)){
                $result['is_last'] = '1';
            }else{
                $result['is_last'] = '0';
            }

            returnJson(1,"查询成功", $result);
        }catch (Exception $e){

            returnJson(0,"数据异常！",$e);
        }
    }

    /**
     * @brief 推荐的人列表
     * 接口地址
     * http://localhost/jlxc_php/index.php/Home/MobileApi/siftFriends?user_id=19
     * @param user_id 用户id
     */
    public function siftFriends(){

        $startTime = microtime();
        $user_id = $_REQUEST['user_id'];

        if(empty($user_id)){
            echo '输入ID';
            return;
        }

        $userModel = M('jlxc_user');
        $user=$userModel->where('id='.$user_id)->find();
        $ratio = 0.8;
        if($user['sex']=1){
            $ratio = 0.2;
        }

        //我的好友
        $relationshipModel = M('jlxc_relationship');
        $friendList = $relationshipModel->where('delete_flag=0 and user_id='.$user_id)->select();
        $leftCount = 150-count($friendList);

        $friends = array();
        foreach($friendList as $friend){
            array_push($friends, $friend['friend_id']);
        }
        $inFriendFriends = implode(',',$friends);
        array_push($friends, $user_id);
        $notInFriends = implode(',',$friends);

        //先计算人数 然后计算比例
        //学校人数
        $schoolCount = $userModel->field('count(1) count')->where('delete_flag=0 and school_code="'.$user['school_code'].'"')->find();
        //好友的好友人数
        $friendFriendCount = $relationshipModel->where('delete_flag=0 and user_id in ('.$inFriendFriends.')'.'
                             ')->group('friend_id')->select();
        //同区不同校的人数
        $schoolModel = M('jlxc_school');
        $school = $schoolModel->where('code='.$user['school_code'])->find();
        $districtSql = 'SELECT count(1) count FROM jlxc_user u,jlxc_school s
                    WHERE u.school_code=s.code AND s.code<>'.$user['school_code'].' AND s.district_code='.$school['district_code'];
        $districtCount = $schoolModel->query($districtSql);
        //同城不同区的人数
        $citySql = 'SELECT count(1) count FROM jlxc_user u,jlxc_school s
                    WHERE u.school_code=s.code AND s.district_code<>'.$school['district_code'].' AND s.city_code='.$school['city_code'];
        $cityCount = $schoolModel->query($citySql);

        //学校
        $nowSchoolCount = $schoolCount['count'];
        //好友的好友
        $nowFriendCount = count($friendFriendCount);
        //同区
        $nowDistrictCount = $districtCount[0]['count'];
        //同城
        $nowCityCount = $cityCount[0]['count'];

        $oriArr = array($nowSchoolCount, $nowFriendCount, $nowDistrictCount, $nowCityCount);
        $leftCount = (int)($leftCount*14/15.0);
        //比例数组
        $countArr = getFriendProportion($oriArr, $leftCount);


        //学校的人
        $schoolGirlList = $userModel->where('delete_flag=0 and sex=1 and school_code="'.$user['school_code'].'"'.' and id not in ('.$notInFriends.')')
            ->limit(0,(int)($countArr[0]*$ratio))->select();
        //减去这些人
//        $leftCount = $leftCount-count($schoolGirlList);
        $schoolBoyList = $userModel->where('delete_flag=0 and sex=0 and school_code="'.$user['school_code'].'"'.' and id not in ('.$notInFriends.')')
            ->limit(0,$countArr[0]-count($schoolGirlList))->select();
        //减去这些人
//        $leftCount = $leftCount-count($schoolBoyList);

        //好友in
        $friendFriends = array();
        foreach($friendList as $friend){
            array_push($friendFriends, $friend['friend_id']);
        }
        $inFriendFriends = implode(',',$friendFriends);

        //好友not in
        $noFriendFriends = array();
        foreach($friendList as $friend){
            array_push($noFriendFriends, $friend['friend_id']);
        }
        //学校女孩
        foreach($schoolGirlList as $friend){
            array_push($noFriendFriends, $friend['id']);
        }
        //学校男孩
        foreach($schoolBoyList as $friend){
            array_push($noFriendFriends, $friend['id']);
        }
        array_push($noFriendFriends, $user_id);
        $notInFriendFriends = implode(',',$noFriendFriends);

        //好友的好友
        //男
        $girlSql = 'SELECT u.id FROM jlxc_relationship r, jlxc_user u
                  WHERE u.sex=1 AND u.id=r.friend_id and r.delete_flag=0 and r.user_id in ('.$inFriendFriends.')'.'
                  and r.friend_id not in ('.$notInFriendFriends.') GROUP BY r.friend_id LIMIT 0,'.((int)($countArr[1]*$ratio));
        $girlFriendFriendList = $relationshipModel->query($girlSql);
//        $leftCount = $leftCount-count($girlFriendFriendList);
        //女
        $boySql = 'SELECT u.id FROM jlxc_relationship r, jlxc_user u
                  WHERE u.sex=0 AND u.id=r.friend_id and r.delete_flag=0 and r.user_id in ('.$inFriendFriends.')'.'
                  and r.friend_id not in ('.$notInFriendFriends.') GROUP BY r.friend_id LIMIT 0,'.($countArr[1]-count($girlFriendFriendList));
        $boyFriendFriendList = $relationshipModel->query($boySql);
//        $leftCount = $leftCount-count($boyFriendFriendList);

        //好友的好友
//        $friendFriendList = $relationshipModel->where('delete_flag=0 and user_id in ('.$inFriendFriends.')')->select();

        $schoolModel = M('jlxc_school');
        $school = $schoolModel->where('code='.$user['school_code'])->find();

        //同区的人
        //朋友的朋友女孩
        foreach($girlFriendFriendList as $friend){
            array_push($noFriendFriends, $friend['id']);
        }
        //朋友的朋友男孩
        foreach($boyFriendFriendList as $friend){
            array_push($noFriendFriends, $friend['id']);
        }

        $notInDistrictFriends = implode(',',$noFriendFriends);

        //女
        $girlSql = 'SELECT u.id FROM jlxc_user u,jlxc_school s
                    WHERE u.sex=1 AND u.school_code=s.code AND s.district_code='.$school['district_code'].'
                    AND u.id not in ('.$notInDistrictFriends.') LIMIT 0,'.((int)($countArr[2]*$ratio));
        $girlDistrictList = $schoolModel->query($girlSql);
//        $leftCount = $leftCount-count($girlDistrictList);
        //男
        $boySql = 'SELECT u.id FROM jlxc_user u,jlxc_school s
                  WHERE u.sex=0 AND u.school_code=s.code AND s.district_code='.$school['district_code'].'
                  AND u.id not in ('.$notInDistrictFriends.') LIMIT 0,'.($countArr[2]-count($girlDistrictList));
        $boyDistrictList = $schoolModel->query($boySql);
//        $leftCount = $leftCount-count($boyDistrictList);

        //同城的人
        //同区女孩
        foreach($girlDistrictList as $friend){
            array_push($noFriendFriends, $friend['id']);
        }
        //同区男孩
        foreach($boyDistrictList as $friend){
            array_push($noFriendFriends, $friend['id']);
        }

        $notInDistrictFriends = implode(',',$noFriendFriends);

        //女
        $girlSql = 'SELECT u.id FROM jlxc_user u,jlxc_school s
                    WHERE u.sex=1 AND u.school_code=s.code AND s.city_code='.$school['city_code'].'
                    AND u.id not in ('.$notInDistrictFriends.') LIMIT 0,'.((int)($countArr[3]*0.8));
        $girlCityList = $schoolModel->query($girlSql);
//        $leftCount = $leftCount-count($girlCityList);
        //男
        $boySql = 'SELECT u.id FROM jlxc_user u,jlxc_school s
                  WHERE u.sex=0 AND u.school_code=s.code AND s.city_code='.$school['city_code'].'
                  AND u.id not in ('.$notInDistrictFriends.') LIMIT 0,'.($countArr[3]-count($girlCityList));
        $boyCityList = $schoolModel->query($boySql);

        $endTime = microtime();

        //type 1自己 2好友 3同校 4朋友的朋友 5同区 6同城
        $list = array();
        $selectList = array();
//        array_push($selectList, array('type'=>1,'uid'=>$user_id));
//        //你的好友
//        foreach($friendList as $friend){
//            array_push($selectList, array('type'=>2,'uid'=>$friend['friend_id']));
//        }
//        //学校女孩
//        foreach($schoolGirlList as $friend){
//            array_push($selectList, array('type'=>3,'uid'=>$friend['id']));
//        }
//        //学校男孩
//        foreach($schoolBoyList as $friend){
//            array_push($selectList, array('type'=>3,'uid'=>$friend['id']));
//        }
//        //朋友的朋友女孩
//        foreach($girlFriendFriendList as $friend){
//            array_push($selectList, array('type'=>4,'uid'=>$friend['id']));
//        }
//        //朋友的朋友男孩
//        foreach($boyFriendFriendList as $friend){
//            array_push($selectList, array('type'=>4,'uid'=>$friend['id']));
//        }
//        //同区女孩
//        foreach($girlDistrictList as $friend){
//            array_push($selectList, array('type'=>5,'uid'=>$friend['id']));
//        }
//        //同区男孩
//        foreach($boyDistrictList as $friend){
//            array_push($selectList, array('type'=>5,'uid'=>$friend['id']));
//        }
//
//        //同城女孩
//        foreach($girlCityList as $friend){
//            array_push($selectList, array('type'=>6,'uid'=>$friend['id']));
//        }
//        //同城男孩
//        foreach($boyCityList as $friend){
//            array_push($selectList, array('type'=>6,'uid'=>$friend['id']));
//        }

        $selectList[$user_id]=1;
        //你的好友
        foreach($friendList as $friend){
            $selectList[$friend['friend_id']]=2;
        }
        //学校女孩
        foreach($schoolGirlList as $friend){
            $selectList[$friend['id']]=3;
        }
        //学校男孩
        foreach($schoolBoyList as $friend){
            $selectList[$friend['id']]=3;
        }
        //朋友的朋友女孩
        foreach($girlFriendFriendList as $friend){
            $selectList[$friend['id']]=4;
        }
        //朋友的朋友男孩
        foreach($boyFriendFriendList as $friend){
            $selectList[$friend['id']]=4;
        }
        //同区女孩
        foreach($girlDistrictList as $friend){
            $selectList[$friend['id']]=5;
        }
        //同区男孩
        foreach($boyDistrictList as $friend){
            $selectList[$friend['id']]=5;
        }
        //同城女孩
        foreach($girlCityList as $friend){
            $selectList[$friend['id']]=6;
        }
        //同城男孩
        foreach($boyCityList as $friend){
            $selectList[$friend['id']]=6;
        }

//        SELECT * FROM jlxc_user WHERE id NOT IN(1,2,3,4) ORDER BY RAND() limit 40

//        $list['friendlist'] = $friendList;
//        //同校的朋友
//        $list['schoolGirlList'] = $schoolGirlList;
//        $list['schoolBoyList'] = $schoolBoyList;
//        //朋友的朋友
//        $list['girlFriendFriendList'] = $girlFriendFriendList;
//        $list['boyFriendFriendList'] = $boyFriendFriendList;
//        //同区的朋友
//        $list['girlDistrictList'] = $girlDistrictList;
//        $list['boyDistrictList'] = $boyDistrictList;
//        //同城的朋友
//        $list['girlCityList'] = $girlCityList;
//        $list['boyCityList'] = $boyCityList;

        foreach($girlCityList as $friend){
            array_push($noFriendFriends, $friend['id']);
        }
        //同区男孩
        foreach($boyCityList as $friend){
            array_push($noFriendFriends, $friend['id']);
        }
        $notInDistrictFriends = implode(',',$noFriendFriends);

        $sql = 'SELECT * FROM jlxc_user WHERE delete_flag=0 AND id NOT IN('.$notInDistrictFriends.') ORDER BY RAND() limit '.(150-count($noFriendFriends));
        $leftList = $userModel->query($sql);
        //剩余填充
        foreach($leftList as $friend){
            $selectList[$friend['id']]=7;
        }

        $list['list']=$selectList;
        $list['time'] = ($endTime-$startTime);

        returnJson(1,'',$list);
//        echo'<br><br>'.($endTime-$startTime).' '.$startTime;

    }

    //http://localhost/jlxc_php/index.php/Home/MobileApi/add? 测试程序
    public function add(){

        //http://rest.yunba.io:8080?method=publish
        //&appkey=5316bd7179b6570f2ca6e20b&seckey=sec-qaAQOCmuFL22b0mv78hcOEyc9DzB9q0zesIfBAereaN6FAcb&topic=helllo&msg="Thistest"
        $data = array ( 'method'=>'publish',
            'appkey'=>'55ab4554c75ecd535d69b955',
            'seckey'=>'sec-UVHzd2ioXYJlOYvLjWggCcvBDAyzXDXsvhpdu9DMKr8esMoV',
            'topic'=>'jlxc20',
            'msg'=>'ff');

        $data_string = json_encode($data);
        $data_string = '{"method":"publish", "appkey":"55ab4554c75ecd535d69b955", "seckey":"sec-UVHzd2ioXYJlOYvLjWggCcvBDAyzXDXsvhpdu9DMKr8esMoV", "topic":"rocket", "msg":"just test"}';
        $ch = curl_init('http://rest.yunba.io:8080');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        $result = json_encode(curl_exec($ch));
        echo $result;

        return;
        $data = array ( 'method'=>'publish',
            'appkey'=>'53ea21cd4e9f46851d5a57b5',
            'seckey'=>'sec-QMirTLEpuNC6tIUynXXXXNfrlWDbgDV64iDnjdni4QFyXXXX',
            'topic'=>'jlxc20',
            'msg'=>'hahaf');

        $data = array ('foo' => 'bar');
        //生成url-encode后的请求字符串，将数组转换为字符串
        $data = http_build_query($data);
        $opts = array (
            'http' => array (
                'method' => 'POST',
                'header'=> "Content-type: application/json\r\n" .
                    "Content-Length: " . strlen($data) . "\r\n",
                'content' => $data
            )
        );

//        $p = '{"method":"publish", "appkey":"53ea21cd4e9f46851d5a57b5", "seckey":"sec-QMirTLEpuNC6tIUynXXXXNfrlWDbgDV64iDnjdni4QFyXXXX", "topic":"rocket", "msg":"just test"}';

        //生成请求的句柄文件
        $context = stream_context_create($opts);
        $html = file_get_contents('http://rest.yunba.io:8080', false, $context);
        print_r($html);
        echo $html.'ddf';


//        $data = array ('foo' => 'bar');
//
//        //生成url-encode后的请求字符串，将数组转换为字符串
//        $data = http_build_query($data);
//        $opts = array (
//            'http' => array (
//                'method' => 'POST',
//                'header'=> "Content-type: application/x-www-form-urlencoded\r\n" .
//                    "Content-Length: " . strlen($data) . "\r\n",
//                'content' => $data
//            )
//        );
//
//        //生成请求的句柄文件
//        $context = stream_context_create($opts);
//        $html = file_get_contents('http://localhost/jlxc_php/index.php/Home/MobileApi/add', false, $context);


//        $data = 'http://www.baidu.com';
//        // 纠错级别：L、M、Q、H
//        $level = 'L';
//        // 点的大小：1到10,用于手机端4就可以了
//        $size = 6;
//        // 下面注释了把二维码图片保存到本地的代码,如果要保存图片,用$fileName替换第二个参数false
//        //$path = "images/";
//        // 生成的文件名
//        //$fileName = $path.$size.'.png';
//        $PNG_WEB_DIR = './HelloHaQRCode/';
//        $filename = $PNG_WEB_DIR.'test.png';
//        QRcode::png($data, $filename, $level, $size);
//        return;
//
//        //$path = "images/";
//        // 生成的文件名
//        //$fileName = $path.$size.'.png';
//        QRcode::png($data, $filename, $level, $size).'111';

//        foreach($_REQUEST as $key => $value) {
//            Log::record($key.'->'.$value,'INFO');
//        }

//        print_r($_REQUEST);
        return;

        echo 111;
//        $secondCommentModel = M('jlxc_news_second_comment');
//        $secondComment = $secondCommentModel->field('count(1) count')->where('top_comment_id=23')->find();
//        echo json_encode($secondComment);
//
//        return;
//        $add = D('testtable');
//        $dataArr = array();
//
//        $data = array();
//        $data['name'] = '测试姓名2';
//        $data['age']  = 183;
//
//        $data1 = array();
//        $data1['name'] = '测试姓名1';
//        $data1['age']  = 181;
//        array_push($dataArr, $data, $data1);
//        echo $add->addAll($dataArr);
    }

    public function get1(){

//        echo urlencode($_REQUEST['username']);
//        echo json_decode(json_encode($_REQUEST['username']));
        //获取用户详细信息
        $findUser = M('jlxc_user');
        $user = $findUser->where(array('id=10'))->find();
        echo urldecode(json_encode($user));
        return;
//        return;
//        $user['name'] = urlencode($_REQUEST['username']);
        $user['name'] = $_REQUEST['username'];
        $user['update_date'] = time();
        $updateModel = D('jlxc_user');
        $ret = $updateModel->save($user);
        $user = $findUser->where(array('id=10'))->find();
        if($ret){
            returnJson(1,"保存成功",$user);
            return;
        }else{
            returnJson(0,"保存失败!");
        }

        return;
        echo pushMessage(1,"gahaha",2);
        return;

        echo $_REQUEST['username'];
        $get = M('testtable');
        $data = $get->find();
        if($data){
            echo json_encode($data);

        }else{
            echo '没有数据';
        }
    }

    //http://localhost/jlxc_php/index.php/Home/MobileApi/testImage
    public function testImage(){
        $path = './Uploads/2015-05-13/11431526535.png';
        echo substr($path, 0, strlen($path)-4);

        $image = new \Think\Image();
        $image->open('./Uploads/2015-05-13/11431526535.png');
        $ret = $image->thumb(270, 270)->save('./Uploads/2015-05-13/11431526535_sub.png');
        echo $ret;
        return;

        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
//        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        $upload->saveName  = '';
//        $upload->thumbPrefix = 'm_';
        $upload->thumb = true; //是否对上传文件进行缩略图处理
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息

//            $this->error();
            //http://localhost/www/test/index.php/Home/Index/testImage.html0
//            echo 'fail';
            print_r($upload);
            echo $upload->getError();
        }else{
            // 上传成功
            foreach($info as $file) {

                $path = $file['savepath'].$file['savename'];
                $image = new \Think\Image();
                $image->open('./Uploads/2015-05-13/11431526535.png');
                echo 'width:'.$image->width();

            }
            $okJson = array();
            $okJson['ok'] = 'ok';
            foreach($info as $file){
                $okJson[$file['savename']] = $file['savepath'];
            }
            echo json_encode($okJson);
//            $this->success('上传成功！');
        }

    }


}

