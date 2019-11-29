<?php
/**
 * @brief		Elasticsearch Search Index
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		31 Oct 2017
*/

namespace IPS\Content\Search\Elastic;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Elasticsearch Search Index
 */
class _Index extends \IPS\Content\Search\Index
{
	/**
	 * @brief	Elasticsearch version requirements
	 */
	const MINIMUM_VERSION = '5.6.0';
	const UNSUPPORTED_VERSION = '7.0.0';

	/**
	 * @brief	The server URL
	 */
	protected $url;
	
	/**
	 * Constructor
	 *
	 * @param	\IPS\Http\Url	$url	The server URL
	 * @return	void
	 */
	public function __construct( \IPS\Http\Url $url )
	{
		$this->url = $url;
	}
	
	/**
	 * Initalize when first setting up
	 *
	 * @return	void
	 */
	public function init()
	{
		$analyzer = \IPS\Settings::i()->search_elastic_analyzer;
		$settings = array(
			'max_result_window'	=> \IPS\Settings::i()->search_index_maxresults
		);
		if ( $analyzer === 'custom' )
		{
			$settings['analysis'] = json_decode( '{' . \IPS\Settings::i()->search_elastic_custom_analyzer . '}', TRUE );
			$analyzer = key( $settings['analysis']['analyzer'] );
		}
				
		$this->url->request()->delete();
		$this->url->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->put( json_encode( array(
			'settings'	=> $settings,
			'mappings'	=> array(
				'content' 	=> array(
					'properties'	=> array(
						'index_class'				=> array( 'type' => 'keyword' ),
						'index_object_id'			=> array( 'type' => 'long' ),
						'index_item_id'				=> array( 'type' => 'long' ),
						'index_container_class'		=> array( 'type' => 'keyword' ),
						'index_container_id'		=> array( 'type' => 'long' ),
						'index_title'				=> array(
							'type' 		=> 'text',
							'analyzer'	=> $analyzer,
						),
						'index_content'				=> array(
							'type' 		=> 'text',
							'analyzer'	=> $analyzer,
						),
						'index_permissions'			=> array( 'type' => 'keyword' ),
						'index_date_created'		=> array(
							'type' 		=> 'date',
							'format'	=> 'epoch_second',
						),
						'index_date_updated'		=> array(
							'type' 		=> 'date',
							'format'	=> 'epoch_second',
						),
						'index_date_commented'		=> array(
							'type' 		=> 'date',
							'format'	=> 'epoch_second',
						),
						'index_author'				=> array( 'type' => 'long' ),
						'index_tags'				=> array( 'type' => 'keyword' ),
						'index_prefix'				=> array( 'type' => 'keyword' ),
						'index_hidden'				=> array( 'type' => 'byte' ),
						'index_item_index_id'		=> array( 'type' => 'keyword' ),
						'index_item_author'			=> array( 'type' => 'long' ),
						'index_is_last_comment'		=> array( 'type' => 'boolean' ),
						'index_club_id'				=> array( 'type' => 'long' ),
						'index_class_type_id_hash'	=> array( 'type' => 'keyword' ),
						'index_comments'			=> array( 'type' => 'long' ),
						'index_reviews'				=> array( 'type' => 'long' ),
						'index_participants'		=> array( 'type' => 'long' ),
					)
				)
			)
		) ) );		
	}
	
	/**
	 * Get index data
	 *
	 * @param	\IPS\Content\Searchable	$object	Item to add
	 * @return	array|NULL
	 */
	public function indexData( \IPS\Content\Searchable $object )
	{
		if ( $indexData = parent::indexData( $object ) )
		{
			$indexData['index_permissions'] = explode( ',', $indexData['index_permissions'] );
			$indexData['index_tags'] = $indexData['index_tags'] ? explode( ',', $indexData['index_tags'] ) : array();
			$indexData['index_is_last_comment'] = (bool) $indexData['index_is_last_comment'];
			
			if ( $object instanceof \IPS\Content\Item )
			{
				$indexData += $this->metaCounts( $object );
			}
			else
			{
				$indexData += $this->metaCounts( $object->item() );
			}

			return $indexData;
		}
		
		return NULL;
	}
			
