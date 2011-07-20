<?php
require_once('/home/white-cube/www/cake/app/controllers/mail.php');
//require_once('/home/white-cube/www/cake/app/models/sendmails.php');

class MaildetailComponent extends Object{

	private $mail_body;
	var $uses = array('Jisuis');


	function send_member_account_mail($mail_data){

  		//メール送信
 		$from = 'blamestitch@23.studio-web.net';
		$to   = $mail_data;
	///	$to = 'oggata@msd.biglobe.ne.jp';
		$subject = 'WELCOME TO JISUIBU!!';

		$contents = @file('/home/white-cube/www/jisui/cake/app/vendors/mail_template/member_account.template');
		foreach($contents as $line){
			$this->mail_body .= $line;
		}

		$body  = $this->mail_body;
		$mail= new MailComponent();
		$mail->send($from,$to,$subject,$body);
		//メール送信ここまで
	}


	function send_dealer_account_mail($mail_to){

	}


	function send_dealer_prepare_mail($order_code_list){

		//オーダーコード毎の得意先情報を取得
		$this->Products =& new Products();
		$data = $this->Products->search_data_by_order_code($order_code_list);


		foreach ($data as $key => $value) {

			$this->mail_body = "";
	 		$from = 'blamestitch@23.studio-web.net';
//			$to   = $value[0]['mail'];
			$to   = 'blamestitch@23.studio-web.net';
	 		$subject = 'White-Cube-Infromation-Mail';


			//メールヘッダーを読み込み/作成---------------------
			$contents = @file('/home/white-cube/www/cake/app/vendors/mail_template/send_dealer_prepare.template');
			foreach($contents as $line){
				//文字列の置き換え
				$replace_line = str_replace("_%dealer_name%_",$value[0]['dealer_name'],$line);
				$replace_line = str_replace("_%shop_name%_",$value[0]['shop_name'],$replace_line);
				$replace_line = str_replace("_%member_name%_",$value[0]['member_name'],$replace_line);
				$this->mail_body .= $replace_line;
			}


			//メール本文を読み込み/作成---------------------
		    $order_code =  $value['sales']['order_code'];
		    $p_data = $this->Products->search_product_by_order_code($order_code);

			foreach ($p_data as $keys => $values){

				$contents = @file('/home/white-cube/www/cake/app/vendors/mail_template/send_dealer_prepare_main.template');
				foreach($contents as $line){
					//文字列の置き換え
					$replace_line = str_replace("_%product_name%_",$values['sales']['product_name'],$line);
					$replace_line = str_replace("_%order_code%_",$values['sales']['order_code'],$replace_line);
					$replace_line = str_replace("_%product_code%_",$values['sales']['product_code'],$replace_line);
					$replace_line = str_replace("_%dealer_product_code%_",$values['sales']['dealer_product_code'],$replace_line);
					$replace_line = str_replace("_%set_detail%_",$values['sales']['set_detail'],$replace_line);
					$replace_line = str_replace("_%amount%_",$values['sales']['amount'],$replace_line);
					$replace_line = str_replace("_%unit_price%_",$values['sales']['unit_price'],$replace_line);
					$replace_line = str_replace("_%price%_",$values['sales']['price'],$replace_line);
					$replace_line = str_replace("_%quantity_per_lot%_",$values['sales']['quantity_per_lot'],$replace_line);
					$this->mail_body .= $replace_line;
				}

			}

			//メールフッターを読み込み/作成---------------------

			$contents = @file('/home/white-cube/www/cake/app/vendors/mail_template/send_dealer_prepare_footer.template');



			//メール送信
			$body  = $this->mail_body;
			$mail= new MailComponent();
			$mail->send($from,$to,$subject,$body);




		}




	}

	function syukka_mail(){


	}


	function password_forget($mail_data,$password){

  		//メール送信
 		$from = 'blamestitch@23.studio-web.net';
		$to   = $mail_data;
		$subject = 'White-Cube-Infromation-Mail!!';

		$body = '------------';
		$body .= $password;
		$body .= '------------';

		$mail= new MailComponent();
		$mail->send($from,$to,$subject,$body);
		//メール送信ここまで
	}


}