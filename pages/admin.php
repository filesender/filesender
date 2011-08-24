<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2011, AARNet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/* ---------------------------------
 * Admin Page
 * ---------------------------------
 * 
 */
 ?>
<script>
	$(function() {
	
	// stripe every second row in the tables
	$("#table2 tr:odd").not(":first").addClass("altcolor");
	$("#table3 tr:odd").not(":first").addClass("altcolor");
	$("#table4 tr:odd").not(":first").addClass("altcolor");
	$("#table5 tr:odd").not(":first").addClass("altcolor");
	$("#table6 tr:odd").not(":first").addClass("altcolor");
	// tab selection
	$( "#tabs" ).tabs({
   		select: function(event, ui) { 
  		window.location ="index.php?s=admin&page=1#tabs-"+ (ui.index+1);
   	}
  	});
	});
	</script>
<?php 

// get file data
$total_pages["Available"] = "";
$total_pages["Voucher"] = "";
$total_pages["Uploaded"] = "";
$total_pages["Download"] = "";
$total_pages["Error"] = "";

$Available = $functions->adminFiles("Available");
$Voucher = $functions->adminFiles("Voucher");
$Uploaded = $functions->adminLogs("Uploaded");
$Download = $functions->adminLogs("Download");
$Error = $functions->adminLogs("Error");
$drivespace = $functions->driveSpace();

?>

<div id="box"> <?php echo '<div id="pageheading">'.lang("_ADMIN").'</div>'; ?>
  <div id="tabs">
    <ul>
      <?php
	// admin tab names
		echo '<li><a href="#tabs-1">'.lang("_GENERAL").'</a></li>';
		echo '<li><a href="#tabs-2">'.lang("_UPLOADS").'</a></li>';
		echo '<li><a href="#tabs-3">'.lang("_DOWNLOADS").'</a></li>';
		echo '<li><a href="#tabs-4">'.lang("_ERRORS").'</a></li>';
		echo '<li><a href="#tabs-5">'.lang("_FILES_AVAILABLE").'</a></li>';
		echo '<li><a href="#tabs-6">'.lang("_ACTIVE_VOUCHERS").'</a></li>';
		?>
    </ul>
    <div id="tabs-1"> <?php echo  $functions->getStats(); ?><br />
      <table border="0" cellpadding="4" width="720"style="table-layout:fixed;" >
        <tr class="headerrow">
          <td><?php echo lang("_DRIVE") ?></td>
          <td><?php echo lang("_TOTAL") ?></td>
          <td><?php echo lang("_USED") ?></td>
          <td><?php echo lang("_AVAILABLE") ?></td>
          <td>% <?php echo lang("_USED") ?></td>
        </tr>
        <tr>
          <td><?php echo lang("_FILES") ?></td>
          <td><?php echo  formatBytes($drivespace["site_filestore_total"]) ?></td>
          <td><?php echo  formatBytes($drivespace["site_filestore_total"]-$drivespace["site_filestore_free"]) ?></td>
          <td><?php echo  formatBytes($drivespace["site_filestore_free"]) ?></td>
          <td><?php echo  round(($drivespace["site_filestore_total"]-$drivespace["site_filestore_free"])/$drivespace["site_filestore_total"] * 100,0) ?>%</td>
        </tr>
        <tr>
          <td><?php echo lang("_TEMP") ?></td>
          <td><?php echo  formatBytes($drivespace["site_temp_filestore_total"]) ?></td>
          <td><?php echo  formatBytes($drivespace["site_temp_filestore_total"]-$drivespace["site_temp_filestore_free"]) ?></td>
          <td><?php echo  formatBytes($drivespace["site_temp_filestore_free"]) ?></td>
          <td><?php echo  round(($drivespace["site_temp_filestore_total"]-$drivespace["site_temp_filestore_free"]) /$drivespace["site_temp_filestore_total"] *100,0)  ?>%</td>
        </tr>
      </table>
    </div>
    <div id="tabs-2">
      <table id="table2" width="720"style="table-layout:fixed;" border="0" cellspacing="1" bgcolor="#FFFFFF">
        <tr>
          <td colspan="5" align="right"><table border="0" cellpadding="5" align="left">
              <tr>
                <td><?php echo lang("_PAGE") ?>:</td>
                <?php
