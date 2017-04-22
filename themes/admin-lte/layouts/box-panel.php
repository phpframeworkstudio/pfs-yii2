<div class="box <?= isset($color) ? $color : "" ?>">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $title ?></h3>
        <?php if (isset($toolsBox)): ?>
        <div class="box-tools">
            <?= $toolsBox ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="box-body">
        <?= $content ?>
    </div>
</div>