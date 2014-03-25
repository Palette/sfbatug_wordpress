<div id="buddypress">

	<?php do_action( 'bp_before_activation_page' ); ?>

	<div class="page" id="activate-page">

		<?php do_action( 'template_notices' ); ?>

		<?php do_action( 'bp_before_activate_content' ); ?>

		<?php if ( bp_account_was_activated() ) : ?>

			<?php if ( isset( $_GET['e'] ) ) : ?>
				<p><?php _e( 'Your account was verified successfully! Your account details have been sent to an administrator for approval.', 'buddypress' ); ?></p>
			<?php else : ?>
				<p><?php printf( __( 'Your account was verified successfully! Your account details have been sent to an administrator for approval.', 'buddypress' ), wp_login_url( bp_get_root_domain() ) ); ?></p>
			<?php endif; ?>

		<?php else : ?>

			<p><?php _e( 'Please provide a valid verification key.', 'buddypress' ); ?></p>

			<form action="" method="get" class="standard-form" id="activation-form">

				<label for="key"><?php _e( 'Verification Key:', 'buddypress' ); ?></label>
				<input type="text" name="key" id="key" value="" />

				<p class="submit">
					<input type="submit" name="submit" value="<?php _e( 'Verify', 'buddypress' ); ?>" />
				</p>

			</form>

		<?php endif; ?>

		<?php do_action( 'bp_after_activate_content' ); ?>

	</div><!-- .page -->

	<?php do_action( 'bp_after_activation_page' ); ?>

</div><!-- #buddypress -->
