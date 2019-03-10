<?php
  function val_anrede( $anrede ) {
    if( stristr( $anrede, 'frau' ) ) $anrede = 'Frau';
    elseif( stristr( $anrede, 'herr' ) ) $anrede = 'Herr';
    else $anrede = false;
    return $anrede;
  }
    
  function val_name($name) {
    if (eregi("^[a-zA-ZäöüÄÖÜ]+([-.[:blank:]]*[a-zA-ZäöüÄÖÜ]+)*$", $name)) $result=$name;
    else $result=false;
    return $result;
  }

  function val_adresse($adresse) {
    if (eregi("^[a-zA-ZäöüÄÖÜ0-9]+([-.,/[:blank:]]*[a-zA-ZäöüÄÖÜ0-9]+)*$", $adresse)) $result=$adresse;
    else $result=false;
    return $result;
  }

  function val_plz($plz) {
    if (eregi("^[a-zA-Z0-9]+$", $plz)) $result=$plz;
    else $result=false;
    return $result;
  }
  
  function val_tel($tel) {
    if (eregi("^[+]{0,2}([-/[:blank:]]*[0-9]+)+$", $tel)) $result=$tel;
    else $result=false;
    return $result;
  }

  function val_datum($datum) {
    if (eregi("^([0-9][0-9])?[0-9]{2}\-[0-1]?[0-9]{1}\-[0-3]?[0-9]{1}$", $datum)) $result=$datum;
    else $result=false;
    if ($result) {
      list($jahr, $mon, $tag)=explode("-", $datum);
      $tag=str_pad($tag, 2, "0", STR_PAD_LEFT);
      $mon=str_pad($mon, 2, "0", STR_PAD_LEFT);
      if (strlen($jahr)<4) {
        $aJahr=date("Y");
        $a1Jahr=substr($aJahr, 0, 2);
        $a2Jahr=date("y");
        if (($jahr-50)>$a2Jahr) $jahr+=(($a1Jahr-1)*100);
        else $jahr+=($a1Jahr*100);
      }
      if ($tag<1||$tag>31) $result=false;
      elseif ($mon<1||$mon>12) $result=false;
      else $result="${jahr}-${mon}-${tag}";
    }
    return $result;
  }
  
  function val_email($email, $depth) {
    if (eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email)) $result=$email;
    else $email=false;
    if ($depth&&$result) {
      list ($username, $domain)=split("@", $email);
      if (getmxrr($domain, $mx)) $mailserver=$mx[0];
      else $mailserver=$domain;
      $connect=fsockopen ($mailserver, 25);
      if ($connect) {
        if (ereg("^220", $msg=fgets($connect, 1024))) {
          fputs($connect, "HELO mx1.80686-net.de\r\n");
          $msg=fgets($connect, 1024);
          fputs($connect, "MAIL FROM: <info@pro-file.info>\r\n");
          $frommsg=fgets($connect, 1024);
          fputs($connect, "RCPT TO: <${email}>\r\n");
          $tomsg=fgets($connect, 1024);
          fputs($connect, "QUIT\r\n");
          fclose($connect);
          if (!ereg("^250", $frommsg)||!ereg( "^250", $tomsg)) $result=false;
        } else $result=false; 
      } else $result=false; 
    }
    return $result;
  }
  
  function val_url($url) {
    if (eregi("^(http://)?([-_a-zA-Z0-9]+[.])+[a-zA-Z]{2,4}([/][-_a-zA-Z0-9]*)*$", $url)) $result=$url;
    else $url=false;
    return $result;
  }

  function val_zahl($zahl, $min, $max) {
    if (eregi("^[0-9]*$", $url)) $result=$zahl;
    else $result=0;
    if ($result) {
      if ($result<$min) $result=0;
      elseif ($result>$max) $result=0;
    }
    return $result;
  }

  function val_bool( $bool ) {
    if( $bool ) return 1;
    else return 0;
  }

  function val_ipv4( $ip ) {
    if( preg_match( '/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/' ) ) return $ip;
    else return false;
  
  }

  function val_hostname( $host ) {
    if( preg_match( '/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/', $host ) ) return $host;
    else return false;
  }

  function val_domain( $domain ) {
    if( preg_match( '/^[a-zA-Z0-9][a-zA-Z0-9\-\_]+[a-zA-Z0-9]$/', $domain ) ) return $domain;
    else return false;
  }

  function val_password( $password ) {
    if( strlen( $password ) < 8 ) return false;
    elseif( !preg_match( '#[0-9]+#', $password ) ) return false;
    elseif( !preg_match( '#[a-zA-Z]+#', $password ) ) return false;
    else return( true );
  }
?>
