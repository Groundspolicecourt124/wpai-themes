<?php
/**
 * Comments template.
 *
 * @package Hearth
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
			$hearth_count = (int) get_comments_number();
			if ( 1 === $hearth_count ) {
				esc_html_e( 'One note from the table', 'hearth' );
			} else {
				printf(
					/* translators: %s: comment count. */
					esc_html( _nx( '%s note from the table', '%s notes from the table', $hearth_count, 'comments title', 'hearth' ) ),
					esc_html( number_format_i18n( $hearth_count ) )
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
			'prev_text' => esc_html__( 'Older notes', 'hearth' ),
			'next_text' => esc_html__( 'Newer notes', 'hearth' ),
		) );

		if ( ! comments_open() ) :
			?>
			<p class="no-comments"><?php esc_html_e( 'The guestbook is closed.', 'hearth' ); ?></p>
			<?php
		endif;

	endif;

	comment_form( array(
		'title_reply'         => esc_html__( 'Leave a note', 'hearth' ),
		'title_reply_to'      => esc_html__( 'Reply to %s', 'hearth' ),
		'class_submit'        => 'button comment-submit',
		'comment_notes_after' => '',
	) );
	?>
</section>
