User ACL My_model
=================

This example My_model assumes that a user is belong to a company, so each data row is belong to a user with that company. The Model basic funcitons overrided BaseModel with user and company verification to implement the protection. 

>Based on BaseModel, My_model is customized for your web application with schema such as primary key and column names for behavior setting. Futher, all of your model may need access features, such as the verification of user ID and company ID for multiple user layers.

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
    protected $userAttribute = false;
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
    protected $companyAttribute = false;
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

---

TABLE SCHEMA
------------

### MySQL

```sql
CREATE TABLE `table` (
  `id` bigint(20) NOT NULL,
  `company_id` bigint(20) NOT NULL
  `user_id` bigint(20) NOT NULL
  `created_at` datetime NOT NULL
  `created_by` bigint(20) UNSIGNED NOT NULL
  `updated_at` datetime NOT NULL
  `updated_by` bigint(20) UNSIGNED NOT NULL
  `deleted_at` datetime NOT NULL
  `deleted_by` bigint(20) UNSIGNED NOT NULL
  `is_deleted` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `table`
--
ALTER TABLE `table`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `table`
--
ALTER TABLE `table`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;COMMIT;
```

