<?php

function sc_portfolio($attr, $content = null)
{
    extract(shortcode_atts(array(
        'count' 				=> 2,
        'category' 			=> '',
        'category_multi'=> '',
        'exclude_id'		=> '',
        'orderby' 			=> 'date',
        'order' 				=> 'DESC',
        'style'					=> 'list',
        'columns'				=> 3,
        'greyscale'			=> '',
        'filters'				=> '',
        'pagination'		=> '',
        'load_more'			=> '',
        'related'				=> '',
    ), $attr));

    // translate

    $translate['all'] = mfn_opts_get('translate') ? mfn_opts_get('translate-item-all', 'All') : __('All', 'betheme');

    // class

    $class = '';
    if ($greyscale) {
        $class .= ' greyscale';
    }

    // query args

    $paged = (get_query_var('paged')) ? get_query_var('paged') : ((get_query_var('page')) ? get_query_var('page') : 1);
    $args = array(
        'post_type' 			=> 'portfolio',
        'posts_per_page' 	    => intval($count, 10),
        'paged' 			    => $paged,
        'orderby' 				=> $orderby,
        'order' 				=> $order,
        'ignore_sticky_posts'   => 1,
        'tax_query' => array(
            array(
                'taxonomy' => 'portfolio-types',
                'field'    => 'slug',
                'terms'    => 'links-uteis',
            ),
        ),
    );

    // categories

    if ($category_multi = trim($category_multi)) {

        $category_multi = mfn_wpml_term_slug($category_multi, 'portfolio-types', 1);
        $args['portfolio-types'] = $category_multi;

        $category_multi_array = explode(',', str_replace(' ', '', $category_multi));

    } elseif ($category) {

        $category = mfn_wpml_term_slug($category, 'portfolio-types');
        $args['portfolio-types'] = $category;

    }

    // exclude posts

    if ($exclude_id) {
        $exclude_id = str_replace(' ', '', $exclude_id);
        $args['post__not_in'] = explode(',', $exclude_id);
    }

    // related | exclude current

    if ($related) {
        $args['post__not_in'] = array( get_the_ID() );
    }

    // query

    $query_portfolio = new WP_Query($args);

    // output -----

    $output = '<div class="column_filters">';

        // output | filters

        if ($filters && ! $category) {
            $output .= '<div id="Filters" class="isotope-filters filters4portfolio" data-parent="column_filters">';
                $output .= '<div class="filters_wrapper">';
                    $output .= '<ul class="categories">';

                        #$output .= '<li meta-campo="imbecil" class="reset current-cat"><a class="all" data-rel="*" href="#">'. esc_html($translate['all']) .'</a></li>';
                        if ($portfolio_categories = get_terms('portfolio-types')) {
                            foreach ($portfolio_categories as $category) {
                                if ($category_multi) {
                                    if (in_array($category->slug, $category_multi_array)) {
                                        $output .= '<li class="'. esc_attr($category->slug) .' '. ($category->slug == "links-uteis" ? "current-cat" : "" ) .'"><a data-rel=".category-'. esc_attr($category->slug) .'" href="'. esc_url(get_term_link($category)) .'">'. esc_html($category->name) .'</a></li>';
                                    }
                                } else {
                                    $output .= '<li class="'. esc_attr($category->slug) .'"><a data-rel=".category-'. esc_attr($category->slug) .'" href="'. esc_url(get_term_link($category)) .'">'. esc_html($category->name) .'</a></li>';
                                }
                            }
                        }

                    $output .= '</ul>';
                $output .= '</div>';
            $output .= '</div>'."\n";
        }

        // output | main

        $output .= '<div class="portfolio_wrapper isotope_wrapper '. esc_attr($class) .'">';

            $output .= '<ul class="portfolio_group lm_wrapper isotope col-'. intval($columns, 10) .' '. esc_attr($style) .'">';
                $output .= mfn_content_portfolio($query_portfolio, $style);
            $output .= '</ul>';

            if ($pagination) {
                $output .= mfn_pagination($query_portfolio, $load_more);
            }

        $output .= '</div>'."\n";

    $output .= '</div>'."\n";

    wp_reset_postdata();

    return $output;
}