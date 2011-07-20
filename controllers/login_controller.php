<?php

require_once(BASE_DIR.'/cake/app/controllers/components/opensocial_get_user.php');
require_once(BASE_DIR.'/cake/app/controllers/components/JSON.php');

class LoginController extends AppController{

  var $uses = array('Member','Ship','OwnWepon');

  function mixi_login(){
    $mixi_account_id = $this->params['form']['id'];
    $mixi_name = $this->params['form']['name'];
    if(strlen($mixi_account_id)==0){
      self::s_t_out();
    }
    $this->Session->write("RefleshImgFlag",1);

    $user_data = $this->oauth_get_user_account($mixi_account_id);
    $mixi_name = $user_data['nickname'];
    $mixi_name = mysql_escape_string($mixi_name);
    $mixi_thumbnail = $user_data['thumbnailUrl'];
    $this->Session->write("after_login_flag",1);
    $this->Session->write("mixi_name",$mixi_name);
    $this->Session->write("mixi_account_code",$mixi_account_id);
    $this->Session->write("mixi_thumbnail",$user_data['thumbnailUrl']);

    $count = $this->Member->findCount(array('mixi_account_id'=>$mixi_account_id));
    if($count == 0){
      $data = array(
        'Member' => array(
          'mixi_account_code' => 0,
          'mixi_account_id' => $mixi_account_id,
          'thumnail_url' => $mixi_thumbnail,
          'name' => $mixi_name,
          'mail' => '',
          'pass' => '',
          'money' => '0',
          'ship_id' => '1',
          'destroy_count' => '0',
          'last_update_date' => date("Y-m-d H:i:s")
        )
      );
      $this->Member->save($data);
      $member_id = $this->Member->getLastInsertID();
      $this->Session->write("MemberId",$member_id);
      $wdata = array(
        'OwnWepon' => array(
          'wepon_id' => 1,
          'member_id' => $member_id,
          'shell_count' => 20
        )
      );
      $this->OwnWepon->save($wdata);
      $own_wepon_id = $this->OwnWepon->getLastInsertID();

      $sdata = array(
        'Ship' => array(
          'member_id' => $member_id,
          'name' => '',
          'hp' => 150,
          'max_hp' => 150,
          'power' => 100,
          'max_power' => 100,
          'lv' => 1,
          'exp' => 0,
          'status_id' => 1,
          'speed' => 30,
          'angle' => 0,
          'map_x' => 0,
          'map_y' => 0,
          'own_wepon_id' => $own_wepon_id,
          'server_id' => 0,
          'target_ship_id'=>0,
          'star_count'=> 5,
          'max_speed'=>30,
          'radar_distance'=>200,
          'sum_exp'=>0,
          'least_next_exp'=>100,
          'status_name' => '航行中',
          'destroy_date'=>'',
          'lv_up_flag'=>0,
          'mship_id'=>1
        )
      );
      $this->Ship->save($sdata);
      $ship_id = $this->Ship->getLastInsertID();
      $m2data = array(
        'Member' => array(
          'id' => $member_id,
          'ship_id' => $ship_id,
        )
      );
      $this->Member->save($m2data);
      self::login_f_mixi();
    }else{
      if(strlen($mixi_name)>0){
        $mdata = $this->Member->findByMixiAccountId($mixi_account_id);
        $id = $mdata['Member']['id'];
        $data = array(
          'id' => $id,
          'mixi_account_code' => 0,
          'mixi_account_id' => $mixi_account_id,
          'thumnail_url' => $mixi_thumbnail,
          'name' => $mixi_name,
          'last_update_date' => date("Y-m-d H:i:s")
        );
        $this->Member->save($data);
      }
      $data = $this->Member->find("first",array("mixi_account_id" => $mixi_account_id));
      if(strlen($data['Member']['id'])==0){
        self::login_failed();
      }else{
        $this->Session->write("member_info",$data['Member']);
        self::login_success();
      }
    }
  }

  function oauth_get_user_account($mixi_account_code){
    $api = new OpensocialGetUserRestfulAPI($mixi_account_code);
    $data = $api->get();
    $json = new Services_JSON;
    $decode_data = $json->decode($data,true);
    $user_data['nickname'] = $decode_data->entry->nickname;
    $user_data['thumbnailUrl']= $decode_data->entry->thumbnailUrl;
    $user_data['platformUserId']= $decode_data->entry->platformUserId;
    return $user_data;
  }

  function oauth_get_user_account_id($mixi_account_id){
    $api = new OpensocialGetUserRestfulAPI($mixi_account_id);
    $data = $api->get();
    $json = new Services_JSON;
    $decode_data = $json->decode($data,true);
    $user_data['nickname'] = $decode_data->entry->nickname;
    $user_data['thumbnailUrl']= $decode_data->entry->thumbnailUrl;
    return $user_data;
  }

  function login_failed(){
    $this->redirect('/login/login/');
  }

  function login_success(){
    //画像更新
    $this->Session->write('img_refresh_flag',1);
    $this->redirect('/top/top/');
  }

  function s_t_out(){
    $this->redirect('/login/session_timeout/');
  }

  function session_timeout(){
  }

  function login_f_mixi(){
    $this->redirect("/login/login_first_mixi");
  }

  function login_first_mixi(){
    $error_txt = $this->Session->read("error_txt");
    $this->set('error_txt',$error_txt);

    $member_id = $this->Session->read("MemberCode");

    $this->Session->write("RefleshImgFlag",1);
    //$mixi_account_code = $this->Session->read("mixi_account_code");
    $mixi_account_id = $this->Session->read("mixi_account_id");

    $this->set('mixi_account_code',$mixi_account_code);
    //$this->render($layout='login_first_mixi',$file='Noheader');
  }

  function log_out(){
    $this->Session->write("user_genre_code","");
    $this->Session->write("member_info","");
    $this->member_id = 1;
  }


}
