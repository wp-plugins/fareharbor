<?
  /*
    Plugin Name: FareHarbor Reservation Calendars
    Plugin URI: https://fareharbor.com/help/setup/wordpress-plugin/
    Description: Adds shortcodes for adding FareHarbor embeds to your site
    Version: 1.1
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
  DEFINE("FH_CLASS", "");
  DEFINE("FH_ID", "");
  
  DEFINE("FH_FULL_ITEMS", "no");
  DEFINE("FH_API_VIEW", "items");
  DEFINE("FH_API_VIEW_ITEM", "");
  DEFINE("FH_API_VIEW_AVAILABILITY", "");


  // [fareharbor] shortcode
  // ---------------------------------------------

  function fareharbor_handler($incomingfrompost) {

    // Preprocess attributes returned from post. Trim whitespace.

    $incomingfrompost = array_map('trim', $incomingfrompost);

    // Strip smart quotes, because WordPress returns them as part of the value if the shortcode was set up using them.

    $incomingfrompost = str_replace(
      array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", chr(145), chr(146), chr(147), chr(148)),
      array('', '', '', '', '', '', '', ''),
    $incomingfrompost);

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
    
    // Clean up item IDs: strip spaces and trailing commas
  
    $incomingfrompost["items"] = str_replace(" ", "", $incomingfrompost["items"]);
    $incomingfrompost["items"] = rtrim($incomingfrompost["items"], ",");

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
    
    // Preprocess attributes returned from post. Trim whitespace.

    $attributes = array_map('trim', $attributes);

    // Strip smart quotes, because WordPress returns them as part of the value if the shortcode was set up using them.

    $attributes = str_replace(
      array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", chr(145), chr(146), chr(147), chr(148)),
      array('', '', '', '', '', '', '', ''),
    $attributes);
    
    // Process options and assign defaults if needed

  	$attrs = shortcode_atts(array(
      "shortname" => FH_SHORTNAME,
      "asn" => FH_ASN,
      "asn_ref" => FH_ASN_REF,
      "ref" => FH_REF,
      "items" => FH_ITEMS,
      "lightframe" => FH_LIGHTFRAME,

      "class" => FH_CLASS,
      "id" => FH_ID,

      "full_items" => FH_FULL_ITEMS,

      "view" => FH_API_VIEW,
      "view_item" => FH_API_VIEW_ITEM,
      "view_availability" => FH_API_VIEW_AVAILABILITY
  	), $attributes);

    // Clean up item IDs: strip spaces and trailing commas
  
    $attrs["items"] = str_replace(" ", "", $attrs["items"]);
    $attrs["items"] = rtrim($attrs["items"], ",");
  
    $attrs["view_item"] = rtrim($attrs["view_item"], ",");
  	
    $output = '';

    if ( empty( $attrs["shortname"] ) ) {
  
      $output .= '<p>Please provide a FareHarbor shortname. (Format: <code>shortname=yourshortname</code>)</p>';
      
    } elseif ( empty( $attrs["view_item"] ) && !empty( $attrs["view_availability"] ) ) {  

        echo '<p>Please provide <code>view_item</code> if using <code>view_availability</code.</p>';

    } else {
  
      // Fallback URL
      // ---------------------------------------------
      
      $fallback_url = 'https://fareharbor.com/';
      $fallback_url .= $attrs["shortname"] . '/items/';

      if( !empty( $attrs["items"] ) ) {
        
        // If filtering but just to one item, link to it

        $fallback_items = explode(',', $attrs["items"]);
        
        if( count($fallback_items) == 1 ) {
          $fallback_url .= $fallback_items[0] . '/';
          
          if( $attrs["full_items"] == 'no' ) {
            $fallback_url .= 'calendar/';
          }
        }

      } else {

        if( $attrs["view"] == 'all-availability' ) {
          $fallback_url .= 'calendar/';
        }
  
        if( !empty( $attrs["view_item"] ) ) {
          $fallback_url .= $attrs["view_item"] . '/';
          
          if( $attrs["full_items"] == 'no' && empty( $attrs["view_availability"] ) ) {
            $fallback_url .= 'calendar/';
          }
        }
        
        if( !empty( $attrs["view_availability"] ) ) {
          $fallback_url .= 'availability/' . $attrs["view_availability"] . '/book/';
        }

      }

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
      
      if( !empty( $fallback_url_query_string ) ) {
        $fallback_url .= '?';
        $fallback_url .= http_build_query($fallback_url_query_string);
      }

      // $lightframe_options array, to be JSONified for Lightframe API call
      // ---------------------------------------------
      
      $lightframe_options["shortname"] = $attrs["shortname"];
      
      // We should still set a default for full_items, but avoid writing it in if it's just a 'no'

      if ( $attrs["full_items"] != 'no' ) {  
        $lightframe_options["fullItems"] = $attrs["full_items"];
      }
      
      if ( !empty( $attrs["asn"] ) ) {      
        $lightframe_options["asn"] = $attrs["asn"];
        
        if( !empty( $attrs["asn_ref"] ) ) {
          $lightframe_options["asnRef"] = $attrs["asn_ref"];
        }
      }
      
      if ( !empty( $attrs["ref"] ) ) {
        $lightframe_options["ref"] = $attrs["ref"];
      }

      if ( !empty( $attrs["items"] ) ) {
        $lightframe_options["items"] = array($attrs["items"]); // Put these in an array so it gets brackets
      }
      
      // If the view is a string type, just write it in

      if ($attrs["view"] == 'items' || $attrs["view"] == 'all-availability') {
        $lightframe_options["view"] = $attrs["view"];
      }
      
      // If the view is not a string type, you need to pass just view_item= OR view_item= and view_availability= 

      if( !empty( $attrs["view_item"] ) && empty( $attrs["view_availability"] )) {
        $lightframe_options["view"] = array( 'item' => $attrs["view_item"] );
      }
      
      if( !empty( $attrs["view_item"] ) && !empty( $attrs["view_availability"] )) {
        $lightframe_options["view"] = array( 'item' => $attrs["view_item"], 'availability' => $attrs["view_availability"] );
      }

      // Put it all together now
      // ---------------------------------------------

    	$output .= '<a href="' . $fallback_url . '" ';
    	
      if ( !empty( $attrs["class"] ) ) {
      	$output .= 'class="' . $attrs["class"] .'" ';
      }
      
      if ( !empty( $attrs["id"] ) ) {
      	$output .= 'id="' . $attrs["id"] .'" ';
      }
    
      $output .= 'onclick="FH.open(' . str_replace('"',"'", json_encode($lightframe_options)) . '); return false;">';
      
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