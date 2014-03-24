<?php get_header( 'buddypress' ); ?>

	<div id="content">
		<div class="padder">

		<?php do_action( 'bp_before_activation_page' ); ?>

		<div class="page" id="activate-page">

			<h3><?php if ( bp_account_was_activated() ) :
				_e( 'Account Verified', 'buddypress' );
			else :
				_e( 'Verify your Account', 'buddypress' );
			endif; ?></h3>

			<?php do_action( 'template_notices' ); ?>

			<?php do_action( 'bp_before_activate_content' ); ?>

			<?php if ( bp_account_was_activated() ) : ?>

				<?php if ( isset( $_GET['e'] ) ) : ?>
					<p><?php _e( 'Your account was verified successfully! Your account details have been sent to you in a separate email.', 'buddypress' ); ?></p>
				<?php else : ?>
					<p><?php _e( 'Your account was verified successfully! You are now on a waiting list. Once an administrator has approved your account you will be notified via email.', 'buddypress' ); ?></p>
				<?php endif; ?>

			<?php else : ?>

				<p><?php _e( 'Please provide a valid verification key.', 'buddypress' ); ?></p>

				<form action="" method="get" class="standard-form" id="activation-form">

					<label for="key"><?php _e( 'Verification Key:', 'buddypress' ); ?></label>
					<input type="text" name="key" id="key" value="" />

					<p class="submit">
						<input type="submit" name="submit" value="<?php _e( 'Activate', 'buddypress' ); ?>" />
					</p>

				</form>

			<?php endif; ?>

			<?php do_action( 'bp_after_activate_content' ); ?>

		</div><!-- .page -->

		<?php do_action( 'bp_after_activation_page' ); ?>

		</div><!-- .padder -->
	</div><!-- #content -->

	<?php get_sidebar( 'buddypress' ); ?>

<?php get_footer( 'buddypress' ); ?>
