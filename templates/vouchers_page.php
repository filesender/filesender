<div class="box">
    <h1>{tr:vouchers_page}</h1>
    
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
  <div class="heading">{tr:ACTIVE_VOUCHERS}</div>
  <table id="myfiles" style="table-layout:fixed; width: 100%; padding: 4px; border-spacing: 0; border: 0">
    <tr class="headerrow">
      <td id="vouchers_header_from" class="tblmcw3 HardBreak" style="vertical-align: middle"><strong>{tr:FROM}</strong></td>
      <td id="vouchers_header_to" class="tblmcw3 HardBreak" style="vertical-align: middle"><strong>{tr:TO}</strong></td>
      <td id="vouchers_header_subject" class="HardBreak" style="width: 50px; vertical-align: middle"><strong>{tr:SUBJECT}</strong></td>
      <td id="vouchers_header_message" class="HardBreak" style="width: 50px; vertical-align: middle"><strong>{tr:MESSAGE}</strong></td>
      <td id="vouchers_header_created" class="tblmcw3 HardBreak" style="vertical-align: middle"><strong>{tr:CREATED}</strong></td>
      <td id="vouchers_header_expiry" class="tblmcw3 HardBreak" style="vertical-align: middle"><strong>{tr:EXPIRY}</strong></td>
      <td class="tblmcw1"></td>
    </tr>
    
    <?php
    $i = 0;
    foreach($json_o as $item) {
        $i += 1; // counter for file id's
        $altColor = ($i % 2 != 0)? 'altcolor' : '';
        echo '<tr><td class="dr7 HardBreak"></td><td class="dr7 '.$altColor.'"></td><td class="dr7"></td><td class="dr7"></td><td class="dr7"></td><td class="dr7"></td><td class="dr7"></td></tr>';
        echo '<tr><td class="dr1 HardBreak '.$altColor.'" style="vertical-align: middle">'.$item['filefrom'].'</td>';
        echo '<td class="dr2 HardBreak '.$altColor.'" style="vertical-align: middle">'.$item['fileto'].'</td>';
        echo '<td class="dr2 HardBreak '.$altColor.'" style="text-align: center; vertical-align: middle">';
        
        if($item['filesubject'] != '') {
            echo '<i class="fa fa-file-text-o fa-lg" border="0" alt="" style="cursor:pointer;display:block; margin:auto" title="'.utf8ToHtml($item['filesubject'], true).'"></i>';
        }
        
        echo '</td><td class="dr2 HardBreak ' . $altColor . '" style="text-align: center; vertical-align: middle">';
        
        if($item['filemessage'] != '') {
            echo '<i class="fa fa-file-text-o fa-lg" border="0" alt="" style="cursor:pointer;display:block; margin:auto" title="'.utf8ToHtml($item['filemessage'], true).'"></i>'; 
        }
        
        echo '<td class="dr2 HardBreak '.$altColor.'" style="vertical-align: middle">'.Utilities::formatDate(strtotime($item['filecreateddate'])).'</td>';
        echo '<td class="dr2 HardBreak '.$altColor.'" style="vertical-align: middle">'.Utilities::formatDate(strtotime($item['fileexpirydate'])).'</td>';
        echo '<td class="dr8 '.$altColor.'" style="text-align: center; vertical-align: middle">
                <div style="cursor:pointer">
                  <i id="btn_deletevoucher_'.$i.'" class="fa fa-minus-circle fa-lg" alt="" title="'.Lang::tr('DELETE').'" onclick="confirmdelete(\''.$item['filevoucheruid'].'\')" style="color:#ff0000;cursor:pointer;border:0"></i>
                </div>
              </td>
            </tr>'; //etc
    }
    echo '<tr><td class="dr7"></td><td class="dr7"></td><td class="dr7"></td><td class="dr7"></td><td class="dr7"></td><td class="dr7"></td><td class="dr7"></td></tr>';

    ?>
  </table>
  
  <?php if($i == 0) echo Lang::tr('NO_VOUCHERS') ?>
  
</div>

<?php //require_once('files.php'); ?>
    
    <script type="text/javascript" src="{path:res/js/vouchers_page.js}"></script>
</div>
