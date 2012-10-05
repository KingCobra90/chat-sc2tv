<?php
/**
 * вспомогательные функции
*/

if ( LOG_ERRORS ) {
	set_error_handler( 'ChatErrorHandler' );
}


function ChatErrorHandler( $errno = '', $errstr = '', $errfile = '', $errline = ''  ) {
	$logFile = fopen( ERROR_FILE, 'a' );
	
	if ( flock( $logFile, LOCK_EX | LOCK_NB ) ) {
		$serverInfo = var_export( $_SERVER, true );
		
		$out = date( 'd M H:i:s', CURRENT_TIME ).' - ip '.$_SERVER[ 'REMOTE_ADDR' ]
			.' - ref '.$_SERVER[ 'HTTP_REFERER' ]."\n $errfile, line $errline, code $errno, $errstr\n\n$serverInfo\n\n";
		
		if ( count( $_POST ) ) {
			$out .= var_export( $_POST, true ) . "\n";
		}
		
		$jsonError = json_last_error();
		
		if ( $jsonError ) {
			switch ( $jsonError ) {
				case JSON_ERROR_NONE:
					$out .= 'json error: - Ошибок нет';
				break;
				case JSON_ERROR_DEPTH:
					$out .= 'json error: - Достигнута максимальная глубина стека';
				break;
				case JSON_ERROR_STATE_MISMATCH:
					$out .= 'json error: - Некорректные разряды или не совпадение режимов';
				break;
				case JSON_ERROR_CTRL_CHAR:
					$out .= 'json error: - Некорректный управляющий символ';
				break;
				case JSON_ERROR_SYNTAX:
					$out .= 'json error: - Синтаксическая ошибка, не корректный JSON';
				break;
				case JSON_ERROR_UTF8:
					$out .= 'json error: - Некорректные символы UTF-8, возможно неверная кодировка';
				break;
				default:
					$out .= 'json error: - Неизвестная ошибка';
				break;
			}
		}
		
		fwrite( $logFile, $out );
		fflush( $logFile );
		flock( $logFile, LOCK_UN );
	}
	
	fclose( $logFile );
	
	return false;
}


function SaveForDebug( $str ) {
	$logFile = fopen( DEBUG_FILE, 'a' );
	
	if ( flock( $logFile, LOCK_EX | LOCK_NB ) ) {
		$serverInfo = var_export( $_SERVER, true );
		
		$str = date( 'd M H:i:s', CURRENT_TIME )
			. ' - '. $_SERVER[ 'REMOTE_ADDR' ]
			. ' - ' . $_SERVER[ 'HTTP_USER_AGENT' ]
			. ' - ref '. $_SERVER[ 'HTTP_REFERER' ]
			. "\n, debug: $str\n\n$serverInfo\n\n";
		
		if ( count( $_POST ) ) {
			$str .= "\n" . var_export( $_POST, true ). "\n";
		}
		
		fwrite( $logFile, $str. "\n\n" );
		fflush( $logFile );
		flock( $logFile, LOCK_UN ); 
	}
	
	fclose( $logFile );
}

/**
 *	генерация токена
 *	@param string $str
 *	@return string 
 */
function GenerateSecurityToken( $str ) {
	// способ хэширования взят из форума и модуля Drupal vbbridge
	$salt = GenerateSalt();
	$token = md5( md5( $str ) . $salt );
	
	return $token;
}


/**
 * генерация соли
 * @param int $length длина строки
 * @return string
 */
function GenerateSalt( $length = 3 ) {
	$salt = '';
	
	for ( $i = 0; $i < $length; $i++ ) {
		$salt .= chr( rand( 32, 127 ) );
	}
	
	return $salt;
}
?>