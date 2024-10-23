<?php

namespace Aepro\Modules\QueryControl\Types;

use Aepro\Modules\QueryControl\TypeBase;

class User extends TypeBase {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function get_name() {
		return 'user';
	}

	public function get_autocomplete_values( array $request ) {

		$args = [
			'search' => '*' . $request['q'] . '*',
		];

		$query = new \WP_User_Query( $args );

		foreach ( $query->get_results() as $user ) {
			$username  = $user->data->display_name;
			$user_id   = $user->ID;
			$results[] = [
				'id'   => $user_id,
				'text' => $username,
			];
		}

		return $results;
	}

	public function get_value_titles( array $request ) {
		$ids     = (array) $request['id'];
		$results = [];

		foreach ( $ids as $id ) {
			$user                 = get_user_by( 'id', $id );
			$results[ $user->ID ] = $user->display_name;
		}

		return $results;
	}
}
