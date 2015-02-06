=== Paysera Tickets ===
Contributors: EVP International
Tags: online payment, payment, payment gateway, paysera, tickets
Requires at least: 3.8
Tested up to: 4.1
Stable tag: 4.3
License: GPLv2
License URL: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Sell tickets online with Paysera Tickets

Take full control over your event and start selling tickets online in minutes. 

Paysera Tickets is a complete WordPress plugin for event hosts.
It lets you create multiple ticket types, customize ticket designs and emails, setup discount codes, manage orders, verify tickets with a smartphone in any venue and many more features that makes it easy to host an event.

== Installation ==

1. Login to WordPress admin panel. http://your-site.com/wp-login.php.
2. Open Paysera „Tickets“ in your wordpress control panel on the left-hand side.

Creating an Event Type
1. First of all you have to create an Event Type, click on „Manage Event Type“ and then „Add“.
2. Set your locale.
3. Set a Name for your Event Type.
4. Next “Max tickets/user”, set a maximum amount of tickets available for purchase per single order.
5. In the „Status“ field set your Event Type as „Active“ and ready for use.
6. In the next field – “Pay by Invoice option”, you may allow or forbid users from paying by invoice. If you don’t need this functionality, leave it as default - “Inactive”.
7. If the last field – “Ability to issue Invoice” is set to “Active”. Ticket buyer will be able to mark that he/she needs an invoice. To receive an invoice, buyer will have to fill in the following information: Company name, company code, address, VAT code. Leave this field as “Inactive” if you do not need this functionality.
8. After providing all information about the Event Type click „Save“.
9. In case you need to change settings of your Event Type, navigate to “Event Types“ and in the “Action” column click on „ … “ and then „Edit“. See an example screenshot below.
10. After you have saved “Event Type”, you can click on “Edit”, change locale and if necessary provide event information in other languages as well and click “Save” again.

more information -  http://tickets.paysera.lt/

== Changelog ==

= Version: 2015-01-06 =

* Fixed:   Instalation into wp 3.5.1
* Fixed:   Saving and updating events
* Fixed:   Saving and updating event templates.


* Fixed {{ invoice.number }} variable. Previously max value of this variable was 10.
* It is possible to activate/deactivate Paysera payment methods by country. Events -> Edit -> Country code
* If event status set to inactive, then user is redirected to /evp-tickets/lt/info/event-inactive/1. event_ianctive.html.twig template is used for that.
* If event status set to inactive, then user is redirected to /evp-tickets/lt/info/event/1 without "Buy tickets" button at the bottom.
* Invoice number is generated only after payment is done. To add discount number in template use this variable: {{ invoice.number }}
* Added ability to add order discount amount in invoice template. Variable {{ order.discountAmount }}
* Link to skip event description: /evp-tickets/lt/order/1 
* 1=eventid

* Removed free ticket code. Will be finished in future.
* Ticket PDF file name is without sold ticket amount after ticket PDF file name, but in this structure ticketname_20141204_a1gr.pdf. Last for symbols are generated random.

* Ticket template now should be generated from session (URL) locale not from event locale.
* Payment methods now should be shown in session (URL) locale language not in event locale language.

* If ticket price is in EUR, in ticket type selection window bellow price in LTL price in EUR is calculated and shown.
* If ticket price is in LTL, in ticket type selection window bellow price in EUR price in LTL is calculated and shown.

* Discount codes could be uploaded from CSV file.
* Multi-usage discount code.



