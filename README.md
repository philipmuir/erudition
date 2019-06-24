# Erudition

Some short sentence describing what this esoterically named lib does.

## Goals

- Wrap two read paths and measure corectness and execution time.
- Integrated feature flag to always use control (existing code).
- Automatic counts and timings collection.
	- `erudition.experiment.<name>`


## Usages

Where using a new static method on same class:
```php
$controlResult = ParallelCodePath::execute('experiment.name', function(ParallelCodePath $instance) {
    $instance->control(['\QDateTime', 'getTimestamp']);
    $instance->candidate(['\QDateTime', 'someNewAwesomeGetTimestamp']);

    $instance->comparator(function($resultA, $resultB) {
        return $resultA === $resultB;
    });
});
```

Where replacing a function call with a new method call, or similar:
```php
// old
_sp('somg language string');

// new
$someValue = 'some value';
$lm = $pimple[LanguageManager::class];
$lm->get('lang.string.name', ['replacement' => $someValue]);

// ParallelCodePath controlled:
$result = ParallelCodePath::execute('experiment.name', function(ParallelCodePath $instance) use($someValue) {
    $instance->control(
        '_sp',
        ['somg language string']
    );
    $instance->candidate(
        [$pimple[LanguageManager::class], 'get'],
        ['replacement' => $someValue]
    );

    $instance->comparator(function($resultA, $resultB) {
        return $resultA === $resultB;
    });
});
```

Where replacing a method call on with a new method on an instance:
```php
$this->createdAt = new \QDateTime();

$object = $this->createdAt;
$result = ParallelCodePath::execute('experiment.name', function(ParallelCodePath $instance) use($object) {
    $instance->control([$this->createdAt, 'getTimestamp']);
    $instance->candidate([$this->createdAt, 'awesomeGetTimestamp']);

    $instance->comparator(function($resultA, $resultB) {
        return $resultA == $resultB;
    });
});
```

Instantiating an instance of `ParallelCodePath`:
```php
$instance = new \QDateTime();

$test = new ParallelCodePath('test', null);
$test->control([$instance, 'getTimestamp']);
$test->candidate([$instance, 'awesomeGetTimestamp']);

// optional comparator, uses phpunits compare library by default.
$test->comparator(function($resultA, $resultB) {
    return $resultA === $resultB;
});
$result = $test->run('test');
```

