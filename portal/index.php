<?php
/**
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * Copyright (C) 2011 Cassian LUP <cassi.lup@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @author Cassian LUP <cassi.lup@gmail.com>
 * @link http://www.open-emr.org
 */

    //setting the session & other config options
    session_start();

    //don't require standard openemr authorization in globals.php
    $ignoreAuth = 1;

    //For redirect if the site on session does not match
    $landingpage = "index.php?site=".$_GET['site'];

    //includes
    require_once('../interface/globals.php');

    ini_set("error_log",E_ERROR || ~E_NOTICE);
    //exit if portal is turned off
    if ( !(isset($GLOBALS['portal_onsite_two_enable'])) || !($GLOBALS['portal_onsite_two_enable']) ) {
      echo htmlspecialchars( xl('Patient Portal is turned off'), ENT_NOQUOTES);
      exit;
    }

    // security measure -- will check on next page.
    $_SESSION['itsme'] = 1;
    //

    //
    // Deal with language selection
    //
    // collect default language id (skip this if this is a password update)
    if (!(isset($_SESSION['password_update']))) {
      $res2 = sqlStatement("select * from lang_languages where lang_description = ?", array($GLOBALS['language_default']) );
      for ($iter = 0;$row = sqlFetchArray($res2);$iter++) {
        $result2[$iter] = $row;
      }
      if (count($result2) == 1) {
        $defaultLangID = $result2[0]{"lang_id"};
        $defaultLangName = $result2[0]{"lang_description"};
      }
      else {
        //default to english if any problems
        $defaultLangID = 1;
        $defaultLangName = "English";
      }
      // set session variable to default so login information appears in default language
      $_SESSION['language_choice'] = $defaultLangID;
      // collect languages if showing language menu
      if ($GLOBALS['language_menu_login']) {
        // sorting order of language titles depends on language translation options.
        $mainLangID = empty($_SESSION['language_choice']) ? '1' : $_SESSION['language_choice'];
        if ($mainLangID == '1' && !empty($GLOBALS['skip_english_translation'])) {
          $sql = "SELECT * FROM lang_languages ORDER BY lang_description, lang_id";
          $res3=SqlStatement($sql);
        }
        else {
          // Use and sort by the translated language name.
          $sql = "SELECT ll.lang_id, " .
                 "IF(LENGTH(ld.definition),ld.definition,ll.lang_description) AS trans_lang_description, " .
                 "ll.lang_description " .
                 "FROM lang_languages AS ll " .
                 "LEFT JOIN lang_constants AS lc ON lc.constant_name = ll.lang_description " .
                 "LEFT JOIN lang_definitions AS ld ON ld.cons_id = lc.cons_id AND " .
                 "ld.lang_id = ? " .
                 "ORDER BY IF(LENGTH(ld.definition),ld.definition,ll.lang_description), ll.lang_id";
          $res3=SqlStatement($sql, array($mainLangID) );
        }
        for ($iter = 0;$row = sqlFetchArray($res3);$iter++) {
          $result3[$iter] = $row;
        }
        if (count($result3) == 1) {
          //default to english if only return one language
          $hiddenLanguageField = "<input type='hidden' name='languageChoice' value='1' />\n";
        }
      }
      else {
        $hiddenLanguageField = "<input type='hidden' name='languageChoice' value='".htmlspecialchars($defaultLangID,ENT_QUOTES)."' />\n";
      }
    }

?>

