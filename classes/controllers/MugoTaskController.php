<?php

/**
 * Class MugoTaskController
 */
class MugoTaskController
{
	/**
	 * @var MugoTask
	 */
	protected $mugo_task;

	/**
	 *
	 * @var MugoQueue
	 */
	public $mugoQueue;

	/**
	 * @var string
	 */
	protected $log_destination;

	/**
	 * @param MugoQueue $queueHandler
	 * @param MugoTask $taskHandler
	 */
	public function __construct( MugoQueue $queueHandler, MugoTask $taskHandler )
	{
		$this->mugoQueue = $queueHandler;
		$this->mugo_task = $taskHandler;
	}

	/**
	 * Uses a Task instance to get a list of task ids and add those to the queue
	 *
	 * @param $task_type_id
	 * @param null $parameters
	 * @param int $limit
	 */
	public function create( $parameters = null, $limit = 0 )
	{
		$task_ids = $this->mugo_task->create( $parameters );

		// Limit handling
		$limit = (int)$limit;
		if( $limit > 0 && $limit < count( $task_ids ) )
		{
			$task_ids = array_slice( $task_ids, 0, $limit );
		}

		if( !empty( $task_ids ) )
		{
			$this->mugoQueue->add_tasks(
				$this->mugo_task->getQueueIdentifier(),
				$task_ids
			);
		}

		$this->log( count( $task_ids ) . ' task(s) created.' );
	}

	/**
	 * @param $task_type_id
	 * @param null $parameters
	 * @param int $limit
	 */
	public function execute( $parameters = null, $limit = 0 )
	{
		$tasks = $this->mugoQueue->get_tasks(
			$this->mugo_task->getQueueIdentifier(),
			$limit
		);
		
		if( !empty( $tasks ) )
		{
			$this->mugo_task->pre_thread_execute();
			
			foreach( $tasks as $task )
			{
				$this->mugo_task->pre_execute();
				$success = $this->mugo_task->execute( $task[ 'id' ], $parameters );
				$this->mugo_task->post_execute();
				
				if( $success )
				{
					$this->mugoQueue->remove_tasks(
						$this->mugo_task->getQueueIdentifier(),
						array( $task[ 'id' ] )
					);
				}
				else
				{
					$this->log( 'Failed to execute task: ' . $task[ 'id' ] );
				}

				//oom
				eZContentObject::clearCache();
				unset( $GLOBALS[ 'eZTemplateInstance' ] );
			}

			$this->mugo_task->post_thread_execute();
			
			//$this->log( count( $task_ids ) . ' task(s) executed.' );
		}
		else
		{
			$this->log( 'no matching tasks found in queued' );
		}
	}

	/**
	 * @param string $task_type_id
	 * @return boolean
	 */
	public function remove()
	{
		$this->log( 'Remove tasks' );
		
		return $this->mugoQueue->remove_tasks( $this->mugo_task->getQueueIdentifier() );
	}

	/**
	 * @param $message
	 */
	protected function log( $message )
	{
		$logDestination = $this->log_destination ? $this->log_destination : ( get_class( $this ) . '.log' );
		$output = '[' . get_class( $this ) . '] ' . $message;
		eZLog::write( $output, $logDestination );
	}
}
