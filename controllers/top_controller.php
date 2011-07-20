<?php
//http://ship.blamestitch.com/cake/titanium/login/
/*
2.5m = 1px
250m = 100px
2.5km = 25000m = 10000px
横2.5km
縦1.5km
1回 30px = 75m進
*/
class TopController extends AppController{

  var $uses = array('StructureSql','Member','Ship','OwnWepon','Message','Exp','Rival');
  var $components = array('Pager');
  //船の画像サイズ
  private $ship_img_x = 200;
  private $ship_img_y = 200;
  //地図の画像サイズ
  private $map_img_x = 10000;
  private $map_img_y = 6000;
  //地図の表示サイズ
  private $map_view_x = 320;
  private $map_view_y = 430;

  function top(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $ship_id = $mdata[0]['ships']['id'];
    $status_id = $mdata[0]['ships']['status_id'];
    $lv_up_flag = $mdata[0]['ships']['lv_up_flag'];

    //エラーの出力とイメージの更新
    $error_txt = $this->Session->read('error_txt');
    $this->Session->write('error_txt','');
    if(strlen($error_txt)>0){
      $alert_txt = "alert('".$error_txt."');";
      $this->set('alert_txt',$alert_txt);
    }else{
      $this->set('alert_txt','');
      //エラーがない場合は画像の更新を行う
      $img_refresh_flag = $this->Session->read('img_refresh_flag');
      if($img_refresh_flag == 1){
        $this->refresh_img($member_id);
      }
      $this->Session->write('img_refresh_flag',0);
    }
    //レベルが上昇していたら・・・
    if($lv_up_flag>0){
      $this->redirect('/state/lv_up_view/'.$ship_id.'/');
    }
    //沈没していたら・・・
    if($status_id == 2){
      $this->redirect('/state/destroy#header-menu/');
    }
    //沈没後48時間経過していたら・・・
    if($status_id == 4){
      $this->redirect('/result/return_doc#header-menu/');
    }
    //ダメージを受けた場合は・・
    $damaged_enemy_id = $mdata[0]['ships']['damaged_enemy_id'];
    if(($damaged_enemy_id > 0)){
      $this->redirect('/result/damaged#header-menu/');
    }
    //サーバーが選ばれていない場合は選択画面へ
    $server_id = $mdata[0]['ships']['server_id'];
    if(($server_id ==0)or(strlen($server_id)==0)){
      $this->redirect('/server/top#header-menu/');
    }

    //表示
    $this->set('mdatas',$mdata);
    $time = time();
    $this->set('time_txt',$time);
    $this->set('member_id',$member_id);

    //プログレスバー用
    $this->set('hp',($mdata[0]['ships']['hp']/$mdata[0]['ships']['max_hp'])*100);
    $this->set('power',($mdata[0]['ships']['power']/$mdata[0]['ships']['max_power'])*100);
    $this->set('speed',$mdata[0]['ships']['speed']);
    $this->set('max_speed',$mdata[0]['ships']['max_speed']);

    //メッセージ
    $messages = $this->Message->find('first',array('conditions'=>array('member_id'=>$member_id),'order'=>array('id desc')));
    $this->set('messages',$messages);
  }

