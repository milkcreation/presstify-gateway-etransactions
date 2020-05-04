<?php
/**
 * @var App\View $this
 */
?>
<form method="post" action="<?php echo esc_url($action); ?>" enctype="application/x-www-form-urlencoded">
    <?php foreach ($params as $name => $value) : ?>
        <?php if ($this->get('debug')) : ?>
        <div>
            <label><?php echo $name; ?></label>
            <?php echo field('text', compact('name', 'value')); ?>
        </div>
        <?php else : ?>
            <?php echo field('hidden', compact('name', 'value')); ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php echo field('button', $this->get('button', [])); ?>
</form>