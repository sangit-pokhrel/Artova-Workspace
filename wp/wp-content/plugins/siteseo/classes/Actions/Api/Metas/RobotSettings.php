<?php

namespace SiteSEO\Actions\Api\Metas;

if (! defined('ABSPATH')) {
	exit;
}

use SiteSEO\Core\Hooks\ExecuteHooks;
use SiteSEO\Helpers\Metas\RobotSettings as MetaRobotSettingsHelper;

class RobotSettings implements ExecuteHooks
{
	public function hooks()
	{
		add_action('rest_api_init', [$this, 'register']);
	}

	/**
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function register()
	{
		register_rest_route('siteseo/v1', '/posts/(?P<id>\d+)/meta-robot-settings', [
			'methods'			 => 'GET',
			'callback'			=> [$this, 'processGet'],
			'args'				=> [
				'id' => [
					'validate_callback' => function ($param, $request, $key) {
						return is_numeric($param);
					},
				],
			],
			'permission_callback' => '__return_true',
		]);

		register_rest_route('siteseo/v1', '/posts/(?P<id>\d+)/meta-robot-settings', [
			'methods'			 => 'PUT',
			'callback'			=> [$this, 'processPut'],
			'args'				=> [
				'id' => [
					'validate_callback' => function ($param, $request, $key) {
						return is_numeric($param);
					},
				],
			],
			'permission_callback' => function ($request) {
				$nonce = $request->get_header('x-wp-nonce');
				if (! wp_verify_nonce($nonce, 'wp_rest')) {
					return false;
				}

				if(!current_user_can('edit_posts')){
					return false;
				}

				return true;
			},
		]);
	}

	/**
	 * @since 5.0.0
	 */
	public function processPut(\WP_REST_Request $request)
	{
		$id	 = $request->get_param('id');

		$metas = MetaRobotSettingsHelper::getMetaKeys($id);

		$params = $request->get_params();

		try {


			//Elementor sync
			$elementor = get_post_meta($id, '_elementor_page_settings', true);

			foreach ($metas as $key => $value) {
				if (! isset($params[$value['key']])) {
					continue;
				}
				$item = $params[$value['key']];
				if(in_array($value['type'], ['input', 'textarea'])){
					$item = esc_html($item);
				}

				if(!empty($item)){
					update_post_meta($id, $value['key'], $item);
				}
				else{
					delete_post_meta($id, $value['key']);
				}

				if (! empty($elementor)) {
					$elementor[$value['key']] = $item;
				}
			}

			if(!empty($elementor)){
				update_post_meta($id, '_elementor_page_settings', $elementor);
			}

			return new \WP_REST_Response([
				'code' => 'success',
			]);
		} catch (\Exception $e) {
			return new \WP_REST_Response([
				'code'		 => 'error',
				'code_message' => 'execution_failed',
			], 403);
		}
	}

	/**
	 * @since 5.0.0
	 */
	public function processGet(\WP_REST_Request $request)
	{
		$id	= $request->get_param('id');

		$metas = MetaRobotSettingsHelper::getMetaKeys($id);

		$data = [];
		foreach ($metas as $key => $value) {
			if (isset($value['use_default']) && $value['use_default']) {
				$data[] = array_merge($value, [
					'can_modify' => false,
					'value'	  => $value['default'],
				]);
			} else {
				$result = get_post_meta($id, $value['key'], true);
				$data[] = array_merge($value, [
					'can_modify' => true,
					'value'	  => 'checkbox' === $value['type'] ? ($result === true || $result === 'yes' ? 'yes' : '') : $result,
				]);
			}
		}

		return new \WP_REST_Response($data);
	}
}
