<?php
/**
 * The template for displaying full width pages.
 *
 * Template Name: Popup
 *
 * @package storefront
 */
if (array_key_exists('iframe', $_GET)) {
    $popup = true;
    global $show_admin_bar;
    if ($show_admin_bar) {
        $show_admin_bar = false;
        add_filter('show_admin_bar', '__return_false');
        remove_action( 'wp_head', '_admin_bar_bump_cb');
        wp_dequeue_script( 'admin-bar' );
        wp_dequeue_style('admin-bar');
        remove_action( 'admin_bar_init', '_wp_admin_bar_init' );
    }
} else {
    $popup = false;
}
if ($popup) {
    load_template(plugin_dir_path(__FILE__) . 'header-popup.php');
} else {
    get_header();
}
?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php
			while ( have_posts() ) :
				the_post();

				do_action( 'storefront_page_before' );

				get_template_part( 'content', 'page' );

				/**
				 * Functions hooked in to storefront_page_after action
				 *
				 * @hooked storefront_display_comments - 10
				 */
				do_action( 'storefront_page_after' );

			endwhile; // End of the loop.
			?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
if ($popup) {
    load_template(plugin_dir_path(__FILE__) . 'footer-popup.php');
} else {
    get_footer();
}