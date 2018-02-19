<?php

/**
 * Библиотека для удобной, понятной и простой работы с БД MySQL
 *
 * Данная библиотека основана на морально устаревшей библиотеке DbSimple (http://dklab.ru/lib/DbSimple/) за авторством Дмитрия Котерова.
 *
 * Основные возможности:
 * — Поддержка PHP 5 и 7, основана на расширении MySQLi
 * — Простой и лаконичный интерфейс (см. ниже), компактный код (всего один файл и один класс)
 * — Условные макроподстановки в теле SQL-запроса ({}-блоки), позволяющие динамически генерировать даже очень сложные запросы без ущерба читабельности кода
 * — Поддержка различных видов плейсхолдеров (параметров запроса): списковый, ассоциативный, идентификаторный и т.д.
 * — Поддержка функции "выборка + подсчет общего числа строк" (для отображения по страницам)
 * — Функции непосредственной выборки: всего результата, строки, столбца, ячейки, ассоциативного массива, многомерного массива, связанного дерева и т.д.
 * — Удобный интерфейс для отслеживания и обработки ошибок
 *
 * Данная библиотека является свободным программным обеспечением. Вы можете использовать её в любых ваших проектах в любом виде.
 *
 * @link https://kirkizh.ru/
 * @author Valery Kirkizh <mail@figaroo.ru>
 * @version 1.0
 */

/**
 * Интерфейс библиотеки:
 *
 * mixed select(string $query [, $arg1 ...])
 * — Выборка двумерного массива
 *
 * hash selectRow(string $query [, $arg1 ...])
 * — Выборка однострочного результата запроса (одна строка)
 *
 * scalar selectCell(string $query [, $arg1 ...])
 * — Выборка скалярного результата запроса (одна ячейка)
 *
 * mixed selectPage(int &$total, string $query [, $arg1 ...)
 * — Выборка двумерного массива с подсчётом общего кол-ва строк
 *
 * mixed query(string $query [, $arg1 ...])
 * — Вызов не-SELECT запроса
 *
 * mixed transaction()
 * — Запускает новую транзакцию
 *
 * mixed commit()
 * — Подтверждает текущую транзакцию
 *
 * mixed rollback()
 * — Отменяет текущую транзакцию
 */


define('DB_SKIP', log(0));

class DB
{

	const SKIP = DB_SKIP;
	const ARRAY_KEY = 'ARRAY_KEY';
	const PARENT_KEY = 'PARENT_KEY';

	protected $config = array(
		'host' => 'localhost',
		'user' => 'root',
		'pass' => '',
		'name' => 'database',
		'pref' => '',
	);

	protected $link = null;
	protected $prefix = '';
	protected $debug = false;
	protected $time = 0;

	// подключение к базе данных
	public function __construct($set = null)
	{
		if ($set !== null) {
			if (!is_array($set)) throw new DB_Exception(-1, 'Parameters should be an array', 'mysql connect');
			$this->config = $set;
		}

		if (!isset($this->config['host'])) {
			throw new DB_Exception(-1, 'Host name was not specified', 'mysql connect');
		}
		if (!isset($this->config['user'])) {
			throw new DB_Exception(-1, 'User name was not specified', 'mysql connect');
		}
		if (!isset($this->config['pass'])) {
			$this->config['pass'] = '';
		}
		if (!isset($this->config['name'])) {
			throw new DB_Exception(-1, 'Database name was not specified', 'mysql connect');
		}
		if (!isset($this->config['pref'])) {
			$this->config['pref'] = '';
		}

		$this->prefix = $this->config['pref'];

		if (!class_exists('mysqli')) {
			throw new DB_Exception(-1, 'MySQLi extension is not loaded', 'mysql connect');
		}

		$this->link = @new mysqli($this->config['host'], $this->config['user'], $this->config['pass'], $this->config['name']);
		if ($this->link->connect_errno) {
			throw new DB_Exception($this->link->connect_errno, $this->link->connect_error, 'mysql connect');
		}

		if (!@$this->link->set_charset('utf8')) {
			throw new DB_Exception($this->link->errno, $this->link->error, 'mysql set charset');
		}
	}

	public function __destruct()
	{
		@$this->link->close();
	}

	private function __clone() {}

	// выборка двумерного массива
	public function select($query)
	{
		$total = false;
		return $this->_query(func_get_args(), $total);
	}

	// выборка двумерного массива с подсчётом общего кол-ва строк
	public function selectPage(&$total, $query)
	{
		$args = func_get_args();
		array_shift($args);
		$total = true;
		return $this->_query($args, $total);
	}

	// выборка однострочного результата запроса (одна строка)
	public function selectRow($query)
	{
		$total = false;
		$res = $this->_query(func_get_args(), $total);
		if (!is_array($res)) return $res;
		if (!count($res)) return array();
		return array_shift($res);
	}

