<?
  /*
    Plugin Name: FareHarbor Reservation Calendars
    Plugin URI: https://fareharbor.com/help/setup/wordpress-plugin/
    Description: Adds shortcodes for adding FareHarbor embeds to your site
    Version: 0.7
    Author: FareHarbor
    Author URI: https://fareharbor.com
  */
  
  defined('ABSPATH') or die("What are you looking at?");
  
  add_shortcode("fareharbor", "fareharbor_handler");
  add_shortcode("lightframe", "lightframe_api_handler");
  
  // Defaults
  
  DEFINE("FH_SHORTNAME", "");
  DEFINE("FH_EMBED_TYPE", "calendar-small");
  DEFINE("FH_ITEMS", "");
  DEFINE("FH_LIGHTFRAME", "yes");
  DEFINE("FH_ASN", "");
  DEFINE("FH_ASN_REF", "");
  DEFINE("FH_REF", "");
  
  DEFINE("FH_API_VIEW", "items");
  DEFINE("FH_API_VIEW_ITEM", "");
  DEFINE("FH_API_VIEW_AVAILABILITY", "");

  // [fareharbor] shortcode
  // ---------------------------------------------

  function fareharbor_handler($incomingfrompost) {

    // Process options and assign defaults if needed
    
    $incomingfrompost = shortcode_atts(array(
      "shortname" => FH_SHORTNAME,
      "type" => FH_EMBED_TYPE,
      "items" => FH_ITEMS,
      "lightframe" => FH_LIGHTFRAME,
      "asn" => FH_ASN,
      "asn_ref" => FH_ASN_REF,
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
  
      $fh_output .= '<p>Please provide a FareHarbor shortname. (Format: <code>shortname=yourshortname</code>)</p>';
      
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
        $fh_query_string_options["asn"] = $fh_options["asn"];
        
        if( !empty( $fh_options["asn_ref"] ) ) {
          $fh_query_string_options["asn-ref"] = $fh_options["asn_ref"];
        }
      }
      
      if ( !empty( $fh_options["ref"] ) ) {
        $fh_query_string_options["ref"] = $fh_options["ref"];
      }
      
      $fh_output .= http_build_query($fh_query_string_options);

      
      $fh_output .= '"></script>';
    }
  
    return $fh_output;
  }

  // [lightframe][/lightframe] shortcode
  // ---------------------------------------------
  
  function lightframe_api_handler($attributes, $content = null) {
    
    // Process options and assign defaults if needed

  	$attrs = shortcode_atts(array(
      "shortname" => FH_SHORTNAME,
      "asn" => FH_ASN,
      "asn_ref" => FH_ASN_REF,
      "ref" => FH_REF,
      "items" => FH_ITEMS,
      "lightframe" => FH_LIGHTFRAME,

      "view" => FH_API_VIEW,
      "view_item" => FH_API_VIEW_ITEM,
      "view_availability" => FH_API_VIEW_AVAILABILITY
  	), $attributes);

    if ( empty( $attrs["shortname"] ) ) {
  
      $output .= '<p>Please provide a FareHarbor shortname. (Format: <code>shortname=yourshortname</code>)</p>';
      
    } elseif ( empty( $attrs["view_item"] ) && !empty( $attrs["view_availability"] ) ) {  

        echo '<p>Please provide <code>view_item</code> if using <code>view_availability</code.</p>';

    } else {
  
      // Build fallback url
      
      $fallback_url = 'https://fareharbor.com/';
      $fallback_url .= $attrs["shortname"] . '/?';

      $fallback_url_query_string = array();

      if ( !empty( $attrs["asn"] ) ) {      
        $fallback_url_query_string["asn"] = $attrs["asn"];
        
        if( !empty( $attrs["asn_ref"] ) ) {
          $fallback_url_query_string["asn-ref"] = $attrs["asn_ref"];
        }
      }
      
      if ( !empty( $attrs["ref"] ) ) {
        $fallback_url_query_string["ref"] = $attrs["ref"];
      }
      
      $fallback_url .= http_build_query($fallback_url_query_string);

      // JSON dictonary for Lightframe Javascript API
      
      $lightframe_options = array('shortname' => $attrs["shortname"]);
      
      if ( !empty( $attrs["asn"] ) ) {      
        $lightframe_options["asn"] = $attrs["asn"];
        
        if( !empty( $attrs["asn_ref"] ) ) {
          $lightframe_options["asnRef"] = $attrs["asn_ref"];
        }
      }
      
      if ( !empty( $attrs["ref"] ) ) {
        $lightframe_options["ref"] = $attrs["ref"];
      }
    
      $lightframe_options["items"] = array($attrs["items"]); // Put these in an array so it gets brackets

      // If the view is a string type, just write it in

      if ($attrs["view"] == 'items' || $attrs["view"] == 'all-availabilities') {
        $lightframe_options["view"] = $attrs["view"];
      }
      
      // If the view is not a string type, you need to pass just view_item= OR view_item= and view_availability= 

      if ( !empty( $attrs["view_item"] ) && empty( $attrs["view_availability"] )) {
        $lightframe_options["view"] = array( 'item' => $attrs["view_item"] );
      }
      
      if ( !empty( $attrs["view_item"] ) && !empty( $attrs["view_availability"] )) {
        $lightframe_options["view"] = array( 'item' => $attrs["view_item"], 'availability' => $attrs["view_availability"] );
      }
    
    	$output .= '<a href="' . $fallback_url . '" ';
    
      $output .= 'onclick=\'FH.open(' . json_encode($lightframe_options) . '); return false;\'>';
      
      $output .= do_shortcode( $content ) . '</a>';
    }

  	return $output;
  }
  
  // Add API script to bottom of page
  
  add_action('wp_footer', 'lightframe_api_footer');
  function lightframe_api_footer() {
?>
  <script src="https://fareharbor.com/embeds/api/v1/"></script>
<?
}