<?php
/**
 * @var App\View $this
 */
?>
<form method="post" action="<?php echo esc_url($action); ?>" enctype="application/x-www-form-urlencoded">
    <?php foreach ($params as $name => $value) : ?>
        <div>
            <label><?php echo $name; ?></label>
            <?php echo field('text', compact('name', 'value')); ?>
        </div>
    <?php endforeach; ?>

    <?php echo field('button', [
        'attrs'   => [
            'type'  => 'submit'
        ],
        'content' => __('Régler', 'theme')
    ]); ?>
</form>