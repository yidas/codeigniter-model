<?php

namespace yidas;

/**
 * Base Model
 *
 * @author   Nick Tsai <myintaer@gmail.com>
 * @version  2.7.0.1
 * @see      https://github.com/yidas/codeigniter-model
 */
class Model extends \CI_Model
{
    /**
     * Database Configuration for read-write master
     * 
     * @var object|string|array CI DB ($this->db as default), CI specific group name or CI database config array
     */
    protected $database = "";

    /**
     * Database Configuration for read-only slave
     * 
     * @var object|string|array CI DB ($this->db as default), CI specific group name or CI database config array
     */
    protected $databaseRead = "";
    
    /**
     * Table name
     *
     * @var string
     */
    protected $table = "";

    /**
     * Table alias name
     *
     * @var string
     */
    protected $alias = null;

    /**
     * Primary key of table
     *
     * @var string Field name of single column primary key
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    protected $timestamps = true;

    /**
     * Date format for timestamps.
     *
     * @var string unixtime|datetime
     */
    protected $dateFormat = 'datetime';

    /**
     * @string Feild name for created_at, empty is disabled.
     */
    const CREATED_AT = 'created_at';

    /**
     * @string Feild name for updated_at, empty is disabled.
     */
    const UPDATED_AT = 'updated_at';

    /**
     * CREATED_AT triggers UPDATED_AT.
     *
     * @var bool
     */
    protected $createdWithUpdated = true;

    /**
     * @var string Feild name for SOFT_DELETED, empty is disabled.
     */
    const SOFT_DELETED = '';

    /**
     * The actived value for SOFT_DELETED
     *
     * @var mixed
     */
    protected $softDeletedFalseValue = '0';

    /**
     * The deleted value for SOFT_DELETED
     *
     * @var mixed
     */
    protected $softDeletedTrueValue = '1';

    /**
     * This feature is actvied while having SOFT_DELETED
     *
     * @string Feild name for deleted_at, empty is disabled.
     */
    const DELETED_AT = '';

    /**
     * @var string Validator class with nampspace
     */
    public $validator = '\GUMP';

    /**
     * @var object database connection for write
     */
    protected $_db;

    /**
     * @var object database connection for read (Salve)
     */
    protected $_dbr;

    /**
     * @var object database caches by database key for write
     */
    protected static $_dbCaches = [];
    
    /**
     * @var object database caches by database key for read (Salve)
     */
    protected static $_dbrCaches = [];

    /**
     * @var array Validation errors (depends on validator driver)
     */
    private $_errors;

    /**
     * @var bool SOFT_DELETED one time switch
     */
    private $_withoutSoftDeletedScope = false;

    /**
     * @var bool Global Scope one time switch
     */
    private $_withoutGlobalScope = false;

    /**
     * Constructor
     */
    function __construct()
    {
        /* Database Connection Setting */
        // Master
        if ($this->database) {
            if (is_object($this->database)) {
                // CI DB Connection
                $this->_db = $this->database;
            } 
            elseif (is_string($this->database)) {
                // Cache Mechanism
                if (isset(self::$_dbCaches[$this->database])) {
                    $this->_db = self::$_dbCaches[$this->database];
                } else {
                    // CI Database Configuration
                    $this->_db = $this->load->database($this->database, true);
                    self::$_dbCaches[$this->database] = $this->_db;
                }
            }
            else {
                // Config array for each Model
                $this->_db = $this->load->database($this->database, true);
            }
        } else {
            // CI Default DB Connection
            $this->_db = $this->_getDefaultDB();
        }
        // Slave
        if ($this->databaseRead) {
            if (is_object($this->databaseRead)) {
                // CI DB Connection
                $this->_dbr = $this->databaseRead;
            } 
            elseif (is_string($this->databaseRead)) {
                // Cache Mechanism
                if (isset(self::$_dbrCaches[$this->databaseRead])) {
                    $this->_dbr = self::$_dbrCaches[$this->databaseRead];
                } else {
                    // CI Database Configuration
                    $this->_dbr = $this->load->database($this->databaseRead, true);
                    self::$_dbrCaches[$this->databaseRead] = $this->_dbr;
                }
            }
            else {
                // Config array for each Model
                $this->_dbr = $this->load->database($this->databaseRead, true);
            }
        } else {
            // CI Default DB Connection
            $this->_dbr = $this->_getDefaultDB();
        }
        
        /* Table Name Guessing */
        if (!$this->table) {
            $this->table = str_replace('_model', '', strtolower(get_called_class()));
        }
    }

