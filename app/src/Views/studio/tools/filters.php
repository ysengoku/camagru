<?php
    $sampleImage = '/assets/img/sample-pic2.jpg';
    $filters = ['none', 'grayscale', 'sepia', 'vintage', 'dream'];
    $defaultFilter = 'none';
?>

<div id="filters" class="tool-container justify-center gap-4 display-none">
    <?php foreach ($filters as $filter): ?>
        <div class="flex-col align-center">
            <button
                type="button"
                id="filter-<?= $filter ?>"
                class="filter-option <?php if ($filter === $defaultFilter) echo 'selected-filter'; ?> "
                data-filter="<?= $filter ?>"
            >
                <img
                    src="<?= $sampleImage ?>"
                    alt="<?= ucfirst($filter) ?>"
                    class="filter-<?= $filter ?>"
                />
            </button>
            <span class="font-size-3"><?= ucfirst($filter) ?></span>
        </div>
    <?php endforeach; ?>
</div>
