<?php
  function rrp_connect() {
    try {
      $rrp = new SoapClient( 
        NULL, 
        array( 
          'location' => RRP_SOAPURL, 
          'uri' => 'urn:Api', 
          'style' => SOAP_RPC, 
          'use' => SOAP_ENCODED,
          'stream_context' => stream_context_create( array( 'ssl' => array( 'verify_peer' => false ) ) ),
          'encoding' => 'UTF-8',
          'exceptions' => true,
          'trace' => true
        ) 
      );
    } catch( Exception $e ) {
      echo '<p class="error">Fehler: konnte SOAP-API nicht initialisieren, '.$e->getMessage().'</p>';
      exit;
    }
    return $rrp;
  }

  function rrp_call( $arguments ) {
    global $rrp;
    if( !is_object( $rrp ) ) {
      $rrp = rrp_connect();
    }
    # Debug output of command
#   foreach( $arguments as $var => $val ) echo $var.'='.$val."<br/>\n";

    $arguments = array (
      array_merge( array(
        's_login' => RRP_USERNAME,
        's_pw' => RRP_PASSWORD,
        's_opmode' => RRP_OPMODE 
      ), $arguments ) 
    );
    $options = array(
      'uri' => 'urn:Api',
      'soapaction' => 'urn:Api#xcall'
    );
    try {
      $r = $rrp->__soapCall( 'xcall', $arguments, $options, NULL, $output );
    } catch( SoapFault $e ) {
      echo '<p class="error">Fehler: SOAP-Aufruf fehlgeschlagen</p><p>';
      print_r( $e );
      echo '</p><p>';
      print_r( $output );
      echo '</p><p>';
      print_r( $r );
      echo '</p>';
      exit;
    }
    return $r;
  }
  
  function rrp_domain( $cmd, $domain = '*', $extra = array() ) {
    switch( $cmd ) {
      case 'QueryDomainList':
        $extra = array(
          'wide' => '1'
        );
        break;
    }
   
    $arguments = array_merge( array(
      'command' => $cmd,
      'domain' => $domain
    ), $extra );

    if( !$r = rrp_call( $arguments ) ) {
      echo '<p class="error">Fehler: SOAP-Kommando "'.$cmd.'" mit Domain "'.$domain.'" ist fehlgeschlagen.</p>';
      exit;
    }
    return $r;
  }

  function rrp_contact( $cmd, $handle = '*', $extra = array() ) {
    $arguments = array_merge( array(
      'command' => $cmd,
      'contact' => $handle
    ), $extra );
    
    if( !$r = rrp_call( $arguments ) ) {
      echo '<p class="error">Fehler: SOAP-Kommando "'.$cmd.'" mit Kontakt "'.$handle.'" ist fehlgeschlagen.</p>';
      exit;
    }
    return $r;
  }

  function rrp_updatecurrencies() {
    $r = rrp_call( array( 
      'command' => 'QueryExchangeRates',
      'currencyfrom' => 'EUR'
    ) );
    # Kurse in Datenbank übertragen
    foreach( $r->PROPERTY->CURRENCYTO as $n => $currency ) {
      sql( '
        REPLACE INTO tbcurrencies 
        SET 
          currency = "'.$r->PROPERTY->CURRENCYTO[$n].'",
          date = "'.$r->PROPERTY->DATE[$n].'",
          rate = "'.$r->PROPERTY->RATE[$n].'"
      ;' );
    } # ENDE foreach currency
    # alte Kurse löschen (letzte 300 Kurse behalten)
    sql( '
      DELETE FROM tbcurrencies
      WHERE date <= (
        SELECT date FROM (
          SELECT date FROM tbcurrencies
          GROUP BY date
          ORDER BY date DESC
          LIMIT 1 OFFSET 300
        ) foo
      )
    ;' );
  }

  function rrp_updatezones() {
    # Domainpreise abrufen
    $r = rrp_call( array( 
      'command' => 'QueryZoneList'
    ) );
    # Preise in Datenbank übertragen
    foreach( $r->PROPERTY['ZONE'] as $n => $zone ) {
      # nur für Jahresgebühren
      if( $r->PROPERTY['PERIODTYPE'][$n] == 'YEAR' ) {
        # TLD ermitteln
        $zone = explode( '.', $r->PROPERTY['ZONE'][$n] );
        $tld = end( $zone );
        if( $zone[0] == $tld ) $sld = '';
        else $sld = $zone[0];
        $zone = $r->PROPERTY['ZONE'][$n];
        # Sonderfälle behandeln
        switch( $r->PROPERTY['ZONE'][$n] ) {
          case 'cenic_prem': $sld = 'xx'; $tld = 'com'; $r->PROPERTY['ZONE'][$n] = 'xx.com Premium'; break;
          case 'cenic_prem2': $sld = 'com'; $tld = 'de'; $r->PROPERTY['ZONE'][$n] = 'com.de Premium'; break;
          case 'cnidn': $sld = ''; $tld = 'cn'; $r->PROPERTY['ZONE'][$n] = 'cn (Sonderzeichen)'; break;
          case 'cnregional': $sld = 'xx'; $tld = 'cn'; $r->PROPERTY['ZONE'][$n] = 'xx.cn'; break;
          case 'nameemail': $sld = ''; $tld = 'name'; $r->PROPERTY['ZONE'][$n] = 'name E-Mail-Adresse'; break;
          case 'name_thirdlevel': $sld = 'xx'; $tld = 'name'; $r->PROPERTY['ZONE'][$n] = 'nachname.name'; break;
          case 'nuidn': $sld = ''; $tld = 'nu'; $r->PROPERTY['ZONE'][$n] = 'nu (Sonderzeichen)'; break;
          case 'twidn': $sld = ''; $tld = 'tw'; $r->PROPERTY['ZONE'][$n] = 'tw (Sonderzeichen)'; break;
        }

        sql( '
          INSERT INTO tbtld SET 
            name = ".'.$r->PROPERTY['ZONE'][$n].'",
            registry = "ks",
            zone = "'.$zone.'",
            setup = "'.$r->PROPERTY['SETUP'][$n].'",
            annual = "'.$r->PROPERTY['ANNUAL'][$n].'",
            transfer = "'.$r->PROPERTY['TRANSFER'][$n].'",
            trade = "'.$r->PROPERTY['TRADE'][$n].'",
            restore = "'.$r->PROPERTY['RESTORE'][$n].'",
            application = "'.$r->PROPERTY['APPLICATION'][$n].'",
            currency = "'.$r->PROPERTY['CURRENCY'][$n].'",
            sld = "'.$sld.'",
            tld = "'.$tld.'",
            vk_alt = 0
          ON DUPLICATE KEY UPDATE 
            zone = "'.$zone.'",
            setup = "'.$r->PROPERTY['SETUP'][$n].'",
            annual = "'.$r->PROPERTY['ANNUAL'][$n].'",
            transfer = "'.$r->PROPERTY['TRANSFER'][$n].'",
            trade = "'.$r->PROPERTY['TRADE'][$n].'",
            restore = "'.$r->PROPERTY['RESTORE'][$n].'",
            application = "'.$r->PROPERTY['APPLICATION'][$n].'",
            currency = "'.$r->PROPERTY['CURRENCY'][$n].'",
            sld = "'.$sld.'",
            tld = "'.$tld.'"
        ;' );
      } # ENDE if Jahresgebühr
    } # ENDE foreach zone

    # Zertifikatpreise abrufen
    $r = rrp_call( array( 
      'command' => 'QueryServiceList',
      'service' => 'certificate'
    ) );
    # Preise in Datenbank übertragen
    foreach( $r->PROPERTY->TYPE as $n => $type ) {

      # %san ausfiltern
      if( !preg_match( '/san$/', $r->PROPERTY->TYPE[$n] ) ) {
    
        # nur für Jahresgebühren
        if( $r->PROPERTY->PERIODTYPE[$n] == 'YEAR' ) {
          sql( '
            INSERT INTO tbcert SET 
              name = "'.$r->PROPERTY->DESCRIPTION[$n].'",
              registry = "ks",
              type = "'.$r->PROPERTY->TYPE[$n].'",
              annual = "'.$r->PROPERTY->ANNUAL[$n].'",
              currency = "'.$r->PROPERTY->CURRENCY[$n].'",
              vk_alt = 0
            ON DUPLICATE KEY UPDATE 
              annual = "'.$r->PROPERTY->ANNUAL[$n].'",
              currency = "'.$r->PROPERTY->CURRENCY[$n].'"
          ;' );
        } # ENDE if Jahresgebühr
      } # ENDE if not %san
    } # ENDE foreach zone

  }
  
  function rrp_recalculate() {
    # aktuelle Kurse basierend auf Historie berechnen und zwischenspeichern
    $r = sql( '
      SELECT 
        currency,
        AVG( rate ) AS avg,
        MAX( rate ) - MIN( rate ) AS diff,
        MIN( rate ) AS min,
        MAX( rate ) AS max
      FROM tbcurrencies
      GROUP BY currency
      ORDER BY currency ASC
    ;' );
    while( $data = mysqli_fetch_assoc( $r ) ) {
      # Kurs ist Durchschnittskurs - Differenz von Minimal- und Maximalkurs
      $rate[$data['currency']] = $data['avg'] - ( $data['diff'] / 2 );
    }
    
    # Domains durchlaufen
    $r = sql( '
      SELECT
        zone,
        registry,
        annual,
        currency
      FROM tbtld
    ;' );
    while( $domain = mysqli_fetch_assoc( $r ) ) {
      # EK berechnen
      if( $domain['currency'] != 'EUR' )
        $ek = $domain['annual'] / $rate[$domain['currency']] * 1.03;
      else
        $ek = $domain['annual'] * 1.03;
      # VK berechnen
      $marge = $ek * 0.2;
      if( $marge < 1 ) $marge = 1.1;
      $vk = ceil( $ek + $marge );
      
      sql( '
        UPDATE tbtld SET 
          ek_eur = "'.$ek.'",
          vk_neu = "'.$vk.'"
        WHERE zone = "'.$domain['zone'].'" AND registry = "'.$domain['registry'].'"
      ;' );
    } # END while domain

    # Zertifikate durchlaufen
    $r = sql( '
      SELECT
        type,
        registry,
        annual,
        currency
      FROM tbcert
    ;' );
    while( $cert = mysqli_fetch_assoc( $r ) ) {
      # EK berechnen
      if( $cert['currency'] != 'EUR' )
        $ek = $cert['annual'] / $rate[$cert['currency']] * 1.03;
      else
        $ek = $cert['annual'] * 1.03;
      # VK berechnen
      $marge = $ek * 0.3;
      if( $marge < 1 ) $marge = 1.1;
      $vk = ceil( $ek + $marge );
      
      sql( '
        UPDATE tbcert SET 
          ek_eur = "'.$ek.'",
          vk_neu = "'.$vk.'"
        WHERE type = "'.$cert['type'].'" AND registry = "'.$cert['registry'].'"
      ;' );
    } # END while domain
    
  } # END function rrp_recalculate
?>