    /**
     * Get Master Database Connection
     * 
     * @return object CI &DB
     */
    public function getDatabase()
    {
        return $this->_db;
    }

    /**
     * Get Slave Database Connection
     * 
     * @return object CI &DB
     */
    public function getDatabaseRead()
    {
        return $this->_dbr;
    }

    /**
     * Alias of getDatabase()
     */
    public function getDB()
    {
        return $this->getDatabase();
    }

    /**
     * Alias of getDatabaseRead()
     */
    public function getDBR()
    {
        return $this->getDatabaseRead();
    }

    /**
     * Alias of getDatabaseRead()
     */
    public function getBuilder()
    {
        return $this->getDatabaseRead();
    }

    /**
     * Get table name
     *
     * @return string Table name
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Alias of getTable()
     */
    public function tableName()
    {
        return $this->getTable();
    }

    /**
     * Validation - Get errors
     *
     * @return mixed Rule data for Validator
     */
    public function rules()
    {
        return [];
    }

    /**
     * Validation - Get errors
     *
     * @return mixed Errors data from Validator
     */
    public function validate($data=[])
    {
        // make a validator driver
        /*
        $result = $this->validator::make($data, $this->rules());
        if ($result === true) {
            return $result;
        } else {
            $this->_errors = $result;
            return false;
        }
        */
    }

    /**
     * Validation - Get errors
     *
     * @return mixed Errors data from Validator
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Set table alias
     *
     * @param string Table alias name
     * @return self
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
        
        return $this;
    }

    /**
     * Create an existent CI Query Builder instance with Model features for query purpose.
     *
     * @param bookl $withAll withAll() switch helper
     * @return object CI_DB_query_builder
     * @example 
     *  $posts = $this->PostModel->find()
     *      ->where('is_public', '1')
     *      ->limit(0,25)
     *      ->order_by('id')
     *      ->get()
     *      ->result_array();
     * @example 
     *  // Without all featured conditions for next find()
     *  $posts = $this->PostModel->find(true)
     *      ->where('is_deleted', '1')
     *      ->get()
     *      ->result_array();
     *  // This is equal to withAll() method
     *  $this->PostModel->withAll()->find();
     *
     */
    public function find($withAll=false)
    {
        // Alias option for FROM
        $sqlFrom = ($this->alias) ? "{$this->table} AS {$this->alias}" : $this->table;
        
        $this->_dbr->from($sqlFrom);

        // WithAll helper
        if ($withAll===true) {
            $this->withAll();
        }

        // Scope condition
        $this->_addGlobalScopeCondition();

        // Soft Deleted condition
        $this->_addSoftDeletedCondition();

        return $this->_dbr;
    }

    /**
     * Create an CI Query Builder instance without Model Filters for query purpose.
     *
     * @return object CI_DB_query_builder
     */
    public function forceFind()
    {
        return $this->withAll()->find();
    }

    /**
     * Return a single record array by a primary key or an array of column values with Model Filters.
     *
     * @param mixed $condition Refer to _findByCondition() for the explanation of this parameter
     * @return array Result
     * @example
     *  $post = $this->PostModel->findOne(123);
     */
    public function findOne($condition)
    {
        $query = $this->_findByCondition($condition)->limit(1)->get();

        return $query->row_array();
    }