	/**
	 * Index an item
	 *
	 * @param	\IPS\Content\Searchable	$object	Item to add
	 * @return	void
	 */
	public function index( \IPS\Content\Searchable $object )
	{
		if ( $indexData = $this->indexData( $object ) )
		{
			/* If nobody has permission to access it, just remove it */
			if ( !$indexData['index_permissions'] )
			{
				$this->removeFromSearchIndex( $object );
			}
			/* Otherwise, go ahead... */
			else
			{
				try
				{
					$class = \get_class( $object );
					
					$existingData		= NULL;
					$existingIndexId	= NULL;
					$resetLastComment	= FALSE;
					
					try
					{
						$r = $this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/' . $this->getIndexId( $object ) )->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->get()->decodeJson();
						if ( $r['found'] )
						{
							$existingData = $r['_source'];
							$existingIndexId = $r['_id'];
						}
					}
					catch( \Exception $e ) { }
					
					if ( $object instanceof \IPS\Content\Comment and $existingIndexId and $existingData['index_is_last_comment'] and $indexData['index_is_last_comment'] and $indexData['index_item_id'] and $indexData['index_hidden'] !== 0 )
					{
						/* We do not allow hidden or needing approval comments to become flagged as the last comment as this means users without hidden view permission never see the item in an item only stream */
						$indexData['index_is_last_comment'] = false;
						
						$resetLastComment = TRUE;
					}
					else if ( $indexData['index_is_last_comment'] and $indexData['index_item_id'] )
					{
						$classes = array( $class );
						
						/* If this is the latest comment, unflag what was set before on both item and comment */
						if ( $object instanceof \IPS\Content\Comment )
						{
							$itemClass = $object::$itemClass;
							if ( ! $itemClass::$firstCommentRequired )
							{
								$classes[] = $itemClass;
							}
							
							if ( isset( $itemClass::$reviewClass ) )
							{
								$classes[] = $itemClass::$reviewClass;
							}
						}
						else if ( $object instanceof \IPS\Content\Item )
						{
							if ( isset( $class::$commentClass ) )
							{
								$classes[] = $class::$commentClass;
							}
							if ( isset( $class::$reviewClass ) )
							{
								$classes[] = $class::$reviewClass;
							}
						}
						
						$resetLastComment = TRUE;
					}
															
					/* Insert into index */
					$r = $this->url
						->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/' . $this->getIndexId( $object ) )
						->request( \IPS\LONG_REQUEST_TIMEOUT )
						->setHeaders( array( 'Content-Type' => 'application/json' ) )
						->put( json_encode( $indexData ) );
						
					/* Views / Comments / Reviews */
					if ( $object instanceof \IPS\Content\Item )
					{
						$item = $object;
					}
					elseif ( $object instanceof \IPS\Content\Comment )
					{
						$item = $object->item();
					}
					$this->rebuildMetaCounts( $item, $indexData['index_is_last_comment'] ? $this->getIndexId( $object ) : NULL );
				}
				catch ( \IPS\Http\Request\Exception $e )
				{
					\IPS\Log::log( $e, 'elasticsearch' );
				}
				
				if ( $resetLastComment )
				{
					$this->resetLastComment( array( $indexData['index_class'] ), $indexData['index_item_id'] );
				}
			}
		}
	}
	
	/**
	 * Clear out any tasks associated with the search index method
	 *
	 * @return void
	 */
	public function clearTasks()
	{
		try
		{
			/* This request *intentionally* goes to _tasks and not (ourpath)/_tasks */
			$response = $this->url->setPath( '/_tasks' )->request( \IPS\LONG_REQUEST_TIMEOUT )->setHeaders( array( 'Content-Type' => 'application/json' ) )->get()->decodeJson();

			foreach( $response['nodes'] as $nodeId => $nodeData )
			{
				foreach( $nodeData['tasks'] as $taskId => $taskData )
				{
					/* We only need to worry about deleting parent tasks */
					if( isset( $taskData['parent_task_id'] ) )
					{
						continue;
					}

					/* If the task is cancellable it isn't finished yet */
					if( $taskData['cancellable'] === TRUE )
					{
						continue;
					}

					$this->url->setPath( '/.tasks/task' . $taskId )->request( \IPS\LONG_REQUEST_TIMEOUT )->setHeaders( array( 'Content-Type' => 'application/json' ) )->delete();
				}
			}
		}
		catch( \Exception $e )
		{
			\IPS\Log::log( $e, 'elasticsearch' );
		}
	}
	
