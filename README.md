# GrumpyPDO
A simple PHP class wrapper for PDO.

## Background

One of my biggest projects used to use a PHP database interface called SQLite, which has a function called [querysingle](http://php.net/manual/en/sqlite3.querysingle.php).
Well, for this project I decided that I wanted to switch to using MySQL, which obviously does not support the SQLite interface, and I needed to choose between `mysqli_*` and PDO.
I started with `mysqli_*` as I had already used it before, but I had zero experience with Prepared Statements, and I wanted my application to have good security, so I tried my hand at prepared statements with `mysqli_*` which worked but I didn't like the syntax, I didn't like the way the interface worked. So I tried PDO, which to me was much easier than `mysqli_*` prepared statements, and the syntax was a bit better.

After refactoring so many SQLite statements to PDO, I realized there wasn't really an alternative to SQLite's querysingle function. I also realized that my code was a bit longer than it used to be, because every statement needed parameters to be binded seperately from the query itself. This is when I wrote a simple function that I believe made my syntax **easier to read**, **easier to use**, and most importantly **more secure** as I'm effectively able to use prepared statements in a way that is more comfortable, thus making me more likely to use them more often and whenever necessary.

This function has helped me out tremendously, but I have since converted it into an actual class, which is what this project is.
