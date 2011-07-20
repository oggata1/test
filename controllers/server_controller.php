<?php

class ServerController extends AppController{

  var $uses = array('Member','Mship','StructureSql','Ship','Server');

  function top(){
  echo 'bb';
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $ship_id = $mdata[0]['ships']['id'];

    //捕捉中の場合はリダイレクト
    $tdata = $this->StructureSql->count_targeted($ship_id);
    if($tdata[0][0]['count']>0){
      $this->Session->write('error_txt','捕捉されている間はサーバー間の移動はできません。');
      $this->redirect('/top/top/');
    }
  }

  function server_list(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    //空いているサーバーを表示する
    $servers = $this->Server->findAll();
    $this->set('servers',$servers);
  }

  function change_server($server_id){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $ship_id = $mdata[0]['ships']['id'];

    //捕捉中の場合はリダイレクト
    $tdata = $this->StructureSql->count_targeted($ship_id);
    if($tdata[0][0]['count']>0){
      $this->Session->write('error_txt','捕捉されている間はサーバー間の移動はできません。');
      $this->redirect('/top/top/');
    }

    //最大数を超えていたらエラー
    $server = $this->Server->findById($server_id);
    $name = $server['Server']['name'];
    $population = $server['Server']['population'];
    $max_population = $server['Server']['max_population'];
    if($population + 1 > $max_population){
      $this->redirect('/server/no_server/');
    }

    //スペースがない場合はリダイレクト
    $server_info = $this->search_space($member_id,$server_id);
    if($server_info == false){
      $this->redirect('/server/no_server/');
    }
    //Member情報を変更
    $data = array(
      'Member' => array(
        'id' => $member_id,
        'server_id' => $server_id
      )
    );
    $this->Member->save($data);

    //Ship情報を変更
    $shdata = array(
      'Ship' => array(
        'id' => $ship_id,
        'target_ship_id'=>0,
        'damaged_enemy_id'=>0,
        'map_x' =>$server_info['x'],
        'map_y' =>$server_info['y'],
        'server_id' => $server_id,
        'status_id' => 1,
        'status_name' => '航行中',
        'target_ship_id' => 0
      )
    );
    $this->Ship->save($shdata);

    //運転中以外のship情報も変更する
    $this->StructureSql->update_ships_by_server_change($member_id,$ship_id);

    $sdata = array(
      'Server' => array(
        'id' => $server_id,
        'population' => $population + 1
      )
    );
    $this->Server->save($sdata);
    $this->set('server_name',$name);
    //画像更新
    $this->Session->write('img_refresh_flag',1);
  }

  function no_server(){

  }

  private function search_space($member_id,$server_id){
    $rdata = false;
    for($i=0;$i<=7;$i++){
      $rand_mapx = rand(200,10000);
      $rand_mapy = rand(200,6000);
      $rand_rad = rand(-17,17);
      $rand_rad = $rand_rad * 10;
      $data = $this->StructureSql->select_rader_target($member_id,$rand_mapx,$rand_mapy,40,$server_id);
      if(count($data)==0){
        $rdata['x']=$rand_mapx;
        $rdata['y']=$rand_mapy;
        $rdata['rad']=$rand_rad;
        break;
      }
    }
    return $rdata;
  }

  function session_manage(){
    $session_data = $this->Session->read("member_info");
    $this->session_data = $session_data;
    if(strlen($session_data['id'])==0){
      $this->redirect('/login/session_timeout/');
    }
  }
}