<html>
<head>
    <title><?php echo xlt('Patient Portal Login'); ?></title>

    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-11-3/index.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery.gritter-1-7-4/js/jquery.gritter.min.js"></script>

    <link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery.gritter-1-7-4/css/jquery.gritter.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/base.css?v=<?php echo $v_js_includes; ?>" />

    <script type="text/javascript">
        function process() {

            if (!(validate())) {
                alert ('<?php echo addslashes( xl('Field(s) are missing!') ); ?>');
                return false;
            }
        }
	function validate() {
            var pass=true;
	    if (document.getElementById('uname').value == "") {
		document.getElementById('uname').style.border = "1px solid red";
                pass=false;
	    }
	    if (document.getElementById('pass').value == "") {
		document.getElementById('pass').style.border = "1px solid red";
                pass=false;
	    }
            return pass;
	}
        function process_new_pass() {

            if (!(validate_new_pass())) {
                alert ('<?php echo addslashes( xl('Field(s) are missing!') ); ?>');
                return false;
            }
            if (document.getElementById('pass_new').value != document.getElementById('pass_new_confirm').value) {
                alert ('<?php echo addslashes( xl('The new password fields are not the same.') ); ?>');
                return false;
            }
            if (document.getElementById('pass').value == document.getElementById('pass_new').value) {
                alert ('<?php echo addslashes( xl('The new password can not be the same as the current password.') ); ?>');
                return false;
            }
        }

        function validate_new_pass() {
            var pass=true;
            if (document.getElementById('uname').value == "") {
                document.getElementById('uname').style.border = "1px solid red";
                pass=false;
            }
            if (document.getElementById('pass').value == "") {
                document.getElementById('pass').style.border = "1px solid red";
                pass=false;
            }
            if (document.getElementById('pass_new').value == "") {
                document.getElementById('pass_new').style.border = "1px solid red";
                pass=false;
            }
            if (document.getElementById('pass_new_confirm').value == "") {
                document.getElementById('pass_new_confirm').style.border = "1px solid red";
                pass=false;
            }
            return pass;
        }
    </script>
    <style type="text/css">
	body {
	    font-family: sans-serif;
	    background-color: #638fd0;

	    background: -webkit-radial-gradient(circle, white, #638fd0);
	    background: -moz-radial-gradient(circle, white, #638fd0);
	}

    </style>


</head>
<body>
<br><br>
    <center>

    <?php if (isset($_SESSION['password_update'])||isset($_GET['password_update'])) {
        $_SESSION['password_update']=1;
        ?>
      <div id="wrapper" class="centerwrapper">
        <h2 class="title"><?php echo xlt('Please Enter a New Password'); ?></h2>
        <form action="get_patient_info.php" method="POST" onsubmit="return process_new_pass()" >
            <table>
                <tr>
                    <td class="algnRight"><?php echo xlt('User Name'); ?></td>
                    <td><input name="uname" id="uname" type="text" autocomplete="off" value="<?php echo attr($_SESSION['portal_username']); ?>"/></td>
                </tr>
                <tr>
                    <td class="algnRight"><?php echo xlt('Current Password');?></>
                    <td>
                        <input name="pass" id="pass" type="password" autocomplete="off" />
                    </td>
                </tr>
                <tr>
                    <td class="algnRight"><?php echo xlt('New Password');?></>
                    <td>
                        <input name="pass_new" id="pass_new" type="password" />
                    </td>
                </tr>
                <tr>
                    <td class="algnRight"><?php echo xlt('Confirm New Password');?></>
                    <td>
                        <input name="pass_new_confirm" id="pass_new_confirm" type="password" />
                    </td>
                </tr>
                <tr>
                    <td colspan=2><br><center><input type="submit" value="<?php echo xlt('Log In');?>" /></center></td>
                </tr>
            </table>
        </form>

        <div class="copyright"><?php echo xlt('Powered by');?> OpenEMR</div>

      </div>

    <?php } else { ?>
      <div id="wrapper" class="centerwrapper">
	<h2 class="title"><?php echo xlt('Patient Portal Login'); ?></h2>
	<form action="get_patient_info.php" method="POST" onsubmit="return process()" >
	    <table>
		<tr>
		    <td class="algnRight"><?php echo xlt('User Name'); ?></td>
		    <td><input name="uname" id="uname" type="text" autocomplete="on" /></td>
		</tr>
		<tr>
		    <td class="algnRight"><?php echo xlt('Password');?></>
		    <td>
			<input name="pass" id="pass" type="password" required autocomplete="on" /><input name="passaddon" id="passaddon" placeholder="Email" type="email" autocomplete="on" />
		    </td>
		</tr>

                <?php if ($GLOBALS['language_menu_login']) { ?>
                 <?php if (count($result3) != 1) { ?>
                  <tr>
                    <td><span class="text"><?php echo xlt('Language'); ?></span></td>
                    <td>
                        <select name=languageChoice size="1">
                            <?php
                            echo "<option selected='selected' value='".htmlspecialchars($defaultLangID,ENT_QUOTES)."'>" . htmlspecialchars( xl('Default') . " - " . xl($defaultLangName), ENT_NOQUOTES) . "</option>\n";
                            foreach ($result3 as $iter) {
                                if ($GLOBALS['language_menu_showall']) {
                                    if ( !$GLOBALS['allow_debug_language'] && $iter['lang_description'] == 'dummy') continue; // skip the dummy language
                                    echo "<option value='".htmlspecialchars($iter['lang_id'],ENT_QUOTES)."'>".htmlspecialchars($iter['trans_lang_description'],ENT_NOQUOTES)."</option>\n";
                                }
                                else {
                                    if (in_array($iter['lang_description'], $GLOBALS['language_menu_show'])) {
                                        if ( !$GLOBALS['allow_debug_language'] && $iter['lang_description'] == 'dummy') continue; // skip the dummy language
                                        echo "<option value='".htmlspecialchars($iter['lang_id'],ENT_QUOTES)."'>".htmlspecialchars($iter['trans_lang_description'],ENT_NOQUOTES)."</option>\n";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </td>
                  </tr>
                <?php }} ?>

		<tr>
		    <td colspan=2><br><center><input type="submit" value="<?php echo xlt('Log In');?>" /></center></td>
		</tr>
	    </table>
            <?php if (!(empty($hiddenLanguageField))) echo $hiddenLanguageField; ?>
	</form>

        <div class="copyright"><?php echo xlt('Powered by');?> OpenEMR</div>
      </div><div><img src='<?php echo $GLOBALS['images_static_relative']; ?>/logo-full-con.png'/></div>
    <?php } ?>

    </center>

<script type="text/javascript">
      $(document).ready(function() {

<?php // if something went wrong
     if (isset($_GET['w'])) { ?>
	var unique_id = $.gritter.add({
	    title: '<span class="red"><?php echo xlt('Oops!');?></span>',
	    text: '<?php echo xlt('Something went wrong. Please try again.'); ?>',
	    sticky: false,
	    time: '5000',
	    class_name: 'my-nonsticky-class'
	});
<?php } ?>

<?php // if successfully logged out
     if (isset($_GET['logout'])) { ?>
	var unique_id = $.gritter.add({
	    title: '<span class="green"><?php echo xlt('Success');?></span>',
	    text: '<?php echo xlt('You have been successfully logged out.');?>',
	    sticky: false,
	    time: '5000',
	    class_name: 'my-nonsticky-class'
	});
<?php } ?>
	return false;

    });
</script>

</body>
</html>
