<?php
/**
* Class to interact with a mysql database
*/
class db_mysql
{
	/**
	* Class instance
	*
	* @var object
	*/
	private static $instance;

	/**
	* Connection to MySQL.
	*
	* @var string
	*/
	protected $link;

	/**
	* Holds the most recent connection.
	*
	* @var string
	*/
	protected $recent_link = null;

	/**
	* Holds the contents of the most recent SQL query.
	*
	* @var string
	*/
	protected $sql = '';

	/**
	* Holds the number of queries executed.
	*
	* @var integer
	*/
	public $query_count = 0;

	/**
	* The text of the most recent database error message.
	*
	* @var string
	*/
	protected $error = '';

	/**
	* The error number of the most recent database error message.
	*
	* @var integer
	*/
	protected $errno = '';

	/**
	* Do we currently have a lock in place?
	*
	* @var boolean
	*/
	protected $is_locked = false;

	/**
	* Show errors? If set to true, the error message/sql is displayed.
	*
	* @var boolean
	*/
	public $show_errors = false;

	/**
	* Database host
	*
	* @var string
	*/
	protected static $db_host;

	/**
	* Database username
	*
	* @var string
	*/
	protected static $db_user;

	/**
	* Database password
	*
	* @var string
	*/
	protected static $db_pass;

	/**
	* Database name.
	*
	* @var string
	*/
	protected static $db_name;

	/**
	* Constructor. Initializes a database connection and selects our database.
	*
	* private, cannot be accessed directly outside of this class
	*
	* @param  string   $db_host  Database host
	* @param  string   $db_user  Database username
	* @param  string   $db_pass  Database password
	* @param  string   $db_name  Database name
	* @return boolean            Connection resource, if database connection is established.
	*/
	private function __construct()
	{
		self::set_params();

		$this->link = @mysql_connect(self::$db_host, self::$db_user, self::$db_pass);

		if (is_resource($this->link) AND @mysql_select_db(self::$db_name, $this->link))
		{
			$this->recent_link =& $this->link;
			return $this->link;
		}
		else
		{
			// If we couldn't connect or select the db...
			$this->raise_error('db_mysql::__construct() - Could not select and/or connect to database: ' . self::$db_name);
		}
	}

