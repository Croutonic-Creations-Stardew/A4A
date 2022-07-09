<?php
class GrumpyPdo extends \PDO
{
    /**
     * @var array
     * Default attributes set for database connection.
     */
    protected $default_attributes = array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    );

    public function __construct($host, $user, $pass, $db, $attributes = array(), $charset = "utf8")
    {
        if(!is_array($attributes)) {
            if($attributes == NULL) {
                $attributes = array();
            } else {
                $attributes = $this->default_attributes;
            }
        } else {
            if(empty($attributes)) $attributes = $this->default_attributes;
        }
        parent::__construct("mysql:host={$host};dbname={$db};charset={$charset}", $user, $pass, $attributes);
    }
    public function run($query, $values = array())
    {
        if(!$values) {
            return $this->query($query);
        }
        if(!is_array($values[0])) {
            $stmt = $this->prepare($query);
            $stmt->execute($values);
			return $stmt;
        }
        return $this->multi($query, $values);
    }
    public function multi($query, $values = array())
    {
        if(!is_array($values[0]))
        {
            return false;
        }
        $stmt = $this->prepare($query);
        $alteredRows = 0;
        foreach($values as $value)
        {
            $stmt->execute($value);
            $alteredRows += $stmt->rowCount();
        }
        return $alteredRows;
    }
    /**
     * Quick queries
     * Allows you to run a query without chaining the return type manually. This allows for slightly shorter syntax.
     */
     public function row($query, $values = array())
     {
         return $this->run($query, $values)->fetch();
     }
     public function cell($query, $values = array())
     {
         return $this->run($query, $values)->fetchColumn();
     }
     public function column($query, $values = array())
     {
         return $this->run($query, $values)->fetchAll(PDO::FETCH_COLUMN);
     }
     public function all($query, $values = array())
     {
         return $this->run($query, $values)->fetchAll();
     }
     public function keypair($query, $values = array(), $multiple_rows_per_key = false)
     {
         try {
             $return = $this->run($query, $values)->fetchAll(PDO::FETCH_KEY_PAIR);
         } catch(PDOException $e) {
             if($multiple_rows_per_key) {
                 $return = $this->run($query, $values)->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
             } else {
                 $return = $this->run($query, $values)->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE);
             }
         }
         return $return;
     }

     public function parse_data_array($array, $type) {
         switch($type) {
             case 'insert':

                 $keys = implode(', ', array_keys($array));
                 $placeholders = ":" . implode(', :', array_keys($array));

                 return ['keys' => $keys, 'placeholders' => $placeholders];
             case 'update': //generates SET clause, WHERE is separate and needs to be added to the passed params.

                 $return_arr = [];
                 foreach($array as $key => $value) {
                     $return_arr[] = "`{$key}`=:{$key}";
                 }
                 return implode(', ', $return_arr);

             case 'in':
                 return implode(',', array_fill(0, count($array), '?'));
             default:
                 return false;
         }
     }

     public function insert($table, $params) {
         $parsed_data = $this->parse_data_array($params, 'insert');
         //die("<pre>".print_r($params, true)."</pre>");
         $this->run("INSERT INTO {$table} ({$parsed_data['keys']}) VALUES ({$parsed_data['placeholders']})", $params);
         //var_dump("INSERT INTO {$table} ({$parsed_data['keys']}) VALUES ({$parsed_data['placeholders']}) - " . json_encode($params));
         return $this->lastInsertId();
     }
}
