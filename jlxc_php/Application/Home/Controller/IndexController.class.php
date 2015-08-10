<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    protected $autoCheckFields =false;
    public function index(){
//		echo "haha\n";
//        echo C('TestConfig');
//        $this->display();
        echo U("MobileApi/getMobileVerify");
        echo '<br>';
        echo U("MobileApi/verifySms");
        echo '<br>';
        echo U("MobileApi/regist");
        echo '<br>';

        echo phpinfo();
    }

public function add(){
    $add = D('testtable');
    $data = array();
    $data['name'] = '测试姓名';
    $data['age']  = 18;
    $add->add($data);
}

public function get(){
    echo $_REQUEST['username'];
    $get = M('testtable');
    $data = $get->find();
    if($data){
        echo json_encode($data);

    }else{
        echo '没有数据';
    }
}
    public function testImage(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
//        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        $upload->saveName  = '';
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
//            $this->error();
            //http://localhost/www/test/index.php/Home/Index/testImage.html0
//            echo 'fail';
            echo $upload->getError();
        }else{// 上传成功
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

