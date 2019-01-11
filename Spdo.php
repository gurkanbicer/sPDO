<?php
/**
 * Simple PHP Pdo Database Class
 * 
 * @author Gürkan Biçer <gurkan@grkn.co>
 * @version 1.0
 * @since 2015-08-27
 * @link https://gitlab.com/gurkanbicer/spdo
 */

    #require_once APPPATH . 'config/database.php'; 
    $GLOBALS['db'] = $db;

    Class Spdo {

        protected $conn = null;
        protected $username = false;
        protected $password = false;
        protected $hostname = false;
        protected $database = false;
        protected $char_set = 'utf8';
        protected $msg = array();
        protected $numRows = 0;
        protected $temp = array();
        protected $errors = array();
        protected $lastQuery = array();

        public function Spdo() {
            $this->temp['db'] = $GLOBALS['db'];
            $this->assignErrorMsgs();
        }

        private function createInstance($key = "default") {
            try {
                if (!is_string($key)) throw new Exception(0);
                if (empty($key)) throw new Exception(1);
                if (empty($this->temp['db'][$key])) throw new Exception(2);

                if ($this->check('username', $this->temp['db'][$key]['username']) === 1)
                    $this->username = $this->temp['db'][$key]['username'];
                else
                    throw new Exception(3);

                /*if ($this->check('password', $this->temp['db'][$key]['password']) === 1)
                    $this->password = $this->temp['db'][$key]['password'];
                else
                    throw new Exception(4);*/
                    
                $this->password = $this->temp['db'][$key]['password'];

                if ($this->check('hostname', $this->temp['db'][$key]['hostname']) === 1)
                    $this->hostname = $this->temp['db'][$key]['hostname'];
                else
                    throw new Exception(5);

                if ($this->check('database', $this->temp['db'][$key]['database']) === 1)
                    $this->database = $this->temp['db'][$key]['database'];
                else
                    throw new Exception(6);

                if ($this->check('char_set', $this->temp['db'][$key]['char_set']) === 1)
                    $this->char_set = $this->temp['db'][$key]['char_set'];
                else
                    throw new Exception(7);

                return true;
            } catch (Exception $e) {
                $this->errors[] = $this->msg[$e->getMessage()];
                return false;
            }
        }

        private function connect($key = "default") {
            try {
                if ($this->createInstance($key) === false)
                    throw new PDOException('Error: function connect() in mysql connection is failed.');

                $this->conn = new PDO('mysql:host=' . $this->hostname . ';dbname=' . $this->database,
                    $this->username, $this->password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '$this->char_set'"));
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
                return true;
            } catch (PDOException $e) {
                $this->errors[] = $e->getMessage();
                return false;
            }
        }

        private function sendQuery($query, $binding = null, $key = "default") {
            try {
                if ($this->connect($key) === false)
                    throw new Exception('Error: Sql query will not be running. Because, it not connected to MySQL.');
                if (!is_string($query) || empty($query)) throw new Exception(8);
                if (is_array($binding) && !empty($binding)) {
                    $statement = $this->conn->prepare( $query );
                    if ( !$statement->execute( $binding ) )
                        return false;
                    else
                        return $statement;
                } else {
                    if ( $statement = $this->conn->query( $query ) )
                        return $statement;
                    else
                        return false;
                }
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
                return false;
            }
        }

        private function assignErrorMsgs() {
            $this->msg = array(
                0 => "Spdo Error: function createInstance(): database config key's value data type is incorrect.",
                1 => "Spdo Error: function createInstance(): database config key's value is empty.",
                2 => "Spdo Error: function createInstance(): database config key's temporary value is empty.",
                3 => "Spdo Error: function createInstance(): mysql username is incorrect.",
                4 => "Spdo Error: function createInstance(): mysql user password is incorrect.",
                5 => "Spdo Error: function createInstance(): mysql hostname is incorrect.",
                6 => "Spdo Error: function createInstance(): mysql database is incorrect.",
                7 => "Spdo Error: function createInstance(): mysql char set is incorrect.",
                8 => "Spdo Error: Sql query is empty or incorrect.",
                9 => "Spdo Error: Sql query is incorrect.",
                10 => "Spdo Error: Table parameter's value is empty or incorrect.",
                11 => "Spdo Error: Data parameter's value is empty or incorrect.",
            );
        }

        private function check($type, $data) {
            switch ($type) {
                case 'username':
                case 'database':
                    return preg_match('/^([a-zA-Z0-9\_]+)$/i',$data);
                    break;
                case 'password':
                    return preg_match('/^([a-zA-Z0-9\_\-\?\*\.\:\;\,\}\{\=\)\(\/\[\]\<\>\&\%\+\$\#\^\!\|]+)$/i',$data);
                    break;
                case 'hostname':
                    return preg_match('/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/i',$data);
                    break;
                case 'char_set':
                    return preg_match('/^([a-zA-Z0-9\-]+)$/i',$data);
                    break;
            }
        }

        private function xmlEncode($array, $level=1) {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';

            if ($level==1) {
            $xml .= "<result>\n";
            }

            foreach ($array as $key=>$value) {
                $key = strtolower($key);
                if (is_object($value)) {$value=get_object_vars($value);}// convert object to array
                $xml .= '<item>';
                if (is_array($value)) {
                    $multi_tags = false;
                    foreach($value as $key2=>$value2) {

                        if (is_object($value2)) {$value2=get_object_vars($value2);} // convert object to array
                        if (is_array($value2)) {
                            $xml .= str_repeat("\t",$level)."<$key>\n";
                            $xml .= $this->xmlEncode($value2, $level+1);
                            $xml .= str_repeat("\t",$level)."</$key>\n";
                            $multi_tags = true;
                        } else {
                            if (trim($value2)!='') {
                                if (htmlspecialchars($value2)!=$value2) {
                                    $xml .= str_repeat("\t",$level).
                                        "<$key2><![CDATA[$value2]]>". // changed $key to $key2... didn't work otherwise.
                                        "</$key2>\n";
                                } else {
                                    $xml .= str_repeat("\t",$level).
                                        "<$key2>$value2</$key2>\n"; // changed $key to $key2
                                }
                            }
                            $multi_tags = true;
                        }

                    }
                    if (!$multi_tags and count($value)>0) {
                        $xml .= str_repeat("\t",$level)."<$key>\n";
                        $xml .= $this->xmlEncode($value, $level+1);
                        $xml .= str_repeat("\t",$level)."</$key>\n";
                    }

                } else {
                    if (trim($value)!='') {
                        echo "value=$value<br>";
                        if (htmlspecialchars($value)!=$value) {
                            $xml .= str_repeat("\t",$level)."<$key>".
                                "<![CDATA[$value]]></$key>\n";
                        } else {
                            $xml .= str_repeat("\t",$level).
                                "<$key>$value</$key>\n";
                        }
                    }
                }
                $xml .= '</item>';
            }


            if ($level==1) {
            $xml .= "</result>\n";
            }
            return $xml;
        }

        public function errors() {
            echo '<pre><code>';
            var_dump($this->errors);
            echo '</code></pre>';
        }

        public function getErrors() {
            return $this->errors;
        }

        public function getLastQuery() {
            return $this->lastQuery;
        }

        public function numRows() {
            return $this->numRows;
        }

        public function getResults($query, $options = array()) {
            try {
                $time = time();

                $this->lastQuery = array(
                    'query' => $query,
                    'bindValues' => @$options['bindValues'],
                    'configKey' => @$options['configKey'],
                    'returnDataType' => @$options['returnDataType'],
                );

                if (empty($query) || !is_string($query))
                    throw new Exception(8);

                if (!isset($options['configKey'])
                    or !is_string($options['configKey']))
                    $options['configKey'] = "default";

                if (!empty($options['bindValues']) && is_array($options['bindValues']))
                    $this->temp['query'][$time] = $this->sendQuery($query, $options['bindValues'], $options['configKey']);
                else
                    $this->temp['query'][$time] = $this->sendQuery($query, null, $options['configKey']);

                if (!$this->temp['query'][$time])
                    $this->numRows = 0;
                else
                    $this->numRows = $this->temp['query'][$time]->rowCount();

                if (!isset($options['returnDataType'])
                    or !is_string($options['returnDataType']))
                    $options['returnDataType'] = "object";

                if (!$this->temp['query'][$time])
                    throw new Exception(9);

                switch ($options['returnDataType']) {
                    case 'array':
                        return $this->temp['query'][$time]->fetchAll(PDO::FETCH_ASSOC);
                        break;
                    case 'object':
                        return $this->temp['query'][$time]->fetchAll(PDO::FETCH_OBJ);
                        break;
                    case 'json':
                        return json_encode($this->temp['query'][$time]->fetchAll(PDO::FETCH_ASSOC));
                        break;
                    case 'xml':
                        return $this->xmlEncode($this->temp['query'][$time]->fetchAll(PDO::FETCH_ASSOC), 1);
                        break;

                    default:
                        return $this->temp['query'][$time]->fetchAll(PDO::FETCH_OBJ);
                }

            } catch (Exception $e) {
                $this->errors[] = $this->msg[$e->getMessage()];
                return false;
            }
        }

        public function getRow($query, $options = array()) {
            try {
                $time = time();

                $this->lastQuery = array(
                    'query' => $query,
                    'bindValues' => @$options['bindValues'],
                    'configKey' => @$options['configKey'],
                    'returnDataType' => @$options['returnDataType'],
                );

                if (empty($query) || !is_string($query))
                    throw new Exception(8);

                if (!isset($options['configKey'])
                    or !is_string($options['configKey']))
                    $options['configKey'] = "default";

                if (!empty($options['bindValues']) && is_array($options['bindValues']))
                    $this->temp['query'][$time] = $this->sendQuery($query, $options['bindValues'], $options['configKey']);
                else
                    $this->temp['query'][$time] = $this->sendQuery($query, null, $options['configKey']);

                if (!$this->temp['query'][$time])
                    $this->numRows = 0;
                else
                    $this->numRows = $this->temp['query'][$time]->rowCount();

                if (!isset($options['returnDataType'])
                    or !is_string($options['returnDataType']))
                    $options['returnDataType'] = "object";

                if (!$this->temp['query'][$time])
                    throw new Exception(9);

                switch ($options['returnDataType']) {
                    case 'array':
                        return $this->temp['query'][$time]->fetch(PDO::FETCH_ASSOC);
                        break;
                    case 'object':
                        return $this->temp['query'][$time]->fetch(PDO::FETCH_OBJ);
                        break;
                    case 'json':
                        return json_encode($this->temp['query'][$time]->fetch(PDO::FETCH_ASSOC));
                        break;
                    case 'xml':
                        return $this->xmlEncode($this->temp['query'][$time]->fetch(PDO::FETCH_ASSOC), 1);
                        break;

                    default:
                        return $this->temp['query'][$time]->fetch(PDO::FETCH_OBJ);
                }

            } catch (Exception $e) {
                $this->errors[] = $this->msg[$e->getMessage()];
                return false;
            }
        }

        public function getVar($query, $options = array()) {
            try {
                $time = time();

                $this->lastQuery = array(
                    'query' => $query,
                    'bindValues' => @$options['bindValues'],
                    'configKey' => @$options['configKey'],
                    'returnDataType' => null,
                );

                if (empty($query) || !is_string($query))
                    throw new Exception(8);

                if (!isset($options['configKey'])
                    or !is_string($options['configKey']))
                    $options['configKey'] = "default";

                if (!empty($options['bindValues']) && is_array($options['bindValues']))
                    $this->temp['query'][$time] = $this->sendQuery($query, $options['bindValues'], $options['configKey']);
                else
                    $this->temp['query'][$time] = $this->sendQuery($query, null, $options['configKey']);

                if (!$this->temp['query'][$time])
                    $this->numRows = 0;
                else
                    $this->numRows = $this->temp['query'][$time]->rowCount();

                if (!$this->temp['query'][$time])
                    throw new Exception(9);

                return $this->temp['query'][$time]->fetchColumn();

            } catch (Exception $e) {
                $this->errors[] = $this->msg[$e->getMessage()];
                return false;
            }
        }

        public function execute($query, $options = array()) {
            try {
                $time = time();

                $this->lastQuery = array(
                    'query' => $query,
                    'bindValues' => @$options['bindValues'],
                    'configKey' => @$options['configKey'],
                    'returnDataType' => null,
                );

                if (empty($query) || !is_string($query))
                    throw new Exception(8);

                if (!isset($options['configKey'])
                    or !is_string($options['configKey']))
                    $options['configKey'] = "default";
                if (!empty($options['bindValues']) && is_array($options['bindValues']))
                    $this->temp['query'][$time] = $this->sendQuery($query, $options['bindValues'], $options['configKey']);
                else
                    $this->temp['query'][$time] = $this->sendQuery($query, null, $options['configKey']);

                if (!$this->temp['query'][$time]) {
                    throw new Exception(9);
                } else {
                    if (stristr($query, 'insert')) return $this->conn->lastInsertId();
                    else return $this->temp['query'][$time]->rowCount();
                }

            } catch (Exception $e) {
                $this->errors[] = $this->msg[$e->getMessage()];
                return false;
            }
        }

        public function insert($tbl, $data, $options = array()) {
            try {
                $time = time();

                if (empty($tbl) || !is_string($tbl))
                    throw new Exception(10);

                if (empty($data) || !is_array($data))
                    throw new Exception(11);

                $query = "INSERT INTO $tbl SET";
                $keys = implode(' = ?, ', array_keys($data)) . " = ?";
                $vals = array_values($data);
                $query .= " $keys";

                $options['bindValues'] = $vals;

                if (!isset($options['configKey'])
                    or !is_string($options['configKey']))
                    $options['configKey'] = "default";

                $this->temp['query'][$time] = $this->sendQuery($query, $options['bindValues'], $options['configKey']);

                $this->lastQuery = array(
                    'query' => $query,
                    'bindValues' => @$options['bindValues'],
                    'configKey' => @$options['configKey'],
                    'returnDataType' => null,
                );

                if (!$this->temp['query'][$time])
                    throw new Exception(9);
                else
                    return $this->conn->lastInsertId();

            } catch (Exception $e) {
                $this->errors[] = $this->msg[$e->getMessage()];
                return false;
            }
        }

        public function update($tbl, $data, $where = array(), $whereBind = array(), $options = array()) {
            try {
                $time = time();

                if (empty($tbl) || !is_string($tbl))
                    throw new Exception(10);

                if (empty($data) || !is_array($data))
                    throw new Exception(11);

                $query = "UPDATE $tbl SET";
                $keys = implode(' = ?, ', array_keys($data)) . " = ?";
                $query .= " $keys";
                $vals = array_values($data);

                $options['bindValues'] = $vals;

                if (is_array($where)
                    && !empty($where)
                    && is_array($whereBind)
                    && !empty($whereBind)) {
                    $query .= " WHERE " . implode(' AND ',$where);
                    foreach ($whereBind as $whereBindVal)
                        $options['bindValues'][] = $whereBindVal;
                }

                if (!isset($options['configKey'])
                    or !is_string($options['configKey']))
                    $options['configKey'] = "default";

                $this->temp['query'][$time] = $this->sendQuery($query, $options['bindValues'], $options['configKey']);

                $this->lastQuery = array(
                    'query' => $query,
                    'bindValues' => @$options['bindValues'],
                    'configKey' => @$options['configKey'],
                    'returnDataType' => null,
                );

                if (!$this->temp['query'][$time])
                    throw new Exception(9);
                else
                    return $this->temp['query'][$time]->rowCount();

            } catch (Exception $e) {
                $this->errors[] = $this->msg[$e->getMessage()];
                return false;
            }
        }

        public function delete($tbl, $where = array(), $whereBind = array(), $options = array()) {
            try {
                $time = time();

                if (empty($tbl) || !is_string($tbl))
                    throw new Exception(10);

                $query = "DELETE FROM $tbl";

                if (is_array($where)
                    && !empty($where)
                    && is_array($whereBind)
                    && !empty($whereBind)) {
                    $query .= " WHERE " . implode(' AND ',$where);
                    foreach ($whereBind as $whereBindVal)
                        $options['bindValues'][] = $whereBindVal;
                }

                if (!isset($options['configKey'])
                    or !is_string($options['configKey']))
                    $options['configKey'] = "default";

                $this->temp['query'][$time] = $this->sendQuery($query, $options['bindValues'], $options['configKey']);

                $this->lastQuery = array(
                    'query' => $query,
                    'bindValues' => @$options['bindValues'],
                    'configKey' => @$options['configKey'],
                    'returnDataType' => null,
                );

                if (!$this->temp['query'][$time])
                    throw new Exception(9);
                else
                    return $this->temp['query'][$time]->rowCount();

            } catch (Exception $e) {
                $this->errors[] = $this->msg[$e->getMessage()];
                return false;
            }
        }

    }

/* End of file */