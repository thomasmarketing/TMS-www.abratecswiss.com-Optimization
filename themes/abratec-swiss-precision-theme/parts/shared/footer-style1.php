<?php
/**
 * Footer Style 1 - Styled for Abratec Swiss Precision
 *
 * @package understrap
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$logo = get_field( 'global_company_logo', 'option' );
?>
<!--Site Footer Start-->
<footer class="site-footer footer-style1 tms-theme-footer" role="contentinfo">
	<div class="container">
		<div class="tms-footer-card">
			<!-- Top Section: Logo & Quote Form -->
			<div class="tms-footer-card__top">
				<div class="tms-footer-card__logo">
					<?php if ( ! empty( $logo ) && ! empty( $logo['url'] ) ) : ?>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tms-footer-logo-link">
							<img src="<?php echo esc_url( $logo['url'] ); ?>" alt="<?php echo esc_attr( ! empty( $logo['alt'] ) ? $logo['alt'] : get_bloginfo( 'name' ) ); ?>">
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tms-footer-logo-text">
							<?php bloginfo( 'name' ); ?>
						</a>
					<?php endif; ?>
				</div>

				<div class="tms-footer-card__quote">
					<form class="tms-footer-quote-form" action="<?php echo esc_url( home_url( '/request-quote' ) ); ?>" method="get">
						<input type="email" name="email" class="tms-footer-quote-input" placeholder="<?php esc_attr_e( 'Enter your email', 'tms' ); ?>">
						<button type="submit" class="tms-footer-quote-btn"><?php esc_html_e( 'Request a Quote', 'tms' ); ?></button>
					</form>
				</div>
			</div>

			<hr class="tms-footer-divider tms-footer-divider--top">

			<!-- Middle Section: 3 Columns -->
			<div class="tms-footer-card__columns">
				<div class="row">
					<!-- Col 1: Quick Links -->
					<div class="col-lg-5 col-md-5 mb-4 mb-lg-0">
						<h4 class="tms-footer-col-title"><?php esc_html_e( 'Quick Links', 'tms' ); ?></h4>
						<?php
						$footer_menu_args = array(
							'menu'        => 'Footer Menu',
							'container'   => 'nav',
							'menu_class'  => 'tms-footer-nav-list',
							'fallback_cb' => false,
						);
						if ( ! has_nav_menu( 'Footer Menu' ) ) {
							$footer_menu_args['theme_location'] = 'primary';
						}
						wp_nav_menu( $footer_menu_args );
						?>
					</div>

					<!-- Col 2: Contact -->
					<div class="col-lg-5 col-md-5 mb-4 mb-lg-0">
						<h4 class="tms-footer-col-title"><?php esc_html_e( 'Contact', 'tms' ); ?></h4>
						<ul class="tms-footer-contact-list">
							<?php
							$phone = get_field( 'global_phone_number', 'option' );
							$clean_phone = preg_replace( '/[^0-9]/', '', $phone );
							if ( $phone ) :
							?>
								<li>
									<span class="tms-contact-label"><?php esc_html_e( 'Tel:', 'tms' ); ?></span>
									<a href="tel:<?php echo esc_attr( $clean_phone ); ?>"><?php echo esc_html( $phone ); ?></a>
								</li>
							<?php endif; ?>

							<?php
							$fax = get_field( 'global_fax', 'option' );
							if ( $fax ) :
							?>
								<li>
									<span class="tms-contact-label"><?php esc_html_e( 'Fax:', 'tms' ); ?></span>
									<span><?php echo esc_html( $fax ); ?></span>
								</li>
							<?php endif; ?>

							<?php
							$email = get_field( 'global_email', 'option' );
							if ( $email ) :
							?>
								<li>
									<a href="mailto:<?php echo esc_attr( $email ); ?>" class="tms-contact-email"><?php echo esc_html( $email ); ?></a>
								</li>
							<?php endif; ?>

							<?php
							$company  = get_field( 'global_company_name', 'option' );
							$address  = get_field( 'global_address', 'option' );
							$map_link = get_field( 'global_address_map_link', 'option' );
							?>
							<li class="tms-contact-address">
								<?php if ( $company ) : ?>
									<?php echo esc_html( $company ); ?><br>
								<?php endif; ?>
								<?php if ( $address ) : ?>
									<?php if ( $map_link ) : ?>
										<a href="<?php echo esc_url( $map_link ); ?>" target="_blank" rel="noopener noreferrer"><?php echo nl2br( esc_html( $address ) ); ?></a>
									<?php else : ?>
										<span><?php echo nl2br( esc_html( $address ) ); ?></span>
									<?php endif; ?>
								<?php endif; ?>
							</li>
						</ul>
					</div>

					<!-- Col 3: Follow Us -->
					<div class="col-lg-2 col-md-2">
						<h4 class="tms-footer-col-title"><?php esc_html_e( 'Follow Us', 'tms' ); ?></h4>
						<ul class="tms-footer-social-list">
							<?php if ( have_rows( 'social_profiles', 'option' ) ) : ?>
								<?php while ( have_rows( 'social_profiles', 'option' ) ) : the_row();
									$icon  = get_sub_field( 'sp_social_icon' );
									$name  = get_sub_field( 'sp_social_profile' );
									$link  = get_sub_field( 'sp_social_link' );
								?>
									<?php if ( $link ) : ?>
										<li>
											<a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener noreferrer">
												<span class="tms-social-icon-box"><?php echo $icon ? $icon : '<i class="fa fa-share-alt"></i>'; ?></span>
												<span class="tms-social-name"><?php echo esc_html( $name ); ?></span>
											</a>
										</li>
									<?php endif; ?>
								<?php endwhile; ?>
							<?php else : ?>
								<li>
									<a href="https://facebook.com" target="_blank" rel="noopener noreferrer">
										<span class="tms-social-icon-box"><i class="fa fa-facebook"></i></span>
										<span class="tms-social-name">Facebook</span>
									</a>
								</li>
								<li>
									<a href="https://instagram.com" target="_blank" rel="noopener noreferrer">
										<span class="tms-social-icon-box"><i class="fa fa-instagram"></i></span>
										<span class="tms-social-name">Instagram</span>
									</a>
								</li>
								<li>
									<a href="https://twitter.com" target="_blank" rel="noopener noreferrer">
										<span class="tms-social-icon-box"><i class="fa fa-twitter"></i></span>
										<span class="tms-social-name">X</span>
									</a>
								</li>
								<li>
									<a href="https://linkedin.com" target="_blank" rel="noopener noreferrer">
										<span class="tms-social-icon-box"><i class="fa fa-linkedin"></i></span>
										<span class="tms-social-name">LinkedIn</span>
									</a>
								</li>
							<?php endif; ?>
						</ul>
					</div>
				</div>
			</div>

			<hr class="tms-footer-divider">

			<!-- Bottom Bar -->
			<div class="tms-footer-card__bottom">
				<p class="tms-footer-copyright">
					<?php
					$copyright_text = get_field( 'footer_copyright_text', 'option' );
					$iso_text = get_field( 'footer_iso_text', 'option' );
					$iso_file = get_field( 'footer_iso_file', 'option' );
					if ( ! $iso_text ) {
						$iso_text = __( 'ISO 9001:2015 Certified', 'tms' );
					}
					$iso_url = ! empty( $iso_file ) ? ( is_array( $iso_file ) ? $iso_file['url'] : $iso_file ) : '';
					?>
					<?php if ($copyright_text) : ?>
						<?php echo esc_html( $copyright_text ); ?>
					<?php else : ?>
						&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.
					<?php endif ?>
					<?php if ( $iso_text ) : ?>
						<?php if ( $iso_url ) : ?>
							<a href="<?php echo esc_url( $iso_url ); ?>" class="tms-footer-iso-link" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $iso_text ); ?></a>
						<?php else : ?>
							<span class="tms-footer-iso-link"><?php echo esc_html( $iso_text ); ?></span>
						<?php endif; ?>
					<?php endif; ?>
				</p>
				<p class="tms-footer-credit">
					<?php esc_html_e( 'Site created by', 'tms' ); ?>
					<a href="https://business.thomasnet.com/marketing-services" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Thomas Marketing Services', 'tms' ); ?>
					</a>
				</p>
			</div>
		</div>
	</div>
</footer>
<!--Site Footer End-->
