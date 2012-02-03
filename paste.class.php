<?php

require_once("common.php");

class Paste
{
  var $filename;
  var $fileref = NULL;
  var $title;
  var $language;
  var $author;
  var $date;
  var $content;
  var $ip;
  var $ref = NULL;
  var $hash;
  var $crypt = NULL;
  var $answers = array();

  /**
   * Create a new paste or open a existing file
   */
  function __construct($filename=NULL)
  {
    if (!empty($filename))
    {
      $this->filename = Paste::get_path($filename);

      if ($this->load())
        $this->fileref = $filename;
    }
  }

  static function cxor($msg, $cle)
  {
    $cle = hash("whirlpool", $cle);

    $xor = NULL;
    for($i = 0; $i < strlen($msg); $i++)
      $xor .= substr($msg,$i,1) ^ substr($cle, $i % strlen($cle), 1);
    return $xor;
  }

  function crypt($key)
  {
    if (!empty($this->crypt))
      $this->crypt = Paste::cxor($this->crypt, $key);
    else
      $this->crypt = Paste::cxor(sha1($key), $key);

    if ($this->crypt == sha1($key))
      $this->content = Paste::cxor(base64_decode($this->content), $key);
    else
      $this->content = base64_encode(Paste::cxor($this->content, $key));
  }

  /**
   * Load or reload the file and set attributes
   */
  function load()
  {
    if (is_file($this->filename))
    {
      $doc = new DOMDocument();
      $doc->load($this->filename);

      $this->title = utf8_decode(
        $doc->getElementsByTagName("title")->item(0)->textContent);
      $this->language = strtolower(
        $doc->getElementsByTagName("language")->item(0)->textContent);
      $this->date = $doc->getElementsByTagName("date")->item(0)->textContent;
      $this->author = utf8_decode(
        $doc->getElementsByTagName("author")->item(0)->textContent);
      $this->content = $doc->getElementsByTagName("content")->item(0)->textContent;
      if ($doc->getElementsByTagName("ref")->length > 0)
        $this->ref = $doc->getElementsByTagName("ref")->item(0)->textContent;
      else
        $this->ref = NULL;
      $this->ip = $doc->getElementsByTagName("ip")->item(0)->textContent;
      if ($doc->getElementsByTagName("hash")->length > 0)
        $this->hash = $doc->getElementsByTagName("hash")->item(0)->textContent;
      else
        $this->hash = NULL;

      if ($doc->getElementsByTagName("crypt")->length > 0)
        $this->crypt = base64_decode($doc->getElementsByTagName("crypt")->item(0)->textContent);
      else
        $this->crypt = NULL;

      for ($i = 0; $i < $doc->getElementsByTagName("answer")->length; $i++)
        $this->answers[] = $doc->getElementsByTagName("answer")->item($i)->textContent;

      //Check the lang exists
      if (empty($this->language) || !is_file(GESHI_DIR.$this->language.".php"))
        $this->language = "text";

      return TRUE;
    }
    else
      return FALSE;
  }

  static function get_path($filename)
  {
    return DESTINATION . "/" . $filename . ".xml";
  }

  static function speed_cmp($filename, $hash)
  {
    if (is_file($filename))
    {
      $doc = new DOMDocument();
      $doc->load($filename);

      return ($hash == $doc->getElementsByTagName("hash")->item(0)->textContent);
    }
    else
      return FALSE;    
  }

