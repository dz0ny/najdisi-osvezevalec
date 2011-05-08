<?php
/*
Plugin Name: Najdi.si osveževalec
Description: Ob posodobitvi/vnosu strani ali članka pošlje zahtevo na najdi.si, za ponovno indeksiranje spremenjene vsebine
Author: Janez Troha
Version: 1.2
Author URI: http://github.com/dz0ny/Loggy

(The MIT License)

Copyright (c) 2011 Janez Troha <janez.troha@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the 'Software'), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

class NajdiSI {
	
	function NajdiSI()
	{
		//Posodobljen članek
		add_action('save_post', array(&$this, 'rezervirajCron'),9999,3);	
		add_action('najdisi_poslji_cron', array(&$this, 'posljiNajdiSi'),1,1);  
	}
	
	/**
	 * Nastavi posodobitev (cron), samo v primeru da ni to uvoz ali revizija
	 *
	 * @return void
	 * @author Janez Troha
	 **/
	function rezervirajCron($post_id)
	{	
		if( (!defined('WP_IMPORTING') || WP_IMPORTING != true) && !wp_is_post_revision($post_id) ) {
			wp_schedule_single_event(time()+15,'najdisi_poslji_cron', array($post_id));
		}

	}
	/**
	 * Pošlji zahtevek za indeksiranje na najdi.si in dodaj sledilno kodo
	 *
	 * @return void
	 * @author Janez Troha
	 **/
	function posljiNajdiSi($post_id)
	{
		//Goolge analyitcs tracking preko Kampanje
		$ga_tacking = "?utm_campaign=najdisi&utm_source=najdi-si-osvezevalec&utm_medium=povezava";
                //glej http://www.najdi.si/publishers/indexing.html za več informacij o omejitvah (1000 zahtevkov na 15s)
		$url = "http://www.najdi.si/indexingOnDemand.jsp?url=" . urlencode ( get_permalink($post_id).$ga_tacking );
		$timeout = 10;
		
		$options = array();
		$options['timeout'] = $timeout;
		
		$response = wp_remote_get( $url, $options );

		if ( is_wp_error( $response ) ) {
			$errs = $response->get_error_messages();
			$errs = htmlspecialchars(implode('; ', $errs));
			trigger_error('WP HTTP API Web Request failed: ' . $errs,E_USER_NOTICE);
			return false;
		}

		return false;
	}
}
add_action("init", "NajdiSIInit");
function NajdiSIInit() {
    global $NajdiSI; $NajdiSI = new NajdiSI();
}
