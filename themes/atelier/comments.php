<?php
/**
 * Comments template.
 *
 * @package Atelier
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
			$atelier_count = get_comments_number();
			if ( 1 === (int) $atelier_count ) {
				esc_html_e( 'One note', 'atelier' );
			} else {
				printf(
					/* translators: %s: comment count. */
					esc_html( _nx( '%s note', '%s notes', $atelier_count, 'comments title', 'atelier' ) ),
					esc_html( number_format_i18n( $atelier_count ) )
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
			'prev_text' => esc_html__( 'Older notes', 'atelier' ),
			'next_text' => esc_html__( 'Newer notes', 'atelier' ),
		) );

		if ( ! comments_open() ) :
			?>
			<p class="no-comments"><?php esc_html_e( 'The conversation is closed.', 'atelier' ); ?></p>
			<?php
		endif;

	endif;

	comment_form( array(
		'title_reply'         => esc_html__( 'Leave a note', 'atelier' ),
		'title_reply_to'      => esc_html__( 'Reply to %s', 'atelier' ),
		'class_submit'        => 'button comment-submit',
		'comment_notes_after' => '',
	) );
	?>
</section>