  function top_2(){
    $this->session_manage();

    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $this->set('mdatas',$mdata);
    $member_ship_id = $mdata[0]['ships']['id'];
    $local_map_x = $mdata[0]['ships']['map_x'];
    $local_map_y = $mdata[0]['ships']['map_y'];
    $server_id = $mdata[0]['ships']['server_id'];
    $local_sphere_distance = $mdata[0]['ships']['radar_distance'];

    //捕捉数を数える
    $page_no = $this->params['named']['page_no'];
    if(strlen($page_no)==0){
      $page_no = 1;
    }
    $count_enemy = $this->StructureSql->select_near_ship_sphere_count($member_id,$local_map_x,$local_map_y,$local_sphere_distance,$server_id);
    $count_num = $count_enemy[0][0]['count'];
    $divide_no = 10;
    //ページ表示部分
    $vlist = $this->Pager->pagelink($divide_no,$count_num,'/cake/top/top_2/page_no:',$page_no);
    $this->set('vlist',$vlist);
    $page_end_no = $divide_no * $page_no;
    $page_start_no = $page_end_no - ($divide_no - 1) -1;

    $edatas = $this->StructureSql->select_near_ship_sphere($member_id,$local_map_x,$local_map_y,$local_sphere_distance,$server_id,$page_start_no,$divide_no);
    $this->set('edata_count',$count_num);
    $this->set('edatas',$edatas);

    //捕捉
    $target_ship_id = $mdata[0]['ships']['target_ship_id'];
    $this->set('target_ship_id',$target_ship_id);

    //被捕捉数の表示
    $targeted = $this->StructureSql->select_find_targeted($member_ship_id);
    $targeted_count = count($targeted);
    if($targeted_count>0){
      $targeted_name = $targeted[0]['members']['name'];
      $targeted_radar_distance = $targeted[0]['ships']['radar_distance'];
      $target_txt = $targeted_count."隻からロックオンされています。>>".$targeted_name."[距]".$targeted_radar_distance."m";
    }else{
      $target_txt ='';
    }
    $this->set('target_txt',$target_txt);

    //表示
    $this->set('mdatas',$mdata);
    $time = time();
    $this->set('time_txt',$time);
    $this->set('member_id',$member_id);

    //エラーの出力とイメージの更新
    $error_txt = $this->Session->read('error_txt');
    $this->Session->write('error_txt','');
    if(strlen($error_txt)>0){
      $alert_txt = "alert('".$error_txt."');";
      $this->set('alert_txt',$alert_txt);
    }else{
      $this->set('alert_txt','');
      //エラーがない場合は画像の更新を行う
      $img_refresh_flag = $this->Session->read('img_refresh_flag');
      if($img_refresh_flag == 1){
        $this->refresh_img($member_id);
      }
      $this->Session->write('img_refresh_flag',0);
    }

    //メッセージ
    $messages = $this->Message->find('first',array('conditions'=>array('member_id'=>$member_id),'order'=>array('id desc')));
    $this->set('messages',$messages);
  }

  function top_3(){
    $this->session_manage();

    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $this->set('mdatas',$mdata);
    $local_map_x = $mdata[0]['ships']['map_x'];
    $local_map_y = $mdata[0]['ships']['map_y'];
    $server_id = $mdata[0]['ships']['server_id'];
    $own_wepon_id = $mdata[0]['ships']['own_wepon_id'];
    $local_sphere_distance = $mdata[0]['ships']['radar_distance'];
    $edatas = $this->StructureSql->select_near_ship_sphere($member_id,$local_map_x,$local_map_y,$local_sphere_distance,6,$server_id);
    $edata_count = count($edatas);
    $this->set('edata_count',$edata_count);
    $this->set('edatas',$edatas);

    //武器一覧
    $wepons = $this->StructureSql->select_own_wepons($member_id,$own_wepon_id);
    $this->set('wepons',$wepons);
    $this->set('radar_distance',$local_sphere_distance);

    //表示
    $this->set('mdatas',$mdata);
    $time = time();
    $this->set('time_txt',$time);
    $this->set('member_id',$member_id);

    //エラーの出力とイメージの更新
    $error_txt = $this->Session->read('error_txt');
    $this->Session->write('error_txt','');
    if(strlen($error_txt)>0){
      $alert_txt = "alert('".$error_txt."');";
      $this->set('alert_txt',$alert_txt);
    }else{
      $this->set('alert_txt','');
      //エラーがない場合は画像の更新を行う
      $img_refresh_flag = $this->Session->read('img_refresh_flag');
      if($img_refresh_flag == 1){
        $this->refresh_img($member_id);
      }
      $this->Session->write('img_refresh_flag',0);
    }

    //メッセージ
    $messages = $this->Message->find('first',array('conditions'=>array('member_id'=>$member_id),'order'=>array('id desc')));
    $this->set('messages',$messages);
  }

