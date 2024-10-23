<?php

namespace Aepro\Modules\QueryControl;

use Aepro\Base\ModuleBase;

class Module extends ModuleBase {

	public function register_ajax_actions( $ajax_manager ) {
		$ajax_manager->register_ajax_action( 'aep_query_value_titles', [ $this, 'query_value_titles' ] );
		$ajax_manager->register_ajax_action( 'aep_query_autocomplete_data', [ $this, 'query_autocomplete_data' ] );
	}

	public function query_value_titles( $request ) {
		if ( empty( $request['query_type'] ) ) {
			throw new \Exception( 'Bad Request' );
		}

		$query_type = $request['query_type'];

		$query_type = str_replace( '-', ' ', $query_type );
		$query_type = str_replace( ' ', '', ucwords( $query_type ) );

		$type_class = 'Aepro\Modules\QueryControl\Types\\' . $query_type;

		$type    = $type_class::instance();
		$results = $type->get_value_titles( $request );

		return $results;
	}

	public function query_autocomplete_data( $request ) {
		if ( empty( $request['query_type'] ) || empty( $request['q'] ) ) {
			throw new \Exception( 'Bad Request' );
		}

		$query_type = $request['query_type'];

		$query_type = str_replace( '-', ' ', $query_type );
		$query_type = str_replace( ' ', '', ucwords( $query_type ) );

		$type_class = 'Aepro\Modules\QueryControl\Types\\' . $query_type;
		$type       = $type_class::instance();
		$results    = $type->get_autocomplete_values( $request );

		return [
			'results' => $results,
		];
	}
}
