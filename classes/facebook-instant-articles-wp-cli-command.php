<?php
/**
 * Facebook Instant Articles WP CLI Command
 *
 * @package  Facebook_Instant_Articles_WP_CLI
 */

namespace Lift\WP_CLI\Facebook_Instant_Articles;

/**
 * Facebook Instant Articles helper commands.
 */
class Command extends \WPCOM_VIP_CLI_Command {

	/**
	 * submit
	 *
	 * Submits a post to Facebook Instant Articles
	 *
	 * ## OPTIONS
	 *
	 * [--post_id=<post_id>]
	 * : The WP_Post id of the post you'd like to refresh.
	 * ---
	 * default: "0"
	 * ---
	 *
	 * [--dry_run]
	 * : Use this if you don't want to actually submit the articles, just see what would happen.
	 * ---
	 * default: "0"
	 * ---
	 *
	 * ## EXAMPLES
	 * 		wp instant-articles submit --post_id=1
	 * 		wp instant-articles submit --post_id=$(wp post list --format=ids)
	 */
	public function submit( $args, $assoc_args ) {
		if ( isset( $assoc_args['dry_run'] ) ) {
			$this->submit_dry_run( $args, $assoc_args );
			return;
		}

		if ( ! isset( $assoc_args['post_id'] ) || ! is_numeric( $assoc_args['post_id'] ) ) {
			\WP_CLI::error( '--post_id expects an integer, ' . gettype( $assoc_args['post_id'] ) . ' given.' );
		}

		$post_id = intval( $assoc_args['post_id'] );
		$post = get_post( $post_id );

		if ( ! $post instanceof \WP_Post ) {
			\WP_CLI::error( 'Failed to retrieve post with ID of ' . absint( $post_id ) );
		}

		try {
			\Instant_Articles_Publisher::submit_article( $post_id, $post );
		} catch ( \Exception $e ) {
			\WP_CLI::error( esc_html( $e->getMessage() ) );
		}

		\WP_CLI::success( 'Successfully submitted (' . absint( $post_id ) . ') to Instant Articles.' );
	}

	/**
	 * Submit Dry Run
	 *
	 * @access protected
	 * @param  array $args       Args passed to submit().
	 * @param  array $assoc_args Assoc args passed to submit().
	 * @return void
	 */
	private function submit_dry_run( $args, $assoc_args ) {
		if ( ! isset( $assoc_args['post_id'] ) || ! is_numeric( $assoc_args['post_id'] ) ) {
			\WP_CLI::error( '--post_id expects an integer, ' . gettype( $assoc_args['post_id'] ) . ' given.' );
		}

		$post_id = intval( $assoc_args['post_id'] );
		$post = \get_post( $post_id );

		if ( ! $post instanceof \WP_Post ) {
			\WP_CLI::error( 'Failed to retrieve post with ID of ' . absint( $post_id ) );
		}

		if ( ! method_exists( '\Instant_Articles_Publisher', 'submit_article' ) ) {
			\WP_CLI::error( 'Failed to fullfill necessary dependencies.  Is the instant articles plugin installed? ' );
		}

		\WP_CLI::success( 'Will attempt to submit (' . absint( $post_id ) . ') to Instant Articles.' );
	}

	/**
	 * Resubmit All
	 *
	 * Reubmits all previously submitted posts to Facebook Instant Articles
	 *
	 * ## OPTIONS
	 *
	 * [--dry_run]
	 * : Use this if you don't want to actually submit the articles, just see what would happen.
	 *
	 * ## EXAMPLES
	 * 		wp instant-articles resubmit_all
	 * 		wp instant-articles resubmit_all --dry_run
	 */
	public function resubmit_all( $args, $assoc_args ) {
		// Query on meta_key.
		$submitted = new \WP_Query( array(
			'meta_key' => \Instant_Articles_Publisher::SUBMISSION_ID_KEY,
		) );

		if ( ! $submitted->posts ) {
			\WP_CLI::error( 'Could not find any posts that have been submitted to Instant Articles.' );
		}

		foreach ( $submitted->posts as $post ) {
			$pass_assoc_args = [ 'post_id' => $post->ID ];

			if ( isset( $assoc_args['dry_run'] ) ) {
				$pass_assoc_args['dry_run'] = true;
			}

			$this->submit( [], $pass_assoc_args );

			$this->stop_the_insanity();
		}
	}
}
