<div class="box">
    <h1>{tr:guests_page}</h1>
    
    <div class="send_voucher box">
        <div class="disclamer">
            {tr:send_new_voucher}
        </div>
        
        <table class="two_columns">
            <tr>
                <td class="box">
                    <div class="fieldcontainer">
                        <?php $emails = Auth::user()->email ?>
                        
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
                    <div class="fieldcontainer">
                        <label for="datepicker" id="datepicker_label" class="mandatory">{tr:expiry_date}:</label>
                        
                        <input name="expires" type="text" title="{tr:dp_dateformat}" value="<?php echo Utilities::formatDate(GuestVoucher::getMaxExpire()) ?>"/>
                    </div>
                    
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

<div class="box">
    <h1>{tr:guests}</h1>
    
    <?php Template::display('guests_table', array('guests' => GuestVoucher::fromUser(Auth::user()))) ?>
</div>

<div class="box">
    <h1>{tr:guests_transfers}</h1>
    
    <?php Template::display('transfers_table', array('transfers' => Transfer::fromGuestsOf(Auth::user()))) ?>
</div>
    
<script type="text/javascript" src="{path:res/js/guests_page.js}"></script>
