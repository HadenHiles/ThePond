<?php
/**
 * @brief		Support profiles in sitemaps
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		29 Aug 2013
 */

namespace IPS\core\extensions\core\Sitemap;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Support profiles in sitemaps
 */
class _Profiles
{
	/**
	 * @brief	Recommended Settings
	 */
	public $recommendedSettings = array(
		'sitemap_profiles_include'	=> true,
		'sitemap_profiles_count'	=> -1,
		'sitemap_profiles_priority'	=> 0.6,
		'sitemap_profiles_content'	=> 10,
	);
	
	/**
	 * Settings for ACP configuration to the form
	 *
	 * @return	array
	 */
	public function settings()
	{
		return array(
			'sitemap_profiles_include'	=> new \IPS\Helpers\Form\YesNo( "sitemap_profiles_include", \IPS\Settings::i()->sitemap_profiles_count != 0, FALSE, array( 'togglesOn' => array( "sitemap_profiles_count", "sitemap_profiles_priority", "sitemap_profiles_content" ) ), NULL, NULL, NULL, "sitemap_profiles_include" ),
			'sitemap_profiles_count'	=> new \IPS\Helpers\Form\Number( 'sitemap_profiles_count', \IPS\Settings::i()->sitemap_profiles_count, FALSE, array( 'min' => '-1', 'unlimited' => '-1' ), NULL, NULL, NULL, 'sitemap_profiles_count' ),
			'sitemap_profiles_content'	=> new \IPS\Helpers\Form\Number( 'sitemap_profiles_content', \IPS\Settings::i()->sitemap_profiles_content, FALSE, array( 'min' => '-1', 'unlimited' => '-1', 'unlimitedLang' => 'sitemap_profiles_content_unlimited' ), NULL, NULL, \IPS\Member::loggedIn()->language()->addToStack('sitemap_profiles_content_suffix'), 'sitemap_profiles_content' ),
			'sitemap_profiles_priority'	=> new \IPS\Helpers\Form\Select( 'sitemap_profiles_priority', \IPS\Settings::i()->sitemap_profiles_priority, FALSE, array( 'options' => \IPS\Sitemap::$priorities, 'unlimited' => '-1', 'unlimitedLang' => 'sitemap_dont_include' ), NULL, NULL, NULL, 'sitemap_profiles_priority' )
		);
	}

	/**
	 * Save settings for ACP configuration
	 *
	 * @param	array	$values	Values
	 * @return	void
	 */
	public function saveSettings( $values )
	{
		if ( $values['sitemap_configuration_info'] )
		{
			\IPS\Settings::i()->changeValues( array( 'sitemap_profiles_count' => $this->recommendedSettings['sitemap_profiles_count'], 'sitemap_profiles_content' => $this->recommendedSettings['sitemap_profiles_content'], 'sitemap_profiles_priority' => $this->recommendedSettings['sitemap_profiles_priority'] ) );
		}
		else
		{
			\IPS\Settings::i()->changeValues( array( 'sitemap_profiles_count' => $values['sitemap_profiles_include'] ? $values['sitemap_profiles_count'] : 0, 'sitemap_profiles_content' => $values['sitemap_profiles_content'], 'sitemap_profiles_priority' => $values['sitemap_profiles_priority'] ) );
		}
	}

	/**
	 * Get the sitemap filename(s)
	 *
	 * @return	array
	 */
	public function getFilenames()
	{
		/* First, we need to make sure we are actually including profiles in the sitemap */
		if ( \IPS\Settings::i()->sitemap_profiles_count == 0 )
		{
			return array();
		}
		
		/* Then, make sure the module is enabled. */
		$profileModule = \IPS\Application\Module::get( 'core', 'members', 'front' );
		if( !$profileModule->visible )
		{
			return array();
		}
		
		/* And that Guests can view the profile module */
		if( !$profileModule->can( 'view', new \IPS\Member ) )
		{
			return array();
		}
		
		/* Get a count of how many files we'll be generating */
		$count = \IPS\Db::i()->select( 'COUNT(*)', 'core_members' )->first();
		$count = ( $count > \IPS\Sitemap::MAX_PER_FILE ) ? ceil( $count / \IPS\Sitemap::MAX_PER_FILE ) : 1;
		
		/* If we are not storing all profiles, limit the count */
		if ( \IPS\Settings::i()->sitemap_profiles_count > -1 AND $count > \IPS\Settings::i()->sitemap_profiles_count )
		{
			$count = \IPS\Settings::i()->sitemap_profiles_count;
		}

		/* Generate the file names */
		$files	= array();
		for( $i=1; $i <= $count; $i++ )
		{
			$files[]	= "sitemap_profiles_" . $i;
		}

		/* Return */
		return $files;
	}

	/**
	 * Generate the sitemap
	 *
	 * @param	string			$filename	The sitemap file to build (should be one returned from getFilenames())
	 * @param	\IPS\Sitemap	$sitemap	Sitemap object reference
	 * @return	void
	 */
	public function generateSitemap( $filename, $sitemap )
	{
		/* Which file are we building? */
		$_info		= explode( '_', $filename );
		$index		= array_pop( $_info ) - 1;
		$entries	= array();
		$start		= \IPS\Sitemap::MAX_PER_FILE * $index;
		$limit		= \IPS\Sitemap::MAX_PER_FILE;

		/* Have we already maxed out?  We shouldn't really hit this because getFilenames() already factors in the max, but best to check */
		if( \IPS\Settings::i()->sitemap_profiles_count > -1 AND \IPS\Settings::i()->sitemap_profiles_count < $start )
		{
			return;
		}

		/* Do we need less than 10k? */
		if( \IPS\Settings::i()->sitemap_profiles_count > -1 AND \IPS\Settings::i()->sitemap_profiles_count < $start + $limit )
		{
			$limit	= \IPS\Settings::i()->sitemap_profiles_count - $start;
		}
		
		$where = NULL;
		
		if ( \IPS\Settings::i()->sitemap_profiles_content > 0 )
		{
			$where = array( array( 'member_posts >= ?', \IPS\Settings::i()->sitemap_profiles_content ) );
		}
		
		/* Retrieve the members */
		foreach( \IPS\Db::i()->select( '*', 'core_members', $where, 'member_id', array( $start, $limit ) ) as $row )
		{
			$row['members_seo_name'] = $row['members_seo_name'] ?: \IPS\Http\Url\Friendly::seoTitle( $row['name'] );

			$entry	= array(
				'url'	=> \IPS\Http\Url::internal( "app=core&module=members&controller=profile&id={$row['member_id']}", 'front', 'profile', $row['members_seo_name'] )
			);

			if( \IPS\Settings::i()->sitemap_profiles_priority > 0 )
			{
				$entry['priority']	= \IPS\Settings::i()->sitemap_profiles_priority;
			}

			$entries[]	= $entry;
		}
		
		/* Build the file */
		$sitemap->buildSitemapFile( $filename, $entries );
	}
}