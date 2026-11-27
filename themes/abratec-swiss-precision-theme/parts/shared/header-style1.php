<?php
/**
 * Header Style 1 - Styled for Abratec Swiss Precision
 *
 * @package understrap
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$container = get_theme_mod( 'understrap_container_type' );
$logo      = get_field( 'global_company_logo', 'option' );
$phone     = get_field( 'global_phone_number', 'option' );
$clean_tel = preg_replace( '/[^0-9]/', '', $phone );
$quote_link = get_field( 'request_quote_link', 'option' );
$quote_url  = ( is_array( $quote_link ) && ! empty( $quote_link['url'] ) ) ? $quote_link['url'] : home_url( '/request-quote' );
$quote_lbl  = ( is_array( $quote_link ) && ! empty( $quote_link['title'] ) ) ? $quote_link['title'] : __( 'Request Quote', 'tms' );
?>

<header class="site-header header0 tms-site-header">
	<!-- Top Bar -->
	<div class="tms-header-top">
		<div class="container d-flex align-items-center justify-content-between">
			<!-- Logo -->
			<div class="tms-header-logo-pill">
				<?php if ( ! empty( $logo ) && ! empty( $logo['url'] ) ) : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" rel="home">
						<img src="<?php echo esc_url( $logo['url'] ); ?>" alt="<?php echo esc_attr( ! empty( $logo['alt'] ) ? $logo['alt'] : get_bloginfo( 'name' ) ); ?>">
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo-text" rel="home">
						<strong><?php bloginfo( 'name' ); ?></strong>
					</a>
				<?php endif; ?>
			</div>

			<!-- Info Blocks -->
			<div class="tms-header-info d-flex align-items-center">
				<?php if ( $phone ) : ?>
					<div class="tms-header-info-item d-flex align-items-center">
						<div class="tms-icon-circle tms-icon-circle--red">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/img/icons/phone.svg' ); ?>" alt="Phone" width="18" height="18">
						</div>
						<div class="tms-header-info-text">
							<a href="tel:<?php echo esc_attr( $clean_tel ); ?>" class="tms-header-phone-link">
								<?php echo esc_html( $phone ); ?>
							</a>
						</div>
					</div>
				<?php endif; ?>

				<div class="tms-header-info-item d-flex align-items-center">
					<div class="tms-icon-circle tms-icon-circle--dark-red">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/img/icons/document.svg' ); ?>" alt="document" width="18" height="18">
					</div>
					<div class="tms-header-info-text">
						<a href="<?php echo esc_url( $quote_url ); ?>" class="tms-header-quote-link">
							<?php echo esc_html( $quote_lbl ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Navigation Bar (Red Pill Container) -->
	<div class="tms-header-nav-wrap">
		<div class="container">
			<nav class="tms-red-nav navbar navbar-expand-lg">
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#tmsNavbarNav" aria-controls="tmsNavbarNav" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation', 'tms' ); ?>">
					<span class="fa fa-bars text-white"></span>
				</button>

				<div class="collapse navbar-collapse" id="tmsNavbarNav">
					<?php if ( has_nav_menu( 'primary' ) ) : ?>
						<?php wp_nav_menu(
							array(
								'theme_location'  => 'primary',
								'container'       => false,
								'menu_class'      => 'navbar-nav tms-main-nav mr-auto',
								'fallback_cb'     => '',
								'depth'           => 2,
								'walker'          => new Understrap_WP_Bootstrap_Navwalker(),
							)
						); ?>
					<?php else : ?>
						<ul class="navbar-nav tms-main-nav mr-auto">
							<li class="nav-item active"><a class="nav-link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'HOME', 'tms' ); ?></a></li>
							<li class="nav-item"><a class="nav-link" href="<?php echo esc_url( home_url( '/about-us' ) ); ?>"><?php esc_html_e( 'About', 'tms' ); ?></a></li>
							<li class="nav-item"><a class="nav-link" href="<?php echo esc_url( home_url( '/swiss-machining-services' ) ); ?>"><?php esc_html_e( 'Swiss Machining Services', 'tms' ); ?></a></li>
							<li class="nav-item"><a class="nav-link" href="<?php echo esc_url( home_url( '/equipment' ) ); ?>"><?php esc_html_e( 'Equipment', 'tms' ); ?></a></li>
							<li class="nav-item"><a class="nav-link" href="<?php echo esc_url( home_url( '/blog' ) ); ?>"><?php esc_html_e( 'Blog', 'tms' ); ?></a></li>
							<li class="nav-item"><a class="nav-link" href="<?php echo esc_url( home_url( '/contact-us' ) ); ?>"><?php esc_html_e( 'Contact Us', 'tms' ); ?></a></li>
						</ul>
					<?php endif; ?>
				</div>

				<!-- Search Button -->
				<div class="tms-nav-search ml-auto">
					<a href="#search" class="tms-search-trigger search-form-tigger" data-toggle="search-form" aria-label="<?php esc_attr_e( 'Search', 'tms' ); ?>">
						<i class="fa fa-search" aria-hidden="true"></i>
					</a>
				</div>
			</nav>
		</div>
	</div>
</header>
