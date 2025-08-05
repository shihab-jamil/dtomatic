
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

```bash
composer require sj/dtomatic
```

---

## 🔧 Configuration

To publish the configuration file:

```bash
php artisan vendor:publish --provider="Dtomatic\DtomaticServiceProvider" --tag=dtomatic-config
```

This will publish a config file at:

```
config/dtomatic.php
```

Example content:

```php
return [

    'strict_types' => true, //If enabled, Dtomatic will throw an exception when a type mismatch occurs.
    'date_format' => 'Y-m-d H:i:s', // The default date format used when converting DateTime objects to strings

    //You can register custom type converters here. When mapping a value,
    //Dtomatic will check if a converter exists for its type and use it.
    'custom_converters' => [   
        // Example:
        // 'string' => \App\Converters\JsonToArrayConverter::class,
    ],
];
```

---

## 🏷️ Available Attributes

Dtomatic supports the following PHP attributes to customize DTO mapping behavior:

### 1. `#[ArrayOf(Type::class)]`

- Use this attribute to specify the type of objects inside an array or collection property.
- Enables automatic mapping of nested collections of DTOs.

```php
    use ShihabJamil\Dtomatic\Attributes\ArrayOf;
    
    class UserDTO
    {
        #[ArrayOf(PostDTO::class)]
        public array $posts;
    }
```

### 2. `#[Ignore]`

- Marks a DTO property to be ignored during mapping.
- Useful for excluding properties you do not want to populate.

```php
    use ShihabJamil\Dtomatic\Attributes\Ignore;

    class PostDTO
    {
        #[Ignore]
        public string $internalNotes;
    }
```

### 3. `#[Converter(ConverterClass::class)]`

- Specifies a custom converter class for a particular property.
- The converter class must implement a `convert($value)` method returning the converted value.

```php
    use ShihabJamil\Dtomatic\Attributes\Converter;

    class PostDTO
    {
        #[Converter(MyCustomConverter::class)]
        public string $specialField;
    }
```

---

## 🚀 Basic Usage

### Example DTO class:

```php
namespace App\DTO;

class UserDTO
{
    public int $id;
    public string $name;
    public string $email;
}
```

### Example mapping in your controller or service:

```php
use App\Models\User;
use Dtomatic\Facades\ModelMapper;
use App\DTO\UserDTO;

$user = User::find(1);
$dto = ModelMapper::map($user, UserDTO::class);

return response()->json($dto);
```

---

## 📑 Nested Mapping Example

```php
class PostDTO {
    public int $id;
    public string $title;
    public UserDTO $author;
}

$post = Post::with('author')->find(1);
$dto = ModelMapper::map($post, PostDTO::class);
```

---

##  Nested Collections Mapping with #[ArrayOf] Attribute

```php
    <?php
    
    namespace App\Dto;
    
    use ShihabJamil\Dtomatic\Attributes\ArrayOf;
    
    class UserDTO
    {
        public int $id;
        public string $name;
    
        #[ArrayOf(PostDTO::class)]
        public array $posts;
    }
    
    class PostDTO
    {
        public int $id;
        public string $title;
    }
```
### Example usage:
```php
    $userModel = User::with('posts')->find(1);
    $userDto = ModelMapper::map($userModel, UserDTO::class);
    
    // $userDto->posts is now an array of PostDTO objects

```

---

## 📚 Collection Mapping

```php
$users = User::all();
$dtoList = ModelMapper::mapCollection($users, UserDTO::class);
```

---

## 🎛️ Property-Level Converter Example

```php
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

```php
use Dtomatic\Attributes\Converter;

class UserDTO
{
    public int $id;

    #[Converter(\App\Converters\UppercaseConverter::class)]
    public string $name;

    public string $email;
}
```

---

## 🌍 Global Converter Example

In `config/dtomatic.php`:

```php
'custom_converters' => [
    'string' => \App\Converters\JsonToArrayConverter::class,
]
```

Converter class:

```php
namespace App\Converters;

class JsonToArrayConverter
{
    public function convert($value): mixed
    {
        if (is_string($value) && $this->isJson($value)) {
            return json_decode($value, true);
        }
        return $value;
    }
    
    private function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
```

```php
    class UserPreferencesDTO
    {
        public array $preferences;
    }
```

```php
    $user = new \App\Models\User();
    $user->preferences = '{"theme":"dark","notifications":true}';
```

```php
    $dto = Dtomatic::map($user, UserPreferencesDTO::class);
    print_r($dto->preferences);
    // Output:
    // [
    //   "theme" => "dark",
    //   "notifications" => true
    // ]
```
---

## 🛑 Ignoring Properties

```php
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

```php
public function getFullNameAttribute()
{
    return "{$this->first_name} {$this->last_name}";
}
```

DTO:

```php
class UserDTO
{
    public string $full_name;
}
```

---

## 📖 Why Use Dtomatic over other solutions?

- Laravel Resources require manual array structures.
- Dtomatic uses native typed DTO classes with reflection and PHP Attributes.
- Supports nested mapping, collections, global/property converters, validation, and getter resolution.
- Clean, typed, scalable API layer.

---

## 📃 License

MIT © Shihab Jamil
