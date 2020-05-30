<?php
/**
 *
 * 微信小程序接口
 *
 * @version        $Id: weapp.php 2017年11月6日14:42:04
 * @copyright      Copyright (c) 2013 - 2017, DedeMao, Inc.
 * @link           https://www.dedemao.com
 */
require_once(dirname(__FILE__)."/../../include/common.inc.php");
require_once(DEDEINC."/channelunit.class.php");
if(isset($do)) $action = $do;
if(!isset($action)) $action = 'website';
$cfg_basehost = trim($cfg_basehost);
$cfg_basehost = trim($cfg_basehost,'/');
$cfg_basehost = empty($cfg_basehost) ? $domain : $cfg_basehost;


//首页调用内容
if($action=='index'){
    if(!isset($typeid)) $typeid = 0;
    if(!isset($page)) $page = 1;
    $config = getTemplateConfig(6);
    $data = getArcList(5,$page,10,$indexFlag);
	//  $data = getArcList($categories,$page,10,$indexFlag);
    echo dedemao_json_encode($data);exit();
}

//推荐内容
if($action=='topic'){
    if(!isset($typeid)) $typeid = 0;
    if(!isset($page)) $page = 1;
    $config = getTemplateConfig(6);
    $data = getArcList(5,$page,10,$indexFlag);
    echo dedemao_json_encode($data);exit();
}

//栏目
if($action=='category'){
     $data = getCategoryList();
     $config = getTemplateConfig(6);
     echo dedemao_json_encode($data);exit();
}

//列表
if($action=='list'){
    if(!isset($typeid)) $typeid = 0;
    if(!isset($page)) $page = 1;
    $config = getTemplateConfig(6);
    $data = getArcList($categories,$page,10,$indexFlag);
	//  $data = getArcList($categories,$page,10,$indexFlag);
    echo dedemao_json_encode($data);exit();
}

//排行内容
if($action=='hot'){
    if(!isset($typeid)) $typeid = 0;
    if(!isset($page)) $page = 1;
    $config = getTemplateConfig(6);
    $indexFlag = $config['index_flag'] ? $config['index_flag'] : 0;
    $data = getHotList(5,$page,20,$indexFlag,$hotType);
//  $data = getArcList($typeid,$page,10,$indexFlag);
     echo dedemao_json_encode($data);exit();
}



//文章栏目信息
if($action=='arctype'){
    $id = intval($typeid);
    $data = getArctype($id);
    echo dedemao_json_encode($data);exit();
	
}


//文章详细
if($action=='view'){
    $id = intval($id);
    //if(!$id) $id = getLatestArcId();
	$OnClick = OnClick($id);
    $p = isset($p) ? intval($p) : 1;
    $data = getArcInfo($id,$p);
    echo dedemao_json_encode($data);exit();
}

//获取相关文章
if($action=='LikeList'){
     $arcInfo =  GetOneArchive($id);
     $data = getLikeArticles($id,$tags,$arcInfo['topid']);  
    echo dedemao_json_encode($data);exit();
}

//获取文章评论

if($action=='setFeedback'){
 if($enableComment=='1'){
 if($cfg_feedback_forbid=='Y'){
 $enableComment='0';
 $message = '系统禁止评论';
 }else{
  $enableComment='1';
  $message = '获取是否开启评论成功';
 }
       $data = array(
        'code'=>'success',
        'message'=>$message,
        'status'=>'200',
		'enableComment'=>$enableComment
    );
  echo dedemao_json_encode($data);exit();
 }
 //调用评论
if($getcomments=='1'){
	   $data = array(
        'code'=>'success',
        'message'=>'获取评论成功',
        'status'=>'200'
    );
 	 $data['data']= getFeedback($arcid);//调用
     echo dedemao_json_encode($data);exit();
}

//提交评论
if($postWeixinComment=='1'){
 //获取评论参数
    $postWeixinComment = file_get_contents('php://input');
    $postWeixinComment = json_decode($postWeixinComment, true);
    $mid =  $postWeixinComment['userLevel']['mid'];
    $aid =  $postWeixinComment['post'];
	$typeid = $postWeixinComment['typeid'] ;
	$title =  $postWeixinComment['posttitle'] ;
    $content = filterEmoji($postWeixinComment['content']);
    $username = utf82gbk($postWeixinComment['author_name']);
	$avatar = $postWeixinComment['author_url'];
    $data = setFeedback($mid,$aid,$typeid,$username,$title,$content,$avatar);//提交评论
 echo dedemao_json_encode($data);exit();
}

 }


//测试是否已经点赞
if($action=='islike'){
   $like = file_get_contents('php://input');
   $like = json_decode($like, true);
   $mid = $like['userLevel']['mid'];
   $aid = $like['postid'];
   global $dsql;
   $row = $dsql->GetOne("SELECT id FROM `#@__like`  WHERE mid='{$mid}' and aid='{$aid}'");
   if($row['id']){
    $data = array(
        'code'=>'success',
        'message'=>'你已经点过赞了！',
        'status'=>'501'
    );
  }else{ 
	 $data = array(
        'code'=>'success',
        'message'=>'你还未点赞！',
        'status'=>'200'
    );
 }
   
echo dedemao_json_encode($data);exit();
}

//文章海报二维码
if($action=='qrcodeimg'){

    $postqrcodeimg = file_get_contents('php://input');
    $postqrcodeimg = json_decode($postqrcodeimg, true);
    $postid =  $postqrcodeimg['postid'];
    $path =  $postqrcodeimg['path'];
    $data = getWinxinQrcodeImg($postid,$path);
    echo dedemao_json_encode($data);exit();
}

//点赞 喜欢
if($action=='like'){
   $like = file_get_contents('php://input');
   $like = json_decode($like, true);
   $mid = $like['userLevel']['mid'];
   $aid = $like['postid'];
   $face = $like['avatarUrl'];
   $time =  time();
   $ip = $_SERVER['REMOTE_ADDR'];
     global $dsql;
      $row = $dsql->GetOne("SELECT id FROM `#@__like`  WHERE mid='{$mid}' and aid='{$aid}'");
   if($row['id']){
    $data = array(
        'code'=>'success',
        'message'=>'你已经点过赞了！',
        'status'=>'501'
    );
  }else{ 
   $inQuery = "INSERT INTO `#@__like` (`aid` ,`mid` ,`face` ,`time` ,`ip`)  VALUES ('{$aid}','{$mid}','{$face}','{$time}','{$ip}');";
   $dsql->ExecuteNoneQuery($inQuery);
   $data = array(
        'code'=>'success',
        'message'=>'谢谢你的点赞！',
        'status'=>'200'
    );
	 global $dsql;
    $dsql->ExecuteNoneQuery2("UPDATE `#@__archives` SET goodpost = goodpost + 1 WHERE id='$aid' ");
 }
   
 echo dedemao_json_encode($data);exit();
}




