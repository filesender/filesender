<div class="box">
    <?php
    
    $sections = array('transfers', 'guests', 'users' );
    
    if(Config::get('config_overrides'))
        $sections[] = 'config';
    
    $section = 'transfers';
    if(array_key_exists('as', $_REQUEST))
        $section = $_REQUEST['as'];
    
    if(!in_array($section, $sections)) throw new GUIUnknownAdminSectionException($section);
    
    ?>
    
    <div class="menu">
        <ul>
        <?php foreach($sections as $s) { ?>
            <li class="<?php if($s == $section) echo 'current' ?>">
                <a href="?s=admin&as=<?php echo $s ?>">
                    <?php echo Lang::tr('admin_'.$s.'_section') ?>
                </a>
            </li>
        <?php } ?>
        </ul>
    </div>
    
    <div class="<?php echo $section ?>_section section">
        <?php Template::display('admin_'.$section.'_section') ?>
    </div>
</div>