	// выборка скалярного результата запроса (одна ячейка)
	public function selectCell($query)
	{
		$total = false;
		$res = $this->_query(func_get_args(), $total);
		if (!is_array($res)) return $res;
		if (!count($res)) return null;
		$res = array_shift($res);
		if (!is_array($res)) return $res;
		return array_shift($res);
	}

	// вызов не-SELECT запроса
	public function query($query)
	{
		$total = false;
		return $this->_query(func_get_args(), $total, true);
	}

	// запуск новой транзакции
	public function transaction()
	{
		return @$this->link->begin_transaction();
	}

	// подтверждение транзакции
	public function commit()
	{
		return @$this->link->commit();
	}

	// отмена транзакции
	public function rollback()
	{
		return @$this->link->rollback();
	}

	// режим отладки запроса
	public function debug()
	{
		$this->debug = true;
	}

	// выполнение запроса
	protected function _query($query, &$total, $nonselect = false)
	{
		$query = $this->placeholders($query);

		if ($this->debug) {
			echo $query;
			$this->debug = false;
		}

		if (!$nonselect && !preg_match("/^(\s* SELECT)(.*)/six", $query)) {
			throw new DB_Exception(-2, 'You should use query() method for non-SELECT queries', $query);
		}

		if ($total) {
			if (preg_match("/^(\s* SELECT)(.*)/six", $query, $m)) {
				$query = $m[1] . ' SQL_CALC_FOUND_ROWS' . $m[2];
			}
		}

		$this->time = microtime(1);
		$result = @$this->link->query($query);
		$this->time = microtime(1) - $this->time;

		if ($result === false) {
			throw new DB_Exception($this->link->errno, $this->link->error, $query);
		}

		if ($result === true) {
			if ($this->link->insert_id) {
				return $this->link->insert_id;
			} else {
				return $this->link->affected_rows;
			}
		}

		if ($total) {
			$total = @$this->link->query('SELECT FOUND_ROWS()');
			if ($total === false) {
				throw new DB_Exception($this->link->errno, $this->link->error, 'SELECT FOUND_ROWS()');
			}

			$total = @$total->fetch_array(MYSQLI_NUM)[0];
			if (!$total) $total = 0;
		}

		$data = array();
		if ($result->num_rows) {
			while ($row = @$result->fetch_assoc()) {
				$data[] = $row;
			}
		}

		@$result->free();
		unset($result);

		$data = $this->transform($data);

		return $data;
	}

