<?php

namespace EAddonsProFormFilters\Modules\Filters\Actions;

use EAddonsForElementor\Core\Utils;
use EAddonsForElementor\Core\Utils\Form;
use EAddonsForElementor\Base\Base_Action;
use Elementor\Controls_Manager;

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class Query extends Base_Action {

    public static $settings = [];
    public static $settings_raw = [];
    public static $fields = [];
    public static $post_id;

    public function __construct() {
        parent::__construct();

        add_action("elementor/widget/render_content", [$this, 'render_content'], 10, 2);
        add_action("elementor/widget/before_render_content", [$this, 'before_render_content']);
        //add_action('elementor-pro/forms/pre_render', [$this, 'pre_render'], 10, 2);

        add_action('elementor_pro/forms/render/item', [$this, 'set_field_value'], 10, 3);

        // elementor PRO Posts
        add_action('elementor/query/query_results', [$this, 'posts_query'], 11, 2);
        // elementor PRO Archive Posts
        add_action('elementor/theme/posts_archive/query_posts/query_vars', [$this, 'set_query_vars']);

        if (!wp_doing_ajax()) {
            /**/
            add_filter('widget_text', [$this, '_widget_text'], 1, 2);
        }

        add_filter('posts_where', [$this, 'post_title_posts_where'], 10, 2);
        add_filter('pre_get_posts', [$this, 'custom_field_search_query']);
        
        
        add_filter( "elementor/frontend/widget/should_render", [$this, 'should_render'], 10, 2 );
    }
    
    public function should_render($should_render, $widget) {
        //var_dump($widget);
        if (in_array($widget->get_name(), array('posts', 'archive-posts', 'e-query-posts'))) {
            if ($this->get_form_id()) {
                $settings = $this->get_form_settings();
                if (empty($settings['archive_id'])) {
                    return true;
                } else {
                    if ($widget->get_id() == $settings['archive_id']) {
                        return true;
                    }
                }
            } else {
                // check if in document there is a Form with Query Action
                // get settings and compare archive ID
            }
        }
        return $should_render;
    }

    /**
     * Get Name
     *
     * Return the action name
     *
     * @access public
     * @return string
     */
    public function get_name() {
        return 'query';
    }

    /**
     * Get Label
     *
     * Returns the action label
     *
     * @access public
     * @return string
     */
    public function get_label() {
        return __('Query Posts Search & Filter', 'e-addons');
    }

    public function get_icon() {
        return 'eadd-el-form-pro-filters-action';
    }

    public function get_pid() {
        return 9233;
    }

    /**
     * Register Settings Section
     *
     * Registers the Action controls
     *
     * @access public
     * @param \Elementor\Widget_Base $widget
     */
    public function register_settings_section($widget) {

        $this->start_controls_section($widget);

        /* $widget->add_control(
          'e_form_query_type',
          [
          'label' => __('Query Type', 'e-addons'),
          'type' => \Elementor\Controls_Manager::HIDDEN,
          'type' => \Elementor\Controls_Manager::CHOOSE,
          'options' => [
          'post' => [
          'title' => __('Post', 'e-addons'),
          'icon' => 'fa fa-file-text-o',
          ],
          'user' => [
          'title' => __('User', 'e-addons'),
          'icon' => 'fa fa-user',
          ],
          'term' => [
          'title' => __('Term', 'e-addons'),
          'icon' => 'fa fa-tag',
          ],
          ],
          'toggle' => false,
          'label_block' => 'true',
          'default' => 'post',
          ]
          ); */

        $repeater_fields = new \Elementor\Repeater();

        // https://developer.wordpress.org/reference/classes/wp_query/
        $repeater_fields->add_control(
                'e_form_query_args_key', [
            'label' => __('Filter Key', 'e-addons'),
            'description' => __('Is the key of the Filter in the WP Query', 'e-addons'),
            'type' => Controls_Manager::SELECT,
            //'options' => $this->get_wp_query_args(),
            'groups' => $this->get_wp_query_args_groups(),
            'label_block' => true,
                ]
        );

        $repeater_fields->add_control(
                'e_form_query_args_title', [
            'label' => __('Search on Title only', 'e-addons'),
            'type' => Controls_Manager::SWITCHER,
            'condition' => [
                'e_form_query_args_key' => 's',
            ]
                ]
        );
        $repeater_fields->add_control(
                'e_form_query_args_search_meta', [
            'label' => __('Search in Meta Field', 'e-addons'),
            'description' => __('Search text also inside this extra Meta Fields', 'e-addons'),
            'type' => 'e-query',
            'placeholder' => __('Select Field', 'e-addons'),
            'label_block' => true,
            'multiple' => true,
            'query_type' => 'metas',
            'object_type' => 'post',
            'condition' => [
                'e_form_query_args_title' => '',
                'e_form_query_args_key' => ['s'],
            ]
                ]
        );

        $repeater_fields->add_control(
                'e_form_query_args_tax_query_include_children', [
            'label' => __('Include Children', 'e-addons'),
            'type' => Controls_Manager::SWITCHER,
            //'default' => 'yes',
            'condition' => [
                'e_form_query_args_key' => 'tax_query',
            ]
                ]
        );

        $repeater_fields->add_control(
                'e_form_query_args_date_query_param', [
            'label' => __('Date Param', 'e-addons'),
            'type' => Controls_Manager::CHOOSE,
            'options' => [
                'before' => [
                    'title' => __('Before', 'e-addons'),
                    'icon' => 'eicon-long-arrow-left',
                ],
                'after' => [
                    'title' => __('After', 'e-addons'),
                    'icon' => 'eicon-long-arrow-right',
                ],
            ],
            'default' => 'after',
            'toggle' => false,
            'label_block' => 'true',
            'condition' => [
                'e_form_query_args_key' => 'date_query',
            ]
                ]
        );

        $repeater_fields->add_control(
                'e_form_query_args_meta_query_key', [
            'label' => __('Custom Meta Field', 'e-addons'),
            'description' => __('Is the key of the Cumtom Meta Field for Filter in the WP Query', 'e-addons'),
            'type' => 'e-query',
            'placeholder' => __('Select Field', 'e-addons'),
            'label_block' => true,
            'query_type' => 'metas',
            'object_type' => 'post',
            'condition' => [
                'e_form_query_args_key' => ['meta_query'],
            ]
                ]
        );
        $repeater_fields->add_control(
                'e_form_query_args_meta_query_compare', [
            'label' => __('Operator to test the Meta', 'e-addons'),
            'type' => Controls_Manager::SELECT,
            'options' => [
                "=" => "=",
                ">" => "&gt;",
                ">=" => "&gt;=",
                "<" => "&lt;",
                "<=" => "&lt;=",
                "!=" => "!=",
                "LIKE" => "LIKE",
                "RLIKE" => "RLIKE",
                "NOT LIKE" => "NOT LIKE",
                "IN" => "IN (...)",
                "NOT IN" => "NOT IN (...)",
                "BETWEEN" => "BETWEEN",
                "NOT BETWEEN" => "NOT BETWEEN",
                "EXISTS" => "EXISTS",
                "NOT EXISTS" => "NOT EXISTS",
                "REGEXP" => "REGEXP",
                "NOT REGEXP" => "NOT REGEXP",
            ],
            'default' => '=',
            'condition' => [
                'e_form_query_args_key' => 'meta_query',
            ]
                ]
        );
        $repeater_fields->add_control(
                'e_form_query_args_meta_query_type', [
            'label' => __('Type of Meta Data', 'e-addons'),
            'type' => Controls_Manager::SELECT,
            'options' => [
                '' => __('Automatic'),
                'CHAR' => __('Char (Default)'),
                'NUMERIC' => __('Numeric'),
                'BINARY' => __('Binary (Bool)'),
                'DATE' => __('Date'),
                'DATETIME' => __('DateTime'),
                'DECIMAL' => __('Decimal'),
                'SIGNED' => __('Signed'),
                'TIME' => __('Time'),
                'UNSIGNED' => __('Unsigned'),
            ],
            'condition' => [
                'e_form_query_args_key' => 'meta_query',
            ]
                ]
        );

        $repeater_fields->add_control(
                'e_form_query_args_value', [
            'label' => __('Filter Value', 'e-addons'),
            'placeholder' => '[field id="title"]',
            'description' => __('Is the value of the Filter, use Form Field values', 'e-addons'),
            'type' => Controls_Manager::TEXT,
                ]
        );
        $repeater_fields->add_control(
                'e_form_query_args_multiple', [
            'label' => __('Accept Multiple Values', 'e-addons'),
            'type' => Controls_Manager::SWITCHER,
            'condition' => [
                'e_form_query_args_key!' => ['s', 'date_query'],
            ]
                ]
        );

        $repeater_fields->add_control(
                'e_form_query_args_relation', [
            'label' => __('Filter Relation', 'e-addons'),
            'description' => __('Is the Relation of the Filter in the WP Query', 'e-addons'),
            'type' => Controls_Manager::SELECT,
            'options' => [
                'AND' => 'AND',
                'OR' => 'OR',
            ],
            'condition' => [
                'e_form_query_args_key' => ['tax_query'], // ['s', 'date_query'],
                'e_form_query_args_multiple!' => '',
            ],
            'default' => 'OR',
                ]
        );

        //
        $widget->add_control(
                'e_form_query_args', [
            'label' => __('Filter Args', 'e-addons'),
            'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $repeater_fields->get_controls(),
            'title_field' => '{{{ e_form_query_args_key }}} = {{{ e_form_query_args_value }}}',
                ]
        );

        $widget->add_control(
                'e_form_query_relation',
                [
                    'label' => __('Change Sub Query Relations', 'e-addons'),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'separator' => 'before',
                ]
        );
        $widget->add_control(
                'e_form_query_relation_tax', [
            'label' => __('Tax Query Relation', 'e-addons'),
            'type' => Controls_Manager::SELECT,
            'options' => [
                'AND' => 'AND',
                'OR' => 'OR',
            ],
            'condition' => [
                'e_form_query_relation!' => '',
                'e_form_query_args!' => ['', []],
            ],
            'default' => 'AND',
                ]
        );
        $widget->add_control(
                'e_form_query_relation_date', [
            'label' => __('Date Query Relation', 'e-addons'),
            'type' => Controls_Manager::SELECT,
            'options' => [
                'AND' => 'AND',
                'OR' => 'OR',
            ],
            'condition' => [
                'e_form_query_relation!' => '',
                'e_form_query_args!' => ['', []],
            ],
            'default' => 'AND',
                ]
        );
        $widget->add_control(
                'e_form_query_relation_meta', [
            'label' => __('Meta Query Relation', 'e-addons'),
            'type' => Controls_Manager::SELECT,
            'options' => [
                'AND' => 'AND',
                'OR' => 'OR',
            ],
            'condition' => [
                'e_form_query_relation!' => '',
                'e_form_query_args!' => ['', []],
            ],
            'default' => 'AND',
                ]
        );

        $widget->add_control(
                'e_form_query_element_id',
                [
                    'label' => __('Archive Element ID', 'e-addons'),
                    'type' => 'element-id', //\Elementor\Controls_Manager::TEXT,
                    'separator' => 'before',
                ]
        );
        $widget->add_control(
                'e_form_query_ajax',
                [
                    'label' => __('Update Archive in Ajax', 'e-addons'),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    //'default' => 'yes',
                    //'frontend_available' => true,
                    'condition' => [
                        'e_form_query_element_id!' => '',
                    ]
                ]
        );

        $extra_fields = new \Elementor\Repeater();
        $extra_fields->add_control(
                'e_form_query_extra_id', [
            'label' => __('Extra Widget ID', 'e-addons'),
            'type' => Controls_Manager::TEXT,
            'label_block' => true,
                ]
        );
        $widget->add_control(
                'e_form_query_extra_ids', [
            'label' => __('Extra Widgets', 'e-addons'),
            'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $repeater_fields->get_controls(),
            'title_field' => '{{{ e_form_query_extra_id }}}',
            //'frontend_available' => true,
            'prevent_empty' => false,
                ]
        );

        $widget->add_control(
                'e_form_query_no_result',
                [
                    'label' => __('No Result Text', 'e-addons'),
                    'type' => \Elementor\Controls_Manager::WYSIWYG,
                    'default' => __('Sorry, no result found', 'e-addons'),
                    'separator' => 'before',
                ]
        );

        /*
          $widget->add_control(
          'e_form_query_filters',
          [
          'label' => __('Show Active filters', 'e-addons'),
          'type' => \Elementor\Controls_Manager::SWITCHER,
          'separator' => 'before',
          ]
          );
          $widget->add_control(
          'e_form_query_counter',
          [
          'label' => __('Show Counter', 'e-addons'),
          'type' => \Elementor\Controls_Manager::SWITCHER,
          'separator' => 'before',
          ]
          );
          $widget->add_control(
          'e_form_query_counter_txt',
          [
          'label' => __('Counter Text', 'e-addons'),
          'type' => \Elementor\Controls_Manager::TEXT,
          'default' => __('Showing {{query.posts|length}} of {{query.found_posts}} results'),
          'condition' => [
          'e_form_query_counter!' => '',
          ]
          ]
          );
          $widget->add_control(
          'e_form_query_counter_position', [
          'label' => __('Counter Position', 'e-addons'),
          'type' => Controls_Manager::CHOOSE,
          'options' => [
          'before' => [
          'title' => __('Before', 'e-addons'),
          'icon' => 'eicon-arrow-up',
          ],
          'after' => [
          'title' => __('After', 'e-addons'),
          'icon' => 'eicon-arrow-down',
          ],
          ],
          'default' => 'after',
          'toggle' => false,
          'label_block' => 'true',
          'condition' => [
          'e_form_query_counter!' => '',
          ]
          ]
          );
         */

        Utils::add_help_control($this, $widget);
        $widget->end_controls_section();
    }

    /**
     * Run
     *
     * Runs the action after submit
     *
     * @access public
     * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record
     * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
     */
    public function run($record, $ajax_handler) {
        if ($ajax_handler) {
            if ($ajax_handler->is_success) {
                $post_id = absint($_POST['post_id']);
                //$form_id = $this->get_form_id();
                $settings = $this->get_form_settings($record);

                add_filter('widget_text', [$this, '_widget_text'], 1, 2);

                global $post, $wp_query;
                if (!empty($_POST['wp_query'])) {
                    $pre_wp_query = stripslashes($_POST['wp_query']);
                    $pre_wp_query = json_decode($pre_wp_query, true);   
                    $wp_query->query = $pre_wp_query['query'];
                    $wp_query->query_vars = $pre_wp_query['query_vars'];
                }
                if (!empty($_POST['queried_id'])) {
                    // force $post in ajax
                    $queried_id = absint($_POST['queried_id']);                    
                    $post = get_post($queried_id);
                    $wp_query->queried_object = $post;
                    $wp_query->queried_object_id = $queried_id;
                }

                $element_id = !empty($settings['e_form_query_element_id']) ? $settings['e_form_query_element_id'] : false;
                $content = $this->get_element_content($element_id);

                if (!$content) {
                    $content = '<div class="elementor-element elementor-no-results elementor-element-' . $element_id . '" data-id="' . $element_id . '"><div class="elementor-widget-container">' . $settings['e_form_query_no_result'] . '</div></div>';
                    //$content = $this->add_filters($settings, $content);
                }

                if (!empty($settings['e_form_query_extra_ids'])) {
                    $extra_ids = wp_list_pluck($settings['e_form_query_extra_ids'], 'e_form_query_extra_id');
                    foreach ($extra_ids as $element_id) {
                        $content .= $this->get_element_content($element_id, true);
                        //var_dump($content); die();
                    }
                }

                if ($content) {
                    wp_send_json_error([
                        'message' => $content,
                        'data' => $ajax_handler->data,
                    ]);
                    die();
                }
            }
            //var_dump($ajax_handler);
            $ajax_handler->add_error_message(__('Error on Search'));
        }
    }

    public function get_element_content($element_id, $show_empty = false) {
        $content = false;
        if ($element_id) {
            $widget_type = Utils::get_widget_type_by_id($element_id);
            if ($widget_type) {
                switch ($widget_type) {

                    case 'active-filters':
                    case 'archive-info':
                    case 'text-editor':
                    case 'posts':
                    case 'archive-posts':
                    case 'e-query-posts':
                        //case 'wc-archive-products':                    
                        $element = Utils::get_element_instance_by_id($element_id, self::$post_id);
                        if ($element) {
                            ob_start();
                            $element->print_element();
                            $content = ob_get_clean();
                            if (!$content && $show_empty) {
                                Utils::add_render_attributes($element);
                                ob_start();
                                $element->before_render();
                                $element->after_render();
                                $content = ob_get_clean();
                            }
                        }
                }
            }
        }
        return $content;
    }

    public function get_query() {
        $settings = $this->get_form_settings();

        // check for a specific Loop Widget
        if (!empty($settings['e_form_query_element_id'])) {
            $element = Utils::get_element_instance_by_id($settings['e_form_query_element_id'], self::$post_id);
            if ($element) {
                switch ($element->get_name()) {
                    case 'posts':
                    case 'archive-posts':
                        return $element->get_query();
                }
            }
        }

        // update the wp_query with form params
        global $wp_query;
        $this->posts_query($wp_query);
        return $wp_query;
    }

    public function get_form_settings($record = false) {
        if (empty(self::$settings)) {
            // init
            if ($record) {
                self::$fields = $fields = Form::get_form_data($record);
                self::$settings = $this->get_settings(true, $fields);
                self::$settings_raw = $this->get_settings(false);
            } else {
                $form_id = $this->get_form_id();
                if (!empty($form_id)) {
                    self::$fields = $fields = $_GET;
                    $form = Utils::get_element_instance_by_id($form_id, self::$post_id);
                    if ($form) {
                        if (empty($fields)) {
                            // get form default values
                        }
                        self::$settings = $this->get_settings(true, $fields);
                        self::$settings_raw = $this->get_settings(false);
                        //self::$settings = $form->get_settings_for_display();
                        //self::$settings_raw = $form->get_settings();
                        //var_dump(self::$settings);
                    }
                }
            }
            if (!empty($_REQUEST['post_id'])) {
                self::$post_id = intval($_REQUEST['post_id']);
            }
        }
        return self::$settings;
    }

    public function inject_content($content, $extra = '', $position = 'before') {
        /* $container = '<div class="elementor-widget-container">';
          $pos_widget_container = strpos($content, $container);
          $posts = 'elementor-posts-container ';
          $pos_posts_container = strpos($content, $posts);
          if ($pos_widget_container !== false && (!$pos_posts_container || $pos_widget_container < $pos_posts_container)) {
          $content = preg_replace('/' . $container . '/', $container.$extra, $content, 1);
          } else { */
        if ($position == 'before') {
            $content = $extra . $content;
        } else {
            $content = $content . $extra;
        }
        //}
        return $content;
    }

    public function update_content($content, $element) {
        $settings = $this->get_form_settings();
        $fields = self::$fields;

        global $wp_query;
        $query = $wp_query;
        $found_posts = 0;
        $element_id = !empty($settings['e_form_query_element_id']) ? $settings['e_form_query_element_id'] : false;

        if (!$element_id || $element_id == $element->get_id()) {
            $archive = $element;
        } else {
            $archive = Utils::get_element_instance_by_id($element_id, self::$post_id);
        }
        if ($archive) {
            $query = $archive->get_query();
            if ($query) {
                $found_posts = count($query->posts); //found_posts;
            }
        }

        if ((!$element_id || $element_id == $element->get_id()) && !$found_posts && !empty($settings['e_form_query_no_result'])) {
            // this might should not works on Archive Posts
            $no_result = $settings['e_form_query_no_result'];
            $content = $this->inject_content($content, $no_result);
        }

        if ($element->get_name() == 'active-filters') {
            ob_start();
            $filters = $this->get_filters(self::$fields);
            $element->render_filters($filters, $settings, self::$settings_raw);
            $content = ob_get_clean();
        }

        $content = Utils::fix_ajax_pagination($content, $element, $fields);

        return $content;
    }

    public function get_filters($fields) {
        $settings = $this->get_form_settings();
        $settings_raw = self::$settings_raw;

        foreach ($fields as $custom_id => $field) {
            if (empty($field)) {
                unset($fields[$custom_id]);
            }
        }

        if (!empty($settings_raw['e_form_query_args'])) {
            foreach ($settings_raw['e_form_query_args'] as $akey => $arg) {
                if (!empty($arg['e_form_query_args_value'])) {
                    switch ($arg['e_form_query_args_key']) {
                        case 'order':
                        case 'orderby':
                        case 'posts_per_page':
                            foreach ($fields as $custom_id => $field) {
                                if (strpos($arg['e_form_query_args_value'], '[field id="' . $custom_id . '"]') !== false) {
                                    unset($fields[$custom_id]);
                                }
                            }
                    }
                }
            }
        }

        return $fields;
    }

    // updating the Widget Query with Form args
    public function posts_query($query, $widget = false) {
        $settings = $this->get_form_settings();

        if (!empty($settings['e_form_query_element_id']) && $widget) {
            $element_ids = array();
            if (!empty($settings['e_form_query_extra_ids'])) {
                $element_ids = wp_list_pluck($settings['e_form_query_extra_ids'], 'e_form_query_extra_id');
            }
            $element_ids[] = $settings['e_form_query_element_id'];
            //var_dump($element_ids);
            if (!in_array($widget->get_id(), $element_ids)) {
                return;
            }
        }

        $query_vars = $query->query_vars;
        $args = $this->get_args();

        if (!empty($args)) {
            //echo '<pre>'; var_dump(array_filter($query->query_vars)); echo '</pre>';
            $query->query_vars = $this->set_query_vars($query_vars, $args);
            //echo '<pre>'; var_dump(array_filter($query->query_vars)); echo '</pre>'; die();
            $posts = $query->get_posts();
            if (!count($posts)) {
                $query->found_posts = count($posts);
            }

            if (empty($posts)) {
                //var_dump($query->query_vars);
                if (!empty($args['page']) || !empty($args['paged'])) {
                    if (wp_doing_ajax()) {
                        // reset page
                        unset($args['page']);
                        unset($args['paged']);
                        $query->query_vars = $this->set_query_vars($query_vars, $args);
                        $posts = $query->get_posts();
                        if (!count($posts)) {
                            $query->found_posts = count($posts);
                        }
                    } else {
                        $location = add_query_arg('page', 1);
                        //wp_redirect($location); die();
                        echo '<script type="text/javascript">window.location = "' . $location . '";</script>';
                    }
                }
            }

            if (wp_doing_ajax()) {
                // pagination fix
                global $wp_query;
                $wp_query = $query;
            }
        }
    }

    public function set_query_vars($query_vars, $args = array()) {
        if (empty($args)) {
            $args = $this->get_args();
        }
        $query_vars = array_merge($query_vars, $args);
        //echo '<pre>'; var_dump(array_filter($query_vars)); echo '</pre>';// die();
        return $query_vars;
    }

    public function get_args() {
        $settings = $this->get_form_settings();

        $args = array();
        if (!empty($settings['e_form_query_args'])) {
            if (!empty($_REQUEST['page'])) {
                $args['paged'] = intval($_REQUEST['page']);
                $args['page'] = intval($_REQUEST['page']);
            } else {
                global $wp_query;
                if (!empty($wp_query->query['paged'])) {
                    $args['paged'] = $wp_query->query['paged'];
                    if (empty($wp_query->query['page'])) {
                        $args['page'] = $args['paged'];
                    }
                }
                if (!empty($wp_query->query['page'])) {
                    $args['page'] = $wp_query->query['page'];
                    if (empty($wp_query->query['paged'])) {
                        $args['paged'] = $args['page'];
                    }
                }
            }
            foreach ($settings['e_form_query_args'] as $akey => $arg) {
                if (!empty($arg['e_form_query_args_value'])) {
                    $value = $arg['e_form_query_args_value'];
                    //var_dump($value);
                    if (!empty($arg['e_form_query_args_multiple'])) {
                        $value = Utils::explode($value);
                        if (is_numeric(reset($value))) {
                            $value = array_map('intval', $value);
                        }
                    }

                    switch ($arg['e_form_query_args_key']) {

                        case 'date_query':
                            $date_param = $arg['e_form_query_args_date_query_param'];
                            $date_query = array(
                                $date_param => $value,
                            );
                            if (empty($args['date_query'])) {
                                $args['date_query'] = array(
                                    $date_query
                                );
                            } else {
                                $args['date_query'][] = $date_query;
                            }
                            break;

                        case 'tax_query':
                            if (empty($arg['e_form_query_args_relation']) || $arg['e_form_query_args_relation'] == 'OR') {
                                $term_id = is_array($value) ? reset($value) : $value;
                                if ($term_id) {
                                    $term = Utils::get_term($term_id);
                                    $tax_query = array(
                                        'taxonomy' => $term->taxonomy,
                                        'terms' => $value,
                                        'include_children' => !empty($arg['e_form_query_args_tax_query_include_children']),
                                    );
                                }
                            } else {
                                // AND
                                if (!is_array($value))
                                    $value = array($value);
                                $tax_query = array(
                                    'relation' => $arg['e_form_query_args_relation'],
                                );
                                foreach ($value as $term_id) {
                                    $term = Utils::get_term($term_id);
                                    $tax_query[] = array(
                                        'taxonomy' => $term->taxonomy,
                                        'terms' => $term_id,
                                        'include_children' => !empty($arg['e_form_query_args_tax_query_include_children']),
                                    );
                                }
                            }
                            if (empty($args['tax_query'])) {
                                $args['tax_query'] = array(
                                    //'relation' => $arg['e_form_query_args_relation'],
                                    $tax_query
                                );
                            } else {
                                $args['tax_query'][] = $tax_query;
                            }
                            break;

                        case 'meta_query':
                            $meta_key = $arg['e_form_query_args_meta_query_key'];
                            $meta_type = '';
                            if (!empty(self::$settings_raw['e_form_query_args'][$akey]['e_form_query_args_value'])) {
                                $meta_value = self::$settings_raw['e_form_query_args'][$akey]['e_form_query_args_value'];
                                if (strpos($meta_value, '[field id=') !== false) {
                                    $meta_value = str_replace('[field id=', '', $meta_value);
                                    $meta_value = str_replace('"', '', $meta_value);
                                    $meta_value = str_replace(']', '', $meta_value);
                                    $meta_value = trim($meta_value);
                                    $field = Form::get_field($meta_value, self::$settings);
                                    if ($field && !empty($field['field_type'])) {
                                        if ($field['field_type'] == 'number' || is_numeric($value)) {
                                            $meta_type = 'numeric';
                                        }
                                        $meta_type = !empty($arg['e_form_query_args_meta_query_type']) ? $arg['e_form_query_args_meta_query_type'] : $meta_type;
                                        if (empty($meta_key)) {
                                            if ($field['field_type'] == 'query' && !empty($field['e_query_type_metas_filter_postmeta'])) {
                                                $meta_key = $field['e_query_type_metas_filter_postmeta'];
                                            }
                                        }
                                    }
                                }
                            }

                            $meta_query = array(
                                'key' => $meta_key,
                                'value' => $value,
                            );
                            if ($meta_type) {
                                $meta_query['type'] = $meta_type;
                            }
                            if (!empty($arg['e_form_query_args_meta_query_compare'])) {
                                $meta_query['compare'] = $arg['e_form_query_args_meta_query_compare'];
                                switch ($meta_query['compare']) {
                                    case 'EXISTS':
                                    case 'NOT EXISTS':
                                        unset($meta_query['value']);
                                        break;
                                    case 'BETWEEN':
                                    case 'NOT BETWEEN':
                                        $meta_query['value'] = explode(',', $meta_query['value'], 2);
                                        break;
                                    case 'IN':
                                    case 'NOT IN':
                                        $meta_query['value'] = Utils::explode($meta_query['value']);
                                        break;
                                }
                            }
                            if (empty($args['meta_query'])) {
                                $args['meta_query'] = array(
                                    $meta_query
                                );
                            } else {
                                $args['meta_query'][] = $meta_query;
                            }
                            break;

                        case 'orderby':

                            $tmp = explode('/', $value);
                            if (count($tmp) >= 2) {
                                $value = array_shift($tmp);
                                foreach ($tmp as $o) {
                                    if (in_array($o, array('asc', 'ASC', 'desc', 'DESC'))) {
                                        $args['order'] = strtoupper($o);
                                    }
                                    if (in_array($o, array('NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED'))) {
                                        $args['meta_type'] = strtoupper($o);
                                    }
                                    if (in_array($o, array('num', '_num', 'NUM'))) {
                                        $args['meta_type'] = 'NUMERIC';
                                    }
                                    if (in_array($o, array('date', '_date'))) {
                                        $args['meta_type'] = 'DATE';
                                    }
                                }
                            }

                            $orderby = array('none', 'ID', 'author', 'title', 'name', 'type', 'date', 'modified', 'parent', 'rand', 'comment_count', 'relevance', 'menu_order', 'meta_value', 'meta_type', 'meta_value_num', 'post__in', 'post_name__in', 'post_parent__in');
                            if (in_array($value, $orderby)) {
                                $args[$arg['e_form_query_args_key']] = $value;
                            } else {
                                $args[$arg['e_form_query_args_key']] = 'meta_value';
                                $args['meta_key'] = $value;
                            }
                            break;

                        case 's':
                            if (!empty($arg['e_form_query_args_title'])) {
                                $args['e_post_title'] = $value;
                            } else {
                                $args[$arg['e_form_query_args_key']] = $value;
                                if (!empty($arg['e_form_query_args_search_meta'])) {
                                    if (empty($args['e_search_meta_key'])) {
                                        $args['e_search_meta_key'] = $arg['e_form_query_args_search_meta'];
                                    } else {
                                        $args['e_search_meta_key'] = array_merge($args['e_search_meta_key'], $arg['e_form_query_args_search_meta']);
                                    }
                                }
                            }
                            break;

                        case 'posts_per_page':
                            $args['posts_per_archive_page'] = $value;

                        default:
                            $args[$arg['e_form_query_args_key']] = $value;
                    }
                }
            }

            if (!empty($settings['e_form_query_relation'])) {
                if (!empty($settings['e_form_query_relation_tax'])) {
                    if (!empty($args['tax_query'])) {
                        $args['tax_query']['relation'] = $settings['e_form_query_relation_tax'];
                    }
                }
                if (!empty($settings['e_form_query_relation_date'])) {
                    if (!empty($args['date_query'])) {
                        foreach ($args['date_query'] as $key => $date) {
                            $args['date_query'][$key]['relation'] = $settings['e_form_query_relation_date'];
                        }
                    }
                }
                if (!empty($settings['e_form_query_relation_meta'])) {
                    if (!empty($args['meta_query'])) {
                        $args['meta_query']['relation'] = $settings['e_form_query_relation_meta'];
                    }
                }
            }
        }
        if (!empty($settings['e_form_query_extra_ids'])) {
            $widgets = wp_list_pluck($settings['e_form_query_extra_ids'], 'e_form_query_extra_id');
            foreach ($widgets as $widget_id) {
                if ($widget_id) {
                    $widget = Utils::get_element_instance_by_id($widget_id, self::$post_id);
                    if ($widget) {
                        switch ($widget->get_name()) {
                            case 'posts-per-page':
                                $aform_fields = $widget->get_settings('form_fields');
                                foreach ($aform_fields as $aform_field) {
                                    if (!empty(self::$fields[$aform_field['custom_id']])) {
                                        $value = self::$fields[$aform_field['custom_id']];
                                        if ($value) {
                                            $args['posts_per_page'] = $value;
                                            $args['posts_per_archive_page'] = $value;
                                        }
                                    }
                                }
                                break;
                            case 'order-by':
                                $aform_fields = $widget->get_settings('form_fields');
                                foreach ($aform_fields as $aform_field) {
                                    if (!empty(self::$fields[$aform_field['custom_id']])) {
                                        $value = self::$fields[$aform_field['custom_id']];
                                        if ($value) {
                                            $tmp = explode('/', $value);
                                            if (count($tmp) >= 2) {
                                                $value = array_shift($tmp);
                                                foreach ($tmp as $o) {
                                                    if (in_array($o, array('asc', 'ASC', 'desc', 'DESC'))) {
                                                        $args['order'] = strtoupper($o);
                                                    }
                                                    if (in_array($o, array('NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED'))) {
                                                        $args['meta_type'] = strtoupper($o);
                                                    }
                                                    if (in_array($o, array('num', '_num', 'NUM'))) {
                                                        $args['meta_type'] = 'NUMERIC';
                                                    }
                                                    if (in_array($o, array('date', '_date'))) {
                                                        $args['meta_type'] = 'DATE';
                                                    }
                                                }
                                            }
                                            $orderby = array('none', 'ID', 'author', 'title', 'name', 'type', 'date', 'modified', 'parent', 'rand', 'comment_count', 'relevance', 'menu_order', 'meta_value', 'meta_type', 'meta_value_num', 'post__in', 'post_name__in', 'post_parent__in');
                                            if (in_array($value, $orderby)) {
                                                $args['orderby'] = $value;
                                            } else {
                                                $args['orderby'] = 'meta_value';
                                                $args['meta_key'] = $value;
                                            }
                                        }
                                    }
                                }
                                break;
                        }
                    } else {
                        //var_dump($widget_id); var_dump(self::$post_id); die();
                    }
                }
            }
        }
        //echo '<pre>'; var_dump($args); echo '<pre>'; //die();
        return $args;
    }

    public function set_field_value($item, $index, $form) {
        $settings = $this->get_form_settings(); // set settings and fields
        $forms = array();
        if (!empty($settings['e_form_query_extra_ids'])) {
            $forms = wp_list_pluck($settings['e_form_query_extra_ids'], 'e_form_query_extra_id');
        }
        $forms[] = $this->get_form_id();

        if (in_array($form->get_id(), $forms)) {
            $custom_id = $item['custom_id'];
            if (!empty(self::$fields[$custom_id])) {
                $field_value = self::$fields[$custom_id];
                $field_value = Utils::to_string($field_value);
                $form->remove_render_attribute('input' . $index, 'value');
                $form->add_render_attribute('input' . $index, 'value', $field_value);
                $item['field_value'] = $field_value;
            }
        }
        return $item;
    }

    public function pre_render($settings, $widget) {
        if (in_array($widget->get_name(), array('form', 'order-by', 'posts-per-page'))) {
            // set defaults
            if ($this->get_form_id() == $widget->get_id()) {
                $form_settings = $this->get_form_settings();
                $form_fields = $settings['form_fields'];
                foreach ($form_fields as $fkey => $field) {
                    $custom_id = $field['custom_id'];
                    if (!empty(self::$fields[$custom_id])) {
                        $form_fields[$fkey]['field_value'] = self::$fields[$custom_id];
                        //var_dump($form_fields[$fkey]);
                    }
                }
                $widget->set_settings('form_fields', $form_fields);
            }
        }
    }

    public function before_render_content($widget) {
        switch ($widget->get_name()) {
            case 'form':
                $actions = $widget->get_settings('submit_actions');
                if (in_array($this->get_name(), $actions)) {
                    $widget->add_render_attribute('form', 'data-query', true);
                    $ajax = $widget->get_settings('e_form_query_ajax');
                    if (!empty($ajax)) {
                        $widget->add_render_attribute('form', 'data-ajax', $ajax);
                    }
                    $widget->add_render_attribute('form', 'data-archive-id', $widget->get_settings('e_form_query_element_id'));
                    $extra = $widget->get_settings('e_form_query_extra_ids');
                    if (!empty($extra)) {
                        $widgets = wp_list_pluck($extra, 'e_form_query_extra_id');
                        $widgets = array_filter($widgets);
                        if (!empty($widgets)) {
                            $widget->add_render_attribute('form', 'data-widgets', wp_json_encode($widgets));
                        }
                    }
                }
                break;
        }
    }

    public function render_content($content, $widget) {
        switch ($widget->get_name()) {
            case 'form':
                $settings = $widget->get_settings();
                if (in_array($this->get_name(), $settings['submit_actions'])) {
                    $content = $content . '<style>.elementor-element-' . $widget->get_id() . ' .elementor-message{display:none;}</style>';
                    if (!empty($settings['e_form_query_ajax'])) {
                        global $wp_query;
                        $content = str_replace('</form>', '<textarea class="elementor-hidden" name="wp_query">'. wp_json_encode($wp_query).'</textarea></form>', $content);
                    }
                    wp_enqueue_script('e-addons-form-filter-query');
                }
                break;
            case 'active-filters':
                if ($this->get_form_id()) {
                    $form_settings = $this->get_form_settings();
                    if (!empty($form_settings['e_form_query_extra_ids'])) {
                        $widgets = wp_list_pluck($form_settings['e_form_query_extra_ids'], 'e_form_query_extra_id');
                        if (in_array($widget->get_id(), $widgets)) {
                            $content = $this->update_content($content, $widget);
                        }
                    }
                }
                break;
            case 'archive-posts':
            case 'posts':
            case 'e-query-posts':
                if ($this->get_form_id()) {
                    $form_settings = $this->get_form_settings();
                    $widgets = array();
                    if (!empty($form_settings['e_form_query_extra_ids'])) {
                        $widgets = wp_list_pluck($form_settings['e_form_query_extra_ids'], 'e_form_query_extra_id');
                    }
                    if (empty($form_settings['e_form_query_element_id'])) {
                        $widgets[] = $widget->get_id();
                    } else {
                        $widgets[] = $form_settings['e_form_query_element_id'];
                    }
                    if (in_array($widget->get_id(), $widgets)) {
                        $content = $this->update_content($content, $widget);
                    }
                }
                break;

            default:
        }
        return $content;
    }

    public function _widget_text($content, $settings) {
        if ($this->get_form_id()) {
            $form_settings = $this->get_form_settings();
            $element_id = !empty($form_settings['e_form_query_element_id']) ? $form_settings['e_form_query_element_id'] : false;
            if (!$element_id || empty($settings['archive_id']) || $element_id == $settings['archive_id']) {
                if (!empty($form_settings['e_form_query_extra_ids'])) {
                    //$widgets = wp_list_pluck($form_settings['e_form_query_extra_ids'], 'e_form_query_extra_id');
                    //if (in_array($widget->get_id(), $widgets)) {
                    if (strpos($content, '{{query.') !== false || strpos($content, '{{ query.') !== false) {
                        //$widget_text = Utils::get_element_instance_by_id($aform_id, self::$post_id);
                        return apply_filters('_widget_text_content', $content, $element_id, self::$post_id);
                    }
                }
            }
        }
        //$content = $this->_widget_archive_info_text($content, $settings);
        return apply_filters('_widget_archive_info_text', $content, $settings);
    }

    public function post_title_posts_where($where, $wp_query) {
        global $wpdb;
        if ($post_title = $wp_query->get('e_post_title')) {
            $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql($wpdb->esc_like($post_title)) . '%\'';
        }
        return $where;
    }

    /*
     * Extend wp search to include custom post meta 
     */

    public function custom_field_search_query($query) {
        if ($custom_field = $query->get('e_search_meta_key')) {

            $matched_meta = array();
            if (!empty($custom_field)) {
                if (!is_array($custom_field)) {
                    $custom_field = array($custom_field);
                }
                foreach ($custom_field as $key) {
                    $args = array(
                        'post_type' => $query->get('post_type'),
                        'post_status' => $query->get('post_status'),
                        'fields' => 'ids',
                        'nopaging' => true,
                        'meta_query' => array(
                            array(
                                'key' => $key,
                                'value' => $query->query_vars['s'],
                                'compare' => 'LIKE'
                            ),
                        ),
                    );
                    $tmp_query = new \WP_Query($args);
                    $matched_meta = array_merge($matched_meta, $tmp_query->posts);
                }
            }

            if (!empty($matched_meta)) {
                $args = array(
                    'post_type' => $query->get('post_type'),
                    'post_status' => $query->get('post_status'),
                    'fields' => 'ids',
                    'nopaging' => true,
                    's' => $query->query_vars['s'],
                );
                $tmp_query = new \WP_Query($args);
                $matched_search = $tmp_query->posts;

                $matched = array_merge($matched_meta, $matched_search); // OR

                $query->query_vars['post__in'] = $matched;

                $query->query_vars['s'] = '';
            }
            //var_dump($query); die();
        }
    }

    public function get_wp_query_args_groups() {
        $args = $this->get_wp_query_args();
        $groups = [
            [
                'label' => __('Common', 'e-addons'),
                'options' => [
                    's' => 'Search (string)', // (string) - Passes along the query string variable from a search. For example usage see: http://www.wprecipes.com/how-to-display-the-number-of-results-in-wordpress-search
                    'tax_query' => 'Tax Query',
                    'meta_query' => 'Meta Query',
                    'date_query' => 'Date Query', // (array) - Date parameters (available with Version 3.7).
                    'post_type' => 'Post Type (string / array)', // - retrieves any type except revisions and types with 'exclude_from_search' set to true.
                ],
            ],
            [
                'label' => __('Author', 'e-addons'),
                'options' => [
                    'author' => 'Author (int | string)', // (int | string) -  use author id or comma-separated list of IDs [use minus (-) to exclude authors by ID ex. 'author' => '-1,-2,-3,']
                    'author_name' => 'Author Name (string)', // (string) - use 'user_nicename' (NOT name)
                    'author__in' => 'Author In (ID array)', // (array) - use author id (available with Version 3.7).
                    'author__not_in' => 'Author Not In (ID array)', // (array)' - use author id (available with Version 3.7).
                ],
            ],
            [
                'label' => __('Category', 'e-addons'),
                'options' => [
                    'cat' => 'Category (int)', // (int) - Display posts that have this category (and any children of that category), using category id.
                    'cat' => 'Not in Category (-int)', // Display all posts except those from a category by prefixing its id with a '-' (minus) sign.
                    'category_name' => 'Category Name (,+string)', // (string) - Display posts that have these categories (and any children of that category), using category slug.
                    'category__and' => 'Category And (ID array)', // (array) - Display posts that are in multiple categories. This shows posts that are in both categories 2 and 6.
                    'category__in' => 'Category In (ID array)', // (array) - Display posts that have this category (not children of that category), using category id.
                    'category__not_in' => 'Category Not In (ID array)', // (array) - Display posts that DO NOT HAVE these categories (not children of that category), using category id.
                ],
            ],
            [
                'label' => __('Tag', 'e-addons'),
                'options' => [
                    'tag' => 'Tag (string)', // (string) - use tag slug.
                    'tag_id' => 'Tag ID (int)', // (int) - use tag id.
                    'tag__and' => 'Tag And (ID array)', // (array) - use tag ids.
                    'tag__in' => 'Tag In (ID array)', // (array) - use tag ids.
                    'tag__not_in' => 'Tag Not In (ID array)', // (array) - use tag ids.
                    'tag_slug__and' => 'Tag Slug And (string array)', // (array) - use tag slugs.
                    'tag_slug__in' => 'Tag Slug In (string array)', // (array) - use tag slugs.
                ],
            ],
            [
                'label' => __('Post & Page', 'e-addons'),
                'options' => [
                    'p' => 'Post ID (int)', // (int) - use post id.
                    'name' => 'Name (string)', // (string) - use post slug.
                    'title' => 'Title (string)', // (string) - use post title (available with Version 4.4)
                    'page_id' => 'Page ID (int)', // (int) - use page id.
                    'pagename' => 'Page Name (string/string)', // (string) - use page slug.  
                    'post_name__in' => 'Post Name In (string array)', //(array) - use post slugs. Specify posts to retrieve. (available since Version 4.4)
                    'post_parent' => 'Post Parent ID (int)', // (int) - use page id. Return just the child Pages. (Only works with heirachical post types.)
                    'post_parent__in' => 'Post Parent In (ID array)', // (array) - use post ids. Specify posts whose parent is in an array. NOTE: Introduced in 3.6
                    'post_parent__not_in' => 'Post Parent Not In (ID array)', // (array) - use post ids. Specify posts whose parent is not in an array.
                    'post__in' => 'Post In (ID array)', // (array) - use post ids. Specify posts to retrieve. ATTENTION If you use sticky posts, they will be included (prepended!) in the posts you retrieve whether you want it or not. To suppress this behaviour use ignore_sticky_posts
                    'post__not_in' => 'Post Not In (ID array)', // (array) - use post ids. Specify post NOT to retrieve.                    
                ],
            ],
            [
                'label' => __('Password', 'e-addons'),
                'options' => [
                    'has_password' => 'Has Password (bool)', // (bool) - available with Version 3.9
                    'post_password' => 'Post Password (string)', // (string) - show posts with a particular password (available with Version 3.9)
                ],
            ],
            [
                'label' => __('Date', 'e-addons'),
                'options' => [
                    'year' => 'Year (int)', // (int) - 4 digit year (e.g. 2011).
                    'monthnum' => 'Month Number (int)', // (int) - Month number (from 1 to 12).
                    'w' => 'Week (int)', // (int) - Week of the year (from 0 to 53). Uses the MySQL WEEK command. The mode is dependenon the "start_of_week" option.
                    'day' => 'Day (int)', // (int) - Day of the month (from 1 to 31).
                    'hour' => 'Hour (int)', // (int) - Hour (from 0 to 23).
                    'minute' => 'Minute (int)', // (int) - Minute (from 0 to 60).
                    'second' => 'Second (int)', // (int) - Second (0 to 60).
                    'm' => 'YearMonth (int)', // (int) - YearMonth (For e.g.: 201307).
                ],
            ],
            [
                'label' => __('Meta', 'e-addons'),
                'options' => [
                    'meta_key' => 'Meta Key (string)', // (string) - Custom field key.
                    'meta_value' => 'Meta Value (string)', // (string) - Custom field value.
                    'meta_value_num' => 'Meta Value Num (number)', // (number) - Custom field value.
                    'meta_compare' => 'Meta Compare (string)', // (string) - Operator to test the 'meta_value'. Possible values are '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'NOT EXISTS', 'REGEXP', 'NOT REGEXP' or 'RLIKE'. Default value is '='.
                ],
            ],
            [
                'label' => __('Other', 'e-addons'),
                'options' => [
                    'post_status' => 'Post Status (string / array)', // - retrieves any status except those from post types with 'exclude_from_search' set to true.
                    'comment_count' => 'Comment Count (int | array)', // (int | array) The amount of comments your CPT has to have ( Search operator will do a '=' operation )
                    'perm' => 'Permission (string)', // (string) Possible values are 'readable', 'editable'
                    'post_mime_type' => 'Post Mime Type (string/array)', // (string/array) - Allowed mime types.
                    'exact' => 'Exact (bool)', // (bool) - flag to make it only match whole titles/posts - Default value is false. For more information see: https://gist.github.com/2023628#gistcomment-285118
                    'sentence' => 'Sentence (bool)', // (bool) - flag to make it do a phrase search - Default value is false. For more information see: https://gist.github.com/2023628#gistcomment-285118
                ],
            ],
            [
                'label' => __('Order', 'e-addons'),
                'options' => [
                    'order' => 'Order (ASC/DESC string)', // (string) - Designates the ascending or descending order of the 'orderby' parameter. Default to 'DESC'.
                    'orderby' => 'Order By field (string)', // (string)
                ],
            ],
            [
                'label' => __('Pagination', 'e-addons'),
                'options' => [
                    'posts_per_page' => 'Post per Page (int)', // (int) - number of post to show per page (available with Version 2.1). Use 'posts_per_page' => -1 to show all posts.
                ],
            ],
        ];
        return $groups;
    }

    public function get_wp_query_args() {
        $args = array(
// Author Parameters - Show posts associated with certain author.
// http://codex.wordpress.org/Class_Reference/WP_Query#Author_Parameters
            'author' => 'Author (int | string)', // (int | string) -  use author id or comma-separated list of IDs [use minus (-) to exclude authors by ID ex. 'author' => '-1,-2,-3,']
            'author_name' => 'Author Name (string)', // (string) - use 'user_nicename' (NOT name)
            'author__in' => 'Author In (ID array)', // (array) - use author id (available with Version 3.7).
            'author__not_in' => 'Author Not In (ID array)', // (array)' - use author id (available with Version 3.7).
// Category Parameters - Show posts associated with certain categories.
// http://codex.wordpress.org/Class_Reference/WP_Query#Category_Parameters
            'cat' => 'Category (int)', // (int) - Display posts that have this category (and any children of that category), using category id.
            'cat' => 'Not in Category (-int)', // Display all posts except those from a category by prefixing its id with a '-' (minus) sign.
            'category_name' => 'Category Name (,+string)', // (string) - Display posts that have these categories (and any children of that category), using category slug.
            'category__and' => 'Category And (ID array)', // (array) - Display posts that are in multiple categories. This shows posts that are in both categories 2 and 6.
            'category__in' => 'Category In (ID array)', // (array) - Display posts that have this category (not children of that category), using category id.
            'category__not_in' => 'Category Not In (ID array)', // (array) - Display posts that DO NOT HAVE these categories (not children of that category), using category id.
// Tag Parameters - Show posts associated with certain tags.
// http://codex.wordpress.org/Class_Reference/WP_Query#Tag_Parameters
            'tag' => 'Tag (string)', // (string) - use tag slug.
            'tag_id' => 'Tag ID (int)', // (int) - use tag id.
            'tag__and' => 'Tag And (ID array)', // (array) - use tag ids.
            'tag__in' => 'Tag In (ID array)', // (array) - use tag ids.
            'tag__not_in' => 'Tag Not In (ID array)', // (array) - use tag ids.
            'tag_slug__and' => 'Tag Slug And (string array)', // (array) - use tag slugs.
            'tag_slug__in' => 'Tag Slug In (string array)', // (array) - use tag slugs.
// Taxonomy Parameters - Show posts associated with certain taxonomy.
// http://codex.wordpress.org/Class_Reference/WP_Query#Taxonomy_Parameters
// Important Note: tax_query takes an array of tax query arguments arrays (it takes an array of arrays)
// This construct allows you to query multiple taxonomies by using the relation parameter in the first (outer) array to describe the boolean relationship between the taxonomy queries.
            'tax_query' => 'Tax Query',
            /*
              array( // (array) - use taxonomy parameters (available with Version 3.1).

              'relation' => 'AND', // (string) - The logical relationship between each inner taxonomy array when there is more than one. Possible values are 'AND', 'OR'. Do not use with a single inner taxonomy array. Default value is 'AND'.
              array(
              'taxonomy' => 'color', // (string) - Taxonomy.
              'field' => 'slug', // (string) - Select taxonomy term by Possible values are 'term_id', 'name', 'slug' or 'term_taxonomy_id'. Default value is 'term_id'.
              'terms' => array( 'red', 'blue' ), // (int/string/array) - Taxonomy term(s).
              'include_children' => true, // (bool) - Whether or not to include children for hierarchical taxonomies. Defaults to true.
              'operator' => 'IN' // (string) - Operator to test. Possible values are 'IN', 'NOT IN', 'AND', 'EXISTS' and 'NOT EXISTS'. Default value is 'IN'.
              ),
              array(
              'taxonomy' => 'actor',
              'field' => 'id',
              'terms' => array( 103, 115, 206 ),
              'include_children' => false,
              'operator' => 'NOT IN'
              )
              ),
             */

// Post & Page Parameters - Display content based on post and page parameters.
// http://codex.wordpress.org/Class_Reference/WP_Query#Post_.26_Page_Parameters
            'p' => 'Post ID (int)', // (int) - use post id.
            'name' => 'Name (string)', // (string) - use post slug.
            'title' => 'Title (string)', // (string) - use post title (available with Version 4.4)
            'page_id' => 'Page ID (int)', // (int) - use page id.
            'pagename' => 'Page Name (string/string)', // (string) - use page slug.  
            'post_name__in' => 'Post Name In (string array)', //(array) - use post slugs. Specify posts to retrieve. (available since Version 4.4)
            'post_parent' => 'Post Parent ID (int)', // (int) - use page id. Return just the child Pages. (Only works with heirachical post types.)
            'post_parent__in' => 'Post Parent In (ID array)', // (array) - use post ids. Specify posts whose parent is in an array. NOTE: Introduced in 3.6
            'post_parent__not_in' => 'Post Parent Not In (ID array)', // (array) - use post ids. Specify posts whose parent is not in an array.
            'post__in' => 'Post In (ID array)', // (array) - use post ids. Specify posts to retrieve. ATTENTION If you use sticky posts, they will be included (prepended!) in the posts you retrieve whether you want it or not. To suppress this behaviour use ignore_sticky_posts
            'post__not_in' => 'Post Not In (ID array)', // (array) - use post ids. Specify post NOT to retrieve.
            // NOTE: you cannot combine 'post__in' and 'post__not_in' in the same query
// Password Parameters - Show content based on post and page parameters. Remember that default post_type is only set to display posts but not pages.
// http://codex.wordpress.org/Class_Reference/WP_Query#Password_Parameters
            'has_password' => 'Has Password (bool)', // (bool) - available with Version 3.9
            // true for posts with passwords;
            // false for posts without passwords;
            // null for all posts with and without passwords
            'post_password' => 'Post Password (string)', // (string) - show posts with a particular password (available with Version 3.9)
// Post Type Parameters - Show posts associated with certain type or status.
// http://codex.wordpress.org/Class_Reference/WP_Query#Type_Parameters
            // (string / array) - use post types. Retrieves posts by Post Types, default value is 'post';
            // NOTE: The 'any' keyword available to both post_type and post_status queries cannot be used within an array.
            'post_type' => 'Post Type (string / array)', // - retrieves any type except revisions and types with 'exclude_from_search' set to true.
// Post Status Parameters - Show posts associated with certain type or status.
// http://codex.wordpress.org/Class_Reference/WP_Query#Status_Parameters
            // (string | array) - use post status. Retrieves posts by Post Status, default value i'publish'.
            // NOTE: The 'any' keyword available to both post_type and post_status queries cannot be used within an array.
            'post_status' => 'Post Status (string / array)', // - retrieves any status except those from post types with 'exclude_from_search' set to true.
// Comment Paremters - @since Version 4.9 Introduced the `$comment_count` parameter.
// https://codex.wordpress.org/Class_Reference/WP_Query#Comment_Parameters
            'comment_count' => 'Comment Count (int | array)', // (int | array) The amount of comments your CPT has to have ( Search operator will do a '=' operation )

            /*
              // Pagination Parameters
              //http://codex.wordpress.org/Class_Reference/WP_Query#Pagination_Parameters
              'posts_per_page' => 10, // (int) - number of post to show per page (available with Version 2.1). Use 'posts_per_page' => -1 to show all posts.
              // Note: if the query is in a feed, wordpress overwrites this parameter with the stored 'posts_per_rss' option. Treimpose the limit, try using the 'post_limits' filter, or filter 'pre_option_posts_per_rss' and return -1
              'nopaging' => false, // (bool) - show all posts or use pagination. Default value is 'false', use paging.
              'paged' => get_query_var('paged'), // (int) - number of page. Show the posts that would normally show up just on page X when usinthe "Older Entries" link.
              // NOTE: Use get_query_var('page'); if you want your query to work in a Page template that you've set as your static front page. The query variable 'page' holds the pagenumber for a single paginated Post or Page that includes the <!--nextpage--> Quicktag in the post content.
              'nopaging' => false, // (boolean) - show all posts or use pagination. Default value is 'false', use paging.
              'posts_per_archive_page' => 10, // (int) - number of posts to show per page - on archive pages only. Over-rides posts_per_page and showposts on pages where is_archive() or is_search() would be true.
              'offset' => 3, // (int) - number of post to displace or pass over.
              // Warning: Setting the offset parameter overrides/ignores the paged parameter and breaks pagination. for a workaround see: http://codex.wordpress.org/Making_Custom_Queries_using_Offset_and_Pagination
              // The 'offset' parameter is ignored when 'posts_per_page'=>-1 (show all posts) is used.
              'paged' => get_query_var('paged'), // (int) - number of page. Show the posts that would normally show up just on page X when usinthe "Older Entries" link.
              // NOTE: This whole paging thing gets tricky. Some links to help you out:
              // http://codex.wordpress.org/Function_Reference/next_posts_link#Usage_when_querying_the_loop_with_WP_Query
              // http://codex.wordpress.org/Pagination#Troubleshooting_Broken_Pagination
              'page' => get_query_var('page'), // (int) - number of page for a static front page. Show the posts that would normally show up just on page X of a Static Front Page.
              // NOTE: The query variable 'page' holds the pagenumber for a single paginated Post or Page that includes the <!--nextpage--> Quicktag in the post content.
              'ignore_sticky_posts' => false, // (boolean) - ignore sticky posts or not (available with Version 3.1, replaced caller_get_posts parameter). Default value is 0 - don't ignore sticky posts. Note: ignore/exclude sticky posts being included at the beginning of posts returned, but the sticky post will still be returned in the natural order of that list of posts returned.
             */
            /*
              // Order & Orderby Parameters - Sort retrieved posts.
              // http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters
              'order' => 'Order (ASC/DESC string)', // (string) - Designates the ascending or descending order of the 'orderby' parameter. Default to 'DESC'.
              //Possible Values:
              //'ASC' - ascending order from lowest to highest values (1, 2, 3; a, b, c).
              //'DESC' - descending order from highest to lowest values (3, 2, 1; c, b, a).
              'orderby' => 'date', // (string) - Sort retrieved posts by parameter. Defaults to 'date'. One or more options can be passed. EX: 'orderby' => 'menu_order title'
              //Possible Values:
              // 'none' - No order (available since Version 2.8).
              // 'ID' - Order by post id. Note the capitalization.
              // 'author' - Order by author. ('post_author' is also accepted.)
              // 'title' - Order by title. ('post_title' is also accepted.)
              // 'name' - Order by post name (post slug). ('post_name' is also accepted.)
              // 'type' - Order by post type (available since Version 4.0). ('post_type' is also accepted.)
              // 'date' - Order by date. ('post_date' is also accepted.)
              // 'modified' - Order by last modified date. ('post_modified' is also accepted.)
              // 'parent' - Order by post/page parent id. ('post_parent' is also accepted.)
              // 'rand' - Random order. You can also use 'RAND(x)' where 'x' is an integer seed value.
              // 'comment_count' - Order by number of comments (available since Version 2.9).
              // 'relevance' - Order by search terms in the following order: First, whether the entire sentence is matched. Second, if all the search terms are within the titles. Third, if any of the search terms appear in the titles. And, fourth, if the full sentence appears in the contents.
              // 'menu_order' - Order by Page Order. Used most often for Pages (Order field in the Edit Page Attributes box) and for Attachments (the integer fields in the Insert / Upload Media Gallery dialog), but could be used for any post type with distinct 'menu_order' values (they all default to 0).
              // 'meta_value' - Note that a 'meta_key=keyname' must also be present in the query. Note also that the sorting will be alphabetical which is fine for strings (i.e. words), but can be unexpected for numbers (e.g. 1, 3, 34, 4, 56, 6, etc, rather than 1, 3, 4, 6, 34, 56 as you might naturally expect). Use 'meta_value_num' instead for numeric values.
              // 'meta_type' if you want to cast the meta value as a specific type. Possible values are 'NUMERIC', 'BINARY',  'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED', same as in '$meta_query'. When using 'meta_type' you can also use 'meta_value_*' accordingly. For example, when using DATETIME as 'meta_type' you can use 'meta_value_datetime' to define order structure.
              // 'meta_value_num' - Order by numeric meta value (available since Version 2.8). Also note that a 'meta_key=keyname' must also be present in the query. This value allows for numerical sorting as noted above in 'meta_value'.
              // 'post__in' - Preserve post ID order given in the 'post__in' array (available since Version 3.5). Note - the value of the order parameter does not change the resulting sort order.
              // 'post_name__in' - Preserve post slug order given in the 'post_name__in' array (available since Version 4.6). Note - the value of the order parameter does not change the resulting sort order.
              // 'post_parent__in' - Preserve post parent order given in the 'post_parent__in' array (available since Version 4.6). Note - the value of the order parameter does not change the resulting sort order.
             */

// Date Parameters - Show posts associated with a certain time and date period.
// http://codex.wordpress.org/Class_Reference/WP_Query#Date_Parameters
            'year' => 'Year (int)', // (int) - 4 digit year (e.g. 2011).
            'monthnum' => 'Month Number (int)', // (int) - Month number (from 1 to 12).
            'w' => 'Week (int)', // (int) - Week of the year (from 0 to 53). Uses the MySQL WEEK command. The mode is dependenon the "start_of_week" option.
            'day' => 'Day (int)', // (int) - Day of the month (from 1 to 31).
            'hour' => 'Hour (int)', // (int) - Hour (from 0 to 23).
            'minute' => 'Minute (int)', // (int) - Minute (from 0 to 60).
            'second' => 'Second (int)', // (int) - Second (0 to 60).
            'm' => 'YearMonth (int)', // (int) - YearMonth (For e.g.: 201307).
            'date_query' => 'Date Query (after/before string)', // (array) - Date parameters (available with Version 3.7).
            /*
              // these are super powerful. check out the codex for more comprehensive code examples http://codex.wordpress.org/Class_Reference/WP_Query#Date_Parameters
              array(
              'year' => 2014, // (int) - 4 digit year (e.g. 2011).
              'month' => 4, // (int) - Month number (from 1 to 12).
              'week' => 31, // (int) - Week of the year (from 0 to 53).
              'day' => 5, // (int) - Day of the month (from 1 to 31).
              'hour' => 2, // (int) - Hour (from 0 to 23).
              'minute' => 3, // (int) - Minute (from 0 to 59).
              'second' => 36, // (int) - Second (0 to 59).
              'after' => 'January 1st, 2013', // (string/array) - Date to retrieve posts after. Accepts strtotime()-compatible string, or array of 'year', 'month', 'day'
              'before' => array( // (string/array) - Date to retrieve posts after. Accepts strtotime()-compatible string, or array of 'year', 'month', 'day'
              'year' => 2013, // (string) Accepts any four-digit year. Default is empty.
              'month' => 2, // (string) The month of the year. Accepts numbers 1-12. Default: 12.
              'day' => 28, // (string) The day of the month. Accepts numbers 1-31. Default: last day of month.
              ),
              'inclusive' => true, // (boolean) - For after/before, whether exact value should be matched or not'.
              'compare' =>  '=', // (string) - Possible values are '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS' (only in WP >= 3.5), and 'NOT EXISTS' (also only in WP >= 3.5). Default value is '='
              'column' => 'post_date', // (string) - Column to query against. Default: 'post_date'.
              'relation' => 'AND', // (string) - OR or AND, how the sub-arrays should be compared. Default: AND.
              ),
              ),
             */

// Custom Field Parameters - Show posts associated with a certain custom field.
// http://codex.wordpress.org/Class_Reference/WP_Query#Custom_Field_Parameters
            'meta_key' => 'Meta Key (string)', // (string) - Custom field key.
            'meta_value' => 'Meta Value (string)', // (string) - Custom field value.
            'meta_value_num' => 'Meta Value Num (number)', // (number) - Custom field value.
            'meta_compare' => 'Meta Compare (string)', // (string) - Operator to test the 'meta_value'. Possible values are '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'NOT EXISTS', 'REGEXP', 'NOT REGEXP' or 'RLIKE'. Default value is '='.
            'meta_query' => 'Meta Query',
            /*
              array( // (array) - Custom field parameters (available with Version 3.1).
              'relation' => 'AND', // (string) - Possible values are 'AND', 'OR'. The logical relationship between each inner meta_query array when there is more than one. Do not use with a single inner meta_query array.
              array(
              'key' => 'color', // (string) - Custom field key.
              'value' => 'blue', // (string/array) - Custom field value (Note: Array support is limited to a compare value of 'IN', 'NOT IN', 'BETWEEN', or 'NOT BETWEEN') Using WP < 3.9? Check out this page for details: http://codex.wordpress.org/Class_Reference/WP_Query#Custom_Field_Parameters
              'type' => 'CHAR', // (string) - Custom field type. Possible values are 'NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED'. Default value is 'CHAR'. The 'type' DATE works with the 'compare' value BETWEEN only if the date is stored at the format YYYYMMDD and tested with this format.
              //NOTE: The 'type' DATE works with the 'compare' value BETWEEN only if the date is stored at the format YYYYMMDD and tested with this format.
              'compare' => '=', // (string) - Operator to test. Possible values are '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS' (only in WP >= 3.5), and 'NOT EXISTS' (also only in WP >= 3.5). Default value is '='.
              ),
              array(
              'key' => 'price',
              'value' => array( 1,200 ),
              'compare' => 'NOT LIKE',
              )
              ),
             */
// Permission Parameters - Display published posts, as well as private posts, if the user has the appropriate capability:
// http://codex.wordpress.org/Class_Reference/WP_Query#Permission_Parameters
            'perm' => 'Permission (string)', // (string) Possible values are 'readable', 'editable'
// Mime Type Parameters - Used with the attachments post type.
// https://codex.wordpress.org/Class_Reference/WP_Query#Mime_Type_Parameters
            'post_mime_type' => 'Post Mime Type (string/array)', // (string/array) - Allowed mime types.

            /*
              // Caching Parameters
              // http://codex.wordpress.org/Class_Reference/WP_Query#Caching_Parameters
              // NOTE Caching is a good thing. Setting these to false is generally not advised.
              'cache_results' => true, // (bool) Default is true - Post information cache.
              'update_post_term_cache' => true, // (bool) Default is true - Post meta information cache.
              'update_post_meta_cache' => true, // (bool) Default is true - Post term information cache.
              'no_found_rows' => false, // (bool) Default is false. WordPress uses SQL_CALC_FOUND_ROWS in most queries in order to implement pagination. Even when you don’t need pagination at all. By Setting this parameter to true you are telling wordPress not to count the total rows and reducing load on the DB. Pagination will NOT WORK when this parameter is set to true. For more information see: http://flavio.tordini.org/speed-up-wordpress-get_posts-and-query_posts-functions
             */

// Search Parameter
// http://codex.wordpress.org/Class_Reference/WP_Query#Search_Parameter
            's' => 'Search (string)', // (string) - Passes along the query string variable from a search. For example usage see: http://www.wprecipes.com/how-to-display-the-number-of-results-in-wordpress-search
            'exact' => 'Exact (bool)', // (bool) - flag to make it only match whole titles/posts - Default value is false. For more information see: https://gist.github.com/2023628#gistcomment-285118
            'sentence' => 'Sentence (bool)', // (bool) - flag to make it do a phrase search - Default value is false. For more information see: https://gist.github.com/2023628#gistcomment-285118
                /*
                  // Post Field Parameters
                  // For more info see: http://codex.wordpress.org/Class_Reference/WP_Query#Return_Fields_Parameter
                  // also https://gist.github.com/luetkemj/2023628/#comment-1003542
                  'fields' => 'ids', // (string) - Which fields to return. All fields are returned by default.
                  // Possible values:
                  // 'ids'        - Return an array of post IDs.
                  // 'id=>parent' - Return an associative array [ parent => ID, … ].
                  // Passing anything else will return all fields (default) - an array of post objects.
                 */
// Filters
// For more information on available Filters see: http://codex.wordpress.org/Class_Reference/WP_Query#Filters
        );
        return $args;
    }

}
