<?php
require_once("../common.php");

function generate_latex($filein)
{
  print system("pdflatex -halt-on-error -output-directory ../gen ".$filein, $ret);
  if ($ret == 0)
    return "../gen/".basename(str_replace(".tex", ".pdf", $filein));
  else
    return NULL;
}

foreach ($_GET as $k => $t)
  {
    if (preg_match("#^([a-zA-Z0-9]{".RGXP_NB."})(:([a-zA-Z0-9]{".RGXP_NB."}))?$#", $k, $kout)
        && is_file(Paste::get_path($kout[1])))
    {
      $paste = new Paste($kout[1]);

      if (!empty($paste->crypt) && empty($_POST["passwd"]) && empty($t))
      {
?>
<!doctype html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>.: Pommultimédia - Paste :.</title>
    <link href="style.css" rel="stylesheet" type="text/css">
    <link href="favicon.ico" type="image/x-icon" rel="shortcut icon"/>
  </head>
  <body>
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
    </div>
  </body>
</html>
<?php
    }
    else
    {
      if (!empty($paste->crypt))
      {
        if (!empty($_POST["passwd"]))
          $paste->crypt($_POST["passwd"]);
        else
          $paste->crypt($t);
      }

      header("Content-Type: text/html; charset=UTF-8");
      echo ($paste->content);
    }
  }
}
?>