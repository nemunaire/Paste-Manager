<?php

// The directory where code will be stored
define("DESTINATION", __dir__."/files/");
if (!is_writable(DESTINATION))
  die ("Destination folder not writable");

// Path to the GeSHi languages
define("GESHI_DIR", __dir__."/geshi/geshi/");


// The size of the generated identifier
define("NB_CHAR", 5);

// If you increase NB_CHAR, in order to help review the old codes published, you
// can adjust the minimum
define("ALLOW_NB_MIN", 5);

// The adress of this service
define("HTTP_URL", "paste.p0m.fr");



/*********************************************
* Don't make any change under this comment ! *
*********************************************/
if (ALLOW_NB_MIN != NB_CHAR)
  {
    if (ALLOW_NB_MIN > NB_CHAR)
      define("RGXP_NB", NB_CHAR.",".ALLOW_NB_MIN);
    else
      define("RGXP_NB", ALLOW_NB_MIN.",".NB_CHAR);
  }
else
  define("RGXP_NB", NB_CHAR);

require_once("paste.class.php");

?>