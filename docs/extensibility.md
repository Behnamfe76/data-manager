# Extending DataManager: Custom Importers, Exporters, and Event Hooks

## Custom Importers

To add a custom importer, implement the `ImporterInterface`:

```php
use DataManager\Contracts\ImporterInterface;

class MyCustomImporter implements ImporterInterface
{
    public function import($source): iterable
    {
        // Your import logic here
    }
}
```

Register your importer in your application or service provider as needed.

## Custom Exporters

To add a custom exporter, implement the `ExporterInterface`:

```php
use DataManager\Contracts\ExporterInterface;

class MyCustomExporter implements ExporterInterface
{
    public function export(iterable $data, $target): void
    {
        // Your export logic here
    }
}
```

Register your exporter in your application or service provider as needed.

## Using Event Hooks

You can listen for and dispatch events during the import/export lifecycle using the `EventDispatcher` utility:

```php
use DataManager\Utils\EventDispatcher;

// Listen for an event
EventDispatcher::listen('import.started', function ($source) {
    // Handle event
});

// Dispatch an event
EventDispatcher::dispatch('import.started', $source);
```

Define and use your own event names as needed for your workflow. 