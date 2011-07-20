<?php

/*
 * 手作りのページャーです。
 *
 */

class PagerComponent extends Object{


	/*  $page_divide_no : １ページにおさめる行数
	 *  $data_all_count : 全部のデータ数
	 * 	$linkurl : リンクするURL
	 *  $now_page : 現在のページ数
	 *
	 *
	 *
	 * */

	function pagelink($page_divide_no,$data_all_count,$linkurl,$now_page){


		//例えば1００件のデータがあって7件ずつ区切ると 200/7で14.28 よってページは１５ページ必要。
		$all_page_count = ceil($data_all_count / $page_divide_no );

		//次にページを表記するにあたって最大の表示件数を前後5ページにしたい。
		//現在１ページであれば 1.2.3.4.5
		//現在２ページであれば 1.2.3.4.5
		//現在３ページであれば 1.2.3.4.5
		//現在４ページであれば 2.3.4.5.6
		//現在8ページでれば 6.7.8.9.10
		//現在10ページでれば 8.9.10.11.12
		//現在11ページでれば 9.10.11.12.13
		//現在12ページでれば 10.11.12.13.14
		//現在13ページでれば 10.11.12.13.14
		//現在14ページでれば 10.11.12.13.14

		//ページャ表記の１ページ目が０より少なくなる場合は開始ページは１ページからでOK
		$pager_start_page = $now_page - 2;
		if ($pager_start_page <= 0 ){
			//echo "少ない";
			$pager_start_page = 1;
		}

		//ページャー表記の最終ページが、最大のページ数よりも大きくなった場合は、最大ページでOK
		$pager_end_page = $now_page + 2;
		if ($pager_end_page >= $all_page_count){
			//echo "超えました";
			$pager_end_page = $all_page_count;
		}

		$v_list = "<ul class='pager'>";
		$v_list .= "<li class='prev'><a href='".$linkurl."1'>&laquo; 頭</a></li>";

	   	for ($pages = $pager_start_page; $pages <= $pager_end_page; $pages++) {
			//今見ているページはリンクいらない
	   		if($pages == $now_page){
	   			$v_list .= "<li><em>".$pages."</em></li>";
	   		}else{
	    		$v_list .= "<li><a href='".$linkurl.$pages."'>".$pages."</a></li>";
	   		}
		}

		$v_list .= "<li class='next'><a href='".$linkurl.$all_page_count."'>終 &raquo;</a></li></ul>";
		return "$v_list";

	}

}