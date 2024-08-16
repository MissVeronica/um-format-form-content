<?php
/**
 * Plugin Name:         Ultimate Member - Format Form Content shortcode
 * Description:         Extension to Ultimate Member for display of custom HTML format of User Profile form content and option to remove Profile Photos from selected Profile pages.
 * Version:             1.5.0
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
    public $directory     = '';

    public $include_field_types  = array(
                                            'text',
                                            'tel',
                                            'url',
                                            'date',
                                            'textarea',
                                            'radio',
                                            'select',
                                            'multiselect',
                                            'checkbox',
                                            'number',
                                            'wp_editor',
                                        );

    public $except_metakeys = array(
                                            'password',
                                            'role_select',
                                            'role_radio',
                                        );
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

        if ( ! empty( $um_profile_forms ) && is_array( $um_profile_forms )) {

            foreach( $um_profile_forms as $um_form ) {

                if ( ! empty( $um_form )) {
                    $this->profile_forms[$um_form->ID] = $um_form->post_title;
                }
            }
        }

        $this->directory = WP_CONTENT_DIR . '/uploads/ultimatemember/format_form_content/';
    }

    public function format_form_content_select_um_shortcode() {

        global $current_user;

        if ( class_exists( 'UM' )) {

            $profile_form = UM()->options()->get( 'um_format_form_content_profile_form' );
            if ( ! empty( $profile_form ) && array_key_exists( $profile_form, $this->profile_forms )) {

                $shortcode = '[ultimatemember form_id="' . $profile_form . '"]';

                if ( ! current_user_can( 'administrator' )) {

                    $profile_id = $current_user->ID;
                    $um_user = get_query_var( 'um_user' );
                    $permalink_base = UM()->options()->get( 'permalink_base' );

                    if ( ! empty( $permalink_base ) && ! empty( $um_user )) {

                        switch( $permalink_base ) {

                            case 'user_login':  $um_user = str_replace( '+', ' ', $um_user );
                                                $user = get_user_by( 'login', $um_user );
                                                if ( ! empty( $user )) {
                                                    $profile_id = $user->ID;
                                                }
                                                break;

                            case 'user_id':     $profile_id = $um_user;
                                                break;

                            default:            $profile_id = $current_user->ID;
                        }
                    }

                    if ( ! empty( $profile_id ) && $profile_id != $current_user->ID && um_can_view_profile( $profile_id )) {

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
    }

    public function um_before_profile_main_meta_photo_excl( $args ) {

        $no_photo = UM()->options()->get( 'um_format_form_content_no_photo' );

        if ( ! empty( $no_photo ) && is_array( $no_photo )) {
            $no_photo_forms = array_map( 'sanitize_text_field', $no_photo );

            if ( in_array( (string)$args['form_id'], $no_photo_forms )) {

                $html = str_replace( 'class="um-profile-photo-img"', 'style="display: none;"', ob_get_clean() );

                ob_start();
                echo $html;
            }
        }
    }

    public function format_form_content_shortcode( $atts = false, $content = false ) {

        if ( class_exists( 'UM' )) {

            $html_file = UM()->options()->get( 'um_format_form_content_profile_html_file' );

            if ( empty( $html_file ) && ! empty( $content )) {
                $html_file = trim( $content );
            }

            if ( ! empty( $html_file )) {

                $html_file = $this->directory . $html_file;

                if ( file_exists( $html_file )) {

                    $file_content = file_get_contents( $html_file );
                    if ( ! empty( $file_content )) {

                        $html_level = UM()->options()->get( 'um_format_form_content_html' );
                        if ( in_array( $html_level, array( 'default', 'templates', 'wp-admin' ))) {

                            if ( version_compare( get_bloginfo( 'version' ),'5.4', '<' ) ) {
                                $html = do_shortcode( $file_content );

                            } else {
                                $html = apply_shortcodes( $file_content );
                            }

                            add_filter( 'um_template_tags_patterns_hook', array( UM()->mail(), 'add_placeholder' ), 10, 1 );
                            add_filter( 'um_template_tags_replaces_hook', array( UM()->mail(), 'add_replace_placeholder' ), 10, 1 );

                            $html = um_convert_tags( $html, array() );
                            $html = wp_kses( $html, UM()->get_allowed_html( $html_level ) );

                            return $html;
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

                    if ( UM()->options()->get( 'um_format_form_content_profile_form_html' ) == 1 ) {

                        $form_id = UM()->options()->get( 'um_format_form_content_profile_form' );

                        if ( ! empty( $form_id )) {

                            $form_fields = get_post_meta( $form_id, '_um_custom_fields', true );
                            $um_user_meta = array();

                            foreach( $form_fields as $metakey => $form_field ) {

                                if ( in_array( $form_field['type'], $this->include_field_types ) && ! in_array( $metakey, $this->except_metakeys )) {

                                    $title = isset( $form_field['title'] ) ? $form_field['title'] : '';
                                    $title = isset( $form_field['label'] ) ? $form_field['label'] : $title;

                                    $um_user_meta[$metakey] = esc_attr( $title );
                                }
                            }

                            asort( $um_user_meta );

                            $html = "<div style=\"padding-left: 30px;\"><ul>\n";
                            foreach( $um_user_meta as $metakey => $title ) {

                                if ( $form_fields[$metakey]['type'] == 'textarea' ) {
                                    $html .= "<li>{$title}: <div>[um_user meta_key=\"{$metakey}\"]</div></li>\n";

                                } else {
                                    $html .= "<li>{$title}: [um_user meta_key=\"{$metakey}\"]</li>\n";
                                }
                            }

                            $html .= "</ul></div>\n";

                            $file_name = $this->directory . 'formatted-' . $form_id . '.html';
                            file_put_contents( $file_name, $html );
                        }
                    }

                    $all_html_files = array();
                    $files = glob( $this->directory . '*.html' );

                    if ( ! empty( $files ) && is_array( $files )) {

                        foreach( $files as $file ) {

                            $file_name = str_replace( $this->directory, '', $file );
                            $file_form_id = explode( '-', str_replace( '.html', '', $file_name ));

                            $profile_form = __( 'Unknown Profile Form', 'ultimate-member' );
                            if ( count( $file_form_id ) == 2 && isset( $this->profile_forms[$file_form_id[1]] )) {
                                $profile_form = $this->profile_forms[$file_form_id[1]];
                            }

                            $all_html_files[$file_name] = $file_name . ' - ' . $profile_form;
                        }
                    }

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
                                'id'             => 'um_format_form_content_profile_form_html',
                                'type'           => 'checkbox',
                                'label'          => $prefix . __( 'Make a HTML file', 'ultimate-member' ),
                                'checkbox_label' => __( 'Click to make a HTML file of this User Profile Form to "formatted-FORMID.html" in the upload directory. Rename file for editing.', 'ultimate-member' ),
                            );

                    $section_fields[] = array(
                                'id'             => 'um_format_form_content_profile_html_file',
                                'type'           => 'select',
                                'size'           => 'medium',
                                'options'        => $all_html_files,
                                'label'          => $prefix . __( 'Select HTML file for shortcode formatting', 'ultimate-member' ),
                                'description'    => __( 'Select HTML file for use by the shortcode [format_form_content] in the formatting', 'ultimate-member' ),
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

