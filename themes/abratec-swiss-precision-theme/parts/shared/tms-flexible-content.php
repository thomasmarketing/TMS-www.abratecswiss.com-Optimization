<?php
/**
 * TMS Flexible Content Independent Renderer
 *
 * Renders all layouts configured under the ACF TMS Flexible Content field.
 *
 * @package TMS
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'have_rows' ) || ! have_rows( 'tms_flexible_content' ) ) {
	return;
}

if ( ! function_exists( 'tms_get_module_bg_style' ) ) {
	/**
	 * Helper to generate inline background style based on TMS universal background controls.
	 *
	 * @return string Inline style attribute or empty string.
	 */
	function tms_get_module_bg_style() {
		$bg_type = get_sub_field( 'tms_background_type' );
		if ( ! $bg_type ) {
			$bg_type = 'solid';
		}

		$styles = array();

		if ( 'solid' === $bg_type ) {
			$color = get_sub_field( 'tms_background_color' );
			if ( $color ) {
				$styles[] = 'background-color: ' . esc_attr( $color ) . ';';
			}
		} elseif ( 'image' === $bg_type ) {
			$img = get_sub_field( 'tms_background_image' );
			$img_url = '';
			if ( is_array( $img ) && ! empty( $img['url'] ) ) {
				$img_url = $img['url'];
			} elseif ( is_numeric( $img ) ) {
				$img_url = wp_get_attachment_image_url( $img, 'full' );
			} elseif ( is_string( $img ) ) {
				$img_url = $img;
			}

			if ( ! $img_url && 'tms_testimonials' === get_row_layout() && file_exists( get_template_directory() . '/img/testimonials-bg.png' ) ) {
				$img_url = get_template_directory_uri() . '/img/testimonials-bg.png';
			}

			if ( $img_url ) {
				$styles[] = 'background-image: url(' . esc_url( $img_url ) . ');';
				$repeat = get_sub_field( 'tms_background_repeat' ) ? get_sub_field( 'tms_background_repeat' ) : 'no-repeat';
				$size   = get_sub_field( 'tms_background_size' ) ? get_sub_field( 'tms_background_size' ) : 'cover';
				$pos_x  = get_sub_field( 'tms_background_position_x' );
				$pos_y  = get_sub_field( 'tms_background_position_y' );
				$pos_x  = ( '' !== $pos_x && null !== $pos_x ) ? intval( $pos_x ) : 50;
				$pos_y  = ( '' !== $pos_y && null !== $pos_y ) ? intval( $pos_y ) : 50;

				$styles[] = 'background-repeat: ' . esc_attr( $repeat ) . ';';
				$styles[] = 'background-size: ' . esc_attr( $size ) . ';';
				$styles[] = 'background-position: ' . $pos_x . '% ' . $pos_y . '%;';
			}
		} elseif ( 'gradient' === $bg_type ) {
			$col1  = get_sub_field( 'tms_gradient_color_1' ) ? get_sub_field( 'tms_gradient_color_1' ) : '#ffffff';
			$col2  = get_sub_field( 'tms_gradient_color_2' ) ? get_sub_field( 'tms_gradient_color_2' ) : '#f3f3f3';
			$angle = intval( get_sub_field( 'tms_gradient_angle' ) );
			$styles[] = 'background: linear-gradient(' . $angle . 'deg, ' . esc_attr( $col1 ) . ', ' . esc_attr( $col2 ) . ');';
		}

		return ! empty( $styles ) ? ' style="' . implode( ' ', $styles ) . '"' : '';
	}
}

if ( ! function_exists( 'tms_get_text_align_class' ) ) {
	/**
	 * Helper to get module text alignment class.
	 *
	 * @return string Alignment class name.
	 */
	function tms_get_text_align_class() {
		$align = get_sub_field( 'tms_text_alignment' );
		if ( in_array( $align, array( 'left', 'center', 'right' ), true ) ) {
			return 'tms-text-align-' . $align;
		}
		return 'tms-text-align-left';
	}
}

