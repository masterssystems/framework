<?php

	function login( $username = false, $password = false ) {
		global $db;
		
		# check if user exists - password could be either in MySQL or PAM
		$r = sql( '
			SELECT tbuser.uid
			FROM tbuser 
			INNER JOIN tbcompany
				ON tbuser.company = tbcompany.id
			WHERE tbuser.username = "'.$username.'" 
				AND tbuser.valid_from <= CURDATE() 
				AND ( tbuser.valid_to >= CURDATE() OR tbuser.valid_to = "0000-00-00" )
				AND tbcompany.valid_from <= CURDATE() 
				AND ( tbcompany.valid_to >= CURDATE() OR tbcompany.valid_to = "0000-00-00" )
			;' );
			
		if( list( $uid ) = mysqli_fetch_row( $r ) ) {
			# now password against MySQL database
			sql( '
				SELECT uid
				FROM tbuser
				WHERE username = "'.$username.'" 
					AND ( password = "'.$password.'" OR password = ENCRYPT( "'.$password.'", LEFT( password, 2 ) ) )
					AND password != ""
				;' );
			if( mysqli_affected_rows( $db ) ) {
				# do the login
				sql( 'UPDATE tbuser SET login_time = NOW() WHERE uid = "'.$uid.'";' );
				return( $uid );
				
			}	else {
				# check password against PAM
				$p = proc_open( 
					FRAMEWORK_PWAUTH,
					array( 
						0 => array( 'pipe', 'r' ),
						1 => array( 'pipe', 'w' ),
						2 => array( 'pipe', 'w' ),
						),
					$pipe
					);
				# send username to stdin
				fwrite( $pipe[0], $username."\n" );
				# send password to stdin
				fwrite( $pipe[0], $password."\n" );
				# finish and catch result
				$r = proc_close( $p );
				
				# return
				if( $r === 0 ) {
					# do the login
					sql( 'UPDATE tbuser SET login_time = NOW() WHERE uid = "'.$uid.'";' );
					return( $uid );

				} else return false;
			}
		} else {
			return false;
		}
	}

	function getuserbyname( $username ) {
		$r = sql( '
			SELECT tbuser.* 
			FROM tbuser 
			INNER JOIN tbcompany
				ON tbuser.company = tbcompany.id
			WHERE tbuser.username = "'.$username.'" 
				AND tbuser.valid_from <= CURDATE() 
				AND ( tbuser.valid_to >= CURDATE() OR tbuser.valid_to = "0000-00-00" )
				AND tbcompany.valid_from <= CURDATE() 
				AND ( tbcompany.valid_to >= CURDATE() OR tbcompany.valid_to = "0000-00-00" )
			;' );

		return( mysqli_fetch_assoc( $r ) );
	}

	function getuserbyid( $uid ) {
		$r = sql( '
			SELECT tbuser.* 
			FROM tbuser 
			INNER JOIN tbcompany
				ON tbuser.company = tbcompany.id
			WHERE tbuser.uid = "'.$uid.'" 
				AND tbuser.valid_from <= CURDATE() 
				AND ( tbuser.valid_to >= CURDATE() OR tbuser.valid_to = "0000-00-00" )
				AND tbcompany.valid_from <= CURDATE() 
				AND ( tbcompany.valid_to >= CURDATE() OR tbcompany.valid_to = "0000-00-00" )
			;' );

		return( mysqli_fetch_assoc( $r ) );
	}

?>