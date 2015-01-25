<?php
namespace wcf\system\event\listener;
use wcf\system\event\IEventListener;
use wcf\system\exception\AJAXException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Restricts the ability to report contents.
 * 
 * @author	Sascha Greuel
 * @copyright	2014 Sascha Greuel
 * @license	Creative Commons Attribution-NoDerivatives <http://creativecommons.org/licenses/by-nd/4.0/legalcode>
 * @package	be.bastelstu.sascha.maelstrom.restrictreport
 * @subpackage	system.event.listener
 * @category	Community Framework
 */
class ReportRestrictListener implements IEventListener {
	/**
	 * @see	\wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		$actionName = $eventObj->getActionName();
		
		if (!WCF::getUser()->userID || WCF::getUser()->banned || WCF::getUser()->activationCode || WCF::getUser()->reactivationCode || !WCF::getSession()->getPermission('user.community.canReport')) {
			if ($actionName == 'prepareReport' || $actionName == 'report') {
				if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
					throw new AJAXException(WCF::getLanguage()->get('wcf.ajax.error.permissionDenied'), AJAXException::INSUFFICIENT_PERMISSIONS);
				}
				else {
					throw new PermissionDeniedException();
				}
					
				exit;
			}
		}
	}
}
