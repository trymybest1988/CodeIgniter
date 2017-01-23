<?php 
require_once  'Job.php';
class RedisJob extends Job {

	/**
	 * The Redis queue instance.
	 *
	 * @var \Illuminate\Queue\RedisQueue
	 */
	protected $redis;

	/**
	 * The Redis job payload.
	 *
	 * @var string
	 */
	protected $job;

	/**
	 * Create a new job instance.
	 *
	 * @param  \Illuminate\Queue\RedisQueue  $redis
	 * @param  string  $job
	 * @param  string  $queue
	 * @return void
	 */
	public function __construct(RedisQueue $redis, $job, $queue)
	{
		$this->job = $job;
		$this->redis = $redis;
		$this->queue = $queue;
	}

	/**
	 * Fire the job.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->resolveAndFire(json_decode($this->getRawBody(), true));
	}

	/**
	 * Get the raw body string for the job.
	 *
	 * @return string
	 */
	public function getRawBody()
	{
		return $this->job;
	}

	/**
	 * Delete the job from the queue.
	 *
	 * @return void
	 */
	public function delete()
	{
		parent::delete();

		$this->redis->deleteReserved($this->queue, $this->job);
	}

	/**
	 * Release the job back into the queue.
	 *
	 * @param  int   $delay
	 * @return void
	 */
	public function release($delay = 0)
	{
		$this->delete();

		$this->redis->release($this->queue, $this->job, $delay, $this->attempts() + 1);
	}

	/**
	 * Get the number of times the job has been attempted.
	 *
	 * @return int
	 */
	public function attempts()
	{
	    $job = json_decode($this->job, true);
		return $job['attempts'];
	}

	/**
	 * Get the job identifier.
	 *
	 * @return string
	 */
	public function getJobId()
	{
	    $job = json_decode($this->job, true);
		return $job['id'];
	}

	/**
	 * Get the underlying queue driver instance.
	 *
	 * @return \Illuminate\Redis\Database
	 */
	public function getRedisQueue()
	{
		return $this->redis;
	}

	/**
	 * Get the underlying Redis job.
	 *
	 * @return string
	 */
	public function getRedisJob()
	{
		return $this->job;
	}

}