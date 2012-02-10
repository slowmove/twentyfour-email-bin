<?php

/**
  Plugin Name: Twentyfour Email Bin
  Plugin URI: http://www.24hr.se/
  Description: Just store all emails submitted in a separate database. Adds an admin view.
  Version: 1.0
  Author: Kristian Erendi & Erik Johansson
  Author URI: http://boyhappy.se
  License: GPL2
 */
/*
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
class TwentyfourEmailBin {

  //plugin db version
  public static $myDbVersion = "0.1";

  function __construct() {

  }

  /**
   * install function, ie create or update the database
   */
  public static function install() {
    global $wpdb;
    $installed_ver = get_option("twentyfourEmailBinDbVersion");
    if ($installed_ver != twentyfourEmailBin::$myDbVersion) {
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      $table_name = $wpdb->prefix . 'emailbin';
      $sql = "CREATE TABLE " . $table_name . " (
              id mediumint(9) NOT NULL AUTO_INCREMENT,
              email varchar(64) NOT NULL,
              createDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              UNIQUE KEY id (id)
              );";
      dbDelta($sql);
      //echo $sql;
      update_option("twentyfourEmailBinDbVersion", twentyfourEmailBin::$myDbVersion);
    }
  }

  /**
   * checks if a database table update is needed
   */
  public static function update() {
    $installed_ver = get_option("twentyfourEmailBinDbVersion");
    if ($installed_ver != twentyfourEmailBin::$myDbVersion) {
      twentyfourEmailBin::install();
    }
  }

  /**
   * Add an email to the db if it has no duplicate already
   *
   * @global  $wpdb
   * @param <type> $email
   */
  public function twentyfourEBinsert($email) {
    $exists = $this->twentyfourEBgetEmail($email);
    if (empty($exists)) {
      global $wpdb;
      $table_name = $wpdb->prefix . 'emailbin';
      $sql = "insert into " . $table_name . " (email) values('" . $email . "');";
      $wpdb->get_results($sql);
    }
  }
  
    /**
   * Delete an email from the db 
   *
   * @global  $wpdb
   * @param <type> $email
   */
  public function twentyfourEBdelete($email) {
    $exists = $this->twentyfourEBgetEmail($email);
    if ($exists) {
      global $wpdb;
      $table_name = $wpdb->prefix . 'emailbin';
      $sql = "delete from " . $table_name . " where email = '" . $email . "';";
      $wpdb->get_results($sql);
    }
  }

  /**
   * If email exists this function will return the whole row
   *
   * @global $wpdb $wpdb
   * @param <type> $email
   * @return <type>
   */
  public function twentyfourEBgetEmail($email) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'emailbin';
    $sql = "select * from " . $table_name . " where email = '" . $email . "';";
    $res = $wpdb->get_results($sql);
    return $res;
  }

  /**
   * Get emails, can be used with offset and limit for pagination
   * To get all emails set ofset to 0 and limit to -1
   *
   * @global $wpdb $wpdb
   * @param <type> $offset
   * @param <type> $limit
   * @return array
   */
  public function twentyfourEBgetEmailList($offset, $limit) {
    if ($limit <= 0) {
      $limit = '';
    } else {
      $limit = ' limit ' . $offset . ',' . $limit . ' ';
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'emailbin';
    $sql = "select * from " . $table_name . " order by id asc " . $limit . " ;";
    return $wpdb->get_results($sql);
  }

  /**
   * Return the count of emails
   *
   * @global $wpdb $wpdb
   * @return <type>
   */
  public function twentyfourEBEmailCount() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'emailbin';
    $sql = "select count(id) from " . $table_name . " ;";
    return $wpdb->get_var($sql);
  }

  /**
   * Print the javascript to the page
   */
  public function twentyfourEBCode() {
    $pluginRoot = plugins_url("", __FILE__);
    $actionFile = $pluginRoot . "/api/emailbin.php";
    $deletionFile = $pluginRoot . "/api/deletemail.php";
    echo '<script type="text/javascript">
  jQuery(document).ready(function(){
    jQuery("#pren").click(function(event) {
      event.preventDefault();
      if( $("#pren-email").val().indexOf("@") == -1){
        alert("Felaktig emailadress");
      } else {
        var dataString = "email="+ jQuery("#pren-email").val();
        //alert(dataString);
        var self = jQuery(this);    var self = jQuery(this);
        if(!self.hasClass("used")){  //continue only if class "used" is not present
          if(dataString==""){
          } else{
            jQuery.ajax({
              type: "POST",
              url: "' . $actionFile . '",
              data: dataString,
              cache: false,
              success: function(html){
                $(".newsletter").fadeOut();
                $(".newsletter").html("Du är nu prenumerant av vårt nyhetsbrev.");
                $(".newsletter").fadeIn();
                self.addClass("used");
              }
            });
          }
          return false;
        }
      }
    });
  });
function unsubscribe()
{
  if( $("#pren-email").val() == "" )
  {
    alert("Fyll i den e-postadress du har registrerad, för att kunna avsluta prenumerationen.");
  }
  else
  {
    var answer = confirm("Vill du avsluta prenumerationen på vårt nyhetsbrev?");
    if (answer){
        $(".newsletter").fadeOut();    
        //delete
        var dataString = "email="+ jQuery("#pren-email").val();
        jQuery.ajax({
          type: "POST",
          url: "' . $deletionFile . '",
          data: dataString,
          cache: false,
          success: function(html){
            $(".newsletter").html("Du har nu avregistrerat dig från vårt nyhetsbrev.");
            $(".newsletter").fadeIn();
          }
        });        
    }
    else{
        //cancel
    }                
  }
}  
</script>';

    echo '<div class="newsletter">
        <label>E-postadress</label>
        <input type="text" class="inputText" value="" id="pren-email"/>
        <input type="submit" class="button" value="Skicka" id="pren"/>
        <p>
          <a href="" onclick="unsubscribe(); return false;" class="stop">» Avsluta prenumeration</a>
        </p>
      </div>';
  }

}

// hooks for install and update
register_activation_hook(__FILE__, 'twentyfourEmailBin::install');
add_action('plugins_loaded', 'twentyfourEmailBin::update');



/**
 * Add admin page
 */
add_action('admin_menu', 'twentyfourEmailBin_add_page');

function twentyfourEmailBin_add_page() {
  add_menu_page('Email för nyhetsbrev', 'Email för nyhetsbrev', 'manage_options', __FILE__, 'twentyfourEmailBinListPage');

  function twentyfourEmailBinListPage() {
    include 'admin-pages/email-list-page.php';
  }

}