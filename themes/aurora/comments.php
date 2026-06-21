<?php
/**
 * Comments template.
 *
 * @package Aurora
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>
<section class="comments-area">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			$aurora_count = get_comments_number();
			printf(
				/* translators: %s: comment count */
				esc_html( _n( '%s Comment', '%s Comments', $aurora_count, 'aurora' ) ),
				esc_html( number_format_i18n( $aurora_count ) )
			);
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments( array(
				'style'      => 'ol',
				'short_ping' => true,
				'avatar_size' => 44,
			) );
			?>
		</ol>

		<?php
		the_comments_navigation();

		if ( ! comments_open() ) :
			?>
			<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'aurora' ); ?></p>
			<?php
		endif;

	endif;

	comment_form();
	?>
</section>
