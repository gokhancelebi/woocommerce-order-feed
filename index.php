<?php

/*
Plugin Name:  LIST ALL ORDERS
Plugin URI:
Description:  LIST ORDERS
Version:      1.0
Author: GOKHAN CELEBI
Author URI: https://gokhancelebi.net
License:      GPL2
License URI:
Text Domain:
*/

function gokhan_xml_order_list()
{
    add_menu_page(
        'XML ORDER LIST',
        'XML ORDER LIST',
        'manage_options',
        'gokhan-xml-order-list',
        'gokhan_order_list',
        "",
        100
    );
}

add_action('admin_menu', 'gokhan_xml_order_list');


function gokhan_order_list()
{

    $return = '<div class="wrap">
        <form action="/wp-content/plugins/gc-order-list/orders.php" method="get">
        <table>
        <tr>
            <td>Start Date</td><td><input name="start" type="date"></td>
        </tr>
        <tr><td>End Date</td><td><input name="end" type="date"></td></tr>
        <input type="hidden" name="download" value="yes">
        </table>
            <button class="btn success">Send</button>
        </form>
    </div>';

    echo $return;

}