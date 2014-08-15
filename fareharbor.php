<?
  /*
    Plugin Name: FareHarbor Reservation Calendars
    Plugin URI: https://fareharbor.com/help/setup/wordpress-plugin/
    Description: Adds shortcodes for adding FareHarbor embeds to your site
    Version: 0.5
    Author: FareHarbor
    Author URI: https://fareharbor.com
  */
  
  defined('ABSPATH') or die("What are you looking at?");
  
  add_shortcode("fareharbor", "fareharbor_handler");
  
  DEFINE("FH_SHORTNAME", "");
  DEFINE("FH_EMBED_TYPE", "calendar-small");
  DEFINE("FH_ITEMS", "");
  DEFINE("FH_LIGHTFRAME", "yes");
  DEFINE("FH_ASN", "");
  DEFINE("FH_REF", "");

  function fareharbor_handler($incomingfrompost) {

    // Process options and assign defaults if needed
    
    $incomingfrompost = shortcode_atts(array(
      "shortname" => FH_SHORTNAME,
      "type" => FH_EMBED_TYPE,
      "items" => FH_ITEMS,
      "lightframe" => FH_LIGHTFRAME,
      "asn" => FH_ASN,
      "ref" => FH_REF
    ), $incomingfrompost);


    $fh_final_output = fareharbor_function($incomingfrompost);
  
    //send back text to replace shortcode in post
    return $fh_final_output;
  }

  function fareharbor_function($fh_options) {
  
    $fh_output = '';  
  
    // Bail if a shortname isn't provided

    if ( empty( $fh_options["shortname"] ) ) {
  
      $fh_output .= '<p>Please enter a FareHarbor shortname.</p>';
      
    } else {
    
      $fh_output = '<script src="https://fareharbor.com/embeds/script/';

      // Types: Clean up "small" and "large" options, otherwise use passed type

      switch ( $fh_options["type"] ) {
        case "small":
          $fh_output .= "calendar-small";
          break;
        case "large":
          $fh_output .= "calendar";
          break;
        default:
          $fh_output .= $fh_options["type"];
      }
      
      $fh_output .= '/';

      // Shortname

      $fh_output .= $fh_options["shortname"] . '/';
  
      // Items, if any were included
      
      if ( !empty( $fh_options["items"] ) ) {
        $fh_output .= 'items/' . $fh_options["items"] . '/';
      }
      
      // Build query string of options. "lightframe" is always included, with either yes or no.

      $fh_output .= '?';

      $fh_query_string_options = array('lightframe' => $fh_options["lightframe"]);

      if ( !empty( $fh_options["asn"] ) ) {      
        $fh_query_string_options['asn'] = $fh_options["asn"];
      }
      
      if ( !empty( $fh_options["ref"] ) ) {
        $fh_query_string_options['asn'] = $fh_options["asn"];
      }
      
      $fh_output .= http_build_query($fh_query_string_options);

      
      $fh_output .= '"></script>';
    }
  
    return $fh_output;
  }
?>