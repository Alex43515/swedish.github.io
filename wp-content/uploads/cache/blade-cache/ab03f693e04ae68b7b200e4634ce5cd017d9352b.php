<?php $__env->startSection('content'); ?>

<?php if(is_active_sidebar('content-area')): ?>
<section class="creamy creamy-border-bottom  u-py-5 sidebar-content-area ">
    <div class="container">
        <div class="grid grid--columns">
            <?php dynamic_sidebar('content-area'); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('templates.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>