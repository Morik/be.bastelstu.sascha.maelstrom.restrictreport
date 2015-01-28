<?php
namespace wcf\system\event\listener;
use wcf\data\moderation\queue\ModerationQueueList;
use wcf\system\event\IEventListener;
use wcf\system\exception\AJAXException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;
/**
 * Restricts the ability to report contents.
 * 
 * @author	Sascha Greuel, Sebastian Zimmer
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
		
		if ($actionName == 'prepareReport' || $actionName == 'report') {
			if (!WCF::getUser()->userID || WCF::getUser()->banned || WCF::getUser()->activationCode || WCF::getUser()->reactivationCode || !WCF::getSession()->getPermission('user.community.canReport')) {
				if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
					throw new AJAXException(WCF::getLanguage()->get('wcf.ajax.error.permissionDenied'), AJAXException::INSUFFICIENT_PERMISSIONS);
				}
				else {
					throw new PermissionDeniedException();
				}
				exit;
			}
			elseif(WCF::getSession()->getPermission('user.community.reportLimit') != -1){
				$moderationQueueList = new ModerationQueueList();
				$moderationQueueList->getConditionBuilder()->add("userID = ?", array(WCF::getUser()->userID));
				$moderationQueueList->getConditionBuilder()->add("time > ?", array((TIME_NOW-(24*60*60))));
				if($moderationQueueList->countObjects() > WCF::getSession()->getPermission('user.community.reportLimit')){
				if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
					throw new AJAXException('<p class="error">'.WCF::getLanguage()->get('wcf.global.error.reportLimitReached').'</p>', AJAXException::INSUFFICIENT_PERMISSIONS);
				}
				else {
					throw new NamedUserException(WCF::getLanguage()->get('wcf.global.error.reportLimitReached'));
				}
				exit;
				}
			}
		}
	}
}
