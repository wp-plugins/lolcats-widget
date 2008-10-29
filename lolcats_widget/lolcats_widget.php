<?php

/*
Plugin Name: Lolcats Widget
Plugin URI: http://www.stratos.me/wp-plugins/lolcats-widget
Description: It give you a widget that you can put on your sidebar to show the latest lolcat. It caches one every two hours. Please read the plugin page on ways to customize a few stuff around here.
Author: stratosg
Version: 1.0
Author URI: http://www.stratos.me
*/

function cheezburger_widget_image_retreiver(){
	
	//-----BEGIN DEFINITIONS-----------
	$cache_timeout = 1;//two hours caching before bringing in the new one
	$image_path = ABSPATH.PLUGINDIR.'/lolcats_widget/cheezes/latest.jpeg';//path to save image
	$link_path = ABSPATH.PLUGINDIR.'/lolcats_widget/cheezes/link.txt';//path to save image
	$final_image_width = 150;//200px default width for image
	$use_blank = true;//add an _blank to the lolcats page or not
	//-----END DEFINITIONS---------------
	
	//checking if the cached image is fine
	if(file_exists($image_path)){//check to see if there is already an image
		$file_stats = stat($image_path);
		$now = gettimeofday();
		if($now['sec'] - $file_stats[9] < $cache_timeout){//not yet timed out... use the current one
			return file_get_contents($link_path);
		}
	}
	
	
	//bringing html and parsing to find the latest image
	$html = file_get_contents('http://icanhascheezburger.com/');

	//getting image source
	$start = strpos($html, '<div class=\'snap_preview\'>') + 26;
	$end = strpos($html, '</p>', $start);
	$img = substr($html, $start, $end - $start);
	$img = strip_tags($img, '<img>');
	$src_start = strpos($img, 'src="') + 5;
	$src_end = strpos($img, '"', $src_start);
	$src = substr($img, $src_start, $src_end - $src_start);
	//getting link source
	$start = strpos($html, 'class="post"') + 12;
	$start = strpos($html, '>', $start) + 1;
	$end = strpos($html, '</h2>', $start);
	$link = trim(strip_tags(substr($html, $start, $end - $start), '<a>'));
	if($use_blank){
		$link = str_replace('<a', '<a target="_blank"', $link);
	}
	file_put_contents($link_path, $link);
	
	//got latest image in $src now resizing and caching

	$src_jpg = imagecreatefromjpeg($src);
	$img_ratio = imagesx($src_jpg) / imagesy($src_jpg);
	$final_image_height = $final_image_width / $img_ratio;
	$dest_jpg = imagecreatetruecolor($final_image_width, $final_image_height);
	imagecopyresized($dest_jpg, $src_jpg, 0, 0, 0, 0, $final_image_width, $final_image_height, imagesx($src_jpg), imagesy($src_jpg));
	imagejpeg($dest_jpg, $image_path, 100);
	imagedestroy($src_jpg);
	imagedestroy($dest_jpg);
	
	return $link;//returning for use with image
}

function widget_cheezburger($args) {
	$link = cheezburger_widget_image_retreiver();
	extract($args);
	echo $before_widget;
	echo $before_title;?>LolCat's Latest!<?php echo $after_title;?>
	<?php echo $link;?>
	<img alt="lolcats" src="<?php bloginfo('url');?>/wp-content/plugins/lolcats_widget/cheezes/latest.jpeg"><br>
	<?php
	echo $after_widget;
}

function cheezburgerwidget_init()
{
  register_sidebar_widget(__('ICanHasCheezburger'), 'widget_cheezburger');
}
add_action("plugins_loaded", "cheezburgerwidget_init");


?>