//搜素
if($action=='search'){
    if(isset($q)){
        $q = utf82gbk($q);
        if(!isset($page)) $page = 1;
        $data['search'] = array(
            'title'=>gbk2utf8($q),
            'keyword'=>gbk2utf8($q)
        );
        $data= getSearchList($q,$page);
    }

    echo dedemao_json_encode($data);exit();
}
//tag
if($action=='tag'){
    if(!isset($id)) $id = 0;
    if(!isset($page)) $page = 1;
    $data['tag'] = array(
        'id'=>$id,
        'name'=>gbk2utf8(getTagname($id)),
    );
    $data['article_list'] = getTagArcList($id,$page);
    if($page==1){
        $data['article_hot']['data'] = getTagArcList($id,$page,5,'h');
        $data['article_hot']['count'] = count($data['article_hot']['data']);
    }
    $data['PAGE'] = getTagPageInfo($id,$page);
    if($data['PAGE']['LAST']) $data['article_list'][count($data['article_list'])-1]['last'] = true;
    $config = getTemplateConfig(6);
    $data['show_litpic'] = $config['show_litpic'];
    $data['position_litpic'] = $config['position_litpic'];
    echo dedemao_json_encode($data);exit();
}
//投票
if($action=='vote' && $type=='good'){
   $vote = file_get_contents('php://input');
    $vote = json_decode($vote, true);
    $id = $vote['postid'];
    global $dsql;
    $dsql->ExecuteNoneQuery2("UPDATE `#@__archives` SET goodpost = goodpost + 1 WHERE id='$id' ");
   $data = array(
        'code'=>'success',
        'message'=>'谢谢你的点赞！',
        'status'=>'200'
    );
 echo dedemao_json_encode($data);exit();
}




//添加收藏
if($action=='setFavorite'){

    $postfavorite = file_get_contents('php://input');
    $postfavorite = json_decode($postfavorite, true);
    $aid = $postfavorite['postid'];
    $userid = $postfavorite['userLevel']['mid'];
    $aid = ( isset($aid) && is_numeric($aid) ) ? $aid : 0;
    $userid = isset($userid) ? intval($userid) : 0;
    if($aid==0)
    {
        $data['code'] =0;
        $data['msg'] =gbk2utf8('文档id为空');
        echo dedemao_json_encode($data);exit();
    }
    if($userid==0)
    {
        $data['code'] =0;
        $data['msg'] =gbk2utf8('未获取到用户信息，不能收藏');
        echo dedemao_json_encode($data);exit();
    }
    $data = setFavorite($aid,$userid);
      echo dedemao_json_encode($data);exit();
}
//用户收藏
if($action=='myfavorite'){
    $userid = isset($userid) ? intval($userid) : 0;
    $page = isset($page) ? intval($page) : 1;
    $data['data'] = getFavoriteArcList($userid,$page);
    $config = getTemplateConfig(6);
    $data['code'] = 'success';
	 $data['message'] = '获取个人收藏成功';
	 $data['status'] = '200';
    echo dedemao_json_encode($data);exit();
}

//用户评论
if($action=='myfeedback'){
    $userid = isset($userid) ? intval($userid) : 0;
    $page = isset($page) ? intval($page) : 1;
    $data['data'] = getmyfeedbacklist($userid,$page);
    $config = getTemplateConfig(6);
    $data['code'] = 'success';
	 $data['message'] = '获取个人评论成功';
	 $data['status'] = '200';
    echo dedemao_json_encode($data);exit();
}

//用户喜欢点赞
if($action=='myLike'){
    $userid = isset($userid) ? intval($userid) : 0;
    $page = isset($page) ? intval($page) : 1;
    $data['data'] = getMyLikeList($userid,$page);
    $config = getTemplateConfig(6);
	
	 $data['code'] = 'success';
	 $data['message'] = '获取个人喜欢成功';
	 $data['status'] = '200';
    echo dedemao_json_encode($data);exit();
}

//评论
if($action=='feedback'){
    if(!$content)
    {
        $data['code'] =0;
        $data['msg'] =gbk2utf8('评论不能为空');
        echo dedemao_json_encode($data);exit();
    }
    $aid = ( isset($aid) && is_numeric($aid) ) ? $aid : 0;
    $userid = isset($userid) ? intval($userid) : 0;
    if($aid==0)
    {
        $data['code'] =0;
        $data['msg'] =gbk2utf8('文档id为空');
        echo dedemao_json_encode($data);exit();
    }
    if($userid==0)
    {
        $data['code'] =0;
        $data['msg'] =gbk2utf8('未获取到用户信息');
        echo dedemao_json_encode($data);exit();
    }
    $data = setFeedback();
    echo dedemao_json_encode($data);exit();
}

//获取openid  并且登陆

if($action=='getopenid'){

//获取小程序参数
    $userInfo = file_get_contents('php://input');
    $userInfo = json_decode($userInfo, true);


//配备小程序接口获取opendid
    $apiUrl   = 'https://api.weixin.qq.com/sns';
    $appId = 'wx1c34b9806b78f036';
    $appSecret = 'c2931b765c308e97c11f0959156647b1';
    $url = $apiUrl.'/jscode2session?grant_type=authorization_code'.'&js_code='.$userInfo['js_code'].'&appid='.$appId.'&secret='.$appSecret;
    $response = http($url);

//处理小程序返回参数，获得用户本站userid
      if($response){
        $openid = $response->openid;
        $memberRow = getMemberByOpenid($openid);
        $userid = $memberRow['mid'] ? $memberRow['mid'] : '';
        $res = array('openid'=>$openid,'userLevel'=>array('mid'=>$userid,'level'=>$memberRow['sex'],'levelName'=>'订阅者'),'code'=>'success','status'=>'200','message'=>'获取用户信息成功');

//如果未能获取本站用户userid  重新注册
        if (empty($userid)) {
            $userid = 'wx-'.substr($openid, 0,8);
            $uname = utf82gbk($userInfo['nickname']);
            $sex   = $userInfo['gender']==1 ? '男' : '女';
            $face = $userInfo['avatarUrl'];
            $jointime = time();
            $logintime = time();
            $joinip = GetIP();
            $loginip = GetIP();
            $inQuery = "INSERT INTO `#@__member` (`mtype` ,`userid` ,`uname` ,`sex` ,`rank` ,`face`,`jointime` ,`joinip` ,`logintime` ,`loginip`,`weapp_openid`)
       VALUES ('个人','$userid','$uname','$sex','10','$face','$jointime','$joinip','$logintime','$loginip','$openid');";
            $dsql->ExecuteNoneQuery($inQuery);
            $mid = $dsql->GetLastID();
		 	$res["code"]="success";
            $res["message"]= "用户登陆成功";
            $res["status"]="200";
			$res["level"]="1";
            $res["openid"]=$openid;
            $res["userLevel"]["mid"]=$mid; 
			$res["userLevel"]["level"]=$sex; 
			$res["userLevel"]["levelName"]=$订阅者;
        }
    }
    echo dedemao_json_encode($res);exit();
}
if($action=='updateuserinfo'){
//获取小程序参数
    $userInfo = file_get_contents('php://input');
    $userInfo = json_decode($userInfo, true);

if($userInfo['openid']){
            $uname = utf82gbk($userInfo['nickname']);
            $face = $userInfo['avatarUrl'];
			$openid = $userInfo['openid'];
 		    $inQuery = "UPDATE `#@__member` SET `uname`='$uname' ,`face`='$face' WHERE `weapp_openid`='$openid';";
			
			$dsql->ExecuteNoneQuery($inQuery);
            $mid = $dsql->GetLastID();
			
            $res["code"]="success";
            $res["message"]= "更新成功";
            $res["status"]="200";
            $res["openid"]=$openid;
            //$res["userLevel"]=$mid; 
echo dedemao_json_encode($res);exit();
}
}



