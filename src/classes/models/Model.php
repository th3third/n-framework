<?php

	use Carbon\Carbon;

	/**
	* Used as a base model class.
	* @class Model
	*/

	Class Model implements ArrayAccess, Iterator
	{
		protected $tableName = null;
		protected $primaryKey = null;
		protected $primaryKeyNotInt = false;
		protected $lastInsertID = null;
		protected $timestamps = false;
		protected $softDelete = false;
		protected $withDeleted = false;
		protected $restricted = false;
		protected $timeCreatedColumn = "CreatedAt";
		protected $timeUpdatedColumn = "UpdatedAt";
		protected $timeDeletedColumn = "DeletedAt";
		protected $columns = null;
		protected $query = "";
		protected $params = [];
		protected $selects = [];
		protected $groups = [];
		protected $wheres = [];
		protected $between = [];
		protected $nests = [];
		protected $havings = [];
		protected $orders = [];
		protected $joins = [];
		protected $start = 1;
		protected $limit = null;
		protected $autocommit = true;
		protected $data = [];
		protected $changed = [];
		protected $position = 0;
		protected $distinct = false;
		protected $has;
		protected $db;
		protected $logger;
		protected $defaults = [];
		protected $required = [];
		protected $validations = [];
		protected $filters = [];
		protected $preprocess = [];
		protected $dates = [
			"CreatedAt"
			, "UpdatedAt"
			, "DeletedAt"
		];
		protected $cache = array();
		protected $session;
		public $errors;
		
		public function __construct(&$db, Logger &$logger, UserSession &$session = null)
		{
			$this->db = $db;
			$this->logger = $logger;
			$this->session = $session;

			if (is_null($this->tableName))
			{
				$this->tableName = strtolower(get_class($this)) . 's';
			}

			if (is_null($this->primaryKey))
			{
				$this->primaryKey = get_class($this) . "Id";
			}

			if ($this->timestamps)
			{
				$this->dates[] = $this->timeCreatedColumn;
				$this->dates[] = $this->timeUpdatedColumm;

				if ($this->softDelete)
				{
					$this->dates[] = $this->timeDeletedColumn;
				}
			}
		}

		public function instance()
		{
			return $this->build(get_class($this));
		}

		public function build($type = "Model")
		{
			$instance = new $type($this->db, $this->logger, $this->session);

			return $instance;
		}

		public function resetChanged()
		{
			$this->changed = [];

			return $this;
		}

		public function getTableName()
		{
			return $this->tableName;
		}

		public function getPrimaryKey()
		{
			if (is_array($this->primaryKey))
			{
				$keys = array();
				foreach ($this->primaryKey as $key)
				{
					$keys[$key] = $this->$key;
				}

				return $keys;
			}
			else
			{
				$primaryKey = $this->primaryKey;
				
				return $this->$primaryKey;
			}
		}

		protected function setPrimaryKey($value)
		{
			$primaryKey = $this->primaryKey;
			$this->$primaryKey = $value;

			return $this;
		}

		public function getPrimaryKeyName()
		{
			return $this->primaryKey;
		}

		function rewind() 
		{
	        $this->position = 0;
	    }

	    function current() 
	    {
	        return $this->data[$this->position];
	    }

	    function key() 
	    {
	        return $this->position;
	    }

	    function next() 
	    {
	        ++$this->position;
	    }

	    function valid() 
	    {
	        return isset($this->data[$this->position]);
	    }

		public function offsetSet($offset, $value) 
		{
	        if (is_null($offset)) 
	        {
	            $this->data[] = $value;
	        } 
	        else 
	        {
	            $this->data[$offset] = $value;
	        }
	    }

	    public function offsetExists($offset) 
	    {
	        return isset($this->data[$offset]);
	    }

	    public function offsetUnset($offset) 
	    {
	        unset($this->data[$offset]);
	    }

	    public function offsetGet($offset) 
	    {
	        return isset($this->data[$offset]) ? $this->data[$offset] : null;
	    }

	    public function __toString()
	    {
	    	return get_class($this) . ": " . var_export($this->data, true);
	    }

	    /**
	    * @method cache
	    * @param {String} key
	    * @return {Mixed}
	    */
	    public function cache($key)
	    {
	    	return $this->cache[$key];
	    }

	    /**
	    * @method cacheHas
	    * @param {String} key
	    * @return {Boolean}
	    */
	    public function cacheHas($key)
	    {
	    	return isset($this->cache[$key]);
	    }

	    /**
	    * @method cacheSet
	    * @param {String} key
	    * @param {Mixed} value
	    */
	    public function cacheSet($key, $value)
	    {
	    	$this->cache[$key] = $value;
	    }

	    /**
	    * @method cacheClear
	    * @param {String} key
	    */
	    public function cacheClear($key)
	    {
	    	unset($this->cache[$key]);
	    }

	    /**
		* @method set
		* @param {Array} settings
		* @return {ShopVendor}
		*/
		public function set($settings, $value = null)
		{
			if (!is_array($settings))
			{
				$settings = [$settings => $value];
			}

			foreach ($settings as $key => $value)
			{
				$this->$key = $value;
			}

			return $this;
		}

		public function __set($name, $value)
	    {
	    	$processed = $this->preprocess([$name => $value]);
	    	$value = array_pop($processed);

	    	$value = is_string($value) ? $value : $this->castToString($value); //we set this as a string because they'll always be returned from the DB as a string, so we want them to match up with the format.
	    	if (!isset($this->data[$name]) || $this->data[$name] !== $value)
	    	{
	    		$this->data[$name] = $value;
	        	$this->changed[$name] = true;
	    	}
	    }

	    public function __get($name)
	    {
	    	if (method_exists($this, $name))
	        {
	        	return $this->$name();
	        }
	        elseif (array_key_exists($name, $this->data)) 
	        {
	        	if (!empty($this->data[$name]) && in_array($name, $this->dates))
	        	{
	        		return new Carbon($this->data[$name]);
	        	}

	            return $this->data[$name];
	        }

	        return null;
	    }

	    public function __isset($name)
	    {
	        return isset($this->data[$name]);
	    }

	    public function __unset($name)
	    {
	        unset($this->data[$name]);
	    }

	    protected function convertToWindows1252($value)
	    {
	    	return iconv("UTF-8", "Windows-1252//TRANSLIT", $value);
	    }

		private function getColumnInfo()
		{
			if (!isset($this->columns))
			{
				$query = '
					SHOW 
						columns
					FROM
						!
				';
				$params = array(
					$this->tableName
				);
				
				$result = $this->db->getAll($query, $params);
				
				if (DB::isError($result))
				{
					throw new Exception($result->getDebugInfo());
				}

				$this->columns = array();
				foreach ($result as $column)
				{
					$this->columns[] = $column["Field"];
				}
			}

			return $this->columns;
		}

		private function checkColumnFields(Array $values)
		{
			return array_intersect_ukey($values, array_flip($this->getColumnInfo()), "strcasecmp");
		}

		private function setDataKeys($keys)
		{
			$this->data = array();

			foreach ($keys as $key)
			{
				$this->data[$key] = null;
			}

			return true;
		}

		public function setData($data)
		{
			$this->data = $data;
		}

		/**
		* @method clearQuery
		*/
		protected function clearQuery()
		{
			$this->query = "";
			$this->wheres = array();
		}

		/**
		* @method clearParams
		*/
		protected function clearParams()
		{
			$this->params = array();
		}

		/**
		* @method clearLimit
		*/
		protected function clearLimit()
		{
			$this->start = 1;
			$this->limit = null;

			return $this;
		}

		/**
		* @method buildQuery
		* @return {String}
		*/
		public function buildQuery()
		{
			if (!empty($this->selects))
			{
				$selects = implode($this->selects, ", ");
			}
			else
			{
				$selects = "*";
			}

			if ($this->distinct)
			{
				$query = "SELECT DISTINCT ";
			}
			else
			{
				$query = "SELECT ";
			}

			if (!empty($this->limit) && $this->limit > 1)
			{
				$query .= " 
					SQL_CALC_FOUND_ROWS
				";
			}

			$query .= "	
					$selects				
				FROM 
					$this->tableName
			";
			$params = [];

			$query .= $this->buildJoins($this->joins);

			if ($this->softDelete && !$this->withDeleted)
			{
				$this->where("(" . $this->tableName . "." . $this->timeDeletedColumn, "=", "'0000-00-00 00:00:00'", "and", true);
				$this->where($this->tableName . "." . $this->timeDeletedColumn, "IS", "NULL)", "or", true);
			}

			if (count($this->wheres) > 0)
			{
				$query .= " WHERE ";

				$i = 0;
				foreach ($this->wheres as $where)
				{
					if (isset($this->nests[$i]) && $this->nests[$i] == ")")
					{
						$query .= " " . $this->nests[$i] . " ";
					}

					if ($i > 0)
					{
						$query .= " " . $where["andor"] . " ";
					}

					if (isset($this->nests[$i]) && $this->nests[$i] == "(")
					{
						$query .= " " . $this->nests[$i] . " ";
					}

					if (is_array($where["value"]))
					{
						$inValues = '"' . implode('","', $where["value"]) . '"';
						$query .= " " . $where["column"] . " IN (" . $inValues . ")";
					}
					else if ($where["asis"])
					{
						$query .= " " . $where["column"] . " " . $where["operator"] . " " . $where["value"];
					}
					else
					{
						$query .= " " . $where["column"] . " " . $where["operator"] . " ?";
						$params[] = $where["value"];
					}

					$i++;
				}
			}

			if (isset($i) && isset($this->nests[$i]))
			{
				$query .= " " . $this->nests[$i] . " ";
			}

			if (!empty($this->groups))
			{
				$query .= " GROUP BY ";
				$query .= implode(",", $this->groups);
			}

			if (count($this->havings) > 0)
			{
				$query .= " HAVING ";

				$i = 0;
				foreach ($this->havings as $having)
				{
					if ($i > 0)
					{
						$query .= " " . $having["andor"] . " ";
					}

					if (is_array($having["value"]))
					{
						$inValues = str_repeat("?, ", count($having["value"]));
						$inValues = rtrim($invalues, ", ");
						$query .= " " . $having["column"] . " IN (" . $inValues . ")";
						$having["value"] = array_map("quote", $having["value"]); //quote each element so it queries okay.
						$params += $having["value"];
					}
					else if ($having["asis"])
					{
						$query .= " " . $having["column"] . " " . $having["operator"] . " " . $having["value"];
					}
					else
					{
						$query .= " " . $having["column"] . " " . $having["operator"] . " ?";
						$params[] = $having["value"];
					}

					$i++;
				}
			}

			if (count($this->orders) > 0)
			{
				$query .= " ORDER BY ";

				$i = 0;
				foreach ($this->orders as $order)
				{
					if ($i > 0)
					{
						$query .= " , ";
					}

					$query    .= " " . $order["column"] . " " . $order["direction"] . " ";

					$i++;
				}
			}

			if (!empty($this->limit))
			{
				$query    .= " LIMIT " . $this->start . ", " . $this->limit . " ";
			}

			return array(
				"query" => $query
				, "params" => $params
			);
		}

		/**
		* @method explain
		*/
		public function explain()
		{
			$build = $this->buildQuery();
			$query = "EXPLAIN " . $build["query"];
			$params = $build["params"];

			$result = $this->db->getAll($query, $params);

			if (DB::isError($result))
			{
				throw new Exception(var_export($result->getDebugInfo(), true));
			}

			return $result;
		}

		/**
		* Alias for fetch().
		*
		*@method read
		*/
		public function read()
		{
			return $this->fetch();
		}

		/**
		* @method fetch
		*/
		public function fetch()
		{
			$this->preRead();

			$build = $this->buildQuery();
			$query = $build["query"];
			$params = $build["params"];
			
			switch ($this->limit)
			{
				case 1:
					$result = $this->db->getRow($query, $params);
					
					if (DB::isError($result))
					{
						throw new Exception(var_export($result->getDebugInfo(), true));
					}

					$data = $result;
					break;
				default:
					$result = $this->db->getAll($query, $params);

					if (DB::isError($result))
					{
						throw new Exception(var_export($result->getDebugInfo(), true));
					}

					$data = array();
					foreach ($result as $row)
					{
						$new = $this->instance();
						$new->setData($row);
						$data[] = $new;
					}
					$data = new Collection($data);
					break;
			}
			
			$this->postRead();

			if (is_a($data, "Collection"))
			{
				return $data;
			}

			if (!empty($data) && (is_array($data)))
			{
				$this->setData($data);
			}

			return $this;
		}

		/**
		* @method find
		* @param {Mixed} key
		*/
		public function find($keys)
		{
			if (is_array($keys))
			{
				foreach ($keys as $index => $value)
				{
					$this->where($this->tableName . "." . $index, "=", $value);
				}
			}
			else
			{
				$this->where($this->tableName . "." . $this->primaryKey, "=", $keys);
			}

			$this->first();

			return $this;
		}

		/**
		* @method first
		*/
		public function first()
		{
			return $this->limit(1)->fetch();
		}

		/**
		* @methos firstOrCreate
		* @param {Array} values
		*/
		public function firstOrCreate($values = array())
		{
			foreach ($values as $col => $value)
			{
				$this->where($this->tableName . "." . $col, "=", $value);
			}

			$result = $this->first();
			
			if ($result->isEmpty())
			{
				foreach ($values as $key => $value)
				{
					$this->$key = $value;
				}

				$this->save();
			}

			return $this;
		}

		/**
		* @method paginate
		* @param {Integer} page
		*/
		public function paginate($records = 15, $page = 1)
		{
			$page = max(0, $page - 1);
			return $this->limit($records, $records * $page)->fetch();
		}

		/**
		* @method random
		*/
		public function random()
		{
			$instance = $this->instance();

			$query = "
				SELECT 
					*
				FROM
					! AS r1
				JOIN(SELECT	(RAND() * (SELECT MAX(!) FROM !)) AS idRandom) AS r2
				WHERE
					r1.! >= r2.idRandom
				ORDER BY
					r1.! ASC
				LIMIT 1
			";
			$params = array(
				$instance->tableName
				, $instance->primaryKey
				, $instance->tableName
				, $instance->primaryKey
				, $instance->primaryKey
			);
			$result = $instance->getRow($query, $params);

			if (DB::isError($result))
			{
				throw new Exception($result->getDebugInfo());
			}

			if (is_array($result))
			{
				$instance->data = $result;
			}

			return $instance;
		}

		/**
		* @method raw
		*/
		public static function raw($query, $params = array())
		{
			$instance = new static;

			$result = $instance->query($query, $params);

			return $result;
		}

		/**
		* @method all
		*/
		public function all()
		{
			$instance = $this->instance();

			return $instance->fetch();
		}

		/** @method select
		* @param {String} select
		*/
		public function select($selects)
		{
			if (is_array($selects))
			{
				foreach ($selects as $select)
				{
					$this->selects[] = $select;
				}
			}
			else
			{
				$this->selects[] = $selects;
			}

			return $this;
		}

		/**
		* @method where
		* @param {String} column
		* @param {String} operator
		* @param {String} value
		* @param {String} andOr
		* @param {String} asIs
		*/
		public function where($column, $operator, $value, $andOr = "AND", $asIs = false)
		{
			if ($this->data instanceof Collection)
			{
				$this->setData($this->data->filterBy($column, $value));
			}

			$this->wheres[] = array(
				"column" => $column
				, "operator" => $operator
				, "value" => $value
				, "andor" => $andOr
				, "asis" => $asIs
			);

			return $this;
		}

		/**
		* @method whereIn
		* @param {String} column
		* @param {String} values
		* @param {String} andOr
		*/
		public function whereIn($column, Array $values, $andOr = "AND")
		{
			if (empty($values))
			{
				$values = ["NULL"];
			}

			$values = array_map(function($value) {
				return "\"" . $value . "\"";
			}, $values);

			$values = "(" . implode(",", $values) . ")";

			return $this->where($column, "IN", $values, $andOr, true);
		}

		/**
		* @method whereNotIn
		* @param {String} column
		* @param {String} values
		* @param {String} andOr
		*/
		public function whereNotIn($column, Array $values, $andOr = "AND")
		{
			if (empty($values))
			{
				return $this;
			}

			$values = array_map(function($value) {
				return "\"" . $value . "\"";
			}, $values);

			$values = "(" . implode(",", $values) . ")";

			return $this->where($column, "NOT IN", $values, $andOr, true);
		}

		/**
		* Just a method to make chaining or conditions a bit easier. Uses the last where value.
		* @method whereOr
		* @param {String} value
		*/
		public function whereOr($value)
		{
			return $this->where(end($this->wheres)["column"], end($this->wheres)["operator"], $value, "OR", end($this->wheres)["asis"]);
		}

		/**
		* @method between
		* @param {String} start
		* @param {String} end
		*/
		public function between($column, $start, $end, $andOr = "AND")
		{
			$operator = "BETWEEN";
			$value = "'" . $start . "' AND '" . $end . "'";

			return $this->where($column, $operator, $value, $andOr, true);
		}

		/**
		* @method having
		* @param {String} column
		* @param {String} operator
		* @param {String} value
		* @param {String} andOr
		* @param {String} asIs
		*/
		public function having($column, $operator, $value, $andOr = "AND", $asIs = false)
		{
			if ($this->data instanceof Collection)
			{
				$this->setData($this->data->filterBy($column, $value));
			}

			$this->havings[] = array(
				"column" => $column
				, "operator" => $operator
				, "value" => $value
				, "andor" => $andOr
				, "asis" => $asIs
			);

			return $this;
		}

		/**
		* @method join
		* @param {String} table
		* @param {String} local
		* @param {String} operator
		* @param {String} foreign
		* @param {String} type
		* @return {Model}
		*/
		public function join($table, $local, $operator, $foreign, $type = "JOIN")
		{
			$this->joins[$table][] = [
				"local" => $local
				, "operator" => $operator
				, "foreign" => $foreign
				, "type" => $type
			];

			return $this;
		}

		/**
		* @method leftJoin
		* @param {String} table
		* @param {String} local
		* @param {String} operator
		* @param {String} foreign
		* @param {String} type
		* @return {Model}
		*/
		public function leftJoin($table, $local, $operator, $foreign)
		{
			return $this->join($table, $local, $operator, $foreign, "LEFT JOIN");
		}

		/**
		* @method order
		* @param {String} column
		* @param {Boolean} descending
		*/
		public function order($column, $descending = false)
		{
			if ($this->data instanceof Collection)
			{
				$this->setData($this->data->sortBy($column, SORT_REGULAR, $descending));
			}

			$this->orders[] = array(
				"column" => $column
				, "direction" => $descending ? "DESC" : "ASC"
			);

			return $this;
		}

		/**
		* @method nest
		* @return {Model}
		*/
		public function nest()
		{
			$this->nests[count($this->wheres)] = count($this->nests) % 2 == 0 ? "(" : ")";

			return $this;
		}

		/**
		* @method group
		* @param {String} column
		* @return {Model}
		*/
		public function group($column)
		{
			$this->groups[] = $column;

			return $this;
		}

		/**
		* @method limit
		*/
		public function limit($limit, $start = 0)
		{
			$this->start = $start;
			$this->limit = $limit;

			return $this;
		}

		/**
		* @method autocommit
		*/
		public function autocommit($autocommit = true)
		{
			$this->autocommit = $autocommit;
			$this->db->autoCommit($this->autocommit);
			
			return $this;
		}

		/**
		* @method distinct
		*/
		public function distinct($value = true)
		{
			$this->distinct = $value;

			return $this;
		}

		/**
		* @method rollback
		*/
		public function rollback()
		{
			$this->db->rollback();
		}

		/**
		* @method commit
		*/
		public function commit()
		{
			$this->db->commit();
		}

		/**
		* @method delete
		*/
		public function delete()
		{
			$this->preDelete();

			if ($this->softDelete == true)
			{
				$timeDeletedColumn = $this->tableName . "." . $this->timeDeletedColumn;
				$this->$timeDeletedColumn = new Carbon;

				return $this->save();
			}
			else
			{
				if (is_array($this->primaryKey))
				{
					$query = '
						DELETE FROM
							!
						WHERE
					';
					$params = array(
						$this->tableName
					);

					$i = 0;
					foreach ($this->primaryKey as $key)
					{
						if ($i > 0)
						{
							$query .= " AND ";
						}

						$query .= " ! = ?";
						$params[] = $key;
						$params[] = $this->$key;

						$i++;
					}
				}
				else
				{
					$query = '
						DELETE FROM
							!
						WHERE
							! = ?
					';
					$params = array(
						$this->tableName
						, $this->primaryKey
						, $this->getPrimaryKey()
					);
				}
				
				$result = $this->db->query($query, $params);

				if (DB::isError($result))
				{
					throw new Exception($result->getDebugInfo());
				}

				$this->postDelete();

				return $this;
			}
		}

		/**
		* @method deleted
		* @return {Boolean}
		*/
		public function deleted()
		{
			if ($this->DeletedAt)
			{
				return true;
			}

			return false;
		}

		/**
		* @method save
		*/
		public function save()
		{
			if (is_array($this->primaryKey))
			{
				foreach ($this->primaryKey as $key)
				{
					if (array_key_exists($key, $this->changed))
					{
						return $this->create($this->data);
					}
				}

				return $this->update();
			}

			if ($this->getPrimaryKey() && empty($this->changed[$this->primaryKey]))
			{
				return $this->update();
			}
			else
			{
				return $this->create($this->data);
			}
		}

		/**
		* @method sort
		*/
		public function sort()
		{
			$this->data = $this->data->sort("CreatedAt");

			return $this;
		}

		/**
		* @method update
		* @return {Model}
		*/
		public function update()
		{
			if (empty($this->changed))
			{
				return $this;
			}
			
			if ($this->timestamps)
			{
				if ($this->timeUpdatedColumn !== null)
				{
					$this->data[$this->tableName . "." . $this->timeUpdatedColumn] = date("Y-m-d H:i:s");
					$this->changed[$this->tableName . "." . $this->timeUpdatedColumn] = true;
				}
			}

			$this->preUpdate();

			$params = array();
			$query = '
				UPDATE
					' . $this->tableName . '
			';

			$query .= $this->buildJoins($this->joins);

			$query .= '
				SET
			';

			$values = array_intersect_key($this->data, $this->changed);

			$updates = array();
			foreach ($values as $column => $value)
			{
				$updates[] = ' ' . $column . ' = ? ';
				$params[] = $values[$column];
			}
			$query .= implode(",", $updates);

			$query .= ' WHERE ';

			$primaryKey = $this->primaryKey;
			if (is_array($primaryKey))
			{
				foreach ($primaryKey as $key)
				{
					$params[] = $this->tableName;
					$params[] = $key;
					$params[] = $this->$key;
				}

				$query .= implode(" AND ", array_fill(0, count($primaryKey), " !.! = ? "));
			}
			else
			{
				$query .= " " . $this->tableName . "." . $this->primaryKey . " = ? ";
				$params[] = $this->getPrimaryKey();
			}
			
			$result = $this->db->query($query, $params);

			if (DB::isError($result))
			{
				throw new Exception($result->getDebugInfo());
			}
			
			if (isset($this->changed[$this->timeDeletedColumn]))
			{
				$this->postDelete();
			}
			else
			{
				$this->postUpdate($this->changed);
			}

			$this->changed = [];

			return $this;
		}

		/**
		* @method create
		* @param {Array} values
		* @return {Model}
		*/
		public function create(Array $values = [])
		{
			$values = $this->checkColumnFields($values);

			foreach ($this->defaults as $key => $value)
			{
				if (!isset($values[$key]))
				{
					$values[$key] = $value;
				}
			}

			foreach ($this->required as $required)
			{
				if (!isset($values[$required]))
				{
					throw new Exception("The $required field is required in " . get_class($this) . ".");
				}
			}

			$values = $this->preprocess($values);

			if ($this->timestamps)
			{
				if ($this->timeCreatedColumn !== null && empty($values[$this->timeCreatedColumn]))
				{
					$values[$this->timeCreatedColumn] = date("Y-m-d H:i:s");
				}

				if ($this->timeUpdatedColumn !== null && empty($values[$this->timeUpdatedColumn]))
				{
					$values[$this->timeUpdatedColumn] = date("Y-m-d H:i:s");
				}
			}

			if ($this->softDelete)
			{
				if ($this->timeDeletedColumn !== null && empty($values[$this->timeDeletedColumn]))
				{
					$values[$this->timeDeletedColumn] = NULL;
				}
			}

			foreach ($this->defaults as $key => $value)
			{
				if (!isset($values[$key]))
				{
					$values[$key] = $value;
				}
			}

			if (count($values) > 0)
			{
				$valuesFiller = implode(", ", array_fill(0, count($values), "?"));
			}
			else
			{
				$valuesFiller = "";
			}

			$this->preCreate();

			$query = '
				INSERT INTO
					' . $this->tableName . '(' . implode(", ", array_keys($values)) . ')
				VALUES(' . $valuesFiller . ')
			';

			$result = $this->db->query($query, $values);

			if (DB::isError($result))
			{
				throw new Exception($result->getDebugInfo());
			}

			$primaryKey = $this->primaryKey;
			if (!$this->primaryKeyNotInt && !is_array($primaryKey) && $this->primaryKey && empty($this->$primaryKey))
			{
				$insertID = $this->db->getOne("SELECT LAST_INSERT_ID()");
				$values[$this->primaryKey] = $insertID;
			}

			$this->data = $values;

			//If there are joins we need to grab those or it'll return incomplete data.
			if (!empty($this->joins))
			{
				$this->find($this->getPrimaryKey());
			}

			$this->postCreate();

			$this->changed = array();

			return $this;
		}

		/**
		* @method insert
		* @param {Array} values
		* @return {Integer}
		*/
		public function insert($values)
		{
			return $this->create($values);
		}

		/**
		* @method getCreatedDateTime
		* @return {DateTime}
		*/
		public function getCreatedDateTime()
		{
			$col = $this->timeCreatedColumn;

			return DateTime::createFromFormat("Y-m-d H:i:s", $this->$col);
		}

		/**
		* @method getUpdatedDateTime
		* @return {DateTime}
		*/
		public function getUpdatedDateTime()
		{
			$col = $this->timeUpdatedColumn;

			return DateTime::createFromFormat("Y-m-d H:i:s", $this->$col);
		}

		/**
		* @method belongsTo
		*/
		public function belongsTo($model, $foreignKey = null, $localKey = null)
		{
			if (is_null($foreignKey))
			{
				$foreignKey = $this->getPrimaryKeyName();
			}

			if (is_null($localKey))
			{
				$localKey = $this->getPrimaryKeyName();
			}

			return $this->hasOne($model, $foreignKey, $localKey);
		}

		/**
		* @method hasOne
		*/
		public function hasOne($model, $foreignKey, $localKey)
		{
			$localKey = $this->$localKey;
			
			if (!isset($this->has["one"][$model][$foreignKey][$localKey]))
			{
				$foreignTable = $this->build($model);
				$foreignTable = $foreignTable->getTableName();
				$this->has["one"][$model][$foreignKey][$localKey] = $this->build($model)->where("$foreignTable.$foreignKey", "=", $localKey)->first();
			}

			return $this->has["one"][$model][$foreignKey][$localKey];
		}

		/**
		* @method hasOne
		*/
		public function hasMany($model, $foreignKey = null, $localKey = null, $cache = true)
		{
			if (is_null($foreignKey))
			{
				$foreignKey = get_class($this) . "Id";
			}
			$foreignTable = $this->build($model);
			$foreignTable = $foreignTable->getTableName();

			if (is_null($localKey))
			{
				$localKey = $this->primaryKey;
			}

			//This occurs if the key we're searching by wasn't in the result set. In this case we need to manually fetch it, but don't store it in the model. There may be a reason it was excluded in the first place.
			if (!isset($this->$localKey))
			{
				$primaryKey = $this->primaryKey;
				$localKey = $this->db->getOne("SELECT $localKey FROM " . $this->tableName . " WHERE " . $this->primaryKey . " = '" . $this->$primaryKey . "'");
			}
			else
			{
				$localKey = $this->$localKey;
			}
			
			if (!isset($this->has["many"][$model][$foreignKey][$localKey]))
			{
				$results = $this->build($model)->where("$foreignTable.$foreignKey", "=", $localKey)->clearLimit()->fetch();

				if ($cache)
				{
					$this->has["many"][$model][$foreignKey][$localKey] = $results;
				}
				else //if we're not caching, we don't need to store this every time. This helps save on memory for some large result sets.
				{
					return $results;
				}
			}

			return $this->has["many"][$model][$foreignKey][$localKey];
		}

		/**
		* @method replace
		* @param {Array} values
		*/
		public function replace($values)
		{
			return $this->insert($values);
		}

		/**
		* @method lastInsertID
		* @return {Mixed}
		*/
		public function lastInsertID()
		{
			return $this->lastInsertID;
		}

		/**
		* @method count
		*/
		public function count()
		{
			return count($this->data);
		}

		/**
		* @method isEmpty
		* @param {Boolean}
		*/
		public function isEmpty()
		{
			if (is_array($this->data))
			{
				if (empty($this->data))
				{
					return true;
				}

				return false;
			}
			
			return $this->data->isEmpty();
		}

		/**
		* @method query
		* @param {String} query
		* @param {Array} array
		* @return {Boolean}
		*/
		public function query($query, $params = [])
		{
			$result = $this->db->query($query, $params);

			$this->log($this->db->last_query);

			if (DB::isError($result))
			{
				throw new Exception($result->getDebugInfo());
			}

			return true;
		}

		/**
		* @method getRow
		*/
		protected function getRow($query, $params)
		{
			$result = $this->db->getRow($query, $params);

			$this->log($this->db->last_query);

			if (DB::isError($result))
			{
				throw new Exception($result->getDebugInfo());
			}

			return $result;
		}

		/**
		* @method getall
		*/
		protected function getAll($query, $params = [])
		{
			$result = $this->db->getAll($query, $params);

			$this->log($this->db->last_query);

			if (DB::isError($result))
			{
				throw new Exception($result->getDebugInfo());
			}
			
			return $result;
		}

		/**
		* @method toArray
		*/
		public function toArray()
		{
			$data = $this->data;
			while ($data instanceOf Collection)
			{
				$data = $data->toArray();
			}

			return $data;
		}

		/**
		* Returns this object's data as a JSON string.
		*
		* @method toJSON
		* @return {String} JSON
		*/
		public function toJSON()
		{
			return json_encode($this->data);
		}

		/**
		* Logs the provided query to the set logger. Default is debug.
		*
		* @method log
		* @param {String} query
		* @param {String} level
		*/
		private function log($query, $level = "debug")
		{
			if ($this->logger === null || !$this->logger->validLogLevel($level))
			{
				return;
			}
			
			return $this->logger->log($query, $level);
		}

		/**
		* Forcibly updates the date last updated column for this record.
		* 
		* @method touch
		* @return {Model}
		*/
		public function touch()
		{
			if (!$this->timestamps || $this->timeUpdatedColumn === null)
			{
				return $this;
			}

			$timeUpdatedColumn = $this->tableName . "." . $this->timeUpdatedColumn;

			$this->$timeUpdatedColumn = date("Y-m-d H:i:s");
			$this->update();

			return $this;
		}

		/**
		* All the preprocessing including the filters, custom functions, and validation
		*
		* @method preprocess
		* @param {Array} $values
		* @return {Array} $values
		*/
		public function preprocess(Array $values)
		{
			//Put the values through a filter if we have any.
			if (!empty($this->filters))
			{
				$validator = new Validator;
				$values = $validator->filter($values, $this->filters);
			}

			if (!empty($this->preprocess))
			{
				foreach ($this->preprocess as $column => $callback)
				{
					if (isset($values[$column]))
					{
						$values[$column] = call_user_method($callback, $this, $values[$column]);
					}
				}
			}

			return $values;
		}

		/**
		* Validates using the set Model values for this class.
		*
		* @method validate
		* @return {Boolean|Array}
		*/
		public function validate()
		{
			if (true !== $result = $validator->validate($values, $this->validations))
			{
				return $result;
			}

			return true;
		}

		/**
		* Returns the join conditions based on the provided data.
		* 
		* @method buildJoins
		* @param {Array}
		* @return {String}
		*/
		protected function buildJoins(array $joins)
		{
			$query = "";
			foreach ($joins as $tableName => $tableData)
			{
				foreach ($tableData as $key => $join)
				{
					if ($key == 0)
					{
						$query .= " 
							" . $join["type"] . "
								"  . $tableName . "
							ON
								" . $join["local"] . " " . $join["operator"] . " " . $join["foreign"] . "
						";
					}
					else
					{
						$query .= " 
							AND
								" . $join["local"] . " " . $join["operator"] . " " . $join["foreign"] . "
						";
					}
				}
			}
			
			return $query;
		}

		/**
		* @method castToString
		* @param {Mixed} value
		*/
		protected function castToString($value)
		{
			return is_null($value) ? null : (string)$value;
		}

		/**
		* PRE AND POST FUNCTIONS
		*
		* These are used to do things before and after CRUD operations.
		*/

		/**
		* @method preCreate
		*/
		public function preCreate()
		{
			
		}

		/**
		* @method postCreate
		*/
		public function postCreate()
		{
			$this->log("Created a new " . get_class($this) . " with PK " . (is_array($this->getPrimaryKey()) ? implode(", ", $this->getPrimaryKey()) : $this->getPrimaryKey()) . " with values \"" . implode(", ", array_keys($this->data)) . "\".", "info");
			$this->log($this->db->last_query, "debug");
		}

		/**
		* @method preRead
		*/
		protected function preRead()
		{
			
		}

		/**
		* @method postRead
		*/
		protected function postRead()
		{
			$this->log($this->db->last_query);

			/*if (!$this->isEmpty() && $this->restricted && !$this->canUserRead($this->session->getLoggedInCustomer()))
			{
				trigger_error("You are not authorized to access that information.");
				throw new Exception("Unauthorized user " . $this->session->getLoggedInCustomer() . " attempted to access " . $this . ".");
			}*/
		}

		/**
		* @method preUpdate
		*/
		protected function preUpdate()
		{

		}

		/**
		* @method postUpdate
		*/
		protected function postUpdate()
		{
			$this->log("Changed the values for " . get_class($this) . " with PK " . (is_array($this->getPrimaryKey()) ? implode(", ", $this->getPrimaryKey()) : $this->getPrimaryKey()) . " for columns \"" . implode(", ", array_keys($this->changed)) . "\".", "info");
			$this->log($this->db->last_query, "debug");
		}

		/**
		* @method preDelete
		*/
		protected function preDelete()
		{
			
		}

		/**
		* @method postDelete
		*/
		protected function postDelete()
		{
			$this->log("Deleted " . get_class($this) . " with PK " . (is_array($this->getPrimaryKey()) ? implode(", ", $this->getPrimaryKey()) : $this->getPrimaryKey()) . ".", "info");
			$this->log($this->db->last_query, "debug");
		}

		/**
		* CREATE, READ, UPDATE, and DELETE PERMISSION CHECKS
		*
		* The default for these are ALWAYS true. You must override the appropriate canUserWhatever functions in order to implement any security.
		*/

		/**
		* Checks to see if the user of the model can create it.
		*
		* @method canUserCreate
		* @return {Boolean}
		*/
		static public function canUserCreate(Customer $user)
		{
			return true;
		}

		/**
		* Checks to see if the user of the model can view it.
		*
		* @method canUserRead
		* @return {Boolean}
		*/
		public function canUserRead(Customer $user)
		{
			return true;
		}

		/**
		* Checks to see if the user of the model can update it.
		*
		* @method canUserUpdate
		* @return {Boolean}
		*/
		public function canUserUpdate(Customer $user)
		{
			return true;
		}

		/**
		* Checks to see if the user of the model can delete it.
		*
		* @method canUserDelete
		* @return {Boolean}
		*/
		public function canUserDelete(Customer $user)
		{
			return true;
		}
	}