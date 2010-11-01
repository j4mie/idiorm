Paris
=====

A lightweight Active Record implementation for PHP5.

Built on top of [Idiorm](http://github.com/j4mie/idiorm/).

Tested on PHP 5.2.0+ - may work on earlier versions with PDO and the correct database drivers.

Released under a [BSD license](http://en.wikipedia.org/wiki/BSD_licenses).

Features
--------

* Extremely simple configuration.
* Exposes the full power of [Idiorm](http://github.com/j4mie/idiorm/)'s fluent query API.
* Supports associations.
* Simple mechanism to encapsulate common queries in filter methods.
* Built on top of [PDO](http://php.net/pdo).
* Uses [prepared statements](http://uk.php.net/manual/en/pdo.prepared-statements.php) throughout to protect against [SQL injection](http://en.wikipedia.org/wiki/SQL_injection) attacks.
* Database agnostic. Currently supports SQLite and MySQL. May support others, please give it a try!

Philosophy
----------

Paris is built with the same *less is more* philosophy as [Idiorm](http://github.com/j4mie/idiorm/).

Let's See Some Code
-------------------

### Setup ###

Paris requires [Idiorm](http://github.com/j4mie/idiorm/). Install Idiorm and Paris somewhere in your project directory, and `require` both.

    require_once 'your/path/to/idiorm.php';
    require_once 'your/path/to/paris.php';

Then, you need to tell Idiorm how to connect to your database. **For full details of how to do this, see [Idiorm's documentation](http://github.com/j4mie/idiorm/).**

Briefly, you need to pass a *Data Source Name* connection string to the `configure` method of the ORM class.

    ORM::configure('sqlite:./example.db');

You may also need to pass a username and password to your database driver, using the `username` and `password` configuration options. For example, if you are using MySQL:

    ORM::configure('mysql:host=localhost;dbname=my_database');
    ORM::configure('username', 'database_user');
    ORM::configure('password', 'top_secret');

### Model Classes ###

You should create a model class for each entity in your application. For example, if you are building an application that requires users, you should create a `User` class. Your model classes should extend the base `Model` class:

    class User extends Model {
    }

Paris takes care of creating instances of your model classes, and populating them with *data* from the database. You can then add *behaviour* to this class in the form of public methods which implement your application logic. This combination of data and behaviour is the essence of the [Active Record pattern](http://martinfowler.com/eaaCatalog/activeRecord.html).

### Database Tables ###

Your `User` class should have a corresponding `user` table in your database to store its data.

By default, Paris assumes your class names are in *CapWords* style, and your table names are in *lowercase_with_underscores* style. It will convert between the two automatically. For example, if your class is called `CarTyre`, Paris will look for a table named `car_tyre`.

To override this default behaviour, add a **public static** property to your class called `$_table`:

    class User extends Model {
        public static $_table = 'my_user_table';
    }

### ID Column ###

Paris requires that your database tables have a unique primary key column. By default, Paris will use a column called `id`. To override this default behaviour, add a **public static** property to your class called `$_id_column`:

    class User extends Model {
        public static $_id_column = 'my_id_column';
    }

**Note** - Paris has its *own* default ID column name mechanism, and does not respect column names specified in Idiorm's configuration.

### Querying ###

Querying allows you to select data from your database and populate instances of your model classes. Queries start with a call to a static *factory method* on the base `Model` class that takes a single argument: the name of the model class you wish to use for your query. This factory method is then used as the start of a *method chain* which gives you full access to [Idiorm](http://github.com/j4mie/idiorm/)'s fluent query API. **See Idiorm's documentation for details of this API.**

For example:

    $users = Model::factory('User')
        ->where('name', 'Fred')
        ->where_gte('age', 20)
        ->find_many();

You can also use the same shortcut provided by Idiorm when looking up a record by its primary key ID:

    $user = Model::factory('User')->find_one($id);

The only differences between using Idiorm and using Paris for querying are as follows:

1. You do not need to call the `for_table` method to specify the database table to use. Paris will supply this automatically based on the class name (or the `$_table` static property, if present).

2. The `find_one` and `find_many` methods will return instances of *your model subclass*, instead of the base `ORM` class. Like Idiorm, `find_one` will return a single instance or `false` if no rows matched your query, while `find_many` will return an array of instances, which may be empty if no rows matched.

3. Custom filtering, see next section.

You may also retrieve a count of the number of rows returned by your query. This method behaves exactly like Idiorm's `count` method:

    $count = Model::factory('User')->where_lt('age', 20)->count();

### Associations ###

Paris provides a simple API for one-to-one, one-to-many and many-to-many relationships (associations) between models. It takes a different approach to many other ORMs, which use associative arrays to add configuration metadata about relationships to model classes. These arrays can often be deeply nested and complex, and are therefore quite error-prone.

Instead, Paris treats the act of querying across a relationship as a *behaviour*, and supplies a family of helper methods to help generate such queries. These helper methods should be called from within *methods* on your model classes which are named to describe the relationship. These methods return ORM instances (rather than actual Model instances) and so, if necessary, the relationship query can be modified and added to before it is run.

#### Summary ####

The following list summarises the associations provided by Paris, and explains which helper method supports each type of association:

##### One-to-one #####

Use `has_one` in the base, and `belongs_to` in the associated model.

##### One-to-many #####

Use `has_many` in the base, and `belongs_to` in the associated model.

##### Many-to-many #####

Use `has_many_through` in both the base and associated models.

Below, each association helper method is discussed in detail.

#### Has-one ####

One-to-one relationships are implemented using the `has_one` method. For example, say we have a `User` model. Each user has a single `Profile`, and so the `user` table should be associated with the `profile` table. To be able to find the profile for a particular user, we should add a method called `profile` to the `User` class (note that the method name here is arbitrary, but should describe the relationship). This method calls the protected `has_one` method provided by Paris, passing in the class name of the related object. The `profile` method should return an ORM instance ready for (optional) further filtering.

    class Profile extends Model {
    }

    class User extends Model {
        public function profile() {
            return $this->has_one('Profile');
        }
    }

The API for this method works as follows:

    // Select a particular user from the database
    $user = Model::factory('User')->find_one($user_id);

    // Find the profile associated with the user
    $profile = $user->profile()->find_one();

By default, Paris assumes that the foreign key column on the related table has the same name as the current (base) table, with `_id` appended. In the example above, Paris will look for a foreign key column called `user_id` on the table used by the `Profile` class. To override this behaviour, add a second argument to your `has_one` call, passing the name of the column to use.

#### Has many ####

One-to-many relationships are implemented using the `has_many` method. For example, say we have a `User` model. Each user has several `Post` objects. The `user` table should be associated with the `post` table. To be able to find the posts for a particular user, we should add a method called `posts` to the `User` class (note that the method name here is arbitrary, but should describe the relationship). This method calls the protected `has_many` method provided by Paris, passing in the class name of the related objects. **Pass the model class name literally, not a pluralised version**. The `posts` method should return an ORM instance ready for (optional) further filtering.

    class Post extends Model {
    }

    class User extends Model {
        public function posts() {
            return $this->has_many('Post'); // Note we use the model name literally - not a pluralised version
        }
    }

The API for this method works as follows:

    // Select a particular user from the database
    $user = Model::factory('User')->find_one($user_id);

    // Find the posts associated with the user
    $posts = $user->posts()->find_many();

By default, Paris assumes that the foreign key column on the related table has the same name as the current (base) table, with `_id` appended. In the example above, Paris will look for a foreign key column called `user_id` on the table used by the `Post` class. To override this behaviour, add a second argument to your `has_many` call, passing the name of the column to use.

#### Belongs to ####

The 'other side' of `has_one` and `has_many` is `belongs_to`. This method call takes identical parameters as these methods, but assumes the foreign key is on the *current* (base) table, not the related table.

    class Profile extends Model {
        public function user() {
            return $this->belongs_to('User');
        }
    }

    class User extends Model {
    }

The API for this method works as follows:

    // Select a particular profile from the database
    $profile = Model::factory('Profile')->find_one($profile_id);

    // Find the user associated with the profile
    $user = $profile->user()->find_one();

Again, Paris makes an assumption that the foreign key on the current (base) table has the same name as the related table with `_id` appended. In the example above, Paris will look for a column named `user_id`. To override this behaviour, pass a second argument to the `belongs_to` method, specifying the name of the column on the current (base) table to use.

#### Has many through ####

Many-to-many relationships are implemented using the `has_many_through` method. This method has only one required argument: the name of the related model. Supplying further arguments allows us to override default behaviour of the method.

For example, say we have a `Book` model. Each `Book` may have several `Author` objects, and each `Author` may have written several `Books`. To be able to find the authors for a particular book, we should first create an intermediary model. The name for this model should be constructed by concatenating the names of the two related classes, in alphabetical order. In this case, our classes are called `Author` and `Book`, so the intermediate model should be called `AuthorBook`.

We should then add a method called `authors` to the `Book` class (note that the method name here is arbitrary, but should describe the relationship). This method calls the protected `has_many_through` method provided by Paris, passing in the class name of the related objects. **Pass the model class name literally, not a pluralised version**. The `authors` method should return an ORM instance ready for (optional) further filtering.

    class Author extends Model {
        public function books() {
            return $this->has_many_through('Book');
        }
    }

    class Book extends Model {
        public function authors() {
            return $this->has_many_through('Author');
        }
    }

    class AuthorBook extends Model {
    }

The API for this method works as follows:

    // Select a particular book from the database
    $book = Model::factory('Book')->find_one($book_id);

    // Find the authors associated with the book
    $authors = $book->authors()->find_many();

    // Get the first author
    $first_author = $authors[0];

    // Find all the books written by this author
    $first_author_books = $first_author->books()->find_many();

##### Overriding defaults #####

The `has_many_through` method takes up to four arguments, which allow us to progressively override default assumptions made by the method.

**First argument: associated model name** - this is mandatory and should be the name of the model we wish to select across the association.

**Second argument: intermediate model name** - this is optional and defaults to the names of the two associated models, sorted alphabetically and concatenated.

**Third argument: custom key to base table on intermediate table** - this is optional, and defaults to the name of the base table with `_id` appended.

**Fourth argument: custom key to associated table on intermediate table** - this is optional, and defaults to the name of the associated table with `_id` appended.

### Filters ###

It is often desirable to create reusable queries that can be used to extract particular subsets of data without repeating large sections of code. Paris allows this by providing a method called `filter` which can be chained in queries alongside the existing Idiorm query API. The filter method takes the name of a **public static** method on the current Model subclass as an argument. The supplied method will be called at the point in the chain where `filter` is called, and will be passed the `ORM` object as the first parameter. It should return the ORM object after calling one or more query methods on it. The method chain can then be continued if necessary.

It is easiest to illustrate this with an example. Imagine an application in which users can be assigned a role, which controls their access to certain pieces of functionality. In this situation, you may often wish to retrieve a list of users with the role 'admin'. To do this, add a static method called (for example) `admins` to your Model class:

    class User extends Model {
        public static function admins($orm) {
            return $orm->where('role', 'admin');
        }
    }

You can then use this filter in your queries:

    $admin_users = Model::factory('User')->filter('admins')->find_many();

You can also chain it with other methods as normal:

    $young_admins = Model::factory('User')
                        ->filter('admins')
                        ->where_lt('age', 18)
                        ->find_many();

#### Filters with arguments ####

You can also pass arguments to custom filters. Any additional arguments passed to the `filter` method (after the name of the filter to apply) will be passed through to your custom filter as additional arguments (after the ORM instance).

For example, let's say you wish to generalise your role filter (see above) to allow you to retrieve users with any role. You can pass the role name to the filter as an argument:

    class User extends Model {
        public static function has_role($orm, $role) {
            return $orm->where('role', $role);
        }
    }

    $admin_users = Model::factory('User')->filter('has_role', 'admin')->find_many();
    $guest_users = Model::factory('User')->filter('has_role', 'guest')->find_many();

These examples may seem simple (`filter('has_role', 'admin')` could just as easily be achieved using `where('role', 'admin')`), but remember that filters can contain arbitrarily complex code - adding `raw_where` clauses or even complete `raw_query` calls to perform joins, etc. Filters provide a powerful mechanism to hide complexity in your model's query API.

### Getting data from objects, updating and inserting data ###

The model instances returned by your queries now behave exactly as if they were instances of Idiorm's raw `ORM` class.

You can access data:

    $user = Model::factory('User')->find_one($id);
    echo $user->name;

Update data and save the instance:

    $user = Model::factory('User')->find_one($id);
    $user->name = 'Paris';
    $user->save();

Of course, because these objects are instances of your base model classes, you can also call methods that you have defined on them:

    class User extends Model {
        public function full_name() {
            return $this->first_name . ' ' . $this->last_name;
        }
    }

    $user = Model::factory('User')->find_one($id);
    echo $user->full_name();

To delete the database row associated with an instance of your model, call its `delete` method:

    $user = Model::factory('User')->find_one($id);
    $user->delete();

You can also get the all the data wrapped by a model subclass instance using the `as_array` method. This will return an associative array mapping column names (keys) to their values.

The `as_array` method takes column names as optional arguments. If one or more of these arguments is supplied, only matching column names will be returned.

    class Person extends Model {
    }

    $person = Model::factory('Person')->create();

    $person->first_name = 'Fred';
    $person->surname = 'Bloggs';
    $person->age = 50;

    // Returns array('first_name' => 'Fred', 'surname' => 'Bloggs', 'age' => 50)
    $data = $person->as_array();

    // Returns array('first_name' => 'Fred', 'age' => 50)
    $data = $person->as_array('first_name', 'age');

### A word on validation ###

It's generally considered a good idea to centralise your data validation in a single place, and a good place to do this is inside your model classes. This is preferable to handling validation alongside form handling code, for example. Placing validation code inside models means that if you extend your application in the future to update your model via an alternative route (say a REST API rather than a form) you can re-use the same validation code.

Despite this, Paris doesn't provide any built-in support for validation. This is because validation is potentially quite complex, and often very application-specific. Paris is deliberately quite ignorant about your actual data - it simply executes queries, and gives you the responsibility of making sure the data inside your models is valid and correct. Adding a full validation framework to Paris would probably require more code than Paris itself!

However, there are several simple ways that you could add validation to your models without any help from Paris. You could override the `save()` method, check the data is valid, and return `false` on failure, or call `parent::save()` on success. You could create your own subclass of the `Model` base class and add your own generic validation methods. Or you could write your own external validation framework which you pass model instances to for checking. Choose whichever approach is most suitable for your own requirements.

### Configuration ###

The only configuration options provided by Paris itself are the `$_table` and `$_id_column` static properties on model classes. To configure the database connection, you should use Idiorm's configuration system via the `ORM::configure` method. **See [Idiorm's documentation](http://github.com/j4mie/idiorm/) for full details.**

### Query logging ###

Idiorm can log all queries it executes. To enable query logging, set the `logging` option to `true` (it is `false` by default).

    ORM::configure('logging', true);

When query logging is enabled, you can use two static methods to access the log. `ORM::get_last_query()` returns the most recent query executed. `ORM::get_query_log()` returns an array of all queries executed.
