<p align="center">
    <a href="https://codeigniter.com/" target="_blank">
        <img src="https://codeigniter.com/assets/images/ci-logo-big.png" height="100px">
    </a>
    <h1 align="center">CodeIgniter Model</h1>
    <br>
</p>

CodeIgniter 3 ORM Base Model supported Read & Write Database Connections

[![Latest Stable Version](https://poser.pugx.org/yidas/codeigniter-model/v/stable?format=flat-square)](https://packagist.org/packages/yidas/codeigniter-model)
[![Latest Unstable Version](https://poser.pugx.org/yidas/codeigniter-model/v/unstable?format=flat-square)](https://packagist.org/packages/yidas/codeigniter-model)
[![License](https://poser.pugx.org/yidas/codeigniter-model/license?format=flat-square)](https://packagist.org/packages/yidas/codeigniter-model)

Features
--------

- ***Elegant patterns** as Laravel Eloquent & Yii2 Active Record (ORM is not yet)*

- ***Codeigniter Query Builder** integration*

- ***Timestamps Behavior** & **Soft Deleting** & **Query Scopes** support*

- ***Read & Write Splitting** for Replications*

This package provide Base Model which extended `CI_Model` and provided full CRUD methods to make developing database interactions easier and quicker for your CodeIgniter applications.

OUTLINE
-------

- [Demonstration](#demonstration)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Defining Models](#defining-models)
  - [Table Names](#table-names)
  - [Primary Keys](#primary-keys)
  - [Timestamps](#timestamps)
  - [Database Connection](#database-connection)
- [Usage](#usage)
  - [find()](#find)
    - [Query Builder Implementation](#query-builder-implementation)
  - [findOne()](#findone)
  - [findAll()](#findall)
  - [reset()](#reset)
  - [insert()](#insert)
  - [batchInsert()](#batchinsert)
  - [update()](#update)
  - [batchUpdate()](#batchupdate)
  - [replace()](#replace)
  - [delete()](#delete)
  - [getLastInsertID()](#getlastinsertid)
  - [getAffectedRows()](#getaffectedrows)
  - [setAlias()](#setalias)
- [Soft Deleted](#soft-deleted)
  - [Configuration](#configuration-1)
  - [Usage](#usage-1)
- [Query Scopes](#query-scopes)
  - [Configuration](#configuration-2)
  - [Usage](#usage-2)
- [Read & Write Connections](#read--write-connections)
  - [Configuration](#configuration-3)
  - [Load Balancing for Databases](#load-balancing-for-databases)
- [Pessimistic Locking](#pessimistic-locking)
- [Helpers](#helpers)
  - [indexBy()](#indexby)
  
---

DEMONSTRATION
-------------

### Find One

```php
$this->load->model('post_model', 'PostModel');

$post = $this->PostModel->findOne(123);
```

### Find with Query Builder
```php
$posts = $this->PostModel->find()
  ->where('is_public', '1')
  ->limit(0,25)
  ->order_by('id')
  ->get()
  ->result_array();
```

### CRUD

```php
$result = $this->PostModel->insert(['title' => 'Codeigniter Model']);
// Find out the record which just be inserted
$post = $this->PostModel->find()
  ->order_by('id', 'DESC')
  ->get()
  ->row_array();
// Update the record
$result = $this->PostModel->update(['title' => 'CI3 Model'], $post['id']);
// Delete the record
$result = $this->PostModel->delete($post['id']);
```

---

REQUIREMENTS
------------

This library requires the following:

- PHP 5.4.0+
- CodeIgniter 3.0.0+

---

INSTALLATION
------------

Run Composer in your Codeigniter project under the folder `\application`:

    composer require yidas/codeigniter-model
    
Check Codeigniter `application/config/config.php`:

```php
$config['composer_autoload'] = TRUE;
```
    
> You could customize the vendor path into `$config['composer_autoload']`

---

CONFIGURATION
-------------

After installation, `yidas\Model` class is ready to use. Simply, you could create a model to extend the `yidas\Model` directly:

```php
class Post_model extends yidas\Model {}
```

After that, this model is ready to use for example: `$this->PostModel->findOne(123);`

However, the schema of tables such as primary key in your applicaiton may not same as default, and it's annoying to defind repeated schema for each model. We recommend you to make `My_model` to extend `yidas\Model` instead.

### Use My_model to Extend Base Model for every Models

You could use `My_model` to extend `yidas\Model`, then make each model to extend `My_model` in Codeigniter application.

*1. Create `My_model` extended `yidas\Model` with configuration for fitting your common table schema:*

```php
class My_model extends yidas\Model
{
    protected $primaryKey = 'sn';
    const CREATED_AT = 'created_time';
    const UPDATED_AT = 'updated_time';
    // Customized Configurations for your app...
}
```

*2. Create each Model extended `My_model` in application with its own table configuration:*

```php
class Post_model extends My_model
{
    protected $table = "post_table";
}
```

*3. Use each extended Model with library usages:*

```php
$this->load->model('post_model', 'PostModel');

$post = $this->PostModel->findOne(123);
```

[My_model Example with Document](https://github.com/yidas/codeigniter-model/tree/master/example)

---

Defining Models
---------------

To get started, let's create an model extends `yidas\Model` or through `My_model`, then define each model suitably.

### Table Names

By convention, the "snake case" with lowercase excluded `_model` postfix of the class name will be used as the table name unless another name is explicitly specified. So, in this case, Model will assume the `Post_model` model stores records in the `post` table. You may specify a custom table by defining a table property on your model:

```php
// class My_model extends yidas\Model
class Post_model extends My_model
{
    protected $table = "post_table";
}
```

> You could set table alias by defining `protected $alias = 'A1';` for model.

#### Table Name Guessing Rule

In our pattern, The naming between model class and table is the same, with supporting no matter singular or plural names:

|Model Class Name|Table Name|
|--|--|
|Post_model|post|
|Posts_model|posts|
|User_info_model|user_info|

#### Get Table Name

You could get table name from each Model:

```php
$tableName = $this->PostModel->getTable();
```



### Primary Keys

You may define a protected `$primaryKey` property to override this convention:

```php
class My_model extends yidas\Model
{
    protected $primaryKey = "sn";
}
```

### Timestamps

By default, Model expects `created_at` and `updated_at` columns to exist on your tables. If you do not wish to have these columns automatically managed by base Model, set the `$timestamps` property on your model as `false`:

```php
class My_model extends yidas\Model
{
    protected $timestamps = false;
}
```

If you need to customize the format of your timestamps, set the `$dateFormat` property on your model. This property determines how date attributes are stored in the database:

```php
class My_model extends yidas\Model
{
    /**
     * Date format for timestamps.
     *
     * @var string unixtime(946684800)|datetime(2000-01-01 00:00:00)
     */
    protected $dateFormat = 'datetime';
}
```

If you need to customize the names of the columns used to store the timestamps, you may set the `CREATED_AT` and `UPDATED_AT` constants in your model:

```php
class My_model extends yidas\Model
{
    const CREATED_AT = 'created_time';
    const UPDATED_AT = 'updated_time';
}
```

Also, you could customized turn timestamps behavior off for specified column by assigning as empty:

```php
class My_model extends yidas\Model
{
    const CREATED_AT = 'created_time';
    const UPDATED_AT = NULL;
}
```

### Database Connection

By default, all models will use the default database connection `$this->db` configured for your application. If you would like to specify a different connection for the model, use the `$database` property:

```php
class My_model extends yidas\Model
{
    protected $database = 'database2';
}
```

> More Database Connection settings: [Read & Write Connections](#read--write-connections)

---

USAGE
-----

Above usage examples are calling Models out of model, for example in controller:

```php
$this->load->model('post_model', 'Model');
```

If you call methods in Model itself, just calling `$this` as model. For example, `$this->find()...` for `find()`;

### find()

Create an existent CI Query Builder instance with Model features for query purpose.

```php
$records = $this->Model->find()
    ->where('is_public', '1')
    ->limit(0,25)
    ->order_by('id')
    ->get()
    ->result_array();
```

```php
// Without any scopes & conditions for this query
$records = $this->Model->find(true)
    ->where('is_deleted', '1')
    ->get()
    ->result_array();
    
// This is equal to find(true) method
$this->Model->withAll()->find();
```

#### Query Builder Implementation

You could assign Query Builder as a variable to handle add-on conditions instead of using `$this->Model->getBuilder()`.

```php
$queryBuilder = $this->Model->find();
if ($filter) {
    $queryBuilder->where('filter', $filter);
}
$records = $queryBuilder->get()->result_array();
```

### findOne()

Return a single record array by a primary key or an array of column values with Model Filters.

```php
$post = $this->Model->findOne(123);
```

### findAll()

Return a list of records that match the specified primary key value(s) or a set of column values with Model Filters.

```php
$post = $this->Model->findAll([3,21,135]);
```

### reset()

reset an CI Query Builder instance with Model.

```php
$this->Model->reset()->find();
```

### insert()

Insert a row with Timestamps feature into the associated database table using the attribute values of this record.

```php
$result = $this->Model->insert([
    'name' => 'Nick Tsai',
    'email' => 'myintaer@gmail.com',
]);
```

### batchInsert()

Insert a batch of rows with Timestamps feature into the associated database table using the attribute values of this record.

```php
$result = $this->Model->batchInsert([
     ['name' => 'Nick Tsai', 'email' => 'myintaer@gmail.com'],
     ['name' => 'Yidas', 'email' => 'service@yidas.com']
]);
```

### replace()

Replace a row with Timestamps feature into the associated database table using the attribute values of this record.

```php
$result = $this->Model->replace([
    'id' => 1,
    'name' => 'Nick Tsai',
    'email' => 'myintaer@gmail.com',
]);
```

### update()

Save the changes with Timestamps feature to the selected record(s) into the associated database table.

```php
$result = $this->Model->update(['status'=>'off'], 123)
```

```php
// Find conditions first then call again
$this->Model->find()->where('id', 123);
$result = $this->Model->update(['status'=>'off']);
```

> Notice: You need to call `update` from Model but not from CI-DB builder chain, the wrong sample code: 
> 
> `$this->Model->find()->where('id', 123)->update('table', ['status'=>'off']);`

### batchUpdate()

Update a batch of update queries into combined query strings.

```php
$result = $this->Model->batchUpdate([
    [['title'=>'A1', 'modified'=>'1'], ['id'=>1]],
    [['title'=>'A2', 'modified'=>'1'], ['id'=>2]],
]);
```

### delete()

Delete the selected record(s) with Timestamps feature into the associated database table.

```php
$result = $this->Model->delete(123)
```

```php
// Find conditions first then call again
$this->Model->find()->where('id', 123);
$result = $this->Model->delete();
```

```php
// Force delete for SOFT_DELETED mode 
$this->Model->delete(123, true);
```

### getLastInsertID()

Get the insert ID number when performing database inserts.

```php
$result = $this->Model->insert(['name' => 'Nick Tsai']);
$lastInsertID = $this->Model->getLastInsertID();
```

### getAffectedRows()

Get the number of affected rows when doing “write” type queries (insert, update, etc.).

```php
$result = $this->Model->update(['name' => 'Nick Tsai'], 32);
$affectedRows = $this->Model->getAffectedRows();
```

### setAlias()

Set table alias

```php
$query = $this->Model->setAlias("A1")
    ->find()
    ->join('table2 AS A2', 'A1.id = A2.id');
```

---

SOFT DELETED
------------

In addition to actually removing records from your database, This Model can also "soft delete" models. When models are soft deleted, they are not actually removed from your database. Instead, a `deleted_at` attribute could be set on the model and inserted into the database.

### Configuration

You could enable SOFT DELETED feature by giving field name to `SOFT_DELETED`:

```php
class My_model extends yidas\Model
{
    const SOFT_DELETED = 'is_deleted';
}
```

While `SOFT_DELETED` is enabled, you could set `$softDeletedFalseValue` and `$softDeletedTrueValue` for fitting table schema. Futher, you may set `DELETED_AT` with column name for Timestapes feature, or disabled by setting to `NULL` by default:

```php
class My_model extends yidas\Model
{
    const SOFT_DELETED = 'is_deleted';
    
    // The actived value for SOFT_DELETED
    protected $softDeletedFalseValue = '0';
    
    // The deleted value for SOFT_DELETED
    protected $softDeletedTrueValue = '1';

    const DELETED_AT = 'deleted_at';
}
```

If you need to disabled SOFT DELETED feature for specified model, you may set `SOFT_DELETED` to `false`, which would disable any SOFT DELETED functions including `DELETED_AT` feature:

```php
// class My_model extends yidas\Model
class Log_model extends My_model
{
    const SOFT_DELETED = false;
}
```

### Usage

#### forceDelete()

Force Delete the selected record(s) with Timestamps feature into the associated database table.

```php
$result = $this->Model->forceDelete(123)
```

```php
// Query builder ORM usage
$this->Model->find()->where('id', 123);
$result = $this->Model->forceDelete();
```


#### restore()

Restore SOFT_DELETED field value to the selected record(s) into the associated database table..

```php
$result = $this->Model->restore(123)
```

```php
// Query builder ORM usage
$this->Model->withTrashed()->find()->where('id', 123);
$this->Model->restore();
```

#### withTrashed()

Without [SOFT DELETED](#soft-deleted) query conditions for next `find()`

```php
$this->Model->withTrashed()->find();
```


---

QUERY SCOPES
------------

Query scopes allow you to add constraints to all queries for a given model. Writing your own global scopes can provide a convenient, easy way to make sure every query for a given model receives certain constraints. The [SOFT DELETED](#soft-deleted) scope is a own scope which is not includes in global scope.

### Configuration

You could override `_globalScopes` method to define your constraints:

```php
class My_model extends yidas\Model
{
    protected $userAttribute = 'uid';
    
    /**
     * Override _globalScopes with User validation
     */
    protected function _globalScopes()
    {
        $this->db->where(
            $this->_field($this->userAttribute), 
            $this->config->item('user_id')
            );
        return parent::_globalScopes();
    }
```

After overriding that, the `My_model` will constrain that scope in every query from `find()`, unless you remove the query scope before a find query likes `withoutGlobalScopes()`.

### Usage

#### withoutGlobalScopes()

Without Global Scopes query conditions for next find()

```php
$this->Model->withoutGlobalScopes()->find();
```

#### withAll()

Without all query conditions ([SOFT DELETED](#soft-deleted) & [QUERY SCOPES](#query-scope)) for next `find()`

That is, with all data set of Models for next `find()`

```php
$this->Model->withAll()->find();
```

---

Read & Write Connections
------------------------

Sometimes you may wish to use one database connection for `SELECT` statements, and another for `INSERT`, `UPDATE`, and `DELETE` statements. This Model implements Replication and Read-Write Splitting, makes database connections will always be used while using Model usages.

### Configuration

Read & Write Connections could be set in the model which extends `yidas\Model`, you could defind the read & write databases in extended `My_model` for every models.

There are three types to set read & write databases:


#### Codeigniter Database Key

You could set the database key refered from `\application\config\database.php` into model attributes of `database` & `databaseRead`, the setting connections would be created automatically:

```php
class My_model extends yidas\Model
{
    protected $database = 'default';
    
    protected $databaseRead = 'slave';
}
```

> This method supports cache mechanism for DB connections, each model could define its own connections but share the same connection by key.

#### Codeigniter DB Connection

If you already have prepared CI DB connections, you could assign to attributes directly in construct section before parent's constrcut: 

```php
class My_model extends yidas\Model
{
    function __construct()
    {
        $this->database = $this->db;
        
        $this->databaseRead = $this->dbr;
        
        parent::__construct();
    }
}
```

#### Codeigniter Database Config Array

This way is used for the specified model related to the one time connected database in a request cycle, which would create a new connection per each model: 

```php
class My_model extends yidas\Model
{
    protected $databaseRead = [
        'dsn'   => '',
        'hostname' => 'specified_db_host',
        // Database Configuration...
        ];
}
```

### Load Balancing for Databases

In above case, you could set multiple databases and implement random selected connection for Read or Write Databases.

For example, configuring read databases in `application/config/database`:

```php
$slaveHosts = ['192.168.1.2', '192.168.1.3'];

$db['slave']['hostname'] = $slaveHosts[mt_rand(0, count($slaveHosts) - 1)];
```

After that, you could use database key `slave` to load or assign it to attribute:

```php
class My_model extends yidas\Model
{
    protected $databaseRead = 'slave';
}
```

---

PESSIMISTIC LOCKING
-------------------

The Model also includes a few functions to help you do "pessimistic locking" on your `select` statements. To run the statement with a "shared lock", you may use the `sharedLock` method to get a query. A shared lock prevents the selected rows from being modified until your transaction commits:

```php
$this->Model->find()->where('id', 123);
$result = $this->Model->sharedLock()->row_array();
```

Alternatively, you may use the `lockForUpdate` method. A "for update" lock prevents the rows from being modified or from being selected with another shared lock:

```php
$this->Model->find()->where('id', 123);
$result = $this->Model->lockForUpdate()->row_array();
```

### Example Code

This transaction block will lock selected rows for next same selected rows with `FOR UPDATE` lock:

```php
$this->Model->getDB()->trans_start();
$this->Model->find()->where('id', 123)
$result = $this->Model->lockForUpdate()->row_array();
$this->Model->getDB()->trans_complete(); 
```

---

HELPERS
-------

### `indexBy()`

Index by Key

```php
array indexBy(Array & $array [, Integer $key] [, Boolean $obj2Array])
```

Example:

```php
$records = $this->Model->findAll();
$this->Model->indexBy($records, 'sn');

// Result example of $records:
[
    7 => ['sn'=>7, title=>'Foo'],
    13 => ['sn'=>13, title=>'Bar']
]
```

