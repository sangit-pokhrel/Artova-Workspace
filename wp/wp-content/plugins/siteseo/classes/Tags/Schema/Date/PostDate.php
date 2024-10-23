<?php

namespace SiteSEO\Tags\Schema\Date;

if ( ! defined('ABSPATH')) {
	exit;
}

use SiteSEO\Models\GetTagValue;

class PostDate implements GetTagValue {
	const NAME = 'schema_post_date';

	public function getValue($args = null) {
		$context = isset($args[0]) ? $args[0] : null;
		$value   = '';

		if (isset($context['post'])) {
			$value = get_the_date('c', $context['post']->ID);
		}

		return apply_filters('siteseo_get_tag_schema_post_date_value', $value, $context);
	}
}
