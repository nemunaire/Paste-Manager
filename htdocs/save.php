<?php
require_once("../common.php");

if (!empty($_POST["content"]))
  {
    if (!isset($_POST["title"]))
      $_POST["title"] = "";
    if (!isset($_POST["author"]))
      $_POST["author"] = "";
    if (!isset($_POST["lang"]))
      $_POST["lang"] = "";
    if (!isset($_POST["hide"]))
      $_POST["hide"] = 0;

    $paste = new Paste();
    $paste->create($_POST);

    header("Location: /?".$paste->save());
    exit;
  }

header("Location: /");
?>