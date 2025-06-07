
# Laravel Stored Procedure

[![Latest Version on Packagist](https://img.shields.io/packagist/v/zapo/laravel-stored-procedure.svg?style=flat-square)](https://packagist.org/packages/zapo/laravel-stored-procedure) [![Total Downloads](https://img.shields.io/packagist/dt/zapo/laravel-stored-procedure.svg?style=flat-square)](https://packagist.org/packages/zapo/laravel-stored-procedure)

A Laravel package for calling **MySQL/MariaDB stored procedures** with optional **pagination** and **model hydration**.

<br>

#### ✨ Features
- Call **single** or **multi-result set** stored procedures
- **Pagination** using Laravel’s paginator for the first result set
- Optionally **hydrate models** on multiple result sets

<br>

### 🤔 Why did i make this?

This all started when i was working on a project that required a complicated query that was too complex to be done in a single query. I first tried writing the query with CTE's but they ended up being to slow. I began to wonder if a stored procedure would be a better solution. This procedure ended up having multiple temporary tables and multiple result sets. Once i had the stored procedure done it was returning the results sets in nano seconds where as the normal query with a CTE's was taking 5 seconds or more.  

So the only issue now was how to call the stored procedure and get the results back into my application. I ended up writing a package that would allow me to call stored procedures with laravel pagination and model hydration for the results sets.

<br>

#### Basic calls

```php
/* Simple call to a procedure that returns a single result set: */
$result = TestModel::CallStoredProcedure('your_procedure_name', [$param_1])
    ->get();

/* Simple call to a procedure that returns a single result set,
   paginated with the default per-page as 15: */
$result_paginated = User::CallStoredProcedure('your_procedure_name', [$param_1, $param_2, $param_3])
    ->paginate();

/* If you want to set a custom per-page, you can do so like this: */
$result_paginated = User::CallStoredProcedure('your_procedure_name')
    ->paginate(100);

/* If your procedure returns multiple result sets 
   you can set the total_result_sets parameter: */
$result_sets = User::CallStoredProcedure('your_procedure_name', [$param_1, $param_2], 3)
    ->get();
```
<br>

#### Hydrating multiple result sets
This example shows how to hydrate multiple result sets. Simply pass the number of result sets you expect to the `CallStoredProcedure` method then pass the model classes you want to hydrate to the `setHydrateModels` method. The first results set is excluded from the hydrate models method as it will be hydrated by the model that called the stored procedure.

```php
$result_sets = User::CallStoredProcedure('your_procedure_name', [$param_1, $param_2], 5)
    ->setHydrateModels([Token::class, Subscription::class, null, Plan::class])
    ->paginate();
```

The first result set is hydrated by the `User` model, the second result set is hydrated by the `Token` model, the third result set is hydrated by the `Subscription` model, the fourth result set is not hydrated and will return an array of objects, and the fifth result set is hydrated by the `Plan` model.

<br>

### So you probably want to know how this works under the hood?

To get this to work you will need to select your results sets into temporary tables. The temporary table name must be equal to the value of the `temporary_pagination_table_prefix` in the configuration file plus the result set number.

```sql
CREATE TEMPORARY TABLE sp_temp_results_1 AS (
    SELECT * FROM USERS
);
```

This is because the inputted stored procedure will run first and populate the temporary tables then the `sp_results` stored procedure will run and select from the temporary tables. This only works because i have used `DB::connection()->getPdo()` and kept the connection open. If this is not done the connection will be closed and the temporary tables will not be available to the `sp_results` stored procedure.

<br>

## 🚀 Installation

First add the package to your project.

```bash
composer require zapo/laravel-stored-procedure
```

Next publish the configuration file.

```bash
php artisan vendor:publish --tag=stored-procedure-config
```

This will add a `config/stored-procedure.php` file to your project.

```php
return [
    'results_procedure_name' => 'sp_results',
    'temporary_pagination_table_prefix' => 'sp_temp_results_',
];
```

Next run the install command to create the results stored procedure.

```bash
php artisan stored-procedure:install
```

If you change the `temporary_pagination_table_prefix` or `results_procedure_name` in the configuration file you will need to run the install command again to recreate the stored procedure.

<br>

## 🛠 Testing

```bash
composer test
```

Or:

```bash
./vendor/bin/pest
```

<br>

## 📄 License

MIT © [James Waring](https://github.com/jameswaring)
