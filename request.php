﻿<?php
/*
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Landing page of Organization Manager View (Approvels)
 *
 * @package    enrol_zarinpal
 * @copyright  2021 Armin Zahedi <ar4min.ir>
 * @author     Armin Zahedi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__) . '/../../config.php');
require_once("lib.php");
global $CFG, $_SESSION, $USER, $DB;

$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$plugininstance = new enrol_zarinpal_plugin();
if (!empty($_POST['multi'])) {
    $instance_array = unserialize($_POST['instances']);
    $ids_array = unserialize($_POST['ids']);
    $_SESSION['idlist']  =implode(',', $ids_array);
    $_SESSION['inslist']  =implode(',', $instance_array);
    $_SESSION['multi'] = $_POST['multi'];
} else {
    $_SESSION['courseid'] = $_POST['course_id'];
    $_SESSION['instanceid'] = $_POST['instance_id'];
}

$_SESSION['totalcost']= $_POST['amount'];
$_SESSION['userid'] = $USER->id;
$Price = $_SESSION['coo'];

$MerchantID = $plugininstance->get_config('merchant_id');
$testing = $plugininstance->get_config('checkproductionmode');
$use_zaringate = $plugininstance->get_config('usezaringate');
$ReturnPath = $CFG->wwwroot.'/enrol/zarinpal/verify.php';
$ResNumber = date('YmdHis');// Order Id In Your System
$Description = 'پرداخت شهریه ' . $_POST['item_name'];
$Paymenter = $USER->firstname. ' ' .$USER->lastname;
$Email = $USER->email;
$Mobile = $USER->phone1;

$data = array(
    'merchant_id' => $MerchantID,
    'amount' => intval($Price) * 10,
    'description' => $Description,
    'callback_url' => $ReturnPath
);
$jsonData = json_encode($data);

$ch = curl_init('https://api.zarinpal.com/pg/v4/payment/request.json');
curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
));

$result = curl_exec($ch);
$err = curl_error($ch);
$result = json_decode($result, true, JSON_PRETTY_PRINT);
curl_close($ch);

//if ($testing == 0)
//
//    $client = new SoapClient('https://sandbox.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
//else
//    $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
//$data_arrray = [
//    'MerchantID'  => $MerchantID,
//    'Amount'      => ($Price / 10),
//    'Description' => $Description,
//    'Email'       => $Email,
//    'Mobile'      => $Mobile,
//    'CallbackURL' => $ReturnPath,
//];
//print_r($_POST);
//print_r($data_arrray);
//die();
//$res = $client->PaymentRequest($data_arrray);

header('Location: https://www.zarinpal.com/pg/StartPay/' . $result['data']["authority"]);

//Redirect to URL You can do it also by creating a form
if ($result['data']['code'] == 100) {
    header('Location: https://www.zarinpal.com/pg/StartPay/' . $result['data']["authority"]);

} else {
    echo'ERR: '.$result['errors']['code'];
}
