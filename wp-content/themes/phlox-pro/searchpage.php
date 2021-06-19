<?php
/**
 * The template for displaying pages.
 *
 * 
 * @package    Auxin
 * @author     averta (c) 2014-2021
 * @link       http://averta.net
 */
get_header(); ?>

    <main id="main" <?php auxin_content_main_class(); ?> >
        <div class="search-page">
            <div class="aux-container aux-fold">

                <div id="primary" class="aux-primary">
                    <div class="content" role="main">

                        <?php
                        if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'single' ) ) {

                        	

                            echo do_shortcode('[elementor-template id="303"]');
                        }
                        ?>

                    </div><!-- end content -->
                </div><!-- end primary -->

                <?php get_sidebar(); ?>

            </div><!-- end container -->
        </div><!-- end wrapper -->
    </main><!-- end main -->

<?php get_sidebar('footer'); ?>
<?php get_footer(); ?>