//  echo "::".$total_pages["Uploaded"];
  for ($i = 1; $i <= $total_pages["Uploaded"]; $i++) {
  $txt = $i;
  if ($page != $i) {$txt = "<a href=\"" . $_SERVER["PHP_SELF"] . "?s=admin&page=$i#tabs-2\">".$txt."</a>";} else { $txt = "<b>".$i."</b>";};
  ?>
                <td align="center"><?php	echo $txt ?></td>
                <?php
  }
  ?>
              </tr>
            </table></td>
        </tr>
        <tr class="headerrow">
          <?php 
echo '<td><strong>'.lang("_TO").'</strong></td>';
echo '<td><strong>'.lang("_FROM").'</strong></td>';
echo '<td><strong>'.lang("_FILE_NAME").'</strong></td>';
echo '<td><strong>'.lang("_SIZE").'</strong></td>';
echo '<td><strong>'.lang("_CREATED").'</strong></td>';
?>
        </tr>
        <?php 
foreach($Uploaded as $item) {
	echo "<tr><td class='HardBreak'>" .$item['logto'] . "</td><td class='HardBreak'>" .$item['logfrom'] . "</td><td class='HardBreak'>" .$item['logfilename']. "</td><td>" .formatBytes($item['logfilesize']). "</td><td>" .date($config['datedisplayformat'],strtotime($item['logdate'])) . "</td></tr>"; //etc
}

?>
      </table>
    </div>
    <div id="tabs-3">
      <table id="table3" width="720"style="table-layout:fixed;" border="0" cellspacing="1" bgcolor="#FFFFFF">
        <tr>
          <td colspan="5" align="right"><table border="0" cellpadding="5" align="left">
              <tr>
                <td> <?php echo lang("_PAGE") ?>:</td>
                <?php
  for ($i = 1; $i <= $total_pages["Download"]; $i++) {
  $txt = $i;
  if ($page != $i) {$txt = "<a href=\"" . $_SERVER["PHP_SELF"] . "?s=admin&page=$i#tabs-3\">".$txt."</a>";} else { $txt = "<b>".$i."</b>";};
  ?>
                <td align="center"><?php	echo $txt ?></td>
                <?php
  }
  ?>
              </tr>
            </table></td>
        </tr>
        <tr class="headerrow">
          <?php
echo '<td><strong>'.lang("_TO").'</strong></td>';
echo '<td><strong>'.lang("_FROM").'</strong></td>';
echo '<td><strong>'.lang("_FILE_NAME").'</strong></td>';
echo '<td><strong>'.lang("_SIZE").'</strong></td>';
echo '<td><strong>'.lang("_CREATED").'</strong></td>';
?>
        </tr>
        <?php 
foreach($Download as $item) {
echo "<tr><td class='HardBreak'>" .$item['logto'] . "</td><td class='HardBreak'>" .$item['logfrom'] . "</td><td class='HardBreak'>" .$item['logfilename']. "</td><td>" .formatBytes($item['logfilesize']). "</td><td>" .date($config['datedisplayformat'],strtotime($item['logdate'])) . "</td></tr>"; //etc
}

?>
      </table>
    </div>
    <div id="tabs-4">
      <table id="table4" width="720"style="table-layout:fixed;" border="0" cellspacing="1" bgcolor="#FFFFFF">
        <tr>
          <td colspan="5" align="right"><table border="0" cellpadding="5" align="left">
              <tr>
                <td> <?php echo lang("_PAGE") ?>:</td>
                <?php
  for ($i = 1; $i <= $total_pages["Error"]; $i++) {
  $txt = $i;
  if ($page != $i) {$txt = "<a href=\"" . $_SERVER["PHP_SELF"] . "?s=admin&page=$i#tabs-4\">".$txt."</a>";} else { $txt = "<b>".$i."</b>";};
  ?>
                <td align="center"><?php	echo $txt ?></td>
                <?php
  }
  ?>
              </tr>
            </table></td>
        </tr>
        <tr class="headerrow">
          <?php 
echo '<td><strong>'.lang("_TO").'</strong></td>';
echo '<td><strong>'.lang("_FROM").'</strong></td>';
echo '<td><strong>'.lang("_FILE_NAME").'</strong></td>';
echo '<td><strong>'.lang("_SUBJECT").'</strong></td>';
echo '<td><strong>'.lang("_CREATED").'</strong></td>';
?>
        </tr>
        <?php 
