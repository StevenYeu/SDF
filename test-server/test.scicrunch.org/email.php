<?php
// EMAIL VERSION 1
if($alt == 0):
?>
<!-- Changed bu Steven - Updated Links to SDF -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <body>
        <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable" style="background-color:#dedede; margin:20px">
            <tr>
                <td valign="top">
                    <table align="center" width=640 border="0" cellpadding="20" cellspacing="0" style="border:1px solid #ccc; background-color:#fefefe; margin-top:10px;" id="emailContainer">
                        <tr style="font-family:'Hevetica Neue'; font-size:35px; color:#FFF; border-bottom:1px solid #ccc;">
                            <!-- <td>
                                <img src="https://scicrunch.org/images/scicrunch-blue.png" height=35px alt="SciCrunch" />
                            </td> -->
                        </tr>
                        <?php echo \helper\writeEachMessage($message); ?>
                    </table>
                </td>
            </tr>
            <tr>
                <td align="center" style="font-family:'Helvetica Neue'; font-size:12px;">
                    <a href="https://sdf.sdsc.edu">SDF</a> | <a href="https://sdf.sdsc.edu/page/privacy">Privacy Policy</a> | <a href="https://sdf.sdsc.edu/page/terms">Terms of Service</a>
                </td>
            </tr>
        </table>
    </body>
</html>

<?php
// EMAIL VERSION 2 -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

elseif($alt == 1):
    $logo_path = '/upload/community-logo/' . $data->logo;
    $image_exists = file_exists($GLOBALS['DOCUMENT_ROOT'] . $logo_path);
    if($image_exists){
        $img_src = "https://scicrunch.org" . $logo_path;
        $img_height = "75px";
    }else{
        $img_src = "https://scicrunch.org/images/scicrunch-blue.png";
        $img_height = "35px";
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <body>
        <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable" style="margin:20px">
            <tr>
                <td valign="top">
                    <table align="center" width=640 border="0" cellpadding="20" cellspacing="0" style="border:1px solid #ccc; margin-top:10px;" id="emailContainer">
                        <tr style="font-family:'Hevetica Neue'; font-size:25px; color:000; border-bottom:1px solid #ccc;">
                            <td>
                                <img src="<?php echo $img_src ?>" height=<?php echo $img_height ?> alt="SciCrunch" />
                                <?php echo $data->name ?>
                            </td>
                        </tr>
                        <?php echo \helper\writeEachMessage($message); ?>
                        <?php if($image_exists): ?>
                            <tr align="right">
                                <a href="http://scicrunch.org"><img height=25px src="https://scicrunch.org/images/scicrunch-blue.png" alt="SciCrunch"/></a>
                            </tr>
                        <?php endif ?>
                    </table>
                </td>
            </tr>
            <tr>
                <td align="center" style="font-family:'Helvetica Neue'; font-size:12px;">
                    <a href="https://scicrunch.org">SciCrunch</a> | <a href="https://scicrunch.org/page/privacy">Privacy Policy</a> | <a href="https://scicrunch.org/page/terms">Terms of Service</a>
                </td>
            </tr>
        </table>
    </body>
</html>

<?php
// EMAIL VERSION 3 -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

elseif($alt == 2): ?>

<html xmlns="http://www.w3.org/1999/xhtml">
    <body>
        <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable" style="background-color:#dedede; margin:20px">
            <tr>
                <td valign="top">
                    <table align="center" width=640 border="0" cellpadding="20" cellspacing="0" style="border:1px solid #ccc; background-color:#fefefe; margin-top:10px;" id="emailContainer">
                        <tr style="font-family:'Hevetica Neue'; font-size:35px; color:#FFF; border-bottom:1px solid #ccc;">
                            <td>
                                <img src="https://scicrunch.org/images/scicrunch-blue.png" height=35px alt="SciCrunch" />
                            </td>
                        </tr>
                        <tr><td><?php echo $message ?></td></tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td align="center" style="font-family:'Helvetica Neue'; font-size:12px;">
                    <a href="https://scicrunch.org">SciCrunch</a> | <a href="https://scicrunch.org/page/privacy">Privacy Policy</a> | <a href="https://scicrunch.org/page/terms">Terms of Service</a>
                </td>
            </tr>
        </table>
    </body>
</html>

<?php
// EMAIL VERSION 4 -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

elseif($alt == 3):
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <body>
        <table border="0" cellpadding="0" cellspacing="0" height="100%" width="640" id="bodyTable" style="background-color:#dedede; margin:20px">
            <tr>
                <td valign="top">
                    <table align="center" width=620 border="0" cellpadding="3" cellspacing="0" style="border:1px solid #ccc; background-color:#fefefe; margin-top:10px;" id="emailContainer">
                        <!-- <tr style="font-family:'Hevetica Neue'; font-size:35px; color:#FFF; border-bottom:1px solid #ccc;">
                            <td>
                                <img src="https://scicrunch.org/images/scicrunch-blue.png" height=35px alt="SciCrunch" />
                            </td>
                        </tr> -->
                        <?php echo \helper\writeEachMessage($message); ?>
                    </table>
                </td>
            </tr>
            <tr>
                <td align="center" style="font-family:'Helvetica Neue'; font-size:12px;">
                    <a href="https://scicrunch.org">SciCrunch</a> | <a href="https://scicrunch.org/page/privacy">Privacy Policy</a> | <a href="https://scicrunch.org/page/terms">Terms of Service</a>
                </td>
            </tr>
        </table>
    </body>
</html>

<?php endif ?>
