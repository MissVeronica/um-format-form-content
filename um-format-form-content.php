<?php
/**
 * Plugin Name:         Ultimate Member - Format Form Content shortcode
 * Description:         Extension to Ultimate Member for display of custom HTML format of User Profile form content and option to remove Profile Photos from selected Profile pages.
 * Version:             1.2.0
 * Requires PHP:        7.4
 * Author:              Miss Veronica
 * License:             GPL v3 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:          https://github.com/MissVeronica
 * Plugin URI:          https://github.com/MissVeronica/um-format-form-content
 * Update URI:          https://github.com/MissVeronica/um-format-form-content
 * Text Domain:         ultimate-member
 * Domain Path:         /languages
 * UM version:          2.8.6
 */

if ( ! defined( 'ABSPATH' ) ) exit; 

class UM_Format_Form_Content {

    public $profile_forms = array();


    function __construct() {

        add_shortcode( 'format_form_content',                             array( $this, 'format_form_content_shortcode' ));
        add_shortcode( 'select_um_shortcode',                             array( $this, 'format_form_content_select_um_shortcode' ));

        add_filter( 'um_settings_structure',                              array( $this, 'um_settings_structure_format_form_content' ), 10, 1 );
        add_filter( 'um_late_escaping_allowed_tags',                      array( $this, 'um_format_form_content_allowed_tags' ), 10, 2 );
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'format_form_content_settings_link' ), 10 );
        add_action( 'um_before_profile_main_meta',                        array( $this, 'um_before_profile_main_meta_photo_excl' ), 10, 1 );

        $um_profile_forms = get_posts( array(   'meta_key'    => '_um_mode',
                                                'meta_value'  => 'profile',
                                                'numberposts' => -1,
                                                'post_type'   => 'um_form',
                                                'post_status' => 'publish'
                                            ));

        $profile_forms = array();
        foreach( $um_profile_forms as $um_form ) {
            $this->profile_forms[$um_form->ID] = $um_form->post_title;
        }
    }

    public function format_form_content_select_um_shortcode() {

        global $current_user;

        $profile_form = UM()->options()->get( 'um_format_form_content_profile_form' );
        if ( ! empty( $profile_form ) && array_key_exists( $profile_form, $this->profile_forms )) {
            
            $shortcode = '[ultimatemember form_id="' . $profile_form . '"]';

            $viewer_role = UM()->options()->get( 'um_format_form_content_viewer_role' );
            if ( ! empty( $viewer_role ) && array_key_exists( $viewer_role, UM()->roles()->get_roles() )) {

                if ( in_array( $viewer_role, $current_user->roles )) {

                    $view_form = UM()->options()->get( 'um_format_form_content_view_form' );
                    if ( ! empty( $view_form ) && array_key_exists( $view_form, $this->profile_forms )) {

                        $shortcode = '[ultimatemember form_id="' . $view_form . '"]';
                    }
                }
            }

            if ( version_compare( get_bloginfo( 'version' ),'5.4', '<' ) ) {
                echo do_shortcode( $shortcode );

            } else {
                echo apply_shortcodes( $shortcode );
            }
        }
    }

    public function um_before_profile_main_meta_photo_excl( $args ) {

        $no_photo_forms = array_map( 'sanitize_text_field', UM()->options()->get( 'um_format_form_content_no_photo' ));

        if ( in_array( (string)$args['form_id'], $no_photo_forms )) {

            $html = str_replace( 'class="um-profile-photo-img"', 'style="display: none;"', ob_get_clean() );

            ob_start();
            echo $html;
        }
    }

    public function format_form_content_shortcode( $atts = false, $content = false ) {

        if ( ! empty( $content ) && class_exists( 'UM' )) {

            $file_name = WP_CONTENT_DIR . '/uploads/ultimatemember/format_form_content/' . trim( $content );
            if ( file_exists( $file_name )) {

                $file_content = file_get_contents( $file_name );
                if ( ! empty( $file_content )) {

                    $html_level = UM()->options()->get( 'um_format_form_content_html' );
                    if ( in_array( $html_level, array( 'default', 'templates', 'wp-admin' ))) {

                        add_filter( 'um_template_tags_patterns_hook', array( UM()->mail(), 'add_placeholder' ), 10, 1 );
                        add_filter( 'um_template_tags_replaces_hook', array( UM()->mail(), 'add_replace_placeholder' ), 10, 1 );

                        $file_content = um_convert_tags( $file_content, array() );
                        $file_content = wp_kses( $file_content, UM()->get_allowed_html( $html_level ) );

                        if ( version_compare( get_bloginfo( 'version' ),'5.4', '<' ) ) {
                            return do_shortcode( $file_content );

                        } else {
                            return apply_shortcodes( $file_content );
                        }
                    }
                }
            }
        }

        return '';
    }

    public function um_format_form_content_allowed_tags( $allowed_html, $context ) {

        // possible addition of allowed HTML in the future

        return $allowed_html;
    }

    public function format_form_content_settings_link( $links ) {

        $url = get_admin_url() . 'admin.php?page=um_options&section=users';
        $links[] = '<a href="' . esc_url( $url ) . '">' . __( 'Settings' ) . '</a>';

        return $links;
    }

    public function um_settings_structure_format_form_content( $settings_structure ) {

        if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'um_options' ) {
            if ( isset( $_REQUEST['section'] ) && $_REQUEST['section'] == 'users' ) {

                if ( ! isset( $settings_structure['']['sections']['users']['form_sections']['format_form_content']['fields'] )) {

                    $plugin_data = get_plugin_data( __FILE__ );

                    $link = sprintf( '<a href="%s" target="_blank" title="%s">%s</a>',
                                                esc_url( $plugin_data['PluginURI'] ),
                                                __( 'GitHub plugin documentation and download', 'ultimate-member' ),
                                                __( 'Plugin', 'ultimate-member' )
                                    );

                    $header = array(
                                        'title'       => __( 'Format Form Content shortcode', 'ultimate-member' ),
                                        'description' => sprintf( __( '%s version %s - tested with UM 2.8.6', 'ultimate-member' ),
                                                                            $link, esc_attr( $plugin_data['Version'] )),
                                    );

                    $prefix = '&nbsp; * &nbsp;';

                    $section_fields = array();

                    $section_fields[] = array(
                                'id'             => 'um_format_form_content_html',
                                'type'           => 'select',
                                'size'           => 'small',
                                'options'        => array(
                                                            'default'   => __( 'Low - Default WP HTML tags',        'ultimate-member' ),
                                                            'templates' => __( 'Medium - Email template HTML tags', 'ultimate-member' ),
                                                            'wp-admin'  => __( 'High - WP Admin HTML tags',         'ultimate-member' ),
                                                        ),
                                'label'          => $prefix . __( 'Select level of HTML tags allowed', 'ultimate-member' ),
                                'description'    => __( 'Select one of the three levels of HTML tags allowed: Low, Medium, High', 'ultimate-member' ),
                            );

                    $section_fields[] = array(
                                'id'             => 'um_format_form_content_viewer_role',
                                'type'           => 'select',
                                'size'           => 'medium',
                                'options'        => UM()->roles()->get_roles(),
                                'label'          => $prefix . __( 'Select the User Role for the viewer', 'ultimate-member' ),
                                'description'    => __( 'Select the Profile Role which will see the Profiles custom formatted User info.', 'ultimate-member' ),
                            );

                    $section_fields[] = array(
                                'id'             => 'um_format_form_content_view_form',
                                'type'           => 'select',
                                'size'           => 'medium',
                                'options'        => $this->profile_forms,
                                'label'          => $prefix . __( 'Select Profile view only Form', 'ultimate-member' ),
                                'description'    => __( 'Select the Profile form with the shortcode field for the [format_form_content] shortcode.', 'ultimate-member' ),
                            );

                    $section_fields[] = array(
                                'id'             => 'um_format_form_content_profile_form',
                                'type'           => 'select',
                                'size'           => 'medium',
                                'options'        => $this->profile_forms,
                                'label'          => $prefix . __( 'Select default User Profile Form', 'ultimate-member' ),
                                'description'    => __( 'Select User Profile Form for the site\'s Members.', 'ultimate-member' ),
                            );

                    $section_fields[] = array(
                                'id'             => 'um_format_form_content_no_photo',
                                'type'           => 'select',
                                'multi'          => true,
                                'size'           => 'medium',
                                'options'        => $this->profile_forms,
                                'label'          => $prefix . __( 'Profile Forms to remove Profile Photo', 'ultimate-member' ),
                                'description'    => __( 'Select single or multiple Profile Forms for Profile Photo removal.', 'ultimate-member' ),
                            );

                    $settings_structure['']['sections']['users']['form_sections']['format_form_content'] = $header;
                    $settings_structure['']['sections']['users']['form_sections']['format_form_content']['fields'] = $section_fields;
                }
            }
        }

        return $settings_structure;
    }
}

new UM_Format_Form_Content();