function getData($name)
{
    $txt = DEDEDATA.'/module/weapp.txt';
    if(!file_exists($txt))
    {
        createWeappCache();
        return '';
    }else{
        $fp = fopen($txt,'r');
        $content = fread($fp, filesize($txt));
        fclose($fp);
        $content = unserialize($content);
        return $content[$name] ? $content[$name] : '';
    }
}
function gbk2utf8($str)
{
    global $cfg_soft_lang;
    if($cfg_soft_lang != 'utf-8'){
        return gb2utf8($str);
    }
    return $str;
}
function utf82gbk($str)
{
    global $cfg_soft_lang;
    if($cfg_soft_lang != 'utf-8'){
        return utf82gb($str);
    }
    return $str;
}
function getLatestArcId()
{
    global $dsql;
    $row = $dsql->GetOne("SELECT id FROM `#@__arctiny` WHERE arcrank = 0 order by sortrank desc");
    return $row['id'];
}
function getTemplateConfig($tid)
{
    global $apiDomain;
    $txt = DEDEDATA.'/module/weapp_template.txt';
    $content = array();
    if(file_exists($txt))
    {
        $fp = fopen($txt,'r');
        $content = fread($fp, filesize($txt));
        fclose($fp);
        $content = unserialize($content);
    }
    $configs = array();
    foreach ($content[$tid] as $k=>$v){
        if($k=='slide'){
            $configs[$k] = explode("\r\n",$v['value']);
        }else{
            $configs[$k] = $v['value'];
        }
    }
    return $configs;
}
function getMemberByOpenid($openid)
{
    global $dsql;
    $memberRow = $dsql->GetOne("SELECT mid,sex FROM `#@__member` WHERE `weapp_openid` = '{$openid}'");
    return $memberRow;
}

//列表内容处理
function formatArcList($arr,$count,$i,$config='')
{
    global $cfg_basehost;
    if(empty($config)) $config = getTemplateConfig(6);
    $timeformat = $config['timeformat'];
    $data['id'] = $arr['id'];
    $arc =  GetOneArchive($arr['id']);
	$data['typename'] = GetTypename($arc['typeid']);
    $data['title']['rendered'] = gbk2utf8($arc['title']);
    $data['post_medium_image'] = substr($arc['litpic'],0,4)=='http' ? $arc['litpic'] : 'http://img.66ui.com'.$arc['litpic'].'?imageView2/1/w/320/h/240/interlace/1/q/100|imageslim';
	$data['post_medium2_image'] = substr($arc['litpic'],0,4)=='http' ? $arc['litpic'] : 'http://img.66ui.com'.$arc['litpic'].'?imageView2/1/w/640/h/480/interlace/1/q/100|imageslim';
    $data['excerpt']['rendered'] = gbk2utf8($arc['description']);
    $pubdate = date('Y-m-d\TH:i:s',$arc['pubdate']);
    $data['date'] = $pubdate;
    $data['pageviews'] = $arc['click'];
    $data['like_count'] = getLikeNum($arr['id']);
	$data['total_comments'] = getFeedbacknum($arr['id']);
    return $data;
}

//热门排行列表内容处理
function formatHotList($arr,$count,$i,$config='')
{
    global $cfg_basehost;
    if(empty($config)) $config = getTemplateConfig(6);
    $timeformat = $config['timeformat'];
    $data['post_id'] = $arr['id'];
    $arc =  GetOneArchive($arr['id']);
	$data['post_typename'] = GetTypename($arc['typeid']);
    $data['post_title'] = gbk2utf8($arc['title']);
    $data['post_thumbnail_image']  = substr($arc['litpic'],0,4)=='http' ? $arc['litpic'] : 'http://img.66ui.com'.$arc['litpic'].'?imageView2/1/w/320/h/240/interlace/1/q/100|imageslim';
    $data['excerpt'] = gbk2utf8($arc['description']);
    $pubdate = date('Y-m-d\TH:i:s',$arc['pubdate']);
    $data['post_date'] = $pubdate;
    $data['pageviews'] = $arc['click'];
    $data['like_count'] = getLikeNum($arr['id']);
	$data['comment_total'] = getFeedbacknum($arr['id']);
    return $data;
}

//内容调用
function getArcList($typeid=0,$page=1,$limit=10,$flag='')
{
    global $dsql,$cfg_basehost;
    $sonids = $typeid;
    $where = "`arcrank` =  0";
    if($typeid==0){
        $config = getTemplateConfig(6);
        if($config['show_categorys']){
            $config['show_categorys'] = str_replace('，',',',$config['show_categorys']);
            $typeids = explode(',',$config['show_categorys']);
            $tsonids = array();
            foreach($typeids as $v){
                $tsonids[] = GetSonIds($v);
            }
            if($tsonids) $where.=" and typeid IN(".implode(',',$tsonids).")";
        }
    }
    if($typeid>0){
        $sonids = GetSonIds($typeid);
        $where.=" and typeid IN({$sonids})";
    }
    if($flag){
        $where.=" and flag like '%{$flag}%'";
    }
    $start = ($page-1)*$limit;
    $query = "SELECT id FROM `#@__archives` WHERE {$where} ORDER BY id DESC LIMIT {$start},{$limit}";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $count = $dsql->GetTotalRow();
    $count = ($count < $limit) ? $count : $limit;
    $query = "SELECT id FROM `#@__archives` WHERE {$where} ORDER BY id DESC LIMIT {$start},{$limit}";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $i=0;
    $data = array();
    $config = getTemplateConfig(6);
    while($arr=$dsql->GetArray())
    {
        $data[$i] = formatArcList($arr,$count,$i,$config);
        $i++;
    }
    return $data;
}

//热门内容调用
function getHotList($typeid=0,$page=1,$limit=10,$flag='',$hotType='')
{
    global $dsql,$cfg_basehost;
    $sonids = $typeid;
    $where = "`arcrank` =  0";
    if($typeid==0){
        $config = getTemplateConfig(6);
        if($config['show_categorys']){
            $config['show_categorys'] = str_replace('，',',',$config['show_categorys']);
            $typeids = explode(',',$config['show_categorys']);
            $tsonids = array();
            foreach($typeids as $v){
                $tsonids[] = GetSonIds($v);
            }
            if($tsonids) $where.=" and typeid IN(".implode(',',$tsonids).")";
        }
    }
    if($typeid>0){
        $sonids = GetSonIds($typeid);
        $where.=" and typeid IN({$sonids})";
    }
    if($flag){
        $where.=" and flag like '%{$flag}%'";
    }

    $start = ($page-1)*$limit;
    $query = "SELECT id FROM `#@__archives` WHERE {$where} ORDER BY {$hotType} DESC LIMIT {$start},{$limit}";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $count = $dsql->GetTotalRow();
    $count = ($count < $limit) ? $count : $limit;
    $query = "SELECT id FROM `#@__archives` WHERE {$where} ORDER BY {$hotType} DESC LIMIT {$start},{$limit}";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $i=0;
    $data = array();
    $config = getTemplateConfig(6);
    while($arr=$dsql->GetArray())
    {
        $data[$i] = formatHotList($arr,$count,$i,$config);
        $i++;
    }
    return $data;
}


//提取个人点赞
function getMyLikeList($userid=0,$page=1,$limit=10)
{
    global $dsql,$cfg_basehost;
    $where = "1=1";
    if($userid>0){
        $where.=" and mid = $userid";
    }
    $start = ($page-1)*$limit;
    $query = "SELECT aid FROM `#@__like` WHERE {$where} ORDER BY id DESC LIMIT {$start},{$limit}";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $count = $dsql->GetTotalRow();
    $count = ($count < $limit) ? $count : $limit;

    $query = "SELECT aid FROM `#@__like` WHERE {$where} ORDER BY id DESC LIMIT {$start},{$limit}";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $i=0;
    $data = array();
    $config = getTemplateConfig(6);
    while($arr=$dsql->GetArray())
    {
        $arr['id'] = $arr['aid'];
        $data[$i] = formatArcList($arr,$count,$i,$config);
        $i++;
    }

    return $data;
}

