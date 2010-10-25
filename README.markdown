Idiorm
======

A lightweight nearly-zero-configuration object-relational mapper and fluent query builder for PHP5.

Released under a [BSD license](http://en.wikipedia.org/wiki/BSD_licenses).

**See Also: [Paris](http://github.com/j4mie/paris), an Active Record implementation built on top of Idiorm.**

Features
--------

* Makes simple queries and simple CRUD operations completely painless.
* Gets out of the way when more complex SQL is required.
* Built on top of [PDO](http://php.net/pdo).
* Uses [prepared statements](http://uk.php.net/manual/en/pdo.prepared-statements.php) throughout to protect against [SQL injection](http://en.wikipedia.org/wiki/SQL_injection) attacks.
* Requires no model classes, no XML configuration and no code generation: works out of the box, given only a connection string.
* Consists of just one class called `ORM`. Minimal global namespace pollution.
* Database agnostic (untested).

TODO
----

* Improve documentation.
* Extra `where_(*)` methods.
* More features.

Philosophy
----------

The [Pareto Principle](http://en.wikipedia.org/wiki/Pareto_principle) states that *roughly 80% of the effects come from 20% of the causes.* In software development terms, this could be translated into something along the lines of *80% of the results come from 20% of the complexity*. In other words, you can get pretty far by being pretty stupid. 

**Idiorm** is an experiment in how far it's possible to get with database abstraction while remaining as simple as possible. If my hunch is correct, this should be quite far enough for many real-world applications. Let's face it: most of us aren't building Facebook. We're building little toy projects, where the emphasis is on fun and rapid development rather than infinite flexibility and features.

You might think of **Idiorm** as a *micro-ORM*. It could, perhaps, be "the tie to go along with [Limonade](http://github.com/sofadesign/limonade/)'s tux" (to borrow a turn of phrase from [DocumentCloud](http://github.com/documentcloud/underscore)). Or it could be an effective bit of spring cleaning for one of those horrendous SQL-littered legacy PHP apps you have to support.

Let's See Some Code
-------------------

The first thing you need to know about Idiorm is that *you don't need to define any model classes to use it*. With almost every other ORM, the first thing to do is set up your models and map them to database tables (through configuration variables, XML files or similar). With Idiorm, you can start using the ORM straight away.

### Setup ###

First, `require` the Idiorm source file:

    require_once 'idiorm.php';

Then, pass a *Data Source Name* connection string to the `configure` method of the ORM class. This is used by PDO to connect to your database. For more information, see the [PDO documentation](http://uk2.php.net/manual/en/pdo.construct.php). Particularly, if you need to pass a username and password to your database driver, use the `username` and `password` configuration options. See "Configuration" section below.

    ORM::configure('sqlite:./example.db');

### Querying ###

Idiorm provides a [*fluent interface*](http://en.wikipedia.org/wiki/Fluent_interface) to enable simple queries to be built without writing a single character of SQL. If you've used [jQuery](http://jquery.com) at all, you'll be familiar with the concept of a fluent interface. It just means that you can *chain* method calls together, one after another. This can make your code more readable, as the method calls strung together in order can start to look a bit like a sentence.

All Idiorm queries start with a call to the `for_table` static method on the ORM class. This tells the ORM which table to use when making the query. 

*Note that this method **does not** escape its query parameter and so the table name should **not** be passed directly from user input.*

Method calls which add filters and constraints to your query are then strung together. Finally, the chain is finished by calling either `find_one()` or `find_many()`, which executes the query and returns the result.

Let's start with a simple example. Say we have a table called `person` which contains the columns `id` (the primary key of the record - Idiorm assumes the primary key column is called `id` but this is configurable, see below), `name`, `age` and `gender`.

#### Single records ####

Any method chain that ends in `find_one()` will return either a *single* instance of the ORM class representing the database row you requested, or `false` if no matching record was found.

To find a single record where the `name` column has the value "Fred Bloggs":

    $person = ORM::for_table('person')->where('name', 'Fred Bloggs')->find_one();

This roughly translates into the following SQL: `SELECT * FROM person WHERE name = "Fred Bloggs"`

To find a single record by ID, you can pass the ID directly to the `find_one` method:

    $person = ORM::for_table('person')->find_one(5);

#### Multiple records ####

Any method chain that ends in `find_many()` will return an *array* of ORM class instances, one for each row matched by your query. If no rows were found, an empty array will be returned.

To find all records in the table:

    $people = ORM::for_table('person')->find_many();

To find all records where the `gender` is `female`:

    $females = ORM::for_table('person')->where('gender', 'female')->find_many();

#### Counting results ####

To return a count of the number of rows that would be returned by a query, call the `count()` method.

    $number_of_people = ORM::for_table('person')->count();

#### Filtering results ####

Idiorm provides a family of methods to extract only records which satisfy some condition or conditions. These methods may be called multiple times to build up your query, and Idiorm's fluent interface allows method calls to be *chained* to create readable and simple-to-understand queries.

##### *Caveats* #####

Only a subset of the available conditions supported by SQL are available when using Idiorm. Additionally, all the `WHERE` clauses will be `AND`ed together when the query is run. Support for `OR`ing `WHERE` clauses is not currently present.

These limits are deliberate: these are by far the most commonly used criteria, and by avoiding support for very complex queries, the Idiorm codebase can remain small and simple.

Some support for more complex conditions and queries is provided by the `where_raw` and `raw_select` methods (see below). If you find yourself regularly requiring more functionality than Idiorm can provide, it may be time to consider using a more full-featured ORM.

##### Equality: `where` and `where_equal` #####

By default, calling `where` with two parameters (the column name and the value) will combine them using an equals operator (`=`). For example, calling `where('name', 'Fred')` will result in the clause `WHERE name = "Fred"`.

If your coding style favours clarity over brevity, you may prefer to use the `where_equal` method: this is identical to `where`.

##### Less than / greater than: `where_lt`, `where_gt`, `where_lte`, `where_gte` #####

There are four methods available for inequalities:

* Less than: `$people = ORM::for_table('person')->where_lt('age', 10)->find_many();`
* Greater than: `$people = ORM::for_table('person')->where_gt('age', 5)->find_many();`
* Less than or equal: `$people = ORM::for_table('person')->where_lte('age', 10)->find_many();`
* Greater than or equal: `$people = ORM::for_table('person')->where_gte('age', 5)->find_many();`

##### String comparision: `where_like` and `where_not_like` #####

To add a `WHERE ... LIKE` clause, use:

    $people = ORM::for_table('person')->where_like('name', '%fred%')->find_many();

Similarly, to add a `WHERE ... NOT LIKE` clause, use:

    $people = ORM::for_table('person')->where_not_like('name', '%bob%')->find_many();

##### Set membership: `where_in` and `where_not_in` #####

To add a `WHERE ... IN ()` or `WHERE ... NOT IN ()` clause, use the `where_in` and `where_not_in` methods respectively.

Both methods accept two arguments. The first is the column name to compare against. The second is an *array* of possible values.

    $people = ORM::for_table('person')->where_in('name', array('Fred', 'Joe', 'John'))->find_many();

##### Raw WHERE clauses #####

If you require a more complex query, you can use the `where_raw` method to specify the SQL fragment for the WHERE clause exactly. This method takes two arguments: the string to add to the query, and an array of parameters which will be bound to the string. The string should contain question marks to represent the values to be bound, and the parameter array should contain the values to be substituted into the string in the correct order.

This method may be used in a method chain alongside other `where_*` methods as well as methods such as `offset`, `limit` and `order_by_*`. The contents of the string you supply will be connected with preceding and following WHERE clauses with AND.

    $people = ORM::for_table('person')
                ->where('name', 'Fred')
                ->where_raw('(`age` = ? OR `age` = ?)', array(20, 25))
                ->order_by_asc('name')
                ->find_many();

    // Creates SQL:
    SELECT * FROM `person` WHERE `name` = "Fred" AND (`age` = 20 OR `age` = 25) ORDER BY `name` ASC;

Note that this method only supports "question mark placeholder" syntax, and NOT "named placeholder" syntax. This is because PDO does not allow queries that contain a mixture of placeholder types. Also, you should ensure that the number of question mark placeholders in the string exactly matches the number of elements in the array.

If you require yet more flexibility, you can manually specify the entire query. See *Raw queries* below.

##### Limits and offsets #####

*Note that these methods **do not** escape their query parameters and so these should **not** be passed directly from user input.*

The `limit` and `offset` methods map pretty closely to their SQL equivalents.

    $people = ORM::for_table('person')->where('gender', 'female')->limit(5)->offset(10)->find_many();

##### Ordering #####

*Note that this method **does not** escape its query parameter and so this should **not** be passed directly from user input.*

Two methods are provided to add `ORDER BY` clauses to your query. These are `order_by_desc` and `order_by_asc`, each of which takes a column name to sort by.

    $people = ORM::for_table('person')->order_by_asc('gender')->order_by_desc('name')->find_many();

#### Result columns ####

By default, all columns in the `SELECT` statement are returned from your query. That is, calling:

    $people = ORM::for_table('person')->find_many();

Will result in the query:

    SELECT * FROM `person`;

The `select` method gives you control over which columns are returned. Call `select` multiple times to specify columns to return.

    $people = ORM::for_table('person')->select('name')->select('age')->find_many();

Will result in the query:

    SELECT `name`, `age` FROM `person`;

Optionally, you may also supply a second argument to `select` to specify an alias for the column:

    $people = ORM::for_table('person')->select('name', 'person_name')->find_many();

Will result in the query:

    SELECT `name` AS `person_name` FROM `person`;

Column names passed to `select` are quoted automatically, even if they contain `table.column`-style identifiers:

    $people = ORM::for_table('person')->select('person.name', 'person_name')->find_many();

Will result in the query:

    SELECT `person`.`name` AS `person_name` FROM `person`;

If you wish to override this behaviour (for example, to supply a database expression) you should instead use the `select_expr` method. Again, this takes the alias as an optional second argument.

    // NOTE: For illustrative purposes only. To perform a count query, use the count() method.
    $people_count = ORM::for_table('person')->select('COUNT(*)', 'count')->find_many();

Will result in the query:

    SELECT COUNT(*) AS `count` FROM `person`;

#### Raw queries ####

If you need to perform more complex queries, you can completely specify the query to execute by using the `raw_query` method. This method takes a string and an array of parameters. The string should contain placeholders, either in question mark or named placeholder syntax, which will be used to bind the parameters to the query.

    $people = ORM::for_table('person')->raw_query('SELECT p.* FROM person p JOIN role r ON p.role_id = r.id WHERE r.name = :role', array('role' => 'janitor')->find_many();

The ORM class instance(s) returned will contain data for all the columns returned by the query. Note that you still must call `for_table` to bind the instances to a particular table, even though there is nothing to stop you from specifying a completely different table in the query. This is because if you wish to later called `save`, the ORM will need to know which table to update.

Note that using `raw_query` is advanced and possibly dangerous, and Idiorm does not make any attempt to protect you from making errors when using this method. If you find yourself calling `raw_query` often, you may have misunderstood the purpose of using an ORM, or your application may be too complex for Idiorm. Consider using a more full-featured database abstraction system.

### Getting data from objects ###

Once you've got a set of records (objects) back from a query, you can access properties on those objects (the values stored in the columns in its corresponding table) in two ways: by using the `get` method, or simply by accessing the property on the object directly:

    $person = ORM::for_table('person')->find_one(5);

    // The following two forms are equivalent
    $name = $person->get('name');
    $name = $person->name;

### Updating records ###

To update the database, change one or more of the properties of the object, then call the `save` method to commit the changes to the database. Again, you can change the values of the object's properties either by using the `set` method or by setting the value of the property directly:

    $person = ORM::for_table('person')->find_one(5);

    // The following two forms are equivalent
    $person->set('name', 'Bob Smith');
    $person->age = 20;

    // Syncronise the object with the database
    $person->save();

### Creating new records ###

To add a new record, you need to first create an "empty" object instance. You then set values on the object as normal, and save it.

    $person = ORM::for_table('person')->create();

    $person->name = 'Joe Bloggs';
    $person->age = 40;

    $person->save();

After the object has been saved, you can call its `id()` method to find the autogenerated primary key value that the database assigned to it.

### Deleting records ###

To delete an object from the database, simply call its `delete` method.

    $person = ORM::for_table('person')->find_one(5);
    $person->delete();

### Configuration ###

Other than setting the DSN string for the database connection (see above), the `configure` method can be used to set some other simple options on the ORM class. Modifying settings involves passing a key/value pair to the `configure` method, representing the setting you wish to modify and the value you wish to set it to.

    ORM::configure('setting_name', 'value_for_setting');

#### Database authentication details ####

Settings: `username` and `password`

Some database adapters (such as MySQL) require a username and password to be supplied separately to the DSN string. These settings allow you to provide these values. A typical MySQL connection setup might look like this:

    ORM::configure('mysql:host=localhost;dbname=my_database');
    ORM::configure('username', 'database_user');
    ORM::configure('password', 'top_secret');

#### PDO Driver Options ####

Setting: `driver_options`

Some database adapters require (or allow) an array of driver-specific configuration options. This setting allows you to pass these options through to the PDO constructor. For more information, see [the PDO documentation](http://www.php.net/manual/en/pdo.construct.php). For example, to force the MySQL driver to use UTF-8 for the connection:

    ORM::configure('driver_options', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));


#### PDO Error Mode ####

Setting: `error_mode`

This can be used to set the `PDO::ATTR_ERRMODE` setting on the database connection class used by Idiorm. It should be passed one of the class constants defined by PDO. For example:

    ORM::configure('error_mode', PDO::ERRMODE_WARNING);

The default setting is `PDO::ERRMODE_EXCEPTION`. For full details of the error modes available, see [the PDO documentation](http://uk2.php.net/manual/en/pdo.setattribute.php).

#### ID Column ####

By default, the ORM assumes that all your tables have a primary key column called `id`. There are two ways to override this: for all tables in the database, or on a per-table basis.

Setting: `id_column`

This setting is used to configure the name of the primary key column for all tables. If your ID column is called `primary_key`, use:

    ORM::configure('id_column', 'primary_key');

Setting: `id_column_overrides`

This setting is used to specify the primary key column name for each table separately. It takes an associative array mapping table names to column names. If, for example, your ID column names include the name of the table, you can use the following configuration:

    ORM::configure('id_column_overrides', array(
        'person' => 'person_id',
        'role' => 'role_id',
    ));

#### Query logging ####

Setting: `logging`

Idiorm can log all queries it executes. To enable query logging, set the `logging` option to `true` (it is `false` by default).

When query logging is enabled, you can use two static methods to access the log. `ORM::get_last_query()` returns the most recent query executed. `ORM::get_query_log()` returns an array of all queries executed.
