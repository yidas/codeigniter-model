<p align="center">
    <a href="https://codeigniter.com/" target="_blank">
        <img src="https://codeigniter.com/assets/images/ci-logo-big.png" height="100px">
    </a>
    <h1 align="center">CodeIgniter Model</h1>
    <br>
</p>

CodeIgniter 3 ORM Base Model pattern with My_model example

[![Latest Stable Version](https://poser.pugx.org/yidas/codeigniter-model/v/stable?format=flat-square)](https://packagist.org/packages/yidas/codeigniter-model)
[![Latest Unstable Version](https://poser.pugx.org/yidas/codeigniter-model/v/unstable?format=flat-square)](https://packagist.org/packages/yidas/codeigniter-model)
[![License](https://poser.pugx.org/yidas/codeigniter-model/license?format=flat-square)](https://packagist.org/packages/yidas/codeigniter-model)

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

Run Composer in your Codeigniter project:

    composer require yidas/codeigniter-model
    
Check Codeigniter `application/config/config.php`:

    $config['composer_autoload'] = TRUE;
    
> You could customize the vendor path into `$config['composer_autoload']`

---

CONFIGURATION
-------------

### Extend BaseModel for Your Application Models

You could extend the `BaseModel` for each model in your application:

```php
class Post_model extends BaseModel
{
    protected $table = "post_table";
    protected $primaryKey = 'sn';
    // Configuration by Inheriting...
}
```

The model is ready to use after extending `BaseModel` with basic configuration, :

```php
$this->load->model('Post_model');
$post = $this->Post_model->findOne(123);
```

Instead of direct extending application models, we recommend you to make a My_model to extend `BaseModel` for each model.

### Extend BaseModel for Your My_model in Application

You could make My_model for each model in your application.

Example of [My_model](https://github.com/yidas/codeigniter-model/blob/master/example/My_model.php):

>Based on BaseModel, My_model is customized for your web application with features, such as the verification of user ID and company ID for multiple user layers.
>
>This example My_model assumes that a user is belong to a company, so each data row is belong to a user with that company. The Model basic funcitons overrided BaseModel with user and company verification to implement the protection. 

```php
class My_model extends BaseModel
{
    protected $primaryKey = 'sn';
    // Configuration by Inheriting...
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
    protected $primaryKey = "id";
}
```

### Timestamps

By default, BaseModel expects `created_at` and `updated_at` columns to exist on your tables. If you do not wish to have these columns automatically managed by BaseModel, set the `$timestamps` property on your model to false:

```php
class Post_model extends BaseModel
{
    protected $timestamps = false;
}
```

If you need to customize the format of your timestamps, set the `$dateFormat` property on your model. This property determines how date attributes are stored in the database:

```php
class Post_model extends BaseModel
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
class Post_model extends BaseModel
{
    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'last_update';
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
// Without all featured conditions for next find()
$posts = $this->PostModel->find(true)
    ->where('is_deleted', '1')
    ->get()
    ->result_array();
    
// This is equal to withAll() method
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
// Query builder ORM usage
$this->Model->find()->where('id', 123);
$result = $this->Model->update(['status'=>'off']);
```

### delete()

Delete the selected record(s) with Timestamps feature into the associated database table.

```php
$result = $this->Model->delete(123)
```

```php
// Query builder ORM usage
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

You could enable SOFT DELETED feature by giving field name to `SOFT_DELETED`, the settings are below:

- SOFT_DELETED: Feild name for SOFT_DELETED, empty is disabled.

- $softDeletedFalseValue: The actived value for SOFT_DELETED

- $softDeletedTrueValue: The deleted value for SOFT_DELETED

- DELETED_AT: (Optional) Feild name for deleted_at, empty is disabled.

```php
class My_model extends BaseModel
{
    const SOFT_DELETED = 'is_deleted';

    protected $softDeletedFalseValue = '0';

    protected $softDeletedTrueValue = '1';

    const DELETED_AT = 'deleted_at';
    
    // Other settings...
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

Without SOFT_DELETED query conditions for next find()

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

Without all query conditions for next find()

That is, with all set of Models for next find()

```php
$this->Model->withAll()->find();
```
