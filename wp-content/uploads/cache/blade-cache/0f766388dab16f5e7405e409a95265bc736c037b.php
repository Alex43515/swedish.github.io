<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=EDGE">

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo apply_filters('Municipio/pageTitle', wp_title('|', false, 'right')); ?></title>

    <meta name="pubdate" content="<?php echo e(the_time('Y-m-d')); ?>">
    <meta name="moddate" content="<?php echo e(the_modified_time('Y-m-d')); ?>">

    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="format-detection" content="telephone=yes">
    <meta name="HandheldFriendly" content="true"/>

    <script>
        var ajaxurl = '<?php echo apply_filters('Municipio/ajax_url_in_head', admin_url('admin-ajax.php')); ?>';
    </script>

    <?php echo wp_head(); ?>

</head>
<body <?php echo body_class('no-js'); ?>>

<a href="#main-menu" class="btn btn-default btn-block btn-lg btn-offcanvas"
   tabindex="1"><?php _e('Jump to the main menu', 'municipio'); ?></a>
<a href="#main-content" class="btn btn-default btn-block btn-lg btn-offcanvas"
   tabindex="2"><?php _e('Jump to the main content', 'municipio'); ?></a>

<div id="wrapper">
    <?php if(isset($notice) && !empty($notice)): ?>
        <div class="notices">
            <?php if(!isset($notice['text']) && is_array($notice)): ?>
                <?php $__currentLoopData = $notice; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo $__env->make('partials.notice', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <?php echo $__env->make('partials.notice', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if($translateLocation == 'header'): ?>
        <?php echo $__env->make('partials.translate', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <?php endif; ?>

    <?php echo $__env->make('partials.header', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>

    <main id="main-content" class="clearfix main-content">
        <?php echo $__env->yieldContent('content'); ?>

        <?php if(is_active_sidebar('content-area-bottom')): ?>
            <div class="container u-py-5 sidebar-content-area-bottom">
                <div class="grid grid--columns">
                    <?php dynamic_sidebar('content-area-bottom'); ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php echo $__env->make('partials.footer', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>

    <?php if(isset($fab['menu'])): ?>
        <?php echo $__env->make('partials.fixed-action-bar', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <?php endif; ?>

    <?php echo $__env->make('partials.vertical-menu', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>

    <?php if(in_array($translateLocation, array('footer', 'fold'))): ?>
        <?php echo $__env->make('partials.translate', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <?php endif; ?>

</div>

<?php echo wp_footer(); ?>



</body>
</html>
