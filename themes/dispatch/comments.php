<?php
/**
 * Comments template.
 *
 * @package Dispatch
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
			$dispatch_count = (int) get_comments_number();
			if ( 1 === $dispatch_count ) {
				esc_html_e( 'One response', 'dispatch' );
			} else {
				printf(
					/* translators: %s: comment count. */
					esc_html( _nx( '%s response', '%s responses', $dispatch_count, 'comments title', 'dispatch' ) ),
					esc_html( number_format_i18n( $dispatch_count ) )
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
			'prev_text' => esc_html__( 'Older comments', 'dispatch' ),
			'next_text' => esc_html__( 'Newer comments', 'dispatch' ),
		) );

		if ( ! comments_open() ) :
			?>
			<p class="no-comments"><?php esc_html_e( 'The conversation is closed.', 'dispatch' ); ?></p>
			<?php
		endif;

	endif;

	comment_form( array(
		'title_reply'         => esc_html__( 'Join the conversation', 'dispatch' ),
		'title_reply_to'      => esc_html__( 'Reply to %s', 'dispatch' ),
		'class_submit'        => 'button comment-submit',
		'comment_notes_after' => '',
	) );
	?>
</section>
