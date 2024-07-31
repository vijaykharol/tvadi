<?php

namespace Pods;

use PodsMeta;

/**
 * Class Service_Provider
 *
 * @since 2.8.0
 */
class Service_Provider extends \Pods\Service_Provider_Base {

	/**
	 * Registers the classes and functionality needed.
	 *
	 * @since 2.8.0
	 */
	public function register() {
		$this->container->singleton( Config_Handler::class, Config_Handler::class );
		$this->container->singleton( Permissions::class, Permissions::class );
		$this->container->singleton( Pod_Manager::class, Pod_Manager::class );
		$this->container->singleton( Static_Cache::class, Static_Cache::class );

		$this->container->bind( Data\Conditional_Logic::class, Data\Conditional_Logic::class );
		$this->container->singleton( Data\Map_Field_Values::class, Data\Map_Field_Values::class );

		$this->container->singleton( Theme\WP_Query_Integration::class, Theme\WP_Query_Integration::class );

		$this->container->singleton( Tools\Repair::class, Tools\Repair::class );
		$this->container->singleton( Tools\Reset::class, Tools\Reset::class );

		$this->container->singleton( WP\Bindings::class, WP\Bindings::class );
		$this->container->singleton( WP\Meta::class, WP\Meta::class );
		$this->container->singleton( WP\Revisions::class, WP\Revisions::class );

		$this->container->singleton( WP\UI\Taxonomy_Filter::class, WP\UI\Taxonomy_Filter::class );

		$this->container->singleton( PodsMeta::class, PodsMeta::class );

		$this->hooks();
	}

	/**
	 * Hooks all the methods and actions the class needs.
	 *
	 * @since 2.8.0
	 */
	protected function hooks() {
		add_action( 'init', $this->container->callback( Theme\WP_Query_Integration::class, 'hook' ), 20 );

		add_action( 'init', $this->container->callback( WP\Bindings::class, 'hook' ), 20 );
		add_action( 'init', $this->container->callback( WP\Meta::class, 'hook' ), 20 );
		add_action( 'init', $this->container->callback( WP\Revisions::class, 'hook' ), 20 );

		add_action( 'init', $this->container->callback( WP\UI\Taxonomy_Filter::class, 'hook' ), 20 );
	}
}
