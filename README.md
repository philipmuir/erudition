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
$controlResult = Experiment::execute('experiment.name', function(Experiment $exp) {
    $exp->control(array('\QDateTime', 'getTimestamp'));
    $exp->candidate(array('\QDateTime', 'someNewAwesomeGetTimestamp'));

    $exp->comparator(function($resultA, $resultB) {
        return $resultA === $resultB;
    });
});
```

Where replacing a function call with a new method call or similar:
```php
// existing
_sp('somg language string');

// new
$lm = $pimple[LanguageManager::class];
$lm->get('lang.string.name');

// Experiment controlled:
$result = Experiment::execute('experiment.name', function(Experiment $exp) use($pimple) {
    $exp->control(
        '_sp',
        ['somg language string']
    );
    $exp->candidate(
        [$pimple[LanguageManager::class], 'get'],
        ['somg language string']
    );

    $exp->comparator(function($resultA, $resultB) {
        return $resultA === $resultB;
    });
});
```

Where replacing a method call on with a new method on an instance:
```php
$object = new \QDateTime();

$result = Experiment::execute('experiment.name', function(Experiment $exp) use($object) {
    $exp->control([$this->createdAt, 'getTimestamp']);

    $exp->candidate([$this->createdAt, 'awesomeGetTimestamp']);
});
```

Instantiating an instance of `ParallelCodePath`:
```php
$instance = new \QDateTime();

$exp = new Experiment('test', null);
$exp->control([$instance, 'getTimestamp']);
$exp->candidate([$instance, 'awesomeGetTimestamp']);

// optional comparator, uses phpunits compare library by default.
$exp->comparator(function($resultA, $resultB) {
    return $resultA === $resultB;
});
$result = $exp->run();
```

