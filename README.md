# Airtable PHP

## Use with Laravel

### Setup `.env`
```python
AIRTABLE_KEY=key...
AIRTABLE_ID=app...
```

### Setup `config/service.php`
```php
'airtable' => [
    'key' => env('AIRTABLE_KEY'),
    'id' => env('AIRTABLE_ID'),
],
```

### Setup `App\Providers\AppServiceProvider`
```php
public function register()
{
    app()->bind('airtable', function() {
        $httpClient = new Http();
        return new Airtable(
            $httpClient,
            config('services.airtable.key'),
            config('services.airtable.id'),
        );
    });
}
```

### (optional) Setup `App\Facades\AirtableFacades`
```php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class AirtableFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'airtable';
    }
}
```

## Examples

### Get a specific record
```php
Airtable::table('Users')->grab('record-id-here');
```
