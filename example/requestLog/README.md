Log My_model
============

Use for log table without ACL concern.

---

TABLE SCHEMA
------------

### MySQL

```sql
CREATE TABLE `table` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ip` char(15) DEFAULT NULL COMMENT 'IP header',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'User-Agent header',
  `created_at` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `table`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `table`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;COMMIT;
```

