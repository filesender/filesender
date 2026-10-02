<div class="fs-admin__block">
    <h4>{tr:admin_config_section}</h4>

    <?php try { ?>

    <form id="config_form" method="post" action="{path:?s=admin&as=config}">
        <?php foreach(Config::overrides() as $key => $dfn) { ?>
            <?php $default = Config::getBaseValue($key) ?>
            <?php $isdefault = is_null($dfn['value']) ? '1' : '' ?>
            <?php $value = $isdefault ? $default : $dfn['value'] ?>
            <div class="fs-admin__parameter parameter" data-key="<?php echo $key ?>" data-default="<?php echo is_bool($default) ? ($default ? '1' : '') : $default ?>" data-is-default="<?php echo $isdefault ?>">
                <?php if($dfn['type'] == 'bool') { ?>
                    <label class="fs-checkbox">
                        <label for="<?php echo $key ?>"><?php echo $key ?></label>
                        <input type="checkbox" id="<?php echo $key ?>" name="<?php echo $key ?>" value="1" <?php echo $value ? 'checked="checked"' : '' ?> />
                        <span class="fs-checkbox__mark"></span>
                    </label>
                <?php } else if($dfn['type'] == 'enum') { ?>
                    <div class="fs-select">
                        <label for="<?php echo $key ?>"><?php echo $key ?></label>
                        <select id="<?php echo $key ?>" name="<?php echo $key ?>">
                            <?php foreach($dfn['values'] as $v) { ?>
                                <option value="<?php echo $v ?>" <?php echo ($v == $value) ? 'selected="selected"' : '' ?>><?php echo $v ?></option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } else if($dfn['type'] == 'string') { ?>
                    <div class="fs-input-group">
                        <label for="<?php echo $key ?>"><?php echo $key ?></label>
                        <input type="text" id="<?php echo $key ?>" name="<?php echo $key ?>" value="<?php echo $value ?>" />
                    </div>
                <?php } ?>

                <span class="make_default clickable"><i class="fa fa-undo"></i> {tr:make_default}</span>
                <span class="is_default">{tr:is_default}</span>
            </div>
        <?php } ?>

        <div class="fs-admin__form-actions">
            <button type="button" class="fs-button save">
                <i class="fa fa-save"></i>
                <span>{tr:save}</span>
            </button>
        </div>
    </form>
    <?php } catch(ConfigOverrideDisabledException $e) {} ?>
</div>

<script type="text/javascript" src="{path:js/admin_config.js}"></script>
