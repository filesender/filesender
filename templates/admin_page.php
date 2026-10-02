<?php

    $sections = array('transfers', 'guests', 'users', 'testing' );

    if(!Config::get('guest_support_enabled')) {
        $sections = array_diff($sections,['guests']);
    }

    if(Config::get('config_overrides'))
        $sections[] = 'config';

    $section = 'transfers';
    if(array_key_exists('as', $_REQUEST)) {
        if( strlen($_REQUEST['as'])) {
            $section = $_REQUEST['as'];
        }
    }

    if(!in_array($section, $sections)) throw new GUIUnknownAdminSectionException($section);

?>
<div class="fs-admin">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="fs-admin__header">
                    <h1>{tr:admin_page}</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <nav class="fs-tabs" aria-label="{tr:admin_page}">
                    <ul class="fs-tabs__list">
                        <?php foreach($sections as $s) { ?>
                            <li class="fs-tabs__item">
                                <a class="fs-tabs__link<?php if($s == $section) echo ' fs-tabs__link--active' ?>" href="?s=admin&as=<?php echo $s ?>"<?php if($s == $section) echo ' aria-current="page"' ?>>
                                    <?php echo Lang::tr('admin_'.$s.'_section') ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="fs-admin__section <?php echo $section ?>_section section">
                    <?php Template::display('admin_'.$section.'_section') ?>
                </div>
            </div>
        </div>
    </div>
</div>
