# DataManager

A PHP package for importing and exporting data in various formats (CSV, JSON, XML, SQL, Excel, database models, Spatie Data objects).

## Installation

Install via Composer:

```bash
composer require fereydooni/data-manager
```

## Configuration

Publish the config file (if using Laravel):

```bash
php artisan vendor:publish --provider="DataManager\Providers\DataManagerServiceProvider" --tag=config
```

## Usage

### Importing Data

```php
use DataManager\Imports\CsvImporter;

$importer = new CsvImporter();
foreach ($importer->import('path/to/file.csv') as $row) {
    // Process $row
}
```

### Exporting Data

```php
use DataManager\Exports\CsvExporter;

$data = [
    ['name' => 'Alice', 'email' => 'alice@example.com'],
    ['name' => 'Bob', 'email' => 'bob@example.com'],
];
$exporter = new CsvExporter();
$exporter->export($data, 'path/to/file.csv');
```

### Supported Formats
- CSV
- JSON
- XML
- SQL
- Excel (using maatwebsite/excel)
- Database models
- Spatie Data objects

### Extending
See [docs/extensibility.md](docs/extensibility.md) for adding custom importers/exporters and using event hooks.

## License
MIT 