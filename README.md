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
    $instance->control(array('\QDateTime', 'getTimestamp'));
    $instance->candidate(array('\QDateTime', 'someNewAwesomeGetTimestamp'));

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
$lm->get('lang.string.name', array('replacement' => $someValue));

// ParallelCodePath controlled:
$result = ParallelCodePath::execute('experiment.name', function(ParallelCodePath $instance) use($someValue) {
    $instance->control(
        '_sp',
        array('somg language string')
    );
    $instance->candidate(
        array($pimple[LanguageManager::class], 'get'),
        array('replacement' => $someValue)
    );

    $instance->comparator(function($resultA, $resultB) {
        return $resultA === $resultB;
    });
});
```

Where replacing a method call on with a new method on an instance:
```php
$this->createdAt = new \QDateTime();

$result = ParallelCodePath::execute('experiment.name', function(ParallelCodePath $instance) use($object) {
    $instance->control(array($this->createdAt, 'getTimestamp'));
    $instance->candidate(array($this->createdAt, 'awesomeGetTimestamp'));

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
$test->candidate(array($instance, 'awesomeGetTimestamp'));

// optional comparator, uses phpunits compare library by default.
$test->comparator(function($resultA, $resultB) {
    return $resultA === $resultB;
});
$result = $a->run('test');
```

