<?php

// URL to Microsoft Server ActiveSync
$url = 'https://'.$_SERVER['SERVER_NAME'].'/Microsoft-Server-ActiveSync';

include_once('lib/exceptions/exceptions.php');
include_once('lib/utils/utils.php');
include_once('lib/utils/compat.php');
include_once('lib/utils/timezoneutil.php');
include_once('lib/core/zpushdefs.php');
include_once('lib/core/stateobject.php');
include_once('lib/core/interprocessdata.php');
include_once('lib/core/pingtracking.php');
include_once('lib/core/topcollector.php');
include_once('lib/core/loopdetection.php');
include_once('lib/core/asdevice.php');
include_once('lib/core/statemanager.php');
include_once('lib/core/devicemanager.php');
include_once('lib/core/zpush.php');
include_once('lib/core/zlog.php');
include_once('lib/core/paddingfilter.php');
include_once('config.php');

$data = file_get_contents('php://input');
preg_match('/\<EMailAddress\>(.*?)(@.*)?\<\/EMailAddress\>/', $data, $email);
preg_match('/\<AcceptableResponseSchema\>(.*?)\<\/AcceptableResponseSchema\>/', $data, $schema);

ob_start();
echo '<?xml version="1.0" encoding="utf-8"?>';

if (!isset($email) || !isset($schema)) {
	ZLog::Write(LOGLEVEL_ERROR, 'AutoDiscover :: Unsupported Request: '.$data);
?>
<Autodiscover xmlns:autodiscover="http://schemas.microsoft.com/exchange/autodiscover/mobilesync/responseschema/2006">
	<autodiscover:Response>
		<autodiscover:Error Time="<?php echo date('H:i:s.u'); ?>" Id="1054084152">
			<autodiscover:ErrorCode>600</autodiscover:ErrorCode>
			<autodiscover:Message>Invalid Request</autodiscover:Message>
			<autodiscover:DebugData />
		</autodiscover:Error>
		</autodiscover:Response>
	<Autodiscover>
<?php
	exit;
}

ZLog::Write(LOGLEVEL_DEBUG, 'AutoDiscover :: Request: '.$data);
ZLog::Write(LOGLEVEL_INFO, 'AutoDiscover :: Request by email: '.$email[0]);
ZLog::Write(LOGLEVEL_DEBUG, 'AutoDiscover :: Acceptable Response Schema: '.$schema[0]);

switch($schema[0]) {
	case 'http://schemas.microsoft.com/exchange/autodiscover/mobilesync/responseschema/2006': 
?>
<Autodiscover xmlns:autodiscover="http://schemas.microsoft.com/exchange/autodiscover/mobilesync/responseschema/2006">
	<autodiscover:Response>
		<autodiscover:Culture>en:us</autodiscover:Culture>
		<autodiscover:User>
			<autodiscover:DisplayName><?php echo $email[1]; ?></autodiscover:DisplayName>
			<autodiscover:EMailAddress><?php echo $email[0]; ?></autodiscover:EMailAddress>
		</autodiscover:User>
		<autodiscover:Action>
			<autodiscover:Settings>
				<autodiscover:Server>
					<autodiscover:Type>MobileSync</autodiscover:Type>
					<autodiscover:Url><?php echo $url; ?></autodiscover:Url>
					<autodiscover:Name><?php echo $url; ?></autodiscover:Name>
				</autodiscover:Server>
			</autodiscover:Settings>
		</autodiscover:Action>
	</autodiscover:Response>
</Autodiscover>
<?php
	ZLog::Write(LOGLEVEL_INFO, 'AutoDiscover :: Successful!');
	break;
	default:
?>
<Autodiscover xmlns:autodiscover="http://schemas.microsoft.com/exchange/autodiscover/mobilesync/responseschema/2006">
	<autodiscover:Response>
		<autodiscover:Error Time="<?php echo date('H:i:s.u'); ?>" Id="1054084152">
			<autodiscover:ErrorCode>601</autodiscover:ErrorCode>
			<autodiscover:Message>Invalid Request</autodiscover:Message>
			<autodiscover:DebugData />
		</autodiscover:Error>
	</autodiscover:Response>
<Autodiscover>
<?php
	ZLog::Write(LOGLEVEL_WARN, 'AutoDiscover :: Unsupported Schema: '.$schema[0]);
	break;
}
ZLog::Write(LOGLEVEL_DEBUG, 'AutoDiscover :: Response: '.ob_get_contents());
?>