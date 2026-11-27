<div class="contact-right-side">
	<h3>Contact Us</h3>
	<br>
	<?php
	$company  = get_field( 'global_company_name', 'option' );
	$address  = get_field( 'global_address', 'option' );
	$map_link = get_field( 'global_address_map_link', 'option' );
	?>
	<p class="tms-contact-address">
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
	</p>
	<br>
	<?php
	$phone = get_field( 'global_phone_number', 'option' );
	$clean_phone = preg_replace( '/[^0-9]/', '', $phone );
	if ( $phone ) :
	?>
	<p class="tms-tel">
		<span class="tms-contact-label"><?php esc_html_e( 'Tel:', 'tms' ); ?></span>
		<a href="tel:<?php echo esc_attr( $clean_phone ); ?>"><?php echo esc_html( $phone ); ?></a>
	</p>
	<?php endif; ?>

	<?php
	$fax = get_field( 'global_fax', 'option' );
	if ( $fax ) :
	?>
	<p class="tms-fax">
		<span class="tms-contact-label"><?php esc_html_e( 'Fax:', 'tms' ); ?></span>
		<span><?php echo esc_html( $fax ); ?></span>
	</p>
	<?php endif; ?>

	<?php
	$email = get_field( 'global_email', 'option' );
	if ( $email ) :
	?>
	<p class="tms-email">
		<span class="tms-contact-label"><?php esc_html_e( 'Email:', 'tms' ); ?></span>
		<a href="mailto:<?php echo esc_attr( $email ); ?>" class="tms-contact-email"><?php echo esc_html( $email ); ?></a>
	</p>
	<?php endif; ?>
</div>