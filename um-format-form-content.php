<?php
/**
 * Plugin Name:         Ultimate Member - Format Form Content shortcodes
 * Description:         Extension to Ultimate Member for display of custom HTML format of User Profile form content and option to remove Profile Photos from selected Profile pages.
 * Version:             2.0.0
 * Requires PHP:        7.4
 * Author:              Miss Veronica
 * License:             GPL v3 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:          https://github.com/MissVeronica
 * Plugin URI:          https://github.com/MissVeronica/um-format-form-content
 * Update URI:          https://github.com/MissVeronica/um-format-form-content
 * Text Domain:         format-form-content
 * Domain Path:         /languages
 * UM version:          2.8.6
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class UM_Format_Form_Content {

    public $directory          = '';
    public $new_plugin_version = '';
    public $file_created       = '';
    public $form_id            = '';

    public $profile_forms      = array();
    public $all_html_files     = array();
    public $form_custom_fields = array();

    public $except_metakeys = array(
                                        'password',
                                        'role_select',
                                        'role_radio',
                                    );

    public $exclude_field_types  = array(
                                        'row',
                                        'password',
                                        'block',
                                        'shortcode',
                                        'spacing',
                                        'divider',
                                        'group',
                                    );
    function __construct() {

        add_shortcode( 'format_form_content',                             array( $this, 'format_form_content_shortcode' ));
        add_shortcode( 'show_field',                                      array( $this, 'format_form_content_show_field' ) );

        add_filter( 'um_settings_structure',                              array( $this, 'um_settings_structure_format_form_content' ), 10, 1 );
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'format_form_content_settings_link' ), 10 );
        add_action( 'um_before_profile_main_meta',                        array( $this, 'um_before_profile_main_meta_photo_excl' ), 10, 1 );
        add_filter( 'um_pre_args_setup',                                  array( $this, 'um_pre_args_setup_format_form_content' ), 10, 1 );

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

        $this->form_id = UM()->options()->get( 'um_format_form_content_profile_form' );
        if ( ! empty( $this->form_id )) {

            $form_fields = get_post_meta( $this->form_id, '_um_custom_fields', true );

            foreach( $form_fields as $metakey => $form_field ) {

                if ( ! in_array( $metakey, $this->except_metakeys ) && ! in_array( $form_field['type'], $this->exclude_field_types )) {

                    $title = isset( $form_field['title'] ) ? $form_field['title'] : '';
                    $title = isset( $form_field['label'] ) ? $form_field['label'] : $title;

                    $this->form_custom_fields[$metakey] = array( 'title' => esc_attr( $title ), 'type' => $form_field['type'] );
                }
            }
        }

        $this->directory = WP_CONTENT_DIR . '/uploads/ultimatemember/format_form_content/';

        define( 'Plugin_File_FFC', __FILE__ );
        define( 'Plugin_Path_FFC', plugin_dir_path( __FILE__ ) );
        define( 'Plugin_Textdomain_FFC', 'format-form-content' );
    }

    public function um_content_moderation_plugin_loaded() {

        $locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
        load_textdomain( Plugin_Textdomain_FFC, WP_LANG_DIR . '/plugins/' . Plugin_Textdomain_FFC . '-' . $locale . '.mo' );
        load_plugin_textdomain( Plugin_Textdomain_FFC, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    public function um_pre_args_setup_format_form_content( $args ) {

        global $current_user;

        $profile_form = UM()->options()->get( 'um_format_form_content_profile_form' );
        if ( ! empty( $profile_form ) && array_key_exists( $profile_form, $this->profile_forms )) {

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

                        $args['form_id'] = $view_form;
                        UM()->fields()->set_id = absint( $view_form );

                        $post_data = UM()->query()->post_data( $view_form );
                        $args      = array_merge( $args, $post_data );
                    }
                }
            }
        }

        return $args;
    }

    public function um_before_profile_main_meta_photo_excl( $args ) {

        $no_photo = UM()->options()->get( 'um_format_form_content_no_photo' );

        if ( ! empty( $no_photo ) && is_array( $no_photo )) {
            $no_photo_forms = array_map( 'sanitize_text_field', $no_photo );

            if ( in_array( $args['form_id'], $no_photo_forms )) {

                $html = str_replace( 'class="um-profile-photo-img"', 'style="display: none;"', ob_get_clean() );

                ob_start();
                echo $html;
            }
        }
    }

    public function format_form_content_show_field( $atts = false, $content = false ) {

        $meta_value = '';

        if ( ! empty( $atts ) && is_array( $atts ) && isset( $atts['meta_key'] ) && ! empty( $atts['meta_key'] ) ) {

            $meta_value = um_user( $atts['meta_key'] );

            if ( is_array( $meta_value ) ) {
                $meta_value = implode( ',', $meta_value );

            } else {
                $meta_value = trim( $meta_value );
            }

            if ( ! empty( $meta_value ) && isset( $this->form_custom_fields[$atts['meta_key']]['type'] )) {

                switch( $this->form_custom_fields[$atts['meta_key']]['type'] ) {

                    case 'url':     $meta_value = '<a href="' . $meta_value . '">' . $meta_value . '</a>'; break;
                    case 'tel':     $meta_value = '<a href="tel:' . $meta_value . '">' . $meta_value . '</a>'; break;
                    case 'text':    if ( is_email( $meta_value )) {
                                        $meta_value = '<a href="mailto:' . $meta_value . '">' . $meta_value . '</a>';
                                    }
                                    break;
                    default:        break;
                }
            }
        }

		return $meta_value;
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

                            if ( UM()->options()->get( 'um_format_form_content_remove_empty' ) == 1 ) {

                                $new_rows = array();
                                $rows = array_map( 'trim', explode( "\n", $html ));

                                if ( UM()->options()->get( 'um_format_form_content_format' ) == 'list' ) {
                                    $empty_html = '<div></div>';
                                } else {
                                    $empty_html = '<td></td>';
                                }

                                foreach( $rows as $row ) {
                                    if ( strpos( $row, ' </li>') === false && strpos( $row, $empty_html ) === false) {
                                        $new_rows[] = $row;
                                    }
                                }

                                $html = implode( "\n", $new_rows );
                            }

                            add_filter( 'um_late_escaping_allowed_tags', array( $this, 'um_format_form_content_allowed_tags' ), 99, 2 );

                            $html = wp_kses( $html, UM()->get_allowed_html( $html_level ) );

                            remove_filter( 'um_late_escaping_allowed_tags', array( $this, 'um_format_form_content_allowed_tags' ), 99, 2 );

                            return $html;
                        }
                    }
                }
            }
        }

        return '';
    }

    public function um_format_form_content_allowed_tags( $allowed_html, $context ) {

        require_once( Plugin_Path_FFC . 'allowed-html-list.php' );

        return $allowed_html;
    }

    public function format_form_content_settings_link( $links ) {

        $url = get_admin_url() . 'admin.php?page=um_options&tab=extensions&section=format-form-content';
        $links[] = '<a href="' . esc_url( $url ) . '">' . __( 'Settings' ) . '</a>';

        return $links;
    }

    public function prepare_page_settings() {

        if ( ! file_exists( $this->directory )) {

            wp_mkdir_p( $this->directory );
        }

        if ( ! empty( $this->form_custom_fields && is_array( $this->form_custom_fields ))) {

            if ( UM()->options()->get( 'um_format_form_content_profile_form_html' ) == 1 ) {

                $html = '';
                asort( $this->form_custom_fields );

                if ( UM()->options()->get( 'um_format_form_content_format' ) == 'list' ) {

                    $html = "<div style=\"padding-left: 30px;\"><ul>\n";
                    foreach( $this->form_custom_fields as $metakey => $array ) {

                        $title = $array['title'];

                        if ( $array['type'] == 'textarea' ) {
                            $html .= "<li>{$title}: <div>[show_field meta_key=\"{$metakey}\"]</div></li>\n";

                        } else {
                            $html .= "<li>{$title}: [show_field meta_key=\"{$metakey}\"]</li>\n";
                        }
                    }

                    $html .= "</ul></div>\n";
                }

                if ( UM()->options()->get( 'um_format_form_content_format' ) == 'table' ) {

                    $title   = __( 'Title', 'format-form-content' );
                    $content = __( 'Field content', 'format-form-content' );

                    $html = "<div style=\"padding-left: 30px;\"><table>\n";
                    $html .= "<tr><th>{$title}</th><th>{$content}</th></tr>\n";

                    foreach( $this->form_custom_fields as $metakey => $array ) {

                        $title = $array['title'];
                        $html .= "<tr><td>{$title}</td><td>[show_field meta_key=\"{$metakey}\"]</td></tr>\n";
                    }

                    $html .= "</table></div>\n";
                }

                if ( ! empty( $html )) {

                    $file_name = $this->directory . 'formatted-' . $this->form_id . '.html';
                    file_put_contents( $file_name, $html );

                    $this->file_created = sprintf( __( 'File "%s" created with %d Profile Form fields.', 'format-form-content' ), 'formatted-' . $this->form_id . '.html', count( $this->form_custom_fields ));
                }
            }
        }

        $files = glob( $this->directory . '*.html' );

        if ( ! empty( $files ) && is_array( $files )) {

            foreach( $files as $file ) {

                $file_name = str_replace( $this->directory, '', $file );
                $file_form_id = explode( '-', str_replace( '.html', '', $file_name ));

                $profile_form = __( 'Unknown Profile Form', 'format-form-content' );
                if ( count( $file_form_id ) == 2 && isset( $this->profile_forms[$file_form_id[1]] )) {
                    $profile_form = $this->profile_forms[$file_form_id[1]];
                }

                $this->all_html_files[$file_name] = $file_name . ' - ' . $profile_form;
            }
        }
    }

    public function um_settings_structure_format_form_content( $settings ) {

        if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'um_options' ) {
            if ( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'extensions' ) {

                $settings['extensions']['sections']['format-form-content']['title'] = __( 'Format Form Content shortcodes', 'format-form-content' );

                if ( ! isset( $_REQUEST['section'] ) || $_REQUEST['section'] == 'format-form-content' ) {

                    if ( ! isset( $settings['extensions']['sections']['format-form-content']['fields'] ) ) {

                        $this->prepare_page_settings();

                        $settings['extensions']['sections']['format-form-content']['description'] = $this->get_possible_plugin_update( 'um-format-form-content' );
                        $settings['extensions']['sections']['format-form-content']['fields'] = $this->create_plugin_settings_fields();
                    }
                }
            }
        }

        return $settings;
    }

    public function create_plugin_settings_fields() {

        $plugin_data = get_plugin_data( __FILE__ );

        $link = sprintf( '<a href="%s" target="_blank" title="%s">%s</a>',
                                    esc_url( $plugin_data['PluginURI'] ),
                                    __( 'GitHub plugin documentation and download', 'format-form-content' ),
                                    __( 'Plugin', 'format-form-content' )
                        );

        $header = array(
                            'title'       => __( 'Format Form Content shortcode', 'format-form-content' ),
                            'description' => sprintf( __( '%s version %s - tested with UM 2.8.6', 'format-form-content' ),
                                                                $link, esc_attr( $plugin_data['Version'] )),
                        );

        $prefix = '&nbsp; * &nbsp;';

        $section_fields = array();

        $section_fields[] = array(
                    'id'             => 'um_format_form_content_html',
                    'type'           => 'select',
                    'size'           => 'medium',
                    'options'        => array(
                                                'default'   => __( 'Low - Default WP HTML tags',        'format-form-content' ),
                                                'templates' => __( 'Medium - Email template HTML tags', 'format-form-content' ),
                                                'wp-admin'  => __( 'High - WP Admin HTML tags',         'format-form-content' ),
                                            ),
                    'label'          => $prefix . __( 'Select level of HTML tags allowed', 'format-form-content' ),
                    'description'    => __( 'Select one of the three levels of HTML tags allowed: Low, Medium, High.', 'format-form-content' ) . '<br />' .
                                        __( 'All three levels allow these additional tags &lt;ul&gt;, &lt;li&gt; and &lt;table&gt;, &lt;tr&gt;, &lt;th&gt;, &lt;td&gt; for this plugin.', 'format-form-content' ),
                );

        $section_fields[] = array(
                    'id'             => 'um_format_form_content_view_form',
                    'type'           => 'select',
                    'size'           => 'medium',
                    'options'        => $this->profile_forms,
                    'label'          => $prefix . __( 'Select "Profile View" only Form', 'format-form-content' ),
                    'description'    => __( 'Select the Profile form with the shortcode field for the [format_form_content] shortcode.', 'format-form-content' ),
                );

        $section_fields[] = array(
                    'id'             => 'um_format_form_content_profile_form',
                    'type'           => 'select',
                    'size'           => 'medium',
                    'options'        => $this->profile_forms,
                    'label'          => $prefix . __( 'Select default User Profile Form', 'format-form-content' ),
                    'description'    => __( 'Select User Profile Form for the site\'s Members.', 'format-form-content' ),
                );

        $section_fields[] = array(
                    'id'             => 'um_format_form_content_profile_form_html',
                    'type'           => 'checkbox',
                    'label'          => $prefix . __( 'Create a HTML file', 'format-form-content' ),
                    'checkbox_label' => __( 'Click to create a HTML file of this User Profile Form to "formatted-FORMID.html" in the upload directory. Rename file before editing.', 'format-form-content' ),
                    'description'    => $this->file_created,
                );

        $section_fields[] = array(
                    'id'             => 'um_format_form_content_format',
                    'type'           => 'select',
                    'size'           => 'small',
                    'options'        => array(
                                                'list'  => __( 'HTML List', 'format-form-content' ),
                                                'table' => __( 'HTML Table', 'format-form-content' ),
                                            ),
                    'label'          => $prefix . __( 'Select HTML format', 'format-form-content' ),
                    'description'    => __( 'Select HTML file format "list" or "Table" for the shortcode [format_form_content]', 'format-form-content' ),
                    'conditional'    => array( 'um_format_form_content_profile_form_html', '=', 1 ),
                );

        $section_fields[] = array(
                    'id'             => 'um_format_form_content_profile_html_file',
                    'type'           => 'select',
                    'size'           => 'medium',
                    'options'        => $this->all_html_files,
                    'label'          => $prefix . __( 'Select HTML file for shortcode formatting', 'format-form-content' ),
                    'description'    => __( 'Select HTML file for use by the shortcode [format_form_content] in the formatting of the view only Profile Form', 'format-form-content' ),
                );

        $section_fields[] = array(
                    'id'             => 'um_format_form_content_remove_empty',
                    'type'           => 'checkbox',
                    'label'          => $prefix . __( 'Remove lines with empty field values', 'format-form-content' ),
                    'checkbox_label' => __( 'Click to remove empty lines (except the title) when the meta field value is empty.', 'format-form-content' ),
                );

        $section_fields[] = array(
                    'id'             => 'um_format_form_content_no_photo',
                    'type'           => 'select',
                    'multi'          => true,
                    'size'           => 'medium',
                    'options'        => $this->profile_forms,
                    'label'          => $prefix . __( 'Profile Forms to remove Profile Photo', 'format-form-content' ),
                    'description'    => __( 'Select single or multiple Profile Forms for Profile Photo removal.', 'format-form-content' ),
                );

        return $section_fields;
    }

    public function get_possible_plugin_update( $plugin ) {

        $update = __( 'Plugin version update failure', 'format-form-content' );
        $transient = get_transient( $plugin );

        if ( is_array( $transient ) && isset( $transient['status'] )) {
            $update = $transient['status'];
        }

        if ( defined( 'Plugin_File_FFC' )) {

            $plugin_data = get_plugin_data( Plugin_File_FFC );
            if ( ! empty( $plugin_data )) {

                if ( empty( $transient ) || $this->new_version_test_required( $transient, $plugin_data )) {

                    if ( extension_loaded( 'curl' )) {

                        $github_user = 'MissVeronica';
                        $url = "https://api.github.com/repos/{$github_user}/{$plugin}/contents/README.md";

                        $curl = curl_init();
                        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
                        curl_setopt( $curl, CURLOPT_BINARYTRANSFER, 1 );
                        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1 );
                        curl_setopt( $curl, CURLOPT_URL, $url );
                        curl_setopt( $curl, CURLOPT_USERAGENT, $github_user );

                        $content = json_decode( curl_exec( $curl ), true );
                        $error = curl_error( $curl );
                        curl_close( $curl );

                        if ( ! $error ) {

                            switch( $this->validate_new_plugin_version( $plugin_data, $content ) ) {

                                case 0:     $update = __( 'Plugin version update verification failed', 'format-form-content' );
                                            break;
                                case 1:     $update = '<a href="' . esc_url( $plugin_data['UpdateURI'] ) . '" target="_blank">';
                                            $update = sprintf( __( 'Update to %s plugin version %s%s is now available for download.', 'format-form-content' ), $update, esc_attr( $this->new_plugin_version ), '</a>' );
                                            break;
                                case 2:     $update = sprintf( __( 'Plugin is updated to the latest version %s.', 'format-form-content' ), esc_attr( $plugin_data['Version'] ));
                                            break;
                                case 3:     $update = __( 'Unknown encoding format returned from GitHub', 'format-form-content' );
                                            break;
                                case 4:     $update = __( 'Version number not found', 'format-form-content' );
                                            break;
                                case 5:     $update = sprintf( __( 'Update to plugin version %s is now available for download from GitHub.', 'format-form-content' ), esc_attr( $this->new_plugin_version ));
                                            break;
                                default:    $update = __( 'Plugin version update validation failure', 'format-form-content' );
                                            break;
                            }

                            if ( isset( $plugin_data['PluginURI'] ) && ! empty( $plugin_data['PluginURI'] )) {

                                $update .= sprintf( ' <a href="%s" target="_blank" title="%s">%s</a>',
                                                            esc_url( $plugin_data['PluginURI'] ),
                                                            __( 'GitHub plugin documentation and download', 'format-form-content' ),
                                                            __( 'Plugin documentation', 'format-form-content' ));
                            }

                            $today = date_i18n( 'Y/m/d H:i:s', current_time( 'timestamp' ));
                            $update2 = sprintf( __( 'Github plugin version status is checked each 24 hours last at %s.', 'format-form-content' ), esc_attr( $today ));

                            $update = '<div>' . $update . '</div><div>' . $update2 . '</div>';
                            set_transient( $plugin,
                                            array( 'status'       => $update,
                                                   'last_version' => $plugin_data['Version'] ),
                                            24 * HOUR_IN_SECONDS
                                        );

                        } else {
                            $update = sprintf( __( 'GitHub remote connection cURL error: %s', 'format-form-content' ), $error );
                        }

                    } else {
                        $update = __( 'cURL extension not loaded by PHP', 'format-form-content' );
                    }
                }
            }
        }

        $update = '<div>' . $update . '</div>';

        return wp_kses( $update, UM()->get_allowed_html( 'templates' ) );
    }

    public function new_version_test_required( $transient, $plugin_data ) {

        $bool = false;
        if ( isset( $transient['last_version'] ) && $plugin_data['Version'] != $transient['last_version'] ) {
            $bool = true;
        }

        return $bool;
    }

    public function validate_new_plugin_version( $plugin_data, $content ) {

        $validation = 0;
        if ( is_array( $content ) && isset( $content['content'] )) {

            $validation = 3;
            if ( $content['encoding'] == 'base64' ) {

                $readme  = base64_decode( $content['content'] );
                $version = strrpos( $readme, 'Version' );

                $validation = 4;
                if ( $version !== false ) {

                    $version = array_map( 'trim', array_map( 'sanitize_text_field', explode( "\n", substr( $readme, $version, 40 ))));

                    if ( isset( $plugin_data['Version'] ) && ! empty( $plugin_data['Version'] )) {

                        $version = explode( ' ', $version[0] );
                        $index = 1;
                        if ( isset( $version[$index] ) && ! empty( $version[$index] )) {

                            $validation = 2;
                            if ( sanitize_text_field( $plugin_data['Version'] ) != $version[$index] ) {

                                $validation = 5;
                                if ( isset( $plugin_data['UpdateURI'] ) && ! empty( $plugin_data['UpdateURI'] )) {

                                    $this->new_plugin_version = $version[$index];
                                    $validation = 1;
                                }
                            } 
                        }
                    }
                }
            }
        }

        return $validation;
    }


}

new UM_Format_Form_Content();
