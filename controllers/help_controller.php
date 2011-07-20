<?php

class HelpController extends AppController{

  var $uses = array('Member');

  function page_1(){
  echo 'a';
  echo 'a';
    echo '11111';
  }

  function page_2(){
    echo 'b';
    echo '22222';
    $fruit=array('apple','banana');
    var_dump($fruit);
    echo 'xxxxxxxxxxxxxxxx';
  }

  function page_3(){
    echo 'c';
    echo '33333';

  }

  function page_4(){
  echo 'd';
  echo 'ttttttttt';
  echo 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
  echo 'bbbbbbbbbbbbbbbbba';
  }

}
