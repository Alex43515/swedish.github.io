<?php

/**
 * Determine form is vendor reg or not
 *
 * @since WPUF_PRO
 *
 * @param $form_id
 *
 * @return bool
 */
function wpuf_is_vendor_reg( $form_id ) {
    $vendor_reg = false;

    if ( isset( $form_id ) ) {
        $form_settings     = wpuf_get_form_settings( $form_id );
        $profile_templates = ( new WPUF_Admin_Profile_Form_Template() )->wpuf_get_profile_form_templates();

        $profile_templates = array_map(
            function ( $template_name, $profile_form ) {
                return $template_name;
            }, array_keys( $profile_templates ), $profile_templates
        );

        $role = [ 'shop_manager', 'seller' ];

        if ( in_array( $form_settings['form_template'], $profile_templates, true ) && in_array( $form_settings['role'], $role, true ) ) {
            $vendor_reg = true;
        }
    }

    return $vendor_reg;
}

/**
 * Get setup wizard or WC account page url
 *
 * @since WPUF_PRO
 *
 * @return url
 */
function wpuf_get_dokan_redirect_url() {
    $wc_acc_page  = get_option( 'woocommerce_myaccount_page_id' );
    $redirect_url = wpuf_get_option( 'account_page', 'wpuf_my_account', 0 );

    if ( function_exists( 'dokan_get_option' ) ) {
        $redirect_url = dokan_get_option( 'disable_welcome_wizard', 'dokan_selling', 'off' ) === 'on' ? get_site_url() . '/?page=dokan-seller-setup' : '';
    }

    if ( $wc_acc_page ) {
        $redirect_url = $redirect_url === '' ? get_permalink( $wc_acc_page ) : $redirect_url;
    }

    return $redirect_url;
}
