<?php
/**
 * Gets templates for posts in specific categories.
 *
 * @package rkv-utilities
 */

namespace RKV\Utilities\Admin_Notices;

/**
 * Category post template class.
 */
class Hide_Notices {

    /**
     * @var 
     */
    private $admin_notice_data;


    /**
     * Add the actions.
     */
    public function __construct() {
        add_action( 'init', [ $this, 'register_hooks' ] );
    }

    /**
     * Register hooks
     */
    public function register_hooks() {
        // Register settings
        add_action( 'admin_init', [ $this, 'rkv_admin_notices_register_settings' ] );

        // Check if we should hide admin notices
        $hide_notices = get_option( 'rkv_hide_admin_notices', false );
        $hide_notices = apply_filters( 'rkv_hide_admin_notices', $hide_notices );
        if ( $hide_notices ) {
            add_action( 'admin_init', [ $this, 'hide_notices' ] );
        }

        // Check if the settings page should be visible
        $show_notice_settings = apply_filters( 'rkv_show_admin_notice_settings', true );
        if ( $show_notice_settings ) {
            add_action( 'admin_menu', [ $this, 'add_notice_settings_page' ]);
        }
        
    }

    /**
     * Register admin notice settings.
     */
    public function rkv_admin_notices_register_settings() {
        // Register a new setting for "rkv-utilities" page.
        register_setting('rkv-utilities', 'rkv_hide_admin_notices');

        // Register a new section on the "rkv-utilities" page.
        add_settings_section(
            'rkv-utilities-admin-notices-section',
            __( 'Admin Notices', 'rkv-utilities' ), 
            [ $this, 'rkv_utilities_admin_notices_section_callback' ],
            'rkv-utilities'
        );

        // Register a new field in the "rkv-utilities-admin-notices-section" section, inside the "rkv-utilities" page.
        add_settings_field(
            'rkv_hide_admin_notices', 
            __( 'Hide Admin Notices', 'rkv-utilities' ),
            [ $this, 'rkv_hide_admin_notices_field_callback' ],
            'rkv-utilities',
            'rkv-utilities-admin-notices-section',
            array(
                'label_for'         => 'rkv_hide_admin_notices',
                'class'             => 'rkv_row',
                'rkv_custom_data' => 'custom',
            )
        );
    }

    /**
     * Callback for the admin notices section markup.
     */
    public function rkv_utilities_admin_notices_section_callback() {
        echo '<p>' . esc_html__( 'Control the display of admin notices across the site. When notices are hidden, they will be available in a toolbar pop-out panel.', 'rkv-utilities' ) . '</p>';
    }

    /**
     * Callback for the hide admin notices input markup.
     */
    public function rkv_hide_admin_notices_field_callback() {
        $options = get_option('rkv_hide_admin_notices', []);
        ?>
        <input type="checkbox" id="rkv_hide_admin_notices" name="rkv_hide_admin_notices" value="1" <?php checked(1, $options); ?> />
        <?php
    }

    /**
     * Hides admin notices.
     */
    public function hide_notices() {
        $this->admin_notice_data = (array) get_option('rkv_admin_notice_data', []);

        //enqueue admin scripts and styles
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );

        // Wrap admin notices in custom div
        $hooks = [ 'admin_notices', 'user_admin_notices', 'network_admin_notices' ];
        foreach ($hooks as $hook) {
            add_action( $hook, [ $this, 'start_hook_capture' ], PHP_INT_MIN );
            add_action( $hook, [ $this, 'end_hook_capture' ], PHP_INT_MAX - 1 );
        }

        // Add admin notices button to admin toolbar
        add_action( 'admin_bar_menu', [ $this, 'add_toolbar_item' ], 998 );

        // Render pop-out panel in the footer
        add_action('admin_footer', [ $this, 'render_panel' ] );