//提取个人评论
function getFavoriteArcList($userid=0,$page=1,$limit=10)
{
    global $dsql,$cfg_basehost;
    $where = "1=1";
    if($userid>0){
        $where.=" and mid = $userid";
    }
    $start = ($page-1)*$limit;
    $query = "SELECT aid FROM `#@__member_stow` WHERE {$where} ORDER BY id DESC LIMIT {$start},{$limit}";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $count = $dsql->GetTotalRow();
    $count = ($count < $limit) ? $count : $limit;

    $query = "SELECT aid FROM `#@__member_stow` WHERE {$where} ORDER BY id DESC LIMIT {$start},{$limit}";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $i=0;
    $data = array();
    $config = getTemplateConfig(6);
    while($arr=$dsql->GetArray())
    {
        $arr['id'] = $arr['aid'];
        $data[$i] = formatArcList($arr,$count,$i,$config);
        $i++;
    }

    return $data;
}



//提取用户收藏
function getmyfeedbacklist($userid=0,$page=1,$limit=10)
{
    global $dsql,$cfg_basehost;
    $where = "1=1";
    if($userid>0){
        $where.=" and mid = $userid";
    }
    $start = ($page-1)*$limit;
    $query = "SELECT aid,msg FROM `#@__feedback` WHERE {$where} ORDER BY id DESC LIMIT {$start},{$limit}";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $count = $dsql->GetTotalRow();
    $count = ($count < $limit) ? $count : $limit;

    $query = "SELECT aid,msg FROM `#@__feedback` WHERE {$where} ORDER BY id DESC LIMIT {$start},{$limit}";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $i=0;
    $data = array();
    $config = getTemplateConfig(6);
    while($arr=$dsql->GetArray())
    { 
        $data[$i]['id'] = $arr['aid'];
		$data[$i]['msg'] = $arr['msg'];
        $i++;
    }
     return $data;
}



function getSearchList($q='',$page=1,$limit=10,$flag='')
{
    global $dsql,$cfg_basehost;
    $where = "`arcrank` =  0";
    if(!empty($q)){
        $where.=" and title like '%{$q}%'";
    }
    if($flag){
        $where.=" and flag like '%{$flag}%'";
        $page=1;
    }
    $start = ($page-1)*$limit;
    $query = "SELECT id FROM `#@__archives` WHERE {$where} ORDER BY id DESC LIMIT {$start},{$limit}";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $count = $dsql->GetTotalRow();
    $count = ($count < $limit) ? $count : $limit;
    $query = "SELECT id FROM `#@__archives` WHERE {$where} ORDER BY id DESC LIMIT {$start},{$limit}";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $i=0;
    $data = array();
    $config = getTemplateConfig(6);
    while($arr=$dsql->GetArray())
    {
        $data[$i] = formatArcList($arr,$count,$i,$config);
        $i++;
    }
    return $data;
}
function getTagArcList($tagId=0,$page=1,$limit=10,$flag='')
{
    global $dsql,$cfg_basehost;
    $where = "`arcrank` =  0";
    if($tagId>0){
        $aids = getAidByTag($tagId);
        if($aids){
            $where.=" and id IN(".implode(',',$aids).")";
        }
    }
    if($flag){
        $where.=" and flag like '%{$flag}%'";
        $page=1;
    }
    $start = ($page-1)*$limit;
    $query = "SELECT id FROM `#@__archives` WHERE {$where} ORDER BY id DESC LIMIT {$start},{$limit}";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $count = $dsql->GetTotalRow();
    $count = ($count < $limit) ? $count : $limit;

    $query = "SELECT id FROM `#@__archives` WHERE {$where} ORDER BY id DESC LIMIT {$start},{$limit}";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $i=0;
    $data = array();
    $config = getTemplateConfig(6);
    while($arr=$dsql->GetArray())
    {
        $data[$i] = formatArcList($arr,$count,$i,$config);
        $i++;
    }
    return $data;
}

function getPageInfo($typeid=0,$page=1,$limit=10,$flag='')
{
    global $dsql;
    $sonids = $typeid;
    $where = "`arcrank` =  0";
    if($typeid>0){
        $sonids = GetSonIds($typeid);
        $where.=" and typeid IN({$sonids})";
    }
    if($flag){
        $where.=" and flag like '%{$flag}%'";
    }
    $query = "SELECT count(*) c FROM `#@__archives` WHERE {$where} ORDER BY id DESC";
    $row = $dsql->GetOne($query);
    $totalPage = ceil($row['c']/$limit);
    $data = array(
        'COUNT'=>intval($row['c']),
        'TOTAL'=>$totalPage,
        'CURRENT'=>$page,
        'PN'=>$page,
        'LAST'=>$page>=$totalPage
    );
    return $data;
}
function getArcInfo($id,$page=1)
{
    global $dsql,$cfg_basehost,$cfg_phpurl,$ver;
    $id = intval($id);
    $arcInfo =  GetOneArchive($id);
    $chRow = $dsql->GetOne("SELECT arc.*,ch.maintable,ch.addtable,ch.issystem FROM `#@__arctiny` arc LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel WHERE arc.id='$id' ");
    $bodyInfo = $dsql->GetOne("SELECT * FROM `{$chRow['addtable']}` WHERE aid='{$id}'");
    $bodyInfo['body'] = gbk2utf8($bodyInfo['body']);
    $pageCounts = count(explode('#p#副标题#e#',$bodyInfo['body']));
    $pageArray = array();
    for($i=1;$i<=$pageCounts;$i++){
        $pageArray[$i]['no'] = $i;
        $pageArray[$i]['pageinfo'] = gbk2utf8('第'.$i.'页');
    }
    $config = getTemplateConfig(6);
    $arcInfo['haspic'] = empty($arcInfo['litpic']) ? 2 : 1;
    $arcInfo['litpic'] = empty($arcInfo['litpic']) ? '/images/defaultpic.gif' : $arcInfo['litpic'];
    $timeformat = $config['timeformat'] ? $config['timeformat'] : 1;
    $pubdate = date('Y-m-d\TH:i:s',$arcInfo['pubdate']);
    if($timeformat==2) $pubdate = date('Y-m-d',$arcInfo['pubdate']);
    if($timeformat==3) $pubdate = date('m-d',$arcInfo['pubdate']);
    $config['jump_img'] = $config['jump_img'] ? $config['jump_img'] : '';
    if($config['jump_img'] && strstr($config['jump_img'],'http')===false){
        $config['jump_img'] = $cfg_basehost.$config['jump_img'];
    }
    $data = array(
        'id'=>$id,
        'typeid'=>$arcInfo['typeid'],
        'category_name'=>GetTypename($arcInfo['typeid']),
        'source'=>gbk2utf8($arcInfo['source']),
        'author'=>gbk2utf8($arcInfo['writer']),
		'date'=>$pubdate,
        'title'=>array('rendered'=>gbk2utf8($arcInfo['arctitle']),),
		'pic'=>array( 'url'=>strstr($arcInfo['litpic'],'http')!==false ? $arcInfo['litpic'] : $cfg_basehost.$arcInfo['litpic'] ),
		'postImageUrl'=>strstr($arcInfo['litpic'],'http')!==false ? $arcInfo['litpic'] : $cfg_basehost.$arcInfo['litpic'] ,
 		'like_count'=>getLikeNum($id),
	    'total_comments' => getFeedbacknum($id),
		'pageviews'=>$arcInfo['click'],
		'tags'=> gbk2utf8($arcInfo['keywords']),
		'excerpt' =>array(rendered=>$arcInfo['description']),
     );
	$ArcArr = GetOneArchive($id);
    $data['link'] = 'http://4g.66ui.com'.$ArcArr['arcurl'];
	$data['avatarurls'] = getlike($id);
	$body = preg_replace ( "/\s(?=\s)/","\\1", $bodyInfo['body']);
	$body = str_replace(array("\r\n", "\r", "\n"), "", $body);
	$body = str_replace("<br />	<br />","<dr />",$body);
	$body = str_replace("<br /><br />","<dr />",$body);
	$body = preg_replace("/<[^\/>]*>([\s]?)*<\/[^>]*>/s","", $body)	;
    $data['content']['rendered'] =  videoFilter(replaceUrl($body,$page));
    $prevNext = GetPreNext($id,$arcInfo['typeid']);
    $data['article_prev'] = $prevNext['pre'];
    $data['article_next'] = $prevNext['next'];
	$data['excerpt']['protected'] = 'false';
   //$data['article_list'] =getLikeArticles($id,$arcInfo['keywords'],$arcInfo['topid']);       //相关文章
     return $data;
}

