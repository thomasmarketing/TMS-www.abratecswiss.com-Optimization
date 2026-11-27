<?php if ( !is_front_page() && is_home() ):
	$id = intval(get_option( 'page_for_posts' ));
else: 
	$id = get_the_ID();
endif; ?>

<?php if ( get_field('pi_heading', $id)): ?>
	<div class="page-intro"
		<?php if (get_field('pi_bg', $id)) {
			echo 'style="background-image:url('.get_field('pi_bg', $id).')"';
		} ?>
	>
		<div class="container">
			<div class="pi-wrap">
				
				<?php if(is_home()):?>
			 		<h1 class="pi-heading">Blog</h1>

			 	<?php elseif(is_404()):?>
					<h1 class="pi-heading">Error 404: Page not found</h1>
				 	
				<?php elseif(is_author()):?>
					<h1 class="pi-heading"><?php echo get_the_author() ; ?></h1>

				<?php elseif(is_search()):?>
				 	<h1 class="pi-heading">Search Results for: <?php echo get_search_query(); ?></h1>

				<?php elseif(is_category()): ?>
					<h1 class="pi-heading"><?php echo single_cat_title( '', false ); ?></h1>

				<?php elseif(is_archive('demo-cpts')): ?>
					<h1 class="pi-heading">Demo CPT</h1>	

				<?php elseif(is_archive('post')): ?>

					<h1 class="pi-heading"><?php echo get_the_archive_title(); ?></h1>

				<?php elseif(get_field('pi_heading')):?>
					<h1 class="pi-heading"><?php echo get_field('pi_heading', $id);?></h1>

				<?php else: ?>
				 	<h1 class="pi-heading"><?php the_title(); ?></h1>
				<?php endif;?>

			</div>

		</div>
	</div>

	<?php
	if ( function_exists('yoast_breadcrumb') ) {
	  yoast_breadcrumb( '<div id="breadcrumbs" class="breadcrumb-menu"><div class="container">','</div></div>' );
	}
	?>
<?php endif ?>