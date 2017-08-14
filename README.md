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

* [Usage](#usage)

---

DEMONSTRATION
-------------

### Find one
```php
$post = $this->PostModel->findOne(123)
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
    
> You could customize the vendor path for `$config['composer_autoload']`

---

CONFIGURATION
-------------

### Extend BaseModel for Your Application Models

You could extend the `BaseModel` for each model in your application, but we recommend you to make a My_model extended `BaseModel` for each model.

### Extend BaseModel for Your Application BaseModel

You could make My_model for each model in your application.

Example of [My_model](https://github.com/yidas/codeigniter-model/blob/master/example/My_model.php):

>Based on BaseModel, My_model is customized for your web application with features, such as the verification of user ID and company ID for multiple user layers.
>
>This example My_model assumes that a user is belong to a company, so each data row is belong to a user with that company. The Model basic funcitons overrided BaseModel with user and company verification to implement the protection. 

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

### findOne()

Returns a single record array by a primary key or an array of column values with Model Filters.

```php
$post = $this->PostModel->findOne(123);
```

### findAll()

Returns a list of records that match the specified primary key value(s) or a set of column values with Model Filters.

```php
$post = $this->PostModel->findAll([3,21,135]);
```

### insert()

Inserts a row with Timestamps feature into the associated database table using the attribute values of this record.

```php
$result = $this->Model->insert([
  'name' => 'Nick Tsai',
  'email' => 'myintaer@gmail.com',
]);
```

### update()

Saves the changes with Timestamps feature to the selected record(s) into the associated database table.

```php
$result = $this->Model->update(['status'=>'off'], 123)
```
Query builder ORM usage:

```php
$this->Model->find()->where('id', 123);
$result = $this->Model->update(['status'=>'off']);
```

### delete()

Deletes the selected record(s) with Timestamps feature into the associated database table.

```php
$result = $this->Model->delete(123)
```
Query builder ORM usage:

```php
$this->Model->find()->where('id', 123);
$result = $this->Model->delete();
```

---

SOFT DELETED
------------

---

QUERY SCOPES
------------
