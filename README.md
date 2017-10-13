# GrumpyPDO
A simple PHP class wrapper for PDO.

I called this project GrumpyPDO because the native syntax made me so grumpy! (Actually, it's just because my username is Grumpy and I'm not very creative)

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

As of patch-1 you can also set PDO attributes and a charset on the fly.
```
include "grumpypdo.php";

$opt = [
    "charset" => "utf8",
    "options" => [
    	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES => false,
    ]
];

$db = new GrumpyPDO("localhost", "username", "password", "database", $opt);
```

> Note: In the example above with the `$opt` variable, these are the default settings of the class, but I was trying to show that you can set whatever you want there.

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

#### Native VS GrumpyPDO

##### GrumpyPDO
```
$stmt = $db->run("SELECT * FROM users")->fetchAll();
```

Notice I used `fetchAll()` after the query, this is a "PDOStatement". Because the class returns the query as an object, you can use native PDO statement types, making this solution very powerful.
[Here is some more PDOStatements that can be used with this class](http://php.net/manual/en/class.pdostatement.php)

##### Native PDO

First, we would have to set up the database connection.

```
//Setting up the database connection
$host = 'localhost';
$db   = '';
$user = '';
$pass = '';
$charset = 'utf8';

$opt = [
	PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES   => false,
];
$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

$db = new PDO($dsn, $user, $pass, $opt);
//end setting up
```

Then we can actually do the query:

```
$stmt = $db->query("SELECT * FROM users")->fetchAll();
```

#### Results

Moving on, the above query will return an array of values. This array will look like this:

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

    [2] => Array
        (
            [uid] => 3
            [fname] => Oswald
            [lname] => Trackt
        )

    [3] => Array
        (
            [uid] => 4
            [fname] => John
            [lname] => Baldwin
        )

)
```

And from there, all you really need to do is loop through the array like any other PHP array. In other interfaces I have mostly seen people use while loops to get through their data, but for this data you can use a for loop or a foreach loop, which I find easier and cleaner.

### Why GrumpyPDO is useful

If you notice, regular queries are exactly the same syntax as native PDO (Except use of `run()` instead of `query()`), but you can skip all of the setup as it is already done for you in the class.

The class really comes in handy when you consider parameterizing your queries. This class allows you to **easily** prepare your queries and pass variables all in one line of code.

Consider the table from above, and consider that we only want results of people who's name is "John".

#### Native VS GrumpyPDO

#### GrumpyPDO
```
$name = "John";
$stmt = $db->run("SELECT * FROM users WHERE fname=?", [$name])->fetchAll();
//OR
$stmt = $db->run("SELECT * FROM users WHERE fname=:name", ["name" => $name])->fetchAll();
```

#### Native PDO
Natively, it's a bit more code to do this. 
```
$name = "John";

$stmt = $db->prepare("SELECT * FROM users WHERE fname=?");
$stmt->execute([$name]);
//OR
$stmt = $stmt->prepare("SELECT * FROM users WHERE fname=:name");
$stmt->bindParam(':name', $name);
$stmt->execute();

$result = $stmt->fetchAll();
```

#### Results

Each would return the same, following array, but _in my opinion_, the GrumpyPDO syntax is much simpler, you don't have to remember to `prepare()` (As it's always `run()`), AND it only takes 1 line for the actual query instead of 3-4. I think you could technically write native PDO all in one line, but it would be a pretty long line and would probably hurt readability. 

```
Array
(
    [1] => Array
        (
            [uid] => 1
            [fname] => John
            [lname] => Doe
        )

    [2] => Array
        (
            [uid] => 4
            [fname] => John
            [lname] => Baldwin
        )

)
```

# Contributors
- Project Founder - [GrumpyCrouton](https://stackoverflow.com/users/5827005/grumpycrouton)
- Contributor (Via [StackOverflow](https://codereview.stackexchange.com/a/177858/96569)) - [mheinzerling](https://codereview.stackexchange.com/users/21181/mheinzerling)

If you would like to help contribute to this project, please let me know.
