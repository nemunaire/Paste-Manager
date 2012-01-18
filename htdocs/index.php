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
    if (preg_match("#^[a-zA-Z0-9]{".RGXP_NB."}$#", $k)
	&& is_file(DESTINATION . "/" . $k . ".xml"))
      {
	require_once("../geshi/geshi.php");

	$doc = new DOMDocument();
	$doc->load(DESTINATION . "/" . $k . ".xml");

	$lang = strtolower($doc->getElementsByTagName("language")->item(0)->textContent);
	if (empty($lang) || !is_file(GESHI_DIR.$lang.".php"))
	  $lang = "text";

	$geshi = new GeSHi(
		   $doc->getElementsByTagName("content")->item(0)->textContent,
		   $lang);

	$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 5);

	?>
    <div id="corps" style="text-align: center;">
      <h1>
       <?php echo htmlentities(utf8_decode($doc->getElementsByTagName("title")->item(0)->textContent)); ?>
      </h1>
      <h2>
        Posté par <?php
               $a = $doc->getElementsByTagName("author")->item(0)->textContent;
	       if (empty($a))
		 echo "<em>un anonyme</em>";
	       else
		 echo htmlentities(utf8_decode($a));
	  ?>, le <?php
	       echo strftime("%A %e %B %G à %H:%M:%S",
		     $doc->getElementsByTagName("date")->item(0)->textContent);
	  ?></h2>
      <div id="content">
       <div class="answer">
	 <a href="/?a=<?php echo $k; ?>">Répondre</a>
	<?php
	 $ref = $doc->getElementsByTagName("ref");
	 if ($ref->length > 0)
	   {
	     $r = $ref->item(0)->textContent;
	     echo '<a href="/?'.$r.'">Voir l\'original</a>';
	   }
        ?>
       </div>
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
	  <input type="text" maxlength="200" size="50" id="title" name="title">
          <br><br>

	  <label for="content">Contenu :</label><br>
	  <textarea id="content" name="content"><?php
  if (!empty($_GET["a"]) && preg_match("#^[a-zA-Z0-9]{".RGXP_NB."}$#", $_GET["a"])
      && is_file(DESTINATION . "/" . ($k = $_GET["a"]) . ".xml"))
    {
      $doc = new DOMDocument();
      $doc->load(DESTINATION . "/" . $k . ".xml");

      echo htmlentities($doc->getElementsByTagName("content")->item(0)->textContent);
      $language = strtolower($doc->getElementsByTagName("language")->item(0)->textContent);
      $ref = $k;
    }
          ?></textarea><br><br>

	  <label for="author">Auteur :</label>
	  <input type="text" maxlength="64" size="35" id="author" name="author">

	  <label for="lang">Langage :</label>
	  <select id="lang" name="lang">
	    <option value=""> Text</option>
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
	{
	  if (isset($language) && $language == $l)
	    echo "<option selected=\"selected\"> ".ucfirst($l)."</option>\n";
	  else
	    echo "<option> ".ucfirst($l)."</option>\n";
	}
    }

?>
	  </select>
<?php
	  if (!empty($ref))
	    echo '<input type="hidden" name="ref" value="'.$ref.'">';
?>
	  <input type="submit" value="Envoyer">
	</fieldset>
      </form>
    </div>
  </body>
</html>
