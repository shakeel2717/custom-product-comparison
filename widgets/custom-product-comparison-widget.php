<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Elementor_Custom_Product_Comparison_Widget extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'custom_product_comparison';
    }

    public function get_title()
    {
        return __('Product Comparison', 'custom-product-comparison');
    }

    public function get_icon()
    {
        return 'eicon-products';
    }

    public function get_categories()
    {
        return ['general'];
    }

    protected function _register_controls()
    {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'custom-product-comparison'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'category_id',
            [
                'label' => __('Category', 'custom-product-comparison'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_product_categories(),
                'default' => '',
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Table Style', 'custom-product-comparison'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'table_background_color',
            [
                'label' => __('Table Background Color', 'custom-product-comparison'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .custom-product-comparison table' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'header_background_color',
            [
                'label' => __('Header Background Color', 'custom-product-comparison'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .custom-product-comparison th' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'header_text_color',
            [
                'label' => __('Header Text Color', 'custom-product-comparison'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .custom-product-comparison th' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'row_background_color',
            [
                'label' => __('Row Background Color', 'custom-product-comparison'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .custom-product-comparison td' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'row_text_color',
            [
                'label' => __('Row Text Color', 'custom-product-comparison'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .custom-product-comparison td' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'table_border',
                'label' => __('Table Border', 'custom-product-comparison'),
                'selector' => '{{WRAPPER}} .custom-product-comparison table, {{WRAPPER}} .custom-product-comparison table th, {{WRAPPER}} .custom-product-comparison table td',
            ]
        );


        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'table_box_shadow',
                'label' => __('Table Box Shadow', 'custom-product-comparison'),
                'selector' => '{{WRAPPER}} .custom-product-comparison table',
            ]
        );

        $this->end_controls_section();
    }

    private function get_product_categories()
    {
        $categories = get_terms('product_cat', ['hide_empty' => false]);
        $options = [];

        if (!empty($categories) && !is_wp_error($categories)) {
            foreach ($categories as $category) {
                $options[$category->term_id] = $category->name;
            }
        }

        return $options;
    }

    protected function render()
{
    $settings = $this->get_settings_for_display();
    $category_id = $settings['category_id'];

    if (empty($category_id)) {
        return;
    }

    $args = [
        'post_type' => 'product',
        'posts_per_page' => -1,
        'tax_query' => [
            [
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $category_id,
            ],
        ],
    ];

    $products = new WP_Query($args);

    if (!$products->have_posts()) {
        return;
    }

    $all_features = [];

    while ($products->have_posts()) {
        $products->the_post();
        global $product;

        $fields = get_field_objects($product->get_id());
        if ($fields) {
            foreach ($fields as $field_name => $field) {
                if (!in_array($field['label'], $all_features)) {
                    $all_features[] = $field['label'];
                }
            }
        }
    }

    wp_reset_postdata();

    echo '<div class="custom-product-comparison">';
    echo '<table>';
    echo '<thead>';
    echo '<tr><th>Feature</th>';

    while ($products->have_posts()) {
        $products->the_post();
        global $product;
        echo '<th>' . $product->get_name() . '</th>';
    }

    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Add row for product images
    echo '<tr><td>Image</td>';
    while ($products->have_posts()) {
        $products->the_post();
        global $product;
        echo '<td>' . get_the_post_thumbnail($product->get_id(), 'thumbnail') . '</td>';
    }
    echo '</tr>';

    // Add row for product prices
    echo '<tr><td>Price</td>';
    while ($products->have_posts()) {
        $products->the_post();
        global $product;
        echo '<td>' . wc_price($product->get_price()) . '</td>';
    }
    echo '</tr>';

    // Reset post data to iterate again for the features
    $products->rewind_posts();

    foreach ($all_features as $feature) {
        echo '<tr>';
        echo '<td>' . $feature . '</td>';
        while ($products->have_posts()) {
            $products->the_post();
            global $product;
            $fields = get_field_objects($product->get_id());

            // Handle checkbox/select fields properly
            $field_key = strtolower(str_replace(' ', '_', $feature));
            $field_value = 'N/A';
            if (isset($fields[$field_key])) {
                $field = $fields[$field_key];
                if (is_array($field['value'])) {
                    $field_value = !empty($field['value']) ? 'Available' : 'N/A';
                } else {
                    $field_value = $field['value'] ? 'Available' : 'N/A';
                }
            }
            echo '<td>' . $field_value . '</td>';
        }
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';

    wp_reset_postdata();
}



    protected function _content_template()
    {
    }
}
