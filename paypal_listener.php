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
	
	//REQUIRE CONFIGURATION FILE
	require("includes/config.php"); //important file. Don't forget to edit it!
    require('includes/paypal.class.php');  // include the class file
    $paypal = new paypal_class;             // initiate an instance of the class
    $paypal->paypal_url = PAYPAL_URL;     // paypal url

if ($paypal->validate_ipn()) {
    if(isset($paypal->pp_data["txn_type"]) && strtolower($paypal->pp_data["txn_type"])=="subscr_cancel"){
        //paypal subscription cancellation email to customer
        $headers = "MIME-Version: 1.0\n";
        $headers .= "Content-type: text/html; charset=utf-8\n";
        $headers .= "From: 'Paypal Standard Payment Terminal' <noreply@" . $_SERVER['HTTP_HOST'] . "> \n";
        $subject = "Subscription Cancellation";
        $message = "Dear ".$paypal->pp_data["first_name"]." ".$paypal->pp_data["last_name"].", <br /> We just wanted to let you know that your subscription is now cancelled.<br /><br />";
        $message .= "Subscription Name:".$paypal->pp_data["item_name"]."<br />";
        $message .= "<br /><br />Kind Regards,<br />The Team";
        mail($paypal->pp_data['payer_email'], $subject, $message, $headers);

        //paypal subscription cancellation email to admin
        $headers = "MIME-Version: 1.0\n";
        $headers .= "Content-type: text/html; charset=utf-8\n";
        $headers .= "From: 'Paypal Standard Payment Terminal' <noreply@" . $_SERVER['HTTP_HOST'] . "> \n";
        $subject = "Subscription Cancellation";
        $message = "Dear Administrator, <br /> We just wanted to let you know that customer subscription is now cancelled.<br /><br />";
        $message .= "Subscription Name:".$paypal->pp_data["item_name"]."<br />";
        $message .= "Customer Name:".$paypal->pp_data["first_name"]." ".$paypal->pp_data["last_name"]."<br />";
        $message .= "Customer Email:".$paypal->pp_data['payer_email']."<br />";
        $message .= "<br /><br />Kind Regards,<br />The Team";
        mail($paypal->pp_data['payer_email'], $subject, $message, $headers);

    } else if(isset($paypal->pp_data["payment_status"]) && strtolower($paypal->pp_data["payment_status"])=="refunded"){
        //paypal process refund email here.
        $headers = "MIME-Version: 1.0\n";
        $headers .= "Content-type: text/html; charset=utf-8\n";
        $headers .= "From: 'Paypal Standard Payment Terminal' <noreply@" . $_SERVER['HTTP_HOST'] . "> \n";
        $subject = "Payment Refund";
        $message = "Dear ".$paypal->pp_data["first_name"]." ".$paypal->pp_data["last_name"].", <br /> We just wanted to let you know that we've refunded your payment by paypal.<br /><br />";
        $message .= "Transaction ID:".$paypal->pp_data["txn_id"]."<br />";
        $message .= "Total Amount: ".(strtolower(AccountCurrency)=="gbp"?"&pound;":(strtolower(AccountCurrency)=="eur"?"&euro;":"$")).number_format($paypal->pp_data["payment_gross"],2)."<br />";
        $message .= "<br /><br />Kind Regards,<br />The Team";
        mail($paypal->pp_data['payer_email'], $subject, $message, $headers);
    } else {
    #**********************************************************************************************#
    #  THIS IS THE PLACE WHERE YOU WOULD INSERT ORDER TO DATABASE OR UPDATE ORDER STATUS FOR PAYPAL
    #**********************************************************************************************#
    //you can use $paypal->pp_data['XXXX'] -> where XXXX is any variable which you will see in
    //confirmation email which is sent below (you will need to do a test transaction to receive this email)

    #**********************************************************************************************#
        //creating message for sending
        $headers = "MIME-Version: 1.0\n";
        $headers .= "Content-type: text/html; charset=utf-8\n";
        $headers .= "From: 'Paypal Standard Payment Terminal' <noreply@" . $_SERVER['HTTP_HOST'] . "> \n";
        $subject = "New Payment Received";
        $message = "New payment was successfully received through  PayPal Website Payments Standard<br />";
        $message .= "from " . $paypal->pp_data['payer_email'] . " on " . date('m/d/Y') . " at " . date('g:i A') . ".<br />";
        $message .= "<br />PayPal returned following variables to IPN listener:<br />";
        foreach($paypal->pp_data as $k=>$v){
            $message .= "<br /><strong>".$k."</strong>: ".$v;
        }
        mail($admin_email, $subject, $message, $headers);

        $headers = "MIME-Version: 1.0\n";
        $headers .= "Content-type: text/html; charset=utf-8\n";
        $headers .= "From: 'Paypal Standard Payment Terminal' <noreply@" . $_SERVER['HTTP_HOST'] . "> \n";
        $subject = "Payment Received";
        $message = "Dear ".$paypal->pp_data["first_name"]." ".$paypal->pp_data["last_name"].", <br /> We successfully received your payment by paypal.<br /><br />";
        $message .= "Transaction ID:".$paypal->pp_data["txn_id"]."<br />";
        $message .= "Total Amount: ".(strtolower(AccountCurrency)=="gbp"?"&pound;":(strtolower(AccountCurrency)=="eur"?"&euro;":"$")).number_format($paypal->pp_data["mc_gross"],2)."<br />";
        $message .= "<br /><br />Kind Regards,<br />The Team";
        mail($paypal->pp_data['payer_email'], $subject, $message, $headers);
    }
}

?>