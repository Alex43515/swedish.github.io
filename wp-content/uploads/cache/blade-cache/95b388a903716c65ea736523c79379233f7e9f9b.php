<?php $__env->startSection('content'); ?>

    <?php if(file_exists(MUNICIPIO_PATH . '/views/partials/404/' . $post_type . '.blade.php')): ?>
        <?php echo $__env->make('partials.404.' . $post_type, array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <?php else: ?>
        <?php echo $__env->make('partials.404.default', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('templates.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>