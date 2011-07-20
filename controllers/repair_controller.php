<?php
class RepairController extends AppController{

  var $uses = array('StructureSql','Member','Ship','OwnWepon','Message','Exp');

  function repair(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $ship_hp = $mdata[0]['ships']['hp'];
    $ship_max_hp = $mdata[0]['ships']['max_hp'];
    if ($ship_hp >= $ship_max_hp){
        $this->Session->write('error_txt','【ドック】修理の必要はありません。');
        $this->Session->write('img_refresh_flag',0);
        $this->redirect('/top/top/');
    }
  }

  function repair_exe($repaire_genre){
    //$repaire_genre 1:全部 2:一部
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $money = $mdata[0]['members']['money'];
    $ship_id = $mdata[0]['ships']['id'];
    $ship_hp = $mdata[0]['ships']['hp'];
    $ship_max_hp = $mdata[0]['ships']['max_hp'];
    $ship_power = $mdata[0]['ships']['power'];
    $before_ship_power_rate = floor(($ship_hp/$ship_max_hp)*100);

    if($repaire_genre==1){
      $repaire_price = floor(($ship_max_hp - $ship_hp)*0.1);
      $repaire_hp = $ship_max_hp - $ship_hp;
      if ($repaire_price > $money){
        $this->Session->write('error_txt','【ドック】修理費用不足>['.$repaire_price.'Gold必要]');
        $this->redirect('/top/top/');
      }
    }elseif($repaire_genre==2){
      $repaire_price = floor(($ship_max_hp/10)*0.1);
      $repaire_hp = floor($ship_max_hp/10);
      if ($repaire_price > $money){
        $this->Session->write('error_txt','【ドック】修理費用不足>['.$repaire_price.'Gold必要]');
        $this->redirect('/top/top/');
      }
    }
    //装甲を回復（最大値は超えない）
    $ship_hp = $ship_hp + $repaire_hp;
    if($ship_hp > $ship_max_hp){
      $ship_hp = $ship_max_hp;
    }
    $after_ship_power_rate = floor(($ship_hp/$ship_max_hp)*100);
    $data = array(
      'Ship' => array(
        'id' => $ship_id,
        'hp' => $ship_hp,
      )
    );
    $this->Ship->save($data);

    $mmdata = array(
      'Member' => array(
        'id' => $member_id,
        'money' => $money-$repaire_price,
      )
    );
    $this->Member->save($mmdata);

    //messages
    $mdata = array(
      'Message' => array(
        'member_id' => $member_id,
        'txt' => '【ドック】修理完了 [装甲]'.$repaire_hp.'↑ [金]'.$repaire_price.'Gold↓',
        'insert_date' => date("Y-m-d H:i:s")
      )
    );
    $this->Message->save($mdata);
    //表示用
    $this->set('before_ship_power_rate',$before_ship_power_rate);
    $this->set('after_ship_power_rate',$after_ship_power_rate);
    $this->set('repaire_hp',$repaire_hp);
    $this->set('repaire_price',$repaire_price);
    $this->Session->write('img_refresh_flag',1);
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