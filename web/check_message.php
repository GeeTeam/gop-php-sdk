<?php
// error_reporting(0);
require_once dirname(dirname(__FILE__)) . '/lib/class.gmessagelib.php';
require_once dirname(dirname(__FILE__)) . '/config/config.php';
$GmSdk = new GMessageLib(ONEPASS_ID, PRIVATE_KEY);



$result = $GmSdk->check_message($_POST['process_id'], $_POST['message_id'], $_POST['message_number'],$_POST['phone'], $ssl = true);
if ($result) {
    echo '{"content":"succes","result":0}';
} else {
    echo '{"content":"fail","result":1}';
}