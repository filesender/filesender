<div class="box">
    <h1>{tr:guests_page}</h1>
    <div id='creation_form'>
        <div class="invite_guest box">
            <div class="disclamer">
                {tr:send_new_voucher}
            </div>

            <table class="two_columns">
                <tr>
                    <td class="box">
                        <div class="fieldcontainer">
                            <?php $emails = Auth::user()->email_addresses ?>

                            <label for="from" class="mandatory">{tr:from} :</label>

                            <?php if (count($emails) > 1) { ?>

                            <select name="from">
                                <?php foreach ($emails as $email) { ?>
                                <option><?php echo $email ?></option>
                                <?php } ?>
                            </select>

                            <?php } else echo $emails[0] ?>
                        </div>

                        <div class="fieldcontainer">
                            <label for="to" class="mandatory">{tr:to} :</label>

                            <div class="recipients"></div>

                            <input name="to" type="text" title="{tr:email_separator_msg}" value="" placeholder="{tr:enter_to_email}" />
                        </div>

                        <div class="fieldcontainer">
                            <label for="subject">{tr:subject} ({tr:optional}) :</label>

                            <input name="subject" type="text"/>
                        </div>

                        <div class="fieldcontainer">
                            <label for="message">{tr:message} ({tr:optional}) : </label>

                            <textarea name="message" rows="4"></textarea>
                        </div>
                    </td>

                    <td class="box">
                        <?php
                            $displayoption = function($name, $cfg) {
                                $checked = $cfg['default'] ? 'checked="checked"' : '';

                                echo '<div class="fieldcontainer">';
                                echo '  <label for="'.$name.'">'.Lang::tr($name).'</label>';
                                echo '  <input name="'.$name.'" type="checkbox" '.$checked.' />';
                                echo '</div>';
                            };
                        ?>

                        <div class="basic_options">
                            <div class="fieldcontainer">
                                <label for="datepicker" id="datepicker_label" class="mandatory">{tr:expiry_date}:</label>

                                <input name="expires" type="text" autocomplete="off" title="{tr:dp_dateformat}" value="<?php echo Utilities::formatDate(Guest::getDefaultExpire()) ?>"/>
                            </div>
                            <?php foreach(Guest::availableOptions(false) as $name => $cfg) $displayoption($name, $cfg) ?>
                        </div>

                        <?php if(count(Guest::availableOptions(true))) { ?>
                        <div class="fieldcontainer">
                            <a class="toggle_advanced_options" href="#">{tr:advanced_settings}</a>
                        </div>
                         <div class="advanced_options">
                            <?php foreach(Guest::availableOptions(true) as $name => $cfg) $displayoption($name, $cfg) ?>
                         </div>     
                        <?php } ?>
                    </td>
                </tr>
            </table>

            <div class="buttons">
                <a href="#" class="send">
                    <span class="fa fa-envelope fa-lg"></span> {tr:send_voucher}
                </a>
            </div>
        </div>
    </div>
</div>

<div class="box">
    <h1>{tr:guests}</h1>
    
    <?php Template::display('guests_table', array('guests' => Guest::fromUser(Auth::user()))) ?>
</div>

<div class="box">
    <h1>{tr:guests_transfers}</h1>
    
    <?php Template::display('transfers_table', array('transfers' => Transfer::fromGuestsOf(Auth::user()))) ?>
</div>
    
<script type="text/javascript" src="{path:js/guests_page.js}"></script>
