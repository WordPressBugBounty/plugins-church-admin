# iTunes Categories

This simple PHP package simply defines all of the valid categories for iTunes podcasts.

## Installation

```bash
composer require lukaswhite/itunes-categories
```

## Usage

```php
use Lukaswhite\ItunesCategories\Categories;

$categories = new Categories();

$categories->all();
```

This simply returns a nested associative array of all the categories as objects.

You can get a category directly:

```php
$categories->get('Sports');
$categories->get('Sports', 'Football');
$categories->get('Kids &amp; Family'); // NOTE we're using the key
```

You can check whether categories exist:

```php
$categories->has('Sports') // true
$categories->has('Sports', 'Football'); // true
```

