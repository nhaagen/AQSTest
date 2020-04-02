<?php declare(strict_types=1);
 
//include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

class ilAQSTestPlugin extends ilRepositoryObjectPlugin
{
	const ID = "xaqs";
	const PLUGIN_NAME = "AQSTest";
 
	function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
 
	protected function uninstallCustom() {}
}
?>