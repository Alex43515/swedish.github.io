<?php

namespace EAddonsProFormFilters\Modules\Filters\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use EAddonsForElementor\Base\Base_Widget;
use EAddonsForElementor\Core\Utils;
use EAddonsForElementor\Core\Utils\Form;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hello World
 *
 * Elementor widget for e-addons
 *
 */
class Filters extends Base_Widget {

    public function get_name() {
        return 'active-filters';
    }

    public function get_title() {
        return __('Form Active Filters', 'e-addons');
    }

    public function get_pid() {
        return 16182;
    }

    public function get_icon() {
        return 'eadd-el-form-pro-filters-activefilter';
    }

    public function get_categories() {
        return ['Search & Filter'];
    }

    protected function _register_controls() {

        $this->start_controls_section(
                'section_content', [
            'label' => __('Active Filters', 'e-addons')
                ]
        );

        $this->add_control(
                'filters_title', [
            'label' => __('Filters Title', 'e-addons'),
            'type' => Controls_Manager::TEXT,
            'placeholder' => __('Active Filters:', 'e-addons'),
                ]
        );
        $this->add_control(
                'filters_title_no', [
            'label' => __('Show Title if no Active Filters', 'e-addons'),
            'type' => Controls_Manager::SWITCHER,
            'condition' => [
                'filters_title!' => '',
            ]
                ]
        );
        $this->add_control(
                'filters_title_html', [
            'label' => __('Title HTML Tag', 'elementor'),
            'type' => Controls_Manager::SELECT,
            'options' => [
                'h1' => 'H1',
                'h2' => 'H2',
                'h3' => 'H3',
                'h4' => 'H4',
                'h5' => 'H5',
                'h6' => 'H6',
                'div' => 'div',
                'span' => 'span',
                'p' => 'p',
            ],
            'default' => 'h4',
            'condition' => [
                'filters_title!' => '',
            ]
                ]
        );

        $this->add_control(
                'list', [
            'label' => __('Render as List', 'e-addons'),
            'type' => Controls_Manager::SWITCHER,
                ]
        );
        $this->add_control(
                'no_filters', [
            'label' => __('No Filters', 'e-addons'),
            'type' => Controls_Manager::TEXT,
            'placeholder' => __('No filters are active', 'e-addons'),
                ]
        );
        $this->add_control(
                'remove_position', [
            'label' => __('Remove Position', 'e-addons'),
            'type' => Controls_Manager::CHOOSE,
            'options' => [
                'before' => [
                    'title' => __('Before', 'e-addons'),
                    'icon' => 'eicon-arrow-left',
                ],
                'none' => [
                    'title' => __('None', 'e-addons'),
                    'icon' => 'eicon-ban',
                ],
                'after' => [
                    'title' => __('After', 'e-addons'),
                    'icon' => 'eicon-arrow-right',
                ],
            ],
            'default' => 'after',
            'toggle' => false,
                ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
                'section_e_query_filters',
                [
                    'label' => '<i class="eadd-logo-e-addons eadd-ic-right"></i>Search Filters',
                    'tab' => Controls_Manager::TAB_STYLE,
                /* 'conditions' => [
                  'terms' => [
                  'name' => 'submit_actions',
                  'operator' => 'contains',
                  'value' => 'query',
                  ]
                  ] */
                ]
        );

        $this->add_responsive_control(
                'e_query_filters_align',
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
                        '{{WRAPPER}} .elementor-form-filters' => 'text-align: {{VALUE}};',
                    ],
                ]
        );

        $this->start_controls_tabs('tabs_query_filters_style');

