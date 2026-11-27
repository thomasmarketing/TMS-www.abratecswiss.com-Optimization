<?php
/**
 * Template Name: Default TMS Template
 *
 * Template for displaying a page without sidebar even if a sidebar widget is published.
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

$container = get_theme_mod( 'understrap_container_type' );

?>

<div class="wrapper" id="page-wrapper" data-test="aa">

	<?php if (get_the_content() || is_page( '9' )): ?>
		
		<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

			<div class="content-area">

				<!-- Do the left sidebar check -->
				<?php //get_template_part( 'global-templates/left-sidebar-check' ); ?>

				<main class="site-main" id="main">

					<?php while ( have_posts() ) : the_post(); ?>

						<?php get_template_part( 'loop-templates/content', 'page' ); ?>
						<?php get_template_part( 'page-templates/contact-info' ) ?>
						<?php if (is_page( '9' )) : ?>
							<!--Sitemap Page-->
							<?php wp_nav_menu(array(
						      'menu'            => 'Sitemap',
						      'container'       => 'ul',
						      'menu_class'      => 'sitemap-menu',
						      )); ?>
						<?php endif; ?>	 


					<?php endwhile; // end of the loop. ?>

				</main><!-- #main -->

				<!-- Do the right sidebar check -->
				<?php //get_template_part( 'global-templates/right-sidebar-check' ); ?>

			</div><!-- .row -->

		</div><!-- #content -->
	<?php endif ?>

	<!-- Do the Flexible Content check -->
	<?php //get_template_part('parts/shared/about-flexible-content'); ?>
	<?php get_template_part('parts/shared/flexible-content'); ?>
	<?php get_template_part('parts/shared/tms-flexible-content'); ?>

	
</div><!-- #page-wrapper -->
<?php get_template_part('parts/full-width-cta-module'); ?>

<?php get_footer(); ?>
