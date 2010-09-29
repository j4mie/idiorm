Paris
=====

A lightweight Active Record implementation for PHP5.

Built on top of [Idiorm](http://github.com/j4mie/idiorm/).

Released under a [BSD license](http://en.wikipedia.org/wiki/BSD_licenses).

Features
--------

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

You may also need to pass a username and password to your database driver, using the `username` and `password` configuration options.

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

#### Custom filters ####

It is often desirable to create reusable filters that can be used as part of queries. Paris allows this by providing a method called `filter` which can be chained in queries alongside the existing Idiorm query API. The filter method takes the name of a **public static** method on the current Model subclass as an argument. The supplied method which will be called at the point in the chain where `filter` is called, and will be passed the `ORM` object as the first parameter. It should return the ORM object after calling one or more query methods on it. The method chain can then be continued if necessary.

It is easiest to illustrate this with an example. It may be desirable for users in your application to have a role, which controls their access to certain parts of the application. In this situation, you may often wish to retrieve a list of users with the role 'admin'. To do this, add a static method called 'admins' to your Model class:

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

### Configuration ###

The only configuration provided by Paris itself is the `$_table` static property on model classes. To configure the database connection, you should use Idiorm's configuration system via the `ORM::configure` method. **See [Idiorm's documentation](http://github.com/j4mie/idiorm/) for full details.**
