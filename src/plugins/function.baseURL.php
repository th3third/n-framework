<?php
	
	function smarty_function_baseURL($params, Smarty_Internal_Template $template)
	{
		$extraParams = !empty($params['params']) ? $params['params'] : array();

		if (Config::get("XDEBUG_PROFILE_MODE"))
		{
			$extraParams["XDEBUG_PROFILE_MODE"] = true;
		}
		
		return ActionRegistry::getActionURL($params['action'], $extraParams);
	}