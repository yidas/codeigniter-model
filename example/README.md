Example of My_model
===================

You could make My_model extends `BaseModel` for each model in your application.

Example of [My_model](https://github.com/yidas/codeigniter-model/blob/master/example/My_model.php):

>Based on BaseModel, My_model is customized for your web application with features, such as the verification of user ID and company ID for multiple user layers.

---

Features
--------

This example My_model assumes that a user is belong to a company, so each data row is belong to a user with that company. The Model basic funcitons overrided BaseModel with user and company verification to implement the protection. 


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

