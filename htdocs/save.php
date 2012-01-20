<?php
require_once("../common.php");

if (!empty($_POST["content"]))
  {
    $paste = new Paste();
    $paste->create($_POST);

    header("Location: /?".$paste->save());
    exit;
  }

header("Location: /");
?>