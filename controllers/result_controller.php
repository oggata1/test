<?php
class ResultController extends AppController{

  var $uses = array('StructureSql','Member','Ship','OwnWepon','Message','Exp');

  function destroy(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
  }

  function return_doc(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
  }

  function damaged(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $ship_id = $mdata[0]['ships']['id'];

    //初期値にアップデート
    $sdata = array(
      'Ship' => array(
        'id' => $ship_id,
        'damaged_enemy_id' => 0
      )
    );
    $this->Ship->save($sdata);

    $hp_rate = $mdata[0]['ships']['hp']/$mdata[0]['ships']['max_hp']*100;
    $this->set('hp_rate',$hp_rate);

    $edata = $this->Member->findById($mdata[0]['ships']['damaged_enemy_id']);
    $this->set('enemy_name',$edata['Member']['name']);
    $this->set('enemy_img_url',$edata['Member']['thumnail_url']);

    //画像の更新は必要
    $this->Session->write('img_refresh_flag',1);
  }

  function win(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    //自分の情報を取得
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $damage = $mdata[0]['wepons']['max_damage'];
    $member_name = $mdata[0]['members']['name'];
    $member_money = $mdata[0]['members']['money'];
    $member_ship_id = $mdata[0]['ships']['id'];
    $win_count = $mdata[0]['members']['win_count'];
    $local_map_x = $mdata[0]['ships']['map_x'];
    $local_map_y = $mdata[0]['ships']['map_y'];
    $local_distance = $mdata[0]['wepons']['max_distance'];
    $member_max_hp = $mdata[0]['ships']['max_hp'];
    $member_max_power = $mdata[0]['ships']['max_power'];
    $member_max_speed = $mdata[0]['ships']['max_speed'];
    $member_radar_distance = $mdata[0]['ships']['radar_distance'];

    //敵の情報を取得
    $enemy_ship_id = $mdata[0]['ships']['target_ship_id'];
    $eships = $this->Ship->findById($enemy_ship_id);
    $enemy_member_id = $eships['Ship']['member_id'];
    $enemy_before_hp = $eships['Ship']['hp'];
    $enemy_hp = $enemy_before_hp-$damage;
    $enemy_status_id = $eships['Ship']['status_id'];
    $enemy_map_x = $eships['Ship']['map_x'];
    $enemy_map_y = $eships['Ship']['map_y'];
    $enemy_ship_lv = $eships['Ship']['lv'];
    $enemy_server_id = $eships['Ship']['server_id'];
    $enemy_max_hp = $eships['Ship']['max_hp'];
    $enemy_max_power = $eships['Ship']['max_power'];
    $enemy_max_speed = $eships['Ship']['max_speed'];
    $enemy_radar_distance = $eships['Ship']['radar_distance'];
    $edata = $this->Member->findById($enemy_member_id);
    $enemy_name = $edata['Member']['name'];
    $enemy_thumnail =$edata['Member']['thumnail_url'];
    $enemy_money =$edata['Member']['money'];
    $enemy_lose_count =$edata['Member']['lose_count'];
    //画像の更新は必要
    $this->Session->write('img_refresh_flag',1);

    //敵の所持金を奪い取る
    $add_money = floor($enemy_money/2);
    if($add_money == 0){
      $add_money = 100;
    }

    //能力を１つランダムで奪い取る
    $rand_activity = rand(1,8);
    if($rand_activity ==1){
      //hp
      $member_max_hp = $member_max_hp + 10;
      $enemy_max_hp = $enemy_max_hp - 10;
      $member_txt ='[装甲]+10';
      $enemy_txt ='[装甲]-10';
    }elseif($rand_activity ==2){
      //power
      $member_max_power = $member_max_power + 10;
      $enemy_max_power = $enemy_max_power - 10;
      $member_txt ='[燃料]+10';
      $enemy_txt ='[燃料]-10';
    }elseif($rand_activity ==3){
      //speed
      $member_max_speed = $member_max_speed + 10;
      $enemy_max_speed = $enemy_max_speed - 10;
      $member_txt ='[速度]+10';
      $enemy_txt ='[速度]-10';
    }elseif($rand_activity ==4){
      //search
      $member_radar_distance = $member_radar_distance + 4;
      $enemy_radar_distance = $enemy_radar_distance - 4;
      $member_txt ='[探索]+10';
      $enemy_txt ='[探索]-10';
    }else{
      //search
      $member_txt ='[能力]奪取不可';
      $enemy_txt ='[能力]奪取不可';
    }
    //相手の状態を変更 1:航行中->2:航行不能
    $sdata = array(
      'Ship' => array(
        'id' => $enemy_ship_id,
        'max_hp' => $enemy_max_hp,
        'max_power' => $enemy_max_power,
        'max_speed' => $enemy_max_speed,
        'radar_distance' => $enemy_radar_distance,
        'status_id' => 2,
        'damaged_enemy_id'=>$member_id,
        'target_ship_id'=>0,
        'status_name' => '撃沈',
        'destroy_date' => date("Y-m-d H:i:s")
      )
    );
    $this->Ship->save($sdata);
    $e_data = array(
      'Member' => array(
        'id' => $enemy_member_id,
        'money' => $add_money,
        'lose_count' => $enemy_lose_count + 1
      )
    );
    $this->Member->save($e_data);
    //相手へメッセージを表示
    $mdata = array(
      'Message' => array(
        'member_id' => $enemy_member_id,
        'txt' => '【沈】'.$member_name.'から沈没させられました。[金]'.$add_money.'Gold↓ '.$enemy_txt,
        'insert_date' => date("Y-m-d H:i:s")
      )
    );
    $this->Message->save($mdata);
    //自分へメッセージを表示
    $up_exp = floor($damage*(1+$enemy_ship_lv*0.05)+150*(1+$enemy_ship_lv*0.05));
    $mdata2 = array(
      'Message' => array(
        'member_id' => $member_id,
        'txt' => '【攻】'.$enemy_name.'を沈没させました。[exp]'.$up_exp.'↑ [金]'.$add_money.'Gold↑ '.$member_txt,
        'insert_date' => date("Y-m-d H:i:s")
      )
    );
    $this->Message->save($mdata2);
    //ターゲットを外す+能力の変更
    $mmdata = array(
      'Ship' => array(
        'id' => $member_ship_id,
        'max_hp' => $member_max_hp,
        'max_power' => $member_max_power,
        'max_speed' => $member_max_speed,
        'radar_distance' => $member_radar_distance,
        'target_ship_id' => ''
      )
    );
    $this->Ship->save($mmdata);

    $mm_data = array(
      'Member' => array(
        'id' => $member_id,
        'win_count' => $win_count + 1
      )
    );
    $this->Member->save($mm_data);
    //経験値＋お金の付与
    $this->StructureSql->call_update_ship_exp($member_ship_id,$up_exp,$add_money);
    //画像の更新は必要
    $this->Session->write('img_refresh_flag',1);
    $this->set('message_1',$enemy_name.'を沈没させました。');
    $this->set('message_2','経験値が'.$up_exp.'Exp上昇しました。↑');
    $this->set('message_3','お金が'.$add_money.'Gold上昇しました。↑');
    $this->set('message_4',$member_txt);
  }

  function session_manage(){
    $session_data = $this->Session->read("member_info");
    $this->session_data = $session_data;
    if(strlen($session_data['id'])==0){
      $this->redirect('/login/session_timeout/');
    }
  }
}
?>