<p align="center">
    <a href="https://codeigniter.com/" target="_blank">
        <img src="https://codeigniter.com/assets/images/ci-logo-big.png" height="100px">
    </a>
    <h1 align="center">CodeIgniter Model</h1>
    <br>
</p>

CodeIgniter 3 ORM BaseModel supported Read & Write Database Connections

[![Latest Stable Version](https://poser.pugx.org/yidas/codeigniter-model/v/stable?format=flat-square)](https://packagist.org/packages/yidas/codeigniter-model)
[![Latest Unstable Version](https://poser.pugx.org/yidas/codeigniter-model/v/unstable?format=flat-square)](https://packagist.org/packages/yidas/codeigniter-model)
[![License](https://poser.pugx.org/yidas/codeigniter-model/license?format=flat-square)](https://packagist.org/packages/yidas/codeigniter-model)

Features
--------

*1. **Elegant patterns** as Laravel Eloquent & Yii2 Active Record (ORM is not yet)*

*2. **Codeigniter Query Builder** integration*

*3. **Timestamps Behavior** & **Soft Deleting** & **Query Scopes** support*

*4. **Read & Write Splitting** for Replications*

OUTLINE
-------

* [Demonstration](#demonstration)

* [Installation](#installation)

* [Configuration](#configuration)

* [Defining Models](#defining-models)
  - [Table Names](#table-names)
  - [Primary Keys](#primary-keys)
  - [Timestamps](#timestamps)

* [Usage](#usage)
  - [find()](#find)
  - [insert()](#insert)
  - [update()](#update)
  - [delete()](#delete)

* [Soft Deleted](#soft-deleted)
  - [Configuration](#configuration-1)
  - [Usage](#usage-1)

* [Query Scopes](#query-scopes)
  - [Configuration](#configuration-2)
  - [Usage](#usage-2)
  
* [Read & Write Connections](#read--write-connections)
  - [Configuration](#configuration-3)
  - [Load Balancing for Databases](#load-balancing-for-databases)
  
---

DEMONSTRATION
-------------

### Find one
```php
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

After installation, `\BaseModel` class is ready to use.

You could extend `BaseModel` for each Model or `My_Model` which defines common features in your application.

### Extend BaseModel for Your Models

Simply, you could extend the `BaseModel` for each model in your application:

```php
class Post_model extends BaseModel
{
    protected $table = "post_table";
    protected $primaryKey = 'sn';
    // Configuration by Inheriting...
}
```

After extending `BaseModel` with basic configuration, the model is ready to use:

```php
$this->load->model('Post_model');
$post = $this->Post_model->findOne(123);
```

Instead of direct extending application models, we recommend you to make `My_model` extended `BaseModel` for each model.

### Extend BaseModel for Your My_model in Application

You could create `My_model` extended `BaseModel` for each model to extend in Codeigniter application.

[My_model Example with Document](https://github.com/yidas/codeigniter-model/tree/master/example):

```php
class My_model extends BaseModel
{
    protected $primaryKey = 'sn';
    const CREATED_AT = 'created_time';
    const UPDATED_AT = 'updated_time';
    // Configuration for fitting your common table schema
}
```

After building `My_model`, it's simple to create each model of application:

```php
class Post_model extends My_model
{
    protected $table = "post_table";
}
```

---

Defining Models
---------------

To get started, let's create an model extends `BaseModel` or through `My_model`, then define each model suitably.

### Table Names

You may specify a custom table by defining a table property on your model:

```php
class Post_model extends BaseModel
{
    protected $table = "post_table";
}
```

### Primary Keys

You may define a protected $primaryKey property to override this convention.

```php
class Post_model extends BaseModel
{
    protected $primaryKey = "sn";
}
```

### Timestamps

By default, BaseModel expects `created_at` and `updated_at` columns to exist on your tables. If you do not wish to have these columns automatically managed by BaseModel, set the `$timestamps` property on your model as `false`:

```php
class My_model extends BaseModel
{
    protected $timestamps = false;
}
```

If you need to customize the format of your timestamps, set the `$dateFormat` property on your model. This property determines how date attributes are stored in the database:

```php
class My_model extends BaseModel
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
class My_model extends BaseModel
{
    const CREATED_AT = 'created_time';
    const UPDATED_AT = 'updated_time';
}
```

Also, you could customized turn timestamps behavior off for specified column by assigning as empty:

```php
class My_model extends BaseModel
{
    const CREATED_AT = 'created_time';
    const UPDATED_AT = NULL;
}
```

---

USAGE
-----

### find()

Create an CI Query Builder instance with Model Filters for query purpose.

```php
$posts = $this->PostModel->find()
    ->where('is_public', '1')
    ->limit(0,25)
    ->order_by('id')
    ->get()
    ->result_array();
```

```php
// Without any scopes & conditions for this query
$posts = $this->PostModel->find(true)
    ->where('is_deleted', '1')
    ->get()
    ->result_array();
    
// This is equal to find(true) method
$this->PostModel->withAll()->find();
```

### findOne()

Return a single record array by a primary key or an array of column values with Model Filters.

```php
$post = $this->PostModel->findOne(123);
```

### findAll()

Return a list of records that match the specified primary key value(s) or a set of column values with Model Filters.

```php
$post = $this->PostModel->findAll([3,21,135]);
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


---

SOFT DELETED
------------

In addition to actually removing records from your database, This Model can also "soft delete" models. When models are soft deleted, they are not actually removed from your database. Instead, a `deleted_at` attribute could be set on the model and inserted into the database.

### Configuration

You could enable SOFT DELETED feature by giving field name to `SOFT_DELETED`:

```php
class My_model extends BaseModel
{
    const SOFT_DELETED = 'is_deleted';
}
```

While `SOFT_DELETED` is enabled, you could set `$softDeletedFalseValue` and `$softDeletedTrueValue` for fitting table schema. Futher, you may set `DELETED_AT` with column name for Timestapes feature, or disabled by setting to `NULL` by default:

```php
class My_model extends BaseModel
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
class My_model extends BaseModel
{
    const SOFT_DELETED = false;
}
```

---

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
class My_model extends BaseModel
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

---

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

Read & Write Connections could be set in the model which extends `BaseModel`, you could defind the read & write databases in extended `My_model` for every models.

There are three types to set read & write databases:


#### Codeigniter Database Key

You could set the database key refering from `\application\config\database.php` to `database` & `databaseRead` attribute, the setting connections would be created automatically:

```php
class My_model extends BaseModel
{
    protected $database = 'default';
    
    protected $databaseRead = 'slave';
}
```

> This method supports cache mechanism for DB connections, each model could define its own connections but share the same connection by key.

#### Codeigniter DB Connection

If you already have prepared CI DB connections, you could assign to attributes directly in construct section before parent's constrcut: 

```php
class My_model extends BaseModel
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
class My_model extends BaseModel
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

After that, you could use database key `slave` to load or assign it to attribute likes: `protected $databaseRead = 'slave';`

