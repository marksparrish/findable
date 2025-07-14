# Findable

**Findable** is a Laravel package that provides a fluent, Eloquent-like interface to search and aggregate data in Elasticsearch.

Built for performance, readability, and Livewire integration — with support for nested filters, reusable aggregations, and friendly pagination output.

---

## 🚀 Installation

```bash
composer require pipcommunications/findable
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag=config --provider="Findable\FindableServiceProvider"
```

---

### 📄 `.env` Variables

Add the following to your Laravel `.env` file to control Findable behavior:

```
ELASTIC_SCHEME=https
ELASTIC_HOST=es.myapp.com
ELASTIC_PORT=443
ELASTIC_USER=elastic
ELASTIC_PASSWORD=yourpassword
ELASTIC_CA=/path/to/http_ca.crt

FINDABLE_DEFAULT_SIZE=10
FINDABLE_DEFAULT_TRACK_TOTAL_HITS=true
```

## ⚙ Configuration (`config/findable.php`)

```php
return [
    'scheme' => env('ELASTIC_SCHEME', 'http'),
    'host' => env('ELASTIC_HOST', 'localhost'),
    'port' => env('ELASTIC_PORT', 9200),
    'user' => env('ELASTIC_USER', ''),
    'password' => env('ELASTIC_PASSWORD', ''),
    'ca' => env('ELASTIC_CA', null),
];
```

- Supports both **HTTP (dev)** and **HTTPS with CA certs (prod)**
- Throws a clear error if `ca` is invalid when using `https`

---

## 🧬 Usage

### ✅ Using the `FindableTrait` in a model

```php
use Findable\Traits\FindableTrait;

class YourModel extends Model
{
    use FindableTrait;
}
```

### 🔎 Example query with filters and aggregations

```php
$results = YourModel::finder()
    ->setSize(0)
    ->setFilter([
        ['terms' => ['filter_on' => ['001-A', '002-B']]],
    ])
    ->setAggs([
        'color_counts' => ['terms' => ['field' => 'color.keyword']],
    ])
    ->paginate();
```

---

## 🧾 Accessing the Response

```php
$results->items();            // Paginated hits (from ES)
$results->aggregations;       // Aggregation results
$results->raw;                // Full ES response
$results->params;             // Query body + index
```

---

## 🧭 Using the `Findable` Facade

You can also use the `Findable` facade for more dynamic or service-based calls:

```php
use Findable\Facades\Findable;

$results = Findable::for(\App\Models\YourModel::class)
    ->setSize(0)
    ->setAggs([
        'gender_counts' => ['terms' => ['field' => 'gender.keyword']],
    ])
    ->paginate();
```

---

## 📦 Using `FindableEngine` Manually (Ad Hoc Queries)

You can resolve `FindableEngine` directly from the container and run queries against any index — no model required.

```php
use Findable\FindableEngine;

$engine = app(FindableEngine::class)
    ->setIndex('custom_index') # <== This is requeried for Ad Hoc Queries
    ->setSize(5)
    ->setFilter([
        ['term' => ['status' => 'active']],
    ])
    ->paginate();

foreach ($engine->items() as $hit) {
    echo $hit['_source']['name'];
}
```

✅ This is especially useful for non-Eloquent data, runtime index names, or admin tools.

> ❗ You must call `setIndex()` if no model is used, or the engine will throw an exception.

### 🧪 Example: Run a raw aggregation

```php
$stats = app(FindableEngine::class)
    ->setIndex('transactions')
    ->setSize(0)
    ->setAggs([
        'avg_amount' => ['avg' => ['field' => 'amount']],
    ])
    ->search();

echo $stats->aggregations['avg_amount']['value'];
```

---

## 🧱 Aggregation Helpers

Your models can define reusable aggregation and filter formatters:

```php
public static function aggGenderCounts(): array
{
    return (new static)->formatTermsAggregation('gender.keyword');
}

public static function defaultFilter(array $ids): array
{
    return [
        ['terms' => ['part_numbers' => $ids]]
    ];
}
```

Use them like:

```php
$results = YourModel::finder()
    ->setSize(0)
    ->setFilter(YourModel::defaultFilter([...]))
    ->setAggs([
        'genders' => YourModel::aggGenderCounts()
    ])
    ->paginate();
```

---

## 🎨 Livewire View Example
For a Livewire-ready rendering example, see docs/livewire-example.blade.php
```blade
{{-- resources/views/livewire/items-summary.blade.php --}}

<div>
    @foreach ($overview->hits() as $results)
        <div>{{ $results['_source']['name'] }}</div>
    @endforeach

    @foreach ($overview->aggregations['color_counts']['buckets'] ?? [] as $bucket)
        <span>{{ $bucket['key'] }}: {{ $bucket['doc_count'] }}</span>
    @endforeach
</div>
```

---

## 🧪 Testing (Coming Soon)

Findable supports `orchestra/testbench` for unit and integration testing:

- Test search response structure
- Validate ES body formatting
- Test Livewire-friendly pagination output

---

## 📄 License

MIT © [Mark Parrish](mailto:mark@pipcommunications.com), PIP Communications