  private function remove_lockon($ship_id){
    $enemy_datas = $this->StructureSql->select_targeted($ship_id);
    //捕捉されていた場合
    if(count($enemy_datas)>0){
      $shipdata = $this->Ship->findById($ship_id);
      $member_id = $shipdata['Ship']['member_id'];
      $local_map_x = $shipdata['Ship']['map_x'];
      $local_map_y = $shipdata['Ship']['map_y'];
      foreach($enemy_datas as $enemy_data){
        //enemy
        $enemy_id = $enemy_data['ships']['member_id'];
        $enemy_ship_id = $enemy_data['ships']['id'];
        $enemy_map_x = $enemy_data['ships']['map_x'];
        $enemy_map_y = $enemy_data['ships']['map_y'];
        $real_distance = sqrt(($local_map_x-$enemy_map_x)*($local_map_x-$enemy_map_x)+($local_map_y-$enemy_map_y)*($local_map_y-$enemy_map_y));
        $enemy_radar_distance = $enemy_data['ships']['radar_distance'];
        //比較
        if($real_distance > $enemy_radar_distance){
          //相手の捕捉距離以上、離れれば解除
          $sdata = array(
            'Ship' => array(
              'id' => $enemy_ship_id,
              'target_ship_id' => 0
            )
          );
          $this->Ship->save($sdata);

          //メッセージ
          $mdata = array(
            'Message' => array(
              'member_id' => $member_id,
              'txt' => 'ロックオンしていた戦艦のレーダー範囲から外れました。',
              'insert_date' => date("Y-m-d H:i:s")
            )
          );
          $this->Message->create();
          $this->Message->save($mdata);

          //メッセージ
          $mmdata = array(
            'Message' => array(
              'member_id' => $enemy_id,
              'txt' => 'ロックオンしていた戦艦がレーダー範囲から外れました。',
              'insert_date' => date("Y-m-d H:i:s")
            )
          );
          $this->Message->create();
          $this->Message->save($mmdata);
        }
      }
    }
  }

  function move(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);

    $m_money = $mdata[0]['members']['money'];
    $m_power = $mdata[0]['ships']['power'];
    $m_map_x = $mdata[0]['ships']['map_x'];
    $m_map_y = $mdata[0]['ships']['map_y'];
    $ship_id = $mdata[0]['ships']['id'];
    $target_ship_id = $mdata[0]['ships']['target_ship_id'];
    $server_id = $mdata[0]['ships']['server_id'];
    $ship_angle = $mdata[0]['ships']['angle'];
    $speed = $this->params['data']['speed'];
    $angle = $this->params['data']['angle'];
    //入力値ミスった場合は・・・
    if(strlen($speed)==0){$speed = 0;}
    if(strlen($angle)==0){$angle = 0;}
    $ship_angle = $ship_angle + $angle;

    //進んだ距離分、燃料減らす(速度の１０分の１が消費ＥＮＥ) hpが
    $power = $m_power - floor(abs($speed)*0.5);

    //角度の絶対値は180を超えない
    if($ship_angle>180){
      $ship_angle = $ship_angle - 360;
    }elseif($ship_angle<-180){
      $ship_angle = $ship_angle + 360;
    }
    //進行方向の調整
    if($ship_angle < -90){
      $adapt_y = -1;
    }
    $map_x =  $m_map_x + $speed * sin(deg2rad($ship_angle));
    $map_y =  $m_map_y + (-1)* $speed * cos(deg2rad($ship_angle));

    //燃料がない場合は、エラー
    if($mdata[0]['ships']['power'] < $speed){
      $this->Session->write('error_txt','【エラー】燃料が不足しています。');
      $this->redirect('/top/top#header-menu/');
    }
    //地図の外に出る場合はエラー
    if($map_x < 0 or $map_x > $this->map_img_x or $map_y < 0 or $map_y > $this->map_img_y){
      $this->Session->write('error_txt','【エラー】戦闘区域外に出ようとしています。');
      $this->redirect('/top/top#header-menu/');
    }
    //進行方向に戦艦がいる場合エラー
    $move_check = $this->StructureSql->select_rader_target($member_id,$map_x,$map_y,50,$server_id);
    if(count($move_check)<>0){
      $this->Session->write('error_txt','【エラー】進行方向に障害物があります。');
      $this->redirect('/top/top#header-menu/');
    }

