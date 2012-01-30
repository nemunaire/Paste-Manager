<!doctype html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>.: Pommultimédia - Paste :.</title>
    <link href="style.css" rel="stylesheet" type="text/css">
    <link href="favicon.ico" type="image/x-icon" rel="shortcut icon"/>
  </head>
  <body>
<a href="http://github.com/nemunaire/Paste-Manager"><img style="position: absolute; top: 0; right: 0; border: 0;" src="https://a248.e.akamai.net/assets.github.com/img/4c7dc970b89fd04b81c8e221ba88ff99a06c6b61/687474703a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f77686974655f6666666666662e706e67" alt="Fork me on GitHub"></a>
<?php
require_once("../common.php");

$view = 0;
foreach ($_GET as $k => $t)
  {
    if (preg_match("#^([a-zA-Z0-9]{".RGXP_NB."})(:([a-zA-Z0-9]{".RGXP_NB."}))?$#", $k, $kout)
	&& is_file(Paste::get_path($kout[1])))
      {
        $paste = new Paste($kout[1]);

        if (!empty($paste->crypt) && empty($_POST["passwd"]))
        {
          ?>
    <div id="corps" style="text-align: center;">
      <h1>
       <?php echo htmlentities($paste->title); ?>
      </h1>
      <h2><?php echo $paste->get_subtitle(); ?></h2>
      <div>
        <form method="post" action="?<?php echo $kout[1]; ?>">
	  <fieldset class="paste_form">
	    <label for="title">Mot de passe :</label>
	    <input type="password" size="42" id="passwd" name="passwd">
            <br><br>
	    <input type="submit" value="Voir le texte">
	  </fieldset>
        </form>
          <?php
        }
        else
        {
          if (!empty($paste->crypt))
            $paste->crypt($_POST["passwd"]);

          if (!empty($kout[3]) && is_file(Paste::get_path($kout[3])))
            $diff = new Paste($kout[3]);
	?>
    <div id="corps" style="text-align: center;">
      <h1>
       <?php echo htmlentities($paste->title); ?>
      </h1>
      <h2><?php echo $paste->get_subtitle(); ?></h2>
      <div id="content">
       <div class="answer">
	 <a href="/?a=<?php echo $kout[1]; ?>">Répondre</a>
         <?php echo $paste->get_ref(isset($diff)); ?>
       </div>
	<?php
           if (isset($diff))
             echo $paste->get_diff($diff);
           else
             echo $paste->get_code();
          echo $paste->show_answers();
        }
        ?>
      </div>
    </div>
  </body>
</html>
<?php
	  $view++;
      }
  }

//Don't show the creation part when we show paste
if (!empty($view))
  exit;

//Load answer paste
if (!empty($_GET["a"]) && preg_match("#^[a-zA-Z0-9]{".RGXP_NB."}$#", $_GET["a"])
    && is_file(Paste::get_path($k = $_GET["a"])))
  $paste = new Paste($k);
else
  $paste = new Paste();
?>
    <header>
      <h1><span>Pommultimédia</span></h1>
      <h2><span>Service de partage de code</span></h2>
    </header>
    <div id="corps">
      <form method="post" action="save.php">
	<fieldset class="paste_form">
	  <label for="title">Titre :</label>
	  <input type="text" size="42" id="title" name="title" value="<?php
            echo $paste->title;
          ?>">
	  <label for="author">Auteur :</label>
	  <input type="text" maxlength="64" size="25" id="author" name="author">
          <br><br>

	  <label for="content">Contenu :</label><br>
	  <textarea id="content" name="content"><?php
            echo htmlentities(utf8_decode($paste->content));
          ?></textarea><br><br>

          <label for="crypt" style="font-style: italic;">Mot de passe :</label>
	  <input type="text" maxlength="64" size="25" id="crypt" name="crypt">

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
	  if (!empty($paste->language) && $paste->language == $l)
	    echo "<option selected=\"selected\"> ".ucfirst($l)."</option>\n";
	  else
	    echo "<option> ".ucfirst($l)."</option>\n";
	}
    }

?>
	  </select>
<?php
	  if (!empty($paste->fileref))
	    echo '<input type="hidden" name="ref" value="'.$paste->fileref.'">';
?>
	  <input type="submit" value="Envoyer">
	</fieldset>
      </form>
<?php
  include("list.php");
?>
    </div>
  </body>
</html>
