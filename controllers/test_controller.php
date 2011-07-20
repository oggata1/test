<?php

class TestController extends AppController{

  var $uses = array('StructureSql','Member','OwnWepon','Ship','Server');
  private $map_img_x = 10000;
  private $map_img_y = 6000;
/*
delete from members where id <> 1;
delete from ships;
delete from ships;
delete from own_wepons;
delete from messages;
http://ship.blamestitch.com/cake/test/top/
 */


  function top(){
    for($j=3;$j<1400;$j++){
      $this->make_user($j);
    }
    $this->refresh_server();
  }

  private function refresh_server(){
    $datas = $this->StructureSql->select_server_population();
    foreach($datas as $data){
      $server_id = $data['ships']['server_id'];
      echo $server_id;
      $population = $data[0]['count'];
      $wdata = array(
        'Server' => array(
          'id' => $server_id,
          'population' => $population,
        )
      );
      $this->Server->save($wdata);
    }
  }

  private function make_user($id){
    $rand_server_id = rand(1,2);
    $map_position = $this->search_space($rand_server_id);
    if($map_position<>false){
      $data = array(
        'Member' => array(
          'id'=>$id,
          'mixi_account_code' => 1,
          'mixi_account_id' => 1,
          'thumnail_url' => 'http://ship.blamestitch.com/jpg/member_img/1.jpg',
          'name' => 'テスト'.$id,
          'mail' => $id,
          'pass' => $id,
          'money' => '10000',
          'ship_id' => '1',
          'destroy_count' => '0',
          'last_update_date' => date("Y-m-d H:i:s")
        )
      );
      $this->Member->create();
      $this->Member->save($data);
      $member_id = $id;
      $this->Session->write("MemberId",$member_id);
      $wdata = array(
        'OwnWepon' => array(
          'wepon_id' => 1,
          'member_id' => $member_id,
          'shell_count' => 20
        )
      );
      $this->OwnWepon->create();
      $this->OwnWepon->save($wdata);
      $own_wepon_id = $id+100;
      $ship_id = $id+100;

      $sdata = array(
        'Ship' => array(
          'id'=>$ship_id,
          'member_id' => $member_id,
          'name' => '',
          'hp' => 150,
          'max_hp' => 150,
          'power' => 10000,
          'max_power' => 10000,
          'lv' => 1,
          'exp' => 0,
          'status_id' => 1,
          'speed' => 30,
          'angle' => $map_position['rad'],
          'map_x' => $map_position['x'],
          'map_y' => $map_position['y'],
          'own_wepon_id' => $own_wepon_id,
          'server_id' => $rand_server_id,
          'target_ship_id'=>0,
          'star_count'=> 5,
          'max_speed'=>100,
          'radar_distance'=>600,
          'sum_exp'=>0,
          'least_next_exp'=>100,
          'status_name' => '航行中',
          'destroy_date'=>'',
          'lv_up_flag'=>0,
          'mship_id'=>1
        )
      );
      $this->Ship->create();
      $this->Ship->save($sdata);
      $m2data = array(
        'Member' => array(
          'id' => $member_id,
          'ship_id' => $ship_id,
        )
      );
      $this->Member->save($m2data);
    }
  }

  private function search_space($rand_server_id){
    $rdata = false;
    for($i=0;$i<=5;$i++){
      $rand_mapx = rand(200,10000);
      $rand_mapy = rand(200,6000);
      $rand_rad = rand(-17,17);
      $rand_rad = $rand_rad * 10;
      $data = $this->StructureSql->select_rader_target(1,$local_x,$local_y,40,$rand_server_id);
      if(count($data)==0){
        $rdata['x']=$rand_mapx;
        $rdata['y']=$rand_mapy;
        $rdata['rad']=$rand_rad;
        break;
      }
    }
    return $rdata;
  }

}
