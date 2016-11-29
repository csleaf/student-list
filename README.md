# student list

### Requirements
* PHP 7.0 or above
* Apache 2.0 or above
* MySQL 5.0 or above
* Composer (to resolve project PHP dependencies)
* Python 2.* (to use genusers tool)

### Installation
* Clone repository with `git clone https://github.com/csleaf/student-list.git`.
* Execute SQL from `db.sql` to create `students` database and tables.
```bash
$ cd student-list
$ mysql -u <mysql_root_user> -p
mysql> source db.sql
mysql> exit
```
* Resolve PHP dependencies with Composer
```bash
$ composer update
```
* Configure Apache to use PHP and serve files from web/ folder.
* Generate stub users using tools/genusers/genusers.py. This tool will generate `users.sql` file that must be run like `db.sql` file.
* Create `app.ini` file in root folder:
```ini
[db]
username=<mysql username>
password=<mysql user password>
hostname=<mysql hostname>
dbname=students

[students_table]
limit=50
```
