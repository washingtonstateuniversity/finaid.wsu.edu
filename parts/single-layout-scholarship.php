<?php
/**
 * Template for the scholarship post type provided by the Scholarships plugin.
 *
 * @since 0.0.3
 */
$options = get_option( 'scholarships_settings' );
if ( $options && isset( $options['search_page'] ) ) {
	$search_page_url = get_permalink( $options['search_page'] );
}

if ( $search_page_url ) { ?>
<section class="row single gutter scholarships-back-to-results">

	<div class="column one">
		<p class="back-to-results">
			<a href="<?php echo esc_url( $search_page_url ); ?>">« Scholarship Search Results</a>
		</p>

		<?php get_template_part( 'parts/share-tools' ); ?>
	</div>

</section>
<?php }

while ( have_posts() ) : the_post();

	$deadline = get_post_meta( get_the_ID(), 'scholarship_deadline', true );
	$amount = get_post_meta( get_the_ID(), 'scholarship_amount', true );
	$paper = get_post_meta( get_the_ID(), 'scholarship_app_paper', true );
	$online = get_post_meta( get_the_ID(), 'scholarship_app_online', true );
	$site = get_post_meta( get_the_ID(), 'scholarship_site', true );
	$email = get_post_meta( get_the_ID(), 'scholarship_email', true );
	$phone = get_post_meta( get_the_ID(), 'scholarship_phone', true );
	$address = get_post_meta( get_the_ID(), 'scholarship_address', true );
	$org_name = get_post_meta( get_the_ID(), 'scholarship_org_name', true );
	$org = get_post_meta( get_the_ID(), 'scholarship_org', true );
	$org_site = get_post_meta( get_the_ID(), 'scholarship_org_site', true );
	$org_email = get_post_meta( get_the_ID(), 'scholarship_org_email', true );
	$org_phone = get_post_meta( get_the_ID(), 'scholarship_org_phone', true );
	?>

	<section class="row side-right gutter pad-top no-bottom-border">

		<div class="column one">
			<h1 class="article-title"><?php the_title(); ?></h1>
		</div>

		<div class="column two">
			<?php if ( $site ) { ?>
			<p class="apply">
				<a class="cta-button" target="_blank" href="<?php echo esc_url( $site ); ?>">Apply</a>
			</p>
			<?php } ?>
		</div>

	</section>

	<section class="row side-right gutter pad-bottom no-bottom-border">

		<div class="column one">
			<?php the_content(); ?>

			<?php
			if ( $deadline ) {
				$date = DateTime::createFromFormat( 'Y-m-d', $deadline );
				$deadline_display = ( $date instanceof DateTime ) ? $date->format( 'm/d/Y' ) : $deadline;
				?><p><strong>Deadline:</strong> <?php echo esc_html( $deadline_display ); ?></p><?php
			}

			if ( $amount ) {
				$amount_pieces = explode( '-', $amount );
				$numeric_amount = str_replace( ',', '', $amount_pieces[0] );
				$prepend = ( is_numeric( $numeric_amount ) ) ? '$' : '';
				?><p><strong>Amount:</strong> <?php echo esc_html( $prepend . $amount ); ?></p><?php
			}

			if ( $paper ) {
				?><p><strong>Paper Application Available</strong></p><?php
			}

			if ( $online ) {
				?><p><strong>Online Application Available</strong></p><?php
			}

			if ( $site || $email || $phone || $address ) {
				?>
				<p><strong>Contact information:</strong></p>
				<ul>
				<?php

				if ( $site ) {
					?><li><a href="<?php echo esc_url( $site ); ?>"><?php echo esc_html( $site ); ?></a></li><?php
				}

				if ( $email ) {
					?><li><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li><?php
				}

				if ( $phone ) {
					?><li><?php echo esc_html( $phone ); ?></li><?php
				}

				if ( $address ) {
					?><li><?php echo esc_html( $address ); ?></li><?php
				}

				?></ul><?php
			}

			if ( $org_name || $org || $org_site || $org_email || $org_phone ) {
				$granter = ( $org_name ) ? $org_name : 'the granter';
				?>
				<p><strong>About <?php echo esc_html( $granter ); ?></strong></p>
				<?php

				if ( $org ) {
					echo wp_kses_post( wpautop( $org ) );
				}

				?>
				<ul>
				<?php

				if ( $org_site ) {
					?><li><strong>Web:</strong> <a href="<?php echo esc_url( $org_site ); ?>"><?php echo esc_html( $org_site ); ?></a></li><?php
				}

				if ( $org_email ) {
					?><li><strong>Email:</strong> <a href="mailto:<?php echo esc_attr( $org_email ); ?>"><?php echo esc_html( $org_email ); ?></a></li><?php
				}

				if ( $org_phone ) {
					?><li><strong>Phone:</strong> <?php echo esc_html( $org_phone ); ?></li><?php
				}

				?></ul><?php
			}
			?>
		</div>

		<div class="column two"></div>

	</section>

<?php endwhile; ?>
