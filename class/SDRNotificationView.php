<?php

define('SDR_NOTIFICATION_ERROR',   9);
define('SDR_NOTIFICATION_WARNING', 8);
define('SDR_NOTIFICATION_SUCCESS', 7);

PHPWS_Core::initModClass('sdr', 'exception/CreateMembershipException.php');
PHPWS_Core::initModClass('sdr', 'exception/NoMemberFoundException.php');

class SDRNotificationView
{
	private $notifications = array();
	
	public function popNotifications()
	{
		$this->notifications = NQ::popAll('sdr');
	}
	
	public function show()
	{
		if(empty($this->notifications)) {
			return '';
		}
		
		$tpl = array();
		$tpl['NOTIFICATIONS'] = array();
		foreach($this->notifications as $notification) {
		    
			if(!$notification instanceof Notification) {
				throw new InvalidArgumentException('Something was pushed onto the NQ that was not a Notification.');
			}
			$type = self::resolveType($notification);
			$tpl['NOTIFICATIONS'][][$type] = $notification->toString();
		}
		
		return PHPWS_Template::process($tpl, 'sdr', 'NotificationView.tpl');
	}
	
	protected function resolveType(Notification $notification)
	{
		switch($notification->getType()) {
			case SDR_NOTIFICATION_ERROR:
				return 'ERROR';
			case SDR_NOTIFICATION_WARNING:
				return 'WARNING';
			case SDR_NOTIFICATION_SUCCESS:
				return 'SUCCESS';
			default:
				return 'UNKNOWN';
		}
	}
}
