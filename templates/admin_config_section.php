<h2>{tr:admin_config_section}</h2>

<?php try { ?>

<form id="config_form" method="post" action="{path:?s=admin&as=config}">
    <div class="box">
    <?php foreach(Config::overrides() as $key => $dfn) { ?>
        <?php $default = Config::getBaseValue($key) ?>
        <?php $isdefault = is_null($dfn['value']) ? '1' : '' ?>
        <?php $value = $isdefault ? $default : $dfn['value'] ?>
        <div class="parameter" data-key="<?php echo $key ?>" data-default="<?php echo is_bool($default) ? ($default ? '1' : '') : $default ?>" data-is-default="<?php echo $isdefault ?>">
            <label for="<?php echo $key ?>"><?php echo $key ?></label>
            
            <?php if($dfn['type'] == 'string') { ?>
            <input type="text" name="<?php echo $key ?>" value="<?php echo $value ?>" />
            
            <?php } else if($dfn['type'] == 'bool') { ?>
            <input type="checkbox" name="<?php echo $key ?>" value="1" <?php echo $value ? 'checked="checked"' : '' ?> />
            
            <?php } else if($dfn['type'] == 'enum') { ?>
            <select name="<?php echo $key ?>">
                <?php foreach($dfn['values'] as $v) { ?>
                <option value="<?php echo $v ?>" <?php echo ($v == $value) ? 'selected="selected"' : '' ?>><?php echo $v ?></option>
                <?php } ?>
            </select>
            
            <?php } ?>
            
            <span class="make_default clickable"><span class="fa fa-lg fa-undo"></span> {tr:make_default}</span>
            <span class="is_default">{tr:is_default}</span>
        </div>
    <?php } ?>
    </div>
    
    <div class="box">
        <a class="save" href="#">
            <span class="fa fa-lg fa-save"></span> {tr:save}
        </a>
    </div>
<?php } catch(ConfigOverrideDisabledException $e) {} ?>

<script type="text/javascript" src="{path:js/admin_config.js}"></script>
