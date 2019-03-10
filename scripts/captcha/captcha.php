<?php
  $alphabet = 'ABCDEFGHJKLMNPRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
  $length = 5;
  session_start();
  $basedir = dirname( $_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF'] ).'/';
  unset( $string );

  while( strlen( $string ) < $length ) {
    $string .= $alphabet[ mt_rand( 0, strlen( $alphabet ) -1 ) ];
  }
  $_SESSION['captcha'] = $string;
  
  header( 'Content-type: image/png' );
  $image = ImageCreateFromPNG( $basedir.'captcha.'.mt_rand( 1, count( glob( $basedir.'captcha.*.png' ) ) ).'.png' ); //Backgroundimage
  $color = ImageColorAllocate( $image, 0, 0, 0 ); //Farbe
  $ttf = $basedir.'captcha.'.mt_rand( 1, count( glob( $basedir.'captcha.*.ttf' ) ) ).'.ttf'; //Schriftart
  $ttfsize = 25; //Schriftgrösse
  $angle = mt_rand( 0, 5 );
  $t_x = mt_rand( 5, 30 );
  $t_y = 35;
  imagettftext( $image, $ttfsize, $angle, $t_x, $t_y, $color, $ttf, $string );
  imagepng( $image );
  imagedestroy( $image );
?>