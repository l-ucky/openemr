<?php
// +-----------------------------------------------------------------------------+
// Copyright (C) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
// Copyright (C) 2017 Brady Miller <brady.g.miller@gmail.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// Author:   Jacob T Paul <jacob@zhservices.com>
//           Vinish K <vinish@zhservices.com>
//           Brady Miller <brady.g.miller@gmail.com>
//
// +------------------------------------------------------------------------------+

include_once("../../globals.php");
include_once("$srcdir/api.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once($GLOBALS['srcdir'] . '/csv_like_join.php');
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

formHeader("Form:Functional and Cognitive Status Form");
$returnurl = 'encounter_top.php';
$formid = 0 + (isset($_GET['id']) ? $_GET['id'] : '');
if ($formid) {
    $sql = "SELECT * FROM `form_functional_cognitive_status` WHERE id=? AND pid = ? AND encounter = ?";
    $res = sqlStatement($sql, array($formid,$_SESSION["pid"], $_SESSION["encounter"]));

    for ($iter = 0; $row = sqlFetchArray($res); $iter++)
        $all[$iter] = $row;
    $check_res = $all;
}

$check_res = $formid ? $check_res : array();
?>
<html>
    <head>
        <?php html_header_show(); ?>

        <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
        <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js?v=<?php echo $v_js_includes; ?>"></script>
        <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
        <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js"></script>

        <link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
        <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css">

    </head>

    <body class="body_top">
        <script type="text/javascript">

            function duplicateRow(e) {
                var newRow = e.cloneNode(true);
                e.parentNode.insertBefore(newRow, e.nextSibling);
                changeIds('tb_row');
                changeIds('description');
                changeIds('activity');
                changeIds('activity1');
                changeIds('code');
                changeIds('codetext');
                changeIds('code_date');
                changeIds('displaytext');
                removeVal(newRow.id);
            }

            function removeVal(rowid)
            {
                rowid1 = rowid.split('tb_row_');
                document.getElementById("description_" + rowid1[1]).value = '';
                document.getElementById("activity_" + rowid1[1]).checked = false;
                document.getElementById("activity_" + rowid1[1]).value = 0;
                document.getElementById("activity1_" + rowid1[1]).value = 0;
                document.getElementById("code_" + rowid1[1]).value = '';
                document.getElementById("codetext_" + rowid1[1]).value = '';
                document.getElementById("code_date_" + rowid1[1]).value = '';
                document.getElementById("displaytext_" + rowid1[1]).innerHTML = '';
            }

            function changeIds(class_val) {
                var elem = document.getElementsByClassName(class_val);
                for (var i = 0; i < elem.length; i++) {
                    if (elem[i].id) {
                        index = i + 1;
                        elem[i].id = class_val + "_" + index;
                    }
                }
            }

            function deleteRow(rowId)
            {
                if (rowId != 'tb_row_1') {
                    var table = document.getElementById("functional_cognitive_status");
                    var rowIndex = document.getElementById(rowId).rowIndex;
                    table.deleteRow(rowIndex);
                }
            }

            function sel_code(id)
            {
                id = id.split('tb_row_');
                var checkId = '_' + id[1];
                document.getElementById('clickId').value = checkId;
                dlgopen('<?php echo $GLOBALS['webroot'] . "/interface/patient_file/encounter/" ?>find_code_popup.php?codetype=SNOMED-CT', '_blank', 700, 400);
            }

            function set_related(codetype, code, selector, codedesc) {
                var checkId = document.getElementById('clickId').value;
                document.getElementById("code" + checkId).value = code;
                document.getElementById("codetext" + checkId).value = codedesc;
                document.getElementById("displaytext" + checkId).innerHTML  = codedesc;
            }

            function checkVal(id)
            {
                var id1 = id.split('activity_')
                if (document.getElementById(id).checked)
                {
                    document.getElementById(id).value = 1;
                    document.getElementById('activity1_' + id1[1]).value = 1;
                }
                else
                {
                    document.getElementById(id).value = 0;
                    document.getElementById('activity1_' + id1[1]).value = 0;
                }
            }

            $(document).ready(function() {
                // special case to deal with static and dynamic datepicker items
                $(document).on('mouseover','.datepicker', function(){
                    $(this).datetimepicker({
                        <?php $datetimepicker_timepicker = false; ?>
                        <?php $datetimepicker_showseconds = false; ?>
                        <?php $datetimepicker_formatInput = false; ?>
                        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
                    });
                });
            });

        </script>
        <p><span class="forms-title"><?php echo xlt('Functional and Cognitive Status Form'); ?></span></p>
        </br>
        <?php echo "<form method='post' name='my_form' " . "action='$rootdir/forms/functional_cognitive_status/save.php?id=" . attr($formid) . "'>\n"; ?>
        <table id="functional_cognitive_status" border="0">

            <?php
            if (!empty($check_res)) {
                foreach ($check_res as $key => $obj) {
                    ?>
                    <tr class="tb_row" id="tb_row_<?php echo $key + 1; ?>">
                        <td align="left" class="forms"><?php echo xlt('Code'); ?>:</td>
                        <td class="forms">
                            <input type="text" id="code_<?php echo $key + 1; ?>" style="width:210px" name="code[]" class="code" value="<?php echo text($obj{"code"}); ?>" onclick='sel_code(this.parentElement.parentElement.id);'><br>
                            <span id="displaytext_<?php echo $key + 1; ?>" style="width:210px !important;display: block;font-size:13px;color: blue;" class="displaytext"><?php echo text($obj{"codetext"}); ?></span>
                            <input type="hidden" id="codetext_<?php echo $key + 1; ?>" name="codetext[]" class="codetext" value="<?php echo text($obj{"codetext"}); ?>">
                        </td>
                        <td align="left" class="forms"><?php echo xlt('Description'); ?>:</td>
                        <td class="forms">
                            <textarea rows="4" id="description_<?php echo $key + 1; ?>" cols="30" name="description[]" class="description"><?php echo text($obj{"description"}); ?></textarea>
                        </td>
                        <td align="left" class="forms"><?php echo xlt('Date'); ?>:</td>
                        <td class="forms">
                            <input type='text' id="code_date_<?php echo $key + 1; ?>" size='10' name='code_date[]' class="code_date datepicker" <?php echo attr($disabled) ?> value='<?php echo attr($obj{"date"}); ?>' title='<?php echo xla('yyyy-mm-dd Date of service'); ?>' />
                        </td>
                        <td align="left" class="forms"><?php echo xlt('Active'); ?>:</td>
                        <td>
                            <input type="checkbox" name="activity[]" onclick="checkVal(this.id);" id="activity_<?php echo $key + 1; ?>" value="<?php echo text($obj{"activity"}); ?>" <?php if ($obj{"activity"} == 1) echo "checked='checked'" ?> class="activity">
                            <input type="hidden" name="activity1[]" id="activity1_<?php echo $key + 1; ?>" value="<?php echo text($obj{"activity"}); ?>" class="activity1">
                        </td>
                        <td>
                            <img src='../../pic/add.png' onclick="duplicateRow(this.parentElement.parentElement);" align='absbottom' width='27' height='24' border='0' style='cursor:pointer;cursor:hand' title='<?php echo xla('Click here to duplicate the row'); ?>'>
                            <img src='../../pic/remove.png' onclick="deleteRow(this.parentElement.parentElement.id);" align='absbottom' width='24' height='22' border='0' style='cursor:pointer;cursor:hand' title='<?php echo xla('Click here to delete the row'); ?>'>
                        </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr class="tb_row" id="tb_row_1">
                <td align="left" class="forms"><?php echo xlt('Code'); ?>:</td>
                <td class="forms">
                    <input type="text" id="code_1" name="code[]" class="code" style="width:210px" value="<?php echo text($obj{"code"}); ?>" onclick='sel_code(this.parentElement.parentElement.id);'><br>
                    <span id="displaytext_1" style="width:210px !important;display: block;font-size:13px;color: blue;" class="displaytext"></span>
                    <input type="hidden" id="codetext_1" name="codetext[]" class="codetext" value="<?php echo text($obj{"codetext"}); ?>">
                </td>
                <td align="left" class="forms"><?php echo xlt('Description'); ?>:</td>
                <td class="forms">
                    <textarea rows="4" id="description_1" cols="30" name="description[]" class="description"><?php echo text($obj{"description"}); ?></textarea>
                </td>
                <td align="left" class="forms"><?php echo xlt('Date'); ?>:</td>
                <td class="forms">
                    <input type='text' id="code_date_1" size='10' name='code_date[]' class="code_date datepicker" <?php echo attr($disabled) ?> value='<?php echo attr($obj{"date"}); ?>' title='<?php echo xla('yyyy-mm-dd Date of service'); ?>' />
                </td>
                <td align="left" class="forms"><?php echo xlt('Active'); ?>:</td>
                <td>
                    <input type="checkbox" name="activity[]" onclick="checkVal(this.id);" id="activity_1" value="0" class="activity">
                    <input type="hidden" name="activity1[]" id="activity1_1" value="0" class="activity1">
                </td>
                <td>
                    <img src='../../pic/add.png' onclick="duplicateRow(this.parentElement.parentElement);" align='absbottom' width='27' height='24' border='0' style='cursor:pointer;cursor:hand' title='<?php echo xla('Click here to duplicate the row'); ?>'>
                    <img src='../../pic/remove.png' onclick="deleteRow(this.parentElement.parentElement.id);" align='absbottom' width='24' height='22' border='0' style='cursor:pointer;cursor:hand' title='<?php echo xla('Click here to delete the row'); ?>'>
                </td>
        </tr>
    <?php }
    ?>

    <tr>
        <td align="left" colspan="5" style="padding-bottom:7px;"></td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td colspan="3">
            <input type="hidden" id="clickId" value="">
            <input type='submit'  value='<?php echo xla('Save'); ?>' class="button-css">&nbsp;
        </td>
    </tr>
</table>
</form>
<?php
formFooter();
?>