$module_counter = 0;
while ( have_rows( 'tms_flexible_content' ) ) :
	the_row();
	$module_counter++;
	$layout = get_row_layout();

	// Common wrapper attributes
	$custom_class = get_sub_field( 'custom_css_class' );
	$custom_id    = get_sub_field( 'custom_css_id' );
	$align_class  = tms_get_text_align_class();
	$bg_style     = tms_get_module_bg_style();
	$bg_type      = get_sub_field( 'tms_background_type' );

	$id_attr = $custom_id ? ' id="' . esc_attr( $custom_id ) . '"' : '';

	// Render video background if active
	$video_markup = '';
	if ( 'video' === $bg_type ) {
		$video_file   = get_sub_field( 'tms_background_video' );
		$video_poster = get_sub_field( 'tms_background_video_poster' );
		$video_url    = is_array( $video_file ) && ! empty( $video_file['url'] ) ? $video_file['url'] : '';
		$poster_url   = is_array( $video_poster ) && ! empty( $video_poster['url'] ) ? $video_poster['url'] : '';

		if ( $video_url ) {
			$video_markup = '<div class="tms-video-bg-wrap"><video class="tms-video-bg" autoplay loop muted playsinline' . ( $poster_url ? ' poster="' . esc_url( $poster_url ) . '"' : '' ) . '><source src="' . esc_url( $video_url ) . '" type="video/mp4"></video></div>';
		}
	}

	// Render background overlay if active
	$overlay_markup = '';
	if ( 'image' === $bg_type ) {
		$overlay_col = get_sub_field( 'tms_overlay_color' );
		if ( ! $overlay_col && 'tms_parts_showcase' === $layout ) {
			$overlay_col = 'rgba(0, 0, 0, 0.7)';
		}
		if ( ! $overlay_col && 'tms_testimonials' === $layout ) {
			$overlay_col = 'rgba(35, 35, 35, 0.5)';
		}
		if ( $overlay_col ) {
			$overlay_markup = '<div class="tms-module-overlay" style="background-color: ' . esc_attr( $overlay_col ) . ';"></div>';
		}
	}

	/* ------------------------------------------------------------------------
	 * Layout 1: TMS Hero Slider
	 * ------------------------------------------------------------------------ */
	if ( 'tms_hero_slider' === $layout ) :
		?>
		<section<?php echo $id_attr; ?> class="tms-hero-slider <?php echo esc_attr( trim( $align_class . ' ' . $custom_class ) ); ?>"<?php echo $bg_style; ?>>
			<?php echo $video_markup; ?>
			<div class="tms-hero-slider__track" data-autoplay="5000" data-loop="true">
				<?php if ( have_rows( 'slides' ) ) : ?>
					<?php while ( have_rows( 'slides' ) ) : the_row();
						$sub   = get_sub_field( 'subtitle' );
						$title = get_sub_field( 'title' );
						$desc  = get_sub_field( 'description' );
						$btn   = get_sub_field( 'button_link' );
						$img   = get_sub_field( 'slide_image' );
						$slide_bg = ( is_array( $img ) && ! empty( $img['url'] ) ) ? ' style="background-image: url(' . esc_url( $img['url'] ) . ');"' : '';
					?>
						<div class="tms-hero-slider__slide"<?php echo $slide_bg; ?>>
							<div class="tms-hero-slider__overlay"></div>
							<div class="container tms-hero-slider__container">
								<div class="tms-hero-slider__content-box">
									<?php if ( $sub ) : ?>
										<p class="tms-hero-slider__eyebrow"><?php echo esc_html( $sub ); ?></p>
									<?php endif; ?>
									<?php if ( $title ) : ?>
										<h1 class="tms-hero-slider__title"><?php echo esc_html( $title ); ?></h1>
									<?php endif; ?>
									<?php if ( $desc ) : ?>
										<div class="tms-hero-slider__desc tms-wysiwyg"><?php echo wp_kses_post( $desc ); ?></div>
									<?php endif; ?>
									<?php if ( ! empty( $btn['url'] ) ) : ?>
										<a href="<?php echo esc_url( $btn['url'] ); ?>" class="tms-btn tms-btn--primary"<?php echo ! empty( $btn['target'] ) ? ' target="' . esc_attr( $btn['target'] ) . '" rel="noopener noreferrer"' : ''; ?>>
											<span><?php echo esc_html( ! empty( $btn['title'] ) ? $btn['title'] : __( 'Contact us', 'tms' ) ); ?></span>
											<img src="<?php echo esc_url( get_template_directory_uri() . '/img/icons/arrow-right.svg' ); ?>" alt="arrow-right" width="14" height="14">
										</a>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endwhile; ?>
				<?php endif; ?>
			</div>
		</section>
		<?php

	/* ------------------------------------------------------------------------
	 * Layout 2: TMS About Cards (Sticky Stacked Cards)
	 * ------------------------------------------------------------------------ */
	elseif ( 'tms_about_cards' === $layout ) :
		$sub   = get_sub_field( 'subtitle' );
		$title = get_sub_field( 'title' );
		$desc  = get_sub_field( 'description' );
		$cards  = get_sub_field( 'cards' );


		$c1_title = get_sub_field( 'card_1_title' );
		$c1_body  = get_sub_field( 'card_1_content' );
		$c1_btn   = get_sub_field( 'card_1_button' );
		$c1_img   = get_sub_field( 'card_1_image' );

		$c2_title = get_sub_field( 'card_2_title' );
		$c2_body  = get_sub_field( 'card_2_content' );
		$c2_btn   = get_sub_field( 'card_2_button' );
		$c2_img   = get_sub_field( 'card_2_image' );
		?>
		<section<?php echo $id_attr; ?> class="tms-about-cards <?php echo esc_attr( trim( $align_class . ' ' . $custom_class ) ); ?>"<?php echo $bg_style; ?>>
			<?php echo $video_markup; ?>
			<div class="container">
				<!-- Section Intro Header -->
				<div class="tms-about-cards__header">
					<div class="row align-items-end">
						<div class="col-lg-5">
							<?php if ( $sub ) : ?>
								<p class="tms-section-eyebrow"><?php echo esc_html( $sub ); ?></p>
							<?php endif; ?>
							<?php if ( $title ) : ?>
								<h2 class="tms-section-title"><?php echo esc_html( $title ); ?></h2>
							<?php endif; ?>
						</div>
						<div class="col-lg-7 d-flex justify-content-end">
							<?php if ( $desc ) : ?>
								<div class="tms-about-cards__intro-desc tms-wysiwyg"><?php echo wp_kses_post( $desc ); ?></div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</section>
		<?php if ( have_rows( 'cards' ) ) : ?>
		<?php 
		$card_idx = 1;
		while ( have_rows( 'cards' ) ) : the_row();
			$title   = get_sub_field( 'title' );
			$content = get_sub_field( 'content' );
			$btn     = get_sub_field( 'button' );
			$image   = get_sub_field( 'image' );
			$img_url = ( is_array( $image ) && ! empty( $image['url'] ) ) ? $image['url'] : '';
			if ( ! $img_url && file_exists( get_template_directory() . '/img/about-card-' . $card_idx . '.png' ) ) {
				$img_url = get_template_directory_uri() . '/img/about-card-' . $card_idx . '.png';
			}
		?>
		<section class="tms-about-cards__sticky-section">
			<!-- Sticky Stacking Cards Container -->
			<div class="tms-about-cards__sticky-container">
				<!-- Cards from repeater -->
				<div class="tms-sticky-card tms-sticky-card--<?php echo ( $card_idx % 2 === 1 ) ? 'right' : 'left'; ?>" data-card-index="<?php echo esc_attr( $card_idx ); ?>">
					<div class="tms-sticky-card__inner">
						<div class="tms-sticky-card__content">
							<div class="tms-sticky-card__title-wrap">
								<img src="<?php echo esc_url( get_template_directory_uri() . '/img/abratec-horizontal-color-1.png' ); ?>" alt="abratec-horizontal-color" width="49" height="48">
								<h3 class="tms-sticky-card__title"><?php echo esc_html( $title ? $title : __( 'About Abratec', 'tms' ) ); ?></h3>
							</div>
							<?php if ( $content ) : ?>
								<div class="tms-sticky-card__text tms-wysiwyg"><?php echo wp_kses_post( $content ); ?></div>
							<?php endif; ?>
							<?php if ( ! empty( $btn['url'] ) ) : ?>
								<a href="<?php echo esc_url( $btn['url'] ); ?>" class="tms-btn tms-btn--primary"<?php echo ! empty( $btn['target'] ) ? ' target="' . esc_attr( $btn['target'] ) . '" rel="noopener noreferrer"' : ''; ?>>
									<span><?php echo esc_html( ! empty( $btn['title'] ) ? $btn['title'] : __( 'Read More', 'tms' ) ); ?></span>
									<img src="<?php echo esc_url( get_template_directory_uri() . '/img/icons/Vector.svg' ); ?>" alt="read-more" width="14" height="14">
								</a>
							<?php endif; ?>
						</div>
						<div class="tms-sticky-card__media">
							<div class="tms-card-shape tms-card-shape--<?php echo ( $card_idx % 2 === 1 ) ? 'right' : 'left'; ?>">
								<?php if ( $img_url ) : ?>
									<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( ! empty( $image['alt'] ) ? $image['alt'] : $title ); ?>" class="tms-card-shape__img">
								<?php else : ?>
									<div class="tms-card-shape__placeholder"></div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<?php 
			$card_idx++;
		endwhile; 
		?>
	<?php endif; ?>
		
					
				
		<?php

	/* ------------------------------------------------------------------------
	 * Layout 3: TMS Parts Showcase (Swiss Machining)
	 * ------------------------------------------------------------------------ */
	elseif ( 'tms_parts_showcase' === $layout ) :
		$sub   = get_sub_field( 'subtitle' );
		$title = get_sub_field( 'title' );
		$desc  = get_sub_field( 'description' );

		$side_title = get_sub_field( 'side_title' );
		$side_desc  = get_sub_field( 'side_description' );
		$side_btn   = get_sub_field( 'side_button' );
		?>
		<section<?php echo $id_attr; ?> class="tms-parts-showcase <?php echo esc_attr( trim( $align_class . ' ' . $custom_class ) ); ?>"<?php echo $bg_style; ?>>
			<?php echo $video_markup; ?>
			<?php echo $overlay_markup; ?>
			<div class="container">
				<!-- Section Header Row -->
				<div class="tms-parts-showcase__header">
					<div class="row align-items-end">
						<div class="col-lg-5">
							<?php if ( $sub ) : ?>
								<p class="tms-section-eyebrow tms-section-eyebrow--white"><?php echo esc_html( $sub ); ?></p>
							<?php endif; ?>
							<?php if ( $title ) : ?>
								<h2 class="tms-section-title tms-section-title--white"><?php echo esc_html( $title ); ?></h2>
							<?php endif; ?>
						</div>
						<div class="col-lg-7 d-flex justify-content-lg-end">
							<?php if ( $desc ) : ?>
								<div class="tms-parts-showcase__intro-desc tms-wysiwyg"><?php echo wp_kses_post( $desc ); ?></div>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<!-- Showcase Body: Side Box + Nav Track + Parts List -->
				<div class="row tms-parts-showcase__body align-items-center">
					<!-- Side Box -->
					<div class="col-lg-5 col-xl-5 tms-side-box">
						<div class="tms-parts-sidebar-card">
							<?php if ( $side_title ) : ?>
								<div class="tms-parts-sidebar-card__title-wrap">
									<img src="<?php echo esc_url( get_template_directory_uri() . '/img/abratec-horizontal-color-1.png' ); ?>" alt="abratec-horizontal-color" width="49" height="48" class="tms-parts-sidebar-card__logo">
									<h3 class="tms-parts-sidebar-card__title"><?php echo esc_html( $side_title ); ?></h3>
								</div>
							<?php endif; ?>
							<?php if ( $side_desc ) : ?>
								<div class="tms-parts-sidebar-card__desc tms-wysiwyg"><?php echo wp_kses_post( $side_desc ); ?></div>
							<?php endif; ?>
							<?php if ( ! empty( $side_btn['url'] ) ) : ?>
								<a href="<?php echo esc_url( $side_btn['url'] ); ?>" class="tms-btn tms-btn--primary"<?php echo ! empty( $side_btn['target'] ) ? ' target="' . esc_attr( $side_btn['target'] ) . '" rel="noopener noreferrer"' : ''; ?>>
									<span><?php echo esc_html( ! empty( $side_btn['title'] ) ? $side_btn['title'] : __( 'Read More', 'tms' ) ); ?></span>
									<img src="<?php echo esc_url( get_template_directory_uri() . '/img/icons/Vector.svg' ); ?>" alt="read-more" width="14" height="14">
								</a>
							<?php endif; ?>
						</div>
					</div>

					<!-- Center Nav Indicator (Figma track) -->
					<div class="col-lg-1 d-none d-xl-flex justify-content-center tms-parts-nav-track-col">
						<div class="tms-parts-nav-track">
							<span class="tms-parts-nav-btn tms-parts-nav-btn--up">
								<img src="<?php echo esc_url( get_template_directory_uri() . '/img/icons/CTA Button.svg' ); ?>" alt="Up" width="32" height="32">
							</span>
							<span class="tms-parts-nav-btn tms-parts-nav-btn--down">
								<img src="<?php echo esc_url( get_template_directory_uri() . '/img/icons/CTA Button-1.svg' ); ?>" alt="Down" width="32" height="32">
							</span>
							<div class="tms-parts-nav-line"></div>
						</div>
					</div>

					<!-- Parts Vertical Cards List -->
					<div class="col-lg-7 col-xl-6 tms-cards-list">
						<div class="tms-parts-list">
							<?php if ( have_rows( 'parts_list' ) ) :
								$part_idx = 0;
								while ( have_rows( 'parts_list' ) ) : the_row();
									$part_idx++;
									$p_name = get_sub_field( 'part_name' );
									$p_desc = get_sub_field( 'part_description' );
									$p_img  = get_sub_field( 'part_image' );
									$p_url  = ( is_array( $p_img ) && ! empty( $p_img['url'] ) ) ? $p_img['url'] : '';
								?>
									<div class="tms-part-card tms-part-card--<?php echo esc_attr( $part_idx ); ?>">
										<div class="tms-part-card__content">
											<h4 class="tms-part-card__title">
												<span class="tms-part-card__num"><?php echo esc_html( $part_idx ); ?>. </span><?php echo esc_html( $p_name ? $p_name : __( 'PART NAME', 'tms' ) ); ?>
											</h4>
											<?php if ( $p_desc ) : ?>
												<div class="tms-part-card__desc tms-wysiwyg"><?php echo wp_kses_post( $p_desc ); ?></div>
											<?php endif; ?>
										</div>
										<div class="tms-part-card__thumb">
											<?php if ( $p_url ) : ?>
												<img src="<?php echo esc_url( $p_url ); ?>" alt="<?php echo esc_attr( $p_name ); ?>">
											<?php else : ?>
												<div class="tms-part-card__thumb-placeholder"></div>
											<?php endif; ?>
										</div>
									</div>
								<?php endwhile; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</section>
		<?php

	/* ------------------------------------------------------------------------
	 * Layout 4: TMS Equipment Carousel
	 * ------------------------------------------------------------------------ */
	elseif ( 'tms_equipment_carousel' === $layout ) :
		$title = get_sub_field( 'title' );
		$desc  = get_sub_field( 'description' );
		$btn   = get_sub_field( 'button_link' );
		?>
		<section<?php echo $id_attr; ?> class="tms-equipment-carousel <?php echo esc_attr( trim( $align_class . ' ' . $custom_class ) ); ?>"<?php echo $bg_style; ?>>
			<?php echo $video_markup; ?>
			<div class="container">
				<div class="row align-items-center">
					<!-- Left Info Column -->
					<div class="col-lg-4">
						<div class="tms-equipment-carousel__info">
							<?php if ( $title ) : ?>
								<div class="tms-equipment-carousel__title-wrap">
									<img src="<?php echo esc_url( get_template_directory_uri() . '/img/abratec-horizontal-color-1.png' ); ?>" alt="abratec-horizontal-color" width="49" height="48" class="tms-equipment-carousel__logo">
									<h2 class="tms-section-title"><?php echo esc_html( $title ); ?></h2>
								</div>
							<?php endif; ?>
							<?php if ( $desc ) : ?>
								<div class="tms-equipment-carousel__desc tms-wysiwyg"><?php echo wp_kses_post( $desc ); ?></div>
							<?php endif; ?>
							<?php if ( ! empty( $btn['url'] ) ) : ?>
								<a href="<?php echo esc_url( $btn['url'] ); ?>" class="tms-btn tms-btn--primary"<?php echo ! empty( $btn['target'] ) ? ' target="' . esc_attr( $btn['target'] ) . '" rel="noopener noreferrer"' : ''; ?>>
									<span><?php echo esc_html( ! empty( $btn['title'] ) ? $btn['title'] : __( 'Read More', 'tms' ) ); ?></span>
									<img src="<?php echo esc_url( get_template_directory_uri() . '/img/icons/Vector.svg' ); ?>" alt="read-more" width="14" height="14">
								</a>
							<?php endif; ?>
						</div>
					</div>

					<!-- Right Carousel Slider (No autoplay, drag/swipe enabled) -->
					<div class="col-lg-8">
						<div class="tms-equipment-slider" data-autoplay="false" data-draggable="true">
							<?php if ( have_rows( 'equipment_items' ) ) : ?>
								<?php while ( have_rows( 'equipment_items' ) ) : the_row();
									$eq_title = get_sub_field( 'item_title' );
									$eq_desc  = get_sub_field( 'item_description' );
									$eq_img   = get_sub_field( 'item_image' );
									$eq_link  = get_sub_field( 'item_link' );
									$eq_url   = ( is_array( $eq_img ) && ! empty( $eq_img['url'] ) ) ? $eq_img['url'] : '';
								?>
									<div class="tms-equipment-card-wrap">
										<div class="tms-equipment-card">
											<div class="tms-equipment-card__media">
												<?php if ( $eq_url ) : ?>
													<img src="<?php echo esc_url( $eq_url ); ?>" alt="<?php echo esc_attr( $eq_title ); ?>">
												<?php else : ?>
													<div class="tms-equipment-card__placeholder"></div>
												<?php endif; ?>
											</div>
											<div class="tms-equipment-card__body">
												<div class="tms-equipment-card__text-stack">
													<h3 class="tms-equipment-card__title"><?php echo esc_html( $eq_title ); ?></h3>
													<?php if ( $eq_desc ) : ?>
														<div class="tms-equipment-card__desc tms-wysiwyg"><?php echo wp_kses_post( $eq_desc ); ?></div>
													<?php endif; ?>
												</div>
												<?php if ( ! empty( $eq_link['url'] ) ) : ?>
													<a href="<?php echo esc_url( $eq_link['url'] ); ?>" class="tms-equipment-card__arrow-btn" aria-label="<?php echo esc_attr( $eq_title ); ?>">
														<img src="<?php echo esc_url( get_template_directory_uri() . '/img/icons/Vector-1.svg' ); ?>" alt="view" width="30" height="30">
													</a>
												<?php endif; ?>
											</div>
										</div>
									</div>
								<?php endwhile; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</section>
		<?php

	/* ------------------------------------------------------------------------
	 * Layout 5: TMS Testimonials (Autoplay Slider)
	 * ------------------------------------------------------------------------ */
	elseif ( 'tms_testimonials' === $layout ) :
		$sub   = get_sub_field( 'subtitle' );
		$title = get_sub_field( 'title' );
		?>
		<section<?php echo $id_attr; ?> class="tms-testimonials <?php echo esc_attr( trim( $align_class . ' ' . $custom_class ) ); ?>"<?php echo $bg_style; ?>>
			<?php echo $video_markup; ?>
			<?php echo $overlay_markup; ?>
			<div class="container">
				<!-- Header -->
				<div class="tms-testimonials__header text-center">
					<?php if ( $sub ) : ?>
						<p class="tms-section-eyebrow tms-section-eyebrow--white"><?php echo esc_html( $sub ); ?></p>
					<?php endif; ?>
					<?php if ( $title ) : ?>
						<div class="tms-testimonials__title-wrap">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/img/abratec-horizontal-color-1.png' ); ?>" alt="abratec-horizontal-color" width="49" height="48" class="tms-testimonials__logo">
							<h2 class="tms-section-title tms-section-title--white"><?php echo esc_html( $title ); ?></h2>
						</div>
					<?php endif; ?>
				</div>

				<!-- Carousel (Autoplay enabled) -->
				<div class="tms-testimonials-slider" data-autoplay="5000">
					<?php if ( have_rows( 'testimonials_list' ) ) : ?>
						<?php 
						while ( have_rows( 'testimonials_list' ) ) : the_row();
							$q_title = get_sub_field( 'quote_title' );
							$q_body  = get_sub_field( 'quote_content' );
							$author  = get_sub_field( 'author_name' );
							$role    = get_sub_field( 'author_title' );
							$avatar  = get_sub_field( 'author_image' );
							$av_url  = ( is_array( $avatar ) && ! empty( $avatar['url'] ) ) ? $avatar['url'] : '';
						?>
							<div class="tms-testimonial-item">
								<div class="tms-testimonial-card">
									<div class="tms-testimonial-card__quote-mark">
										<img src="<?php echo esc_url( get_template_directory_uri() . '/img/quote.png' ); ?>" alt="quote" width="140" height="140">
									</div>
									<div class="tms-testimonial-card__inner">
										<?php if ( $q_title ) : ?>
											<h3 class="tms-testimonial-card__quote-title"><?php echo esc_html( $q_title ); ?></h3>
										<?php endif; ?>
										<?php if ( $q_body ) : ?>
											<div class="tms-testimonial-card__content tms-wysiwyg"><?php echo wp_kses_post( $q_body ); ?></div>
										<?php endif; ?>
										<div class="tms-testimonial-card__author">
											<div class="tms-testimonial-card__avatar">
												<?php if ( $av_url ) : ?>
													<img src="<?php echo esc_url( $av_url ); ?>" alt="<?php echo esc_attr( $author ); ?>" width="58" height="58">
												<?php else : ?>
													<div class="tms-avatar-placeholder"><i class="fa fa-user" aria-hidden="true"></i></div>
												<?php endif; ?>
											</div>
											<div class="tms-testimonial-card__info">
												<strong class="tms-testimonial-card__name"><?php echo esc_html( $author ? $author : __( 'Anonymous', 'tms' ) ); ?></strong>
												<?php if ( $role ) : ?>
													<span class="tms-testimonial-card__role"><?php echo esc_html( $role ); ?></span>
												<?php endif; ?>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php 
						endwhile; 
						?>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php

	endif; // end of layout conditions.

endwhile;
