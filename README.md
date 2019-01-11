# Spdo
Simple PDO Database Class

 - Less coding
 - PDO-Based Structure
 - Multiple Database Connection Possiblity
 - Multiple Output Possiblity (json, xml, object, array)
 - CodeIgniter 2.x and 3.x Support
 
You must download Spdo.php file from Github servers to your project directory. After downloading, you must create a database connection configuration file. 

## Sample Database Connection Configuration:

```php
$db = [
  "default" => [
    "database" => "YourDatabaseName",
    "hostname" => "MysqlHostname",
    "username" => "MysqlUsername",
    "password" => "MysqlUserPassword",
    "char_set" => "utf8",
  ],
  "secondaryDB" => [
    "database" => "YourDatabaseName",
    "hostname" => "MysqlHostname",
    "username" => "MysqlUsername",
    "password" => "MysqlUserPassword",
    "char_set" => "utf8",
  ]
];
```
Maybe, you want to include this codes to your own configuration file. No problem, just this codes must be before Spdo class. 

## Sample Including Spdo Class To Project:

```php
include "config.php";
include "Spdo.php";

$spdo = new Spdo();
```

If you want this class to **CodeIgniter** project, you must follow this rules;

 - Add a value to $autoload variable like as this;

```php
$autoload['libraries'] = array('Spdo');
```

 - You must move Spdo.php file to application/libraries directory.
 - Open Spdo.php file and apply like as this changes;
 
```php 
#require_once APPPATH . 'config/database.php'; 
```
to
```php
require_once APPPATH . 'config/database.php'; 
```

 - Open application/config/database.php and edit database configuration values for your database.
 
## Functions List:
 
 - errors()
 - getErrors()
 - getLastQuery()
 - numRows()
 - getResults()
 - getRow()
 - getVar()
 - execute()
 - insert()
 - update()
 - delete()
 
## Some Sample For CRUD Processes:

### Using getResults() function

```php
$results = $spdo->getResults('SELECT * FROM categories');
```
**Returning data type:** object

```php
$results = $spdo->getResults('SELECT * FROM categories', ["returnDataType" => "json"]);
```
**Returning data type:** json
**returnDataType** parameter's value can be object, array, json or xml.

```php
$results = $spdo->getResults('SELECT * FROM categories WHERE status > ?', 
["bindValues" => ["active"], "returnDataType" => "json"]);
```
**Returning data type:** json
**bindValues** parameter's value can be array or string.

```php
$results = $spdo->getResults('SELECT * FROM categories WHERE status > ?', 
["bindValues" => ["active"], "returnDataType" => "json", "configKey" => "secondaryDB"]);
```
**Returning data type:** json
**configKey** parameter's value can be a parameter name on the your config file.

### Using getRow() function

```php
$result = $spdo->getRow('SELECT * FROM categories ID = 5');
```
**Returning data type:** object

```php
$result = $spdo->getRow('SELECT * FROM categories ID = 5', array("returnDataType" => "json"));
```
**Returning data type:** json
**returnDataType** parameter's value can be object, array, json or xml.

```php
$result = $spdo->getRow('SELECT * FROM categories WHERE name = ?', array("bindValues" => array("Technology"), "returnDataType" => "json"));
```
**Returning data type:** json
**bindValues** parameter's value can be array or string.

```php
$result = $spdo->getRow('SELECT * FROM categories WHERE name = ?', array("bindValues" => array("Technology"), "returnDataType" => "json", "configKey" => "secondaryDB"));
```
**Returning data type:** json
**configKey** parameter's value can be a parameter name on the your config file.


### Using getVar() function

```php
$categoryName = $spdo->getVar('SELECT name FROM categories ID = 5');
```
**Returning data type:** string

```php
$categoryName = $spdo->getVar('SELECT name FROM categories ID = 5', array("returnDataType" => "json"));
```
**Returning data type:** json
**returnDataType** parameter's value can be object, array, json or xml

