<?php

namespace EAddonsProFormFilters\Modules\Filters\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
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
 * Order By
 *
 * Elementor widget for e-addons
 *
 */
class Posts_Per_Page extends \ElementorPro\Modules\Forms\Widgets\Form {

    use \EAddonsForElementor\Base\Traits\Base;

    public function get_name() {
        return 'posts-per-page';
    }

    public function get_title() {
        return __('Posts per Page', 'e-addons');
    }

    public function get_pid() {
        return 16687;
    }

    public function get_icon() {
        return 'eadd-el-form-pro-filters-perpage';
    }

    public function get_categories() {
        return ['Search & Filter'];
    }

    protected function _register_controls() {

        $repeater = new Repeater();

        $field_types = [
            //'text' => __('Text', 'elementor-pro'),
            'radio' => __('Radio', 'elementor-pro'),
            'select' => __('Select', 'elementor-pro'),
            'number' => __('Number', 'elementor-pro'),
            'hidden' => __('Hidden', 'elementor-pro'),
            //'html' => __('HTML', 'elementor-pro'),
        ];

        $repeater->start_controls_tabs('form_fields_tabs');

        $repeater->start_controls_tab('form_fields_content_tab', [
            'label' => __('Content', 'elementor-pro'),
        ]);

        $repeater->add_control(
                'field_type',
                [
                    'label' => __('Type', 'elementor-pro'),
                    'type' => Controls_Manager::SELECT,
                    'options' => $field_types,
                    'default' => 'text',
                ]
        );

        $repeater->add_control(
                'field_label',
                [
                    'label' => __('Label', 'elementor-pro'),
                    'type' => Controls_Manager::TEXT,
                    'default' => '',
                ]
        );

        $repeater->add_control(
                'placeholder',
                [
                    'label' => __('Placeholder', 'elementor-pro'),
                    'type' => Controls_Manager::TEXT,
                    'default' => '',
                    'conditions' => [
                        'terms' => [
                            [
                                'name' => 'field_type',
                                'operator' => 'in',
                                'value' => [
                                    'tel',
                                    'text',
                                    'email',
                                    'textarea',
                                    'number',
                                    'url',
                                    'password',
                                ],
                            ],
                        ],
                    ],
                ]
        );

        $repeater->add_control(
                'field_options',
                [
                    'label' => __('Options', 'elementor-pro'),
                    'type' => Controls_Manager::TEXTAREA,
                    'default' => '',
                    'description' => __('Enter each option in a separate line. To differentiate between label and value, separate them with a pipe char ("|"). For example: First Name|f_name', 'elementor-pro'),
                    'conditions' => [
                        'terms' => [
                            [
                                'name' => 'field_type',
                                'operator' => 'in',
                                'value' => [
                                    'select',
                                    'checkbox',
                                    'radio',
                                ],
                            ],
                        ],
                    ],
                ]
        );

        $repeater->add_control(
                'inline_list',
                [
                    'label' => __('Inline List', 'elementor-pro'),
                    'type' => Controls_Manager::SWITCHER,
                    'return_value' => 'elementor-subgroup-inline',
                    'default' => '',
                    'conditions' => [
                        'terms' => [
                            [
                                'name' => 'field_type',
                                'operator' => 'in',
                                'value' => [
                                    'checkbox',
                                    'radio',
                                ],
                            ],
                        ],
                    ],
                ]
        );
        $repeater->add_control(
                'field_html',
                [
                    'label' => __('HTML', 'elementor-pro'),
                    'type' => Controls_Manager::TEXTAREA,
                    'dynamic' => [
                        'active' => true,
                    ],
                    'conditions' => [
                        'terms' => [
                            [
                                'name' => 'field_type',
                                'value' => 'html',
                            ],
                        ],
                    ],
                ]
        );
        $repeater->add_responsive_control(
                'width',
                [
                    'label' => __('Column Width', 'elementor-pro'),
                    'type' => Controls_Manager::HIDDEN,
                    'options' => [
                        '' => __('Default', 'elementor-pro'),
                        '100' => '100%',
                        '80' => '80%',
                        '75' => '75%',
                        '70' => '70%',
                        '66' => '66%',
                        '60' => '60%',
                        '50' => '50%',
                        '40' => '40%',
                        '33' => '33%',
                        '30' => '30%',
                        '25' => '25%',
                        '20' => '20%',
                    ],
                    'default' => '100',
                    'conditions' => [
                        'terms' => [
                            [
                                'name' => 'field_type',
                                'operator' => '!in',
                                'value' => [
                                    'hidden',
                                    'recaptcha',
                                    'recaptcha_v3',
                                    'step',
                                ],
                            ],
                        ],
                    ],
                ]
        );
        $repeater->add_control(
                'css_classes',
                [
                    'label' => __('CSS Classes', 'elementor-pro'),
                    'type' => Controls_Manager::HIDDEN,
                    'default' => '',
                    'title' => __('Add your custom class WITHOUT the dot. e.g: my-class', 'elementor-pro'),
                ]
        );

        $repeater->add_control(
                'required',
                [
                    'label' => __('Required', 'elementor-pro'),
                    'type' => Controls_Manager::HIDDEN,
                    'return_value' => 'true',
                    'default' => '',
                    'conditions' => [
                        'terms' => [
                            [
                                'name' => 'field_type',
                                'operator' => '!in',
                                'value' => [
                                    'checkbox',
                                    'recaptcha',
                                    'recaptcha_v3',
                                    'hidden',
                                    'html',
                                    'step',
                                ],
                            ],
                        ],
                    ],
                ]
        );
        $repeater->add_control(
                'allow_multiple',
                [
                    'label' => __('Multiple Selection', 'elementor-pro'),
                    'type' => Controls_Manager::HIDDEN,
                    'conditions' => [
                        'terms' => [
                            [
                                'name' => 'field_type',
                                'value' => 'select',
                            ],
                        ],
                    ],
                ]
        );

        $repeater->end_controls_tab();

        $repeater->start_controls_tab(
                'form_fields_advanced_tab',
                [
                    'label' => __('Advanced', 'elementor-pro'),
                    'condition' => [
                        'field_type!' => 'html',
                    ],
                ]
        );

        $repeater->add_control(
                'field_value',
                [
                    'label' => __('Default Value', 'elementor-pro'),
                    'type' => Controls_Manager::TEXT,
                    'default' => '',
                    'dynamic' => [
                        'active' => true,
                    ],
                    'conditions' => [
                        'terms' => [
                            [
                                'name' => 'field_type',
                                'operator' => 'in',
                                'value' => [
                                    'text',
                                    'email',
                                    'textarea',
                                    'url',
                                    'tel',
                                    'radio',
                                    'select',
                                    'number',
                                    'date',
                                    'time',
                                    'hidden',
                                ],
                            ],
                        ],
                    ],
                ]
        );

        $repeater->add_control(
                'custom_id',
                [
                    'label' => __('ID', 'elementor-pro'),
                    'type' => Controls_Manager::TEXT,
                    'description' => __('Please make sure the ID is unique and not used elsewhere in this form. This field allows <code>A-z 0-9</code> & underscore chars without spaces.', 'elementor-pro'),
                    'render_type' => 'none',
                ]
        );

        $shortcode_template = '{{ view.container.settings.get( \'custom_id\' ) }}';
        $repeater->add_control(
                'shortcode',
                [
                    'label' => __('Shortcode', 'elementor-pro'),
                    'type' => Controls_Manager::RAW_HTML,
                    'classes' => 'forms-field-shortcode',
                    'raw' => '<input class="elementor-form-field-shortcode" value=\'[field id="' . $shortcode_template . '"]\' readonly />',
                ]
        );

        $repeater->end_controls_tab();

        $repeater->end_controls_tabs();

        $this->start_controls_section(
                'section_form_fields',
                [
                    'label' => __('Posts per Page', 'elementor-pro'),
                ]
        );

        $this->add_control(
                'form_fields',
                [
                    'type' => Controls_Manager::REPEATER, //Fields_Repeater::CONTROL_TYPE,
                    'fields' => $repeater->get_controls(),
                    'default' => [
                        /* [
                          'custom_id' => 'ppp',
                          'field_type' => 'number',
                          'field_label' => __('Post per Page', 'elementor-pro'),
                          'field_value' => 10,
                          ], */
                        [
                            'custom_id' => 'post_per_page',
                            'field_type' => 'select',
                            'field_label' => __('Post per Page', 'elementor-pro'),
                            'placeholder' => __('Post per Page', 'elementor-pro'),
                            'field_options' => 'Default|' . PHP_EOL . '8' . PHP_EOL . '12' . PHP_EOL . '24' . PHP_EOL . '32',
                        ],
                    ],
                    'title_field' => '{{{ field_label }}}',
                    'item_actions' => [
                            'add' => false,
                            'duplicate' => false,
                            'remove' => false,
                            'sort' => false,
                    ],
                ]
        );

        $this->add_control(
                'input_size',
                [
                    'label' => __('Input Size', 'elementor-pro'),
                    'type' => Controls_Manager::SELECT,
                    'options' => [
                        'xs' => __('Extra Small', 'elementor-pro'),
                        'sm' => __('Small', 'elementor-pro'),
                        'md' => __('Medium', 'elementor-pro'),
                        'lg' => __('Large', 'elementor-pro'),
                        'xl' => __('Extra Large', 'elementor-pro'),
                    ],
                    'default' => 'sm',
                    'separator' => 'before',
                ]
        );

        $this->add_control(
                'show_labels',
                [
                    'label' => __('Label', 'elementor-pro'),
                    'type' => Controls_Manager::SWITCHER,
                    'label_on' => __('Show', 'elementor-pro'),
                    'label_off' => __('Hide', 'elementor-pro'),
                    'return_value' => 'true',
                    'default' => 'true',
                    'separator' => 'before',
                ]
        );

        $this->add_control(
                'label_position',
                [
                    'label' => __('Label Position', 'elementor-pro'),
                    'type' => Controls_Manager::SELECT,
                    'options' => [
                        'above' => __('Above', 'elementor-pro'),
                        'inline' => __('Inline', 'elementor-pro'),
                    ],
                    'default' => 'above',
                    'condition' => [
                        'show_labels!' => '',
                    ],
                    'render_type' => 'template',
                    'selectors' => [
                        '{{WRAPPER}} .elementor-form-fields-wrapper.elementor-labels-inline .elementor-field-group .elementor-select-wrapper, {{WRAPPER}} .elementor-form-fields-wrapper.elementor-labels-inline .elementor-field-group .elementor-field-textual' => 'width: auto',
                        '{{WRAPPER}} .elementor-form-fields-wrapper.elementor-labels-above .elementor-column' => 'display: block',
                        '{{WRAPPER}} .elementor-field-type-submit' => 'display: none !important;',
                    ]
                ]
        );

        $this->add_control(
                'submit_actions',
                [
                    'label' => __('Add Action', 'elementor-pro'),
                    'type' => Controls_Manager::HIDDEN,
                    'multiple' => true,
                    'options' => [],
                    'render_type' => 'none',
                    'label_block' => true,
                    'description' => __('Add actions that will be performed after a visitor submits the form (e.g. send an email notification). Choosing an action will add its setting below.', 'elementor-pro'),
                ]
        );
        $this->add_control(
                'button_hover_animation',
                [
                    'label' => __('Animation', 'elementor-pro'),
                    'type' => Controls_Manager::HIDDEN,
                ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
                'section_form_style',
                [
                    'label' => __('Form', 'elementor-pro'),
                    'tab' => Controls_Manager::TAB_STYLE,
                ]
        );

        $this->add_control(
                'column_gap',
                [
                    'label' => __('Columns Gap', 'elementor-pro'),
                    'type' => Controls_Manager::SLIDER,
                    'default' => [
                        'size' => 10,
                    ],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 60,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-field-group' => 'padding-right: calc( {{SIZE}}{{UNIT}}/2 ); padding-left: calc( {{SIZE}}{{UNIT}}/2 );',
                        '{{WRAPPER}} .elementor-form-fields-wrapper' => 'margin-left: calc( -{{SIZE}}{{UNIT}}/2 ); margin-right: calc( -{{SIZE}}{{UNIT}}/2 );',
                    ],
                ]
        );

        $this->add_control(
                'row_gap',
                [
                    'label' => __('Rows Gap', 'elementor-pro'),
                    'type' => Controls_Manager::SLIDER,
                    'default' => [
                        'size' => 10,
                    ],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 60,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-field-group' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                        '{{WRAPPER}} .elementor-field-group.recaptcha_v3-bottomleft, {{WRAPPER}} .elementor-field-group.recaptcha_v3-bottomright' => 'margin-bottom: 0;',
                        '{{WRAPPER}} .elementor-form-fields-wrapper' => 'margin-bottom: -{{SIZE}}{{UNIT}};',
                    ],
                ]
        );

