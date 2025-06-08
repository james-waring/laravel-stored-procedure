[![Latest Version on Packagist](https://img.shields.io/packagist/v/jlw/laravel-stored-procedure.svg?style=flat-square)](https://packagist.org/packages/jlw/laravel-stored-procedure) [![Total Downloads](https://img.shields.io/packagist/dt/jlw/laravel-stored-procedure.svg?style=flat-square)](https://packagist.org/packages/jlw/laravel-stored-procedure)


## Laravel Stored Procedure
A Laravel package for calling **MySQL/MariaDB stored procedures** with optional **pagination** and **model hydration**.

- Call **single** or **multi-result set** stored procedures
- **Pagination** using Laravel’s paginator for the first result set
- Optionally **hydrate models** on multiple result sets


### Basic calls


```php
// Simple call to a procedure that returns a single result set:
$result = TestModel::CallStoredProcedure('your_procedure_name', [$param_1])
    ->get();


// Simple call to a procedure that returns a single result set,
// paginated with the default per-page as 15:
$result_paginated = User::CallStoredProcedure(
    'your_procedure_name', 
    [$param_1, $param_2, $param_3]
)
->paginate();


// If you want to set a custom per-page, you can do so like this:
$result_paginated = User::CallStoredProcedure('your_procedure_name')
    ->paginate(100);


// If your procedure returns multiple result sets 
// you can set the total_result_sets parameter:
$result_sets = User::CallStoredProcedure(
    procedure_name: 'your_procedure_name', 
    parameters: [$param_1, $param_2], 
    total_result_sets: 3
)
->get();
```


## Hydrating multiple result sets
This example shows how to hydrate multiple result sets. Simply pass the number of result sets you expect to the `CallStoredProcedure` method then pass the model classes you want to hydrate to the `setHydrateModels` method. The first results set is excluded from the hydrate models method as it will be hydrated by the model that called the stored procedure.

```php
$result_sets = User::CallStoredProcedure('your_procedure_name', [$param_1, $param_2], 5)
    ->setHydrateModels([Token::class, Subscription::class, null, Plan::class])
    ->paginate();
```

The first result set is hydrated by the `User` model, the second result set is hydrated by the `Token` model, the third result set is hydrated by the `Subscription` model, the fourth result set is not hydrated and will return an array of objects, and the fifth result set is hydrated by the `Plan` model.


## Why did i make this?

This all started when i was working on a project that required a complicated query that was too complex to be done in a single query. I first tried writing the query with CTE's but they ended up being to slow. I began to wonder if a stored procedure would be a better solution. This procedure ended up having multiple temporary tables and multiple result sets. Once i had the stored procedure done it was returning the results sets in nano seconds where as the normal query with a CTE's was taking 5 seconds or more.  

So the only issue now was how to call the stored procedure and get the results back into my application. I ended up writing a package that would allow me to call stored procedures with laravel pagination and model hydration for the results sets.


### So you probably want to know how this works under the hood?

If you are just doing a `get()` then there is nothing more you need to do in your stored procedure. 

If you are doing a `paginate()` you will need to do a few things first. The first thing you will need to do is add two `IN` parameters to your stored procedure, one for the per page and one for the page number. Then you will need to select the total number of rows as the first result set. After that you can add the limit and offset to your first result set query.

```sql
CREATE PROCEDURE get_users_paginate(IN _email VARCHAR(255), IN _per_page INT, IN _page INT)
BEGIN
    SELECT COUNT(*) as total_rows FROM users WHERE email like _email;

    SELECT * FROM users WHERE email like _email LIMIT _per_page OFFSET _page;
END;
```

### When to use this?

If your just doing simple queries you probably wont see any speed increase but if you are doing complex queries with multiple joins and subqueries then you will probably see a significant speed increase.

## Installation
Simply just add the package to your project.

```bash
composer require zapo/laravel-stored-procedure
```

Then add trait to your model.

```php
use JLW\LaravelStoredProcedure\StoredProcedureTrait;

class User extends Model
{
    use StoredProcedureTrait;
}
```

## Testing

```bash
./vendor/bin/pest
```

## License 

MIT © [James Waring](https://github.com/jameswaring)
