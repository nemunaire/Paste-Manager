<!doctype html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>.: Pommultimédia - Paste :.</title>
    <link href="style.css" rel="stylesheet" type="text/css">
    <link href="favicon.ico" type="image/x-icon" rel="shortcut icon"/>
  </head>
  <body>
<?php
require_once("../common.php");

foreach ($_GET as $k => $t)
  {
    if (preg_match("#^[a-zA-Z0-9]{5}$#", $k) && is_file(DESTINATION . "/" . $k . ".xml"))
      {
	require_once("../geshi/geshi.php");

	$doc = new DOMDocument();
	$doc->load(DESTINATION . "/" . $k . ".xml");

	$lang = $doc->getElementsByTagName("language")->item(0)->textContent;
	if (empty($lang) || !is_file(GESHI_DIR.$lang.".php"))
	  $lang = "whitespace";

	$geshi = new GeSHi(
		   $doc->getElementsByTagName("content")->item(0)->textContent,
		   $lang);

	?>
    <div id="corps" style="text-align: center;">
      <h1><?php echo $doc->getElementsByTagName("title")->item(0)->textContent ?></h1>
      <h2>Posté par <?php $a = $doc->getElementsByTagName("author")->item(0)->textContent; if (empty($a)) echo "<em>un anonyme</em>"; else echo $a; ?>, le <?php echo strftime("%A %e %B %G à %H:%M:%S", $doc->getElementsByTagName("date")->item(0)->textContent); ?></h2>
      <div id="content">
	<?php
	 echo $geshi->parse_code();
	 ?>
      </div>
    </div>
  </body>
</html>
	<?php
	  $view = true;
      }
  }
if (!empty($view))
  exit;
?>
    <header>
      <h1><span>Pommultimédia</span></h1>
      <h2><span>Service de partage de code</span></h2>
    </header>
    <div id="corps">
      <form method="post" action="save.php">
	<fieldset class="paste_form">
	  <label for="title">Titre :</label>
	  <input type="text" maxlength="200" size="50" id="title" name="title"><br><br>

	  <label for="content">Contenu :</label><br>
	  <textarea id="content" name="content"></textarea><br><br>

	  <label for="author">Auteur :</label>
	  <input type="text" maxlength="64" size="35" id="author" name="author">

	  <label for="lang">Langage :</label>
	  <select id="lang" name="lang">
	    <option value=""> Fundamental</option>
<?php

  if ($dh = opendir(GESHI_DIR))
    {
      $lg = array();
      while (($file = readdir($dh)) !== false)
	{
	  if (is_file(GESHI_DIR.$file))
	    $lg[] = substr($file, 0, -4);
	}
      closedir($dh);

      sort($lg);

      foreach ($lg as $l)
	echo "<option> ".$l."</option>\n";
    }

?>
	  </select>

	  <input type="submit" value="Envoyer">
	</fieldset>
      </form>
    </div>
  </body>
</html>
