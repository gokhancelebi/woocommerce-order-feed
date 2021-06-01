<?php

if($_GET["download"] != "yes") exit;

include "../../../wp-load.php";
header("Content-Type:text/xml");

function download($data){


    if ($_GET["download"] == "yes"){

        header("Content-type: text/plain");
        header("Content-Disposition: attachment; filename=data.xml");

    }
    return  $data ;
}


$start = explode("-",$_GET["start"]);
$end = explode("-",$_GET["end"]);

ob_start("download");


$date_from = $start[0].'-'.$start[1].'-'.$start[2];
$date_to = $end[0].'-'.$end[1].'-'.$end[2];
$post_status = implode("','", array('wc-processing', 'wc-completed','wc-issiustas') );

$result = $wpdb->get_results( "SELECT * FROM $wpdb->posts 
            WHERE post_type = 'shop_order'
            AND post_status IN ('{$post_status}')
            AND post_date BETWEEN '{$date_from}  00:00:00' AND '{$date_to} 23:59:59'");
$user_ids = [];
foreach ($result as $order):

    $order = wc_get_order($order->ID);
    $order_customer_id = $order->get_customer_id();

    $user_ids[] = $order_customer_id;

endforeach;
$user_ids = implode(",",$user_ids);
echo '<?xml version="1.0"?>';
?>
    <iSAFFile xmlns="http://www.vmi.lt/cms/imas/isaf" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <Header>

        <FileDescription>
            <FileVersion>iSAF1.2</FileVersion>
            <FileDateCreated><?= date('Y-m-d\TH:i:s\Z', time()) ?></FileDateCreated>
            <DataType>F</DataType>
            <SoftwareCompanyName>PapilduEra</SoftwareCompanyName>
            <SoftwareName>PapilduEra.lt</SoftwareName>
            <SoftwareVersion>2.0</SoftwareVersion>
            <RegistrationNumber>305519913</RegistrationNumber>
            <NumberOfParts>1</NumberOfParts>
            <PartNumber>1</PartNumber>
            <SelectionCriteria>
                <SelectionStartDate><?= $start[0] ?>-<?= $start[1]?>-<?= $start[2]?></SelectionStartDate>
                <SelectionEndDate><?= $end[0] ?>-<?= $end[1] ?>-<?= $end[2] ?></SelectionEndDate>
            </SelectionCriteria>
        </FileDescription>
    </Header>
    <MasterFiles>
        <Customers>
            <?php

            $users = $wpdb->get_results("SELECT * FROM ".$table_prefix."users where ID IN($user_ids)" );

            foreach ($users as $user):
                ?>
                <Customer>
                    <CustomerID><?=$user->ID?></CustomerID>
                    <VATRegistrationNumber>LT</VATRegistrationNumber>
                    <RegistrationNumber>LT</RegistrationNumber>
                    <Country>LT</Country>
                    <Name><?=get_user_meta($user->ID,"first_name",true)?> <?=get_user_meta($user->ID,"last_name",true)?></Name>
                </Customer>
            <?php endforeach;?>
        </Customers>
    </MasterFiles>
    <SourceDocuments>
        <SalesInvoices><?php

            $date_from = $start[0].'-'.$start[1].'-'.$start[2];
            $date_to = $end[0].'-'.$end[1].'-'.$end[2];
            $post_status = implode("','", array('wc-processing', 'wc-completed','wc-issiustas') );

            $result = $wpdb->get_results( "SELECT * FROM $wpdb->posts 
            WHERE post_type = 'shop_order'
            AND post_status IN ('{$post_status}')
            AND post_date BETWEEN '{$date_from}  00:00:00' AND '{$date_to} 23:59:59'
        ");

            foreach ($result as $order):

                $order_id = $order->ID;
                $order = wc_get_order($order->ID);
                $order_customer_id = $order->get_customer_id();
                $order_shipping_country = $order->get_shipping_country();
                $order_billing_first_name = $order->get_billing_first_name();
                $order_billing_last_name = $order->get_billing_last_name();
                $order_date_created = $order->get_date_created();
                $order_date_created = explode("T",$order_date_created);
                $order_date_created = $order_date_created[0];
                $order_shipping_total = $order->get_total();
                $order_total_tax = $order->get_total_tax();
                $order_data = $order->get_data();
                ?>
                <Invoice>
                    <InvoiceNo><?=get_post_meta($order_id,"invoice_number",true) ? get_post_meta($order_id,"invoice_number",true) : $order_id ?></InvoiceNo>
                    <CustomerInfo>
                        <CustomerID><?= $order_customer_id ?></CustomerID>
                        <VATRegistrationNumber>ND</VATRegistrationNumber>
                        <RegistrationNumber>ND</RegistrationNumber>
                        <Country><?= $order_shipping_country ?></Country>
                        <Name><?= $order_billing_first_name . " " . $order_billing_last_name ?></Name>
                    </CustomerInfo>
                    <InvoiceDate><?= $order_date_created ?></InvoiceDate>
                    <InvoiceType>SF</InvoiceType>
                    <SpecialTaxation/>
                    <References/>
                    <VATPointDate xsi:nil="true"/>
                    <DocumentTotals>
                        <DocumentTotal>
                            <TaxableValue><?= ($order->get_total() - $order_data['total_tax'])  ?></TaxableValue>
                            <TaxCode>PVM1</TaxCode>
                            <TaxPercentage><?= explode(".",number_format($order_data['total_tax']  / (($order->get_total() - $order_data['total_tax'])),2,`,`,`.`) )[1]?></TaxPercentage>
                            <Amount><?= $order_shipping_total ?></Amount>
                            <VATPointDate2 xsi:nil="true"/>
                        </DocumentTotal>
                    </DocumentTotals>
                </Invoice>
            <?php endforeach; ?>
        </SalesInvoices>
    </SourceDocuments>
    </iSAFFile><?php ob_end_flush()?>