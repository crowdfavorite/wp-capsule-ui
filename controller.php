<?php
/**
 * The controler
 *
 * @package capsule-ui
 */

/**
 * The capsule controler.
 *
 * @return void
 */
function capsule_controller() {
	$capsule_action_get  = filter_input( INPUT_GET, 'capsule_action' );
	$capsule_action_post = filter_input( INPUT_POST, 'capsule_action' );
	$api_key             = filter_input( INPUT_GET, 'api_key' );
	$post_id_get         = filter_input( INPUT_GET, 'post_id' );
	$post_id_post        = filter_input( INPUT_POST, 'post_id' );
	$content             = filter_input( INPUT_POST, 'content' );
	$q_get               = filter_input( INPUT_GET, 'q' );

	if ( ! empty( $capsule_action_get ) ) {
		if ( 'search' === $capsule_action_get ) {
			global $wpdb;
			if ( ! empty( $q_get ) && in_array( $q_get[0], array( '@', '#', '`' ), true ) ) {
				$prefix = $q_get[0];
				switch ( $prefix ) {
					case '@':
						$taxonomy = 'projects';
						break;
					case '#':
						$taxonomy = 'post_tag';
						break;
					case '`':
						$taxonomy = 'code';
						break;
					default:
						$taxonomy = null;
						break;
				}

				$term_name = stripslashes( substr( $q_get, 1, strlen( $q_get ) ) );
				if ( ! strlen( $term_name ) < 1 ) {
					// Taken from wp_ajax_ajax_tag_search().
					$results = $wpdb->get_col($wpdb->prepare("
						SELECT t.name 
						FROM $wpdb->term_taxonomy AS tt 
						INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id 
						WHERE tt.taxonomy = %s AND t.name LIKE (%s)
						AND tt.count > 0
					", $taxonomy, $wpdb->esc_like( $term_name ) . '%'));
					$html = '';
					foreach ( $results as $result ) {
						$html .= $prefix . $result . "\n";
					}
					echo $html;
				}
			}
			die();
		}// End if().

		if ( strpos( $capsule_action_get, 'queue_' ) === 0 &&
			stripslashes( $api_key ) === capsule_queue_api_key() ) {
			switch ( $capsule_action_get ) {
				case 'queue_run':
					capsule_queue_run();
					break;
				case 'queue_post_to_server':
					// Required params: post_id.
					if ( ! empty( $post_id_get ) ) {
						$post_id = intval( $post_id_get );
						if ( ! empty( $post_id ) ) {
							capsule_queue_post_to_server( $post_id );
						}
					}
				break;
			}
			die();
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			capsule_unauthorized_json();
		}
		switch ( $capsule_action_get ) {
			case 'post_excerpt':
			case 'post_content':
				// Required params: post_id.
				if ( isset( $post_id_get ) ) {
					$post_id = intval( $post_id_get );
					if ( ! empty( $post_id ) ) {
						global $post;
						$post = get_post( $post_id );
						setup_postdata( $post );
						$view = str_replace( 'post_', '', $capsule_action_get );
						ob_start();
						include( get_template_directory() . '/ui/views/' . $view . '.php' );
						$html     = ob_get_clean();
						$response = compact( 'html' );
						header( 'Content-type: application/json' );
						echo wp_json_encode( $response );
						die();
					}
				}
			break;
			case 'post_editor':
				// Required params: post_id.
				if ( isset( $post_id_get ) ) {
					$post_id = intval( $post_id_get );
					if ( ! empty( $post_id ) ) {
						global $post;
						$post = get_post( $post_id );
						setup_postdata( $post );
						ob_start();
						include( get_template_directory() . '/ui/views/edit.php' );
						$html     = ob_get_clean();
						$response = array(
							'html'    => $html,
							'content' => $post->post_content,
						);
						header( 'Content-type: application/json' );
						echo wp_json_encode( $response );
					}
					die();
				}
			break;
			default:
				do_action( 'capsule_controller_action_get', $capsule_action_get );
			break;
		}// End switch().
	}// End if().
	if ( ! empty( $capsule_action_post ) ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			capsule_unauthorized_json();
		}
		switch ( $capsule_action_post ) {
			case 'create_post':
				global $post;
				$post_id = wp_insert_post( array(
					'post_title'   => time(),
					'post_status'  => 'draft',
					'post_content' => '',
				), true);
				if ( is_wp_error( $post_id ) ) {
					$result   = 'error';
					$msg      = $post_id->get_error_message();
					$response = compact( 'result', 'msg' );
				} else {
					$result = 'success';
					$msg    = __( 'Post created.', 'capsule' );
					$post   = get_post( $post_id );
					setup_postdata( $post );
					ob_start();
					include( get_template_directory() . '/ui/views/edit.php' );
					$html     = ob_get_clean();
					$ymd      = get_the_time( 'Ymd', $post );
					$response = array(
						'post_id' => $post_id,
						'result'  => $result,
						'msg'     => $msg,
						'html'    => $html,
						'content' => $post->post_content,
					);
				}
				header( 'Content-type: application/json' );
				echo wp_json_encode( $response );
				die();
			break;
			case 'update_post':
				// Required params: content, post_id.
				$post_id = intval( $post_id_post );
				if ( ! $post_id ) {
					die();
				}
				$post_title = '';
				$taxonomies = array(
					'projects' => array(),
					'post_tag' => array(),
					'code'     => array(),
				);
				foreach ( $taxonomies as $tax => $terms ) {
					$terms = json_decode( stripslashes( $_POST[ $tax ] ) );
					// There is no easy WP way assign terms by name to a post on the fly
					// they must be created first and then use the slug (or ID for heirarchial).
					foreach ( $terms as $term_name ) {
						$term = get_term_by( 'name', $term_name, $tax );
						if ( ! $term ) {
							$term_data = wp_insert_term( $term_name, $tax );
							if ( ! is_wp_error( $term_data ) ) {
								$term = get_term_by( 'id', $term_data['term_id'], $tax );
								$taxonomies[ $tax ][] = $term->slug;
							}
						} else {
							$taxonomies[ $tax ][] = $term->slug;
						}
					}
					$post_title .= ' ' . implode( ' ', $terms );
				}
				// Ff the content is empty, wp_update_post fails.
				if ( empty( $content ) ) {
					$content = ' ';
				}
				$update = wp_update_post( array(
					'ID'           => $post_id,
					'post_title'   => trim( $post_title ),
					'post_content' => $content,
					'post_status'  => 'publish',
				) );
				if ( $update ) {
					foreach ( $taxonomies as $tax => $terms ) {
						wp_set_post_terms( $post_id, $terms, $tax );
					}
					$result = 'success';
					$msg    = 'Post saved.';
				} else {
					$result = 'error';
					$msg    = 'Saving post #' . $post_id . ' failed.';
				}
				$projects_html = capsule_term_list( $post_id, 'projects' );
				$tags_html     = capsule_term_list( $post_id, 'post_tag' );
				$code_html     = capsule_term_list( $post_id, 'code' );
				$response      = compact( 'post_id', 'result', 'msg', 'projects_html', 'tags_html', 'code_html' );
				header( 'Content-type: application/json' );
				echo wp_json_encode( $response );
				die();
			break;
			case 'delete_post':
				// Required params: post_id.
				$post_id = intval( $post_id_post );
				$delete  = wp_delete_post( $post_id );
				if ( false !== $delete ) {
					$post = get_post( $post_id );
					setup_postdata( $post );
					$result = 'success';
					$msg    = __( 'Post deleted', 'capsule' );
					ob_start();
					include( get_template_directory() . '/ui/views/deleted.php' );
					$html = ob_get_clean();
				} else {
					$result = 'error';
					$msg    = __( 'Post not deleted, please try again.', 'capsule' );
					$html   = '';
				}
				$response = compact( 'post_id', 'result', 'msg', 'html' );
				header( 'Content-type: application/json' );
				echo wp_json_encode( $response );
				die();
			break;
			case 'undelete_post':
				// required params:
				// - post_id
				global $post;
				$post_id = intval( $post_id_post );
				$post    = wp_untrash_post( $post_id );
				if ( $post != false ) {
					$post = get_post( $post_id );
					setup_postdata( $post );
					$result = 'success';
					$msg    = __( 'Post recovered from trash.', 'capsule' );
					ob_start();
					include( get_template_directory() . '/ui/views/excerpt.php' );
					$html = ob_get_clean();
				} else {
					$result = 'error';
					$msg    = __( 'Post not restored, please try again.', 'capsule' );
					$html   = '';
				}
				$response = compact( 'post_id', 'result', 'msg', 'html' );
				header( 'Content-type: application/json' );
				echo wp_json_encode( $response );
				die();
			break;
			case 'stick_post':
				// Required params: post_id.
				$post_id = intval( $post_id_post );
				$post    = get_post( $post_id );
				if ( ! $post ) {
					die();
				}
				stick_post( $post_id );
				$response = array(
					'post_id' => $post_id,
					'result'  => 'success',
					'msg'     => __( 'Post stuck.', 'capsule' ),
					'html'    => '',
				);
				header( 'Content-type: application/json' );
				echo wp_json_encode( $response );
				die();
			break;
			case 'unstick_post':
				// Required params: post_id.
				$post_id = intval( $post_id_post );
				if ( ! $post_id ) {
					die();
				}
				unstick_post( $post_id );
				$response = array(
					'post_id' => $post_id,
					'result'  => 'success',
					'msg'     => __( 'Post unstuck.', 'capsule' ),
					'html'    => '',
				);
				header( 'Content-type: application/json' );
				echo wp_json_encode( $response );
				die();
			break;
			case 'split_post':
				// required params:
				// - post_id
				// - content
				// - new_post_content
				// TODO
			break;
			case 'merge_posts':
				// required params:
				// - post_ids (array)
				// TODO
			break;
			default:
				do_action( 'capsule_controller_action_post', $capsule_action_post );
			break;
		}// End switch().
	}// End if().
}
add_action( 'init', 'capsule_controller', 11 );
