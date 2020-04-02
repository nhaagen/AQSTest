<?php declare(strict_types=1);

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");
require_once("./Services/Tracking/interfaces/interface.ilLPStatusPlugin.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/AQSTest/classes/class.ilObjAQSTestGUI.php");
/*
*/

class ilObjAQSTest extends ilObjectPlugin implements ilLPStatusPluginInterface
{
	function __construct($a_ref_id = 0)
	{
		parent::__construct($a_ref_id);
	}
 
	final function initType()
	{
		$this->setType(ilAQSTestPlugin::ID);
	}
 
	function doCreate()
	{
	}
 
	function doRead()
	{
	}
 
	function doUpdate()
	{
	}
 
	function doDelete()
	{
	}
 
	function doClone($a_target_id, $a_copy_id, $new_obj)
	{
	}
 
	function setOnline($a_val)
	{
		$this->online = $a_val;
	}
 
	function isOnline()
	{
		return $this->online;
	}
 
	/**
	 * Get all user ids with LP status completed
	 *
	 * @return array
	 */
	public function getLPCompleted() {
		return array();
	}
 
	/**
	 * Get all user ids with LP status not attempted
	 *
	 * @return array
	 */
	public function getLPNotAttempted() {
		return array();
	}
 
	/**
	 * Get all user ids with LP status failed
	 *
	 * @return array
	 */
	public function getLPFailed() {
		return array();
	}
 
	/**
	 * Get all user ids with LP status in progress
	 *
	 * @return array
	 */
	public function getLPInProgress() {
		return array();
	}
 
	/**
	 * Get current status for given user
	 *
	 * @param int $a_user_id
	 * @return int
	 */
	public function getLPStatusForUser($a_user_id) {
		global $ilUser;
		if($ilUser->getId() == $a_user_id)
			return $_SESSION[ilObjAQSTestGUI::LP_SESSION_ID];
		else
			return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
	}
}
?>