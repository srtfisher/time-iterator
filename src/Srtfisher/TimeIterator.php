<?php namespace Srtfisher;

use Carbon\Carbon,
    Srtfisher\TimeIteratorException;

/**
 * Time Iterator
 *
 * Making it easier to pull in data for intervals of time over a period of time.
 *
 * @author  Sean Fisher <hi@seanfisher.co>
 * @link https://github.com/srtfisher/time-iterator
 */
class TimeIterator implements \Iterator, \Countable {
    /**
     * Start Time of the Interval
     *
     * @type Carbon\Carbon
     */
    protected $start;

    /**
     * End Time of the Current Interval
     * 
     * @var Carbon\Carbon
     */
    protected $end;

    /**
     * Interval Length in Seconds
     * 
     * @var integer
     */
    protected $intervalTime = 3600;

    /**
     * Current Time in the Interval
     * 
     * @var Carbon\Carbon
     */
    protected $currentTime;

    /**
     * Callback to handle the results
     * 
     * @var callable
     */
    protected $callback;

    /**
     * Results from the Iterator
     * 
     * @var array
     */
    protected $results = array();

    /**
     * Run Counter
     * 
     * @var integer
     */
    public $runCount = 0;

    /**
     * Current Key for Tracking in the Array Iterator
     *
     * @var interger
     */
    protected $position = 0;

    /**
     * Setup the Iterator to go though time
     * 
     * @param integer|Guzzle\Guzzle Time in seconds to go back (also could be a Guzzle Object)
     * @param integer|Guzzle\Guzzle Time in seconds to continue onto (also could be a Guzzle Object)
     * @param integer Time in seconds between each interval
     * @param callable Callback function to retrieve the data for a time span
     */
    public function __construct($start = NULL, $end = NULL, $intervalTime = NULL, $callback = NULL)
    {
        $this->setStart($start);
        $this->setEnd($end);
        $this->position = 0;

        if ($intervalTime)
            $this->intervalTime = (int) $intervalTime;

        if ($callback) :
            if (! is_callable($callback))
                throw new TimeIteratorException('Callback for Timeiterator is not callable.');
            else
                $this->callback = $callback;
        endif;
    }

    /**
     * Set the Start Time
     *
     * @param  integer|Guzzle/Guzzle Interger to subtract from now or a Guzzle Object
     */
    public function setStart($time = 0)
    {
        $this->start = ($time instanceof Carbon) ? $time : Carbon::now()->subSeconds((int) $time);
    }

    /**
     * Set the End Time
     *
     * @param  integer|Guzzle/Guzzle Interger to add from now or a Guzzle Object
     */
    public function setEnd($time = 0)
    {
        $this->end = ($time instanceof Carbon) ? $time : Carbon::now()->addSeconds((int) $time);
    }

    /**
     * Retrieve the Interval Time
     *
     * @return  interval
     */
    public function getInterval()
    {
        return $this->intervalTime;
    }

    /**
     * Retrieve the Start Time
     *
     * @return Guzzle\Guzzle
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Retrieve the End Time
     *
     * @return Guzzle\Guzzle
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set the Interval
     *
     * @param  integer
     */
    public function setInterval($interval)
    {
        $this->intervalTime = $interval;
    }

    /**
     * Set the Callback
     *
     * @param  callable
     */
    public function setCallback($callback)
    {
        if (! is_callable($callback))
            throw new TimeIteratorException('Callback for Timeiterator is not callable.');
        $this->callback = $callback;
    }

    /**
     * Retrieve the Results
     *
     * @return  array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Run the Iterator
     *
     * @return Srtfisher\TimeIterator
     */
    public function run()
    {
        // Sanity Check
        if (! $this->callback)
            throw new TimeIteratorException('No callback passed to run iterator for.');

        if ($this->start >= $this->end)
            throw new TimeIteratorException('Start time cannot be after or the same as the endtime.');

        // Reset the Variables
        $this->results = array();
        $finished = false;
        $this->currentTime = $this->start->copy();
        
        // Begin the Loop
        while (! $finished) {
            // Ensuring that the callback always adds results
            $resultCountBefore = count($this->results);

            // Execute the callback
            // 
            // Three arguments are sent:
            //  1. Start time of the Interval
            //  2. End time of the Interval
            //  3. TimeIterator object to be used to set results for
            call_user_func_array($this->callback, array(
                $this->currentTime->copy(),
                $this->currentTime->copy()->addSeconds($this->intervalTime),
                $this
            ));

            // Ensure we don't miss anything
            if (count($this->results) == $resultCountBefore)
                throw new TimeIteratorException('TimeInterval Error: no results added for interval');

            // Bump to next time interval
            $this->currentTime->addSeconds($this->intervalTime);

            // See if we've run out of time
            if ($this->currentTime >= $this->end)
                $finished = true;

            $this->runCount++;
        }

        return $this;
    }

    /**
     * Add Results to the Current Interval Set
     *
     * @param array
     */
    public function addResults(array $data)
    {
        $this->results[] = array(
            'data' => $data,
            'intervalStart' => $this->currentTime->copy(),
            'intervalEnd' => $this->currentTime->copy()->addSeconds($this->intervalTime)
        );
    }

    /**
     * Rewind the array pointer to the first element
     * 
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Retrieve the Current Element
     * 
     * @return array
     */
    public function current()
    {
        return $this->results[$this->position];
    }

    /**
     * Retrieve the Current Key
     * 
     * @return integer
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Moves the current position to the next element.
     * 
     * @return void
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * This method is called after Iterator::rewind() and Iterator::next() to check if the current position is valid.
     *
     * @return  boolean
     */
    public function valid()
    {
        return isset($this->results[$this->position]);
    }

    /**
     * Retrieve the Current Count of the Result Set
     * 
     * @return integer
     */
    public function count() 
    { 
        return count($this->results);
    }
}