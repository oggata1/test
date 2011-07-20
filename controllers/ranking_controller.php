<?php

class RankingController extends AppController{

  var $uses = array('StructureSql','Message');

  function top(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $rankings = $this->StructureSql->select_rankings();
    $this->set('rankings',$rankings);
  }

  function messages(){
    $this->session_manage();
    //セッションから会員番号を取得
    $member_id = $this->session_data['id'];
    $mdata = $this->StructureSql->select_member_detail($member_id);
    $messages = $this->Message->findAll(array('member_id'=>$member_id),null,'id desc',40,null,null);
    $this->set('messages',$messages);
  }

  function session_manage(){
    $session_data = $this->Session->read("member_info");
    $this->session_data = $session_data;
    if(strlen($session_data['id'])==0){
      $this->redirect('/login/session_timeout/');
    }
  }
}
