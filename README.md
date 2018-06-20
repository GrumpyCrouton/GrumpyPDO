# GrumpyPDO
A simple PHP class wrapper for PDO.

I called this project GrumpyPDO because the native syntax made me so grumpy! (Actually, it's just because my username is Grumpy and I'm not very creative)

If your project is using GrumpyPDO, please send me a message and give me the details of your project! I would love to compile a list of projects that is currently using GrumpyPDO.

## Background

After refactoring a ton of SQLite statements to PDO, I realized there wasn't really an alternative to SQLite's [querysingle](http://php.net/manual/en/sqlite3.querysingle.php) function. I also realized that my code was a bit longer than it used to be, because every statement needed parameters to be binded seperately from the query itself. This is when I wrote a simple function that I believe made my syntax **easier to read**, **easier to use**, and most importantly **more secure** as I'm effectively able to use prepared statements in a way that is more comfortable, thus making me more likely to use them more often and whenever necessary.

This function has helped me out tremendously, but I have since converted it into an actual class, which is what this project is.

## Installation Instructions

This project is simply 1 file, `grumpypdo.php`.

All you need to do is download the file (or copy it's contents and put them in your own file), include the file on the page, and then initialize a variable while calling the class.

```
include "grumpypdo.php";
$db = new GrumpyPDO("localhost", "username", "password", "database");
```
This will load all of the GrumpyPDO default attributes and the default charset for your connection, which are as follows:

- PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
- PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
- PDO::ATTR_EMULATE_PREPARES => false,
- charset: utf8

**_If you would like more detailed setup instructions including how to overwrite the default attributes and charset, please [check out this wiki page](https://github.com/GrumpyCrouton/GrumpyPDO/wiki/Page-Setup---PDO--VS-GrumpyPDO)_**

## Simple Usage Instructions

I wrote this class going for simplicity. As such, it is very easy to use. This is some basic ways that you can use this class, but some more complicated things may be documented in the wiki.

### Select & Loop

When I was researching PDO and `mysqli_*`, I had a hard time figuring out how to do simple things like looping through results from a query (Using prepared statements). There were few straightforward answers for this, so I'm going to try to explain it the best I can.

Now I remember when I first used `mysqli_*` you would use a while statement, and it's pretty easy to do, but not as easy with prepared statements. Here is how you do it with my function. Let's say we have these headers:

Table Name: **users**

| uid | fname | lname |
| --- | --- | --- |
| 1 | John | Doe |
| 2 | Jane | Doe |
| 3 | Oswald | Trackt |
| 4 | John | Baldwin |

Let's start with selecting all columns.

```
$stmt = $db->run("SELECT `fname`, `lname` FROM users")->fetchAll();
//OR
$stmt = $db->all("SELECT `fname`, `lname` FROM users");
```

Notice you can use `fetchAll()` after the query, this is a "PDOStatement". Because the class method `run()` returns the query as an object, you can use native PDO statement types, making this solution very powerful.
[Here is some more PDOStatements that can be used with this class](http://php.net/manual/en/class.pdostatement.php)
Or you can simply use the built in "Quick Queries" as noted in the code snippet above.

Moving on, the above query will return an array that looks like this:

```
Array
(
    [0] => Array
        (
            [fname] => John
            [lname] => Doe
        )

    [1] => Array
        (
            [fname] => Jane
            [lname] => Doe
        )

    [2] => Array
        (
            [fname] => Oswald
            [lname] => Trackt
        )

    [3] => Array
        (
            [fname] => John
            [lname] => Baldwin
        )

)
```

### Why GrumpyPDO is useful

I'm sure you have noticed that regular queries are exactly the same syntax as native PDO (Except use of `run()` instead of `query()`), but you can skip all of the setup as it is already done for you in the class.

The class really comes in handy when you consider parameterizing your queries. This class allows you to **easily** prepare your queries and pass variables all in one line of code.

Consider the table from above, and consider that we only want results of people who's name is "John".

```
$name = "John";
$stmt = $db->run("SELECT fname, lname FROM users WHERE fname=?", [$name])->fetchAll();
//OR
$stmt = $db->all("SELECT fname, lname FROM users WHERE fname=?", [$name]);
```

You could also use named variables.

```
$name = "John";
$stmt = $db->run("SELECT fname, lname FROM users WHERE fname=:name", ["name" => $name])->fetchAll();
//OR
$stmt = $db->all("SELECT fname, lname FROM users WHERE fname=:name", ["name" => $name]);
```

The code above will return an array:

```
Array
(
    [1] => Array
        (
            [fname] => John
            [lname] => Doe
        )

    [2] => Array
        (
            [fname] => John
            [lname] => Baldwin
        )

)
```

**_If you would like a more in-depth explanation of the differences between this query in GrumpyPDO VS Native PDO, check out [this wiki article](https://github.com/GrumpyCrouton/GrumpyPDO/wiki/Usage---Select-Many-Rows)_**

# Projects using GrumpyPDO

- Multiple private projects by GrumpyCrouton.

# Contributors
- Project Founder - [GrumpyCrouton](https://stackoverflow.com/users/5827005/grumpycrouton)
- Contributor (Via [CodeReview (StackExchange)](https://codereview.stackexchange.com/a/177858/96569)) - [mheinzerling](https://codereview.stackexchange.com/users/21181/mheinzerling)
- Contributor - [colshrapnel](https://github.com/colshrapnel)

If you would like to help contribute to this project, please let me know, or send in some requests!

Additional tags: PDO Class, Easy Prepared Statements
