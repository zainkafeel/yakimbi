<?php
require('config.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <title>Test Yakimbi.com</title>
    <meta charset=utf-8 />
    <link rel="stylesheet" href="css/colorbox.css" />
    <link rel="stylesheet" href="css/style.css" />
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
        <script src="js/jquery.colorbox.js"></script>
        <script language="javascript" src="js/custom.js"></script>
  </head>
  <body>
          <div id="fav_image">
            <h1>Total 0 Favourite</h1>
          </div>
          <div id="container">
            <?php
            if (isset($_GET['tag'])) {
            ?>
             <form action="<?php echo $_SERVER['PHP_SELF']?>" method="get">
             <p>Search for photos with the following tag:
            <input type="text" size="20" name="tag" value="<?=$_GET['tag'];?>"/> <input type="submit" value="Go!"/></p>
             </form>
        <?
           print api::do_search($_GET['tag']);
        } else {
        ?>
             <form action="<?php echo $_SERVER['PHP_SELF']?>" method="get">
             <p>Search for photos with the following tag:
            <input type="text" size="20" name="tag"/> <input type="submit" value="Go!"/></p>
             </form>
        <?php
        }
        ?>
        </div>
  </body>
</html>