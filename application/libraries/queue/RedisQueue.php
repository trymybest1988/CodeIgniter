<?php 
require_once  'Queue.php';
require_once  'QueueInterface.php';
require_once  'RedisJob.php';

/**
 * reidis 消息队列
 * @author Mr.Nobody
 *
 */
class RedisQueue extends Queue implements QueueInterface {

	/**
	* The Redis database instance.
	*
	 * @var \Illuminate\Redis\Database
	 */
	protected $redis;

	/**
	 * The connection name.
	 *
	 * @var string
	 */
	protected $connection;

	/**
	 * The name of the default queue.
	 *
	 * @var string
	 */
	protected $default;

	/**
	 * key of the source lock.
	 *
	 * @var string
	 */
	protected static $_KEY_OF_SOURCE_LOCK = 'redis_queue_source_lock_key';
	
	/**
	 * Create a new Redis queue instance.
	 *
	 * @param  \Illuminate\Redis\Database  $redis
	 * @param  string  $default
	 * @param  string  $connection
	 * @return void
	 */
	public function __construct($params)
	{
	    extract($params);
	    //MyRedis $redis, $default = 'default', $connection = null
		$this->redis = isset($redis) ? $redis : NULL;
		$this->default = isset($default) ? $default : NULL;
		$this->connection = isset($connection) ? $connection : NULL;
	}

	/**
	 * Push a new job onto the queue.
	 *
	 * @param  string  $job eg. $job = 'class@method'
	 * @param  mixed   $data
	 * @param  string  $queue
	 * @return void
	 */
	public function push($job, $data = '', $queue = null)
	{
		return $this->pushRaw($this->createPayload($job, $data), $queue);
	}

	/**
	 * Push a raw payload onto the queue.
	 *
	 * @param  string  $payload
	 * @param  string  $queue
	 * @param  array   $options
	 * @return mixed
	 */
	public function pushRaw($payload, $queue = null, array $options = array())
	{
		$this->redis->rpush($this->getQueue($queue), $payload);
		$payload = json_decode($payload, true);

		return $payload['id'];
	}

	/**
	 * Push a new job onto the queue after a delay.
	 *
	 * @param  \DateTime|int  $delay
	 * @param  string  $job
	 * @param  mixed   $data
	 * @param  string  $queue
	 * @return void
	 */
	public function later($delay, $job, $data = '', $queue = null)
	{
		$payload = $this->createPayload($job, $data);

		$delay = $this->getSeconds($delay);

		$this->redis->zadd($this->getQueue($queue).':delayed', $this->getTime() + $delay, $payload);

		$payload = json_decode($payload, true);

		return $payload['id'];
	}

	/**
	 * Release a reserved job back onto the queue.
	 *
	 * @param  string  $queue
	 * @param  string  $payload
	 * @param  int  $delay
	 * @param  int  $attempts
	 * @return void
	 */
	public function release($queue, $payload, $delay, $attempts)
	{
		$payload = $this->setMeta($payload, 'attempts', $attempts);

		$this->redis->zadd($this->getQueue($queue).':delayed', $this->getTime() + $delay, $payload);
	}

	/**
	 * Pop the next job off of the queue.
	 *
	 * @param  string  $queue
	 * @return \Illuminate\Queue\Jobs\Job|null
	 */
	public function pop($queue = null)
	{
		$original = $queue ?: $this->default;

		$this->migrateAllExpiredJobs($queue = $this->getQueue($queue));

		$job = $this->redis->lpop($queue);

		if ( ! is_null($job) && $job !== false)
		{
			$this->redis->zadd($queue.':reserved', $this->getTime() + 60, $job);

			return new RedisJob($this, $job, $original);
		}
	}

	/**
	 * Delete a reserved job from the queue.
	 *
	 * @param  string  $queue
	 * @param  string  $job
	 * @return void
	 */
	public function deleteReserved($queue, $job)
	{
		$this->redis->zrem($this->getQueue($queue).':reserved', $job);
	}

	/**
	 * Migrate all of the waiting jobs in the queue.
	 *
	 * @param  string  $queue
	 * @return void
	 */
	protected function migrateAllExpiredJobs($queue)
	{
		$this->migrateExpiredJobs($queue.':delayed', $queue);

		$this->migrateExpiredJobs($queue.':reserved', $queue);
	}

	/**
	 * Migrate the delayed jobs that are ready to the regular queue.
	 *
	 * @param  string  $from
	 * @param  string  $to
	 * @return void
	 */
	public function migrateExpiredJobs($from, $to)
	{
	   // 取到期任务 
	   $jobs = $this->getExpiredJobs($from, $time = $this->getTime());
	   if (count($jobs) <= 0) {
	       return;
	   }
	   
        // 锁的key
        $lockKey = self::$_KEY_OF_SOURCE_LOCK . $from;
        
        // 上锁
        $lock = $this->redis->setnx($lockKey, 1);
        
        // 锁的过期时间设1s
        $this->redis->expire($lockKey, 1);

        // 上锁失败，则不继续执行
        if (! $lock) {
            return;
        }
        
        // 上锁成功，则执行
        $this->removeExpiredJobs($from, $time);
        call_user_func_array(array($this->redis, 'rpush'), array_merge(array($to), $jobs));
        
        // 解锁
        $this->redis->del($lockKey);
	}

	/**
	 * Get the delayed jobs that are ready.
	 *
	 * @param  string  $queue
	 * @param  int     $time
	 * @return array
	 */
	protected function getExpiredJobs($queue, $time)
	{
		return $this->redis->zrangebyscore($queue, '-inf', $time);
	}

	/**
	 * Remove the delayed jobs that are ready for processing.
	 *
	 * @param  string  $queue
	 * @param  int     $time
	 * @return void
	 */
	protected function removeExpiredJobs($queue, $time)
	{
		$this->redis->zremrangebyscore($queue, '-inf', $time);
	}

	/**
	 * Create a payload string from the given job and data.
	 *
	 * @param  string  $job
	 * @param  mixed   $data
	 * @param  string  $queue
	 * @return string
	 */
	protected function createPayload($job, $data = '', $queue = null)
	{
		$payload = parent::createPayload($job, $data);

		$payload = $this->setMeta($payload, 'id', $this->getRandomId());

		return $this->setMeta($payload, 'attempts', 1);
	}

	/**
	 * Get a random ID string.
	 *
	 * @return string
	 */
	protected function getRandomId($length = 32)
	{
	    
		$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

		return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
	}

	/**
	 * Get the queue or return the default.
	 *
	 * @param  string|null  $queue
	 * @return string
	 */
	protected function getQueue($queue)
	{
		return 'openapi_taskqueues_'.($queue ?: $this->default);
	}

	/**
	 * Get the underlying Redis instance.
	 *
	 * @return \Illuminate\Redis\Database
	 */
	public function getRedis()
	{
		return $this->redis;
	}

}
