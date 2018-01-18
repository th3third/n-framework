<?php
	
	function smarty_function_alphaLinks($params, Smarty_Internal_Template $template)
	{
		$chars = [
			"All" => ""
			, "#" => "#"
		];
		$chars += array_combine(range("A", "Z"), range("A", "Z"));
		$hostname = "//" . explode('?', $_SERVER['REQUEST_URI'], 2)[0];
		$get = $_GET;
		$activeChar = isset($get["search_char"]) ? $get["search_char"] : "";

		$html = '<ul class="paginate">';

		foreach ($chars as $char => $value)
		{
			$isActive = "";
			if ($activeChar == $value)
			{
				$isActive = " class='active'";
			}

			$get["search_char"] = $value;
			$href = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) . "?" . http_build_query($get);
			$html .= "<li$isActive><a href='$href'>$char</a></li>";
		}

		$html .= '</ul>';

		return $html;
	}