  /**
   * Save the current paste
   */
  function save($filename=NULL)
  {
    $this->hash = base64_encode(sha1($this->content, true));

    if (empty($filename))
    {
      $i = 0;
      do
      {
        $filename = substr(
        str_replace("+", "",
	  str_replace("/", "",
	    $this->hash)), $i++, NB_CHAR);
      }
      //If the file already exists, find another name if the content is different
      while(is_file(Paste::get_path($filename))
            && Paste::speed_cmp(Paste::get_path($filename), $hash));
    }
    $this->filename = $filename;

    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput   = true;

    $xml_paste = $xml->createElement("paste");

    $xml_paste->appendChild(
        $xml->createElement("title", $this->title));
    $xml_paste->appendChild(
        $xml->createElement("author", $this->author));
    $xml_paste->appendChild(
        $xml->createElement("language", $this->language));
    $xml_paste->appendChild(
        $xml->createElement("date", $this->date));
    $xml_paste->appendChild(
        $xml->createElement("ip", $this->ip));
    $xml_paste->appendChild(
        $xml->createElement("content", $this->content));

    if (!empty($this->crypt))
      $xml_paste->appendChild(
        $xml->createElement("crypt", base64_encode($this->crypt)));
    if (!empty($this->ref))
    {
      //Also indicate in the parent file
      $parent = new Paste($this->ref);

      //Does the parent exist?
      if ($parent->load())
      {
        $xml_paste->appendChild(
          $xml->createElement("ref", $this->ref));

        if ($parent->add_answer($this->filename))
          $parent->save();
      }
    }

    foreach ($this->answers as $a)
      $xml_paste->appendChild(
        $xml->createElement("answer", $a));

    $xml_paste->appendChild(
        $xml->createElement("hash", $this->hash));

    $xml->appendChild($xml_paste);

    if ($xml->save(Paste::get_path($this->filename)))
      return $this->filename;
    else
    {
      die ("Sorry, an error occured while saving the file. Please try again later.");
      return FALSE;
    }
  }

  /**
   * Set attributes from an dictionnary like _POST
   */
  function create($dict)
  {
    $this->title = $dict["title"];
    $this->author = $dict["author"];
    $this->language = $dict["lang"];
    $this->date = time();
    $this->ip = $_SERVER["REMOTE_ADDR"];
    if (isset($dict["ref"]))
      $this->ref = $dict["ref"];

    //TODO: allow uploading file
    $this->content = $dict["content"];

    if (!empty($dict["crypt"]))
      $this->crypt($dict["crypt"]);
  }

  /**
   * Generate a subtitle
   */
  function get_subtitle()
  {
    if (empty($this->author))
      $author = "<em>un inconnu</em>";
    else
      $author = htmlentities($this->author);

    return "Posté par ".$author.", le ".strftime("%A %e %B %G à %H:%M:%S",
                                                 $this->date);
  }

  /**
   * Get the ref HTML part of the paste
   */
  function get_ref($is_diff)
  {
    if (!empty($this->ref))
    {
      if (empty($is_diff))
        return '<a href="/?'.$this->ref.'">Voir l\'original</a> '.
        '<a href="/?'.$this->fileref.':'.$this->ref.'">Voir la différence</a>';
      else
        return '<a href="/?'.$this->fileref.'">Cacher les différences</a> ';
    }
    else
      return "";
  }

  function get_gen()
  {
    if (!empty($_POST["passwd"]))
      $passwd = "=".$_POST["passwd"];
    else
      $passwd = "";
    $raw = '<a href="/raw.php?'.$this->fileref.$passwd.'">Raw</a> ';

    if ($this->language == "latex")
      return $raw.'<a href="/gen.php?'.$this->fileref.$passwd.'">Générer le document</a> ';
    else
      return $raw;
  }

  /**
   * Get the parsed code
   */
  function get_code()
  {
    require_once("geshi/geshi.php");

    $geshi = new GeSHi($this->content, $this->language);
    $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 5);

    return $geshi->parse_code();
  }

  /**
   * Get the parsed code with diff
   */
  function get_diff($diff)
  {
    require_once("geshi/geshi.php");
    require_once("simplediff.php");

    $geshi = new GeSHi(htmlDiff($diff->content, $this->content),
                       $this->language);

    return str_replace("&lt;ins&gt;", "<ins>",
           str_replace("&lt;/ins&gt;", "</ins>",
           str_replace("&lt;del&gt;", "<del>",
           str_replace("&lt;/del&gt;", "</del>",
                       $geshi->parse_code()))));
  }

  function add_answer($ref)
  {
    if (!in_array($ref, $this->answers) && $ref != $this->fileref)
    {
      $this->answers[] = $ref;
      return true;
    }
    else
      return false;
  }

  function show_answers()
  {
    $nb = count($this->answers);
    if ($nb > 0)
    {
      $ret = '<div id="res">Des réponses à ce paste ont été publiées : <ul>';
      foreach($this->answers as $a)
        $ret .= '<li><a href="?'.$a.'">'.$a.'</a></li>';
      return $ret.'</ul></div>';
    }
  }

  function export_to_file($fileto)
  {
    file_put_contents($fileto, $this->content);
  }
}

?>