function getSinglePageContent($typeid)
{
    global $dsql,$cfg_basehost,$cfg_phpurl;
    $typeid = intval($typeid);

    $row = $dsql->GetOne("SELECT content FROM `#@__arctype` WHERE id='{$typeid}'");
    $data['content'] = html2Wxml($row['content']);
    return $data;
}
//文章栏目信息
function getArctype($id)
{
    global $dsql;
    $row = $dsql->GetOne("SELECT * FROM `#@__arctype` WHERE id='{$id}'");
	$data['name'] = $row['typename'] ;
	$data['description'] = $row['description'] ;
 	$data['category_thumbnail_image'] ='http://img.66ui.com/plus/wxjson/categorys/'.$row['id'].'-1.png';
    return $data;
}
/**
 * 获取文章收藏数量
 * @param $id
 * @return mixed
 */
function getFavoriteCount($id)
{
    global $dsql;
    $row = $dsql->GetOne("SELECT count(*) c FROM `#@__member_stow` WHERE aid = {$id}");
    return $row['c'];
}

/**
 * 添加收藏
 * @param $aid
 * @param $mid
 * @return mixed
 */
function setFavorite($aid,$mid)
{
    global $dsql;
    $row = $dsql->GetOne("SELECT count(*) c FROM `#@__member_stow` WHERE aid = {$aid} and mid = {$mid}");
    $arc =  GetOneArchive($aid);
    $time =  time();
    if($row['c']==0){
        //添加收藏
        $inQuery = "INSERT INTO `#@__member_stow` (`mid` ,`aid` ,`title` ,`addtime`)
       VALUES ('{$mid}','{$aid}','{$arc['title']}','{$time}');";
        $dsql->ExecuteNoneQuery($inQuery);
         $data = array(
        'code'=>'success',
        'message'=>'添加成功',
        'status'=>'200'
        );	
		
    }else{
        //取消收藏
        $inQuery = "DELETE FROM `#@__member_stow` where mid = '{$mid}' and aid = '{$aid}'";
        //$dsql->ExecuteNoneQuery($inQuery);
        $data = array(
        'code'=>'success',
        'message'=>'谢谢，已经收藏',
        'status'=>'501'
        );	
    }
    return $data;
}

/**
 * 添加评论
 * @param $aid
 * @param $mid
 * @return mixed
 */
function setFeedback()
{
    global $dsql,$mid,$aid,$typeid,$username,$title,$content,$avatar;
	
	$time =  time();
    $ip = $_SERVER['REMOTE_ADDR'];
    //$arc =  $postWeixinComment['post'];
    //$content = filterEmoji($postWeixinComment['content']);
    //$username = utf82gbk($postWeixinComment['author_name']);
    //$content = utf82gbk($content);
    //$mid = intval($postWeixinComment['userLevel']);
    $inQuery = "INSERT INTO `#@__feedback` (`mid` ,`aid` ,`typeid`,`username`,`arctitle` ,`dtime`,`msg`,`avatar`,`ip`)
   VALUES ('{$mid}','{$aid}','{$typeid}','{$username}','{$title}','{$time}','{$content}','{$avatar}','{$ip}')";
 
    $dsql->ExecuteNoneQuery($inQuery);
	
    $data = array(
        'code'=>'success',
        'message'=>'评论成功',
        'status'=>'200'
    );

    return $data;
}
// 过滤掉emoji表情
function filterEmoji($str)
{
    return preg_replace('/\xEE[\x80-\xBF][\x80-\xBF]|\xEF[\x81-\x83][\x80-\xBF]/', '', $str);
}
/**
 *  获取上一篇，下一篇
 */
function GetPreNext($aid,$typeid)
{
    global $dsql,$cfg_basehost;
    $preR =  $dsql->GetOne("Select id From `#@__arctiny` where id<$aid And arcrank>-1 And typeid='{$typeid}' order by id desc");
    $nextR = $dsql->GetOne("Select id From `#@__arctiny` where id>$aid And arcrank>-1 And typeid='{$typeid}' order by id asc");
    $next = (is_array($nextR) ? " where arc.id={$nextR['id']} " : ' where 1>2 ');
    $pre = (is_array($preR) ? " where arc.id={$preR['id']} " : ' where 1>2 ');
    $query = "Select arc.id,arc.title,arc.shorttitle,arc.typeid,arc.ismake,arc.senddate,arc.arcrank,arc.money,arc.filename,arc.litpic,
                    t.typedir,t.typename,t.namerule,t.namerule2,t.ispart,t.moresite,t.siteurl,t.sitepath
                    from `#@__archives` arc left join #@__arctype t on arc.typeid=t.id  ";
    $nextRow = $dsql->GetOne($query.$next);
    $preRow = $dsql->GetOne($query.$pre);
    $preData = array();
    $nextData = array();
    if(is_array($preRow))
    {
        $preData['id'] = $preRow['id'];
        $preData['title'] = gbk2utf8($preRow['title']);
        $preData['pic'] = array(
            'url'=>strstr($preRow['litpic'],'http') ? $preRow['litpic'] : $cfg_basehost.$preRow['litpic']
        );
    }

    if(is_array($nextRow))
    {
        $nextData['id'] = $nextRow['id'];
        $nextData['title'] = gbk2utf8($nextRow['title']);
        $nextData['pic'] = array(
            'url'=>strstr($nextRow['litpic'],'http') ? $nextRow['litpic'] : $cfg_basehost.$nextRow['litpic']
        );
    }
    return array(
        'pre'=>$preData,
        'next'=>$nextData,
    );
}

/**
 * 获取相关文章
 */
