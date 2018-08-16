# GrumpyPDO
A simple PHP class wrapper for PDO.

I called this project GrumpyPDO because the native syntax made me so grumpy! (Actually, it's just because my username is Grumpy and I'm not very creative)

If your project is using GrumpyPDO, please send me a message and give me the details of your project! I would love to compile a list of projects that is currently using GrumpyPDO.

## Background

I wrote this simple class based on a function that I wrote when I switched from SQLite to PDO statements. It was designed for simplicity and ease of use, and makes running database queries **easier**, **cleaner**, and most importantly **safer** without the learning curve of how to utilize Prepared Statements.

## Installation Instructions

### Composer

- GrumpyPDO has Composer support! The composer package is `grumpycrouton/grumpypdo`

### Manual

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

## Basic Use Instructions

This section will walk through some basic use instructions. All of the following examples will utilize the following database table:

Table: **users**:

| uid           |    first_name |     last_name |
| ------------- | ------------- | ------------- |
| 1             |   John        |     Doe       |
| 2             |   Jane        |     Doe       |
| 3             | Oswald        |  Trackt       |
| 4             |   John        | Baldwin       |

### Fetching Data

#### SELECT Without Variables

The most basic query, of course, is a `SELECT` query with no variables attached.

```
//using Method Chaining;
$results = $db->run('SELECT * FROM `users`')->fetchAll();
    
//using Quick Queries
$results = $db->all('SELECT * FROM `users`');
```

Both of the above are equivalent in GrumpyPDO, and will result in `$results` being a multidimensional array with all of the data from every row of the table in it's own subarray.

```
Array
(
    [0] => Array
        (
            [uid] => 1
            [fname] => John
            [lname] => Doe
        )
    [1] => Array
        (
            [uid] => 2
            [fname] => Jane
            [lname] => Doe
        )
    ...
)
```

#### SELECT With Variables

Of course most of the time you'll want to use variables with your queries to get only information that is relevant to a specific use, but you don't want to send your variables inside the query because it puts you at risk for [SQL Injection](https://stackoverflow.com/questions/601300/what-is-sql-injection). Passing variables this way allows GrumpyPDO to automatically prepare your query and sent the data separately, mitigating risk of SQL Injection, this data does not need to be escaped or sanitized.

For this example, let's get all of the rows from the table that have the first name "John".

```
//using Method Chaining;
$results = $db->run('SELECT * FROM `users` WHERE `first_name`=?', ['John'])->fetchAll();
    
//using Quick Queries
$results = $db->all('SELECT * FROM `users` WHERE `first_name`=?', ['John']);
```

The above will result in this array:

```
Array
(
    [0] => Array
        (
            [uid] => 4
            [fname] => John
            [lname] => Doe
        )
    [1] => Array
        (
            [uid] => 2
            [fname] => John
            [lname] => Baldwin
        )
)
```

##### Single Row Results

If you are only expecting a single row result, there is no reason to pull the data into a multidimensional array, it's better to pull it into a single array of values. With GrumpyPDO, this is easy:

```
//using Method Chaining;
$result = $db->run('SELECT * FROM `users` WHERE `uid`=?', [1])->fetch();
    
//using Quick Queries
$result = $db->row('SELECT * FROM `users` WHERE `uid`=?', [1]);
```

The above will result in this array:

```
Array
(
    [uid] => 1
    [fname] => John
    [lname] => Doe
)
```

##### Single Value Results

If you only want a single value result, such as the `first_name` of a specific user, you can do:

```
//using Method Chaining;
$result = $db->run('SELECT `first_name` FROM `users` WHERE `uid`=?', [1])->fetchColumn();
    
//using Quick Queries
$result = $db->cell('SELECT `first_name` FROM `users` WHERE `uid`=?', [1]);
```

The above will set `$result` to equal `John`.

### Data Manipulation

Now that we know how to pull information from our database, we need to learn how to do DML queries such as `INSERT`, `UPDATE`, and `DELETE`.

#### Basic Usage

A majority of DML queries are quite simple, and don't need any special markup the only method you will use is `run()`.

```
$db->run('INSERT INTO `users` (`first_name`, `last_name`) VALUES (?, ?)', ['John', 'Cena']); //lets assume this is inserted with `uid`=5
    
$db->run('UPDATE `users` SET `first_name`=? WHERE `uid`=?', ['Barbara', 5]); //changes first_name of row with uid 5 to "Barbara"
    
$db->run('DELETE FROM users WHERE `uid`=?', [5]); //Deletes row with uid 5
```

Again, passing variables this way allows GrumpyPDO to automatically prepare your query and sent the data separately, mitigating risk of SQL Injection, this data does not need to be escaped or sanitized.

#### Multiple Datasets With One Call

GrumpyPDO has the ability to execute multiple sets of data using a single `prepare()`, thus reducing your database touches by half + 1.

Sure, you can easily use a loop to process multiple sets of data using `$db->run()`, but this means that `prepare()` and `execute()` will both be ran _per query_ and both of those methods send a request to your database. (So for example if you are inserting 50 names to your database, it will send 100 different requests to your database)

But there is a better way! Simply form a multidimensional array of all of the values you'd like to send to the database, and pass that multidimensional array all at once instead of each set of data at a time. For a quick example, let's insert 3 new users into our users table.

```
$new_users = array(
    array("John", "Cena"),
    array("Andy", "Samberg"),
    array("Adam", "Sandler")
);

$this->run("INSERT INTO `users` (`first_name`, `last_name`) VALUES (?, ?)", $new_users);
```

This will run `prepare()` a single time, and then `execute()` on all of the subarrays, meaning if you inserted all of these names separately it would send the database 6 requests, but if you use this method it will only send 4 requests (1 per subarray and 1 for `prepare()`)

For small datasets, this is kind of overkill, but for larger datasets it is a huge improvement. Consider if you're adding 100 new rows, this method will send 101 requests to your database but doing it one at a time will send 200 requests.

### Bonus Features

#### Named Parameters

GrumpyPDO supports named parameters.

```
$db->run("SELECT * FROM `users` WHERE `first_name`=:name", ["name" => 'John']);
```

# Contributors
- Project Founder - [GrumpyCrouton](https://stackoverflow.com/users/5827005/grumpycrouton)
- Contributor (Via [CodeReview (StackExchange)](https://codereview.stackexchange.com/a/177858/96569)) - [mheinzerling](https://codereview.stackexchange.com/users/21181/mheinzerling)
- Contributor - [colshrapnel](https://github.com/colshrapnel)

If you would like to help contribute to this project, please let me know, or send in some requests!
