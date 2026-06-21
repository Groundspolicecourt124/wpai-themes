<?php
/**
 * Comments template.
 *
 * @package Sonnet
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
			$sonnet_count = get_comments_number();
			if ( '1' === $sonnet_count ) {
				esc_html_e( 'One response', 'sonnet' );
			} else {
				printf(
					/* translators: %s: comment count. */
					esc_html( _n( '%s response', '%s responses', $sonnet_count, 'sonnet' ) ),
					esc_html( number_format_i18n( $sonnet_count ) )
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
			'prev_text' => esc_html__( 'Older comments', 'sonnet' ),
			'next_text' => esc_html__( 'Newer comments', 'sonnet' ),
		) );

		if ( ! comments_open() ) :
			?>
			<p class="no-comments"><?php esc_html_e( 'The conversation on this piece is closed.', 'sonnet' ); ?></p>
			<?php
		endif;

	endif;

	comment_form( array(
		'title_reply'         => esc_html__( 'Leave a response', 'sonnet' ),
		'title_reply_to'      => esc_html__( 'Reply to %s', 'sonnet' ),
		'cancel_reply_link'   => esc_html__( 'Cancel reply', 'sonnet' ),
		'label_submit'        => esc_html__( 'Post response', 'sonnet' ),
		'comment_notes_before' => '<p class="comment-notes">' . esc_html__( 'Your email address stays private. Required fields are marked.', 'sonnet' ) . '</p>',
		'class_submit'        => 'button comment-submit',
	) );
	?>
</section>