	/**
	* Creates an instance of the class.
	*
	* @param  void
	* @return object
	*/
	public static function getInstance()
	{
		if (!self::$instance)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	* Sets connection/database parameters.
	*
	* @param  void
	* @return void
	*/
	protected static function set_params()
	{
		global $dbconfig;

		self::$db_host = $dbconfig['host'];
		self::$db_user = $dbconfig['user'];
		self::$db_pass = $dbconfig['pass'];
		self::$db_name = $dbconfig['name'];
	}

	/**
	* Executes a sql query. If optional $only_first is set to true, it will
	* return the first row of the result as an array.
	*
	* @param  string  Query to run
	* @param  bool    Return only the first row, as an array?
	* @return mixed
	*/
	public function query($sql, $only_first = false)
	{
		$this->recent_link =& $this->link;
		$this->sql =& $sql;
		$result = @mysql_query($sql, $this->link);

		$this->query_count++;

		if ($only_first)
		{
			$return = $this->fetch_array($result);
			$this->free_result($result);
			return $return;
		}
		return $result;
	}

	/**
	* Fetches a row from a query result and returns the values from that row as an array.
	*
	* @param  string  The query result we are dealing with.
	* @return array
	*/
	public function fetch_array($result)
	{
		return @mysql_fetch_assoc($result);
	}

	/**
	* Will fetch all records from the database, and will optionally return the
	* value of a single field from all records.
	*
	* @param  string  $sql    SQL Query string
	* @param  string  $field  Field/column
	* @return array           Will return array of all db records.
	*/
	public function fetch_all($sql, $field = '')
	{
		$return = array();

		if (($result = $this->query($sql)))
		{
			while ($row = $this->fetch_array($result))
			{
				$return[] = ($field) ? $row[$field] : $row;
			}
			$this->free_result($result);
		}
		return $return;
	}

	/**
	* Returns the number of rows in a result set.
	*
	* @param  string  The query result we are dealing with.
	* @return integer
	*/
	public function num_rows($result)
	{
		return @mysql_num_rows($result);
	}

	/**
	* Retuns the number of rows affected by the most recent query
	*
	* @return integer
	*/
	public function affected_rows()
	{
		return @mysql_affected_rows($this->recent_link);
	}

	/**
	* Returns the number of queries executed.
	*
	* @param  none
	* @return integer
	*/
	public function num_queries()
	{
		return $this->query_count;
	}

	/**
	* Lock database tables
	*
	* @param   array  Array of table => lock type
	* @return  void
	*/
	public function lock($tables)
	{
		if (is_array($tables) AND count($tables))
		{
			$sql = '';

			foreach ($tables AS $name => $type)
			{
				$sql .= (!empty($sql) ? ', ' : '') . "$name $type";
			}

			$this->query("LOCK TABLES $sql");
			$this->is_locked = true;
		}
	}

	/**
	* Unlock tables
	*/
	public function unlock()
	{
		if ($this->is_locked)
		{
			$this->query("UNLOCK TABLES");
			$this->is_locked = false; 
		}
	}

	/**
	* Returns the ID of the most recently inserted item in an auto_increment field
	*
	* @return  integer
	*/
	public function insert_id()
	{
		return @mysql_insert_id($this->link);
	}

	/**
	* Escapes a value to make it safe for using in queries.
	*
	* @param  string  Value to be escaped
	* @param  bool    Do we need to escape this string for a LIKE statement?
	* @return string
	*/
	public function prepare($value, $do_like = false)
	{
		$value = stripslashes($value);

		if ($do_like)
		{
			$value = str_replace(array('%', '_'), array('\%', '\_'), $value);
		}
		return mysql_real_escape_string($value, $this->link);
	}

	/**
	* Frees memory associated with a query result.
	*
	* @param  string   The query result we are dealing with.
	* @return boolean
	*/
	public function free_result($result)
	{
		return @mysql_free_result($result);
	}

	/**
	* Turns database error reporting on
	*/
	public function show_errors()
	{
		$this->show_errors = true;
	}

	/**
	* Turns database error reporting off
	*/
	public function hide_errors()
	{
		$this->show_errors = false;
	}

	/**
	* Closes our connection to MySQL.
	*
	* @param  none
	* @return boolean
	*/
	public function close()
	{
		$this->sql = '';
		return @mysql_close($this->link);
	}

	/**
	* Returns the MySQL error message.
	*
	* @param  none
	* @return string
	*/
	public function error()
	{
		$this->error = (is_null($this->recent_link)) ? '' : mysql_error($this->recent_link);
		return $this->error;
	}

	/**
	* Returns the MySQL error number.
	*
	* @param  none
	* @return string
	*/
	function errno()
	{
		$this->errno = (is_null($this->recent_link)) ? 0 : mysql_errno($this->recent_link);
		return $this->errno;
	}

	/**
	* Gets the url/path of where we are when a MySQL error occurs.
	*
	* @access private
	* @param  none
	* @return string
	*/
	protected function get_error_path()
	{
		if ($_SERVER['REQUEST_URI'])
		{
			$errorpath = $_SERVER['REQUEST_URI'];
		}
		else
		{
			if ($_SERVER['PATH_INFO'])
			{
				$errorpath = $_SERVER['PATH_INFO'];
			}
			else
			{
				$errorpath = $_SERVER['PHP_SELF'];
			}

			if ($_SERVER['QUERY_STRING'])
			{
				$errorpath .= '?' . $_SERVER['QUERY_STRING'];
			}
		}

		if (($pos = strpos($errorpath, '?')) !== false)
		{
			$errorpath = urldecode(substr($errorpath, 0, $pos)) . substr($errorpath, $pos);
		}
		else
		{
			$errorpath = urldecode($errorpath);
		}
		return $_SERVER['HTTP_HOST'] . $errorpath;
	}

	/**
	* If there is a database error, the script will be stopped and an error message displayed.
	*
	* @param  string  The error message. If empty, one will be built with $this->sql.
	* @return string
	*/
	public function raise_error($error_message = '')
	{
		if ($this->recent_link)
		{
			$this->error = $this->error($this->recent_link);
			$this->errno = $this->errno($this->recent_link);
		}

		if ($error_message == '')
		{
			$this->sql = "Error in SQL query:\n\n" . rtrim($this->sql) . ';';
			$error_message =& $this->sql;
		}
		else
		{
			$error_message = $error_message . ($this->sql != '' ? "\n\nSQL:" . rtrim($this->sql) . ';' : '');
		}

		$message = htmlspecialchars("$error_message\n\nMySQL Error: {$this->error}\nError #: {$this->errno}\nFilename: " . $this->get_error_path());
		$message = '<code>' . nl2br($message) . '</code>';

		if (!$this->show_errors)
		{
			$message = "<!--\n\n$message\n\n-->";
		}
		die("There seems to have been a slight problem with our database, please try again later.<br /><br />\n$message");
	}
}

?>