<?php
/**
 * Theme related functions. 
 *
 */

/**
 * Get title for the webpage by concatenating page specific title with site-wide title.
 *
 * @param string $title for this page.
 * @return string/null wether the favicon is defined or not.
 */
function get_title($title) {
  global $prot;
  return $title . (isset($prot['title_append']) ? $prot['title_append'] : null);
}


/**
 * Create a navigation bar / menu for the site.
 *
 * @param string $menu for the navigation bar.
 * @return string as the html for the menu.
 */
/*
function get_navbar($menu) {
  $html = "<nav>\n<ul class='{$menu['class']}'>\n";
  foreach($menu['items'] as $item) {
    $selected = $menu['callback_selected']($item['url']) ? " class='selected' " : null;
    $html .= "<li{$selected}><a href='{$item['url']}' title='{$item['title']}'>{$item['text']}</a></li>\n";
  }
  $html .= "</ul>\n</nav>\n";
  return $html;
}
*/
/**
 * Create a navigation bar / menu, with submenu.
 *
 * @param string $menu for the navigation bar.
 * @return string as the html for the menu.
 */
function get_navbar($menu) {
  // Keep default options in an array and merge with incoming options that can override the defaults.
  $default = array(
    'id'      => null,
    'class'   => null,
    'wrapper' => 'nav',
  );
  $menu = array_replace_recursive($default, $menu);
 
 
  // Create the ul li menu from the array, use an anonomous recursive function that returns an array of values.
  $create_menu = function($items, $callback) use (&$create_menu) {
    $html = null;
    $hasItemIsSelected = false;
 
    foreach($items as $item) {
 
      // has submenu, call recursivly and keep track on if the submenu has a selected item in it.
      $submenu        = null;
      $selectedParent = null;
   
      if(isset($item['submenu'])) 
      {
        list($submenu, $selectedParent) = $create_menu($item['submenu']['items'], $callback);
        $selectedParent = $selectedParent ? " selected-parent" : null;
     
      }
      // Check if the current menuitem is selected
      $selected = $callback($item['url']) ? 'selected' : null;
      if($selected) {
        $hasItemIsSelected = true;
      }
      $selected = ($selected || $selectedParent) ? " class='${selected}{$selectedParent}' " : null;      
      $html .= "\n<li{$selected}><a href='{$item['url']}' title='{$item['title']}'>{$item['text']}</a>{$submenu}</li>\n";
    }
 
    return array("\n<ul>$html</ul>\n", $hasItemIsSelected);
  };
 
  // Call the anonomous function to create the menu, and submenues if any.
  list($html, $ignore) = $create_menu($menu['items'], $menu['callback']);
 
 
  // Set the id & class element, only if it exists in the menu-array
  $id      = isset($menu['id'])    ? " id='{$menu['id']}'"       : null;
  $class   = isset($menu['class']) ? " class='{$menu['class']}'" : null;
  $wrapper = $menu['wrapper'];
 
  return "\n<{$wrapper}{$id}{$class}>{$html}</{$wrapper}>\n";
}