<?php

	function login( $username = false, $password = false ) {
		$r = sql( '
			SELECT tbuser.uid
			FROM tbuser 
			INNER JOIN tbcompany
				ON tbuser.company = tbcompany.id
			WHERE tbuser.username = "'.$username.'" 
				AND ( tbuser.password = "'.$password.'" OR tbuser.password = ENCRYPT( "'.$password.'", LEFT( tbuser.password, 2 ) ) )
				AND tbuser.valid_from <= CURDATE() 
				AND ( tbuser.valid_to >= CURDATE() OR tbuser.valid_to = "0000-00-00" )
				AND tbcompany.valid_from <= CURDATE() 
				AND ( tbcompany.valid_to >= CURDATE() OR tbcompany.valid_to = "0000-00-00" )
			;' );
			
		if( list( $uid ) = mysqli_fetch_row( $r ) ) {
			# do the login
			sql( 'UPDATE tbuser SET login_time = CURDATE() WHERE uid = "'.$uid.'";' );
			return( $uid );

		} else return false;
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