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
			if ( '1' === $aurora_count ) {
				esc_html_e( 'One response', 'aurora' );
			} else {
				printf(
					/* translators: %s: comment count. */
					esc_html( _nx( '%s response', '%s responses', $aurora_count, 'comments title', 'aurora' ) ),
					esc_html( number_format_i18n( $aurora_count ) )
				);
			}
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments( array(
				'style'       => 'ol',
				'short_ping'  => true,
				'avatar_size' => 48,
			) );
			?>
		</ol>

		<?php
		the_comments_navigation( array(
			'prev_text' => esc_html__( 'Older comments', 'aurora' ),
			'next_text' => esc_html__( 'Newer comments', 'aurora' ),
		) );

		if ( ! comments_open() ) :
			?>
			<p class="no-comments"><?php esc_html_e( 'The conversation is closed.', 'aurora' ); ?></p>
			<?php
		endif;

	endif;

	comment_form( array(
		'title_reply'         => esc_html__( 'Leave a response', 'aurora' ),
		'title_reply_to'      => esc_html__( 'Reply to %s', 'aurora' ),
		'class_submit'        => 'button comment-submit',
		'comment_notes_after' => '',
	) );
	?>
</section>