	/**
	 * Get the comment / review counts for an item
	 *
	 * @param	\IPS\Content\Searchable	$item					The content item
	 * @return	void
	 */
	protected function metaCounts( $item )
	{
		$databaseColumnId = $item::$databaseColumnId;
		
		$participants = array( $item->mapped('author') );
		if ( isset( $item::$commentClass ) )
		{
			$commentClass = $item::$commentClass;
			$participants += iterator_to_array( \IPS\Db::i()->select( 'DISTINCT ' . $commentClass::$databasePrefix . $commentClass::$databaseColumnMap['author'], $commentClass::$databaseTable, array( $commentClass::$databasePrefix . $commentClass::$databaseColumnMap['item'] . '=?', $item->$databaseColumnId ) ) );
		}
		if ( isset( $item::$reviewClass ) )
		{
			$reviewClass = $item::$reviewClass;
			$participants += iterator_to_array( \IPS\Db::i()->select( 'DISTINCT ' . $reviewClass::$databasePrefix . $reviewClass::$databaseColumnMap['author'], $reviewClass::$databaseTable, array( $reviewClass::$databasePrefix . $reviewClass::$databaseColumnMap['item'] . '=?', $item->$databaseColumnId ) ) );
		}
		$participants = array_values( array_unique( $participants ) );
		
		return array(
			'index_comments'		=> $item->mapped('num_comments'),
			'index_reviews'			=> $item->mapped('num_reviews'),
			'index_participants'	=> $participants
		);
	}
	
