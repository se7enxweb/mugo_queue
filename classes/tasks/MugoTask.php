<?php

/**
 * 
 */
class MugoTask
{
	/**
	 * @var string
	 */
	protected $log_destination;

	/**
	 * @var string
	 */
	protected $queueIdentifier;


	public function __construct(){}

	/**
	 * Creates an array of task IDs that get added to the queue for a later execute.
	 *
	 * @param array $parameters
	 * @return array
	 */
	public function create( $parameters )
	{
		return array();
	}
	
	/**
	 * Executes on single task id.
	 *
	 * @param string $task_id
	 * @param array $parameters
	 * @return boolean
	 */
	public function execute( $task_id, $parameters )
	{
		return true;
	}

	/**
	 * The controller calls it for each task execution
	 */
	public function pre_execute() {}

	/**
	 * The controller calls it for each task execution
	 */
	public function post_execute() {}

	/**
	 * The controller calls it before it starts executing tasks
	 */
	public function pre_thread_execute() {}

	/**
	 * The controller calls it after it executed all tasks
	 */
	public function post_thread_execute() {}

	/**
	 * Helper function to log messages
	 * TODO: considert to forward the call to the controller -- it has the same log function
	 * 
	 * @param type $message
	 */
	protected function log( $message )
	{
		$logDestination = $this->log_destination ? $this->log_destination : ( get_class( $this ) . '.log' );

		$output = '[' . get_class( $this ) . '] ' . $message;
		eZLog::write( $output, $logDestination );
	}

	/**
	 * @param string $taskTypeId
	 * @return null|MugoTask
	 */
	public static function factory( $taskTypeId )
	{
		$instance = null;

		// Try to get an instance of the class
		if( class_exists( $taskTypeId ) )
		{
			$instance = new $taskTypeId;

			if( !( $instance instanceof MugoTask ) )
			{
				unset( $instance );
			}
		}

		return $instance;
	}

	public function getQueueIdentifier()
	{
		return $this->queueIdentifier ? $this->queueIdentifier : get_class( $this );
	}
}