    /**
     * Return a list of records that match the specified primary key value(s) or a set of column values with Model Filters.
     *
     * @param mixed $condition Refer to _findByCondition() for the explanation 
     * @return array Result
     * @example
     *  $post = $this->PostModel->findAll([3,21,135]);
     */
    public function findAll($condition)
    {
        $query = $this->_findByCondition($condition)->get();

        return $query->result_array();
    }

    /**
     * reset an CI Query Builder instance with Model.
     *
     * @return object Self
     * @example 
     *  $this->Model->reset()->find();
     */
    public function reset()
    {
        // Reset query
        $this->_db->reset_query();
        $this->_dbr->reset_query();
        
        return $this;
    }

    /**
     * Insert a row with Timestamps feature into the associated database table using the attribute values of this record.
     * 
     * @param array $attributes
     * @return bool Result
     * @example
     *  $result = $this->Model->insert([
     *      'name' => 'Nick Tsai',
     *      'email' => 'myintaer@gmail.com',
     *  ]);
     */
    public function insert($attributes)
    {
        $this->_attrEventBeforeInsert($attributes);

        return $this->_db->insert($this->table, $attributes);
    }

    /**
     * Insert a batch of rows with Timestamps feature into the associated database table using the attribute values of this record.
     * 
     * @param array $data The rows to be batch inserted
     * @return int Number of rows inserted or FALSE on failure
     * @example
     *  $result = $this->Model->batchInsert([
     *      ['name' => 'Nick Tsai', 'email' => 'myintaer@gmail.com'],
     *      ['name' => 'Yidas', 'email' => 'service@yidas.com']
     *  ]);
     */
    public function batchInsert($data)
    {
        foreach ($data as $key => &$attributes) {

            $this->_attrEventBeforeInsert($attributes);
        }

        return $this->_db->insert_batch($this->table, $data);
    }

    /**
     * Get the insert ID number when performing database inserts.
     *
     * @return integer Last insert ID
     */
    public function getLastInsertID()
    {
        return $this->getDB()->insert_id();
    }

    /**
     * Replace a row with Timestamps feature into the associated database table using the attribute values of this record.
     * 
     * @param array $attributes
     * @return bool Result
     * @example
     *  $result = $this->Model->replace([
     *      'id' => 1,
     *      'name' => 'Nick Tsai',
     *      'email' => 'myintaer@gmail.com',
     *  ]);
     */
    public function replace($attributes)
    {
        $this->_attrEventBeforeInsert($attributes);

        return $this->_db->replace($this->table, $attributes);
    }

    /**
     * Save the changes with Timestamps feature to the selected record(s) into the associated database table.
     * 
     * @param array $attributes
     * @param mixed $condition Refer to _findByCondition() for the explanation 
     * @return bool Result
     *
     * @example    
     *  $this->Model->update(['status'=>'off'], 123)
     * @example
     *  // Query builder ORM usage
     *  $this->Model->find()->where('id', 123);
     *  $this->Model->update(['status'=>'off']);
     */
    public function update($attributes, $condition=NULL)
    {
        // Model Condition
        $query = $this->_findByCondition($condition);

        $attributes = $this->_attrEventBeforeUpdate($attributes);

        // Pack query then move it to write DB from read DB
        $sql = $this->_dbr->set($attributes)->get_compiled_update();
        $this->_dbr->reset_query();

        return $this->_db->query($sql);
    }

    /**
     * Update a batch of update queries into combined query strings.
     *
     * @param array $dataSet [[[Attributes], [Condition]], ]
     * @param integer $maxLenth MySQL max_allowed_packet
     * @return integer Count of sucessful query 
     * @example 
     *  $result = $this->Model->batchUpdate([
     *      [['title'=>'A1', 'modified'=>'1'], ['id'=>1]],
     *      [['title'=>'A2', 'modified'=>'1'], ['id'=>2]],
     *  ];);
     */
    public function batchUpdate(Array $dataSet, $maxLength=4*1024*1024)
    {
        $count = 0;
        $sqlBatch = '';
        
        foreach ($dataSet as $key => &$each) {

            // Data format
            list($attributes, $condition) = $each;

            // Model Condition
            $query = $this->_findByCondition($condition);

            $attributes = $this->_attrEventBeforeUpdate($attributes);

            // Pack query then move it to write DB from read DB
            $sql = $this->_dbr->set($attributes)->get_compiled_update();
            $this->_dbr->reset_query();

            // Max length process
            if (strlen($sqlBatch) >= $maxLength) {
                // Each batch of query
                $result = $this->_db->query($sqlBatch);
                $sqlBatch = "";
                $count = ($result) ? $count + 1 : $count;
            }
            
            // Keep Combining query
            $sqlBatch .= "{$sql};\n";
        }

        // Last batch of query
        $result = $this->_db->query($sqlBatch);

        return ($result) ? $count + 1 : $count;
    }