	/**
	 * Rebuild the comment / review counts for an item
	 *
	 * @param	\IPS\Content\Searchable	$item					The content item
	 * @param	string					$lastCommentIndexId		The index ID of the last comment, if you want to reset others
	 * @return	void
	 */
	protected function rebuildMetaCounts( $item, $lastCommentIndexId = NULL )
	{
		$databaseColumnId = $item::$databaseColumnId;
		$class = \get_class( $item );
		$classes = array( $class );
		if ( isset( $class::$commentClass ) )
		{
			$classes[] = $class::$commentClass;
		}
		if ( isset( $class::$reviewClass ) )
		{
			$classes[] = $class::$reviewClass;
		}
		
		try
		{			
			$updates	= array();
			$params		= array();
			foreach ( $this->metaCounts( $item ) as $k => $v )
			{
				if ( \is_array( $v ) )
				{
					$updates[]	= "ctx._source.{$k} = params.param_{$k};";
					$params[ 'param_' . $k ]	= array_map( 'intval', $v );
				}
				elseif ( \is_null( $v ) )
				{
					$updates[]	= "ctx._source.{$k} = params.param_{$k};";
					$params['param_' . $k ]		= null;
				}
				else
				{
					$updates[]	= "ctx._source.{$k} = params.param_{$k};";
					$params['param_' . $k ]		= \intval( $v );
				}
			}
			if ( !\is_null( $lastCommentIndexId ) )
			{
				$updates[] = "ctx._source.index_is_last_comment = ( ctx._id == params.param_lastcommentid );";
				$params['param_lastcommentid']	= $lastCommentIndexId;
			}
			$this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/_update_by_query' )->setQueryString( array( 'conflicts' => 'proceed', 'wait_for_completion' => 'false', 'scroll_size' => \IPS\Settings::i()->search_index_maxresults ) )->request( \IPS\LONG_REQUEST_TIMEOUT )->setHeaders( array( 'Content-Type' => 'application/json' ) )->post( json_encode( array(
				'script'	=> array(
					'source'	=> implode( ' ', $updates ),
					'lang'		=> 'painless',
					'params'	=> $params
				),
				'query'		=> array(
					'bool'		=> array(
						'must'		=> array(
							array(
								'terms'	=> array(
									'index_class' => $classes
								)
							),
							array(
								'term'	=> array(
									'index_item_id' => $item->$databaseColumnId
								)
							),
						)
					)
				)
			) ) );
		}
		catch ( \IPS\Http\Request\Exception $e )
		{
			\IPS\Log::log( $e, 'elasticsearch' );
		}
	}
	
	/**
	 * Retrieve the search ID for an item
	 *
	 * @param	\IPS\Content\Searchable	$object	Item to add
	 * @return	void
	 */
	public function getIndexId( \IPS\Content\Searchable $object )
	{
		$databaseColumnId = $object::$databaseColumnId;
		return \strtolower( str_replace( '\\', '_', \substr( \get_class( $object ), 4 ) ) ) . '-' . $object->$databaseColumnId;
	}
	
	/**
	 * Remove item
	 *
	 * @param	\IPS\Content\Searchable	$object	Item to remove
	 * @return	void
	 */
	public function removeFromSearchIndex( \IPS\Content\Searchable $object )
	{
		try
		{
			$class = \get_class( $object );
			$idColumn = $class::$databaseColumnId;

			$this->directIndexRemoval( $class, $object->$idColumn );

			if ( !( $object instanceof \IPS\Content\Item ) )
			{
				$this->rebuildMetaCounts( $object->item() );
			}		
		}
		catch ( \IPS\Http\Request\Exception $e )
		{
			\IPS\Log::log( $e, 'elasticsearch' );
		}
	}

	/**
	 * Direct removal from the search index - only used when we don't need to perform ancillary cleanup (i.e. orphaned data)
	 *
	 * @param	string	$class	Class
	 * @param	int		$id		ID
	 * @return	void
	 */
	public function directIndexRemoval( $class, $id )
	{
		try
		{
			$indexId = \strtolower( str_replace( '\\', '_', \substr( $class, 4 ) ) ) . '-' . $id;
			$this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/' . $indexId )->request()->delete();
			
			if ( is_subclass_of( $class, 'IPS\Content\Item' ) )
			{				
				if ( isset( $class::$commentClass ) )
				{
					$commentClass = $class::$commentClass;
					$response = $this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/_delete_by_query' )->setQueryString( array( 'conflicts' => 'proceed', 'wait_for_completion' => 'false' ) )->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->post( json_encode( array(
						'query'	=> array(
							'bool' => array(
								'must' => array(
									array(
										'term'	=> array(
											'index_class' => $commentClass
										)
									),
									array(
										'term'	=> array(
											'index_item_id' => $id
										)
									),
								)
							)
									
						)
					) ) );
				}
				if ( isset( $class::$reviewClass ) )
				{
					$reviewClass = $class::$reviewClass;
					$this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/_delete_by_query' )->setQueryString( array( 'conflicts' => 'proceed', 'wait_for_completion' => 'false' ) )->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->post( json_encode( array(
						'query'	=> array(
							'bool' => array(
								'must' => array(
									array(
										'term'	=> array(
											'index_class' => $reviewClass
										)
									),
									array(
										'term'	=> array(
											'index_item_id' => $id
										)
									),
								)
							)
									
						)
					) ) );
				}
			}	
		}
		catch ( \IPS\Http\Request\Exception $e )
		{
			\IPS\Log::log( $e, 'elasticsearch' );
		}
	}
	
	/**
	 * Removes all content for a classs
	 *
	 * @param	string		$class 	The class
	 * @param	int|NULL	$containerId		The container ID to delete, or NULL
	 * @param	int|NULL	$authorId			The author ID to delete, or NULL
	 * @return	void
	 */
	public function removeClassFromSearchIndex( $class, $containerId=NULL, $authorId=NULL )
	{
		try
		{
			if ( $containerId or $authorId )
			{
				$query = array(
					'bool'	=> array(
						'must'	=> array(
							array(
								'term'	=> array(
									'index_class' => $class
								)
							)
						)
					)
				);
				
				if ( $containerId )
				{
					$query['bool']['must'][] = array(
						'term'	=> array(
							'index_container_id' => $containerId
						)
					);
				}
				
				if ( $authorId )
				{
					$query['bool']['must'][] = array(
						'term'	=> array(
							'index_author' => $authorId
						)
					);
				}				
				
				$this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/_delete_by_query' )->setQueryString( array( 'conflicts' => 'proceed', 'wait_for_completion' => 'false' ) )->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->post( json_encode( array(
					'query'	=> $query
				) ) );
			}
			else
			{
				$this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/_delete_by_query' )->setQueryString( array( 'conflicts' => 'proceed', 'wait_for_completion' => 'false' ) )->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->post( json_encode( array(
					'query'	=> array(
						'term'	=> array(
							'index_class' => $class
						)
					)
				) ) );
			}
		}
		catch ( \IPS\Http\Request\Exception $e )
		{
			\IPS\Log::log( $e, 'elasticsearch' );
		}
	}
	
	/**
	 * Mass Update (when permissions change, for example)
	 *
	 * @param	string				$class 						The class
	 * @param	int|NULL			$containerId				The container ID to update, or NULL
	 * @param	int|NULL			$itemId						The item ID to update, or NULL
	 * @param	string|NULL			$newPermissions				New permissions (if applicable)
	 * @param	int|NULL			$newHiddenStatus			New hidden status (if applicable) special value 2 can be used to indicate hidden only by parent
	 * @param	int|NULL			$newContainer				New container ID (if applicable)
	 * @param	int|NULL			$authorId					The author ID to update, or NULL
	 * @param	int|NULL			$newItemId					The new item ID (if applicable)
	 * @param	int|NULL			$newItemAuthorId			The new item author ID (if applicable)
	 * @param	bool				$addAuthorToPermissions		If true, the index_author_id will be added to $newPermissions - used when changing the permissions for a node which allows access only to author's items
	 * @return	void
	 */
	public function massUpdate( $class, $containerId = NULL, $itemId = NULL, $newPermissions = NULL, $newHiddenStatus = NULL, $newContainer = NULL, $authorId = NULL, $newItemId = NULL, $newItemAuthorId = NULL, $addAuthorToPermissions = FALSE )
	{
		try
		{
			$conditions = array();
			$conditions['must'][] = array(
				'term'	=> array(
					'index_class' => $class
				)
			);
			if ( $containerId !== NULL )
			{
				$conditions['must'][] = array(
					'term'	=> array(
						'index_container_id' => $containerId
					)
				);
			}
			if ( $itemId !== NULL )
			{
				$conditions['must'][] = array(
					'term'	=> array(
						'index_item_id' => $itemId
					)
				);
			}
			if ( $authorId !== NULL )
			{
				$conditions['must'][] = array(
					'term'	=> array(
						'index_item_author' => $authorId
					)
				);
			}
			
			$updates	= array();
			$params		= array();
			if ( $newPermissions !== NULL )
			{
				$updates[] = "ctx._source.index_permissions = params.params_indexpermissions;";
				$params['params_indexpermissions']	= explode( ',', $newPermissions );
			}
			if ( $newContainer )
			{
				$updates[] = "ctx._source.index_container_id = params.params_indexcontainer;";
				$params['params_indexcontainer']	= \intval( $newContainer );
				
				if ( $itemClass = ( \in_array( 'IPS\Content\Item', class_parents( $class ) ) ? $class : $class::$itemClass ) and $containerClass = $itemClass::$containerNodeClass and \IPS\IPS::classUsesTrait( $containerClass, 'IPS\Content\ClubContainer' ) and $clubIdColumn = $containerClass::clubIdColumn() )
				{
					try
					{
						$updates[] = "ctx._source.index_club_id = params.params_indexclub;";
						$params['params_indexclub']	= \intval( $containerClass::load( $newContainer )->$clubIdColumn );
					}
					catch ( \OutOfRangeException $e )
					{
						$updates[] = "ctx._source.index_club_id = params.params_indexclub;";
						$params['params_indexclub']	= null;
					}
				}
			}
			if ( $newItemId )
			{
				$updates[] = "ctx._source.index_item_id = params.params_indexitem;";
				$params['params_indexitem']	= \intval( $newItemId );
			}
			if ( $newItemAuthorId )
			{
				$updates[] = "ctx._source.index_item_author = params.params_indexauthor;";
				$params['params_indexauthor']	= \intval( $newItemAuthorId );
			}
			
			if ( \count( $updates ) )
			{
				$this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/_update_by_query' )->setQueryString( array( 'conflicts' => 'proceed', 'wait_for_completion' => 'false', 'scroll_size' => \IPS\Settings::i()->search_index_maxresults ) )->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->post( json_encode( array(
					'script'	=> array(
						'source'	=> implode( ' ', $updates ),
						'lang'		=> 'painless',
						'params'	=> $params
					),
					'query'		=> array(
						'bool'		=> $conditions
					)
				) ) );
			}
			
			if ( $addAuthorToPermissions )
			{
				$addAuthorToPermissionsConditions = $conditions;
				$addAuthorToPermissionsConditions['must_not'][] = array(
					'term'	=> array(
						'index_author' => 0
					)
				);
				
				$this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/_update_by_query' )->setQueryString( array( 'conflicts' => 'proceed', 'wait_for_completion' => 'false', 'scroll_size' => \IPS\Settings::i()->search_index_maxresults ) )->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->post( json_encode( array(
					'script'	=> array(
						'source'	=> "ctx._source.index_permissions.add( 'm.' + ctx._source.index_author );",
						'lang'		=> 'painless'
					),
					'query'		=> array(
						'bool'		=> $conditions
					)
				) ) );
			}
			
			if ( $newHiddenStatus !== NULL )
			{
				if ( $newHiddenStatus === 2 )
				{
					$conditions['must'][] = array(
						'term'	=> array(
							'index_hidden' => 0
						)
					);
				}
				else
				{
					$conditions['must'][] = array(
						'term'	=> array(
							'index_hidden' => 2
						)
					);
				}
							
				$this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/_update_by_query' )->setQueryString( array( 'conflicts' => 'proceed', 'wait_for_completion' => 'false', 'scroll_size' => \IPS\Settings::i()->search_index_maxresults ) )->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->post( json_encode( array(
					'script'	=> array(
						'source'	=> "ctx._source.index_hidden = params.newHiddenStatus;",
						'lang'		=> 'painless',
						'params'	=> array( 'newHiddenStatus' => \intval( $newHiddenStatus ) )
					),
					'query'		=> array(
						'bool'		=> $conditions
					)
				) ) );
			}
		}
		catch ( \IPS\Http\Request\Exception $e )
		{
			\IPS\Log::log( $e, 'elasticsearch' );
		}		
	}
	
	/**
	 * Convert an arbitary number of elasticsearch conditions into a query
	 *
	 * @param	array	$conditions	Conditions
	 * @return	array
	 */
	public static function convertConditionsToQuery( $conditions )
	{
		if ( \count( $conditions ) == 1 )
		{
			return $conditions[0];
		}
		elseif ( \count( $conditions ) == 0 )
		{
			return array( 'match_all' => new \StdClass );
		}
		else
		{
			return array(
				'bool' => array(
					'must' => array( $conditions )
				)
			);
		}
	}
	
	/**
	 * Update data for the first and last comment after a merge
	 * Sets index_is_last_comment on the last comment, and, if this is an item where the first comment is indexed rather than the item, sets index_title and index_tags on the first comment
	 *
	 * @param	\IPS\Content\Item	$item	The item
	 * @return	void
	 */
	public function rebuildAfterMerge( \IPS\Content\Item $item )
	{
		if ( $item::$commentClass )
		{
			$firstComment = $item->comments( 1, 0, 'date', 'asc', NULL, FALSE, NULL, NULL, TRUE, FALSE, FALSE );
			$lastComment = $item->comments( 1, 0, 'date', 'desc', NULL, FALSE, NULL, NULL, TRUE, FALSE, FALSE );
			
			$idColumn = $item::$databaseColumnId;
			$update = array( 'index_is_last_comment' => false );
			if ( $item::$firstCommentRequired )
			{
				$update['index_title'] = NULL;
			}
			
			try
			{
				$this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/' . $this->getIndexId( $item ) . '/_update' )->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->post( json_encode( array(
					'doc'	=> $update
				) ) );
				
				if ( $firstComment )
				{
					$this->index( $firstComment );
				}
				if ( $lastComment )
				{
					$this->index( $lastComment );
				}
			}
			catch ( \IPS\Http\Request\Exception $e )
			{
				\IPS\Log::log( $e, 'elasticsearch' );
			}			
		}
	}
	
	/**
	 * Prune search index
	 *
	 * @param	\IPS\DateTime|NULL	$cutoff	The date to delete index records from, or NULL to delete all
	 * @return	void
	 */
	public function prune( \IPS\DateTime $cutoff = NULL )
	{
		if ( $cutoff )
		{			
			try
			{
				$this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/_delete_by_query' )->setQueryString( array( 'conflicts' => 'proceed', 'wait_for_completion' => 'false' ) )->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->post( json_encode( array(
					'query'	=> array(
						'range'	=> array(
							'index_date_updated' => array(
								'lt' => $cutoff->getTimestamp()
							)
						)
					)
				) ) );
			}
			catch ( \IPS\Http\Request\Exception $e )
			{
				\IPS\Log::log( $e, 'elasticsearch' );
			}
		}
		else
		{
			$this->init();
		}		
	}
	
	/**
	 * Reset the last comment flag in any given class/index_item_id
	 *
	 * @param	array				$classes					The classes (when first post is required, this is typically just \IPS\forums\Topic\Post but for others, it will be both item and comment classes)
	 * @param	int|NULL			$indexItemId				The index item ID
	 * @return 	void
	 */
	public function resetLastComment( $classes, $indexItemId )
	{
		try
		{			
			/* Remove the flag */
			$this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/_update_by_query' )->setQueryString( array( 'conflicts' => 'proceed', 'wait_for_completion' => 'true', 'scroll_size' => \IPS\Settings::i()->search_index_maxresults ) )->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->post( json_encode( array(
				'script'	=> array(
					'source'	=> "ctx._source.index_is_last_comment = false;",
					'lang'		=> 'painless'
				),
				'query'		=> array(
					'bool'		=> array(
						'must'		=> array(
							array(
								'terms'	=> array(
									'index_class' => $classes
								)
							),
							array(
								'term'	=> array(
									'index_item_id' => $indexItemId
								)
							),
						)
					)
				)
			) ) );
			
			/* Get the latest comment */
			$itemClass = NULL;
			foreach ( $classes as $class )
			{
				if ( \in_array( 'IPS\Content\Item', class_parents( $class ) ) )
				{
					$itemClass = $class;
					break;
				}
				elseif ( isset( $class::$itemClass ) )
				{
					$itemClass = $class::$itemClass;
				}
			}
			if ( $itemClass )
			{
				try
				{
					$item = $itemClass::load( $indexItemId );
					
					if ( $lastComment = $item->comments( 1, 0, 'date', 'desc', NULL, FALSE ) )
					{
						/* Set that it is the latest comment */
						$r = $this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/' . $this->getIndexId( $lastComment ) . '/_update' )->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->post( json_encode( array(
							'doc'	=> array(
								'index_is_last_comment' => true
							)
						) ) );
						
						/* And set the updated time on the main item (done as _update_by_query because it might not exist if the first comment is required) */
						$indexDataForLastComment = $this->indexData( $lastComment );
						$this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/_update_by_query' )->setQueryString( array( 'conflicts' => 'proceed', 'wait_for_completion' => 'false', 'scroll_size' => \IPS\Settings::i()->search_index_maxresults ) )->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->post( json_encode( array(
							'script'	=> array(
								'source'	=> "ctx._source.index_date_updated = params.dateUpdated; ctx._source.index_date_commented = params.dateCommented;",
								'lang'		=> 'painless',
								'params'	=> array(
									'dateUpdated'	=> \intval( $indexDataForLastComment['index_date_updated'] ),
									'dateCommented'	=> \intval( $indexDataForLastComment['index_date_commented'] )
								)
							),
							'query'		=> array(
								'bool'		=> array(
									'must'		=> array(
										array(
											'terms'	=> array(
												'index_class' => $classes
											)
										),
										array(
											'term'	=> array(
												'index_item_id' => $indexItemId
											)
										),
										array(
											'term'	=> array(
												'index_object_id' => $indexItemId
											)
										),
									)
								)
							)
						) ) );
					}
				}
				catch ( \OutOfRangeException $e ) { }
			}
		}
		catch ( \Exception $e )
		{
			\IPS\Log::log( $e, 'elasticsearch' );
		}
	}
	
	/**
	 * Given a list of item index IDs, return the ones that a given member has participated in
	 *
	 * @param	array		$itemIndexIds	Item index IDs
	 * @param	\IPS\Member	$member			The member
	 * @return 	array
	 */
	public function iPostedIn( array $itemIndexIds, \IPS\Member $member )
	{
		try
		{
			/* Set the query */
			$query = array(
				'bool'	=> array(
					'filter' => array(
						array(
							'terms'	=> array(
								'index_item_index_id' => $itemIndexIds
							),
						),
						array(
							'term'	=> array(
								'index_author' => $member->member_id
							)
						)
					)
				)
			);
			
			/* Get the count */
			$count = $this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/_search' )->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->get( json_encode( array(
				'size'	=> 0,
				'query'	=> $query
			) ) )->decodeJson();
			if ( !$count['hits']['total'] )
			{
				return array();
			} 
			
			/* Now get the unique item ids */
			$results = $this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/_search' )->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->get( json_encode( array(
				'aggs'	=> array(
					'itemIds' => array(
						'terms'	=> array(
							'field'	=> 'index_item_index_id',
							'size'	=> $count['hits']['total']
						)
					)
				),
				'query'	=> $query
			) ) )->decodeJson();
		}
		catch ( \Exception $e )
		{
			\IPS\Log::log( $e, 'elasticsearch' );
			return array();
		}
		
		$iPostedIn = array();
		foreach ( $results['aggregations']['itemIds']['buckets'] as $result )
		{
			if ( $result['doc_count'] )
			{
				$iPostedIn[] = $result['key'];
			}
		}
		
		return $iPostedIn;
	}
	
	/**
	 * Given a list of "index_class_type_id_hash"s, return the ones that a given member has permission to view
	 *
	 * @param	array		$hashes			Item index hashes
	 * @param	\IPS\Member	$member			The member
	 * @param	int|NULL		$limit			Number of results to return
	 * @return 	array
	 */
	public function hashesWithPermission( array $hashes, \IPS\Member $member, $limit = NULL )
	{
		try
		{
			$results = $this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/_search' )->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->get( json_encode( array(
				'query'	=> array(
					'bool'	=> array(
						'filter' => array(
							array(
								'terms' => array(
									'index_class_type_id_hash' => $hashes
								)
							),
							array(
								'terms' => array(
									'index_permissions' => array_merge( $member->permissionArray(), array( '*' ) )
								)
							),
							array(
								'term'	=> array(
									'index_hidden' => 0
								)
							)
						)
					)
				),
				'size'	=> $limit ?: 10 // If we define a limit, use that, otherwise default to 10 which is ElasticSearch's default
			) ) )->decodeJson();
		}
		catch ( \Exception $e )
		{
			\IPS\Log::log( $e, 'elasticsearch' );
			return array();
		}
		
		$hashesWithPermission = array();
		foreach ( $results['hits']['hits'] as $result )
		{
			$hashesWithPermission[ $result['_source']['index_class_type_id_hash'] ] = $result['_source']['index_class_type_id_hash'];
		}
		
		return $hashesWithPermission;
	}
	
	/**
	 * Get timestamp of oldest thing in index
	 *
	 * @return 	int|null
	 */
	public function firstIndexDate()
	{
		try
		{
			$results = $this->url->setPath( $this->url->data[ \IPS\Http\Url::COMPONENT_PATH ] . '/content/_search' )->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->get( json_encode( array(
				'size'	=> 1,
				'sort'	=> array( array( 'index_date_updated' => 'asc' ) )
			) ) )->decodeJson();
			
			if ( isset( $results['hits']['hits'][0] ) )
			{
				return $results['hits']['hits'][0]['_source']['index_date_updated'];
			}
			
			return NULL;
		}
		catch ( \Exception $e )
		{
			\IPS\Log::log( $e, 'elasticsearch' );
			return NULL;
		}
	}
	
	/**
	 * Convert terms into stemmed terms for the highlighting JS
	 *
	 * @param	array	$terms	Terms
	 * @return	array
	 */
	public function stemmedTerms( $terms )
	{
		$analyzer = \IPS\Settings::i()->search_elastic_analyzer;
		if ( $analyzer === 'custom' )
		{
			$analysisSettings = json_decode( '{' . \IPS\Settings::i()->search_elastic_custom_analyzer . '}', TRUE );
			$analyzer = key( $analysisSettings['analyzer'] );
		}
		
		try
		{
			$results = $this->url->setPath( '/_analyze' )->request()->setHeaders( array( 'Content-Type' => 'application/json' ) )->get( json_encode( array(
				'analyzer'	=> $analyzer,
				'text'		=> implode( ' ', $terms )
			) ) )->decodeJson();
			
			if ( isset( $results['tokens'] ) )
			{
				$stemmed = $terms;
				foreach ( $results['tokens'] as $token )
				{
					$stemmed[] = $token['token'];
				}
				return $stemmed;
			}
			
			return $terms;
		}
		catch ( \Exception $e )
		{
			return $terms;
		}
	}
	
	/**
	 * Supports filtering by views?
	 *
	 * @return	bool
	 */
	public function supportViewFiltering()
	{
		return FALSE;
	}
}