        $this->start_controls_tab(
                'e_query_filters_title',
                [
                    'label' => __('Title', 'e-addons'),
                    'condition' => [
                        'filters_title!' => '',
                    ]
                ]
        );
        $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'e_query_filters_title_typography',
                    'selector' => '{{WRAPPER}} .elementor-form-filters .elementor-form-filters__filter_title',
                    'global' => [
                        'default' => Global_Typography::TYPOGRAPHY_TEXT,
                    ],
                ]
        );
        $this->add_control(
                'e_query_filters_title_color',
                [
                    'label' => __('Title Color', 'e-addons'),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .elementor-form-filters .elementor-form-filters__filter_title' => 'color: {{VALUE}};',
                    ],
                    'global' => [
                        'default' => Global_Colors::COLOR_TEXT,
                    ],
                ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab(
                'e_query_filters_label',
                [
                    'label' => __('Label', 'e-addons'),
                ]
        );
        $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'e_query_filters_label_typography',
                    'selector' => '{{WRAPPER}} .elementor-form-filters .elementor-form-filters__filter_label',
                    'global' => [
                        'default' => Global_Typography::TYPOGRAPHY_TEXT,
                    ],
                ]
        );
        $this->add_control(
                'e_query_filters_label_color',
                [
                    'label' => __('Label Color', 'e-addons'),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .elementor-form-filters .elementor-form-filters__filter_label' => 'color: {{VALUE}};',
                    ],
                    'global' => [
                        'default' => Global_Colors::COLOR_TEXT,
                    ],
                ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab(
                'e_query_filters_value',
                [
                    'label' => __('Value', 'e-addons'),
                ]
        );
        $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'e_query_filters_value_typography',
                    'selector' => '{{WRAPPER}} .elementor-form-filters .elementor-form-filters__filter_value',
                    'global' => [
                        'default' => Global_Typography::TYPOGRAPHY_TEXT,
                    ],
                ]
        );
        $this->add_control(
                'e_query_filters_value_color',
                [
                    'label' => __('Value Color', 'e-addons'),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .elementor-form-filters .elementor-form-filters__filter_value' => 'color: {{VALUE}};',
                    ],
                    'global' => [
                        'default' => Global_Colors::COLOR_TEXT,
                    ],
                ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab(
                'e_query_filters_option_remove',
                [
                    'label' => __('Remove', 'e-addons'),
                    'condition' => [
                        'remove_position!' => 'none',
                    ]
                ]
        );
        $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'e_query_filters_remove_typography',
                    'selector' => '{{WRAPPER}} .elementor-form-filters .elementor-form-filters__filter_remove',
                    'global' => [
                        'default' => Global_Typography::TYPOGRAPHY_TEXT,
                    ],
                ]
        );
        $this->add_control(
                'e_query_filters_remove_color',
                [
                    'label' => __('Remove Color', 'e-addons'),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .elementor-form-filters .elementor-form-filters__filter_remove' => 'color: {{VALUE}};',
                    ],
                    'global' => [
                        'default' => Global_Colors::COLOR_TEXT,
                    ],
                ]
        );
        $this->add_control(
                'e_query_filters_hover_remove_color',
                [
                    'label' => __('Remove Color Hover', 'e-addons'),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .elementor-form-filters .elementor-form-filters__filter_remove:hover' => 'color: {{VALUE}};',
                    ],
                    'global' => [
                        'default' => Global_Colors::COLOR_TEXT,
                    ],
                ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
                'e_query_filters_no',
                [
                    'label' => __('No Filters', 'e-addons'),
                    'condition' => [
                        'no_filters!' => '',
                    ]
                ]
        );
        $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'e_query_filters_no_typography',
                    'selector' => '{{WRAPPER}} .elementor-form-filters .elementor-form-filters__no_filter',
                    'global' => [
                        'default' => Global_Typography::TYPOGRAPHY_TEXT,
                    ],
                ]
        );
        $this->add_control(
                'e_query_filters_no_color',
                [
                    'label' => __('No Filters Color', 'e-addons'),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .elementor-form-filters .elementor-form-filters__no_filter' => 'color: {{VALUE}};',
                    ],
                    'global' => [
                        'default' => Global_Colors::COLOR_TEXT,
                    ],
                ]
        );
        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
                'e_query_filters_bgcolor',
                [
                    'label' => __('Background Color', 'e-addons'),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .elementor-form-filters .elementor-form-filters__filter' => 'background-color: {{VALUE}};',
                    ],
                    'separator' => 'before',
                ]
        );

        $this->add_responsive_control(
                'e_query_filters_margin',
                [
                    'label' => __('Margin', 'elementor'),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => ['px', 'em', '%'],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-form-filters .elementor-form-filters__filter' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
        );
        $this->add_responsive_control(
                'e_query_filters_padding',
                [
                    'label' => __('Padding', 'elementor'),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => ['px', 'em', '%'],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-form-filters .elementor-form-filters__filter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
        );
        $this->add_group_control(
                Group_Control_Border::get_type(), [
            'name' => 'e_query_filters_border',
            'selector' => '{{WRAPPER}} .elementor-form-filters .elementor-form-filters__filter',
                ]
        );
        $this->add_control(
                'e_query_filters_radius',
                [
                    'label' => __('Border Radius', 'e-addons'),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => ['px', '%'],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-form-filters .elementor-form-filters__filter' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        if (empty($settings))
            return;

        $this->render_filters();
    }

    public function render_filters($fields = array(), $form_settings = array(), $form_settings_raw = array()) {
        $settings = $this->get_settings_for_display();
        if (empty($settings))
            return;

        $html_tag = $settings['list'] ? 'div' : 'span';

        if (Utils::is_preview() || $settings['no_filters'] || !Utils::empty($fields)) {
            echo '<div class="elementor-form-filters">';
            
            $active_filters = false;
            if (!Utils::empty($fields)) {
                foreach ($fields as $custom_id => $field) {
                    if (!empty($field)) {
                        $active_filters = true;
                    }
                }
            }
            //var_dump($active_filters);
            if ($settings['filters_title'] && ($active_filters || $settings['filters_title_no'] || Utils::is_preview())) {
                echo '<'.$settings['filters_title_html'].' class="elementor-form-filters__filter_title">'.$settings['filters_title'].'</'.$settings['filters_title_html'].'> ';
            }

            if (!Utils::empty($fields)) {
                foreach ($fields as $custom_id => $field) {
                    if (!empty($field)) {
                        $form_field = Form::get_field($custom_id, $form_settings);
                        if ($form_field) {
                            $field_label = !empty($form_field['field_label']) ? $form_field['field_label'] : $custom_id;
                            $field_value = $this->get_field_value($field, $custom_id, $form_settings, $form_settings_raw);
                            ?>
                            <<?php echo $html_tag; ?> class="elementor-form-filters__filter">
                            <?php $this->_remove_filter($form_field, $settings['remove_position'] == 'before'); ?>  
                            <b class="elementor-form-filters__filter_label"><?php echo $field_label; ?>:</b> <i class="elementor-form-filters__filter_value"><?php echo $field_value; ?></i>
                            <?php $this->_remove_filter($form_field, $settings['remove_position'] == 'after'); ?>                            
                            </<?php echo $html_tag; ?>>
                            <?php
                            if (empty($settings['list']))
                                echo ' &nbsp; ';
                        }
                    }
                }
            } else {
                if ($settings['no_filters']) {
                    echo '<' . $html_tag . ' class="elementor-form-filters__no_filter">' . $settings['no_filters'] . '</' . $html_tag . '>';
                } else {
                    //if (Utils::is_preview()) {
                    echo '<' . $html_tag . ' class="elementor-form-filters__filter">';
                    if ($settings['remove_position'] == 'before')
                        echo ' <a href="#" class="elementor-form-filters__filter_remove eicon-close-circle"></a> ';
                    echo '<b class="elementor-form-filters__filter_label">Category:</b> <i class="elementor-form-filters__filter_value">Tshirts</i>';
                    if ($settings['remove_position'] == 'after')
                        echo ' <a href="#" class="elementor-form-filters__filter_remove eicon-close-circle"></a> ';
                    echo '</' . $html_tag . '>';
                    if (empty($settings['list']))
                        echo ' &nbsp; ';
                    echo '<' . $html_tag . ' class="elementor-form-filters__filter">';
                    if ($settings['remove_position'] == 'before')
                        echo ' <a href="#" class="elementor-form-filters__filter_remove eicon-close-circle"></a> ';
                    echo '<b class="elementor-form-filters__filter_label">Color:</b> <i class="elementor-form-filters__filter_value">Green, Red</i>';
                    if ($settings['remove_position'] == 'after')
                        echo ' <a href="#" class="elementor-form-filters__filter_remove eicon-close-circle"></a> ';
                    echo '</' . $html_tag . '>';
                    //}
                }
            }
            echo '</div>';
        } else {
            echo '<span class="elementor-hidden"></span>';
        }
    }

    public function _remove_filter($form_field, $should_print = true) {
        if ($should_print) {
            $custom_id = $form_field['custom_id'];
            $field_id = '#form-field-' . $custom_id;
            $field_reset = '.val(\'\')';
            if ($form_field['field_type'] == 'checkbox' || $form_field['field_type'] == 'radio' || ($form_field['field_type'] == 'query' && $form_field['e_query_input'] == 'radio_checkbox')) {
                $field_id = 'input[id^=form-field-' . $custom_id . '-]';
                $field_reset = '.prop(\'checked\', false)';
            }
            ?>
            <a onclick="jQuery('<?php echo $field_id; ?>')<?php echo $field_reset; ?>.trigger('change').closest('form').trigger('submit'); jQuery(this).parent().remove(); return false;" href="#" class="elementor-form-filters__filter_remove e-eicon-close-circle">&#10005;</a>
            <?php
        }
    }

    public function get_field_value($field, $custom_id, $settings, $settings_raw) {
        //$settings_raw = self::$settings_raw;
        //var_dump($settings_raw['e_form_query_args']); die();
        if (!empty($settings_raw['e_form_query_args'])) {
            foreach ($settings_raw['e_form_query_args'] as $filter) {
                if (strpos($filter['e_form_query_args_value'], '[field id="' . $custom_id . '"]') !== false) {
                    switch ($filter['e_form_query_args_key']) {
                        case 'tax_query':
                            if (!empty($filter['e_form_query_args_multiple'])) {
                                if (!is_array($field)) {
                                    $field = explode(',', $field);
                                }
                            } else {
                                $field = array($field);
                            }
                            $tmp = array();
                            foreach ($field as $term_id) {
                                $term = Utils::get_term($term_id);
                                //var_dump($term); die();
                                if ($term) {
                                    //$tmp[$term_id] = $term->name;
                                    $tmp[$term_id] = $term;
                                }
                            }
                            $field = $tmp;
                    }
                }
            }
        }
        return Utils::to_string($field);
    }

}
