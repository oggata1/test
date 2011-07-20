<?php

class WeponsController extends AppController{

  var $uses = array('Member','Message','StructureSql','Ship','Wepon','OwnWepon','Mship','Exp');

  function enemy(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $local_map_x = $mdata[0]['ships']['map_x'];
    $local_map_y = $mdata[0]['ships']['map_y'];
    $edatas = $this->StructureSql->select_near_ship($member_id,$local_map_x,$local_map_y,15);
    $this->set('edatas',$edatas);
  }

  function radar_list(){
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $this->set('mdatas',$mdata);
    $local_map_x = $mdata[0]['ships']['map_x'];
    $local_map_y = $mdata[0]['ships']['map_y'];
    $server_id = $mdata[0]['ships']['server_id'];
    $local_sphere_distance = $mdata[0]['ships']['radar_distance'];
    $edatas = $this->StructureSql->select_near_ship_sphere($member_id,$local_map_x,$local_map_y,$local_sphere_distance,30,$server_id);
    $edata_count = count($edatas);
    $this->set('edata_count',$edata_count);
    $this->set('edatas',$edatas);
  }

  function update_status(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);

    if($mdata[0]['ships']['star_count'] == 0){
      $this->redirect('/wepons/top#header-menu/');
    }

    /**/
    $star_count = $mdata[0]['ships']['star_count']-1;
    $ship_id = $mdata[0]['ships']['id'];
    $max_hp = $mdata[0]['ships']['max_hp'];
    $max_power = $mdata[0]['ships']['max_power'];
    $max_speed = $mdata[0]['ships']['max_speed'];
    $radar_distance = $mdata[0]['ships']['radar_distance'];

    /*1:燃料 2:装甲 3:推進 4:レーダー*/
    $target_id = $this->params['named']['target_id'];
      $update_num = $this->params['named']['update_num'];
      if($target_id==1){
      $max_hp = $max_hp + $update_num;
      }elseif($target_id==2){
      $max_power = $max_power + $update_num;
      }elseif($target_id==3){
      $max_speed = $max_speed + $update_num;
      }elseif($target_id==4){
      $radar_distance = $radar_distance + $update_num;
      }

    $data = array(
      'Ship' => array(
        'id' => $ship_id,
        'max_hp' => $max_hp,
        'hp'=>$max_hp,
        'max_power'=> $max_power,
        'power'=>$max_power,
        'max_speed'=>$max_speed,
        'radar_distance'=>$radar_distance,
        'star_count'=>$star_count
      )
    );
    $this->Ship->save($data);

    //メッセージ
    $mdata = array(
      'Message' => array(
        'member_id' => $member_id,
        'txt' => '【改造】ステータスが上昇しました...',
        'insert_date' => date("Y-m-d H:i:s")
      )
    );
    $this->Message->create();
    $this->Message->save($mdata);

    $this->redirect('/wepons/top#header-menu/');
  }

  function top(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $this->set('mdatas',$mdata);
    $local_map_x = $mdata[0]['ships']['map_x'];
    $local_map_y = $mdata[0]['ships']['map_y'];
    $server_id = $mdata[0]['ships']['server_id'];
    $local_sphere_distance = $mdata[0]['ships']['radar_distance'];
    $edatas = $this->StructureSql->select_near_ship_sphere($member_id,$local_map_x,$local_map_y,$local_sphere_distance,6,$server_id);
    $edata_count = count($edatas);
    $this->set('edata_count',$edata_count);
    $this->set('edatas',$edatas);

    //残りexp表示用
    $member_exp = $mdata[0]['ships']['exp'];
    $member_least_next_exp = $mdata[0]['ships']['least_next_exp'];
    $exp_rate = ($member_exp / ($member_exp + $member_least_next_exp)) *100;
    $this->set('exp_rate',$exp_rate);
    $this->set('member_least_next_exp',$member_least_next_exp);
  }

  function add_target($target_ship_id){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $ship_id = $mdata[0]['ships']['id'];
    $data = array(
      'Ship' => array(
        'id' => $ship_id,
        'target_ship_id' => $target_ship_id,
      )
    );
    $this->Ship->save($data);

      //画像更新
    $this->Session->write('img_refresh_flag',1);
    $this->redirect('/top/top_2#header-menu/');
  }

  function own_list(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $wepons = $this->StructureSql->select_own_wepon($member_id);
    $this->set('wepons',$wepons);

  }

  function change_wepons($wepon_id){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $ship_id = $mdata[0]['ships']['id'];
    $data = array(
      'Ship' => array(
        'id' => $ship_id,
        'own_wepon_id' => $wepon_id
      )
    );
    $this->Ship->save($data);
      //画像更新
    $this->Session->write('img_refresh_flag',1);
    $this->redirect('/top/top_3#header-menu/');
  }

  function change_wepon(){
    $this->session_manage();
    $item_code = $this->params['data']['submit'];
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $wepon_id = $this->params['form']['wepon_id'];
    $data = array(
      'OwnWepon' => array(
        'member_id' => $member_id,
        'wepon_id' => $wepon_id
      )
    );
    $this->OwnWepon->save($data);
  }

  function ship_list(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    //$wepons = $this->Mship->findAll();

  }

  function change_ship($ship_id){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $before_ship_id = $mdata[0]['ships']['id'];

    $bdata = $this->Ship->findById($before_ship_id);
    $bmap_x = $bdata['Ship']['map_x'];
    $bmap_y = $bdata['Ship']['map_y'];
    $bangle = $bdata['Ship']['angle'];
    $bown_wepon_id = $bdata['Ship']['own_wepon_id'];
    $btarget_ship_id = $bdata['Ship']['target_ship_id'];
    $bserver_id = $bdata['Ship']['server_id'];
    //サーバーＩＤ，map_x,map_y,angleの付け替えを行う
    $data = array(
      'Ship' => array(
        'id' => $ship_id,
        'angle' => $bangle,
        'map_x'=>$bmap_x,
        'map_y'=>$bmap_y,
        'own_wepon_id'=>$bown_wepon_id,
        'server_id'=>$bserver_id,
        'target_ship_id'=>$btarget_ship_id
      )
    );
    $this->Ship->save($data);

    //この艦以外をすべて待機中にする
    $this->StructureSql->update_ships_by_server_change($member_id,$ship_id);

    //member
    $data = array(
      'Member' => array(
        'id' => $member_id,
        'ship_id' => $ship_id
      )
    );
    $this->Member->save($data);
    $this->redirect('/cake/wepons/top#header-menu/');
  }

  function session_manage(){
    $session_data = $this->Session->read("member_info");
    $this->session_data = $session_data;
    if(strlen($session_data['id'])==0){
      $this->redirect('/login/session_timeout/');
    }
  }
}