	// раскрытие плейсхолдеров
	protected $placeholderArgs = array();
	protected $placeholderNoValueFound = false;
	protected function placeholders(&$queryAndArgs)
	{
		$this->placeholderArgs = array_reverse($queryAndArgs);
		$query = array_pop($this->placeholderArgs);
		$this->placeholderNoValueFound = false;
		$query = $this->placeholdersFlow($query);
		return $query;
	}
	protected function placeholdersFlow($query)
	{
		 $re = '{
			 (?>
				 (?>
					 -- [^\r\n]*
				 )
				   |
				 (?>
					 "   (?> [^"\\\\]+|\\\\"|\\\\)*	   "     |
					\'   (?> [^\'\\\\]+|\\\\\'|\\\\)* \'     |
					 `   (?> [^`]+ | ``)*              `     |
					 /\* .*? \*/
				 )
			 )
				  |
			 (?>
				 \{
					 ( (?> (?>[^{}]+)	|  (?R) )* )
				 \}
			 )
				  |
			 (?>
				 (\?) ( [_dsafnblsx\#]? )
			 )
		 }sx';
		 $query = preg_replace_callback(
			 $re,
			 array(&$this, 'placeholdersCallback'),
			 $query
		 );
		 return $query;
	}
	protected function placeholdersCallback($m)
	{
		if (!empty($m[2])) {
			$type = $m[3];

			if ($type == '_') {
				return $this->prefix;
			}

			if (!$this->placeholderArgs) return 'DB_ERROR_NO_VALUE';
			$value = array_pop($this->placeholderArgs);

			if ($value === self::SKIP) {
				$this->placeholderNoValueFound = true;
				return '';
			}

			switch ($type) {

				case 'a':
					if (!$value) $this->placeholderNoValueFound = true;
					if (!is_array($value)) return 'DB_ERROR_VALUE_NOT_ARRAY';
					$parts = array();
					foreach ($value as $k => $v) {
						$v = $v === null ? 'NULL' : $this->escape($v);
						if (!is_int($k)) {
							$k = $this->ident($k);
							$parts[] = "$k=$v";
						} else {
							$parts[] = $v;
						}
					}
					return join(', ', $parts);

				case "#":
					if (!is_array($value)) return $this->ident($value);
					$parts = array();
					foreach ($value as $table => $identifier) {
						if (!is_string($identifier)) return 'DB_ERROR_ARRAY_VALUE_NOT_STRING';
						$parts[] = (!is_int($table) ? $this->ident($table) . '.' : '') . $this->ident($identifier);
					}
					return join(', ', $parts);

				case 'n':
					return empty($value) ? 'NULL' : intval($value);

				case 'b':
					return (bool)$value ? "'1'" : "'0'";

				case '':
					if (!is_scalar($value)) return 'DB_ERROR_VALUE_NOT_SCALAR';
					return $this->escape($value);

				case 'd':
					return intval($value);

				case 'f':
					return str_replace(',', '.', floatval($value));

				case 'l':
					$value = str_replace(array('%', '_'), array('\\%', '\\_'), $value);
					return $this->escape($value, true);

				case 's':
					return $this->escape($value, true);

				case 'x':
					return $value;

			}

			return $this->escape($value);
		}

		if (isset($m[1]) && strlen($block = $m[1])) {
			$prev = @$this->placeholderNoValueFound;
			$block = $this->placeholdersFlow($block);
			$block = $this->placeholderNoValueFound? '' : ' ' . $block . ' ';
			$this->placeholderNoValueFound = $prev;
			return $block;
		}

		return $m[0];
	}

	// экранирование значения
	protected function escape($val, $flag = false)
	{
		$val = $this->link->real_escape_string((string)$val);
		if (!$flag) $val = "'{$val}'";
		return $val;
	}

	// экранирование идентификатора
	protected function ident($val)
	{
		return '`' . str_replace('`', '``', (string)$val) . '`';
	}

	// преобразование выборки
	protected function transform($rows)
	{
		if (is_array($rows) && $rows) {
			$pk = null;
			$ak = array();
			foreach (current($rows) as $fieldName => $dummy) {
				if (0 == strncasecmp($fieldName, self::ARRAY_KEY, strlen(self::ARRAY_KEY))) {
					$ak[] = $fieldName;
				} else if (0 == strncasecmp($fieldName, self::PARENT_KEY, strlen(self::PARENT_KEY))) {
					$pk = $fieldName;
				}
			}
			natsort($ak);
			if ($ak) {
				if ($pk !== null) {
					return $this->transformToForest($rows, $ak[0], $pk);
				}
				return $this->transformToHash($rows, $ak);
			}
		}
		return $rows;
	}
	protected function transformToHash($rows, $arrayKeys)
	{
		$arrayKeys = (array)$arrayKeys;
		$result = array();
		foreach ($rows as $row) {
			$current =& $result;
			foreach ($arrayKeys as $ak) {
				$key = $row[$ak];
				unset($row[$ak]);
				if ($key !== null) {
					$current =& $current[$key];
				} else {
					$tmp = array();
					$current[] =& $tmp;
					$current =& $tmp;
					unset($tmp);
				}
			}
			$current = count($row) > 1 ? $row : array_shift($row);
		}
		return $result;
	}
	protected function transformToForest($rows, $idName, $pidName)
	{
		$children = array();
		$ids = array();
		foreach ($rows as $i => $r) {
			$row =& $rows[$i];
			$id = $row[$idName];
			if ($id === null) {
				continue;
			}
			$pid = $row[$pidName];
			if ($id == $pid) $pid = null;
			$children[$pid][$id] =& $row;
			if (!isset($children[$id])) $children[$id] = array();
			$row['childNodes'] =& $children[$id];
			$ids[$id] = true;
		}
		$forest = array();
		foreach ($rows as $i => $r) {
			$row =& $rows[$i];
			$id = $row[$idName];
			$pid = $row[$pidName];
			if ($pid == $id) $pid = null;
			if (!isset($ids[$pid])) {
				$forest[$row[$idName]] =& $row;
			}
			unset($row[$idName]);
			unset($row[$pidName]);
		}
		return $forest;
	}

}

class DB_Exception extends Exception
{

	public function __construct($code, $text, $query)
	{
		$query = preg_replace("#^\s*|\s*$#uim", '', $query);

		$caller = self::getLastCaller();
		$file = @$caller['file'] ?: 'unknown file';
		$line = @$caller['line'] ?: 0;

		parent::__construct($text . "\n" . $query, $code);
		$this->file = $file;
		$this->line = $line;
	}

	protected function getLastCaller()
	{
		$trace = debug_backtrace();
		foreach ($trace as &$a) unset($a['object']);

		$seen = 0;
		$smart = array();
		for ($i = 0, $n = count($trace); $i < $n; $i++) {
			$t = $trace[$i];
			if (!$t) continue;

			$next = isset($trace[$i+1]) ? $trace[$i+1] : null;

			if (!isset($t['file'])) {
				$t['over_function'] = $trace[$i+1]['function'];
				$t = $t + $trace[$i+1];
				$trace[$i+1] = null;
			}

			if (++$seen < 2) continue;

			if ($next) {
				$caller = (isset($next['class'])? $next['class'].'::' : '') . (isset($next['function'])? $next['function'] : '');
				if (preg_match("/^(?> DB::.* | DB_.*::.* | call_user_func.* )$/six", $caller)) continue;
			}

			return $t;
			$smart[] = $t;
		}

		return false;
	}

}