        // Admin notices whitelist, blacklist and undo ajax callback
        add_action( 'wp_ajax_rkv_admin_notice_action', [ $this, 'admin_notice_ajax_handler' ] );
    }

    /**
     * Add admin notices script and styles
     * 
     * @return void
     */
    public function admin_scripts() {
            
        //enqueue styles
        wp_enqueue_style(
            'rkv-admin-notice-css', 
            RKV_UTILITIES_URL . 'assets/css/admin-notices.css', 
            [], 
            '1.0.0', 
            'all'
        );

        //enqueue scripts
        wp_enqueue_script(
            'rkv-admin-notice-js', 
            RKV_UTILITIES_URL . 'assets/js/admin-notices.js', 
            ['jquery'], 
            '1.0.0', 
            false
        );

        // localize scripts
        wp_localize_script(
            'rkv-admin-notice-js',
            'rkvAdminNoticesData',
            [
                'nonce' => wp_create_nonce('rkv-admin-notices-action'),
                'admin_notice_options' => $this->admin_notice_options(),
                'admin_notice_data' => $this->admin_notice_data,
                'whitelist_label' => esc_html__( 'Display this notice', 'rkv-utilities' ),
                'blacklist_label' => esc_html__( 'Silence this notice', 'rkv-utilities' ),
                'remove_whitelist_label' => esc_html__( 'Hide this notice', 'rkv-utilities' ),
                'remove_blacklist_label' => esc_html__( 'Move to Hidden Notices', 'rkv-utilities' ),
                'whitelist_note' => esc_html__( 'Displayed notices will no longer be removed from admin pages.', 'rkv-utilities' ),
                'blacklist_note' => esc_html__( 'This notice will be moved to the "Silenced Notices" tab and you will not receive a notification if it appears again.', 'rkv-utilities' ),
            ]
        );

    }

    /**
     * Wrap admin notices in custom opening div
     * @return void
     */
    public function start_hook_capture() {
        // Wrap the whole admin notice inside our hidden div
        echo '<div class="rkv-admin-notices-selector" style="display: none;">';
    }

    /**
     * Close admin notices custom opening div
     * @return void
     */
    public function end_hook_capture() {
        // close the opened notice hidden selector
        echo '</div>';
    }


    /**
     * Get admin notice option for current role
     * 
     * @return array
     */
    private function admin_notice_options() {

        $admin_notice_options = [
            'enable_toolbar_access' => 1,
            'notice_type_remove' => [ 'success', 'error', 'warning', 'info' ],
            'notice_type_display' => [ 'success', 'error', 'warning', 'info' ]
        ];
        
        return $admin_notice_options;
    }

    /**
     * Check if current user can manage notice
     * @return bool
     */
    private function can_see_admin_toolbar() {
        $admin_notice_options = $this->admin_notice_options();

        $can_see_admin_toolbar = false;

        if ( ! empty( $admin_notice_options['enable_toolbar_access'] ) && ! empty( $admin_notice_options['notice_type_remove'] ) && ! empty( $admin_notice_options['notice_type_display'] ) ) {
            $can_see_admin_toolbar = true;
        }

        return $can_see_admin_toolbar;
    }

    /**
     * Add admin notices to admin toolbar
     * 
     * @param \WP_Admin_Bar $wp_admin_bar WordPress admin bar.
     * 
     * @return void
     */
    public function add_toolbar_item( $wp_admin_bar ) {

        if ( ! $this->can_see_admin_toolbar() || !is_admin_bar_showing() ) {
            return;
        }
        
        $args = [
            'id'     => 'rkv-admin-notices-panel',
            'title'  => '<span class="ab-label">' . esc_html__( 'Admin Notices', 'rkv-utilities' ) . ' <span class="rkv-admin-notices-count" style="display: none;"></span></span>',
            'href'   => '#',
            'parent' => 'top-secondary',
            'meta'   => [
                'class' => 'rkv-admin-notices-toolbar-item',
            ],
        ];
        $wp_admin_bar->add_node( $args );
    }

    /**
     * Render our toolbar panel in the footer
     * @return void
     */
    public function render_panel() {
        if ( ! $this->can_see_admin_toolbar() ) {
            return;
        }
        ?>
        <div id="rkv-admin-notices-panel">
            <div class="admin-notices-tab" style="display: none;">
                <div class="admin-notices-button-group" data-hide-selector=".rkv-panel-notice-item">
                    <label class="active-notices selected" style="display: none;">
                        <input type="radio" name="admin_notices_tab" value=".active-notices-item" checked>
                        <?php esc_html_e( 'Hidden Notices', 'rkv-utilities' ); ?><span class="tab-notice-count"></span>
                    </label>
                    <label class="blacklisted-notices" style="display: none;">
                        <input type="radio" name="admin_notices_tab" value=".blacklisted-notices-item">
                        <?php esc_html_e( 'Silenced Notices', 'rkv-utilities' ); ?><span class="tab-notice-count"></span>
                    </label>
                </div>
            </div>
            <div class="rkv-admin-notices-panel-none empty-notices-message" style="display: none;">
                <svg width="170" height="170" viewBox="0 0 170 170" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="85" cy="85" r="85" fill="#a8a8a8"></circle>
                    <path d="M97.6667 78.6665H72.3333C70.5917 78.6665 69.1667 80.0915 69.1667 81.8332C69.1667 83.5748 70.5917 84.9998 72.3333 84.9998H97.6667C99.4083 84.9998 100.833 83.5748 100.833 81.8332C100.833 80.0915 99.4083 78.6665 97.6667 78.6665ZM107.167 56.4998H104V53.3332C104 51.5915 102.575 50.1665 100.833 50.1665C99.0917 50.1665 97.6667 51.5915 97.6667 53.3332V56.4998H72.3333V53.3332C72.3333 51.5915 70.9083 50.1665 69.1667 50.1665C67.425 50.1665 66 51.5915 66 53.3332V56.4998H62.8333C61.1536 56.4998 59.5427 57.1671 58.355 58.3548C57.1673 59.5426 56.5 61.1535 56.5 62.8332V107.167C56.5 108.846 57.1673 110.457 58.355 111.645C59.5427 112.833 61.1536 113.5 62.8333 113.5H107.167C110.65 113.5 113.5 110.65 113.5 107.167V62.8332C113.5 59.3498 110.65 56.4998 107.167 56.4998ZM104 107.167H66C64.2583 107.167 62.8333 105.742 62.8333 104V72.3332H107.167V104C107.167 105.742 105.742 107.167 104 107.167ZM88.1667 91.3332H72.3333C70.5917 91.3332 69.1667 92.7582 69.1667 94.4998C69.1667 96.2415 70.5917 97.6665 72.3333 97.6665H88.1667C89.9083 97.6665 91.3333 96.2415 91.3333 94.4998C91.3333 92.7582 89.9083 91.3332 88.1667 91.3332Z" fill="#8E8E8E"></path>
                </svg>
                <h4><?php esc_html_e( 'Admin Notices', 'rkv-utilities' ); ?></h4>
                <p><?php esc_html_e( 'There are currently no admin notices.', 'rkv-utilities' ); ?> <a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=pp-capabilities-settings&pp_tab=admin-notices' ) ); ?>"><?php esc_html_e( 'Edit the settings.', 'rkv-utilities' ); ?></a></p>
            </div>
            <div class="rkv-admin-notices-panel-content"><?php esc_html_e( 'Admin Notices', 'rkv-utilities' ); ?></div>
        </div>
        <?php
    }

    /**
     * Admin notices whitelist, blacklist and undo ajax callback
     *
     */
    public function admin_notice_ajax_handler() {
        error_log( 'admin_notice_ajax_handler called' );
        error_log( 'POST data: ' . print_r( $_POST, true ) );
        $response['status']  = 'error';
        $response['message'] = esc_html__( 'An error occured!', 'rkv-utilities' );
        $response['content'] = '';

        $nonce   = isset( $_POST['nonce'] ) ? sanitize_key( $_POST['nonce'] ) : '';
        $action_type = isset( $_POST['action_type'] ) ? sanitize_text_field( $_POST['action_type'] ) : '';
        $action_option = isset( $_POST['action_option'] ) ? sanitize_text_field( $_POST['action_option'] ) : '';
        $notice_id = isset( $_POST['notice_id'] ) ? sanitize_text_field( $_POST['notice_id'] ) : '';

        if ( ! $this->can_see_admin_toolbar() ) {
            $response['message'] = esc_html__( 'You do not have permission to manage admin notices.', 'rkv-utilities' );
        } elseif ( ! wp_verify_nonce( $nonce, 'rkv-admin-notices-action' ) ) {
            $response['message'] = esc_html__( 'Invalid action. Reload this page and try again.', 'rkv-utilities' );
        } elseif ( empty( $action_type ) || empty( $action_option ) && empty( $notice_id ) ) {
            $response['message'] = esc_html__( 'Invalid form.', 'rkv-utilities' );
        } else {
            
            $admin_notice_data = (array) get_option( 'rkv_admin_notice_data', [] );
            // remove current notice from both whitelist and blacklist if present
            if ( ! empty( $admin_notice_data['whitelist_notices'] ) ) {
                $admin_notice_data['whitelist_notices'] = array_values( array_diff( $admin_notice_data['whitelist_notices'], [$notice_id] ) );
            }
            if ( ! empty( $admin_notice_data['blacklist_notices'] ) ) {
                $admin_notice_data['blacklist_notices'] = array_values( array_diff( $admin_notice_data['blacklist_notices'], [$notice_id] ) );
            }

            // add notice to whitelist/blacklist if action is default and not undo action
            if ( $action_option == 'default' ) {
                if ( $action_type == 'whitelist' ) {
                    $admin_notice_data['whitelist_notices'][] = $notice_id;
                } else {
                    $admin_notice_data['blacklist_notices'][] = $notice_id;
                }
            }

            update_option( 'rkv_admin_notice_data', $admin_notice_data );

            $response['message'] = esc_html__( 'Admin notice status updated successfully.', 'rkv-utilities');
            $response['status']  = 'success';
        }

        wp_send_json($response);
    }

    /**
     * Add admin notice settings page
     * 
     * @return void
     */
    public function add_notice_settings_page() {
        add_options_page(
            'Site Level Options',
            'Site Level Options',
            'manage_options',
            'admin-notice-settings',
            [ $this, 'admin_notice_settings_page_html' ]
        );
    }

    /**
     * Render admin notice settings page HTML
     * 
     * @return void
     */
    public function admin_notice_settings_page_html() {
        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
		    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		    <form action="options.php" method="post">
                <?php
                settings_fields( 'rkv-utilities' );

                do_settings_sections( 'rkv-utilities' );
                
			    submit_button( __( 'Save Settings', 'rkv-utilities' ) );
                ?>
            </form>
        </div>
        <?php
    }
}