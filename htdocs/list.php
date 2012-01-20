      <h1>Dernières publications</h1>
<?php

require_once("../common.php");

if ($dh = opendir(DESTINATION))
{
  $list = array();
  while (($file = readdir($dh)) !== false)
    $list[] = array(filemtime(DESTINATION.$file), $file);
  closedir($dh);

  array_multisort($list, SORT_DESC);
?>
      <ul id="list">
	<?php
         if (empty($_GET["s"]))
           $_GET["s"] = 0;
         $s = intval($_GET["s"]);
         $i = 0;
         foreach($list as $f)
         {
           if ($s > 0)
           {
             $s--;
             continue;
           }

           if ($i++ > 10)
           {
             print '<li><a href="./?s='.(intval($_GET["s"])+10).'#list">Plus anciens ...</a></li>';
             break;
           }

           if (preg_match("#^([a-zA-Z0-9]{".RGXP_NB."}).xml$#", $f[1], $fout))
           {
             $paste = new Paste($fout[1]);

             if (empty($paste->title))
               $title = "Sans titre";
             else
               $title = htmlentities($paste->title);

             if (empty($paste->author))
               $author = "<em>un anonyme</em>";
             else
               $author = htmlentities($paste->author);

             print '<li><a href="./?'.$paste->fileref.'">'.$title."</a> par ".$author.", le ".date("d/m/Y H:i:s", $paste->date)."</li>";

           }
         }
        ?>
      </ul>
<?php
}
?>
