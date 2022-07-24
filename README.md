# GrumpyPDO

A simple and secure PHP class wrapper for designed for simplicity and ease of use, and makes running database queries **easier**, **cleaner**, and most importantly **safer** without the learning curve of how to utilize Prepared Statements.

GrumpyPDO is an extension of PDO, so any regular PDO methods also work with GrumpyPDO. This means you can switch from regular PDO to GrumpyPDO at any time and it won't break your existing code.

# Installation Instructions

## Composer

- GrumpyPDO has Composer support! The composer package is `grumpycrouton/grumpypdo`

## Manual

- Copy `grumpypdo.php` into your server, then include it in your page and initialize it as a variable.

```
include "grumpypdo.php";
$db = new GrumpyPDO("localhost", "username", "password", "database");
```

The snippet above will load GrumpyPDO's default attributes, which are as follows:

- PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
- PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
- PDO::ATTR_EMULATE_PREPARES => false,
- charset: utf8

If you want to add more attributes, or alter existing ones, pass an array of attributes to replace as a 5th parameter of the class initialization.

# How to Use

## Selecting Data

### Manual Select Queries

For any query, you could choose to use the run() method. This method returns a PDOStatement Object, so you can interact with it like native PDO.

```
$output = $db->run("SELECT * FROM users WHERE type=?", ['admin']);
```

This is essentially the same as running `$pdo->query()` or `$pdo->prepare()` (depending on if you send any values with it or not) and executing the query at the same time, then returning the PDOStatement Object from that. This means you can use one of PDO's many [Fetch Modes](https://phpdelusions.net/pdo/fetch_modes) from there.

### Quick Queries

All of the following examples can accept up to 2 parameters. `$query` and `$values`.

- `$query` should be a parameterized query string using either anonymous placeholders or named placeholders.
- `$values` can either be nothing, an empty array, or a key value pair of values to pass to the query. Should reflect the placeholders placed in the query, including position when using anonymous placeholders.

| Method | Description | Example Usage |
|---|---|---|
| all() | Fetch all of the results from the database into a multidimensional array. | `$query = "SELECT * FROM users WHERE type=?"`<br> `$values = array('admin')` |
| row() | Fetch a singular row from the database in a flat array. | `$query = "SELECT * FROM users WHERE uid=?"`<br> `$values = array(4)` |
| cell() | Fetch a single cell from the database. Doesn't support multiple rows. | `$query = "SELECT name FROM users WHERE uid=?"`<br> `$values = array(4)` |
| column() | Fetch a single column from the database. Similar to cell(), except can have multiple rows. | `$query = "SELECT uid FROM users WHERE type=?"`<br> `$values = array('admin')`&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  |

#### Example Output: all()
```
[
   {
      "uid":4,
      "name":"John Doe",
      "type":"Admin"
   },
   {
      "uid":2,
      "name":"John Baldwin",
      "type":"Admin"
   }
]
```

#### Example Output: row()
```
{
  "uid":4,
  "name":"John Doe",
  "type":"Admin"
}
```

#### Example Output: cell()
```
{
  "uid":4,
  "name":"John Doe",
  "type":"Admin"
}
```

#### Example Output: column()
```
{2,4}
```

## Manipulating Data

### Method: insert()

The insert method allows you to insert data into a table using only the table name and an array of data.

Since this method accepts a table name and database columns that may come from a user, thus is unsafe, we also run 2 extra queries when running the insert method. These queries verify the table name exists in the currently connected database, and also makes sure the columns passed to the query exist in the given table name.

#### Single Data Set
```
$values = [
    'name' => 'Example Name'
];
$db->insert('users', $values);
```

#### Multiple Data Sets

Inserting this way is the most effecient way to insert multiple records at once. This will prepare the statement once, and execute each set against that single prepare.

```
$values = [
    ['name' => 'Example Name 1'],
    ['name' => 'Example Name 2']
];
$db->insert('users', $values);
```

### Method: update()

The update method allows you to perform basic update operations on a single table at a time. It's not very useful for more advanced updates.

Since this method accepts a table name and database columns that may come from a user, thus is unsafe, we also run 2 extra queries when running the insert method. These queries verify the table name exists in the currently connected database, and also makes sure the columns passed to the query exist in the given table name.

Note: This method does not support multiple data sets

#### Usage
```
$updates = [
    "name" => "Updated Name"
];
$where = [
    "id" => 2
];
$db->update('users', $updates, $where);
```

### More Control and Other Manipulation: run()

The backbone of GrumpyPDO is it's `run()` method. This method is essentially the same as running `query()`/`prepare()` and executing the query at the same time. It allows you to write any kind of query you need, and pass the values to it at the same time.

This method returns a PDOStatement Object, which can be used just like native PDO to do whatever you need to do.

This method also supports multiple data sets.

#### Simple examples using run()
```
//delete
$db->run("DELETE FROM users WHERE id=?", array(4));

//update
$db->run("UPDATE users SET name=? WHERE id=?", array("Updated Name", 2));

//multi data insert
$inserts = [
    ['name' => "New Name 1"],
    ['name' => "New Name 2"]
];
$db->run("INSERT INTO users (name) VALUES (:name)", $inserts);

//multi data pdate
$updates = [
    ['name' => 'Updated Name 1', 'uid' => 2],
    ['name' => 'Updated Name 2', 'uid' => 4]
];
$db->run("UPDATE users SET name=:name WHERE uid=:uid", $updates);
```


# Contributors
- Project Founder - [GrumpyCrouton](https://stackoverflow.com/users/5827005/grumpycrouton)
- Contributor (Via [CodeReview (StackExchange)](https://codereview.stackexchange.com/a/177858/96569)) - [mheinzerling](https://codereview.stackexchange.com/users/21181/mheinzerling)
- Contributor - [colshrapnel](https://github.com/colshrapnel)

If you would like to help contribute to this project, please let me know, or send in some requests!
