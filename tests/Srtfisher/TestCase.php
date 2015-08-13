<?php namespace Srtfisher;
use Carbon\Carbon;

class TestCase extends \PHPUnit_Framework_TestCase {
    public function testStartTimeMethod()
    {
        $object = new TimeIterator;
        $object->setStart(3600);

        $carbon = Carbon::now()->subSeconds(3600);

        $this->assertEquals($carbon, $object->getStart());

        // Guzzle Object
        $objectTwo = new TimeIterator;
        $objectTwo->setStart(Carbon::now()->subSeconds(3600));
        $this->assertEquals($carbon, $objectTwo->getStart());
    }

    public function testEndTimeMethod()
    {
        $object = new TimeIterator;
        $object->setEnd(3600);

        $carbon = Carbon::now()->addSeconds(3600);

        $this->assertEquals($carbon, $object->getEnd());

        // Guzzle Object
        $objectTwo = new TimeIterator;
        $objectTwo->setEnd(Carbon::now()->addSeconds(3600));
        $this->assertEquals($carbon, $objectTwo->getEnd());
    }

    public function testStartTimeConstruct()
    {
        $object = new TimeIterator(3600);

        $carbon = Carbon::now()->subSeconds(3600);

        $this->assertEquals($carbon, $object->getStart());

        $objectTwo = new TimeIterator(Carbon::now()->subSeconds(3600));
        $this->assertEquals($carbon, $objectTwo->getStart());
    }

    public function testEndTimeConstruct()
    {
        $object = new TimeIterator(null, 3600);

        $carbon = Carbon::now()->addSeconds(3600);

        $this->assertEquals($carbon, $object->getEnd());

        $objectTwo = new TimeIterator(null, Carbon::now()->addSeconds(3600));
        $this->assertEquals($carbon, $objectTwo->getEnd());
    }

    public function testIntervalMethod()
    {
        $object = new TimeIterator;
        $object->setInterval(3600);

        $this->assertEquals(3600, $object->getInterval());
    }

    public function testIntervalConstruct()
    {
        $object = new TimeIterator(null, null, 3600);

        $this->assertEquals(3600, $object->getInterval());
    }

    public function testCountableInterface()
    {
        // Create a time iterator going back 7 days that will loop over each day
        $object = new TimeIterator(3600*24*7, null, 3600*24, function($start, $end, $object) {
            $object->addResults(array(
                'testValueOne' => true,
                'testValueTwo' => false
            ));
        });
        $object->run();

        $this->assertEquals(count($object), 7);
        $this->assertEquals($object->runCount, 7);
    }

    public function testArrayAccessInterface()
    {
        // Create a time iterator going back 7 days that will loop over each day
        $object = new TimeIterator(3600*24*7, null, 3600*24, function($start, $end, $object) {
            $object->addResults(array(
                'testValueOne' => true,
                'testValueTwo' => false
            ));
        });
        $object->run();
        $object->next();

        $this->assertEquals(1, $object->key());

        // Go though 10 (there is only 7 in the system) so it should be invalid
        for ($i = 0; $i < 10; $i++)
            $object->next();

        $this->assertFalse($object->valid());

        $object->rewind();
        $this->assertEquals(0, $object->key());
    }

    /**
     * @expectedException               Srtfisher\TimeIteratorException
     * @expectedExceptionMessage  TimeInterval Error: no results added for interval
     */
    public function testNoResultsAdded()
    {
        $object = new TimeIterator(3600*24*7, null, 3600*24, function($start, $end, $object) {

        });
        $object->run();
    }

    /**
     * @expectedException               Srtfisher\TimeIteratorException
     * @expectedExceptionMessage  Callback for `Timeiterator` is not callable.
     */
    public function testValidCallback()
    {
        $object = new TimeIterator(3600*24*7, null, 2600*24, true);
    }

    /**
     * @expectedException               Srtfisher\TimeIteratorException
     * @expectedExceptionMessage  Callback for Timeiterator is not callable.
     */
    public function testValidCallbackMethod()
    {
        $object = new TimeIterator(3600*24*7, null, 3600*24);
        $object->setCallback(true);
    }

    public function testResultsReturned()
    {
        $object = new TimeIterator(3600*24*7, null, 3600*24, function($start, $end, $object) {
            $object->addResults(array(
                'value' => 'the value',
            ));
        });
        $start = $object->getStart()->copy();
        $object->run();

        foreach ($object as $data) {
            $this->assertEquals($start, $data['intervalStart']);
            $start->addSeconds(3600*24);
            $this->assertEquals($start, $data['intervalEnd']);

            // Test the data that it returns
            $this->assertEquals($data['data']['value'], 'the value');
        }

        $results = $object->getResults();
        $this->assertEquals(count($results), 7);
        $start = $object->getStart()->copy();

        foreach ($object as $data) {
            $this->assertEquals($start, $data['intervalStart']);
            $start->addSeconds(3600*24);
            $this->assertEquals($start, $data['intervalEnd']);

            // Test the data that it returns
            $this->assertEquals($data['data']['value'], 'the value');
        }
    }

    /**
     * @expectedException               Srtfisher\TimeIteratorException
     * @expectedExceptionMessage  Start time cannot be after or the same as the endtime.
     */
    public function testEndTimeBeforeStart()
    {
        $object = new TimeIterator;
        $object->setCallback(function($start, $end, $object) {
            $object->addResults(array());
        });

        $object->setStart(Carbon::now()->addSeconds(3600));
        $object->setEnd(Carbon::now());
        $object->run();
    }
}
