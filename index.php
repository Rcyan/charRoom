<?php
//面向对象
class TalkController{

    //5.连接数据库（访问自动连接数据库）
    private static $pdo=null;//声明静态变量保存

	public function __construct(){
		//4.如果啊a！=login，且isset中没有nickname，说明没登陆，则跳转到登陆页面
        if($_GET['a']!='login' && !isset($_SESSION['nickname'])){
        	header('Location:index.php?a=login');//跳转
        }
        if(is_null(self::$pdo)){//第一次，连接
        	try{
        		$dsn="mysql::host=127.0.0.1;dbname=talk";
        		$pdo=new PDO($dsn,'root','',array(PDO::ATTR_PERSISTENT=>TRUE));//开启持久连接
        		$pdo->query("SET NAMES UTF8");//设置字符集
        		self::$pdo= $pdo;//保存在pdo
        	}catch(Exception $e){
                die("Connect Error");//如果有错误
        	}
        }
	}
    
	public function index(){//1.聊天页面
        include './index.html';
	}
    
    //6. 获得数据
    public function get(){
    	header('Content-Type:text/event-stream');
    	header('Cache-Control:no-cache');
    	$sql="SELECT * FROM message ORDER BY time ASC LIMIT 1000";
    	$result=self::$pdo->query($sql);
    	$rows=$result->fetchAll(PDO::FETCH_ASSOC);
    	foreach ($rows as $v) {
    		$time=date('H:i:s',$v['time']);

    		//显示每一条数据
    		echo "data:[$time]<span style='color:#fff'>{$v['nickname']}</span>：{$v['content']}</br/>\n";
    	}
    	echo "retry:1000\n";//每隔一秒发送一次
    	echo "\n\n";
    	flush();
    }

	public function login(){//2.登陆页面

		//如果$_POST不为空即为提交,提交时发生
		if(!empty($_POST)){
            $_SESSION['nickname']=$_POST['nickname'];
            header('location:index.php');//写好昵称后跳转
		}
        include './login.html';
	}
    
    //插入数据
	public function put(){
		//发送的内容、时间、昵称
        $content=$_POST['content'];
        $time=time();
        $nickname=$_SESSION['nickname'];
        $sql="INSERT INTO message (content,time,nickname) VALUES ('{$content}',{$time},'{$nickname}')";
        self::$pdo -> exec($sql);
	}

	public function delete(){
		header('Content-Type:text/event-stream');
    	header('Cache-Control:no-cache');
    	$sql="TRUNCATE message";
    	$result=self::$pdo->query($sql);
	}
}

//3.实例化
session_start();//用$_SSESION之前必须要session_start()
//设置时区
date_default_timezone_set('PRC');

//开始(默认a=index，如果给a传参数，$action=参数a，否则默认$action为index)
$action=$_GET['a']=isset($_GET['a']) ? ($_GET['a']) : 'index'; 

$controller=new TalkController;
$controller->$action();

?>