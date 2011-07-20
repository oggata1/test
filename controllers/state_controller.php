<?php
class StateController extends AppController{
  var $uses = array('StructureSql','Member','OwnWepon','Ship','Server','Message');

  function top(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
  }

  function lv_up_view($ship_id){
    //フラグを下げる
    $sdata = array(
      'Ship' => array(
        'id' => $ship_id,
        'lv_up_flag' => 0
      )
    );
    $this->Ship->save($sdata);
  }

  function destroy(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
  }

  function all_repair(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);

    $max_hp = $mdata[0]['ships']['max_hp'];
    $power = $mdata[0]['ships']['power'];
    $max_power = $mdata[0]['ships']['max_power'];
    $ship_id = $mdata[0]['ships']['id'];
    $money = $mdata[0]['members']['money'];
    $price = floor($money/2);

    //回復＋出撃
    $data = array(
      'Ship' => array(
        'id' => $ship_id,
        'hp' => $max_hp,
        'power' => $max_power,
        'status_id'=> 1,
        'damaged_enemy_id'=>0,
        'status_name'=> '航行中'
      )
    );
    $this->Ship->save($data);

    //お金をマイナス
    $mmdata = array(
      'Member' => array(
        'id' => $member_id,
        'money' => $price
      )
    );
    $this->Member->save($mmdata);

    //メッセージ
    $mdata = array(
      'Message' => array(
        'member_id' => $member_id,
        'txt' => '【ドック】修理完了、再出撃 [修理費]'.$price.'Gold↓',
        'insert_date' => date("Y-m-d H:i:s")
      )
    );
    $this->Message->save($mdata);
    $this->Session->write('img_refresh_flag',0);
  }

  function normal_repair(){
  }

  function own_ship_list(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $before_ship_id = $mdata[0]['ships']['id'];

    //捕捉中の場合はリダイレクト
    $tdata = $this->StructureSql->count_targeted($before_ship_id);
    if($tdata[0][0]['count']>0){
      $this->Session->write('error_txt','敵艦にロックオンされている間は戦艦の乗り換えはできません。');
      $this->redirect('/top/top/');
    }
    $mships = $this->StructureSql->select_own_ship_list($member_id);
    $this->set('mships',$mships);
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
        'status_id'=>1,
        'status_name'=>'航行中',
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
    $this->Session->write('img_refresh_flag',1);
    $this->redirect('/wepons/top/');
  }

  function add(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
  }

  function session_manage(){
    $session_data = $this->Session->read("member_info");
    $this->session_data = $session_data;
    if(strlen($session_data['id'])==0){
      $this->redirect('/login/session_timeout/');
    }
  }
}
