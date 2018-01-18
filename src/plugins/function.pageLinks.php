<?php
	
	function smarty_function_pageLinks($params, Smarty_Internal_Template $template)
	{
		$page = isset($params["page"]) ? $params["page"] : 1;
		$rows = $params["rows"];
		$perPage = isset($params["perPage"]) ? $params["perPage"] : 15;
		$limit = isset($params["limit"]) ? $params["limit"] : 6;

		if (empty($page))
		{
			$page = 1;
		}

		$max = ceil($rows / $perPage);

		if ($max <= 1)
		{
			return "";
		}

		$html = "<div class='paginateContainer'><ul class='paginate'>";

		$start = (($page - $limit) > 0) ? $page - $limit : 1;
	   	$end = (($page + $limit) < $max) ? $page + $limit : $max;

	   	$url = strtok($_SERVER["REQUEST_URI"],'?');

	   	//If there are any query vars set this will parse out the "page" variable since we don't want that duplicating over every page.
	   	if (isset($_SERVER['QUERY_STRING']))
	   	{
 		   	$parsed = array();
 		   	$toRemove = "page";
		    parse_str(substr($_SERVER['QUERY_STRING'], 0), $parsed);

		    unset($parsed[$toRemove]);
		    
		    if(!empty($parsed))
		    {
		        $url .= '?' . http_build_query($parsed);
		    }
		}

	    $class = ($page == 1) ? "disabled" : "";
	    $html .= '<li class="' . $class . '"><a href="' . $url . '&page=' . ($page - 1) . '">&laquo;</a></li>';
	 
	    if ($start > 1) 
	    {
	        $html .= '<li><a href="' . $url . '&page=1">1</a></li>';
	        if ((1 + ($limit + 2)) <= $page)
	    	{
		        $html .= '<li class="disabled"><span>...</span></li>';
		    }
	    }
	 
	    for ($i = $start; $i <= $end; $i++) 
	    {
	    	if ($page == $i)
	    	{
	    		$html .= '<li class="active">' . $i . '</li>';
	    	}
	    	else
	    	{
	    		$html .= '<li><a href="' . $url . '&page=' . $i . '">' . $i . '</a></li>';
	    	}
	    }

	    if ($page < $end && $end > $limit && ($max - $limit) > $page) 
	    {
	    	if (($max - ($limit + 1)) > $page)
	    	{
	        $html .= '<li class="disabled"><span>...</span></li>';
		    }
	        $html .= '<li><a href="' . $url . '&page=' . $max . '">' . $max . '</a></li>';
	    }
	 
	    $class = ($page == $max ) ? "disabled" : "";
	    $html .= '<li><a href="' . $url . '&page=' . ($page + 1) . '">&raquo;</a></li>';

	    $html .= "</ul>$rows results found</div>";

	    return $html;
	}