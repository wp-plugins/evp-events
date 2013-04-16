<?php

function wpe_install() {

	global $wpdb;
	
	$folder_pdf = WPE_PLUGIN_DIR . DS . 'pdf';
	$folder_tmp = WPE_PLUGIN_DIR . DS . 'tmp';
	$folder_ticket_templates = WPE_PLUGIN_DIR . DS . 'tmpl'. DS . 'ticket_templates';
	$folder_templates = WPE_PLUGIN_DIR . DS . 'tmpl' . DS . 'personalized_templates';
	
	if (!is_writable($folder_pdf)) {
		die($folder_pdf . ' must be writable!!');
	}
	
	if (!is_writable($folder_tmp)) {
		die($folder_tmp . ' must be writable!!');
	}
	
	if (!is_writable($folder_ticket_templates)) {
		die($folder_ticket_templates . 'must be writable!!');
	}
	
	
	if (!is_writable($folder_templates)) {
		die($folder_templates . 'must be writable!!');
	}
	
	$sql ="
	CREATE TABLE IF NOT EXISTS `wp_event_companies` (
	  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
	  `name` varchar(100) NOT NULL,
	  `address` varchar(150) NOT NULL,
	  `code` varchar(100) NOT NULL,
	  `pvm_code` varchar(100) NOT NULL,
	  `fax` varchar(100) NOT NULL,
	  `person_name` varchar(100) NOT NULL,
	  `person_phone` varchar(20) NOT NULL,
	  `person_email` varchar(150) NOT NULL,
	  `created` datetime NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
	";
	
	$wpdb->query($sql);
	
	$sql = "
	CREATE TABLE IF NOT EXISTS `wp_event_customers` (
	  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
	  `name` varchar(200) NOT NULL,
	  `phone` varchar(20) NOT NULL,
	  `email` varchar(100) NOT NULL,
	  `created` datetime NOT NULL,
	  `ticket_id` int(11) unsigned DEFAULT NULL,
	  `group_id` int(6) unsigned NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `ticket_id` (`ticket_id`),
	  KEY `group_id` (`group_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
	";
	$wpdb->query($sql);
	
	$sql = "
	CREATE TABLE IF NOT EXISTS `wp_event_discount_codes` (
	  `id` int(7) unsigned NOT NULL AUTO_INCREMENT,
	  `event_id` int(5) NOT NULL,
	  `code` varchar(50) COLLATE utf8_bin NOT NULL,
	  `discount` float NOT NULL,
	  `amount_left` int(5) NOT NULL,
	  `amount` int(5) NOT NULL,
	  `created` datetime NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
	";
	$wpdb->query($sql);
	
	$sql = "
		CREATE TABLE IF NOT EXISTS `wp_event_events` (
		  `id` int(5) NOT NULL AUTO_INCREMENT,
		  `webtopay_project_id` varchar(20) NOT NULL,
		  `webtopay_project_sign` varchar(150) NOT NULL,
		  `webtopay_project_test` int(1) NOT NULL,
		  `valid_from` datetime NOT NULL,
		  `valid_to` datetime NOT NULL,
		  `ticket_amount` int(5) NOT NULL,
		  `currency` varchar(3) NOT NULL,
		  `payment_options` blob NOT NULL,
		  `smtp_options` blob NOT NULL,
		  `email_from` varchar(150) NOT NULL,
		  `email_from_name` varchar(150) NOT NULL,
		  `email_subject` varchar(150) NOT NULL,
		  `tickets` blob NOT NULL,
		  `unique_code` varchar(100) NOT NULL,
		  `email_to_cust_sb` varchar(100) NOT NULL,
		  `email_to_rep1_sb` varchar(100) NOT NULL,
		  `email_to_rep2_sb` varchar(100) NOT NULL,
		  `email_to_rep3_sb` varchar(100) NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `unique_code` (`unique_code`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
	";
	
	$wpdb->query($sql);

	$sql = "
	CREATE TABLE IF NOT EXISTS `wp_event_groups` (
	  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
	  `company_id` int(6) unsigned DEFAULT NULL,
	  `event_id` int(5) NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `company_id` (`company_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
	";
	$wpdb->query($sql);

	$sql = "
	CREATE TABLE IF NOT EXISTS `wp_event_invoices` (
	  `id` int(6) NOT NULL AUTO_INCREMENT,
	  `event_id` int(5) NOT NULL,
	  `paid_in` int(1) NOT NULL,
	  `paid_in_date` datetime NOT NULL,
	  `amount` float(7,2) NOT NULL,
	  `created` datetime NOT NULL,
	  `group_id` int(6) unsigned NOT NULL,
	  `payment_type` int(1) NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `group_id` (`group_id`),
	  KEY `payment_type` (`payment_type`)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
	";
	$wpdb->query($sql);
	
	$sql = "
	CREATE TABLE IF NOT EXISTS `wp_event_tickets` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `amount` float(7,2) NOT NULL,
	  `currency` char(3) NOT NULL,
	  `created` datetime NOT NULL,
	  `used` datetime DEFAULT NULL,
	  `ticket_nr` varchar(24) NOT NULL DEFAULT '',
	  `valid_from` datetime NOT NULL,
	  `valid_to` datetime NOT NULL,
	  `validated_by` varchar(255) NOT NULL,
	  `event_id` int(5) NOT NULL,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `ticket_nr` (`ticket_nr`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
	";
	$wpdb->query($sql);
	
	$sql = "
	ALTER TABLE `wp_event_customers`
	  ADD CONSTRAINT `wp_event_customers_ibfk_3` FOREIGN KEY (`group_id`) REFERENCES `wp_event_groups` (`id`),
	  ADD CONSTRAINT `wp_event_customers_ibfk_4` FOREIGN KEY (`ticket_id`) REFERENCES `wp_event_tickets` (`id`);
	";
	$wpdb->query($sql);
	
	$sql = "
	ALTER TABLE `wp_event_groups`
	  ADD CONSTRAINT `wp_event_groups_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `wp_event_companies` (`id`);
	";
	$wpdb->query($sql);
	
	$sql = "
	ALTER TABLE `wp_event_invoices`
	  ADD CONSTRAINT `wp_event_invoices_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `wp_event_groups` (`id`);
	";
	 $wpdb->query($sql);
	 
}

function wpe_deactivate() {
	global $wpdb;
	
	$sql ="SET foreign_key_checks = 0;";
	$wpdb->query($sql);
	
	$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}event_companies`";
	$wpdb->query($sql);
	$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}event_customers`";
	$wpdb->query($sql);
	$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}event_discount_codes`";
	$wpdb->query($sql);
	$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}event_groups`";
	$wpdb->query($sql);
	$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}event_invoices`";
	$wpdb->query($sql);
	$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}event_tickets`";
	$wpdb->query($sql);
	$sql ="SET foreign_key_checks = 1;";
	$wpdb->query($sql);
}

function wpe_uninstall() {
	
	global $wpdb;
	
	$sql ="SET foreign_key_checks = 0;";
	$wpdb->query($sql);
	
	$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}event_events`";
	$wpdb->query($sql);
	
	$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}event_companies`";
	$wpdb->query($sql);
	$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}event_customers`";
	$wpdb->query($sql);
	$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}event_discount_codes`";
	$wpdb->query($sql);
	$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}event_groups`";
	$wpdb->query($sql);
	$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}event_invoices`";
	$wpdb->query($sql);
	$sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}event_tickets`";
	
	$sql ="SET foreign_key_checks = 1;";
	$wpdb->query($sql);
}





