<?php declare(strict_types=1);
//include_once("./Services/Repository/classes/class.ilObjectPluginAccess.php");

class ilObjAQSTestAccess extends ilObjectPluginAccess
{
 
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		global $ilUser, $ilAccess;
 
		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}
 
		switch ($a_permission)
		{
			case "read":
				if (!self::checkOnline($a_obj_id) &&
					!$ilAccess->checkAccessOfUser($a_user_id, "write", "", $a_ref_id))
				{
					return false;
				}
				break;
		}
 
		return true;
	}
 
	
	static function checkOnline($a_id)
	{
		return true;
	}
 
}
 
?>