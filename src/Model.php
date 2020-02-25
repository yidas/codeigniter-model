<?php

namespace yidas;

use Exception;

/**
 * Base Model
 *
 * @author   Nick Tsai <myintaer@gmail.com>
 * @version  2.19.2
 * @see      https://github.com/yidas/codeigniter-model
 */
class Model extends \CI_Model implements \ArrayAccess
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
     * The active value for SOFT_DELETED
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
     * @var string Feild name for deleted_at, empty is disabled.
     */
    const DELETED_AT = '';

    /**
     * Check property schema for write
     *
     * @var boolean
     */
    protected $propertyCheck = false;

    /**
     * @var array Validation errors (depends on validator driver)
     */
    protected $_errors;

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
     * @var object ORM schema caches by model class namespace
     */
    private static $_ormCaches = [];

    /**
     * @var bool SOFT_DELETED one time switch
     */
    private $_withoutSoftDeletedScope = false;

    /**
     * @var bool Global Scope one time switch
     */
    private $_withoutGlobalScope = false;

    /**
     * ORM read properties
     *
     * @var array
     */
    private $_readProperties = [];

    /**
     * ORM write properties
     *
     * @var array
     */
    private $_writeProperties = [];

    /**
     * ORM self query
     *
     * @var string
     */
    private $_selfCondition = null;

    /**
     * Clean next find one time setting
     *
     * @var boolean
     */
    private $_cleanNextFind = false;

    /**
     * Relationship property caches by method name
     *
     * @var array
     */
    private $_relationshipCaches = [];

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
                    $this->_db = get_instance()->load->database($this->database, true);
                    self::$_dbCaches[$this->database] = $this->_db;
                }
            }
            else {
                // Config array for each Model
                $this->_db = get_instance()->load->database($this->database, true);
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
                    $this->_dbr = get_instance()->load->database($this->databaseRead, true);
                    self::$_dbrCaches[$this->databaseRead] = $this->_dbr;
                }
            }
            else {
                // Config array for each Model
                $this->_dbr = get_instance()->load->database($this->databaseRead, true);
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
     * Returns the filter rules for validation.
     *
     * @return array Filter rules. [[['attr1','attr2'], 'callable'],]
     */
    public function filters()
    {
        return [];
    }

    /**
     * Returns the validation rules for attributes.
     * 
     * @see https://www.codeigniter.com/userguide3/libraries/form_validation.html#rule-reference
     * @return array validation rules. (CodeIgniter Rule Reference)
     */
    public function rules()
    {
        return [];
    }

    /**
     * Performs the data validation with filters
     * 
     * ORM only performs validation for assigned properties.
     * 
     * @param array Data of attributes
     * @param boolean Return filtered data
     * @return boolean Result
     * @return mixed Data after filter ($returnData is true)
     */
    public function validate($attributes=[], $returnData=false)
    {
        // Data fetched by ORM or input
        $data = ($attributes) ? $attributes : $this->_writeProperties;
        // Filter first
        $data = $this->filter($data);
        // ORM re-assign properties
        $this->_writeProperties = (!$attributes) ? $data : $this->_writeProperties;
        // Get validation rules from function setting
        $rules = $this->rules();

        // The ORM update will only collect rules with corresponding modified attributes.
        if ($this->_selfCondition) {

            $newRules = [];
            foreach ((array) $rules as $key => $rule) {
                if (isset($this->_writeProperties[$rule['field']])) {
                    // Add into new rules for updating
                    $newRules[] = $rule;
                }
            }
            // Replace with mapping rules
            $rules = $newRules;
        }

        // Check if has rules
        if (empty($rules))
            return ($returnData) ? $data : true;

        // CodeIgniter form_validation doesn't work with empty array data
        if (empty($data))
            return false;

        // Load CodeIgniter form_validation library for yidas/model namespace, which has no effect on common one
        get_instance()->load->library('form_validation', null, 'yidas_model_form_validation');
        // Get CodeIgniter validator
        $validator = get_instance()->yidas_model_form_validation;
        $validator->reset_validation();
        $validator->set_data($data);
        $validator->set_rules($rules);
        // Run Validate
        $result = $validator->run();
        
        // Result handle
        if ($result===false) {

            $this->_errors = $validator->error_array();
            return false;

        } else {

            return ($returnData) ? $data : true;
        }
    }

    /**
     * Validation - Get error data referenced by last failed Validation
     *
     * @return array 
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Validation - Reset errors
     *
     * @return boolean
     */
    public function resetErrors()
    {
        $this->_errors = null;

        return true;
    }

    /**
     * Filter process
     *
     * @param array $data Attributes
     * @return array Filtered data
     */
    public function filter($data)
    {
        // Get filter rules
        $filters = $this->filters();

        // Filter process with setting check
        if (!empty($filters) && is_array($filters)) {
            
            foreach ($filters as $key => $filter) { 
                
                if (!isset($filter[0]))
                    throw new Exception("No attributes defined in \$filters from " . get_called_class() . " (" . __CLASS__ . ")", 500);

                if (!isset($filter[1]))
                    throw new Exception("No function defined in \$filters from " . get_called_class() . " (" . __CLASS__ . ")", 500);

                list($attributes, $function) = $filter;

                $attributes = (is_array($attributes)) ? $attributes : [$attributes];

                // Filter each attribute
                foreach ($attributes as $key => $attribute) {

                    if (!isset($data[$attribute]))
                        continue;
                    
                    $data[$attribute] = call_user_func($function, $data[$attribute]);
                }
            }
        }
        
        return $data;
    }

    /**
     * Set table alias for next find()
     *
     * @param string Table alias name
     * @return $this
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        // Turn off cleaner to prevent continuous setting 
        $this->_cleanNextFind = false;
        
        return $this;
    }

    /**
     * Create an existent CI Query Builder instance with Model features for query purpose.
     *
     * @param boolean $withAll withAll() switch helper
     * @return \CI_DB_query_builder CI_DB_query_builder
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
        $instance = (isset($this)) ? $this : new static;
        
        // One time setting reset mechanism
        if ($instance->_cleanNextFind === true) {
            // Reset alias
            $instance->setAlias(null);
        } else {
            // Turn on clean for next find
            $instance->_cleanNextFind = true;
        }
        
        // Alias option for FROM
        $sqlFrom = ($instance->alias) ? "{$instance->table} AS {$instance->alias}" : $instance->table;
        
        $instance->_dbr->from($sqlFrom);

        // WithAll helper
        if ($withAll===true) {
            $instance->withAll();
        }

        // Scope condition
        $instance->_addGlobalScopeCondition();

        // Soft Deleted condition
        $instance->_addSoftDeletedCondition();

        return $instance->_dbr;
    }

    /**
     * Create an CI Query Builder instance without Model Filters for query purpose.
     *
     * @return \CI_DB_query_builder CI_DB_query_builder
     */
    public function forceFind()
    {
        return $this->withAll()->find();
    }

    /**
     * Return a single active record model instance by a primary key or an array of column values.
     *
     * @param mixed $condition Refer to _findByCondition() for the explanation of this parameter
     * @return object ActiveRecord(Model)
     * @example
     *  $post = $this->Model->findOne(123);
     * @example
     *  // Query builder ORM usage
     *  $this->Model->find()->where('id', 123);
     *  $this->Model->findOne();
     */
    public static function findOne($condition=[])
    {
        $instance = (isset($this)) ? $this : new static;
        
        $record = $instance->_findByCondition($condition)
            ->limit(1)
            ->get()->row_array();
        
        // Record check
        if (!$record) {
            return $record;
        }

        return $instance->createActiveRecord($record, $record[$instance->primaryKey]);
    }

    /**
     * Returns a list of active record models that match the specified primary key value(s) or a set of column values.
     *
     * @param mixed $condition Refer to _findByCondition() for the explanation 
     * @param integer|array $limit Limit or [offset, limit]
     * @return array Set of ActiveRecord(Model)s
     * @example
     *  $post = $this->PostModel->findAll([3,21,135]);
     * @example
     *  // Query builder ORM usage
     *  $this->Model->find()->where_in('id', [3,21,135]);
     *  $this->Model->findAll();
     */
    public static function findAll($condition=[], $limit=null)
    {
        $instance = (isset($this)) ? $this : new static;

        $query = $instance->_findByCondition($condition);

        // Limit / offset
        if ($limit) {

            $offset = null;
            
            if (is_array($limit) && isset($limit[1])) {
                // Prevent list() variable effect
                $set = $limit;
                list($offset, $limit) = $set;
            }
            
            $query = ($limit) ? $query->limit($limit) : $query;
            $query = ($offset) ? $query->offset($offset) : $query;
        }
        
        $records = $query->get()->result_array();

        // Record check
        if (!$records) {
            return $records;
        }

        $set = [];
        // Each ActiveRecord
        foreach ((array)$records as $key => $record) {
            // Check primary key setting
            if (!isset($record[$instance->primaryKey])) {
                throw new Exception("Model's primary key not set", 500); 
            }
            // Create an ActiveRecord into collect
            $set[] = $instance->createActiveRecord($record, $record[$instance->primaryKey]);
        }

        return $set;
    }

    /**
     * reset an CI Query Builder instance with Model.
     *
     * @return $this
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
     * @param boolean $runValidation Whether to perform validation (calling validate()) before manipulate the record. 
     * @return boolean Result
     * @example
     *  $result = $this->Model->insert([
     *      'name' => 'Nick Tsai',
     *      'email' => 'myintaer@gmail.com',
     *  ]);
     */
    public function insert($attributes, $runValidation=true)
    {
        // Validation
        if ($runValidation && false===$attributes=$this->validate($attributes, true))
            return false; 
        
        $this->_attrEventBeforeInsert($attributes);

        return $this->_db->insert($this->table, $attributes);
    }

    /**
     * Insert a batch of rows with Timestamps feature into the associated database table using the attribute values of this record.
     * 
     * @param array $data The rows to be batch inserted
     * @param boolean $runValidation Whether to perform validation (calling validate()) before manipulate the record. 
     * @return int Number of rows inserted or FALSE on failure
     * @example
     *  $result = $this->Model->batchInsert([
     *      ['name' => 'Nick Tsai', 'email' => 'myintaer@gmail.com'],
     *      ['name' => 'Yidas', 'email' => 'service@yidas.com']
     *  ]);
     */
    public function batchInsert($data, $runValidation=true)
    {
        foreach ($data as $key => &$attributes) {

            // Validation
            if ($runValidation && false===$attributes=$this->validate($attributes, true))
                return false; 

            $this->_attrEventBeforeInsert($attributes);
        }

        return $this->_db->insert_batch($this->table, $data);
    }

    /**
     * Get the insert ID number when performing database inserts.
     *
     * @param string $name Name of the sequence object from which the ID should be returned.
     * @return integer Last insert ID
     */
    public function getLastInsertID($name=null)
    {
        return $this->getDB()->insert_id($name);
    }

    /**
     * Replace a row with Timestamps feature into the associated database table using the attribute values of this record.
     * 
     * @param array $attributes
     * @param boolean $runValidation Whether to perform validation (calling validate()) before manipulate the record. 
     * @return bool Result
     * @example
     *  $result = $this->Model->replace([
     *      'id' => 1,
     *      'name' => 'Nick Tsai',
     *      'email' => 'myintaer@gmail.com',
     *  ]);
     */
    public function replace($attributes, $runValidation=true)
    {
        // Validation
        if ($runValidation && false===$attributes=$this->validate($attributes, true))
            return false; 
        
        $this->_attrEventBeforeInsert($attributes);

        return $this->_db->replace($this->table, $attributes);
    }

    /**
     * Save the changes with Timestamps feature to the selected record(s) into the associated database table.
     * 
     * @param array $attributes
     * @param mixed $condition Refer to _findByCondition() for the explanation 
     * @param boolean $runValidation Whether to perform validation (calling validate()) before manipulate the record. 
     * @return bool Result
     *
     * @example    
     *  $this->Model->update(['status'=>'off'], 123)
     * @example
     *  // Query builder ORM usage
     *  $this->Model->find()->where('id', 123);
     *  $this->Model->update(['status'=>'off']);
     */
    public function update($attributes, $condition=NULL, $runValidation=true)
    {
        // Validation
        if ($runValidation && false===$attributes=$this->validate($attributes, true))
            return false; 

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
     * @param boolean $withAll withAll() switch helper
     * @param integer $maxLenth MySQL max_allowed_packet
     * @param boolean $runValidation Whether to perform validation (calling validate()) before manipulate the record. 
     * @return integer Count of successful query pack(s)
     * @example 
     *  $result = $this->Model->batchUpdate([
     *      [['title'=>'A1', 'modified'=>'1'], ['id'=>1]],
     *      [['title'=>'A2', 'modified'=>'1'], ['id'=>2]],
     *  ];);
     */
    public function batchUpdate(Array $dataSet, $withAll=false, $maxLength=null, $runValidation=true)
    {
        $maxLength = $maxLength ?: 4 * 1024 * 1024;

        $count = 0;
        $sqlBatch = '';
        
        foreach ($dataSet as $key => &$each) {

            // Data format
            list($attributes, $condition) = $each;

            // Check attributes
            if (!is_array($attributes) || !$attributes)
                continue; 

            // Validation
            if ($runValidation && false===$attributes=$this->validate($attributes, true))
                continue; 

            // WithAll helper
            if ($withAll===true) {
                $this->withAll();
            }
                
            // Model Condition
            $query = $this->_findByCondition($condition);

            $attributes = $this->_attrEventBeforeUpdate($attributes);

            // Pack query then move it to write DB from read DB
            $sql = $this->_dbr->set($attributes)->get_compiled_update();
            $this->_dbr->reset_query();

            // Last batch check: First single query & Max length
            // The first single query needs to be sent ahead to prevent the limitation that PDO transaction could not 
            // use multiple SQL line in one query, but allows if the multi-line query is behind a single query. 
            if (($count==0 && $sqlBatch) || strlen($sqlBatch)>=$maxLength) {
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
        // Check is Active Record
        if ($this->_readProperties) {
            // Reset condition and find single by self condition
            $this->reset();
            $condition = $this->_selfCondition;
        }
        
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
     * Get count from query
     *
     * @param boolean Reset query conditions
     * @return integer
     */
    public function count($resetQuery=true)
    {
        return $this->getDBR()->count_all_results('', $resetQuery);
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
     * @return $this
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
     * @return $this
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
     * @return $this
     * @example 
     *  $this->Model->withAll()->find();
     */
    public function withAll()
    {
        // Turn off switches of all featured conditions
        $this->withTrashed();
        $this->withoutGlobalScopes();

        return $this;
    }

    /**
     * New a Active Record from Model by data
     *
     * @param array $readProperties
     * @param array $selfCondition
     * @return object ActiveRecord(Model)
     */
    public function createActiveRecord($readProperties, $selfCondition)
    {
        $activeRecord = new static();
        // ORM handling
        $activeRecord->_readProperties = $readProperties;
        // Primary key condition to ensure single query result 
        $activeRecord->_selfCondition = $selfCondition;

        return $activeRecord;
    }

    /**
     * Active Record (ORM) save for insert or update
     *
     * @param boolean $runValidation Whether to perform validation (calling validate()) before manipulate the record. 
     * @return bool Result of CI insert
     */
    public function save($runValidation=true)
    {
        // if (empty($this->_writeProperties))
        //     return false;
        
        // ORM status distinguishing
        if (!$this->_selfCondition) {

            // Event
            if (!$this->beforeSave(true)) {
                return false;
            }

            $result = $this->insert($this->_writeProperties, $runValidation);
            // Change this ActiveRecord to update mode
            if ($result) {
                // ORM handling
                $this->_readProperties = $this->_writeProperties;
                $insertID =  $this->getLastInsertID();
                $this->_readProperties[$this->primaryKey] = $insertID;
                $this->_selfCondition = $insertID;
                // Event
                $this->afterSave(true, $this->_readProperties);
                // Reset properties
                $this->_writeProperties = [];
            }

        } else {

            // Event
            if (!$this->beforeSave(false)) {
                return false;
            }
            
            $result = ($this->_writeProperties) ? $this->update($this->_writeProperties, $this->_selfCondition, $runValidation) : true;
            // Check the primary key is changed
            if ($result) {

                // Primary key condition to ensure single query result 
                if (isset($this->_writeProperties[$this->primaryKey])) {
                    $this->_selfCondition = $this->_writeProperties[$this->primaryKey];
                }
                $this->_readProperties = array_merge($this->_readProperties, $this->_writeProperties);
                // Event
                $this->afterSave(true, $this->_readProperties);
                // Reset properties
                $this->_writeProperties = [];
            }
        }
        
        return $result;
    }

    /**
     * This method is called at the beginning of inserting or updating a active record
     *
     * @param bool $insert whether this method called while inserting a record.
     * If `false`, it means the method is called while updating a record.
     * @return bool whether the insertion or updating should continue.
     * If `false`, the insertion or updating will be cancelled.
     */
    public function beforeSave($insert)
    {
        // overriding
        return true;
    }

    /**
     * This method is called at the end of inserting or updating a active record
     *
     * @param bool $insert whether this method called while inserting a record.
     * If `false`, it means the method is called while updating a record.
     * @param array $changedAttributes The old values of attributes that had changed and were saved.
     * You can use this parameter to take action based on the changes made for example send an email
     * when the password had changed or implement audit trail that tracks all the changes.
     * `$changedAttributes` gives you the old attribute values while the active record (`$this`) has
     * already the new, updated values.
     */
    public function afterSave($insert, $changedAttributes)
    {
        // overriding
    }

    /**
     * Declares a has-many relation.
     *
     * @param string $modelName The model class name of the related record
     * @param string $foreignKey 
     * @param string $localKey
     * @return \CI_DB_query_builder CI_DB_query_builder
     */
    public function hasMany($modelName, $foreignKey=null, $localKey=null)
    {
        return $this->_relationship($modelName, __FUNCTION__, $foreignKey, $localKey);
    }

    /**
     * Declares a has-many relation.
     *
     * @param string $modelName The model class name of the related record
     * @param string $foreignKey 
     * @param string $localKey
     * @return \CI_DB_query_builder CI_DB_query_builder
     */
    public function hasOne($modelName, $foreignKey=null, $localKey=null)
    {
        return $this->_relationship($modelName, __FUNCTION__, $foreignKey, $localKey);
    }

    /**
     * Base relationship.
     *
     * @param string $modelName The model class name of the related record
     * @param string $relationship
     * @param string $foreignKey 
     * @param string $localKey
     * @return \CI_DB_query_builder CI_DB_query_builder
     */
    protected function _relationship($modelName, $relationship, $foreignKey=null, $localKey=null)
    {
        /**
         * PSR-4 support check
         * 
         * @see https://github.com/yidas/codeigniter-psr4-autoload
         */
        if (strpos($modelName, "\\") !== false ) {
            
            $model = new $modelName;

        } else {
            // Original CodeIgniter 3 model loader
            get_instance()->load->model($modelName);
            // Fix the modelName if it has path
            $path = explode('/', $modelName);
            $modelName = count($path) > 1 ? end($path) : $modelName;
            $model = get_instance()->$modelName;
        }

        $libClass = __CLASS__;
        
        // Check if is using same library
        if (!is_subclass_of($model, $libClass)) {
            throw new Exception("Model `{$modelName}` does not extend {$libClass}", 500);
        }

        // Keys
        $foreignKey = ($foreignKey) ? $foreignKey : $this->primaryKey;
        $localKey = ($localKey) ? $localKey : $this->primaryKey; 

        $query = $model->find()
            ->where($foreignKey, $this->$localKey);

        // Inject Model name into query builder for ORM relationships
        $query->modelName = $modelName;
        // Inject relationship type into query builder for ORM relationships
        $query->relationship = $relationship;

        return $query;
    }

    /**
     * Get relationship property value
     *
     * @param string $method
     * @return mixed
     */
    protected function _getRelationshipProperty($method)
    {
        // Cache check
        if (isset($this->_relationshipCaches[$method])) {
            return $this->_relationshipCaches[$method];
        }
        
        $query = call_user_func_array([$this, $method], []);

        // Extract query builder injection property
        $modelName = isset($query->modelName) ? $query->modelName : null;
        $relationship = isset($query->relationship) ? $query->relationship : null;

        if (!$modelName || !$relationship) {
            throw new Exception("ORM relationships error", 500);
        }

        /**
         * PSR-4 support check
         * 
         * @see https://github.com/yidas/codeigniter-psr4-autoload
         */
        if (strpos($modelName, "\\") !== false ) {
            
            $model = new $modelName;

        } else {
            // Original CodeIgniter 3 model loader
            get_instance()->load->model($modelName);
            $model = get_instance()->$modelName;
        }

        // Check return type
        $result = ($relationship == 'hasOne') ? $model->findOne(null) : $model->findAll(null);

        // Save cache
        $this->_relationshipCaches[$method] = $result;

        return $result;
    }

    /**
     * Active Record transform to array record
     *
     * @return array
     * @example $record = $activeRecord->toArray();
     */
    public function toArray()
    {
        return $this->_readProperties;
    }
    
    /**
     * Get table schema
     *
     * @return array Column names
     */
    public function getTableSchema()
    {
        $class = get_class($this);

        // Check ORM Schema cache
        if (!isset(self::$_ormCaches[$class])) {

            $columns = $this->_dbr->query("SHOW COLUMNS FROM `{$this->table}`;")
                ->result_array();

            // Cache
            self::$_ormCaches[$class] = $columns;
        }

        return self::$_ormCaches[$class];
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
     * Encodes special characters into HTML entities.
     * 
     * The [[$this->config->item('charset')]] will be used for encoding.
     * 
     * @param string $content the content to be encoded
     * @param bool $doubleEncode whether to encode HTML entities in `$content`. If false,
     * HTML entities in `$content` will not be further encoded.
     * @return string the encoded content
     * 
     * @see http://www.php.net/manual/en/function.htmlspecialchars.php
     * @see https://www.yiiframework.com/doc/api/2.0/yii-helpers-basehtml#encode()-detail
     */
    public static function htmlEncode($content, $doubleEncode = true)
    {
        $ci = & get_instance();
        
        return htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, $ci->config->item('charset') ? $ci->config->item('charset') : 'UTF-8', $doubleEncode);
    }

    /**
     * Decodes special HTML entities back to the corresponding characters.
     * 
     * This is the opposite of [[encode()]].
     * 
     * @param string $content the content to be decoded
     * @return string the decoded content
     * @see htmlEncode()
     * @see http://www.php.net/manual/en/function.htmlspecialchars-decode.php
     * @see https://www.yiiframework.com/doc/api/2.0/yii-helpers-basehtml#decode()-detail
     */
    public static function htmlDecode($content)
    {
        return htmlspecialchars_decode($content, ENT_QUOTES);
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
     * @param mixed Primary key value or a set of column values. If is null, it would be used for  
     *  previous find() method, which means it would not rebuild find() so it would check and 
     *  protect the SQL statement.
     * @return \CI_DB_query_builder CI_DB_query_builder
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
    protected function _findByCondition($condition=null)
    {
        // Reset Query if condition existed
        if ($condition !== null) {
            $this->_dbr->reset_query();
            $query = $this->find();
        } else {
            // Support for previous find(), no need to find() again
            $query = $this->_dbr;
        }

        // Check condition type
        if (is_array($condition)) {

            // Check if is numeric array
            if (array_keys($condition)===range(0, count($condition)-1)) {
                
                /* Numeric Array */
                $query->where_in($this->_field($this->primaryKey), $condition);

            } else {

                /* Associated Array */
                foreach ($condition as $field => $value) {
                    
                    (is_array($value)) ? $query->where_in($field, $value) : $query->where($field, $value);
                }
            }
        } 
        elseif (is_numeric($condition) || is_string($condition)) {
            /* Single Primary Key */
            $query->where($this->_field($this->primaryKey), $condition);
        }
        else {
            // Simply Check SQL for no condition such as update/delete
            // Warning: This protection just simply check keywords that may not find out for some situations.
            $sql = $this->_dbr->get_compiled_select('', false); // No reset query
            // Check FROM for table condition
            if (stripos($sql, 'from ')===false)
                throw new Exception("You should find() first, or use condition array for update/delete", 400);
            // No condition situation needs to enable where protection
            if (stripos($sql, 'where ')===false)
                throw new Exception("You could not update/delete without any condition! Use find()->where('1=1') or condition array at least.", 400);
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
        if ($this->_withoutSoftDeletedScope) {
            // Reset SOFT_DELETED switch
            $this->_withoutSoftDeletedScope = false;
        } 
        elseif (static::SOFT_DELETED && isset($this->softDeletedFalseValue)) {
            // Add condition
            $this->_dbr->where($this->_field(static::SOFT_DELETED), 
            $this->softDeletedFalseValue);
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
        if ($this->_withoutGlobalScope) {
            // Reset Global Switch switch
            $this->_withoutGlobalScope = false;

        } else {
            // Default to apply global scopes
            $this->_globalScopes();
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
            get_instance()->load->database();
        }
        // No need to set as reference because $this->db is refered to &DB already.
        return get_instance()->db;
    }

    /**
     * ORM set property
     *
     * @param string $name Property key name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        // Property check option
        if ($this->propertyCheck) {

            $flag = false;
            
            // Check if exists
            foreach ($this->getTableSchema() as $key => $column) {
                if ($name == $column['Field']) {
                    $flag = true;
                }
            }

            // No mathc Exception
            if (!$flag) {
                throw new \Exception("Property `{$name}` does not exist", 500);  
            }
        }
        
        $this->_writeProperties[$name] = $value;
    }

    /**
     * ORM get property
     *
     * @param string $name Property key name
     */
    public function __get($name)
    {
        // ORM property check
        if (array_key_exists($name, $this->_writeProperties) ) {
            
            return $this->_writeProperties[$name]; 
        }
        else if (array_key_exists($name, $this->_readProperties)) {
            
            return $this->_readProperties[$name]; 
        }
        // ORM relationship check
        else if (method_exists($this, $method = $name)) {
            
            return $this->_getRelationshipProperty($method);
        }
        // ORM schema check
        else {

            // Write cache to read properties of this ORM
            foreach ($this->getTableSchema() as $key => $column) {

                $this->_readProperties[$column['Field']] = isset($this->_readProperties[$column['Field']]) 
                    ? $this->_readProperties[$column['Field']] 
                    : null;
            }

            // Match property again
            if (array_key_exists($name, $this->_readProperties)) {
        
                return $this->_readProperties[$name]; 
            }

            // CI parent::__get() check
            if (property_exists(get_instance(), $name)) {
                
                return parent::__get($name);
            }

            // Exception
            throw new \Exception("Property `{$name}` does not exist", 500);  
        }

        return null;
    }
    
    /**
     * ORM isset property
     *
     * @param string $name
     * @return void
     */
    public function __isset($name) {

        if (isset($this->_writeProperties[$name])) {
            
            return true;
        }
        else if (isset($this->_readProperties[$name])) {

            return true;
        }
        else if (method_exists($this, $method = $name)) {
            
            return ($this->_getRelationshipProperty($method));
        }

        return false;
    }

    /**
     * ORM unset property
     *
     * @param string $name
     * @return void
     */
    public function __unset($name) {

        unset($this->_writeProperties[$name]);
        unset($this->_readProperties[$name]);
    }
    
    /**
     * ArrayAccess offsetSet
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value) {
        
        return $this->__set($offset, $value);
    }

    /**
     * ArrayAccess offsetExists
     *
     * @param string $offset
     * @return bool Result
     */
    public function offsetExists($offset) {

        return $this->__isset($offset);
    }

    /**
     * ArrayAccess offsetUnset
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset) {

        return $this->__unset($offset);
    }

    /**
     * ArrayAccess offsetGet
     *
     * @param string $offset
     * @return mixed Value of property
     */
    public function offsetGet($offset) {

        return $this->$offset;
    }
}
