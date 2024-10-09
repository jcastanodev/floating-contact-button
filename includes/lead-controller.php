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
        add_action('rest_api_init', array($this, 'register_routes'));
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

    public function db_create($name, $email, $message)
    {
        return $this->wpdb->insert($this->table_name, array(
            'name' => $name,
            'email' => $email,
            'message' => $message,
        ));
    }

    public function db_mark_read($lead_id)
    {
        $this->wpdb->update($this->table_name, array(
            'readed_at' => current_time('timestamp'),
        ), array('id' => $lead_id));
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes()
    {
        $version = '1';
        $namespace = 'fcbjc/v' . $version;
        $base = 'lead';
        register_rest_route($namespace, '/' . $base, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_leads'),
                'permission_callback' => array($this, 'get_leads_permissions_check'),
                'args'                => array(),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array($this, 'create_lead'),
                'permission_callback' => array($this, 'create_lead_permissions_check'),
                'args'                => $this->get_endpoint_args_for_item_schema(true),
            ),
        ));
        register_rest_route($namespace, '/' . $base . '/(?P<id>[\d]+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_lead'),
                'permission_callback' => array($this, 'get_lead_permissions_check'),
                'args'                => array(
                    'context' => array(
                        'default' => 'view',
                    ),
                ),
            ),
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array($this, 'update_lead'),
                'permission_callback' => array($this, 'update_lead_permissions_check'),
                'args'                => $this->get_endpoint_args_for_item_schema(false),
            ),
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array($this, 'delete_lead'),
                'permission_callback' => array($this, 'delete_lead_permissions_check'),
                'args'                => array(
                    'force' => array(
                        'default' => false,
                    ),
                ),
            ),
        ));
        register_rest_route($namespace, '/' . $base . '/schema', array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_public_item_schema'),
        ));
    }

    /**
     * Get a collection of items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_leads($request)
    {
        $items = array(); //do a query, call another class, etc
        $data = array();
        foreach ($items as $item) {
            $itemdata = $this->prepare_lead_for_response($item, $request);
            $data[] = $this->prepare_response_for_collection($itemdata);
        }

        return new WP_REST_Response($data, 200);
    }

    /**
     * Get one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_lead($request)
    {
        //get parameters from request
        $params = $request->get_params();
        $item = array(); //do a query, call another class, etc
        $data = $this->prepare_lead_for_response($item, $request);

        //return a response or error based on some conditional
        if (1 == 1) {
            return new WP_REST_Response($data, 200);
        } else {
            return new WP_Error('code', __('message', 'text-domain'));
        }
    }

    /**
     * Create one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
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

    /**
     * Update one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function update_lead($request)
    {
        $item = $this->prepare_lead_for_database($request);

        if (function_exists('slug_some_function_to_update_lead')) {
            $data = null; //slug_some_function_to_update_lead($item);
            if (is_array($data)) {
                return new WP_REST_Response($data, 200);
            }
        }

        return new WP_Error('cant-update', __('message', 'text-domain'), array('status' => 500));
    }

    /**
     * Delete one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function delete_lead($request)
    {
        $lead = $this->prepare_lead_for_database($request);

        if (function_exists('slug_some_function_to_delete_lead')) {
            $deleted = null; //slug_some_function_to_delete_lead($item);
            if ($deleted) {
                return new WP_REST_Response(true, 200);
            }
        }

        return new WP_Error('cant-delete', __('message', 'text-domain'), array('status' => 500));
    }

    /**
     * Check if a given request has access to get items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function get_leads_permissions_check($request)
    {
        //return true; <--use to make readable by all
        return current_user_can('edit_something');
    }

    /**
     * Check if a given request has access to get a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function get_lead_permissions_check($request)
    {
        return $this->get_leads_permissions_check($request);
    }

    /**
     * Check if a given request has access to create items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function create_lead_permissions_check($request)
    {
        return true;
    }

    /**
     * Check if a given request has access to update a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function update_lead_permissions_check($request)
    {
        return current_user_can('edit_something');
    }

    /**
     * Check if a given request has access to delete a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function delete_lead_permissions_check($request)
    {
        return $this->create_lead_permissions_check($request);
    }

    /**
     * Prepare the item for create or update operation
     *
     * @param WP_REST_Request $request Request object
     * @return WP_Error|object $prepared_item
     */
    protected function prepare_lead_for_database($request)
    {
        return array();
    }

    /**
     * Prepare the item for the REST response
     *
     * @param mixed $item WordPress representation of the item.
     * @param WP_REST_Request $request Request object.
     * @return mixed
     */
    public function prepare_lead_for_response($item, $request)
    {
        return array();
    }

    /**
     * Get the query params for collections
     *
     * @return array
     */
    public function get_collection_params()
    {
        return array(
            'page'     => array(
                'description'       => 'Current page of the collection.',
                'type'              => 'integer',
                'default'           => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page' => array(
                'description'       => 'Maximum number of items to be returned in result set.',
                'type'              => 'integer',
                'default'           => 10,
                'sanitize_callback' => 'absint',
            ),
            'search'   => array(
                'description'       => 'Limit results to those matching a string.',
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        );
    }
}
