<?php
  # Funktionen, die für diverse Abrechnungszwecke zu gebrauchen sind
  
  # berechnet den letzten Tag eines Quartals (wenn nichts angegeben wurde des aktuellen)
  function quarter_end( $date = false ) {
    # Startdatum
    if( !$date ) $date = time();
    elseif( strtotime( $date ) ) $date = strtotime( $date );
    # Monat ermitteln
    $month = ceil( idate( 'm', $date ) / 3 ) * 3 ;
    # Jahr ermitteln
    $year = idate( 'Y', $date );
    if( $month > 12 ) {
      $month -= 12;
      $year++;
    }
    # Letzter Monatstag
    switch( $month ) {
      case 3: $day = 31;
        break;
      case 6: $day = 30;
        break;
      case 9: $day = 30;
        break;
      case 12: $day = 31;
        break;
    }
    return( strtotime( $year.'-'.$month.'-'.$day ) );
  }

  function interval_end( $date = false, $interval = 'm', $ref = false ) {
    # Ursprungsdatum
    if( !$date ) $date = time();
    elseif( strtotime( $date ) ) $date = strtotime( $date );
    if( !$ref ) $ref = time();
    elseif( strtotime( $ref ) ) $ref = strtotime( $ref );
    # Berechung
    switch( $interval[0] ) {
      case 'y':
        if( strtotime( date( 'Y', $ref ).'-'.date( 'm', $date ).'-'.date( 'd', $date ) ) >= $ref ) {
          return( strtotime( date( 'Y', $ref ).'-'.date( 'm', $date ).'-'.date( 'd', $date ) ) );
        } else {
          $new = strtotime( '+1 Year', $ref );
          return( strtotime( date( 'Y', $new ).'-'.date( 'm', $date ).'-'.date( 'd', $date ) ) );
        }
        break;
      case 'q':
        $new = quarter_end( $ref );
        return( strtotime( date( 'Y', $new ).'-'.date( 'm', $new ).'-'.date( 'd', $date ) ) );
        break;
      default:	# Monat
        $new = strtotime( '+1 Month', $ref );
        return( strtotime( date( 'Y', $new ).'-'.date( 'm', $new ).'-'.date( 'd', $date ) ) );
    }
  }
  
  function nice_date( $date = false ) {
    if( !$date ) $date = time();
    elseif( strtotime( $date ) ) $date = strtotime( $date );
    return date( 'd.m.Y', $date );
  }
  
  function mysql_date( $date = false ) {
    if( !$date ) $date = time();
    elseif( strtotime( $date ) ) $date = strtotime( $date );
    return date( 'Y-m-d', $date );
  }

  function nice_price( $price ) {
    return number_format( round( $price, 2 ), 2, ',', '.' );
  }

  function nice_tax( $tax ) {
    return number_format( $tax, 1, ',', '.' );
  }

  function latex_safe( $text ) {
    $text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
    $search = array(
      '\\',
      '€',
      '#',
      '$',
      '%',
      '&',
      '~',
      '_',
      '^',
      '{',
      '}',
      '>',
      '<',
      '/',
      '°',
      "\n"
    );
    $replace = array(
      '',
      '\\euro{}',
      '\\#',
      '\\$',
      '\\%',
      '\\&',
      '\\~',
      '\\_',
      '\\^',
      '\\{',
      '\\}',
      '$>$',
      '$<$',
      '\\/',
      '\\degree',
      "\n".'\\newline '
    );
    return str_replace( $search, $replace, $text );
  }

  function nice_domain( $sub = false, $domain = false ) {
    if( $sub && $domain ) return $sub.'.'.$domain;
    elseif( $sub ) return $sub;
    elseif( $domain ) return $domain;
    else return false;
  }

  function nice_bytes( $size, $unit = 'bytes' ) {
    $pow = array( 0 => '', 1 => 'k', 2 => 'M', 3 => 'G', 4 => 'T' );
    if( stripos( $unit, 'bit' ) !== false ) $size /= 8;
    $i = 0;	# Bytes
    # Faktor ermitteln
    if( ! ( $i = array_search( strtoupper( $unit[0] ), $pow ) ) )
      if( ! ($i = array_search( strtolower( $unit[0] ), $pow ) ) ) 
        $i = 0;
    while( $size > 1024 ) {
      $size /= 1024;
      $i++;
    }
    return( round( $size, 2 ).' '.$pow[$i].'B' );
  }
  
  function next_id( $table, $field = 'id', $start = 1 ) {
    $id[0] = 1;
    $i = $start;
    while( $id[0] ) {
      $i++;
      $r = sql( 'SELECT '.$field.' FROM '.$table.' WHERE '.$field.' = '.$i.';' );
      $id = mysqli_fetch_row( $r );
    }
    return $i;
  }

  function pad( $i, $n = 5 ) {
    return str_pad( $i, $n, '0', STR_PAD_LEFT );
  }

  function mpassword( $characters = 6, $numbers = 2 ) {
    $password = '';
    $letters = array(
      0 => array( 'B', 'V', 'C', 'X', 'G', 'F', 'D', 'S', 'T', 'R', 'W', 'Q', 'q', 'w', 'r', 't', 's', 'd', 'f', 'g', 'x', 'c', 'v', 'b' ),
      1 => array( 'J', 'U', 'u', 'i', 'j', '-' ),
      2 => array( 'M', 'N', 'L', 'K', 'H', 'P', 'Z', 'z', 'p', 'h', 'k', 'n', 'm' ),
      3 => array( 'Y', 'A', 'E', 'e', 'a', 'y' )
    );
    $digits = array( 
      0 => array( '2', '3', '4', '5', '6' ),
      1 => array( '7', '8', '9', '+'  )
     );
     $blacklist = array( 'cum', 'fuc', 'fuk', 'fik', 'fic', 'shit', 'poo', 'penis', 'vag' );
     # generate string part
     for( $i = 0; $i < $characters; $i++ ) $password .= $letters[$i % 4][array_rand( $letters[$i % 4] )];
     # check blacklist, restart if word is found
     foreach( $blacklist as $word ) if( stripos( $password, $word ) ) return mpassword( $characters, $numbers );
     # add numbers
     if( $numbers > 0 ) for( $i = 0; $i < $numbers; $i++ ) $password .= $digits[$i % 2][array_rand( $digits[$i % 2] )];
     
     return $password;
  } # END function mpassword

  $registries = array( 'ks', 'lv' );

	# get domain details
	function prepare_domain( $domain, $tld ) {

		if( $tld ) $domain = $domain.'.'.$tld;

		$r = sql( '
			SELECT 
				sld,
				tld,
				registry,
				registry_zone
			FROM tbdomain 
			WHERE domain = "'.idn_to_ascii( trim( $domain ) ).'"
			;' );
			
		if( $data = mysqli_fetch_assoc( $r ) ) {
			# prepare domain root
			$data['domain'] = substr( $domain, 0, strpos( $domain, '.' ) );
			
			# prepare tld
			if( $data['sld'] != '' ) $data['tld'] = $data['sld'].'.'.$data['tld'];

			return( $data );
		} else {
			return( false );
		} # if fetch data
	} # function get_domain_array

  # Kontakt-Handles
  function get_handle( $kunde, $registry ) {
    # Kundendaten holen
    $r = sql( 'SELECT * FROM tbuser WHERE uid = "'.$kunde.'";' );
    $kunde = mysqli_fetch_assoc( $r );
    # prüfen ob Kontakt-Handle für gewünschte Registry existiert
    if( $kunde['handle_'.$registry] == '' ) {
      # Handle existiert noch nicht - anlegen
      # Kontaktdaten für NIC-Handle vorbereiten
      $contact = array(
        'title' => $kunde['titel'],
        'firstname' => $kunde['vorname'],
        'lastname' => $kunde['name'],
        'organization' => $kunde['firma'],
        'street' => $kunde['adresse'],
        'city' => $kunde['ort'],
        'state' => '',
        'zip' => $kunde['plz'],
        'country' => $kunde['land'],
        'phone' => $kunde['tel'],
        'fax' => $kunde['fax'],
        'email' => $kunde['email']
      );
      # Handle neu anlegen
      switch( $registry ) {
        # Keysystems
        case 'ks':
          # Lege Handle an
          $r = rrp_contact( 'AddContact', NULL, $contact );
          # Prüfe Erfolg
          if( $r->CODE == '200' ) {
            # Handle wurde angelegt - Rückgabe Handle
            $return['error'] = false;
            $return['handle'] = $r->PROPERTY->CONTACT[0];
          } else {
            # Handle wurde nicht angelegt - Rückgabe Fehler
            $return['error']= $r->CODE.': '.$r->DESCRIPTION;
            $return['handle'] = false;
          } # END if Code
          break;
        case 'lv':
          # Lege Handle an
          $r = '';
          # Prüfe Erfolg
          if( $r == '' ) {
            # Handle wurde angelegt - Rückgabe Handle
            $return['error'] = false;
            $return['handle'] = $r->PROPERTY->CONTACT[0];
          } else {
            # Handle wurde nicht angelegt - Rückgabe Fehler
            $return['error']= $r->CODE.': '.$r->DESCRIPTION;
            $return['handle'] = false;
          } # END if Code
          break;
      } # END switch registry
      if( !$return['error'] && $return['handle'] ) {
        # generiertes Handle speichern
        sql( 'UPDATE tbuser SET handle_'.$registry.' = "'.$return['handle'].'" WHERE uid = "'.$kunde['uid'].'";' );
      }
      # retourniere Rückgabe von Generierung
      return $return;
    } else {
      # retourniere gefundenes Handle
      return array( 'error' => false, 'handle' => $kunde['handle_'.$registry] );
    } # END if Handle existiert
  } # END function get_handle

  function update_handle( $kunde ) {
    global $registries;
    $return = false;
    # Kundendaten holen
    $r = sql( 'SELECT * FROM tbuser WHERE uid = "'.$kunde.'";' );
    $kunde = mysqli_fetch_assoc( $r );
    foreach( $registries as $registry ) {
      # prüfen ob Kontakt-Handle für gewünschte Registry existiert
      if( $kunde['handle_'.$registry] ) {
        # Handle existiert - aktualisieren
        # Kontaktdaten für NIC-Handle vorbereiten
        $contact = array(
          'title' => $kunde['titel'],
          'firstname' => $kunde['vorname'],
          'lastname' => $kunde['name'],
          'organization' => $kunde['firma'],
          'street' => $kunde['adresse'],
          'city' => $kunde['ort'],
          'state' => '',
          'zip' => $kunde['plz'],
          'country' => $kunde['land'],
          'phone' => $kunde['tel'],
          'fax' => $kunde['fax'],
          'email' => $kunde['email']
        );
        # Handle aktualisieren
        switch( $registry ) {
          # Keysystems
          case 'ks':
            # Lege Handle an
            $r = rrp_contact( 'ModifyContact', $kunde['handle_'.$registry], $contact );
            # Prüfe Erfolg
            if( $r->CODE != '200' ) {
              # Handle wurde nicht aktualisiert - Rückgabe Fehler
              $return .= 'KS '.$r->CODE.': '.$r->DESCRIPTION.'<br/>';
            } # END if Code
            break;
          case 'lv':
            # Lege Handle an
            $r = '';
            # Prüfe Erfolg
            if( $r == '' ) {
              # Handle wurde nicht aktualisiert - Rückgabe Fehler
              $return .= 'LV '.$r->CODE.': '.$r->DESCRIPTION.'<br/>';
            } # END if Code
            break;
        } # END switch registry
      } # END if Handle existiert
    } # END foreach
    return( $return );
  } # END function get_handle

  function check_domain( $domain, $registry ) {
    # true: Domain frei
    # false: Domain registriert
    switch( $registry ) {
      # Keysystems
      case 'ks':
        # Prüfung durchführen
        $rrp = rrp_connect();
        $r = rrp_domain( 'CheckDomain', $domain );
        if( $r->CODE == 211 ) return false;
        elseif( $r->CODE == 210 ) return true;
        else return $r->CODE;
        break;
      # NIC.LV
      case 'lv':
        break;
    }
  } # END function check_domain

  function reg_domain( $domain, $tld, $handle, $registry ) {
    $error = false;
    switch( $registry ) {
      # Keysystems
      case 'ks':
        # Registrierung durchführen
        switch( $tld ) {
          case 'hk':
            # determine whether registrant is Individual or Organisation
            $r = sql( 'SELECT land, ausweisnr, regtyp, regnr FROM tbuser WHERE handle_ks = "'.$handle.'";' );
            $kunde = mysqli_fetch_assoc( $r );
            if( $kunde['regtyp'] && $kunde['regnr'] ) {
              $r = rrp_domain( 
                'AddDomain', 
                $domain.'.'.$tld,
                array( 
                  'X-HK-DOMAIN-CATEGORY' => 'O',
                  'X-HK-OWNER-DOCUMENT-ORIGIN-COUNTRY' => $kunde['land'],
                  'X-HK-OWNER-DOCUMENT-TYPE' => 'OTHORG',
                  'X-HK-OWNER-OTHER-DOCUMENT-TYPE' => $kunde['regtyp'],
                  'X-HK-OWNER-DOCUMENT-NUMBER' => $kunde['regnr'],
                  'ownercontact0' => $handle, 
                  'admincontact0' => $handle, 
                  'techcontact0' => 'P-MFS4136', 
                  'billingcontact0' => 'P-MFS4136', 
                  'nameserver0' => 'ns1.mastersdns.com', 
                  'nameserver1' => 'ns2.mastersdns.com'
                ) 
              );
            } else {
              $r = rrp_domain( 
                'AddDomain', 
                $domain.'.'.$tld,
                array( 
                  'X-HK-DOMAIN-CATEGORY' => 'I',
                  'X-HK-OWNER-DOCUMENT-ORIGIN-COUNTRY' => $kunde['land'],
                  'X-HK-OWNER-DOCUMENT-TYPE' => 'OTHID',
                  'X-HK-OWNER-DOCUMENT-NUMBER' => $kunde['ausweisnr'],
                  'ownercontact0' => $handle,
                  'admincontact0' => $handle, 
                  'techcontact0' => 'P-MFS4136', 
                  'billingcontact0' => 'P-MFS4136', 
                  'nameserver0' => 'ns1.mastersdns.com', 
                  'nameserver1' => 'ns2.mastersdns.com'
                ) 
              );
            }
            break;
          case 'uk':
            $r = rrp_domain( 
              'AddDomain', 
              $domain.'.'.$tld,
              array( 
                'X-UK-ACCEPT-TRUSTEE-TAC' => '1',
                'ownercontact0' => $handle, 
                'admincontact0' => $handle, 
                'techcontact0' => 'P-MFS4136', 
                'billingcontact0' => 'P-MFS4136', 
                'nameserver0' => 'ns1.mastersdns.com', 
                'nameserver1' => 'ns2.mastersdns.com'
              ) 
            );
            break;
          default:
            $r = rrp_domain( 
              'AddDomain', 
              $domain.'.'.$tld,
              array( 
                'ownercontact0' => $handle, 
                'admincontact0' => $handle, 
                'techcontact0' => 'P-MFS4136', 
                'billingcontact0' => 'P-MFS4136', 
                'nameserver0' => 'ns1.mastersdns.com', 
                'nameserver1' => 'ns2.mastersdns.com'
              ) 
            );
            break;
        }
        # Ergebnis abprüfen und retournieren
        if( $r->CODE == 200 ) return false;
        else return $r->CODE.': '.$r->DESCRIPTION;
        break;
      # NIC.LV
      case 'lv':
        # Registrierung durchführen
        # Ergebnis abprüfen und retournieren
        break;
    } # END switch registry
  } # END function reg_domain

  function set_authcode( $domain, $tld = false ) {
  	
		$domain = prepare_domain( $domain, $tld );
		# generate authcode
		$auth = mpassword( 10, 4 );
		
		switch( $domain['registry'] ) {
			# Keysystems
			case 'ks':
				switch( $domain['tld'] ) {
					case 'eu':
						# allow transfer
						$r = rrp_domain( 
							'ModifyDomain', 
							$domain['domain'].'.'.$domain['tld'],
							$extra = array( 
								'TRANSFERMODE' => 'AUTOAPPROVE',
								'RENEWALMODE' => 'AUTODELETE',
								'transferlock' => 0
							)
						);
						# check result
						if( $r->CODE != 200 ) {
							echo $r->DESCRIPTION;
							$auth = false;
						}
						# SetAuthocde
						$r = rrp_call(
							array( 
								'command' => 'SetAuthcode',
								'domain' => $domain['domain'].'.'.$domain['tld'],
								'auth' => $auth,
								'action' => 'set'
							) 
						);
						# check result
						if( $r->CODE != 200 ) {
							echo $r->DESCRIPTION;
							$auth = false;
						} else {
							$r = rrp_call(
								array( 
									'command' => 'StatusDomain',
									'domain' => $domain['domain'].'.'.$domain['tld'],
								) 
							);
							$close = $r->PROPERTY->REGISTRATION_EXPIRATION_DATE[0];
							$auth = trim( $r->PROPERTY->AUTH[0] );
						}
						break; # case eu

					default:
						# allow transfer and set authcode
						$r = rrp_domain( 
							'ModifyDomain', 
							$domain['domain'].'.'.$domain['tld'],
							$extra = array( 
								'TRANSFERMODE' => 'AUTOAPPROVE',
								'RENEWALMODE' => 'AUTODELETE',
								'transferlock' => 0,
								'auth' => $auth
							)
						);
						# check result
						if( $r->CODE != 200 ) {
							echo $r->DESCRIPTION;
							$auth = false;
						} else {
							$r = rrp_call(
								array( 
									'command' => 'StatusDomain',
									'domain' => $domain['domain'].'.'.$domain['tld'],
								) 
							);
							$close = $r->PROPERTY->REGISTRATION_EXPIRATION_DATE[0];
							$auth = trim( $r->PROPERTY->AUTH[0] );
						}
						break; # default
				} # switch tld
				break; # case ks

			if( $auth ) {
				sql( 'UPDATE tbdomain SET close = "'.$close.'", authcode = "'.$auth.'" WHERE domain = "'.$domain['domain'].'.'.$domain['tld'].'";' );
				return( $auth );
			} else {
				return( false );
			}

		} #switch registry
		
		return( trim( $auth ) );
  } # function set_authcode

  function get_authcode( $domain, $tld = false ) {
		$domain = prepare_domain( $domain, $tld );
		$r = rrp_domain( 'statusDomain', $domain['domain'].'.'.$domain['tld'] );
		$auth = trim( $r->PROPERTY->AUTH[0] );
		sql( 'UPDATE tbdomain SET authcode = "'.$authcode.'" WHERE domain = "'.$domain['domain'].'.'.$domain['tld'].'";' );
		return( $auth );
  } # function get_authcode

  function transfer_domain( $domain, $tld, $handle, $registry, $auth ) {
    $s = false;
    switch( $registry ) {
      # Keysystems
      case 'ks':
        # Transfer durchführen
        switch( $tld ) {
          case 'es':
            # no authcode
            $r = rrp_domain( 'TransferDomain',
              $domain.'.'.$tld, 
              array( 
                'action' => 'REQUEST'
              ) 
            );
            $s = update_domain( $domain.'.'.$tld );
            break;
          case 'at':
          case 'be':
          case 'ch':
          case 'cn':
          case 'eu':
          case 'li':
          case 'co.uk':
          case 'com':
            $r = rrp_domain( 'TransferDomain',
              $domain.'.'.$tld, 
              array( 
                'action' => 'REQUEST',
                'auth' => stripslashes( $auth )
              ) 
            );
            $s = update_domain( $domain.'.'.$tld );
            break;
          default:
            $r = rrp_domain( 'TransferDomain',
              $domain.'.'.$tld,
              array(
                'action' => 'REQUEST',
                'auth' => stripslashes( $auth ),
                'ownercontact0' => $handle,
                'admincontact0' => $handle,
                'techcontact0' => 'P-MFS4136',
                'billingcontact0' => 'P-MFS4136',
                'nameserver0' => 'ns1.mastersdns.com',
                'nameserver1' => 'ns2.mastersdns.com'
              )
            );
        } # END switch tld
        break;
      # NIC.LV
      case 'lv':
        # Registrierung durchführen
        # Ergebnis abprüfen und retournieren
        break;
    } # END switch registry
    # Ergebnis abprüfen und retournieren
    if( $r->CODE == 200 || $r->CODE == 536 ) {
      return false;
    }
    else return $r->CODE.': '.$r->DESCRIPTION;
  } # END function reg_domain

  function update_domain( $domain, $ns1 = 'ns1.mastersdns.com', $ns2 = 'ns2.mastersdns.com', $ns3 = '' ) {
    # Handle holen
    $r = sql( 'SELECT 
        tbuser.*,
        tbdomain.registry
      FROM tbuser INNER JOIN tbdomain 
        ON tbuser.uid = tbdomain.kunde
      WHERE tbdomain.domain = "'.$domain.'"
    ;' );
    $data = mysqli_fetch_assoc( $r );
    $handle = get_handle( $data['uid'], $data['registry'] );
    if( $handle['error'] ) {
      return $handle['error'];
    } else {
      $handle = $handle['handle'];
    }
    switch( $data['registry'] ) {
      # Keysystems
      case 'ks':
        # Update durchführen
        switch( $tld ) {
          default:
            $r = rrp_domain( 'ModifyDomain',
              $domain,
              array(
                'ownercontact0' => $handle,
                'admincontact0' => $handle,
                'techcontact0' => 'P-MFS4136',
                'billingcontact0' => 'P-MFS4136',
                'nameserver0' => $ns1,
                'nameserver1' => $ns2,
                'nameserver2' => $ns3
              )
            );
            if( $r->CODE == 0 || $r->CODE == 541 || $r->CODE == 549 ) {
              $r = rrp_domain( 'TradeDomain', 
                $domain,
                array( 
                  'ownercontact0' => $handle, 
                  'admincontact0' => $handle,
                  'techcontact0' => 'P-MFS4136',
                  'billingcontact0' => 'P-MFS4136',
                  'nameserver0' => 'ns1.mastersdns.com',
                  'nameserver1' => 'ns2.mastersdns.com'
                )
              );
              #$r = update_domain( $domain, $ns1 = 'ns1.mastersdns.com', $ns2 = 'ns2.mastersdns.com', $ns3 = '' );
            }
        } # END switch tld
        break;
      # NIC.LV
      case 'lv':
        # Update durchführen
        # Ergebnis abprüfen und retournieren
        break;
      # other (no) registry
      default:
        return( 'not registered here' );
        break;
    } # END switch registry
    # Ergebnis abprüfen und retournieren
    if( $r->CODE == 200 ) {
      return false;
    }
    else $r;
  } # END function update_domain
  
	function cancel_domain( $domain, $tld = false ) {
		$domain = prepare_domain( $domain, $tld );
		# allow transfer
		$r = rrp_domain( 
			'ModifyDomain', 
			$domain['domain'].'.'.$domain['tld'],
			$extra = array( 
				'TRANSFERMODE' => 'AUTOAPPROVE',
				'RENEWALMODE' => 'AUTODELETE',
				'transferlock' => 0
			)
		);
		# check result
		if( $r->CODE != 200 ) {
			echo $r->DESCRIPTION;
			$auth = false;
		} else {
			$r = rrp_call(
				array( 
					'command' => 'StatusDomain',
					'domain' => $domain['domain'].'.'.$domain['tld'],
				) 
			);
			$close = $r->PROPERTY->REGISTRATION_EXPIRATION_DATE[0];
			$auth = trim( $r->PROPERTY->AUTH[0] );
		}

		if( $auth ) {
			sql( 'UPDATE tbdomain SET close = "'.$close.'", authcode = "'.$auth.'" WHERE domain = "'.$domain['domain'].'.'.$domain['tld'].'";' );
			return( $auth );
		} else {
			return( false );
		}

  } # function cancel_domain

	function keep_domain( $domain, $tld = false ) {
		$domain = prepare_domain( $domain, $tld );
		# allow transfer
		$r = rrp_domain( 
			'ModifyDomain', 
			$domain['domain'].'.'.$domain['tld'],
			$extra = array( 
				'TRANSFERMODE' => 'DEFAULT',
				'RENEWALMODE' => 'DEFAULT',
				'transferlock' => 1
			)
		);
		# check result
		if( $r->CODE != 200 ) {
			echo $r->DESCRIPTION;
			$auth = false;
		} else {
			$r = rrp_call(
				array( 
					'command' => 'StatusDomain',
					'domain' => $domain['domain'].'.'.$domain['tld'],
				) 
			);
			$auth = trim( $r->PROPERTY->AUTH[0] );
		}

		if( $auth ) {
			sql( 'UPDATE tbdomain SET close = "NULL", authcode = "'.$auth.'" WHERE domain = "'.$domain['domain'].'.'.$domain['tld'].'";' );
			return( $auth );
		} else {
			return( false );
		}

  } # function cancel_domain
