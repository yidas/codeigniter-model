Example of My_model
===================

The best practice to use `BaseModel` is using `My_model` to extend for every models, you could refer the document [Use My_model to Extend BaseModel for every Models](https://github.com/yidas/codeigniter-model#use-my_model-to-extend-basemodel-for-every-models) for building the structure in your Codeigniter application.

[My_model example code](https://github.com/yidas/codeigniter-model/blob/master/example/My_model.php)

>Based on BaseModel, My_model is customized for your web application with schema such as primary key and column names for behavior setting. Futher, all of your model may need access features, such as the verification of user ID and company ID for multiple user layers.

---

Features
--------

This example My_model assumes that a user is belong to a company, so each data row is belong to a user with that company. The Model basic funcitons overrided BaseModel with user and company verification to implement the protection. 

---

CONFIGURATION
-------------

```php
class My_model extends BaseModel
{
    /* Configuration by Inheriting */
    
    // The regular PK Key in App
    protected $primaryKey = 'id';
    // Timestamps on
    protected $timestamps = true;
    // Soft Deleted on
    const SOFT_DELETED = 'is_deleted';
    
    protected function _globalScopes()
    {
        // Global Scope...
    }
    
    protected function _attrEventBeforeInsert(&$attributes)
    {
        // Insert Behavior...
    }
    
    // Other Behaviors...
}
```


Defining Models
---------------

### User ACL 

By default, `My_model` assumes that each row data in model is belong to a user, which means the Model would find and create data owned by current user. You may set `$userAttribute` to `NULL` to disable user ACL:

```php
class Post_model extends My_model
{
    protected $userAttribute = NULL;
}
```

If you need to customize the names of the user ACL column, you may set the `$userAttribute` arrtibute in your specified model:

```php
class Post_model extends My_model
{
    protected $userAttribute = 'user_id_for_post';
}
```

### Company ACL 

By default, `My_model` assumes that each row data in model is belong to a company, which means the Model would find and create data owned by current company. You may set `$companyAttribute` to `NULL` to disable company ACL:

```php
class Post_model extends My_model
{
    protected $companyAttribute = NULL;
}
```

If you need to customize the names of the company ACL column, you may set the `$companyAttribute` arrtibute in your specified model:

```php
class Post_model extends My_model
{
    protected $companyAttribute = 'company_id_for_post';
}
```

> If user ACL and company ACL are both disbled, which means this model won't filter any ACL scopes.

### Transaction Log

Likes Timestamps feature, you may need to record transaction Log for each row. By default, This example `My_model` expects `created_by` , `updated_by` and `deleted_by` columns to exist on your tables. If you do not wish to have these columns automatically managed by `My_model`, set each property on your model to `NULL`:

```php
class Post_model extends My_model
{
    protected $createdUserAttribute = 'created_by';
    
    protected $updatedUserAttribute = 'updated_by';
    
    protected $deletedUserAttribute = NULL;
}
```



