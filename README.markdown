Idiorm
======

** Version 0.1 - Alpha **

A lightweight nearly-zero-configuration object-relational mapper and fluent query builder for PHP5.

Released under a [BSD license](http://en.wikipedia.org/wiki/BSD_licenses).

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

* Implement raw queries.
* Improve documentation.
* Proper testing.
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

Then, pass a *Data Source Name* connection string to the `configure` method of the ORM class. This is used by PDO to connect to your database. For more information, see the [PDO documentation](http://uk2.php.net/manual/en/pdo.construct.php).

    ORM::configure('sqlite:./example.db');

### Querying ###

Idiorm provides a [*fluent interface*](http://en.wikipedia.org/wiki/Fluent_interface) to enable simple queries to be built without writing a single character of SQL. If you've used [jQuery](http://jquery.com) at all, you'll be familiar with the concept of a fluent interface. It just means that you can *chain* method calls together, one after another. This can make your code more readable, as the method calls strung together in order can start to look a bit like a sentence.

All Idiorm queries start with a call to the `for_table` static method on the ORM class. This tells the ORM which table to use when making the query. Method calls which add parameters to the query are then strung together. Finally, the chain is finished by calling either `find_one()` or `find_many()`, which executes the query and returns the result.

Let's start with a simple example. Say we have a table called `person` which contains the columns `id` (the primary key of the record - Idiorm assumes the primary key column is called `id` but this is configurable, see below [TODO]), `name`, `age` and `gender`.

#### Single records ####

Any method chain that ends in `find_one()` will return either a *single* instance of the ORM class representing the database row you requested, or `false` if no matching record was found.

To find a single record where the `name` column has the value "Fred Bloggs":

    ORM::for_table('person')->where('name', 'Fred Bloggs')->find_one();

This roughly translates into the following SQL: `SELECT * FROM person WHERE name = "Fred Bloggs"`

To find a single record by ID, you can pass the ID directly to the `find_one` method:

    ORM::for_table('person')->find_one(5);

#### Multiple records ####

Any method chain that ends in `find_many()` will return an *array* of ORM class instances, one for each row matched by your query. If no rows were found, an empty array will be returned.

To find all records in the table:

    ORM::for_table('person')->find_many();

To find all records where the `gender` is `female`:

    ORM::for_table('person')->where('gender', 'female')->find_many();

#### WHERE clauses ####

The `where` method on the ORM class adds a single `WHERE` clause to your query. The method may be called (chained) multiple times to add more than one WHERE clause. All the WHERE clauses will be ANDed together when the query is run. Support for ORing WHERE clauses is not currently present; if a query requires an OR clause you should use the `where_raw` or `raw_select` methods (see below). [TODO]

By default, calling `where` with two parameters (the column name and the value) will combine them using an equals operator (`=`). For example, calling `where('name', 'Fred')` will result in the clause `WHERE name = "Fred"`. However, the `where` method takes an optional third parameter which specifies the type of operator to use. Constants for each operator are provided on the ORM class. Currently, the supported operators are: `ORM::EQUALS` and `ORM::LIKE`.

#### LIMIT and OFFSET ####

The `limit` and `offset` methods map pretty closely to their SQL equivalents.

    ORM::for_table('person')->where('gender', 'female')->limit(5)->offset(10)->find_many();

#### ORDER BY ####

Two methods are provided to add `ORDER BY` clauses to your query. These are `order_by_desc` and `order_by_asc`, each of which takes a column name to sort by.

    ORM::for_table('person')->order_by_asc('gender')->order_by_desc('name')->find_many();

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

