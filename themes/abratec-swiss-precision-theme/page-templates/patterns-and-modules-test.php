<?php
/**
 * Template Name: Patterns and Modules Test
 *
 * Test and showcase template for RPM theme patterns, typography, components, and modules.
 * Faithfully ported from RPM Framework v7.1.5 (rpmthemenew.wpengine.com)
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

$theme_uri = get_stylesheet_directory_uri();
?>

<div class="wrapper patterns-test-wrapper" id="page-wrapper">

	<div id="content" class="patterns-content-area" tabindex="-1">

		<main class="site-main" id="main" role="main">

			<!-- Page Intro Header -->
			<div class="page-intro">
				<div class="inner-wrap">
					<div class="pi-wrap">
						<h1 class="pi-heading">Patterns and Modules Test</h1>
					</div>
				</div>
			</div>

			<!-- Breadcrumbs -->
			<div id="breadcrumbs" class="breadcrumb-menu">
				<div class="inner-wrap">
					<span>
						<span><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></span> &gt;
						<span class="breadcrumb_last" aria-current="page">Patterns and Modules Test</span>
					</span>
				</div>
			</div>

			<!-- Main Showcase Content -->
			<section class="site-content patterns-and-modules-test" role="main">

				<!-- 1. Color Palette & Typography -->
				<div class="inner-wrap">
					<article class="site-content-primary">
						<div class="color-pallet">
							<h2>Color Usage</h2>
							<div class="cp-wrap">
								<div class="cp-item">
									<div class="cp-color" style="background-color: #b11226;"></div>
									<div class="cp-content"><span class="cp-title">Primary</span>HEX: #B11226 <br> RGB: 177 18 38</div>
								</div>
								<div class="cp-item">
									<div class="cp-color" style="background-color: #a91817;"></div>
									<div class="cp-content"><span class="cp-title">Secondary</span>HEX: #A91817 <br> RGB: 169 24 23</div>
								</div>
								<div class="cp-item">
									<div class="cp-color" style="background-color: #cc2222;"></div>
									<div class="cp-content"><span class="cp-title">Accent</span>HEX: #CC2222 <br> RGB: 204 34 34</div>
								</div>
								<div class="cp-item">
									<div class="cp-color" style="background-color: #111827;"></div>
									<div class="cp-content"><span class="cp-title">Neutral</span>HEX: #111827 <br> RGB: 17 24 39</div>
								</div>
							</div>
							<hr>
						</div>

						<div class="typo-wrap">
							<div>
								<h2>Typography</h2>
							</div>
							<div>
								<p class="tw-title"><span>Header Font Family</span>: Roboto Condensed</p>
							</div>
							<div>
								<p class="tw-title"><span>Body Font Family</span>: Roboto</p>
							</div>
						</div>
					</article>
				</div>

				<section class="additional-content">

					<!-- 2. Heading with Background (50/50 Light/Dark) -->
					<div class="heading-wrap">
						<div class="inner-wrap">
							<div class="rows-of-2">
								<div class="on-light-bg">
									<div class="hw-txt">
										<h1 class="lb-title">THIS IS AN H1</h1>
										<h2>This is a light Background</h2>
										<p>Maecenas ante purus, laoreet sed venenatis et, dignissim vel erat. Donec nec diam sed turpis tempus luctus sit amet eu ipsum. Nullam libero dolor, rhoncus in mi ut, ullamcorper consequat nulla. Nullam blandit nisi vitae rutrum dapibus.</p>
									</div>
								</div>
								<div class="on-color-bg">
									<div class="hw-txt">
										<h1>THIS IS AN H1</h1>
										<h2>This is a Dark Background</h2>
										<p>Maecenas ante purus, laoreet sed venenatis et, dignissim vel erat. Donec nec diam sed turpis tempus luctus sit amet eu ipsum. Nullam libero dolor, rhoncus in mi ut, ullamcorper consequat nulla. Nullam blandit nisi vitae rutrum dapibus.</p>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- 3. Typography Hierarchy & Lists -->
					<section class="multiple-cols-module">
						<div class="inner-wrap">
							<section class="">
								<div>
									<h2>This is an H2</h2>
									<p>Maecenas ante purus, laoreet sed venenatis et, dignissim vel erat. Donec nec diam sed turpis tempus luctus sit amet eu ipsum. Nullam libero dolor, rhoncus in mi ut, ullamcorper consequat nulla. Nullam blandit nisi vitae rutrum dapibus.</p>
									<h3>This is an h3</h3>
									<p>Maecenas ante purus, laoreet sed venenatis et, dignissim vel erat. Donec nec diam sed turpis tempus luctus sit amet eu ipsum. Nullam libero dolor, rhoncus in mi ut, ullamcorper consequat nulla. Nullam blandit nisi vitae rutrum dapibus.</p>
									<h4>This is an h4</h4>
									<p>Maecenas ante purus, laoreet sed venenatis et, dignissim vel erat. Donec nec diam sed turpis tempus luctus sit amet eu ipsum. Nullam libero dolor, rhoncus in mi ut, ullamcorper consequat nulla. Nullam blandit nisi vitae rutrum dapibus.</p>
									<h5>This is an h5</h5>
									<p>Maecenas ante purus, laoreet sed venenatis et, dignissim vel erat. Donec nec diam sed turpis tempus luctus sit amet eu ipsum. Nullam libero dolor, rhoncus in mi ut, ullamcorper consequat nulla. Nullam blandit nisi vitae rutrum dapibus.</p>
									<h6>This is an h6</h6>
									<p>Maecenas ante purus, laoreet sed venenatis et, dignissim vel erat. Donec nec diam sed turpis tempus luctus sit amet eu ipsum. Nullam libero dolor, rhoncus in mi ut, ullamcorper consequat nulla. Nullam blandit nisi vitae rutrum dapibus.</p>
									<p class="emph">Emphasized text porta erat id dictum. In ultricies, nisl id suscipit pulvinar, mauris tellus feugiat lorem, a rutrum turpis est in odio. Phasellus est eros, rutrum sit amet rutrum ut, dignissim a tellus.</p>
									<hr>
									<div class="rows-of-2">
										<div>
											<h2>Unorganized List</h2>
											<ul>
												<li>This is an unorganized list item lvl 1
													<ul>
														<li>This is a second level unorganized list item lvl 2</li>
														<li>This is a second level unorganized list item lvl 2</li>
													</ul>
												</li>
												<li>This is an unorganized list item lvl 1</li>
												<li>This is an unorganized list item lvl 1
													<ul>
														<li>This is a second level unorganized list item lvl 2</li>
													</ul>
												</li>
											</ul>
										</div>
										<div>
											<h2>Organized List</h2>
											<ol>
												<li>This is an organized list item lvl 1
													<ol>
														<li>This is a second level organized list item lvl 2</li>
														<li>This is a second level organized list item lvl 2</li>
													</ol>
												</li>
												<li>This is an organized list item lvl 1</li>
												<li>This is an organized list item lvl 1
													<ol>
														<li>This is a second level organized list item lvl 2</li>
													</ol>
												</li>
											</ol>
										</div>
									</div>
									<hr>
								</div>
							</section>
						</div>
					</section>

					<!-- 4. Image Gallery With Thumbnails -->
					<section class="image-gallery-with-thumbs">
						<div class="inner-wrap">
							<h2><span>Image Gallery Example With Thumbnails</span></h2>
							<div class="innerpage-carousel">
								<div id="slider0" class="slides slider-for popup-gallery">
									<div class="item">
										<a href="https://placehold.co/800x533?text=Image+1">
											<img src="https://placehold.co/800x533?text=Image+1" alt="Gallery Image 1" title="dest-slider-01">
										</a>
									</div>
									<div class="item">
										<a href="https://placehold.co/800x533?text=Image+2">
											<img src="https://placehold.co/800x533?text=Image+2" alt="Gallery Image 2" title="dest-slider-02">
										</a>
									</div>
									<div class="item">
										<a href="https://placehold.co/800x533?text=Image+3">
											<img src="https://placehold.co/800x533?text=Image+3" alt="Gallery Image 3" title="dest-slider-03">
										</a>
									</div>
									<div class="item">
										<a href="https://placehold.co/800x533?text=Image+4">
											<img src="https://placehold.co/800x533?text=Image+4" alt="Gallery Image 4" title="dest-slider-04">
										</a>
									</div>
								</div>
								<div id="sliderNav0" class="slides slider-nav">
									<div class="item">
										<a href="javascript:void(0)" class="slider-nav-item">
											<img src="https://placehold.co/150x100?text=Image+1" alt="Thumb 1">
										</a>
									</div>
									<div class="item">
										<a href="javascript:void(0)" class="slider-nav-item">
											<img src="https://placehold.co/150x100?text=Image+2" alt="Thumb 2">
										</a>
									</div>
									<div class="item">
										<a href="javascript:void(0)" class="slider-nav-item">
											<img src="https://placehold.co/150x100?text=Image+3" alt="Thumb 3">
										</a>
									</div>
									<div class="item">
										<a href="javascript:void(0)" class="slider-nav-item">
											<img src="https://placehold.co/150x100?text=Image+4" alt="Thumb 4">
										</a>
									</div>
								</div>
							</div>
							<hr>
						</div>
					</section>

					<!-- 5. Image Carousel Without Thumbnails -->
					<div class="inner-wrap">
						<section class="innerpage-carousel-widthout-thumb">
							<div class="icwt-slider popup-gallery">
								<div class="item">
									<a href="https://placehold.co/800x533?text=Image+1">
										<img src="https://placehold.co/800x533?text=Image+1" alt="Slide 1">
									</a>
								</div>
								<div class="item">
									<a href="https://placehold.co/800x533?text=Image+2">
										<img src="https://placehold.co/800x533?text=Image+2" alt="Slide 2">
									</a>
								</div>
								<div class="item">
									<a href="https://placehold.co/800x533?text=Image+3">
										<img src="https://placehold.co/800x533?text=Image+3" alt="Slide 3">
									</a>
								</div>
							</div>
						</section>
					</div>

					<!-- 6. One Column Content Test -->
					<section class="multiple-cols-module">
						<div class="inner-wrap">
							<h2>One Column Content Test</h2>
							<p class="column-subtext">Subtext Test</p>
							<section class="">
								<div>
									<div id="lipsum">
										<p>Quisque sit amet ullamcorper ante, nec gravida nisi. Sed consequat, elit id ornare volutpat, libero mauris blandit turpis, eget faucibus purus justo nec dui. Fusce facilisis ultrices diam, ut vehicula mauris scelerisque vel. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Integer sagittis quis lectus quis vehicula. Nunc elementum lacinia turpis a blandit. Cras imperdiet diam sit amet iaculis ultrices. Nulla congue ipsum sed vulputate convallis. Curabitur malesuada finibus porta. Morbi ultrices eu justo sit amet imperdiet. Aenean ut elit ultrices, sodales est in, auctor justo. Etiam sed imperdiet nibh, vitae pulvinar mi. Sed hendrerit sodales molestie. Nunc non facilisis lorem, ac convallis eros.</p>
									</div>
								</div>
							</section>
							<hr>
						</div>
					</section>

					<!-- 7. Two Column Content Test -->
					<section class="multiple-cols-module">
						<div class="inner-wrap">
							<h2>Two Column Content Test</h2>
							<p class="column-subtext">Subtext Test</p>
							<section class="rows-of-2">
								<div>
									<div id="lipsum">
										<p>Curabitur malesuada finibus porta. Morbi ultrices eu justo sit amet imperdiet. Aenean ut elit ultrices, sodales est in, auctor justo. Etiam sed imperdiet nibh, vitae pulvinar mi. Sed hendrerit sodales molestie. Nunc non facilisis lorem, ac convallis eros.</p>
									</div>
								</div>
								<div>
									<div id="lipsum">
										<p>Curabitur malesuada finibus porta. Morbi ultrices eu justo sit amet imperdiet. Aenean ut elit ultrices, sodales est in, auctor justo. Etiam sed imperdiet nibh, vitae pulvinar mi. Sed hendrerit sodales molestie. Nunc non facilisis lorem, ac convallis eros.</p>
									</div>
								</div>
							</section>
							<hr>
						</div>
					</section>

					<!-- 8. Three Column Content Test -->
					<section class="multiple-cols-module">
						<div class="inner-wrap">
							<h2>Three Column Content Test</h2>
							<p class="column-subtext">Subtext Test</p>
							<section class="rows-of-3">
								<div>
									<div id="lipsum">
										<p>Curabitur malesuada finibus porta. Morbi ultrices eu justo sit amet imperdiet. Aenean ut elit ultrices, sodales est in, auctor justo. Etiam sed imperdiet nibh, vitae pulvinar mi. Sed hendrerit sodales molestie. Nunc non facilisis lorem, ac convallis eros.</p>
									</div>
								</div>
								<div>
									<div id="lipsum">
										<p>Curabitur malesuada finibus porta. Morbi ultrices eu justo sit amet imperdiet. Aenean ut elit ultrices, sodales est in, auctor justo. Etiam sed imperdiet nibh, vitae pulvinar mi. Sed hendrerit sodales molestie. Nunc non facilisis lorem, ac convallis eros.</p>
									</div>
								</div>
								<div>
									<div id="lipsum">
										<p>Curabitur malesuada finibus porta. Morbi ultrices eu justo sit amet imperdiet. Aenean ut elit ultrices, sodales est in, auctor justo. Etiam sed imperdiet nibh, vitae pulvinar mi. Sed hendrerit sodales molestie. Nunc non facilisis lorem, ac convallis eros.</p>
									</div>
								</div>
							</section>
							<hr>
						</div>
					</section>

					<!-- 9. Four Column Content Test -->
					<section class="multiple-cols-module">
						<div class="inner-wrap">
							<h2>Four Column Content Test</h2>
							<p class="column-subtext">Subtext Test</p>
							<section class="rows-of-4">
								<div>
									<div id="lipsum">
										<p>Curabitur malesuada finibus porta. Morbi ultrices eu justo sit amet imperdiet. Aenean ut elit ultrices, sodales est in, auctor justo. Etiam sed imperdiet nibh, vitae pulvinar mi. Sed hendrerit sodales molestie. Nunc non facilisis lorem, ac convallis eros.</p>
									</div>
								</div>
								<div>
									<div id="lipsum">
										<p>Curabitur malesuada finibus porta. Morbi ultrices eu justo sit amet imperdiet. Aenean ut elit ultrices, sodales est in, auctor justo. Etiam sed imperdiet nibh, vitae pulvinar mi. Sed hendrerit sodales molestie. Nunc non facilisis lorem, ac convallis eros.</p>
									</div>
								</div>
								<div>
									<div id="lipsum">
										<p>Curabitur malesuada finibus porta. Morbi ultrices eu justo sit amet imperdiet. Aenean ut elit ultrices, sodales est in, auctor justo. Etiam sed imperdiet nibh, vitae pulvinar mi. Sed hendrerit sodales molestie. Nunc non facilisis lorem, ac convallis eros.</p>
									</div>
								</div>
								<div>
									<div id="lipsum">
										<p>Curabitur malesuada finibus porta. Morbi ultrices eu justo sit amet imperdiet. Aenean ut elit ultrices, sodales est in, auctor justo. Etiam sed imperdiet nibh, vitae pulvinar mi. Sed hendrerit sodales molestie. Nunc non facilisis lorem, ac convallis eros.</p>
									</div>
								</div>
							</section>
							<hr>
						</div>
					</section>

					<!-- 10. Navigation & Icons Library -->
					<section class="multiple-cols-module">
						<div class="inner-wrap">
							<h2>Navigation</h2>
							<section class="">
								<div>
									<div class="icn-nav">
										<div class="in-item">
											<div class="in-img">
												<figure><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#333333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg></figure>
											</div>
											<div class="in-title">ico-mail</div>
										</div>
										<div class="in-item">
											<div class="in-img">
												<figure><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#333333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></figure>
											</div>
											<div class="in-title">ico-phone</div>
										</div>
										<div class="in-item">
											<div class="in-img">
												<figure><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#333333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg></figure>
											</div>
											<div class="in-title">ico-download</div>
										</div>
										<div class="in-item">
											<div class="in-img">
												<figure><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#333333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg></figure>
											</div>
											<div class="in-title">ico-less</div>
										</div>
										<div class="in-item">
											<div class="in-img">
												<figure><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#333333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg></figure>
											</div>
											<div class="in-title">ico-arrow</div>
										</div>
										<div class="in-item">
											<div class="in-img">
												<figure><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#333333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg></figure>
											</div>
											<div class="in-title">ico-more</div>
										</div>
										<div class="in-item">
											<div class="in-img">
												<figure><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#333333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></figure>
											</div>
											<div class="in-title">search</div>
										</div>
										<div class="in-item">
											<div class="in-img">
												<figure><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#333333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></figure>
											</div>
											<div class="in-title">ico-arrow2</div>
										</div>
										<div class="in-item">
											<div class="in-img">
												<figure><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#333333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg></figure>
											</div>
											<div class="in-title">ico-dropdown</div>
										</div>
									</div>
									<p>(24px default size)</p>
									<div id="breadcrumbs" class="breadcrumb-menu"><a href="#">Home</a> &gt; <span class="breadcrumb_last">Breadcrumbs</span></div>
								</div>
							</section>
						</div>
					</section>

					<!-- 11. Accordion Tabs Module -->
					<section class="accordian-tabs-module">
						<div class="inner-wrap">
							<h2>Tab Content Test</h2>
							<p class="column-subtext">Tab content subtext test</p>
							<ul class="accordion-tabs">
								<li class="tab-header-and-content">
									<a href="javascript:void(0)" class="tab-link is-active">Tab One</a>
									<div class="tab-content is-open" style="display: block;">
										<p>Donec mattis mauris gravida metus laoreet non rutrum sem viverra. Aenean nibh libero, viverra vel vestibulum in, porttitor ut sapien. Phasellus tempor lorem id justo ornare tincidunt. Nulla faucibus, purus eu placerat fermentum, velit mi iaculis nunc, bibendum tincidunt ipsum justo eu mauris. Nulla facilisi. Vestibulum vel lectus ac purus tempus suscipit nec sit amet eros. Nullam fringilla, enim eu lobortis dapibus, quam magna tincidunt nibh, sit amet imperdiet dolor justo congue turpis.</p>
									</div>
								</li>
								<li class="tab-header-and-content">
									<a href="javascript:void(0)" class="tab-link">Tab Two</a>
									<div class="tab-content">
										<p>Phasellus tempor lorem id justo ornare tincidunt. Nulla faucibus, purus eu placerat fermentum, velit mi iaculis nunc, bibendum tincidunt ipsum justo eu mauris. Nulla facilisi. Vestibulum vel lectus ac purus tempus suscipit nec sit amet eros.</p>
									</div>
								</li>
								<li class="tab-header-and-content">
									<a href="javascript:void(0)" class="tab-link">Tab Three</a>
									<div class="tab-content">
										<p>Aenean nibh libero, viverra vel vestibulum in, porttitor ut sapien. Donec mattis mauris gravida metus laoreet non rutrum sem viverra. Vestibulum vel lectus ac purus tempus suscipit nec sit amet eros.</p>
									</div>
								</li>
							</ul>
							<hr>
						</div>
					</section>

					<!-- 12. Click to Expand (Accordions) -->
					<section class="click-expand-module">
						<div class="inner-wrap">
							<div class="click-expand">
								<h3 class="ce-header" tabindex="0">Click to Expand (accordion) Test 1</h3>
								<div class="ce-body">
									<p>Donec mattis mauris gravida metus laoreet non rutrum sem viverra. Aenean nibh libero, viverra vel vestibulum in, porttitor ut sapien. Phasellus tempor lorem id justo ornare tincidunt. Nulla faucibus, purus eu placerat fermentum, velit mi iaculis nunc, bibendum tincidunt ipsum justo eu mauris.</p>
								</div>
							</div>
						</div>
					</section>

					<section class="click-expand-module">
						<div class="inner-wrap">
							<div class="click-expand">
								<h3 class="ce-header" tabindex="0">Click to Expand (accordion) Test 2</h3>
								<div class="ce-body">
									<p>Vestibulum vel lectus ac purus tempus suscipit nec sit amet eros. Nullam fringilla, enim eu lobortis dapibus, quam magna tincidunt nibh, sit amet imperdiet dolor justo congue turpis.</p>
								</div>
							</div>
						</div>
					</section>

					<!-- 13. Table Test (TablePress Styling) -->
					<section class="multiple-cols-module">
						<div class="inner-wrap">
							<h2>Table Test</h2>
							<p class="column-subtext">Table subtext test</p>
							<section class="">
								<div>
									<div class="dt-container table-responsive">
										<table id="tablepress-1" class="tablepress tablepress-id-1 dataTable" style="width: 100%;">
											<thead>
												<tr class="row-1">
													<th class="column-1">mm</th>
													<th class="column-2">inches</th>
													<th class="column-3">Series</th>
													<th class="column-4">Description</th>
													<th class="column-5">CFM</th>
													<th class="column-6">inH2O</th>
													<th class="column-7">dBA</th>
												</tr>
											</thead>
											<tbody class="row-striping row-hover">
												<tr class="row-2"><td class="column-1">254 x 89</td><td class="column-2">10.0 x 3.5</td><td class="column-3">RA2589-C</td><td class="column-4">High Air Flow</td><td class="column-5">423-850</td><td class="column-6">0.35-0.83</td><td class="column-7">52-88</td></tr>
												<tr class="row-3"><td class="column-1">222 x 60</td><td class="column-2">8.7 x 2.4</td><td class="column-3">RA2589-C</td><td class="column-4">High Air Flow</td><td class="column-5">430-485</td><td class="column-6">0.71-0.94</td><td class="column-7">59-62</td></tr>
												<tr class="row-4"><td class="column-1">172 x 51</td><td class="column-2">6.8 x 2.0</td><td class="column-3">RA2589-C</td><td class="column-4">High Air Flow</td><td class="column-5">250-293</td><td class="column-6">0.63-0.79</td><td class="column-7">56-61</td></tr>
												<tr class="row-5"><td class="column-1">172 x 51</td><td class="column-2">6.8 x 2.0</td><td class="column-3">RA2589-C</td><td class="column-4">Standard</td><td class="column-5">133-187</td><td class="column-6">0.15-0.55</td><td class="column-7">45-55</td></tr>
												<tr class="row-6"><td class="column-1">172 x 150 x 51</td><td class="column-2">6.8 x 5.9 x 2.0</td><td class="column-3">RA2589-C</td><td class="column-4">High Air Flow</td><td class="column-5">250-293</td><td class="column-6">0.63-0.79</td><td class="column-7">56-61</td></tr>
												<tr class="row-7"><td class="column-1">172 x 150 x 51</td><td class="column-2">6.8 x 5.9 x 2.0</td><td class="column-3">RA2589-C</td><td class="column-4">Standard</td><td class="column-5">186-240</td><td class="column-6">0.22-0.37</td><td class="column-7">48-53</td></tr>
												<tr class="row-8"><td class="column-1">172 x 150 x 51</td><td class="column-2">6.8 x 5.9 x 2.0</td><td class="column-3">RA2589-C</td><td class="column-4">Low Air Flow</td><td class="column-5">161-228</td><td class="column-6">0.32-0.53</td><td class="column-7">40-49</td></tr>
												<tr class="row-9"><td class="column-1">172 x 150 x 38</td><td class="column-2">6.8 x 5.9 x 1.5</td><td class="column-3">RA2589-C</td><td class="column-4">High Air Flow</td><td class="column-5">166-198</td><td class="column-6">0.66-0.77</td><td class="column-7">57-61</td></tr>
												<tr class="row-10"><td class="column-1">172 x 150 x 38</td><td class="column-2">6.8 x 5.9 x 2.2</td><td class="column-3">RA2589-C</td><td class="column-4">High Air Flow</td><td class="column-5">250-293</td><td class="column-6">0.63-0.79</td><td class="column-7">56-61</td></tr>
											</tbody>
										</table>
									</div>
								</div>
							</section>
							<hr>
						</div>
					</section>

					<!-- 14. Text & Media Test -->
					<section class="text-media-module">
						<div class="inner-wrap">
							<h2>Text &amp; Media Test</h2>
							<div class="rows-of-2">
								<div>
									<p><a class="lightbox" href="https://placehold.co/800x533?text=Text+%26+Media"><img decoding="async" class="media-img" title="Media Image" src="https://placehold.co/800x533?text=Text+%26+Media" alt="Media Image" width="100%"></a></p>
								</div>
								<div>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris faucibus risus dolor, vel rhoncus metus feugiat eget. Ut volutpat vel libero eget volutpat. Nam blandit pellentesque placerat. Phasellus at faucibus lectus. Aenean pretium nisi pulvinar mi euismod, et placerat metus condimentum. Vestibulum ut ultricies nunc. Etiam euismod, enim eget malesuada lobortis, ante purus pulvinar risus, at <a href="#">blandit libero arcu quis elit</a>. Integer enim leo.</p>
								</div>
							</div>
							<hr>
						</div>
					</section>

					<!-- 15. Full Width CTA Test -->
					<section class="full-width-cta-test">
						<div class="inner-wrap"><h2 class="cta-banner-header">Full Width CTA Test</h2></div>
						<section class="fwc-module">
							<div class="inner-wrap">
								<div class="row cta-banner bottom-baseline">
									<p class="h2 cta-banner-body">Maecenas ante purus, laoreet sed venenatis et, dignissim vel erat.</p>
									<a class="btn fw-cta" href="#"><span>Learn More</span></a>
								</div>
							</div>
						</section>
						<div class="inner-wrap"><hr></div>
					</section>

					<!-- 16. Button Style -->
					<section class="multiple-cols-module">
						<div class="inner-wrap">
							<h2>Button Style</h2>
							<section class="">
								<div>
									<div class="btn-wrap">
										<div class="btn-wrap-wo-bg">
											<a class="btn" href="#">Primary</a>
											<a class="btn-alt" href="#">Secondary</a>
										</div>
										<div class="btn-wrap-bg">
											<a class="btn btn-color" href="#"><span>On Color Primary</span></a>
											<a class="btn btn-alt-color" href="#"><span>On Color Secondary</span></a>
										</div>
									</div>
								</div>
							</section>
							<hr>
						</div>
					</section>

					<!-- 17. Product Grid Module -->
					<section class="product-grid-module">
						<div class="inner-wrap">
							<h2 class="carousel-header">Product Grid Test</h2>
							<div class="product-item-wap">
								<div class="product-items">
									<div>
										<a class="product-item" href="#">
											<span class="product-img">
												<img class="pmi-img" src="https://placehold.co/400x300?text=Product+1" alt="Product 1" title="Product 1">
											</span>
											<span class="product-title">Learn More</span>
										</a>
									</div>
									<div>
										<a class="product-item" href="#">
											<span class="product-img">
												<img class="pmi-img" src="https://placehold.co/400x300?text=Product+2" alt="Product 2" title="Product 2">
											</span>
											<span class="product-title">Learn More</span>
										</a>
									</div>
									<div>
										<a class="product-item" href="#">
											<span class="product-img">
												<img class="pmi-img" src="https://placehold.co/400x300?text=Product+3" alt="Product 3" title="Product 3">
											</span>
											<span class="product-title">Learn More</span>
										</a>
									</div>
									<div>
										<a class="product-item" href="#">
											<span class="product-img">
												<img class="pmi-img" src="https://placehold.co/400x300?text=Product+4" alt="Product 4" title="Product 4">
											</span>
											<span class="product-title">Learn More</span>
										</a>
									</div>
								</div>
							</div>
							<hr>
						</div>
					</section>

					<!-- 18. Form Test, Side Nav, & Dropdown Test -->
					<section class="multiple-cols-module">
						<div class="inner-wrap">
							<section class="rows-of-3">
								<!-- Form Test -->
								<div>
									<div class="test-form">
										<h2>Form Test</h2>
										<?php
										$test_form_id = function_exists( 'abratec_get_or_create_patterns_test_form' ) ? abratec_get_or_create_patterns_test_form() : false;
										if ( $test_form_id && function_exists( 'gravity_form' ) ) {
											gravity_form( $test_form_id, false, false, false, '', true );
										} elseif ( function_exists( 'gravity_form' ) ) {
											gravity_form( 1, false, false, false, '', true );
										} else {
										?>
										<div class="gform_wrapper gravity-theme" id="gform_wrapper_3">
											<form method="post" action="#">
												<div class="gform-body gform_body">
													<div class="gform_fields top_label">
														<div class="gfield gfield--type-text">
															<label class="gfield_label" for="input_3_8">First Name <span class="gfield_required">*</span></label>
															<div class="ginput_container"><input name="input_8" id="input_3_8" type="text" class="large" required></div>
														</div>
														<div class="gfield gfield--type-text">
															<label class="gfield_label" for="input_3_10">Last Name <span class="gfield_required">*</span></label>
															<div class="ginput_container"><input name="input_10" id="input_3_10" type="text" class="large" required></div>
														</div>
														<div class="gfield gfield--type-email">
															<label class="gfield_label" for="input_3_2">Email <span class="gfield_required">*</span></label>
															<div class="ginput_container"><input name="input_2" id="input_3_2" type="email" class="large" required></div>
														</div>
														<div class="gfield gfield--type-text">
															<label class="gfield_label" for="input_3_3">Company Name <span class="gfield_required">*</span></label>
															<div class="ginput_container"><input name="input_3" id="input_3_3" type="text" class="large" required></div>
														</div>
														<div class="gfield gfield--type-phone">
															<label class="gfield_label" for="input_3_7">Phone Number</label>
															<div class="ginput_container"><input name="input_7" id="input_3_7" type="tel" class="large"></div>
														</div>
														<div class="gfield gfield--type-select">
															<label class="gfield_label" for="input_3_11">Country <span class="gfield_required">*</span></label>
															<div class="ginput_container">
																<select name="input_11" id="input_3_11" class="large gfield_select">
																	<option value="United States" selected>United States</option>
																	<option value="Canada">Canada</option>
																	<option value="Switzerland">Switzerland</option>
																	<option value="Germany">Germany</option>
																	<option value="United Kingdom">United Kingdom</option>
																	<option value="France">France</option>
																	<option value="Japan">Japan</option>
																</select>
															</div>
														</div>
													</div>
												</div>
												<div class="gform-footer gform_footer top_label">
													<input type="submit" id="gform_submit_button_3" class="gform_button button" value="Submit">
												</div>
											</form>
										</div>
										<?php } ?>
									</div>
								</div>

								<!-- Side Nav. Test -->
								<div>
									<aside class="side-nav">
										<h2>Side Nav. Test</h2>
										<ul class="sn-nav">
											<li class="menu-item-has-children"><a href="javascript:void(0)">Item 1</a><span class="m-subnav-arrow"></span>
												<ul class="sub-menu">
													<li><a href="#">Sub Item 1.1</a></li>
													<li><a href="#">Sub Item 1.2</a></li>
												</ul>
											</li>
											<li><a href="#">Item 2</a></li>
											<li class="menu-item-has-children"><a href="javascript:void(0)">Item 3</a><span class="m-subnav-arrow"></span>
												<ul class="sub-menu">
													<li><a href="#">Sub Item 3.1</a></li>
													<li><a href="#">Sub Item 3.2</a></li>
												</ul>
											</li>
										</ul>
									</aside>
								</div>

								<!-- Dropdown Test -->
								<div>
									<aside class="side-nav">
										<h2>Dropdown Test</h2>
										<ul class="sn-nav">
											<li class="menu-item-has-children"><a href="javascript:void(0)">Title</a><span class="m-subnav-arrow"></span>
												<ul class="sub-menu">
													<li><a href="#">Dropdown Item A</a></li>
													<li><a href="#">Dropdown Item B</a></li>
													<li><a href="#">Dropdown Item C</a></li>
												</ul>
											</li>
										</ul>
									</aside>
								</div>
							</section>
							<hr>
						</div>
					</section>

					<!-- 19. Internal Page Footer CTA Module -->
					<section class="internal-page-cta-module">
						<div class="inner-wrap">
							<h2 class="hfwc-heading">Already Know what you need?</h2>
							<div class="hfwc-cta">
								<a class="btn" href="#"><span>Contact Us</span></a>
								<a class="btn-alt" href="#"><span>Request for Quote</span></a>
							</div>
						</div>
					</section>

				</section><!-- .additional-content -->

			</section><!-- .site-content -->

		</main><!-- #main -->

	</div><!-- #content -->

</div><!-- #page-wrapper -->

<?php get_footer(); ?>
