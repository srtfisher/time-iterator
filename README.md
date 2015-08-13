[![Build Status](https://travis-ci.org/srtfisher/time-iterator.png?branch=master)](https://travis-ci.org/srtfisher/time-iterator)

# Time Iterator

Making it easier to pull in data for intervals of time over a period of time. This was built so that
it would be easier to build graphs that show changes over time for a specific period.

## Authors

Maintained by [Sean Fisher](https://github.com/srtfisher).

## Installation

Install via Composer

```json
{
    "require": {
        "srtfisher/time-iterator": "0.0.2"
    },
}
```

## Usage

```php
use Srtfisher\TimeIterator;

$iterate = new TimeIterator;

// Set the Start date for five months ago
$iterate->setStart(60*60*24*7*5);

// Set the Interval to go over as 24 hours
$iterate->setInterval(60*60*24);

$iterate->setCallback(function(Carbon $start, Carbon $end, TimeIterator $object) {
    // Perform some logic here
    $object->addResults(array(
        'data' => true,
        // ....
    ));
});

// Run it
$iterate->run();

// Now, you can treat the object as an array
foreach ($iterate as $key => $data) {
    // Do something with the data...
}

```

## Handling of Time

When setting the callback, we pass a `$start` and an `$end` arguments. Those arguments are [Carbon](https://github.com/briannesbitt/Carbon) objects. Carbon is a class built off of PHP's `DateTime` but with better methods to handle time more efficiently.
