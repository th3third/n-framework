<?php
	
	function smarty_function_sortCol($params, Smarty_Internal_Template $template)
	{
		$get = $_GET;
		$name = $params["name"];
		$col = $params["col"];
		$default = isset($params["desc"]) ? $params["desc"] : true;
		$isBeingSorted = isset($get["order"]) && $get["order"] == $col;

		$get["order"] = $col;

		if ($isBeingSorted)
		{
			$desc = isset($get["desc"]) ? !$get["desc"] : $default;
			$arrow = $desc == 1 ? "<span class='octicon octicon-triangle-up'></span>" : "<span class='octicon octicon-triangle-down'></span>";
			$get["desc"] = $desc;
		}
		else
		{
			$desc = $default;
			$arrow = "";
			$get["desc"] = $desc;
		}

		$href = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) . "?" . http_build_query($get);

		$html = "
			<a href='$href'>$name $arrow</a>
		";

		return $html;
	}