function getLikeArticles($arcid,$keyword,$topid=1,$row=5)
{
    global $dsql,$cfg_basehost;
    $ids = array();
    $tids = array();
     $typeid = GetSonIds($topid);
     $keywords = explode(',' , trim($keyword));
    $keyword = '';
    $n = 1;
    foreach($keywords as $k)
    {
        if($n > 3)  break;
        if(trim($k)=='') continue;
        else $k = addslashes($k);
        $keyword .= ($keyword=='' ? " CONCAT(arc.keywords,' ',arc.title) LIKE '%$k%' " : " OR CONCAT(arc.keywords,' ',arc.title) LIKE '%$k%' ");
        $n++;
    }
    $arcid = intval($arcid);
    $orderquery = " ORDER BY arc.id desc ";
    if($keyword != '')
    {
        if(!empty($typeid)) {
            $typeid = " AND arc.typeid IN($typeid) AND arc.id<>$arcid ";
        }
        $query = "SELECT arc.*,tp.typedir,tp.typename,tp.corank,tp.isdefault,tp.defaultname,tp.namerule,
                  tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath
                  FROM `#@__archives` arc LEFT JOIN `#@__arctype` tp ON arc.typeid=tp.id
                  where arc.arcrank>-1 AND ($keyword)  $typeid $orderquery limit 0, $row";
    }
    else
    {
        if(!empty($typeid)) {
            $typeid = " arc.typeid IN($typeid) AND arc.id<>$arcid ";
        }
        $query = "SELECT arc.*,tp.typedir,tp.typename,tp.corank,tp.isdefault,tp.defaultname,tp.namerule,
                  tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath
                  FROM `#@__archives` arc LEFT JOIN `#@__arctype` tp ON arc.typeid=tp.id
                 WHERE arc.arcrank>-1 AND  $typeid $orderquery limit 0, $row";
    }
    $dsql->SetQuery($query);
    $dsql->Execute('al');
    $data = array();
    $count = $row;
    $config = getTemplateConfig(6);
    $timeformat = $config['timeformat'];

    for($i=0; $i < $count; $i++){
        $item = $dsql->GetArray("al");
        if(empty($item)){
            break;
        }
        $data[$i]['id'] = $item['id'];
        $data[$i]['title'] =gbk2utf8($item['title']);
        $data[$i]['haspic'] = empty($item['litpic']) ? 2 : 1;
        $litpic = empty($item['litpic']) ? '/images/defaultpic.gif' : $item['litpic'];
        $data[$i]['pic']['src'] = substr($litpic,0,4)=='http' ? $litpic : $cfg_basehost.$litpic;
        $data[$i]['pic']['url'] = $data[$i]['pic']['src'];
        $data[$i]['description'] =gbk2utf8($item['description']);
        $pubdate = date('Y-m-d H:i:s',$item['pubdate']);
        if($timeformat==2) $pubdate = date('Y-m-d',$item['pubdate']);
        if($timeformat==3) $pubdate = date('m-d',$item['pubdate']);
        $data[$i]['pubdate'] = $pubdate;
    }
    return $data;
}

/**
 * 获取评论
 */
 
 
 function getFeedbacknum($id)
{

global $dsql;
$row = $dsql->GetOne("Select count(id) as c from #@__feedback where aid=$id");
$num=$row['c'];
if($num==0)$num='0';
return $num;
}


/**
 * 获取点赞数量
 */
 
 
 function getLikeNum($id)
{
global $dsql;
$row = $dsql->GetOne("Select count(id) as c from #@__like where aid=$id");
$num=$row['c'];
if($num==0)$num='0';
return $num;
}
 
 
//调用评论详细内容
 
function getFeedback($arcid,$start=0,$size=5)
{
    global $dsql,$cfg_basehost;
    $arcid = intval($arcid);
    $orderquery = " ORDER BY id desc ";
    $config = getTemplateConfig(6);
    $addwhere = "aid = {$arcid}";
    if($config['feedback_type']==2){
        //$addwhere.=" and ischeck=1 ";
    }
	$addwhere.=" and ischeck=1 ";
    $query = "SELECT * from `#@__feedback` where {$addwhere} {$orderquery} limit {$start},{$size}";
    $dsql->SetQuery($query);
    $dsql->Execute('al');
    $data = array();
    for($i=0; $i < $size; $i++){
        $item = $dsql->GetArray("al");
        if(empty($item)){
            break;
        }
        $data[$i]['id'] = $item['id'];
        $data[$i]['content'] =gbk2utf8($item['msg']);
        $data[$i]['author_name'] =gbk2utf8($item['username']);
        $data[$i]['author_url'] = $item['avatar'];
        $pubdate = date('Y-m-d H:i:s',$item['dtime']);
        $data[$i]['pubdate'] = $pubdate;
        $data[$i]['pubdate'] = gbk2utf8(time_tran($item['dtime']));
    }
return $data;
		
	 
}


 
//调用评论详细内容
 
function getlike($arcid,$start=0,$size=50)
{
    global $dsql;
    $arcid = intval($arcid);
    $orderquery = " ORDER BY id desc ";
    $config = getTemplateConfig(6);
    $addwhere = "aid = {$arcid}";
    if($config['feedback_type']==2){
        $addwhere.=" and ischeck=1 ";
    }
    $query = "SELECT * from `#@__like` where {$addwhere} {$orderquery} limit {$start},{$size}";
    $dsql->SetQuery($query);
    $dsql->Execute('al');
    $data = array();
    for($i=0; $i < $size; $i++){
        $item = $dsql->GetArray("al");
        if(empty($item)){
            break;
        }
        $data[$i]['avatarurl'] = $item['face'];
    }
return $data;
		
	 
}


/**
 * 计算几分钟前、几小时前、几天前、几月前、几年前。
 * $agoTime string Unix时间
 * @author tangxinzhuan
 * @version 2016-10-28
 */
function time_tran($agoTime)
{
    $agoTime = (int)$agoTime;
    // 计算出当前日期时间到之前的日期时间的毫秒数，以便进行下一步的计算
    $time = time() - $agoTime;
    if ($time >= 31104000) { // N年前
        $num = (int)($time / 31104000);
        return $num.'年前';
    }
    if ($time >= 2592000) { // N月前
        $num = (int)($time / 2592000);
        return $num.'月前';
    }
    if ($time >= 86400) { // N天前
        $num = (int)($time / 86400);
        return $num.'天前';
    }
    if ($time >= 3600) { // N小时前
        $num = (int)($time / 3600);
        return $num.'小时前';
    }
    if ($time > 120) { // N分钟前
        $num = (int)($time / 60);
        return $num.'分钟前';
    }
    return '刚刚';
}

