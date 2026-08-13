# Laravel JsonConfig

A Laravel trait that manages model attributes stored as JSON in a single database column while exposing them as first-class properties.

## How It Works

Define which attributes should be stored in a JSON column on your model. The trait automatically:

- **Expands** the JSON column into individual model attributes when a model is retrieved from the database
- **Compacts** those attributes back into the JSON column when the model is saved
- **Intercepts** `getAttribute`/`setAttribute` so JSON-stored attributes behave like regular columns
- **Includes** them in `toArray()` so serialization works seamlessly

## Installation

```
composer require russellramey/laravel-jsonconfig
```

The service provider is auto-discovered (Laravel 9+).

## Usage

### 1. Add a JSON column to your table

Create a migration with a `json` (or `text`) column:

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique();
    $table->json('config')->nullable();
    $table->timestamps();
});
```

### 2. Use the trait on your model

```php
use Illuminate\Database\Eloquent\Model;
use RussellRamey\JsonConfig\HasJsonConfig;

class User extends Model
{
    use HasJsonConfig;

    protected $fillable = [
        'email',
        ...
    ];

    protected string $jsonConfigColumn = 'config';

    protected array $jsonConfigAttributes = [
        'theme',
        'locale',
        'timezone',
        'notifications_enabled',
    ];
}
```

| Property | Purpose |
|---|---|
| `$jsonConfigColumn` | The database column name (default: `'config'`) |
| `$jsonConfigAttributes` | List of attribute names to store in that JSON column |

> The JSON config attributes **must** be included in your model's `$fillable` array when using mass assignment (`::create()`, `::update()`, `fill()`).

### 3. Use the attributes naturally

```php
$user = User::create([
    'email' => 'john@example.com',
    'theme' => 'dark',
    'locale' => 'en-US',
]);

echo $user->theme;  // 'dark'

$user->locale = 'fr-FR';
$user->save();

echo $user->locale;  // 'fr-FR'

// Included in serialization
return response()->json($user);
// { "email": "john@example.com", "theme": "dark", "locale": "fr-FR", ... }
```

## API

### `getConfigAttributes(): array`

Returns all attributes stored in the JSON column.

```php
$user->getConfigAttributes();
// ['theme' => 'dark', 'locale' => 'en-US']
```

### `hasJsonSchemaAttribute(string $key): bool`

Check if a key exists in the JSON column.

```php
$user->hasJsonSchemaAttribute('theme'); // true
```

### `setJsonConfig(string $key, mixed $value): self`

Set a single JSON config attribute. Throws `InvalidArgumentException` if the key is not in `$jsonConfigAttributes`.

```php
$user->setJsonConfig('theme', 'light');
```

### `removeJsonConfig(string $key): self`

Remove a single JSON config attribute.

```php
$user->removeJsonConfig('timezone');
```

## How It Works Internally

1. **`retrieved` event** — `expandJsonSchemaAttributes()` reads the JSON column and copies each defined attribute into `$this->attributes`
2. **`saving` event** — `compactJsonSchemaAttributes()` moves those attributes back into the JSON column and removes them from the top-level attributes
3. **`saved` event** — expands again so the in-memory model still has the attributes
4. **`getAttribute()` / `setAttribute()`** — fallback access through the JSON data when the attribute isn't in `$this->attributes`
5. **`toArray()`** — merges JSON config attributes into the output
