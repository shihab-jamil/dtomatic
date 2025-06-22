
# 📦 Dtomatic

**Dtomatic** is a flexible, reflection-based object-to-DTO mapper for Laravel applications. It automatically maps data from source models or plain objects to strongly typed DTO classes — including support for:

- Nested DTO mapping
- Collections
- Custom value converters
- Ignoring specific properties
- Strict type validation
- Mapping values via custom model getters
- Clean integration with Laravel services

---

## 📥 Installation

Install the package via Composer:

```
bash
composer require sj/dtomatic
```

---

## 🔧 Configuration

To publish the configuration file:

```
bash
php artisan vendor:publish --provider="Dtomatic\DtomaticServiceProvider" --tag=config
```

This will publish a config file at:

```
config/dtomatic.php
```

Example content:

```
php
return [

    'strict_types' => true,
    'date_format' => 'Y-m-d H:i:s',
    'global_ignored_properties' => [],
    'custom_converters' => [
        // Example:
        // \Carbon\Carbon::class => \App\Converters\CarbonDateConverter::class
    ],
];
```

---

## 🚀 Basic Usage

### Example DTO class:

```
php
namespace App\DTO;

class UserDTO
{
    public int $id;
    public string $name;
    public string $email;
}
```

### Example mapping in your controller or service:

```
php
use App\Models\User;
use Dtomatic\Facades\ModelMapper;
use App\DTO\UserDTO;

$user = User::find(1);
$dto = ModelMapper::map($user, UserDTO::class);

return response()->json($dto);
```

---

## 📑 Nested Mapping Example

```
php
class PostDTO {
    public int $id;
    public string $title;
    public UserDTO $author;
}

$post = Post::with('author')->find(1);
$dto = ModelMapper::map($post, PostDTO::class);
```

---

## 📚 Collection Mapping

```
php
$users = User::all();
$dtoList = ModelMapper::mapCollection($users, UserDTO::class);
```

---

## 🎛️ Property-Level Converter Example

```
php
namespace App\Converters;

class UppercaseConverter
{
    public function convert(string $value): string
    {
        return strtoupper($value);
    }
}
```

DTO usage:

```
php
use Dtomatic\Attributes\Converter;

class UserDTO
{
    public int $id;

    #[Converter(converterClass: \App\Converters\UppercaseConverter::class)]
    public string $name;

    public string $email;
}
```

---

## 🌍 Global Converter Example

In `config/dtomatic.php`:

```
php
'custom_converters' => [
    \Carbon\Carbon::class => \App\Converters\CarbonDateConverter::class
]
```

Converter class:

```
php
namespace App\Converters;

class CarbonDateConverter
{
    public function convert(\Carbon\Carbon $date): string
    {
        return $date->format('d-m-Y');
    }
}
```

---

## 🛑 Ignoring Properties

```
php
use Dtomatic\Attributes\Ignore;

class UserDTO
{
    public int $id;

    #[Ignore]
    public string $password;
}
```

---

## ✅ Strict Type Validation

When `strict_types` is enabled in config, Dtomatic throws `InvalidArgumentException` on type mismatches.

---

## 🎣 Mapping From Getter Methods

Model:

```
php
public function getFullNameAttribute()
{
    return "{$this->first_name} {$this->last_name}";
}
```

DTO:

```
php
class UserDTO
{
    public string $full_name;
}
```

---

## 📖 Why Use Dtomatic over Laravel Resources?

- Laravel Resources require manual array structures.
- Dtomatic uses native typed DTO classes with reflection and PHP Attributes.
- Supports nested mapping, collections, global/property converters, validation, and getter resolution.
- Clean, typed, scalable API layer.

---

## 📃 License

MIT © Shihab Jamil