    /**
     * Delete the selected record(s) with Timestamps feature into the associated database table.
     * 
     * @param mixed $condition Refer to _findByCondition() for the explanation 
     * @param boolean $forceDelete Force to hard delete
     * @param array $attributes Extended attributes for Soft Delete Mode
     * @return bool Result
     *
     * @example    
     *  $this->Model->delete(123);
     * @example
     *  // Query builder ORM usage
     *  $this->Model->find()->where('id', 123);
     *  $this->Model->delete();
     * @example  
     *  // Force delete for SOFT_DELETED mode 
     *  $this->Model->delete(123, true);
     */
    public function delete($condition=NULL, $forceDelete=false, $attributes=[])
    {
        // Model Condition by $forceDelete switch
        $query = ($forceDelete)
            ? $this->withTrashed()->_findByCondition($condition)
            : $this->_findByCondition($condition);

        /* Soft Delete Mode */
        if (static::SOFT_DELETED 
            && isset($this->softDeletedTrueValue) 
            && !$forceDelete) {
            
            // Mark the records as deleted
            $attributes[static::SOFT_DELETED] = $this->softDeletedTrueValue;

            $attributes = $this->_attrEventBeforeDelete($attributes);

            // Pack query then move it to write DB from read DB
            $sql = $this->_dbr->set($attributes)->get_compiled_update();
            $this->_dbr->reset_query();

        } else {

            /* Hard Delete */
            // Pack query then move it to write DB from read DB
            $sql = $this->_dbr->get_compiled_delete();
            $this->_dbr->reset_query();
        }
        
        return $this->_db->query($sql);
    }

    /**
     * Force Delete the selected record(s) with Timestamps feature into the associated database table.
     * 
     * @param mixed $condition Refer to _findByCondition() for the explanation 
     * @return mixed CI delete result of DB Query Builder
     *
     * @example    
     *  $this->Model->forceDelete(123)
     * @example
     *  // Query builder ORM usage
     *  $this->Model->find()->where('id', 123);
     *  $this->Model->forceDelete();
     */
    public function forceDelete($condition=NULL)
    {
        return $this->delete($condition, true);
    }

    /**
     * Get the number of affected rows when doing “write” type queries (insert, update, etc.).
     *
     * @return integer Last insert ID
     */
    public function getAffectedRows()
    {
        return $this->getDB()->affected_rows();
    }

    /**
     * Restore SOFT_DELETED field value to the selected record(s) into the associated database table.
     * 
     * @param mixed $condition Refer to _findByCondition() for the explanation 
     * @return bool Result
     *
     * @example    
     *  $this->Model->restore(123)
     * @example
     *  // Query builder ORM usage
     *  $this->Model->withTrashed()->find()->where('id', 123);
     *  $this->Model->restore();
     */
    public function restore($condition=NULL)
    {
        // Model Condition with Trashed
        $query = $this->withTrashed()->_findByCondition($condition);

        /* Soft Delete Mode */
        if (static::SOFT_DELETED 
            && isset($this->softDeletedFalseValue)) {
            
            // Mark the records as deleted
            $attributes[static::SOFT_DELETED] = $this->softDeletedFalseValue;

            return $query->update($this->table, $attributes);

        } else {

            return false;
        }
    }

