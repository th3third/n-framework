<?php

	class Collection extends ArrayObject
	{
		public function isEmpty()
		{
			return $this->count() == 0;
		}

		public function first()
		{
			return $this[0];
		}

		public function last()
		{
			return $this[$this->count() - 1];
		}

		public function prepend($item)
		{
			$array = (array)$this;
	        array_unshift($array, $item);
	        $this->exchangeArray($array);

	        return $this;
		}

		public function remove($item)
		{
			$key = array_search($item, $this);

			return $this->offsetUnset($key);
		}

		public function slice($start, $length = null, $preserveKeys = false)
		{
			return array_slice($this->toArray(), $start, $length, $preserveKeys);
		}

		public function __toString()
	    {
	    	return get_class($this) . ": " . var_export($this, true);
	    }

	    public function toArray(Array $columns = [])
	    {
	    	if (!empty($columns))
	    	{
	    		$result = [];
	    		foreach ($this as $item)
	    		{
	    			$new = [];
	    			foreach ($columns as $column)
	    			{
	    				$new[$column] = $item[$column];
	    			}

	    			$result[] = $new;
	    		}

	    		return $result;
	    	}

	    	return $this->getArrayCopy();
	    }

	    public function offsetGet($index)
	    {
	    	if ($this->count() == 0)
	    	{
	    		return null;
	    	}

	    	return parent::offsetGet($index);
	    }

	    public function reverse($preserveKeys = false)
	    {
	    	$this->exchangeArray(array_reverse($this->toArray(), $preserveKeys));

	    	return $this;
	    }

	    public function offsetUnset($index)
		{
			return parent::offsetUnset($index);
		}

	    /**
	    * @method find
	    * @param {String} column
	    * @param {String} value
	    * @return {Mixed}
	    */
	    public function find($column, $value)
	    {
	    	$instance = $this->filterBy($column, $value);

	    	return $instance->first();
	    }

	    /**
	    * @method removeDuplicates
	    * @param {String} column
	    * @return {Collection}
	    */
	    public function removeDuplicates()
	    {
	    	// Unique Array for return
		    $arrayRewrite = [];
		    // Array with the md5 hashes
		    $arrayHashes = [];
		    foreach($this as $key => $item) 
		    {
		        // Serialize the current element and create a md5 hash
		        $hash = md5(serialize($item));
		        // If the md5 didn't come up yet, add the element to
		        // to arrayRewrite, otherwise drop it
		        if (!isset($arrayHashes[$hash])) 
		        {
		            // Save the current element hash
		            $arrayHashes[$hash] = true;
		            $arrayRewrite[] = $item;
		        }
		    }

		    $this->exchangeArray($arrayRewrite);

		    return $this;
	    }

	    /**
	    * @method filterBy
	    * @param {String} column
	    * @param {String} value
	    * @return {Collection}
	    */
	    public function filterBy($column, $value, $wild = false)
	    {
	    	$instance = new Collection($this);

	    	$results = array();

			//Go through all of the items we have in the collection.
			foreach ($instance as $key => $item)
			{
				if ($wild)
				{
					if (isset($item[$column]) && stripos($item[$column], $value) !== false)
					{
						$results[$key] = $item[$column];
					}
				}
				else
				{
					if (isset($item[$column]) && $item[$column] == $value)
					{
						$results[$key] = $item[$column];
					}
				}
			}

			foreach (array_keys($results) as $key)
			{
				$results[$key] = $instance[$key];
			}
			
			$instance->exchangeArray(array_values($results));

			return $instance;
	    }

	    /**
	    * @method filterByClosure
	    * @param {Closure} callback
	    * @param {String} column
	    * @return {Collection}
	    */
	    public function filterByClosure(Closure $callback, $column, $params = null)
	    {
	    	$instance = new Collection($this);

	    	$results = array();

			//Go through all of the items we have in the collection.
			foreach ($instance as $key => $item)
			{
				if (isset($item[$column]) && $callback($item[$column], $params) == true)
				{
					$results[$key] = $item[$column];
				}
			}

			foreach (array_keys($results) as $key)
			{
				$results[$key] = $instance[$key];
			}
			
			$instance->exchangeArray(array_values($results));

			return $instance;
	    }

	    /**
		* @method unique
		* @param {String} column
		* @return {Collection}
		*/
		public function unique($column)
		{
			$instance = new Collection($this);

			$results = [];

			//Go through all of the items we have in the collection.
			foreach ($instance as $key => $item)
			{
				if (!array_key_exists($item[$column], $results))
				{
					$results[$item[$column]] = $item;
				}
			}

			$instance->exchangeArray(array_values($results));

			return $instance;
		}

		/**
		* @method sortBy
		* @param {String} callback
		* @param {String} options; default SORT_REGULAR
		* @param {String} descending; default false
		* @return {Collection}
		*/
		public function sortBy($callback, $options = SORT_NATURAL | SORT_FLAG_CASE, $descending = false)
		{
			$results = array();

			//Go through all of the items we have in the collection.
			foreach ($this as $key => $item)
			{
				$results[$key] = method_exists($item, $callback) ? $item->$callback : $item[$callback];
			}

			$descending ? arsort($results, $options) : asort($results, $options);

			foreach (array_keys($results) as $key)
			{
				$results[$key] = $this[$key];
			}

			$this->exchangeArray(array_values($results));

			unset($results);

			return $this;
		}

		/**
		* @method combine
		* @param {Collection}
		* @param {String} unique The column to use to determine unique entries. If false, all entries will be combined.
		* @return {Collection}
		*/
		public function combine(Collection $collection, $unique = false)
		{
			foreach ($collection as $item)
			{
				if (!empty($unique))
				{
					if (!$this->find($unique, $item->$unique))
					{
						$this->append($item);
					}
				}
				else
				{
					$this->append($item);
				}
			}

			return $this;
		}

		/**
		* @method intersect
		* @param {Collection} intersection
		* @param {String} column
		* @return {Collection}
		*/
		public function intersect(Collection $intersection, $column)
		{
			$result = new Collection;
			$values = [];
			foreach ($intersection as $key => $value)
			{
				$values[$value->$column] = $value->$column;
			}

			foreach ($this as $value)
			{
				if (in_array($value->$column, $values))
				{
					$result->append($value);
				}
			}

			return $result;	
		}

		/**
		* @method difference
		* @param {Collection} difference
		* @param {String} column
		* @return {Collection}
		*/
		public function difference(Collection $difference, $column)
		{
			$result = new Collection;
			$values = [];
			foreach ($difference as $key => $value)
			{
				$values[$value->$column] = $value->$column;
			}

			foreach ($this as $value)
			{
				if (!in_array($value->$column, $values))
				{
					$result->append($value);
				}
			}

			return $result;	
		}

		/**
		* @method call
		* @param {String} function
		* @param {Mixed} argument
		* @return {Collection}
		*/
		public function call($function, $argument = null)
		{
			foreach ($this as $element)
			{
				$element->$function($argument);
			}

			return $this;
		}
	}