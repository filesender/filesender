<?php
      include_once "pagemenuitem.php"
?>

<div class="core">

    <div id="dialog-about" title="About">
        {tr:about_text}
    </div>

    <?php 
    if (AggregateStatistic::enabled()) {
        pagelink('aggregate_statistics');
    }
    ?>
</div>
