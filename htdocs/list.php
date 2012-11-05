<?php

require_once("../common.php");

$srch_title = $srch_author = $srch_lang = "";
if (!empty($_GET["title"]))
  $srch_title = $_GET["title"];
if (!empty($_GET["author"]))
  $srch_author = $_GET["author"];
if (!empty($_GET["lang"]))
  $srch_lang = $_GET["lang"];

if ($dh = opendir(DESTINATION))
{
  $list = array();
  while (($file = readdir($dh)) !== false)
    $list[] = array(filemtime(DESTINATION.$file), $file);
  closedir($dh);

  array_multisort($list, SORT_DESC);
?>
      <h1>Dernières publications</h1>
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

           if ($i > 23)
           {
             $GT = $GA = $GL = "";
             if (!empty($srch_title)) $GT = "&amp;title=".$srch;
             if (!empty($srch_author)) $GA = "&amp;author=".$srch;
             if (!empty($srch_lang)) $GL = "&amp;lang=".$srch;
             print '<li><a href="./?s='.(intval($_GET["s"])+23).$GT.$GA.$GL.'#list">Plus anciens ...</a></li>';
             break;
           }

           if (preg_match("#^([a-zA-Z0-9]{".RGXP_NB."}).xml$#", $f[1], $fout))
           {
             $paste = new Paste($fout[1]);
             if ($paste->is_private ()
                 || !preg_match("#".$srch_title."#i", $paste->title)
                 || !preg_match("#".$srch_author."#i", $paste->author)
                 || !preg_match("#".$srch_lang."#i", $paste->language))
               continue;

             if (empty($paste->title))
               $title = "Sans titre";
             else
               $title = htmlentities($paste->title);

             if (empty($paste->author))
               $author = "<em>un anonyme</em>";
             else
               $author = htmlentities($paste->author);

             print '<li><a href="./?'.$paste->fileref.'">'.$title."</a> par ".$author.", le ".date("d/m/Y H:i:s", $paste->date)."</li>";
             $i++;
           }
         }
        ?>
      </ul>
<?php
}
?>
