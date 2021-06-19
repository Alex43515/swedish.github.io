<?php

namespace EAddonsProFormFilters\Modules\Filters\Widgets;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Typography;
use EAddonsForElementor\Base\Base_Widget;
use EAddonsForElementor\Core\Utils;
use EAddonsForElementor\Core\Utils\Form;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Archive_Info
 *
 * Elementor widget for e-addons
 *
 */
class Archive_Info extends Base_Widget { // \Elementor\Widget_Text_Editor {
    //use \EAddonsForElementor\Base\Traits\Base;

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
        add_action('elementor/element/archive-info/section_editor/before_section_end', [$this, '_add_extra_controls'], 10, 2);
        add_filter('_widget_text_content', [$this, '_widget_text_content'], 10, 3);
        add_filter('_widget_archive_info_text', [$this, '_widget_archive_info_text'], 10, 2);
    }

    public function get_name() {
        return 'archive-info';
    }

    public function get_title() {
        return __('Archive Info', 'e-addons');
    }

    public function get_pid() {
        return 16149;
    }

    public function get_icon() {
        return 'eadd-el-form-pro-filters-archiveinfo';
    }

    public function get_categories() {
        return ['Search & Filter', 'theme-elements-archive'];
    }

    public function _add_extra_controls($element, $args) {
        $is_archive_info = $element->get_controls('is_archive_info');
        if (!$is_archive_info) {
            $element->add_control(
                    'is_archive_info',
                    [
                        'label' => __('Is Archive Info', 'e-addons'),
                        'type' => \Elementor\Controls_Manager::HIDDEN,
                        'default' => 1,
                    ]
            );
            $element->add_control(
                    'archive_id',
                    [
                        'label' => __('Archive ID', 'e-addons'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'separator' => 'before',
                    ]
            );
            /* $element->add_control(
              'archive_info',
              [
              'label' => __('Text Before Search', 'e-addons'),
              'type' => \Elementor\Controls_Manager::TEXT,
              ]
              ); */

            $element->update_control('editor', array(
                'default' => 'Showing {{query.posts|length}} of {{query.found_posts}} results. Results from {{query.start}} to {{query.end}}. Page {{query.page}} of {{query.max_num_pages}}.',
            ));
        }
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        if (empty($settings))
            return;

        if ($this->get_form_id() || $settings['archive_id']) {
            $this->_render();
        }/* else {
          if ($settings['archive_info'] ) {
          echo $settings['archive_info'];
          }
          } */
    }

    public function get_form_id() {
        if (!empty($_REQUEST['form_id'])) {
            return sanitize_key($_REQUEST['form_id']);
        }
        return false;
    }

    public function _widget_text_content($content, $element_id, $post_id = null) {
        if (strpos($content, '{{query.') !== false || strpos($content, '{{ query.') !== false) {
            //var_dump($element_id); die();
            if ($element_id) {
                //remove_filter('_widget_text_content', [$this, '_widget_text_content'], 10);
                $archive = Utils::get_element_instance_by_id($element_id, $post_id);
                if ($archive) {
                    $archive->query_posts();
                    $query = $archive->get_query();
                    //echo '<pre>'; var_dump($query); echo '</pre>'; //die();
                    $posts_in_page = count($query->posts);
                    $content = str_replace('{{query.posts|length}}', $posts_in_page, $content);
                    $content = str_replace('{{query.found_posts}}', $query->found_posts, $content);

                    $page = (!empty($query->query['page'])) ? $query->query['page'] : 1;
                    $page = (!empty($query->query['paged'])) ? $query->query['paged'] : $page;
                    $content = str_replace('{{query.page}}', $page, $content);

                    $query->posts_per_page = !(empty($query->posts_per_page)) ? $query->posts_per_page : get_option('posts_per_page');

                    $query->start = ($page - 1) * $query->posts_per_page + 1;
                    $content = str_replace('{{query.start}}', $query->start, $content);
                    $query->end = $query->start + $posts_in_page - 1;
                    $content = str_replace('{{query.end}}', $query->end, $content);

                    $content = Utils::get_dynamic_data($content, array('query' => $query));
                    $this->widget_text_content[$element_id] = $content;
                }
            }
        }
        return $content;
    }

    public function _widget_archive_info_text($content, $settings) {
        if (!empty($settings['is_archive_info'])) {
            if (!empty($settings['archive_id'])) {
                $element_id = $settings['archive_id'];
                return apply_filters('_widget_text_content', $content, $element_id);
            }
            if (!empty($settings['archive_info'])) {
                $content = $settings['archive_info'];
            }
        }
        return $content;
    }

    /*     * *********************************************************************** */

    /**
     * Register text editor widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 3.1.0
     * @access protected
     */
    protected function register_controls() {
        $this->start_controls_section(
                'section_editor',
                [
                    'label' => __('Text Editor', 'elementor'),
                ]
        );

        $this->add_control(
                'editor',
                [
                    'label' => '',
                    'type' => Controls_Manager::WYSIWYG,
                    'default' => '<p>' . __('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'elementor') . '</p>',
                ]
        );

        $text_columns = range(1, 10);
        $text_columns = array_combine($text_columns, $text_columns);
        $text_columns[''] = __('Default', 'elementor');

        $this->add_responsive_control(
                'text_columns',
                [
                    'label' => __('Columns', 'elementor'),
                    'type' => Controls_Manager::SELECT,
                    'separator' => 'before',
                    'options' => $text_columns,
                    'selectors' => [
                        '{{WRAPPER}}' => 'columns: {{VALUE}};',
                    ],
                ]
        );

        $this->add_responsive_control(
                'column_gap',
                [
                    'label' => __('Columns Gap', 'elementor'),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => ['px', '%', 'em', 'vw'],
                    'range' => [
                        'px' => [
                            'max' => 100,
                        ],
                        '%' => [
                            'max' => 10,
                            'step' => 0.1,
                        ],
                        'vw' => [
                            'max' => 10,
                            'step' => 0.1,
                        ],
                        'em' => [
                            'max' => 10,
                            'step' => 0.1,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}}' => 'column-gap: {{SIZE}}{{UNIT}};',
                    ],
                ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
                'section_style',
                [
                    'label' => __('Text Editor', 'elementor'),
                    'tab' => Controls_Manager::TAB_STYLE,
                ]
        );

        $this->add_responsive_control(
                'align',
                [
                    'label' => __('Alignment', 'elementor'),
                    'type' => Controls_Manager::CHOOSE,
                    'options' => [
                        'left' => [
                            'title' => __('Left', 'elementor'),
                            'icon' => 'eicon-text-align-left',
                        ],
                        'center' => [
                            'title' => __('Center', 'elementor'),
                            'icon' => 'eicon-text-align-center',
                        ],
                        'right' => [
                            'title' => __('Right', 'elementor'),
                            'icon' => 'eicon-text-align-right',
                        ],
                        'justify' => [
                            'title' => __('Justified', 'elementor'),
                            'icon' => 'eicon-text-align-justify',
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                    ],
                ]
        );

        $this->add_control(
                'text_color',
                [
                    'label' => __('Text Color', 'elementor'),
                    'type' => Controls_Manager::COLOR,
                    'default' => '',
                    'selectors' => [
                        '{{WRAPPER}}' => 'color: {{VALUE}};',
                    ],
                    'global' => [
                        'default' => Global_Colors::COLOR_TEXT,
                    ],
                ]
        );

        $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'typography',
                    'global' => [
                        'default' => Global_Typography::TYPOGRAPHY_TEXT,
                    ],
                ]
        );

        $this->end_controls_section();
    }

    /**
     * Render text editor widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function _render() {
        $editor_content = $this->get_settings_for_display('editor');

        $editor_content = $this->parse_text_editor($editor_content);

        if (!\Elementor\Plugin::$instance->experiments->is_feature_active('e_dom_optimization')) {
            $this->add_render_attribute('editor', 'class', ['elementor-text-editor', 'elementor-clearfix']);
        }

        $this->add_inline_editing_attributes('editor', 'advanced');
        ?>
        <?php if (!\Elementor\Plugin::$instance->experiments->is_feature_active('e_dom_optimization')) { ?>
            <div <?php echo $this->get_render_attribute_string('editor'); ?>>
        <?php } ?>
        <?php echo $editor_content; ?>
        <?php if (!\Elementor\Plugin::$instance->experiments->is_feature_active('e_dom_optimization')) { ?>
            </div>
        <?php } ?>
            <?php
        }

        /**
         * Render text editor widget as plain content.
         *
         * Override the default behavior by printing the content without rendering it.
         *
         * @since 1.0.0
         * @access public
         */
        public function render_plain_content() {
            // In plain mode, render without shortcode
            echo $this->get_settings('editor');
        }

        /**
         * Render text editor widget output in the editor.
         *
         * Written as a Backbone JavaScript template and used to generate the live preview.
         *
         * @since 2.9.0
         * @access protected
         */
        protected function content_template() {
            ?>
        <#
        <?php if (!\Elementor\Plugin::$instance->experiments->is_feature_active('e_dom_optimization')) { ?>
            view.addRenderAttribute( 'editor', 'class', [ 'elementor-text-editor', 'elementor-clearfix' ] );
        <?php } ?>

        view.addInlineEditingAttributes( 'editor', 'advanced' );
        #>
        <?php if (!\Elementor\Plugin::$instance->experiments->is_feature_active('e_dom_optimization')) { ?>
            <div {{{ view.getRenderAttributeString( 'editor' ) }}}>
        <?php } ?>
            {{{ settings.editor }}}
        <?php if (!\Elementor\Plugin::$instance->experiments->is_feature_active('e_dom_optimization')) { ?>
            </div>
        <?php } ?>
            <?php
        }

    }
