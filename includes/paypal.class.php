<?php
/******************************************************************************
#                      PHP Stripe Payment Terminal v1.3
#******************************************************************************
#      Author:     Convergine.com
#      Email:      info@convergine.com
#      Website:    http://www.convergine.com
#
#
#      Version:    1.3
#      Copyright:  (c) 2013 - Convergine.com
#
#*******************************************************************************/

class paypal_class {
   var $response;
   var $pp_data = array(); 
   var $fields = array();           

   function paypal_class() {   
      // constructor.  
      $this->paypal_url = PAYPAL_URL_STD;
      $this->response = '';
      $this->add_field('rm','2');
      if(PAYMENT_MODE=="RECUR"){
          $this->add_field('cmd','_xclick-subscriptions');
      } else {
          $this->add_field('cmd','_xclick');
      }
   }

   function add_field($field, $value) {
      $this->fields["$field"] = $value;
   }

   function submit_paypal_post() {
       $mess = '<div class="ui-widget"><div class="ui-state-info ui-corner-all" style="padding: 0 .7em;">
       Please wait, you will be redirected to the paypal website.<br />
       If you are not automatically redirected to paypal within 5 seconds...
       <form method="post" name="dps_paypal_form" action="'.$this->paypal_url.'">';
       foreach ($this->fields as $name => $value) {
           $mess .= "<input type=\"hidden\" name=\"$name\" value=\"$value\"/>";
       	}
        $mess .= '<input type="submit" class="submitProcessing" value="Click Here">
        </form>
        </div></div><br /><script>$(document).ready(function() { document.dps_paypal_form.submit(); }); </script>';
       return $mess;
   }

   
   function validate_ipn() {
      // parse the paypal URL
      $url_parsed=parse_url($this->paypal_url);        
	  
      $post_string = '';    
      foreach ($_POST as $field=>$value) { 
         $this->pp_data["$field"] = $value;
         $post_string .= $field.'='.urlencode(stripslashes($value)).'&'; 
      }
      $post_string.="cmd=_notify-validate"; 
      // open the connection to paypal
      $fp = fsockopen('ssl://'.$url_parsed["host"], "443", $err_num, $err_str, 30);
      if(!$fp) {
         return false;
      } else {
         // Post the data back to paypal
         fputs($fp, "POST ".$url_parsed["path"]." HTTP/1.1\r\n");
         fputs($fp, "Host: ".$url_parsed["host"]."\r\n");
         fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
         fputs($fp, "Content-length: ".strlen($post_string)."\r\n");
         fputs($fp, "Connection: close\r\n\r\n");
         fputs($fp, $post_string . "\r\n\r\n");
         // loop through the response from the server and append to variable
         while(!feof($fp)) {
            $this->response .= fgets($fp, 1024);
         }
         fclose($fp); // close connection
      }
      if (preg_match("/VERIFIED/i",$this->response)) {
         // Valid IPN transaction.
         return true;
      } else {
         // Invalid IPN transaction.
         return false;
      }
   }

}  //class end

?>