foreach($Error as $item) {
echo "<tr><td class='HardBreak'>" .$item['logto'] . "</td><td class='HardBreak'>" .$item['logfrom'] . "</td><td class='HardBreak'>" .$item['logfilename']. "</td><td>" .date($config['datedisplayformat'],strtotime($item['logdate'])) . "</td></tr>"; //etc
echo "<tr><td colspan=4>".$item['logmessage']."</td></tr>";
}

?>
      </table>
    </div>
    <div id="tabs-5">
      <table id="table5" width="720"style="table-layout:fixed;" border="0" cellspacing="1" bgcolor="#FFFFFF">
        <tr>
          <td colspan="5" align="right"><table border="0" cellpadding="5" align="left">
              <tr>
                <td> <?php echo lang("_PAGE") ?>:</td>
                <?php
  for ($i = 1; $i <= $total_pages["Available"]; $i++) {
  $txt = $i;
  if ($page != $i) {  $txt = "<a href=\"" . $_SERVER["PHP_SELF"] . "?s=admin&page=$i#tabs-5\">".$txt."</a>";} else { $txt = "<b>".$i."</b>";};
  ?>
                <td align="center"><?php	echo $txt ?></td>
                <?php
  }
  ?>
              </tr>
            </table></td>
        </tr>
        <tr class="headerrow">
          <?php 
echo '<td><strong>'.lang("_TO").'</strong></td>';
echo '<td><strong>'.lang("_FROM").'</strong></td>';
echo '<td><strong>'.lang("_FILE_NAME").'</strong></td>';
echo '<td><strong>'.lang("_SIZE").'</strong></td>';
echo '<td><strong>'.lang("_SUBJECT").'</strong></td>';
echo '<td><strong>'.lang("_CREATED").'</strong></td>';
echo '<td><strong>'.lang("_EXPIRY").'</strong></td>';
?>
        </tr>
        <?php 
foreach($Available as $item) {
echo "<tr><td class='HardBreak'>" .$item['fileto'] . "</td><td class='HardBreak'>" .$item['filefrom'] . "</td><td class='HardBreak'>" .$item['fileoriginalname']. "</td><td>" .formatBytes($item['filesize']). "</td><td>".$item['filesubject']. "</td><td>" .date($config['datedisplayformat'],strtotime($item['filecreateddate'])) . "</td><td>" .date($config['datedisplayformat'],strtotime($item['fileexpirydate'])) . "</td></tr>"; //etc
}

?>
      </table>
    </div>
    <div id="tabs-6">
      <div id="tablediv1">
        <table id="table6" width="720"style="table-layout:fixed;" border="0" cellspacing="1" bgcolor="#FFFFFF">
          <tr>
            <td colspan="5" align="right"><table border="0" cellpadding="5" align="left">
                <tr>
                  <td> <?php echo lang("_PAGE") ?>:</td>
                  <?php
  for ($i = 1; $i <= $total_pages["Voucher"]; $i++) {
  $txt = $i;
  if ($page != $i) { $txt = "<a href=\"" . $_SERVER["PHP_SELF"] . "?s=admin&page=$i#tabs-6\">".$txt."</a>";} else { $txt = "<b>".$i."</b>";};
  ?>
                  <td align="center"><?php	echo $txt ?></td>
                  <?php
  }
  ?>
                </tr>
              </table></td>
          </tr>
          <tr class="headerrow">
            <?php 
echo '<td><strong>'.lang("_TO").'</strong></td>';
echo '<td><strong>'.lang("_FROM").'</strong></td>';
echo '<td><strong>'.lang("_FILE_NAME").'</strong></td>';
echo '<td><strong>'.lang("_SIZE").'</strong></td>';
echo '<td><strong>'.lang("_SUBJECT").'</strong></td>';
echo '<td><strong>'.lang("_CREATED").'</strong></td>';
echo '<td><strong>'.lang("_EXPIRY").'</strong></td>';
?>
            <?php 
foreach($Voucher as $item) {
echo "<tr><td class='HardBreak'>" .$item['fileto'] . "</td><td class='HardBreak'>" .$item['filefrom'] . "</td><td class='HardBreak'>" .$item['fileoriginalname']. "</td><td>" .formatBytes($item['filesize']). "</td><td>".$item['filesubject']. "</td><td>" .date($config['datedisplayformat'],strtotime($item['filecreateddate'])) . "</td><td>" .date($config['datedisplayformat'],strtotime($item['fileexpirydate'])) . "</td></tr>"; //etc
}

?>
        </table>
      </div>
    </div>
  </div>
</div>