        $this->add_control(
                'heading_label',
                [
                    'label' => __('Label', 'elementor-pro'),
                    'type' => Controls_Manager::HEADING,
                    'separator' => 'before',
                ]
        );

        $this->add_control(
                'label_spacing',
                [
                    'label' => __('Spacing', 'elementor-pro'),
                    'type' => Controls_Manager::SLIDER,
                    'default' => [
                        'size' => 0,
                    ],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 60,
                        ],
                    ],
                    'selectors' => [
                        'body.rtl {{WRAPPER}} .elementor-labels-inline .elementor-field-group > label' => 'padding-left: {{SIZE}}{{UNIT}};',
                        // for the label position = inline option
                        'body:not(.rtl) {{WRAPPER}} .elementor-labels-inline .elementor-field-group > label' => 'padding-right: {{SIZE}}{{UNIT}};',
                        // for the label position = inline option
                        'body {{WRAPPER}} .elementor-labels-above .elementor-field-group > label' => 'padding-bottom: {{SIZE}}{{UNIT}};',
                    // for the label position = above option
                    ],
                ]
        );

        $this->add_control(
                'label_color',
                [
                    'label' => __('Text Color', 'elementor-pro'),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .elementor-field-group > label, {{WRAPPER}} .elementor-field-subgroup label' => 'color: {{VALUE}};',
                    ],
                    'global' => [
                        'default' => Global_Colors::COLOR_TEXT,
                    ],
                ]
        );

        $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'label_typography',
                    'selector' => '{{WRAPPER}} .elementor-field-group > label',
                    'global' => [
                        'default' => Global_Typography::TYPOGRAPHY_TEXT,
                    ],
                ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
                'section_field_style',
                [
                    'label' => __('Field', 'elementor-pro'),
                    'tab' => Controls_Manager::TAB_STYLE,
                ]
        );

        $this->add_control(
                'field_text_color',
                [
                    'label' => __('Text Color', 'elementor-pro'),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .elementor-field-group .elementor-field' => 'color: {{VALUE}};',
                    ],
                    'global' => [
                        'default' => Global_Colors::COLOR_TEXT,
                    ],
                ]
        );

        $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'field_typography',
                    'selector' => '{{WRAPPER}} .elementor-field-group .elementor-field, {{WRAPPER}} .elementor-field-subgroup label',
                    'global' => [
                        'default' => Global_Typography::TYPOGRAPHY_TEXT,
                    ],
                ]
        );

        $this->add_control(
                'field_background_color',
                [
                    'label' => __('Background Color', 'elementor-pro'),
                    'type' => Controls_Manager::COLOR,
                    'default' => '#ffffff',
                    'selectors' => [
                        '{{WRAPPER}} .elementor-field-group:not(.elementor-field-type-upload) .elementor-field:not(.elementor-select-wrapper)' => 'background-color: {{VALUE}};',
                        '{{WRAPPER}} .elementor-field-group .elementor-select-wrapper select' => 'background-color: {{VALUE}};',
                    ],
                    'separator' => 'before',
                ]
        );

        $this->add_control(
                'field_border_color',
                [
                    'label' => __('Border Color', 'elementor-pro'),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .elementor-field-group:not(.elementor-field-type-upload) .elementor-field:not(.elementor-select-wrapper)' => 'border-color: {{VALUE}};',
                        '{{WRAPPER}} .elementor-field-group .elementor-select-wrapper select' => 'border-color: {{VALUE}};',
                        '{{WRAPPER}} .elementor-field-group .elementor-select-wrapper::before' => 'color: {{VALUE}};',
                    ],
                    'separator' => 'before',
                ]
        );

        $this->add_control(
                'field_border_width',
                [
                    'label' => __('Border Width', 'elementor-pro'),
                    'type' => Controls_Manager::DIMENSIONS,
                    'placeholder' => '1',
                    'size_units' => ['px'],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-field-group:not(.elementor-field-type-upload) .elementor-field:not(.elementor-select-wrapper)' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        '{{WRAPPER}} .elementor-field-group .elementor-select-wrapper select' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
        );

        $this->add_control(
                'field_border_radius',
                [
                    'label' => __('Border Radius', 'elementor-pro'),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => ['px', '%'],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-field-group:not(.elementor-field-type-upload) .elementor-field:not(.elementor-select-wrapper)' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        '{{WRAPPER}} .elementor-field-group .elementor-select-wrapper select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
        );

        $this->add_control(
                'field_input_width',
                [
                    'label' => __('Field Input Width', 'elementor-pro'),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => ['px', '%'],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 200,
                        ],
                        '%' => [
                            'min' => 0,
                            'max' => 100,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-field-group .elementor-select-wrapper, {{WRAPPER}} .elementor-field-group .elementor-field-textual' => 'width: {{SIZE}}{{UNIT}} !important; flex-basis: auto; flex-grow: 0;',
                        '{{WRAPPER}} .elementor-field-group .elementor-select-wrapper .elementor-field-textual' => 'width: 100% !important;',
                    ],
                ]
        );

        $this->end_controls_section();
    }

    /* protected function render() {
      $settings = $this->get_settings_for_display();
      if (empty($settings))
      return;

      $this->render_orderby();
      } */

}
