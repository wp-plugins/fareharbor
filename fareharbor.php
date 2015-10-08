<?php
  /*
    Plugin Name: FareHarbor Reservation Calendars
    Plugin URI: https://fareharbor.com/help/setup/wordpress-plugin/
    Description: Adds shortcodes for adding FareHarbor embeds to your site
    Version: 2.1
    Author: FareHarbor
    Author URI: https://fareharbor.com
  */
  
  defined('ABSPATH') or die("What are you looking at?");
  
  add_shortcode("fareharbor", "fh_shortcode");
  add_shortcode("lightframe", "lightframe_shortcode");
  add_shortcode("partners", "partners_shortcode");
  
  // Defaults
  
  DEFINE('FH_SHORTNAME', '');
  DEFINE('FH_EMBED_TYPE', 'calendar-small');
  DEFINE('FH_ITEMS', '');
  DEFINE('FH_LIGHTFRAME', 'yes');
  DEFINE('FH_ASN', '');
  DEFINE('FH_ASN_REF', '');
  DEFINE('FH_REF', '');
  DEFINE('FH_CLASS', '');
  DEFINE('FH_ID', '');
  
  DEFINE('FH_FULL_ITEMS', 'no');
  DEFINE('FH_API_VIEW', 'items');
  DEFINE('FH_API_VIEW_ITEM', '');
  DEFINE('FH_API_VIEW_AVAILABILITY', '');

  DEFINE('FH_PARTNERS_INCLUDE', '');
  
  // Process the info returned from a shortcode
  // ---------------------------------------------
  
  function fh_sanitize_csv( $value ) {
    $value = str_replace(" ", "", $value);
    $value =  rtrim($value, ",");
    return $value;
  }

  function fh_process_attrs( $attrs ) {
    if( is_array($attrs) ) {

      // Trim whitespace
  
      $attrs = array_map('trim', $attrs);
  
       // Strip smart quotes, because WordPress returns them as part of the value if the shortcode was set up using them
    
      $attrs = str_replace(
        array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", chr(145), chr(146), chr(147), chr(148)),
        array('', '', '', '', '', '', '', ''),
      $attrs);
      
      // Process options and assign defaults if needed
      
      $attrs = shortcode_atts(array(
        "shortname" => FH_SHORTNAME,
        "items" => FH_ITEMS,
        "asn" => FH_ASN,
        "asn_ref" => FH_ASN_REF,
        "ref" => FH_REF,
        "lightframe" => FH_LIGHTFRAME,
        "full_items" => FH_FULL_ITEMS,
        "sheet" => '',
        
        // [fareharbor] only
        
        "type" => FH_EMBED_TYPE,
        "lightframe" => FH_LIGHTFRAME,
  
        // [lightframe] only
  
        "class" => FH_CLASS,
        "id" => FH_ID,
        "view" => FH_API_VIEW,
        "view_item" => FH_API_VIEW_ITEM,
        "view_availability" => FH_API_VIEW_AVAILABILITY,
    
        // [partners] only
    
        "include" => FH_PARTNERS_INCLUDE
  
      ), $attrs);
      
      // Clean up item IDs and included companies because users can't be trusted
      
      $attrs["items"] = fh_sanitize_csv( $attrs["items"] );
      $attrs["view_item"] = fh_sanitize_csv( $attrs["view_item"] );
      $attrs["include"] = fh_sanitize_csv( $attrs["include"] );
    
      return $attrs;

    }
  }
  
  function fh_url() {
    $env_url = defined('FH_ENVIRONMENT') ? FH_ENVIRONMENT . '.fareharbor.com' : 'fareharbor.com';
    return $env_url;
  }

  // [fareharbor] shortcode
  // ---------------------------------------------

  function fh_shortcode( $attrs ) {
    
    $fh_options = fh_process_attrs( $attrs );
  
    $output = '';  
  
    // Bail if a shortname isn't provided

    if ( empty( $fh_options["shortname"] ) ) {
  
      $output .= '<p>Please provide a FareHarbor shortname. (Format: <code>shortname="yourshortname"</code>)</p>';
      
    } else {
    
      $output = '<script src="https://' . fh_url() . '/embeds/script/';

      // Types: Clean up "small" and "large" options, otherwise use passed type

      switch ( $fh_options["type"] ) {
        case "small":
          $output .= "calendar-small";
          break;
        case "large":
          $output .= "calendar";
          break;
        default:
          $output .= $fh_options["type"];
      }
      
      $output .= '/';

      // Shortname

      $output .= $fh_options["shortname"] . '/';
  
      // Items, if any were included
      
      if ( !empty( $fh_options["items"] ) ) {
        $output .= 'items/' . $fh_options["items"] . '/';
      }
      
      // Build query string of options. "lightframe" is always included, with either yes or no.

      $output .= '?';

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
      
      if ( !empty( $fh_options["sheet"] ) ) {
        $fh_query_string_options["sheet"] = $fh_options["sheet"];
      }
      
      $output .= http_build_query($fh_query_string_options);

      
      $output .= '"></script>';
    }
  
    return $output;
  }

  // [lightframe][/lightframe] shortcode
  // ---------------------------------------------
  
  function lightframe_shortcode( $attrs, $content = null ) {

    $attrs = fh_process_attrs( $attrs );
  	
    $output = '';

    if ( empty( $attrs["shortname"] ) ) {
  
      $output .= '<p>Please provide a FareHarbor shortname. (Format: <code>shortname="yourshortname"</code>)</p>';
      
    } elseif ( empty( $attrs["view_item"] ) && !empty( $attrs["view_availability"] ) ) {  

        echo '<p>Please provide <code>view_item</code> if using <code>view_availability</code.</p>';

    } else {
  
      // Fallback URL
      // ---------------------------------------------
      
      $fallback_url = 'https://' . fh_url() . '/';
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
      
      if ( !empty( $attrs["sheet"] ) ) {
        $fallback_url_query_string["sheet"] = $attrs["sheet"];
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
      
      if ( !empty( $attrs["sheet"] ) ) {
        $lightframe_options["sheet"] = $attrs["sheet"];
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
  
  // [partners] shortcode
  // ---------------------------------------------

  function partners_shortcode( $attrs ) {
    
    $attrs = fh_process_attrs( $attrs );
  
    $output = '';
  
    // Bail if a shortname isn't provided

    if ( empty( $attrs["shortname"] ) ) {
  
      $output .= '<p>Please provide a FareHarbor shortname. (Format: <code>shortname="yourshortname"</code>)</p>';
      
    } else {
    
      $output = '<script src="https://' . fh_url() . '/embeds/script/partners/';
      
      $output .= $attrs["shortname"] . '/';
      
      // Build query string of options

      $output .= '?';
      
      // lightframe is always included

      $fh_query_string_options = array('lightframe' => $attrs["lightframe"]);
      
      // For safety, always set asn, just to the shortname if asn isn't given
      
      $fh_query_string_options["asn"] = !empty( $attrs["asn"] ) ? $attrs["asn"] : $attrs["shortname"];

      if( !empty( $attrs["asn_ref"] ) ) {
        $fh_query_string_options["asn-ref"] = $attrs["asn_ref"];
      }
      
      if ( !empty( $attrs["ref"] ) ) {
        $fh_query_string_options["ref"] = $attrs["ref"];
      }
      
      if ( $attrs["full_items"] != 'no' ) {  
        $fh_query_string_options["full-items"] = $attrs["full_items"];
      }

      if ( !empty( $attrs["include"] ) ) {
        $fh_query_string_options["include"] = $attrs["include"];
      }
      
      $output .= http_build_query($fh_query_string_options);
      
      $output .= '"></script>';
    }
  
    return $output;
  }

  // Add API script to footer
  // ---------------------------------------------
  
  add_action('wp_footer', 'lightframe_api_footer');
  function lightframe_api_footer() {
    echo '<!-- FareHarbor plugin activated --><script src="https://' . fh_url() . '/embeds/api/v1/"></script>';
  }
?>