function html2Wxml($content,$page=1)
{
    global $cfg_basehost;
    $contentPage = explode('#p#副标题#e#',$content);
    $content = $contentPage[$page-1];
    $_arr = preg_split('/(<img.*?>)/i', $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    $_r = array();
    $i=0;
    foreach($_arr as $_txt) {
        if(substr($_txt, 0, 4) == '<img') {
            $_matchs = array();
            preg_match('/<img.*?src="(.*?)"/i', $_txt, $_matchs);
            $_txt = $_matchs[1];
            if(empty($_txt)) continue;
            if(preg_match('/^\//', $_txt)) $_txt = strstr($_txt,'http') ? $_txt : $cfg_basehost.$_txt;
            $_r[]= array('index'=>$i,'type'=>'img', 'data'=>$_txt);
            $i++;
        }else {
            $_txt = preg_replace('/&.*?;/', ' ', $_txt);
            $_txt = preg_replace('/\s+/', ' ', $_txt);
            $_txt = preg_replace(array('/<br.*?>/i', '/<p.*?>/i', '/<li.*?>/i', '/<div.*?>/i', '/<tr.*?>/i', '/<th.*?>/i'),
                "\n", $_txt);
            $_txt = preg_replace('/<.*?>/', '', $_txt);
            $_txt2 = str_replace("\n","",$_txt);
            $_txt2 = trim($_txt2);
            if(empty($_txt2)) continue;
            $_r[]= array('type'=>'txt', 'data'=>$_txt);
        }
    }
   //$_r = strtr($_r, 'http://', 'http://'); 
    return $_r;
}
function replaceUrl($content,$page=1)
{
    global $cfg_basehost;
    $contentPage = explode('#p#副标题#e#',$content);
    $content = $contentPage[$page-1];

    $_arr = preg_split('/(<img.*?>)/i', $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    $_r = array();
    $i=0;
    foreach($_arr as $_txt) {
        if(substr($_txt, 0, 4) == '<img') {
            $_matchs = array();
            preg_match('/<img.*?src="(.*?)"/i', $_txt, $_matchs);
            $_txt = $_matchs[1];
            if(empty($_txt)) continue;
            if(preg_match('/^\//', $_txt)) $_txt = strstr($_txt,'http') ? $_txt : 'http://img.66ui.com'.$_txt.'?imageView2/2/w/640/interlace/1/q/100|imageslim';
            $_r[]= "<img src='$_txt'>";
            $i++;
        }else {
            $_r[]= $_txt;
        }
    }
    $content = implode('',$_r);
    $_arr = preg_split('/(<video.*?>)/i', $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    $_r = array();
    $i=0;
    foreach($_arr as $_txt) {
        if(substr($_txt, 0, 6) == '<video') {
            $_matchs = array();
            preg_match('/<video.*?src="(.*?)"/i', $_txt, $_matchs);
            $_txt = $_matchs[1];
            if(empty($_txt)) continue;
            if(preg_match('/^\//', $_txt)) $_txt = strstr($_txt,'http') ? $_txt : $cfg_basehost.$_txt;
            $_r[]= "<video src='$_txt' enable-danmu danmu-btn controls></video>";
            $i++;
        }else {
            $_r[]= $_txt;
        }
    }
    return implode('',$_r);
}

function getImgList($content,$page=1)
{
    global $cfg_basehost;
    $contentPage = explode('#p#副标题#e#',$content);
    $content = $contentPage[$page-1];
    $_arr = preg_split('/(<img.*?>)/i', $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    $_r = array();
    $i=0;
    foreach($_arr as $_txt) {
        if(substr($_txt, 0, 4) == '<img') {
            $_matchs = array();
            preg_match('/<img.*?src="(.*?)"/i', $_txt, $_matchs);
            $_txt = $_matchs[1];
            if(empty($_txt)) continue;
            if(preg_match('/^\//', $_txt)) $_txt = strstr($_txt,'http') ? $_txt : $cfg_basehost.$_txt;
            $_r[]= $_txt;
            $i++;
        }
    }
	//$_r = strtr($_r, 'http://', 'http://');  
    return $_r;
}

function getCategoryList()
{
    $config = getTemplateConfig(6);
    global $dsql;
    if($config['show_categorys']){
        $config['show_categorys'] = str_replace('，',',',$config['show_categorys']);
        $query = "Select * From `#@__arctype` where topid = 5 AND channeltype = 1 and id in ({$config['show_categorys']}) order by `sortrank` asc limit 0,50";
        $row = $dsql->GetOne($query);
        if(empty($row)){
            $where = " and id in ({$config['show_categorys']})";
        }else{
            $where = " AND topid = 5 AND id in ({$config['show_categorys']})";
        }
    }else{
        $where = " and topid = 5 ";
    }
    $query = "Select * From `#@__arctype` where channeltype = 1 {$where} order by `sortrank` asc limit 0,50";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $i=0;
    $categorys = array();
    while($arr=$dsql->GetArray()){
        if($arr['ispart']==2) continue;
        if($arr['ishidden']==1) continue;
        if($config['show_categorys']){
            $show_categorys = explode(',',$config['show_categorys']);
            if(in_array($arr['id'],$show_categorys)===false){
                continue;
            }
        }
        $categorys[$i]['id'] = $arr['id'];
        $categorys[$i]['name']=gbk2utf8($arr['typename']);
       	$categorys[$i]['description'] = $arr['description'] ;
 	    $categorys[$i]['category_thumbnail_image'] ='http://img.66ui.com/plus/wxjson/categorys/'.$arr['id'].'.png';
        $i++;
    }
    //$categorys['tag_list'] = getTagList();
    return $categorys;
}

function isHasChild($typeid)
{
    global $cfg_Cs;
    if(!is_array($cfg_Cs))
    {
        require_once(DEDEDATA."/cache/inc_catalog_base.inc");
    }
    foreach ($cfg_Cs as $tid=>$item){
        if($item[0]==$typeid){
            return true;
        }
    }
    return false;
}

function getChildListCategory($typeid)
{
    global $cfg_Cs, $dsql;
    if(!is_array($cfg_Cs))
    {
        require_once(DEDEDATA."/cache/inc_catalog_base.inc");
    }
    $categorys['hasChildList'] = false;
    foreach ($cfg_Cs as $tid=>$item){
        if($item[0]==$typeid && $item[1]==1){
            $row = $dsql->GetOne("SELECT ispart FROM #@__arctype WHERE id ='{$tid}'");
            if($row['ispart']==0){
                $categorys['tid'] = $tid;
                $categorys['hasChildList'] = true;
                break;
            }
            if($row['ispart']==1){
                //封面栏目
                $categorys['tid'] = $tid;
                break;
            }
        }
    }
    return $categorys;
}

function GetTypename($tid)
{
    global $dsql;
    if (empty($tid)) return '';
    if (file_exists(DEDEDATA.'/cache/inc_catalog_base.inc'))
    {
        require_once(DEDEDATA.'/cache/inc_catalog_base.inc');
        global $cfg_Cs;
        if (isset($cfg_Cs[$tid]))
        {
            return gbk2utf8(base64_decode($cfg_Cs[$tid][3]));
        }
    } else {
        $row = $dsql->GetOne("SELECT typename FROM #@__arctype WHERE id = '{$tid}'");
        unset($dsql);
        unset($cfg_Cs);
        return isset($row['typename'])? gbk2utf8($row['typename']) : '';
    }
    return '';
}
function getTagList()
{
    global $dsql;
    $config = getTemplateConfig(6);
    $tagCount = $config['tag_count'] ? intval($config['tag_count']) : 100;
    if($tagCount>100) $tagCount=100;
    $query = "Select id,tag From `#@__tagindex` order by `count` desc limit 0,{$tagCount}";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $i=0;
    $data = array();
    while($arr=$dsql->GetArray()){
        $data[$i]['id'] = $arr['id'];
        $data[$i]['name'] = gbk2utf8($arr['tag']);
        $i++;
    }
    return $data;
}
function getTagListByAid($id=0)
{
    global $dsql;
    $tags = array();
    if($id>0){
        $tags = '';
        $query = "SELECT tid,tag FROM `#@__taglist` WHERE aid='$id' ";
        $dsql->Execute('tag',$query);
        $i=0;
        while($row = $dsql->GetArray('tag'))
        {
            $tags[$row['tid']]['id'] = $row['tid'];
            $tags[$row['tid']]['name'] = gbk2utf8($row['tag']);
            $i++;
        }
    }
    return $tags;
}
function getAidByTag($tagId=0)
{
    global $dsql;
    $where = "`arcrank` =  0 and tid = {$tagId}";
    $query = "SELECT aid FROM `#@__taglist` WHERE {$where} ORDER BY aid DESC limit 0,30";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $i=0;
    $data = array();
    while($arr=$dsql->GetArray())
    {
        $data[] = $arr['aid'];
    }
    return $data;
}

function getTagPageInfo($tagId=0,$page=1,$limit=10,$flag='')
{
    global $dsql;
    $where = "`arcrank` =  0";
    if($tagId>0){
        $aids = getAidByTag($tagId);
        if($aids){
            $where.=" and id IN(".implode(',',$aids).")";
        }
    }
    if($flag){
        $where.=" and flag like '%{$flag}%'";
        $page=1;
    }
    $query = "SELECT count(*) c FROM `#@__archives` WHERE {$where} ORDER BY id DESC";
    $row = $dsql->GetOne($query);
    $totalPage = ceil($row['c']/$limit);
    $data = array(
        'COUNT'=>intval($row['c']),
        'TOTAL'=>$totalPage,
        'CURRENT'=>$page,
        'PN'=>$page,
        'LAST'=>$page>=$totalPage
    );
    return $data;
}
function getTagname($id)
{
    global $dsql;
    $row = $dsql->GetOne("SELECT tag FROM #@__tagindex WHERE id = '{$id}'");
    return isset($row['tag'])? $row['tag'] : '';
}

function getSearchPageInfo($q='',$page=1,$limit=10,$flag='')
{
    global $dsql;
    $where = "`arcrank` =  0";
    if(!empty($q)){
        $where.=" and title like '%{$q}%'";
    }
    if($flag){
        $where.=" and flag like '%{$flag}%'";
        $page=1;
    }
    $query = "SELECT count(*) c FROM `#@__archives` WHERE {$where} ORDER BY id DESC";
    $row = $dsql->GetOne($query);
    $totalPage = ceil($row['c']/$limit);
    $data = array(
        'COUNT'=>intval($row['c']),
        'TOTAL'=>$totalPage,
        'CURRENT'=>$page,
        'PN'=>$page,
        'LAST'=>$page>=$totalPage
    );
    return $data;
}


function getFavoritePageInfo($userid=0,$page=1,$limit=10)
{
    global $dsql;
    $where = "1=1";
    if($userid>0){
        $where.=" and mid = $userid";
    }
    $query = "SELECT count(*) c FROM `#@__member_stow` WHERE {$where} ORDER BY id DESC";
    $row = $dsql->GetOne($query);
    $totalPage = ceil($row['c']/$limit);
    $data = array(
        'COUNT'=>intval($row['c']),
        'TOTAL'=>$totalPage,
        'CURRENT'=>$page,
        'PN'=>$page,
        'LAST'=>$page>=$totalPage
    );
    return $data;
}
function http($url, $POSTFIELDS=null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

    if($POSTFIELDS){
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS);
    }

    $response = curl_exec ($ch);
    curl_close ($ch);

    if(empty($response)){
        return '-100000';
    }
    return json_decode($response);
}
//body 内容处理
function videoFilter($content) {
    preg_match('/https\:\/\/v.qq.com\/x\/(\S*)\/(\S*)\.html/',$content,$matches);
    if($matches)
    {
        $vids=$matches[2];
        $url='http://vv.video.qq.com/getinfo?vid='.$vids.'&defaultfmt=auto&otype=json&platform=11001&defn=fhd&charge=0';
        $res = file_get_contents($url);
        if($res)
        {
            $str = substr($res,13,-1);
            $newStr =json_decode($str,true);
            $videoUrl= $newStr['vl']['vi'][0]['ul']['ui'][0]['url'].$newStr['vl']['vi'][0]['fn'].'?vkey='.$newStr['vl']['vi'][0]['fvkey'];
            $contents = preg_replace('~<video (.*?)></video>~s','<video src="'.$videoUrl.'" controls="controls" width="100%"></video>',$content);
            return $contents;
        }
        else
        {
            return $content;
        }
    }
    else
    {
	         return $content;
    }
}



//
function dedemao_json_encode($str)
{
	ob_clean();
	return json_encode($str);
}

//访问点击
function OnClick($aid){
      global $dsql;
	 $dsql->ExecuteNoneQuery(" UPDATE `#@__archives` SET click=click+1 WHERE id='$aid' ");
   }


//小程序二维码创建
    function getWinxinQrcodeImg($postid,$path)
    {

      $qrcodeName = 'qrcode-'.$postid.'.png';//文章小程序二维码文件名     
      $qrcodeurl = 'D:/WEB/www.66ui.com/plus/wxjson/qrcode/'.$qrcodeName;//文章小程序二维码路径
      $qrcodeimgUrl = 'https://app.66ui.com/plus/wxjson/qrcode/'.$qrcodeName;
        //自定义参数区域，可自行设置      
	   $appId = 'wx1c34b9806b78f036';
       $appSecret = 'c2931b765c308e97c11f0959156647b1';
       
        //判断文章小程序二维码是否存在，如不存在，在此生成并保存
        if(!is_file($qrcodeurl)) {
            //$ACCESS_TOKEN = getAccessToken($appid,$appsecret,$access_token);
            $access_token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appId.'&secret='.$appSecret;
             $access_token_result = file_get_contents($access_token_url);
              if($access_token_result !="ERROR")
              {
                $access_token_array= json_decode($access_token_result,true);
                if(empty($access_token_array['errcode']))
                {
                  $access_token =$access_token_array['access_token'];
                  if(!empty($access_token))
                  {

                    //接口A小程序码,总数10万个（永久有效，扫码进入path对应的动态页面）
                    $url = 'https://api.weixin.qq.com/wxa/getwxacode?access_token='.$access_token;
 
                    //接口B小程序码,不限制数量（永久有效，将统一打开首页，可根据scene跟踪推广人员或场景）
                    //$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$ACCESS_TOKEN;
                    //接口C小程序二维码,总数10万个（永久有效，扫码进入path对应的动态页面）
                    //$url = 'http://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token='.$ACCESS_TOKEN;

                    //header('content-type:image/png');
                    $color = array(
                        "r" => "0",  //这个颜色码自己到Photoshop里设
                        "g" => "0",  //这个颜色码自己到Photoshop里设
                        "b" => "0",  //这个颜色码自己到Photoshop里设
                    );
                    $data = array(
                        //$data['scene'] = "scene";//自定义信息，可以填写诸如识别用户身份的字段，注意用中文时的情况
                        //$data['page'] = "pages/index/index";//扫码后对应的path，只能是固定页面
                        'path' => $path, //前端传过来的页面path
                        'width' => intval(100), //设置二维码尺寸
                        'auto_color' => false,
                        'line_color' => $color,
                    );
                    $data = json_encode($data);
                    //可在此处添加或者减少来自前端的字段
                    $QRCode = http_post($url,$data);//小程序二维码
                    if($QRCode !='error')
                    {
                      //输出二维码
                      file_put_contents($qrcodeurl,$QRCode);
                      //imagedestroy($QRCode);
                      $flag=true;
                    }
                    
                  }
                  else
                  {
                    $flag=false;
                  }

                }
                else
                {
                  $flag=false;
                }

              }
              else
              {
                $flag=false;
              }
            
        }
        else
        {

          $flag=true;
        }

        if($flag)
        {
          $result["code"]="success";
            $result["message"]= "小程序码创建成功";
            $result["qrcodeimgUrl"]=$qrcodeimgUrl; 
            $result["status"]="200"; 
            

        }
        else {
            $result["code"]="success";
            $result["message"]= "小程序码创建失败"; 
            $result["status"]="500"; 
            
        } 

       // $response = rest_ensure_response( $result);
        return $result;
      
    }

  function http_post($url,$data,$timeout = 60)
    {
        //curl验证成功
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//// 跳过证书检查 
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ));
 
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            print curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
 

?>
