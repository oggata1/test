<?php
class SumnailComponent extends Object{

	var $uses = array('Evos');


  	function make_sumnail_midiam($file_path,$output_tmp_file_path,$default_gazou_size){

		$image = ImageCreateFromJPEG($file_path);


		$fukidashiImage = imagecreatefromgif("/home/white-cube/www/ufo/cake/app/webroot/jpg/moto/ufo.gif");

		imageCopy($image, $fukidashiImage, 0, 0, 0, 0,
		    ImageSX($fukidashiImage),
		    ImageSY($fukidashiImage)
		);

		ImageDestroy($fukidashiImage);

		ImageJPEG($image,$output_tmp_file_path, 80);

		/*
		$width = ImageSX($image);
		$height = ImageSY($image);

		//画像が縦長の場合
		if ($width < $height){
			$new_width = $default_gazou_size * $width / $height;
			$new_height = $default_gazou_size;
			$start_x = ($default_gazou_size - $new_width) / 2;
			$start_y = 0;
			$new_image = ImageCreateTrueColor($default_gazou_size, $default_gazou_size);
			$black = ImageColorAllocate($new_image, 0, 0, 0 );
			imagefill($new_image , 0 , 0 , $black);
		//画像が横長の場合
		}else{
			$new_height = $default_gazou_size * $height / $width;
			$new_width = $default_gazou_size;
			$start_y = ($default_gazou_size - $new_height) / 2;
			$start_x = 0;
			$new_image = ImageCreateTrueColor($default_gazou_size, $default_gazou_size);
			$black = ImageColorAllocate($new_image, 0, 0, 0 );
			imagefill($new_image , 0 , 0 , $black);
		}
		ImageCopyResampled($new_image,$image,$start_x,$start_y,0,0,$new_width,$new_height,$width,$height);
		ImageJPEG($new_image,$output_tmp_file_path, 80);
*/
  	}
}