    /**
     * Lock the selected rows in the table for updating.
     * 
     * sharedLock locks only for write, lockForUpdate also prevents them from being selected
     *
     * @example 
     *  $this->Model->find()->where('id', 123)
     *  $result = $this->Model->lockForUpdate()->row_array();
     * @example
     *  // This transaction block will lock selected rows for next same selected
     *  // rows with `FOR UPDATE` lock:
     *  $this->Model->getDB()->trans_start();
     *  $this->Model->find()->where('id', 123)
     *  $result = $this->Model->lockForUpdate()->row_array();
     *  $this->Model->getDB()->trans_complete();  
     * 
     * @return object CI_DB_result
     */
    public function lockForUpdate()
    {
        // Pack query then move it to write DB from read DB for transaction
        $sql = $this->_dbr->get_compiled_select();
        $this->_dbr->reset_query();

        return $this->_db->query("{$sql} FOR UPDATE");
    }

    /**
     * Share lock the selected rows in the table.
     * 
     * @example 
     *  $this->Model->find()->where('id', 123)
     *  $result = $this->Model->sharedLock()->row_array();'
     * 
     * @return object CI_DB_result
     */
    public function sharedLock()
    {
        // Pack query then move it to write DB from read DB for transaction
        $sql = $this->_dbr->get_compiled_select();
        $this->_dbr->reset_query();

        return $this->_db->query("{$sql} LOCK IN SHARE MODE");
    }

    /**
     * Without SOFT_DELETED query conditions for next find()
     *
     * @return object Self
     * @example 
     *  $this->Model->withTrashed()->find();
     */
    public function withTrashed()
    {
        $this->_withoutSoftDeletedScope = true;

        return $this;
    }

    /**
     * Without Global Scopes query conditions for next find()
     *
     * @return object Self
     * @example 
     *  $this->Model->withoutGlobalScopes()->find();
     */
    public function withoutGlobalScopes()
    {
        $this->_withoutGlobalScope = true;

        return $this;
    }

    /**
     * Without all query conditions for next find()
     * That is, with all set of Models for next find()
     *
     * @return object Self
     * @example 
     *  $this->Model->withAll()->find();
     */
    public function withAll()
    {
        // Turn off switchs of all featured conditions
        $this->withTrashed();
        $this->withoutGlobalScopes();

        return $this;
    }

    /**
     * Index by Key
     *
     * @param array  $array Array data for handling
     * @param string $key  Array key for index key
     * @param bool   $obj2Array Object converts to array if is object
     * @return array Result with indexBy Key
     * @example 
     *  $records = $this->Model->findAll();
     *  $this->Model->indexBy($records, 'sn');
     */
    public static function indexBy(Array &$array, $key=null, $obj2Array=false)
    {
        // Use model instance's primary key while no given key
        $key = ($key) ?: (new static())->primaryKey;

        $tmp = [];
        foreach ($array as $row) {
            // Array & Object types support 
            if (is_object($row) && isset($row->$key)) {
                
                $tmp[$row->$key] = ($obj2Array) ? (array)$row : $row;
            } 
            elseif (is_array($row) && isset($row[$key])) {
                
                $tmp[$row[$key]] = $row;
            }
        }
        return $array = $tmp;
    }

    /**
     * Query Scopes Handler
     *
     * @return bool Result
     */
    protected function _globalScopes()
    {
        // Events for inheriting

        return true;
    }

    /**
     * Attributes handle function for each Insert
     *
     * @param array $attributes
     * @return array Addon $attributes of pointer
     */
    protected function _attrEventBeforeInsert(&$attributes)
    {
        $this->_formatDate(static::CREATED_AT, $attributes);

        // Trigger UPDATED_AT
        if ($this->createdWithUpdated) {
            
            $this->_formatDate(static::UPDATED_AT, $attributes);
        }

        return $attributes;
    }

    /**
     * Attributes handle function for Update
     *
     * @param array $attributes
     * @return array Addon $attributes of pointer
     */
    protected function _attrEventBeforeUpdate(&$attributes)
    {
        $this->_formatDate(static::UPDATED_AT, $attributes);

        return $attributes;
    }