```php
$categoryStatus = $spdo->getVar('SELECT status FROM categories WHERE name = ?', array("bindValues" => array("Technology"), "returnDataType" => "json"));
```
**Returning data type:**
**bindValues** parameter's value can be array or string.

```php
$categoryStatus = $spdo->getVar('SELECT status FROM categories WHERE name = ?', array("bindValues" => array("Technology"), "returnDataType" => "json", "configKey" => "secondaryDB"));
```
**Returning data type.**
**configKey** parameter's value can be a parameter name on the your config file.

### Using insert() function

```php
$category = array('name' => 'Technology', 'status' => 'active');
$result = $spdo->insert('categories', $category);
```
**Returning data:** Inserted Row Id
**First parameter:** table name
**Secondary paramater:** column names and values

```php
$category = array('name' => 'Technology', 'status' => 'active');
$result = $spdo->insert('categories', $category, array('configKey' => 'secondaryDB'));
```
**Returning data:** Inserted row id
**First parameter:** table name
**Secondary paramater:** column names and values
**configKey** paramater's can be a parameter name on the config file.

### Using update() function

```php
$result = $spdo->update('categories', array('status' => 'active'), array('ID > ?'), array(5));
```
**Returning data:** Affected row number
**First parameter:** table name
**Secondary parameter:** column names and values
**Third parameter:** Where block
**Fourth parameter:** Where block values

```php
$result = $spdo->update('categories', array('status' => 'active'), array('ID > ?'), array(5), array('configKey' => 'secondaryDB'));
```
**Returning data:** Affected row number
**First parameter:** table name
**Secondary parameter:** column names and values
**Third parameter:** Where block
**Fourth parameter:** Where block values
**configKey** parameter's can be a paramater name on the config file.

### Using delete() function 

```php
$result = $spdo->delete('categories', array('ID = 5'));
```
**First paramater:** table name
**Secondary paramater:** where block

```php
$result = $spdo->delete('categories', array('ID = ?'), array(5));
```
**First paramater:** table name
**Secondary parameter:** where block
**Third parameter:** where block values

```php
$result = $spdo->delete('categories', array('ID = ?'), array(5), array('configKey' => 'secondaryDB'));
```
**First paramater:** table name
**Secondary parameter:** where block
**Third parameter:** where block values
**configKey** value can be a parameter name on the config file.

### Using execute() function

Samples are in the below:

**Sample 1:**
```php
$result = $spdo->execute('INSERT INTO categories SET name = ?, status = ?', array('bindValues' => array('Technology', 'status')));
```

**Sample 2:**
```php
$result = $spdo->execute('INSERT INTO categories SET name = ?, status = ?', array('bindValues' => array('Technology', 'active'), 'configKey' => 'secondaryDB'));
```

**Sample 3:**
```php
$result = $spdo->execute('DELETE FROM categories WHERE ID = ?', array('bindValues' => array(5)));
```

**Sample 4:**
```php
$result = $spdo->execute('DELETE FROM categories WHERE ID = ?', array('bindValues' => array(5), 'configKey' => 'secondaryDB'));
```

**Sample 5:**
```php
$result = $spdo->execute('UPDATE categories SET name = ? WHERE ID = ?', array('bindValues' => array('Tech', 5)));
```

**Sample 6**
```php
$result = $spdo->execute('UPDATE categories SET name = ? WHERE ID = ?', array('bindValues' => array('Tech', 5), 'configKey' => 'secondaryDB'));
```

### Other functions

```php
$spdo->errors();
```
This function shows all errors.

```php
$errors = $spdo->getErrors();
```
This function returns all errors.

```php
$spdo->getLastQuery();
```
This function will return data as following;

- SQL query,
- Binding values,
- Selected database,
- Returned data type,

```php
echo $spdo->numRows();
```
This function returns selected rows in the SELECT query.

If you're have a question, please send an email to gurkan@grkn.co

***Gurkan Bicer***
