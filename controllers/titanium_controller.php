<?php

class TitaniumController extends AppController{

  var $uses = array('Member');

  function login(){


  }

  function execute(){
    $this->Session->write('img_refresh_flag',1);
    $cond = array(
      'mail'=>$this->params['data']['mail'],
      'pass'=>$this->params['data']['pass']
    );
    $data = $this->Member->findAll($cond);

    //var_dump($cond);
    //var_dump($data);

    if(strlen($this->params['data']['mail'])==0){
      self::login_failed();
    }
       if(strlen($this->params['data']['pass'])==0){
      self::login_failed();
    }



    if(is_null($data[0]['Member']) == '1'){
      self::login_failed();
    }else{
      $this->Session->write("member_info",$data[0]['Member']);
      $this->Session->write("user_genre_code",1);
      self::login_success();
    }

  }

  function login_failed(){
    $this->redirect('/titanium/login/');

  }

  function login_success(){
    $this->redirect('/top/top/');
  }

  function account(){

  }

  function account_confirm(){
    $this->set('email',$this->params['data']['email']);
    $this->set('pass',$this->params['data']['pass']);
    $this->set('name',$this->params['data']['name']);
    $this->Session->write("InputData",$this->params['data']);

    if(strlen($this->params['data']['email'])==0){
      $this->Session->write("error_message",'メールアドレスを入力してください。');
      $this->redirect('/member/account/');
    }
       if(strlen($this->params['data']['pass'])==0){
      $this->Session->write("error_message",'パスワードを入力して下さい。');
      $this->redirect('/member/account/');
    }
       if(strlen($this->params['data']['name'])==0){
      $this->Session->write("error_message",'名前を入力して下さい。');
      $this->redirect('/member/account/');
    }

  }

  function account_execute(){
       $InputData =  $this->Session->read("InputData");
    $this->set('email',$InputData['email']);
    $this->set('pass',$InputData['pass']);
     $this->set('name',$InputData['name']);
    $mail			= $InputData['email'];
    $password		= $InputData['pass'];
    $member_name	= $InputData['name'];

    //insert
      $data = array(
        'Member' => array(
          'mixi_account_code' => 0,
          'mixi_account_id' => 0,
          'thumnail_url' => '',
          'name' => $member_name,
          'mail' => $mail,
          'pass' => $password,
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
          'distance' => 70,
          'angle' => 0,
        )
      );
      $this->OwnWepon->save($wdata);
      $own_wepon_id = $this->OwnWepon->getLastInsertID();

      $rand_mapx = rand(100,2000);
      $rand_mapy = rand(100,1500);
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
          'map_x' => $rand_mapx,
          'map_y' => $rand_mapy,
          'own_wepon_id' => $own_wepon_id,
          'server_id' => 0,
          'target_ship_id'=>0,
          'star_count'=> 5,
          'max_speed'=>30,
          'radar_distance'=>50,
          'least_exp'=>1000
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
  }

  function top(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_code = $this->session_data['member_code'];
  }

  function check(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_code = $this->session_data['member_code'];

  }

  function session_manage(){
    $session_data = $this->Session->read("member_info");
    $this->session_data = $session_data;
    if(strlen($session_data['member_code'])==0){
      $this->redirect('/login/session_timeout/');
    }
  }
}