    /**
     * Attributes handle function for Delete
     *
     * @param array $attributes
     * @return array Addon $attributes of pointer
     */
    protected function _attrEventBeforeDelete(&$attributes)
    {
        $this->_formatDate(static::DELETED_AT, $attributes);

        return $attributes;
    }

    /**
     * Finds record(s) by the given condition with a fresh query.
     *
     * This method is internally called by findOne(), findAll(), update(), delete(), etc.
     * The query will be reset to start a new scope if the condition is used.
     * 
     * @param mixed Primary key value or a set of column values
     * @return object CI_DB_query_builder
     * @internal
     * @example 
     *  // find a single customer whose primary key value is 10
     *  $this->_findByCondition(10);
     *
     *  // find the customers whose primary key value is 10, 11 or 12.
     *  $this->_findByCondition([10, 11, 12]);
     *
     *  // find the first customer whose age is 30 and whose status is 1
     *  $this->_findByCondition(['age' => 30, 'status' => 1]);
     */
    protected function _findByCondition($condition=NULL)
    {
        // Reset Query if condition existed
        if ($condition) {
            $this->_dbr->reset_query();
        }

        $query = $this->find();

        // Check condition type
        if (is_array($condition)) {

            // Check if is numeric array
            if (array_keys($condition)===range(0, count($condition)-1)) {
                
                /* Numeric Array */
                $query->where_in($this->_field($this->primaryKey), $condition);

            } else {

                /* Associated Array */
                foreach ($condition as $field => $value) {
                    
                    $query->where($field, $value);
                }
            }
        } 
        elseif (is_numeric($condition) || is_string($condition)) {
            /* Single Primary Key */
            $query->where($this->_field($this->primaryKey), $condition);
        }

        return $query;
    }

    /**
     * Format a date for timestamps
     *
     * @param string Field name
     * @param array Attributes
     * @return array Addon $attributes of pointer
     */
    protected function _formatDate($field, &$attributes)
    {
        if ($this->timestamps && $field) {

            switch ($this->dateFormat) {
                case 'datetime':
                    $dateFormat = date("Y-m-d H:i:s");
                    break;
                
                case 'unixtime':
                default:
                    $dateFormat = time();
                    break;
            }
            
            $attributes[$field] = $dateFormat;
        }

        return $attributes;
    }

    /**
     * The scope which not been soft deleted 
     *
     * @param bool $skip Skip
     * @return bool Result
     */
    protected function _addSoftDeletedCondition()
    {
        if (!$this->_withoutSoftDeletedScope 
            && static::SOFT_DELETED 
            && isset($this->softDeletedFalseValue)) {
            
            $this->_dbr->where($this->_field(static::SOFT_DELETED), 
                $this->softDeletedFalseValue);

            // Reset SOFT_DELETED switch
            $this->_withoutSoftDeletedScope = false;
        }

        return true;
    }

    /**
     * The scope which not been soft deleted 
     *
     * @param bool $skip Skip
     * @return bool Result
     */
    protected function _addGlobalScopeCondition()
    {
        if (!$this->_withoutGlobalScope) {
            
            $this->_globalScopes();

            // Reset Global Switch switch
            $this->_withoutGlobalScope = false;
        }

        return true;
    }

    /**
     * Standardize field name
     *
     * @param string $columnName
     * @return string Standardized column name
     */
    protected function _field($columnName)
    {
        return ($this->alias) ? "`{$this->alias}`.`{$columnName}`" : "`{$this->table}`.`{$columnName}`";
    }

    /**
     * Get & load $this->db in CI application
     * 
     * @return object CI $this->db
     */
    private function _getDefaultDB()
    {
        // For ReadDatabase checking Master first
        if ($this->_db) {
            return $this->_db;
        }
        
        if (!isset($this->db)) {
            $this->load->database();
        }
        // No need to set as reference because $this->db is refered to &DB already.
        return $this->db;
    }
}
