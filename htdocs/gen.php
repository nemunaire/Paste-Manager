<?php
require_once("../common.php");

function generate_latex($filein, $content)
{
  $fileout = $filein.".pdf";
  $filein = $filein.".tex";

  if (is_file($fileout))
    return $fileout;

  if (!preg_match("#\\begin{document}#ui", $content))
    $content = "\documentclass[10pt]{report}

\usepackage[utf8x]{inputenc}
\usepackage[frenchb]{babel}
\usepackage{ucs}
\usepackage{amsmath}
\usepackage{amsfonts}
\usepackage{amssymb}
\usepackage{eurosym}
\usepackage{enumerate}
\usepackage{hyperref}
\usepackage{listings}
\usepackage{color}

\definecolor{dkgreen}{rgb}{0,0.6,0}
\definecolor{gray}{rgb}{0.5,0.5,0.5}
\definecolor{mauve}{rgb}{0.58,0,0.82}

\lstset{language=C++,keywordstyle=\color{blue},stringstyle=\color{mauve}}

\begin{document}".$content."\end{document}";

  file_put_contents($filein, $content);

  print system("pdflatex -halt-on-error -output-directory ../gen ".$filein, $ret);
  if ($ret == 0)
    return "../gen/".basename(str_replace(".tex", "", $filein)).".pdf";
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
    <link href="favicon.ico" type="image/x-icon" rel="shortcut icon">
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
          $t = $_POST["passwd"];

        $paste->crypt($t);

        if ($paste->crypt != sha1($t))
          die ("Bad password");
      }

      $filename = "../gen/".$paste->fileref;

      ob_start();
      if (strtolower($paste->language) == "latex")
        $f = generate_latex($filename, $paste->content);
      else if (strtolower($paste->language) == "html")
        die ($paste->content);
      else
        print "Je ne sais pas compiler ce code source :(";
      $log = ob_get_clean();
      ob_end_clean();
      
      if (isset($f) && is_file($f))
      {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=\"".basename($paste->title).substr($f, count($f)-5, 4)."\"");
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($f));
        readfile($f);
      }
      else
        die(nl2br($log));
    }
  }
}
?>