    $data = array(
      'Ship' => array(
        'id' => $ship_id,
        'power' => $power,
        'map_x' => $map_x,
        'map_y' => $map_y,
        'speed' => $speed,
        'angle' => $ship_angle
      )
    );
    $this->Ship->save($data);
    $add_money = floor(abs($speed) * 0.1);

    $m_data = array(
      'Member' => array(
        'id' => $member_id,
        'money' => $m_money + $add_money
      )
    );
    $this->Member->save($m_data);
    $mdata = array(
      'Message' => array(
        'member_id' => $member_id,
        'txt' => '【進】[速]'.$speed.' [角]'.$angle.'に移動 >[金]'.$add_money.'Gold↑',
        'insert_date' => date("Y-m-d H:i:s")
      )
    );
    $this->Message->save($mdata);
    //自艦が捕捉されている場合は・・範囲外に出れば外す
    $this->remove_lockon($ship_id);
    if(strlen($target_ship_id)>0){
      $this->remove_lockon($target_ship_id);
    }
    //画像更新
    $this->Session->write('img_refresh_flag',1);
    $this->redirect('/top/top#header-menu/');
  }

  function change_operation(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $m_power = $mdata[0]['ships']['power'];
    $m_map_x = $mdata[0]['ships']['map_x'];
    $m_map_y = $mdata[0]['ships']['map_y'];
    $own_wepons_id = $mdata[0]['own_wepons']['id'];

    $distance = $this->params['data']['distance'];
    $angle = $this->params['data']['angle'];

    $data = array(
      'OwnWepon' => array(
        'id' => $own_wepons_id,
      )
    );
    $this->OwnWepon->save($data);
    $this->redirect('/top/top#header-menu/');
  }

  function use_wepon_exe(){
    $this->redirect('/top/top#header-menu/');
  }

  function use_wepon(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);

    $power_cost = $mdata[0]['wepons']['power_cost'];

    //燃料がない場合は、エラー
    if($mdata[0]['ships']['power']<$power_cost){
      $this->Session->write('error_txt','【エラー】燃料が不足しています。');
      $this->redirect('/top/top#header-menu/');
    }
    //弾がない場合はエラー
    if($mdata[0]['own_wepons']['shell_count']<=0){
      $this->Session->write('error_txt','【エラー】弾が不足しています。');
      $this->redirect('/top/top#header-menu/');
    }

    //データ
    $ship_id = $mdata[0]['ships']['id'];
    $ship_power = $mdata[0]['ships']['power'];
    $ship_angle = $mdata[0]['ships']['angle'];
    $map_x = $mdata[0]['ships']['map_x'];
    $map_y = $mdata[0]['ships']['map_y'];
    $own_wepon_id = $mdata[0]['ships']['own_wepon_id'];
    $wepon_shell_count = $mdata[0]['own_wepons']['shell_count'];
    $distance = $mdata[0]['wepons']['max_distance'];
    $max_damage = $mdata[0]['wepons']['max_damage'];
    $power_cost = $mdata[0]['wepons']['power_cost'];

    //燃料を減らす
    $sdata = array(
      'Ship' => array(
        'id' => $ship_id,
        'power' => $ship_power - $power_cost
      )
    );
    $this->Ship->save($sdata);

    //弾の数を減らす
    $sdata = array(
      'OwnWepon' => array(
        'id' => $own_wepon_id,
        'shell_count' => $wepon_shell_count - 1
      )
    );
    $this->OwnWepon->save($sdata);

    //攻撃。
    $this->ship_hits($member_id);

  }

  private function ship_hits($member_id){
    //自分の情報を取得
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $damage = $mdata[0]['wepons']['max_damage'];
    $member_name = $mdata[0]['members']['name'];
    $member_ship_id = $mdata[0]['ships']['id'];
    $local_map_x = $mdata[0]['ships']['map_x'];
    $local_map_y = $mdata[0]['ships']['map_y'];
    $local_distance = $mdata[0]['wepons']['max_distance'];
    $member_server_id = $mdata[0]['ships']['server_id'];
    $member_max_hp = $mdata[0]['ships']['max_hp'];
    $member_max_power = $mdata[0]['ships']['max_power'];
    $member_max_speed = $mdata[0]['ships']['max_speed'];
    $member_status_id = $mdata[0]['ships']['status_id'];
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

    //自分が沈没していたらエラー
    if($member_status_id<>1){
      $this->redirect('/top/top#header-menu/');
    }

    //距離を換算
    $real_distance = sqrt(($local_map_x-$enemy_map_x)*($local_map_x-$enemy_map_x)+($local_map_y-$enemy_map_y)*($local_map_y-$enemy_map_y));
    if($local_distance < $real_distance){
      $this->Session->write('error_txt','【エラー】攻撃範囲外です。');
      $this->redirect('/top/top#header-menu/');
    }
    //既に沈没していた場合はエラー
    if($enemy_status_id<>1){
      $this->Session->write('error_txt','【エラー】既にターゲットは沈没しています。');
      $this->redirect('/top/top#header-menu/');
    }
    //サーバーが変更していた場合
    if($enemy_server_id <> $member_server_id){
      $this->Session->write('error_txt','【エラー】ターゲットを失いました');
      $this->redirect('/top/top#header-menu/');
    }

    //敵艦の攻撃前後のＨＰ差
    $this->set('enemy_before_hp_rate',$enemy_before_hp/$enemy_max_hp*100);
    $this->set('enemy_hp_rate',$enemy_hp/$enemy_max_hp*100);

    //敵の状態を変更
    $sdata = array(
      'Ship' => array(
        'id' => $enemy_ship_id,
        'hp' => $enemy_hp,
        'damaged_enemy_id'=>$member_id
      )
    );
    $this->Ship->save($sdata);
    $emdata = array(
      'Message' => array(
        'member_id' => $enemy_member_id,
        'txt' => '【被】被弾しました。'.$member_name.'から['.$damage.'ダメージ]',
        'insert_date' => date("Y-m-d H:i:s")
      )
    );
    $this->Message->create();
    $this->Message->save($emdata);
    //自分へメッセージ
    $mdata = array(
      'Message' => array(
        'member_id' => $member_id,
        'txt' => '【攻】'.$enemy_name.'に['.$damage.'ダメージ]与えました。',
        'insert_date' => date("Y-m-d H:i:s")
      )
    );
    $this->Message->create();
    $this->Message->save($mdata);
    //画像の更新は不要
    $this->Session->write('img_refresh_flag',0);

    //敵艦を倒した場合は別画面へリダイレクト
    if($enemy_hp <= 0){
      //仇を入力する
      $this->insert_update_rivals($enemy_member_id,$member_id);
      //リダイレクト
      $this->redirect('/result/win#header-menu/');
    }

    $this->set('enemy_name',$enemy_name);
    $this->set('enemy_thumnail',$enemy_thumnail);
    $this->set('enemy_hp',$enemy_hp);
    $this->set('damage',$damage);
  }

  private function insert_update_rivals($member_id,$enemy_member_id){
      $condition = array("member_id" => $member_id,"enemy_member_id"=>$enemy_member_id);
      $rival_data = $this->Rival->findAll($condition);
      $count = count($rival_data);
      if($count==0){
        //insert
        $rdata = array(
          'Rival' => array(
            'member_id' => $member_id,
            'enemy_member_id' => $enemy_member_id,
            'destroy_count'=>0,
            'insert_date' => date("Y-m-d H:i:s")
          )
        );
        $this->Rival->save($rdata);
      }else{
        $rival_id = $rival_data['Rival']['id'];
        $destroy_count = $rival_data['Rival']['destroy_count'];
        $destroy_count = $destroy_count + 1;
        //insert
        $rdata = array(
          'Rival' => array(
            'id'=>$rival_id,
            'destroy_count' => $destroy_count,
            'update_date' => date("Y-m-d H:i:s")
          )
        );
        $this->Rival->save($rdata);
      }
  }

  private function refresh_img($member_id){
    //全体図を読み込む
    $base_canvas = new Imagick();
    $base_canvas->newImage($this->map_view_x,$this->map_view_y,new ImagickPixel('black'));
    $base_canvas->setImageFormat("png");
    $base_map = new Imagick(IMG_DIR."/map.png");
    $base_canvas->compositeImage($base_map, imagick::COMPOSITE_OVER,0,0);

    //自艦描画
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $member_server_id = $mdata[0]['ships']['server_id'];
    $member_map_x = $mdata[0]['ships']['map_x'];
    $member_map_y = $mdata[0]['ships']['map_y'];
    $member_ship_angle = $mdata[0]['ships']['angle'];
    $member_wepon_distance = $mdata[0]['wepons']['max_distance'];
    $member_target_ship_id = $mdata[0]['ships']['target_ship_id'];

    //ずらす位置関係
    $map_move_x = $member_map_x - 160;
    $map_move_y = $member_map_y - 215;

    //circle
    $idraw = new ImagickDraw();
    $idraw->setFillColor('white');
    $idraw->setStrokeColor("#FFF68F");
    $idraw->setStrokeWidth(1);
    $idraw->setStrokeAlpha(0.3);
    $idraw->setStrokeDashArray(array(2,2));
    $idraw->setFillAlpha(0);
    $idraw->ellipse($member_map_x - $map_move_x,$member_map_y - $map_move_y,$member_wepon_distance,$member_wepon_distance,0,360);
    $base_canvas->drawImage($idraw);

    //自艦に近いものをすべて取得
    $ships = $this->StructureSql->select_rader_target_include_me($member_map_x,$member_map_y,300,$member_server_id);

    $pass_id = 0;
    foreach($ships as $ship){
      $pass_id = $pass_id + 1;
      //角度
      $ship_angle = $ship['ships']['angle'];
      $adapt = $this->ship_adapt($ship_angle);
      //ズレ
      $ship_x = $ship['ships']['map_x'] - $map_move_x + $adapt['x'] - 100; //-abs($add_sx);
      $ship_y = $ship['ships']['map_y'] - $map_move_y + $adapt['y'] - 100; //-abs($add_sy);
      $hp = $ship['ships']['hp'];
      $max_hp = $ship['ships']['max_hp'];
      $mship_id = $ship['ships']['mship_id'];

      //沈没している場合
      if($hp>0){
        $pic_name = "ship.png";
      }elseif($hp<=0){
        $pic_name = "d_ship.png";
      }
      ${'passed_marker_'.$pass_id} = new Imagick(JPG_DIR.'/ships/'.$mship_id.'/'.$pic_name);

      //hpが0以上、40％以下の場合は炎を演出
      if(($hp>0)and(($hp/$max_hp*100)<=30)){
        $pic_fire_name = 'f_10_ship.png';
        ${'passed_marker_fire_'.$pass_id} = new Imagick(JPG_DIR.'/ships/'.$mship_id.'/'.$pic_fire_name);
        ${'passed_marker_'.$pass_id}->compositeImage(${'passed_marker_fire_'.$pass_id},imagick::COMPOSITE_OVER,0,0);
      }
      ${'passed_marker_'.$pass_id}->rotateImage('none',$ship_angle);
      $base_canvas->compositeImage(${'passed_marker_'.$pass_id}, imagick::COMPOSITE_OVER, $ship_x, $ship_y);
    }

    //target
    if(($member_target_ship_id > 0) and (strlen($member_target_ship_id)>0)){
      $target = $this->Ship->findById($member_target_ship_id);
    }else{
      $target =array();
    }
    if(count($target)>0){
      //攻撃範囲内か外を確認
      //直線距離を算出
      $real_distance = sqrt(($member_map_x-$target['Ship']['map_x'])*($member_map_x-$target['Ship']['map_x'])+($member_map_y-$target['Ship']['map_y'])*($member_map_y-$target['Ship']['map_y']));
      if($real_distance>$member_wepon_distance){
        //距離外は青
        $tt = "#00CED1";
      }else{
        //距離内は赤
        $tt = "#FF0000";
      }
      $target_draw = new ImagickDraw();
      $target_draw->setStrokeColor($tt);
      $target_draw->setStrokeWidth(1);
      $target_draw->setStrokeDashArray(array(2,2));
      $target_draw->line($member_map_x-$map_move_x,$member_map_y-$map_move_y,$target['Ship']['map_x']-$map_move_x,$target['Ship']['map_y']-$map_move_y);
      $base_canvas->drawImage($target_draw);
    }
    $mini_canvas = new Imagick();
    $mini_canvas->newPseudoImage(100,60, 'gradient:#006400-#808000');
    $mini_idraw = new ImagickDraw();
    $mini_idraw->setFillColor('white');
    $mini_idraw->setStrokeColor("#FFF68F");
    $mini_idraw->setStrokeWidth(0.5);
    $mini_idraw->setStrokeAlpha(1);
    $mini_idraw->setStrokeDashArray(array(2,2));
    $mini_idraw->setFillAlpha(0.5);
    //実際のサイズは横10000->miniマップ100
    $mini_idraw->ellipse($member_map_x/100,$member_map_y/100,3,3,0,360);
    $mini_canvas->drawImage($mini_idraw);
    $base_canvas->compositeImage($mini_canvas, imagick::COMPOSITE_OVER, 10, 10);

    if( is_dir(TOP_IMG_DIR.'/top/'.$member_id) ){
      $base_canvas->writeImage(TOP_IMG_DIR.'/top/'.$member_id."/top.png");
    }else{
      if ( mkdir(TOP_IMG_DIR.'/top/'.$member_id,0777) ) {
        chmod(TOP_IMG_DIR.'/top/'.$member_id,0777);
        $base_canvas->writeImage(TOP_IMG_DIR.'/top/'.$member_id."/top.png");
      } else {
        echo "miss image";
      }
    }
  }

  private function ship_adapt($angle){
    $angle = abs($angle);
    if($angle>180){
      $angle = $angle - 180;
    }
    if($angle == 10){
      $data['x'] = -15;
      $data['y'] = -15;
      return $data;
    }
    if($angle == 20){
      $data['x'] = -25;
      $data['y'] = -25;
      return $data;
    }
    if($angle ==30){
      $data['x'] = -35;
      $data['y'] = -35;
      return $data;
    }
    if($angle ==40){
      $data['x'] = -40;
      $data['y'] = -40;
      return $data;
    }
    if($angle ==50){
      $data['x'] = -45;
      $data['y'] = -45;
      return $data;
    }
    if($angle ==60){
        $data['x'] = -35;
        $data['y'] = -35;
        return $data;
    }
    if($angle ==70){
        $data['x'] = -25;
        $data['y'] = -25;
        return $data;
    }
    if($angle ==80){
        $data['x'] = -15;
        $data['y'] = -15;
        return $data;
    }
    if($angle ==100){
        $data['x'] = -15;
        $data['y'] = -15;
        return $data;
    }
    if($angle ==110){
        $data['x'] = -30;
        $data['y'] = -30;
        return $data;
    }
    if($angle ==120){
        $data['x'] = -35;
        $data['y'] = -35;
        return $data;
    }
    if($angle ==130){
        $data['x'] = -40;
        $data['y'] = -40;
        return $data;
    }
    if($angle ==140){
        $data['x'] = -45;
        $data['y'] = -45;
        return $data;
    }
    if($angle ==150){
        $data['x'] = -35;
        $data['y'] = -35;
        return $data;
    }
    if($angle ==160){
        $data['x'] = -25;
        $data['y'] = -25;
        return $data;
    }
    if($angle ==170){
        $data['x'] = -15;
        $data['y'] = -15;
        return $data;
    }
  }

  function session_manage(){
    $session_data = $this->Session->read("member_info");
    $this->session_data = $session_data;
    if(strlen($session_data['id'])==0){
      $this->redirect('/login/session_timeout/');
    }
  }
}
