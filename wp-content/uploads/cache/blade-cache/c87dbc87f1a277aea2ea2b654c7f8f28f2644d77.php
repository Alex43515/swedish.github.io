<div class="creamy">
    <div class="container">
        <div class="grid-lg-12 text-center">
            <div class="gutter gutter-xl">
                <h1 class="error404-title">404</h1>
                <span class="error404-subtitle"><?php echo e(get_field('404_error_message', 'option') ? get_field('404_error_message', 'option') : 'The page could not be found'); ?></span>
            </div>
        </div>
    </div>
</div>

<div class="container main-container">
    <div class="grid">
        <div class="grid-lg-8" style="margin: 0 auto;">
            <article class="clearfix">
                <!--
                <h1><?php echo e(get_field('404_error_message', 'option') ? get_field('404_error_message', 'option') : 'The page could not be found'); ?></h1>
                <h4 style="margin-top:0;"><?php _e('Error 404', 'municipio'); ?></h4>
                -->

                <?php echo get_field('404_error_info', 'option') ? get_field('404_error_info', 'option') : ''; ?>

            </article>

            <ul class="actions">
                <?php if(is_array(get_field('404_display', 'option')) && in_array('search', get_field('404_display', 'option'))): ?>
                <li>
                    <a rel="nofollow" href="<?php echo e(home_url()); ?>?s=<?php echo e($keyword); ?>" class="link-item"><?php echo e(sprintf(get_field('404_display', 'option') ? get_field('404_search_link_text', 'option') : 'Search "%s"', $keyword)); ?></a>
                </li>
                <?php endif; ?>

                <?php if(is_array(get_field('404_display', 'option')) && in_array('home', get_field('404_display', 'option'))): ?>
                <li><a href="<?php echo e(home_url()); ?>" class="link-item"><?php echo e(get_field('404_home_link_text', 'option') ? get_field('404_home_link_text', 'option') : 'Go to home'); ?></a></li>
                <?php endif; ?>

                <?php if(is_array(get_field('404_display', 'option')) && in_array('back', get_field('404_display', 'option'))): ?>
                <li><a href="javascript:history.go(-1);" class="link-item"><?php echo e(get_field('404_back_button_text', 'option') ? get_field('404_back_button_text', 'option') : 'Go back'); ?></a></li>
                <?php endif; ?>
            </ul>

            <?php if(is_array($related) && !empty($related)): ?>

                <div class="grid">
                    <div class="grid-xs-12">
                         <h3><?php _e("We made a search for you, maybe you were looking for...", 'municipio'); ?></h3>
                    </div>
                </div>

                <div class="grid">
                    <div class="grid-xs-12">
                        <ul class="search-result-list">
                            <?php $__currentLoopData = $related; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <div class="search-result-item">
                                        <span class="search-result-date"><?php echo e(date(get_option('date_format'), strtotime($item->post_date))); ?></span>
                                        <h3><a href="<?php echo e(get_permalink($item->ID)); ?>" class="link-item"><?php echo e($item->post_title); ?></a></h3>
                                        <p><?php echo e(wp_trim_words(wp_strip_all_tags($item->post_content, true), 55, "")); ?></p>
                                        <div class="search-result-info">
                                            <span class="search-result-url"><i class="fa fa-globe"></i> <a href="<?php echo e(get_permalink($item->ID)); ?>"><?php echo e(get_permalink($item->ID)); ?></a></span>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
