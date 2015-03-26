<?php
	define( EMS_HOST, "121.78.114.35" );
	define( EMS_USER, "mmss_user" );
	define( EMS_PASS, "ehsqjfdj!" );
	define( EMS_DBNAME, "ems" );
	define( EMS_USE_UNITCODE, true ); // 변경시 현재 페이지 인코딩도 변경하길 바람.

	define( CAMPAIN_STATUS_RESERVE, "R" );
	define( CAMPAIN_STATUS_WAIT, "W" );
	define( CAMPAIN_STATUS_SENDING, "S" );
	define( CAMPAIN_STATUS_COMPLETE, "C" );

	$CAMPAIN_STATUS_TEXT = array( CAMPAIN_STATUS_RESERVE => "예약중",
					CAMPAIN_STATUS_WAIT => "발송대기중",
					CAMPAIN_STATUS_SENDING => "발송중",
					CAMPAIN_STATUS_COMPLETE => "발송완료" );

	class NewEms
	{
		var $mDB;
		var $target_list = array();
		var $subject = false;
		var $body = false;

		public function NewEms()
		{
			$this->mDB = false;
			register_shutdown_function(array(&$this, "__destruct"));
		}

		public function __construct()
		{
			$this->mDB = false;
		}

		public function __destruct()
		{
			$this->close();
			unset($this);
			return true;
		}

		public function connect()
		{
			$this->mDB = mysql_connect( EMS_HOST, EMS_USER, EMS_PASS );
			if( !$this->mDB ) return false;

			if( !mysql_select_db( EMS_DBNAME, $this->mDB ) ) {
				$this->close();
				return false;
			}

			if( EMS_USE_UNITCODE )
			{
				mysql_query( "SET collation_connection = utf8_general_ci", $this->mDB );
				mysql_query( "SET NAMES utf8", $this->mDB );
			}
			else
			{
				mysql_query( "SET collation_connection = euckr_korean_ci", $this->mDB );
				mysql_query( "SET NAMES euckr", $this->mDB );
			}

			return true;
		}

		public function close()
		{
			if( $this->mDB )
			{
				mysql_close( $this->mDB );
				$this->mDB = false;
			}
		}

		public function _makeKey()
		{
			srand((double) microtime() * 1000000);
			$keystr = sprintf("%s_%s_%s", getmypid(), time(), rand( 10000,99999 ) );

			return md5( $keystr );
		}

		public function addTarget( $pemail, $name = "" , $personal = "" )
		{
			$email = trim( $pemail );
			if( $email == "" || !is_string( $email) ) return false;
			if( !eregi("^[a-z0-9_-]+[a-z0-9_.-]*@[a-z0-9_-]+[a-z0-9_.-]*\.[a-z]{2,5}$", $email) ) return false;

			$targets = array( "email" => strtolower( trim($email) ), "name" => trim($name), "personal" => trim($personal)  );
			array_push( $this->target_list , $targets );

			return true;
		}

		public function setMail( $subject, $body, $attach_path = "" /* reserve item */ )
		{
			if( !isset($subject) || trim($subject) == "" ) return false;
			if( !isset($body) || trim($body) == "" ) return false;

			$this->subject = trim($subject);
			$this->body = trim($body);

			return true;
		}

		public function createCampain( $title, $sname, $semail , $is_personal = 0 )
		{
			if( count($this->target_list) <= 0 ) return false;
			if( !$this->mDB ) return false;

			$mailkey = $this->_makeKey();
			$c_query = sprintf("insert into Campain( CpIdx, CpTitle, SenderName, SenderEmail, DateReg, DateSend, MailTitle, MailBody, CpStep )
				values( '%s', '%s','%s','%s',now(),now(), '%s','%s', -2 )", $mailkey, $title, trim($sname), strtolower( trim($semail) ), $this->subject, str_replace("'","''",$this->body));

			if( !mysql_query( $c_query, $this->mDB ) )
			{
				echo $c_query;
				return false;
			}

			$err_flag = false;
			foreach( $this->target_list as $target )
			{
				$mail_item = explode("@",$target["email"] );
				$t_query = sprintf("insert into CpMail( CpIdx, ToName, ToId, ToDomain ) values('%s','%s','%s','%s')",
					$mailkey, $target["name"], $mail_item[0], $mail_item[1] );
				if( !mysql_query( $t_query, $this->mDB ) )
				{
					echo $t_query;
					$err_flag = true;
					break;
				}

				$mail_idx = mysql_insert_id( $this->mDB );
				if( $target["personal"] != "" )
				{
					$p_query = sprintf("insert into CpWstr( CpIdx, MailIdx, WideStr ) values ( '%s',%s,'%s')",
						$mailkey, $mail_idx  , $target["personal"] );
					if( !mysql_query( $p_query, $this->mDB ) )
					{
						echo $p_query;
						$err_flag = true;
						break;
					}
				}
			}

			if( $err_flag )
			{
				mysql_query(sprintf("delete from Campain where CpIdx = '%s'",$mailkey ), $this->mDB );
				mysql_query(sprintf("delete from CpMail where CpIdx = '%s'",$mailkey ), $this->mDB );
				mysql_query(sprintf("delete from CpWstr where CpIdx = '%s'",$mailkey ), $this->mDB );
				return false;
			}

			$u_query = sprintf("update Campain SET CpStep = -1 where CpIdx = '%s'",$mailkey );
			if( !mysql_query( $u_query, $this->mDB ) )
			{
				mysql_query(sprintf("delete from Campain where CpIdx = '%s'",$mailkey ), $this->mDB );
				mysql_query(sprintf("delete from CpMail where CpIdx = '%s'",$mailkey ), $this->mDB );
				mysql_query(sprintf("delete from CpWstr where CpIdx = '%s'",$mailkey ), $this->mDB );
				return false;
			}

			return $mailkey;
		}

		public function getCampainStatus( $mailkey )
		{
			$c_query = sprintf("select IF( DateSend > now(), -2, CpStep ) as step from Campain where CpIdx = '%s'",$mailkey );
			$res = mysql_query($c_query, $this->mDB );
			if( !$res ) return false;
			$row = mysql_fetch_row( $res );
			if( !$row ) return false;

			$ret_code = false;

			switch( $row[0] )
			{
			case "-2":
				// 예약중
				$ret_code = CAMPAIN_STATUS_RESERVE;
				break;
			case "-1":
				// 발송대기
				$ret_code = CAMPAIN_STATUS_WAIT;
				break;
			case "0":
			case "1":
				// 발송중
				$ret_code = CAMPAIN_STATUS_SENDING;
				break;
			case "2":
				// 발송완료
				$ret_code = CAMPAIN_STATUS_COMPLETE;
				break;
			default:
				break;
			}

			return $ret_code;
		}

		public function getCampainStatusText( $status_code )
		{
			global $CAMPAIN_STATUS_TEXT;
			$result = "";

			if( !isset( $CAMPAIN_STATUS_TEXT[ $status_code ] ) ) $result = "정의되지 않은 상태";
			else $result = $CAMPAIN_STATUS_TEXT[ $status_code ];

			if( EMS_USE_UNITCODE )
			{
				return iconv("euckr","utf8",$result);
			}
			else
			{
				return $result;
			}

		}

		public function _makeMailResult( $row )
		{
			$result_array = array(
				"email" => $row["ToId"]."@".$row["ToDomain"],
				"senddate" => $row["DateSend"],
				"smtp_stemp" => $row["SmtpStep"],
				"smtp_code" => $row["SmtpCode"],
				"try_count" => $row["TryCnt"],
				"explain" => $row["Explain"],
				"status" => $row["ResultCode"]
				);

			return $result_array;
		}

		public function getMailStatus( $mailkey, $mailidx )
		{
			$m_query = sprintf("select ToId, ToDomain, DateSend, a.SmtpStep, a.SmtpCode, a.TryCnt, IFNULL( b.Explain, '' ) as `Explain`, IFNULL( ResultCode, IF(a.SmtpStep=7,121,0) ) as `ResultCode`
				from CpMail a
				LEFT JOIN CodeExp b ON a.CpIdx = b.CpIdx AND a.MailIdx = b.MailIdx AND a.TryCnt = b.TryCnt
				where a.CpIdx = '%s' AND a.MailIdx = %s", $mailkey, $mailidx );

			$res = mysql_query($m_query, $this->mDB );
			if( !$res ) return false;
			$row = mysql_fetch_array( $res );
			if( !$row ) return false;

			return $this->_makeMailResult( $row );
		}

		public function getMailStatusByEmail( $mailkey, $email )
		{
			if( $email == "" || !is_string( $email) ) return false;
			if( !eregi("^[a-z0-9_-]+[a-z0-9_.-]*@[a-z0-9_-]+[a-z0-9_.-]*\.[a-z]{2,5}$", $email) ) return false;

			$mail_item = explode("@",$email );

			$m_query = sprintf("select ToId, ToDomain, DateSend, a.SmtpStep, a.SmtpCode, a.TryCnt, IFNULL( b.Explain, '' ) as `Explain` , IFNULL( ResultCode, IF(a.SmtpStep=7,121,0) ) as `ResultCode`
				from CpMail a
				LEFT JOIN CodeExp b ON a.CpIdx = b.CpIdx AND a.MailIdx = b.MailIdx AND a.TryCnt = b.TryCnt
				where a.CpIdx = '%s' AND ToId = '%s' AND ToDomain = '%s'", $mailkey, $mail_item[0], $mail_item[1] );

			$res = mysql_query($m_query, $this->mDB );
			if( !$res ) return false;
			$row = mysql_fetch_array( $res );
			if( !$row ) return false;


			return $this->_makeMailResult( $row );
		}

		public function getMailStatusAll( $mailkey )
		{
			$m_query = sprintf("select ToId, ToDomain, DateSend, a.SmtpStep, a.SmtpCode, a.TryCnt, IFNULL( b.Explain, '' ) as `Explain`, IFNULL( ResultCode, IF(a.SmtpStep=7,121,0) ) as `ResultCode`
				from CpMail a
				LEFT JOIN CodeExp b ON a.CpIdx = b.CpIdx AND a.MailIdx = b.MailIdx AND a.TryCnt = b.TryCnt
				where a.CpIdx = '%s'", $mailkey );

			$res = mysql_query($m_query, $this->mDB );
			if( !$res ) return false;

			$result_array = array();
			while( $row = mysql_fetch_array( $res ) )
			{
				//print_r( $row );
				array_push( $result_array, $this->_makeMailResult( $row ) );
			}

			return $result_array;
		}

		public function getTotalCount( $mailkey )
		{
			$m_query = sprintf("select count(*) as cnt  from CpMail where CpIdx = '%s'", $mailkey );
			$res = mysql_query($m_query, $this->mDB );
			if( !$res ) return 0;

			$row = mysql_fetch_array( $res );
			if( !$row ) return 0;

			return $row["cnt"];
		}

		public function getSuccessCount( $mailkey )
		{
			$m_query = sprintf("select count(*) as cnt  from CpMail where SmtpStep = 7 and CpIdx = '%s'", $mailkey );
			$res = mysql_query($m_query, $this->mDB );
			if( !$res ) return 0;

			$row = mysql_fetch_array( $res );
			if( !$row ) return 0;

			return $row["cnt"];
		}

		public function getRemainCount( $mailkey )
		{
			$m_query = sprintf("select count(*) as cnt
				from CpMail a
				LEFT JOIN CodeExp b ON a.CpIdx = b.CpIdx AND a.MailIdx = b.MailIdx AND a.TryCnt = b.TryCnt
				where a.SmtpStep in( 0, -1 ) and b.ResultCode is null and a.CpIdx = '%s'", $mailkey );
			// echo $m_query;
			$res = mysql_query($m_query, $this->mDB );
			if( !$res ) return 0;

			$row = mysql_fetch_array( $res );
			if( !$row ) return 0;

			return $row["cnt"];
		}

		public function getErrorCount( $mailkey )
		{
			$m_query = sprintf("select count(*) as cnt  from CpMail where SmtpStep not in( 0,-1,7) and CpIdx = '%s'", $mailkey );
			$res = mysql_query($m_query, $this->mDB );
			if( !$res ) return 0;

			$row = mysql_fetch_array( $res );
			if( !$row ) return 0;

			return $row["cnt"];
		}


	}
?>