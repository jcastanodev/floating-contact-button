<?php

class FCBJC_Lead_Controller extends WP_REST_Controller
{
    private static $instance;
    private static $wpdb;
    private static $table_name;
    private static $charset_collate;

    private function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'fcbjc_leads';
        $this->charset_collate = $wpdb->get_charset_collate();
        add_action('init', array($this, 'db_create_table'));
        add_action('rest_api_init', array($this, 'register_routes'), 15);
    }

    public static function instance(): FCBJC_Lead_Controller
    {
        if (!isset(self::$instance)) {
            self::$instance = new FCBJC_Lead_Controller();
        }
        return self::$instance;
    }

    public function db_create_table()
    {
        $sql = "CREATE TABLE IF NOT EXISTS $this->table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            email varchar(255) NOT NULL,
            message text NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            readed_at TIMESTAMP NULL,
            PRIMARY KEY (id)
        ) $this->charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public function db_readed()
    {
        return $this->wpdb->get_results("SELECT * FROM $this->table_name WHERE readed_at IS NOT NULL");
    }

    public function db_unreaded()
    {
        return $this->wpdb->get_results("SELECT * FROM $this->table_name WHERE readed_at IS NULL");
    }

    public function db_mark_readed($lead_id)
    {
        $this->wpdb->update($this->table_name, array(
            'readed_at' => current_time('mysql', 1),
        ), array('id' => $lead_id));
        return $this->wpdb->get_row("SELECT * FROM $this->table_name WHERE id = $lead_id");
    }

    public function db_mark_unreaded($lead_id)
    {
        $this->wpdb->update($this->table_name, array(
            'readed_at' => null,
        ), array('id' => $lead_id));
        return $this->wpdb->get_row("SELECT * FROM $this->table_name WHERE id = $lead_id");
    }

    public function db_create($name, $email, $message)
    {
        return $this->wpdb->insert($this->table_name, array(
            'name' => $name,
            'email' => $email,
            'message' => $message,
        ));
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes()
    {
        $version = '1';
        $namespace = 'fcbjc/v' . $version;
        $base = 'lead';
        register_rest_route($namespace, '/' . $base . '/unreaded', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_unreaded_leads'),
                'permission_callback' => array($this, 'get_admin_permissions_check'),
                'args'                => array(),
            ),
        ));
        register_rest_route($namespace, '/' . $base . '/mark_unreaded/(?P<id>[\d]+)', array(
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array($this, 'mark_unreaded_lead'),
                'permission_callback' => array($this, 'get_admin_permissions_check'),
                'args'                => array(),
            ),
        ));
        register_rest_route($namespace, '/' . $base . '/readed', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_readed_leads'),
                'permission_callback' => array($this, 'get_admin_permissions_check'),
                'args'                => array(),
            ),
        ));
        register_rest_route($namespace, '/' . $base . '/mark_readed/(?P<id>[\d]+)', array(
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array($this, 'mark_readed_lead'),
                'permission_callback' => array($this, 'get_admin_permissions_check'),
                'args'                => array(),
            ),
        ));
        register_rest_route($namespace, '/' . $base, array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array($this, 'create_lead'),
                'permission_callback' => array($this, 'get_public_permissions_check'),
                'args'                => $this->get_endpoint_args_for_item_schema(true),
            ),
        ));
    }

    public function get_unreaded_leads($request)
    {
        $leads = $this->db_unreaded();
        return new WP_REST_Response($leads, 200);
    }

    public function get_readed_leads($request)
    {
        $leads = $this->db_readed();
        return new WP_REST_Response($leads, 200);
    }

    public function mark_readed_lead($request)
    {
        $lead = $this->db_mark_readed(
            $request->get_param('id')
        );
        return new WP_REST_Response($lead, 200);
    }

    public function mark_unreaded_lead($request)
    {
        $lead = $this->db_mark_unreaded(
            $request->get_param('id')
        );
        return new WP_REST_Response($lead, 200);
    }

    public function create_lead($request)
    {
        $response = $this->db_create(
            $request->get_param('name'),
            $request->get_param('email'),
            $request->get_param('message'),
        );
        if (isset($response)) {
            return new WP_REST_Response($response, 200);
        }
        return new WP_Error('cant-create', __('message', 'text-domain'), array('status' => 500));
    }

    public function get_admin_permissions_check($request)
    {
        if (!current_user_can('edit_pages')) {
            return new WP_Error('rest_forbidden', esc_html__('You can not access private data.'), array('status' => 401));
        }
        return true;
    }

    public function get_public_permissions_check($request)
    {
        return true;
    }
}
