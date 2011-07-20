<?php

class ShopController extends AppController{

  var $uses = array('Member','Mship','StructureSql','Ship','Wepon','OwnWepon');


  function wepon_list(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $ship_id = $mdata[0]['ships']['id'];
    $money = $mdata[0]['members']['money'];
    $wepons = $this->Wepon->findAll();
    $this->set('money',$money);
    $this->set('wepons',$wepons);
  }

  function wepon_confirm(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $money = $mdata[0]['members']['money'];
    $wepon_id = $this->params['data']['wepon_id'];
    $this->Session->write('wepon_id',$wepon_id);
    $wepons = $this->Wepon->findById($wepon_id);
    $wepon_pirce = $wepons['Wepon']['price'];
    $after_price = $money - $wepon_pirce;
    $this->set('before_price',$money);
    $this->set('after_price',$after_price);
    $this->set('wepons',$wepons);
  }

  function wepon_execute(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $money = $mdata[0]['members']['money'];
    $wepon_id = $this->Session->read('wepon_id');
    $wepons = $this->Wepon->findById($wepon_id);
    $shell_count = $wepons['Wepon']['shell_count'];
    $wepon_pirce = $wepons['Wepon']['price'];
    $after_price = $money - $wepon_pirce;
    $this->set('before_price',$money);
    $this->set('after_price',$after_price);
    $this->set('wepons',$wepons);
    //武器を取得する
    $data = array(
      'OwnWepon' => array(
        'member_id' => $member_id,
        'wepon_id' => $wepon_id,
        'shell_count'=>$shell_count
      )
    );
    $this->OwnWepon->save($data);
    //お金を減らす
    $mdata = array(
      'Member' => array(
        'id' => $member_id,
        'money' => $after_price
      )
    );
    $this->Member->save($mdata);
  }

  function ship_list(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $money = $mdata[0]['members']['money'];

    $mships = $this->Mship->findAll();
    $this->set('mships',$mships);
    $this->set('money',$money);
  }

  function ship_confirm(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $money = $mdata[0]['members']['money'];

    $mship_id = $this->params['data']['mship_id'];
    $this->Session->write('mship_id',$mship_id);
    $mships = $this->Mship->findById($mship_id);
    $ship_price = $mships['Mship']['price'];
    $after_price = $money - $ship_price;

    $this->set('before_price',$money);
    $this->set('after_price',$after_price);
    $this->set('mships',$mships);
  }

  function ship_execute(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $money = $mdata[0]['members']['money'];

    $mship_id = $this->Session->read('mship_id');
    $mships = $this->Mship->findById($mship_id);
    $ship_price = $mships['Mship']['price'];
    $after_price = $money - $ship_price;

    $this->set('mships',$mships);
    $sdata = array(
      'Ship' => array(
        'member_id' => $member_id,
        'name' => '',
        'hp' => $mships['Mship']['max_hp'],
        'max_hp' => $mships['Mship']['max_hp'],
        'power' => $mships['Mship']['max_power'],
        'max_power' => $mships['Mship']['max_power'],
        'lv' => $mships['Mship']['lv'],
        'exp' => 0,
        'status_id' => 0,
        'speed' => $mships['Mship']['max_speed'],
        'max_speed' => $mships['Mship']['max_speed'],
        'angle' => 0,
        'map_x' => 0,
        'map_y' => 0,
        'own_wepon_id' => $own_wepon_id,
        'server_id' => 0,
        'target_ship_id'=>0,
        'star_count'=>3,
        'radar_distance'=>$mships['Mship']['radar_distance'],
        'damaged_enemy_id'=>0,
        'sum_exp'=>0,
        'least_next_exp'=>0,
        'status_name'=>'待機中',
        'destroy_date'=>'',
        'lv_up_flag'=>0,
        'mship_id'=>$mship_id
      )
    );
    $this->Ship->create();
    $this->Ship->save($sdata);
    //お金を減らす
    $mdata = array(
      'Member' => array(
        'id' => $member_id,
        'money' => $after_price
      )
    );
    $this->Member->save($mdata);
    $this->set('before_price',$money);
    $this->set('after_price',$after_price);
  }

  function session_manage(){
    $session_data = $this->Session->read("member_info");
    $this->session_data = $session_data;
    if(strlen($session_data['id'])==0){
      $this->redirect('/login/session_timeout/');
    }
  }
}