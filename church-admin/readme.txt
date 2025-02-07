=== Church Admin ===
Contributors: andy_moyle
Donate link: https://pay.sumup.io/b2c/QEEPP89C
Tags: church, sermons, membership,schedule,  calendar
Requires at least: 5.0
Requires PHP: 7.0
Tested up to: 6.7
Stable tag: 5.0.16
Elementor tested up to: 3.25.0
License: GNU General Public License (GPL) version 2 

Organise and communicate church life, with associated Android and iOS app for your congregation.
== Description ==

This plugin is for church wordpress sites and has an smartphone app too - it adds an easy to use address directory and you can email and sms different groups of people.
<a href="http://churchadminplugin.com/#email-list">Sign up</a> for our email list to get a detailed PDF manual
<a href="http://demo.churchadminplugin.com">Demo site</a>
<a href="https://www.churchadminplugin.com">Plugin site</a>

Compatible with Elementor and provides Elementor widgets, tested to v3.25


* FREE VERSION
* Church Membership database
* Integrate newcomers with customisable registration form and follow up flows
* Calendar for church diary
* Sermon podcasting
* Customisable Church Directory with full privacy settings.

* PREMIUM VERSION
* Adds scheduling, communication tools, ministries, giving, pastoral visitation, an app and more.
== Installation ==

1. Upload the `church_admin` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Place [church_admin type=address-list member_type_id=# map=1 photo=1] on the page you want the address book displayed, member_type=1 for members, map=1 toshow map for geocoded addresses. The member_type_id can be comma separated  e.g. member_type_id=1,2,3
4. Place [church_admin type=small-groups-list] on the page you want the small group list displayed
5. Place [church_admin type=small-groups ] on the page you want the list of small groups and their members displayed
6. Place [church_admin type=rota] on the page you want the rota displayed
7. Place [church_admin type=calendar category=# weeks=#] on the page you want a page per month calendar displayed
8. Place [church_admin type=calendar-list] on the page you want a agenda view calendar - option category and weeks options pastable from category admin page
9. There is a calendar widget with customisable title, how many events you want to show and an option for it to look like a post-it note
10. Place [church_admin_map member_type_id=#] to show a map of colour coded small groups - need to set a service venue first to centre map and geolocate member's addresses by editing them.
We recommend password protecting the pages - if it is password protected, a link is provided to logout
The # should be replaced with which member types you want displayed as a comma separated list e.g. member_type=1,2


== Frequently Asked Questions ==
= Where can I get help? =
<a href="http://churchadminplugin.com/#email-list">Sign up</a> for our email list to get a detailed PDF manual
<a href="http://www.churchadminplugin.com/support/">http://www.churchadminplugin.com/support/</a>

= Where do I report security bugs found in this plugin? =  
You can report any security bugs found in the source code of the site-reviews plugin through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/church-admin). The Patchstack team will assist you with verification, CVE assignment and take care of notifying the developers of this plugin.

== Screenshots ==
1. Address list display
2. Sermon podcasting display
3. Calendar display


== Changelog ==
= 5.0.16 =
* Calendar script file versioned so it doesn't cache with old buggy version
= 5.0.15 =
* Small church upgrade offer of $30
= 5.0.14 =
* Sanitize output for church_admin member map Glutenberg block
* New style calendar fix for 6 week months like March 2025
= 5.0.13 =
* Fix some external mp3 files rejected when adding media as not mp3s
= 5.0.12 = 
* Small churches special offer price message
= 5.0.11 =
* Graceful update message shown for premium shortcodes
= 5.0.10 =
* Remove giving CSV download which is no longer in free version
= 5.0.9 =
* Removed AJAX access to cron email sending (only to be done by WordPress scheduled task)
= 5.0.8 =
* Fix [church_admin type="podcast"] shortcode
* Fix [church_admin type="calendar"] shortcode
= 5.0.7 =
* Remove rota sections
* Single sermon shortcode includes exclude items again
* Update shortcode generator
= 5.0.6 = 
* Make Elementor v3.25.0
= 5.0.5 =
* Change premium install download URL
* Clicking Premium Plugin install, deletes the uninstall script to prevent loss of data

= 5.0.4 = 
* Fix activation error on some sites
= 5.0.3 =
* Add missing elementor file
= 5.0.2 =
* Fix graph errors
= 5.0.1 =
* Pricing update for USD
= 5.0.0 =
* In line with WordPress plugin rules, Church Admin contains all the free stuff.
* All premium features are now in a separate plugin downloadable from https://churchadminplugin.com
* Fix new calendar category not saving
= 4.5.3 =
* Toilet messaging shortcode added, for chidlrens workers to quickly SMS parents when child needs toilet. 
* Twilio list also shows  outbound not replied yet too.
* Custom Field Bulk Editor entries sorted by name
= 4.5.2 =
* Fix roles not saving
* Added date search to main sermons new style block and shortcode
* Fixed Roles typo for Spiritual Gifts
= 4.5.1 =
* Podcast Feed importer added to Media section
* Fix layout for multiple event blocks on page
* Fix email title for event booking
= 4.5.0 =
* Roles and Permission update - possible breaking change, PLEASE CHECK in Church Admin>Settings Roles or Permissions.
= 4.4.26 =
* Fix iCal links on calendar list shortcode and block.
= 4.4.25 =
* Fix schedule edit formatting.
* Fix Twilio reply push
* Remove error debugging output on Email send
= 4.4.24 =
* Fix ticket form issue
* App admin edit person bug fixes
* Fix app address list search
* Restricted list check for address list block
= 4.4.23 =
* Fix next and previous buttons for Podcast schortcode where series id is set.
* Search added to new style sermons block and shortcode.
= 4.2.22 =
* Date picker for whole series edit and rest of series edit
* Fix merge people form
* Address List search to includes phone number and part or full address
* Fix church wide prayers save for app.
* Scheduled email - add time to schedule section.
* Services - more options for service frequency when adding or editing a new service
* Schedule - dates automatically generated for most service frequencies
* Schedule - create calendar event from schedule row added.
* Ensure unchecked "Use prefix" stops prefix being used
* UX improvements for Child Protection reporting
* Series choice respected on new style sermon block/shortcode
* Use/not use middle name switch respected
* Ministry list displayed alphabetically with sub-ministries under each ministry.
= 4.4.21 =
* Improve copy schedule user experience
* Fix apostrophe issue in PDFs
= 4.4.20 =
* Fix calendar admin area no events issue
= 4.4.19 =
* Fix page break issue on Address List PDF type 1
= 4.4.18 =
* Fix [NAME] error on some emails
= 4.4.17 = 
* Sermon Podcast single series_id next and previous button fixed
* Event bookings numbered more sensibly
* Event booking photo permission field applied to individuals
* Include photo permission on events ticket bookings PDF
= 4.4.16 =
* Remove multipart tag from add calendar causing false positive for malware.
= 4.4.15 =
* fix username check
* Fix logging post name on app
= 4.4.14 =
* Calendar single edit recurring bug fixed
* Calendar single edit resets recurring to single and how many to 1 in form
* Facility booking module - link added and calendar start date set to current month.
* Moved People module to pole position on main admin page
= 4.4.13 =
* Fix move household member type
* Fix move member type people to different member type
= 4.4.12 =
* Add no index and no follow headers to all downloads in the plugin
* Added a setting to SMS schedule to add text at the end of the message
* Fix specified series_id in old style sermon podcast
= 4.4.11 =
* New Sermon podcast share fixes and improvements
= 4.4.10 =
* Fix schedule overrides send now 
* Fix [NAME] uses wrong first name on 
= 4.4.9 =
* Sermon chain link icon now copies sermon link to clipboard
* Send email schedule for future date fixed
* Email send - add [NAME] shortcode to message text to be replaced by first name.
* Calendar category text color automatically changes between black or white depending on background color
* Fix apple podcasts logo on old style podcasts shortcode/block.
= 4.4.8 =
* Fix not available database bug
* Graceful error messages for app schedule
= 4.4.7 =
* Database changes to ensure compatability with Elementor
* Better QR code formation
* Schedule form fields not populated with "Click to enter" value
= 4.4.6 =
* Calendar and Calendar list only show events where Show on General calendar is checked (ignored where facility is specified)
* Add edit individual attendance
* Improve graph module
* Yet more debuging added to sermon podcast uploading
* nofollow attribute added to download links
= 4.4.5 =
* Itunes badge replaced with Apple Podcasts
* Fix vulnerability with Video Embed Glutenberg block
* Front end register, block attempts to delete different households
= 4.4.4 =
* Debugging for sermon uploads amended
= 4.4.3 =
* Add delete all calendar events link
= 4.4.2 =
* Security tokens added to emailed links for admins.
* Sermon uploads warning when attempting to upload a file that is too big.
= 4.4.1 =
* "Check them out" links fixed
* Approve and decline volunteer links fixed
= 4.4.0 =
* MailChimp removed from plugin
* Elementor widgets added
* Fix current data not added to text field when click schedule rota item
* Fix Address List PDF form in admin area
* Fix edit service admin links and service save bugs
= 4.3.6 =
* Use correct default email/from name on email send form.
* Add nonce to admin new household "Check them out" link
* Conditional multisie upload directory check only if not already done by another plugin or theme.
= 4.3.5 =
* Event ticket checking
* Tidy up edit ticket form
* Fix PHP 8.0 deprecation warnings
= 4.2.4 =
* Fix sermon plays not updating
= 4.2.3 =
* Volunteer approve/decline links have nonce security added (links last 12 hours)
= 4.2.2 =
* Auto Schedule SMS link fixed
= 4.2.1 =
* Remove unused Hope team code
* Fix import CSV bug
= 4.2.0 =
* Add in Kiosk App section for upcoming kiosk registrations and ticket check in app.
* Remove download link for debug log for security
* Add security nonces to pastoral visitation module
* Optimise database table install function for newer mysql and mariadb compatability
= 4.1.33 =
* Fix people database table for some sites where front end register not working
* Site id option selection fixed for front end register
* Fix issue where sermon-series is specified in old style sermon shortcode/block
* Fix issues where series slug not saved in some instances preventing series search
* Fix sermon series entries missing a series slug
= 4.1.32 =
* Fix custom fields bug on admin people edit
* Fix household custom field filter not selecting people
= 4.1.31 =
* Email send filter changes show which emails will receive the email.
* Fix filter for household custom fields
= 4.1.30 =
* Event ticketing add ticket bug fixed
= 4.1.29 =
* Further improvement to filter for checkbox
= 4.1.28 =
* Add checkbox, radio and select people custom fields to the filter (add from choose filter in settings)
* Attendance admin links fixed
* Edit small group links fixed
= 4.1.27 =
* Fixed Giving date not showing on Giving table
= 4.1.26 =
* Increase number of options for custom fields
* Fix photo permissions PDF error
= 4.1.25 =
* Correct email method showing after update
* Clicking Schedule edit bug fixed
= 4.1.24 =
* Fix front end register edit address adding slashes to apostrophes
* Full privacy form show flag now working for front end register shortcode and block
= 4.1.23 =
* Longer form SMS messaging allowed, shows how many SMS credits per cell number
* Edit schedule job invalid link fix
= 4.1.22 =
* Email schedule fixed
= 4.1.21 =
* Calendar next/previous buttons added nonce
= 4.1.20 =
* Fix schedule settings link expired
= 4.1.19 = 
* Fix save service
* Updated nonces for Ajax calls
= 4.1.18 =
* Close XSS vulnerability in Bible readings Bible passage by authenticated author, contributor
= 4.1.17 =
* Pagination link fixed
* Sermon edit where no series set fixed
= 4.1.16 =
* App sermon audio fix
= 4.1.15 =
* Pastoral visitation settings defaults added.
* Individual attendance form tidy up
* Email send bug fixed (when not using Mailersend)

= 4.1.14 =
* Payment gateway settings tweak
= 4.1.13 =
* App account editing disabled option in App settings
* add calendar link expired fixed
= 4.1.12 =
* Multisite correct links for sermon podcast 
* Display household link fixed
* Fix email schedule bug
* Fix add/edit small group
= 4.1.11 =
* Further nonce adjustments for security
* Ticket booking PDF fix
= 4.1.10 =
* Fix address list in admin area bug
* Admin area media links protected with nonces
= 4.1.9 =
* Pastoral visitation module bug fix
= 4.1.8 =
* Fixed address list not showing if there's no site_id on some sites.
* Removed attachments from email for security
* Fixed small group PDF throwing an error if no day set for meeting
* Added security nonces to links and forms.
= 4.1.7 =
* Audited access control for every function
= 4.1.6 =
* Prevent executable files from being sent by email or stored in cache
* Detect if bulk Bible reading upload is definitely a CSV file
* Only admin level users can delete the debug log
= 4.1.5 =
* New shortcode for simple mailing list form
* Mailing list added to Shortcode generator
* App address list error fixed
= 4.1.4 =
* Confirmed email Thank you page error fix.
* Contact form email "reply to" goes to contact message questioner
* Remove extra > from contact message email.
* Fix broken link in Child Protection module reporting module
= 4.1.3 =
* Fix warning on some sites for address list display
* Graceful failure message for Mailersend Bulk API send failure.
* Test email function tests wp_mail and church admin email with graceful messaging of issues.
= 4.1.1 =
* Child Protection incident logging added for Premium version
= 4.1.0 =
* Household images in address section of app.
* Delete household no longer deletes user account (and therefore posts), but emails admin email to get them to do it.
* Push messages saving correctly in the app.
= 4.0.33 =
* Calendar single event edit - automatically set the recurrence to single
* Add rest of series calendar edit option
* Fix prayer request moderation on app
* Only show draft prayer requests to moderators on app.
* Add search to old style address list on app.
= 4.0.32 =
* App bug fixed
= 4.0.31 =
* Spiritual gifts module showable on main menu screen for Standard and Premium Versions
* Detailed privacy settings applied to address list PDF
= 4.0.30 =
* Address list block - added dropdown selection of church site.
* Mailersend API added for ease of email sending from Church Admin plugin
* Warning message if host has disabled mail() stopping email sending
* Sermons not showing in admin area if no series set fixed.
* Old style sermon block and shortcode - fix next button not remembering speaker choice.
* Updated method for Premium version Push sending
* Fix giving forms not displaying on some premium sites
* Service booking not showing fixed
* Push notifications updated for Firebase deprecated API.
* Twilio SMS replies ordered by latest first
* Future calendar event delete goes to same month calendar after deletion
* Fix calendar not saving bug
* PHP 8.0 fixes
* Pagination fix for PHP 8.0
* PDF generation updates for PHP 8.0
= 4.0.29 =
* Registration email templates neatened and previews added
* Single sermon shortcode fixed
* Contact form block added
* Improvements to new entry, confirmation templates
* Ability to format new user's username in different ways
* Register block and short code now has option to show all privacy form fields
* Fix for Photo permissions PDF crash on some sites.
* Fix for Individual Attendance CSV download crashing for old PHP versions.
= 4.0.28 =
* Security NONCE added to toggle debug mode.
= 4.0.27 =
* Fix XSS vulnerabilities in [church_admin_map] and [church_admin type="recent"]
= 4.0.26 = 
* Fix crash for non English languages sites on Schedule section
= 4.0.25 =
* Fix crash where schedule trying to show service that has no schedule jobs for it.
= 4.0.24 =
* Fixed email send database error
* Fixed Individual attendance CSV download bug
= 4.0.23 =
* Fix bug with choosing service for schedules.
= 4.0.22 =
* Module dropdown only shows options for current version level
* Standard upgrade form shown when version is free or basic
= 4.0.21 =
* Bulk Geocode initial map location set to London if no service location preset
* Sermon upload/add form tidy
= 4.0.20 =
* My rota page working for Standard and Premium versions
= 4.0.19 =
* Sent emails logs time and date sent now and ordered by newest first
* Sermon podcast forms - service and series previous choices persist
* New filter categories added
* Push messages save in app "Messages" menu item.
* New app content publish/trash triggers app menu rebuild
* Updated simplified main email template for 2024
* Fix update email settings link
* Improved schedule admin page
* Confirm email automation only sends if no data protection reason set and no user account connection
* Registration and new user email settings added to settings menu
* Fix Quick Household - adding member_type_id 
* Fixed bug in Check for directory issues.
* Improvements to "Add uploaded file" in media section
* Fix app edit celendar series event bug 
* Emails handle video embed and buttons blocks 
= 4.0.18 =
* Tweaks to email settings 
= 4.0.17 =
* Radio choice for MailChimp
= 4.0.16 =
* Quick household menu item added (just first name, last name and email), creates user account so they can edit.
* Bulk Email MailChimp option made a checkbox for ease of switching off (will be deprecating MailChimp in 2024)
* Link in SMS section to Twilio not for profit credit scheme
* Twilio message that "not for profit" impact credit doesn't show on API credit balance
* Graceful failure message when email send fails.
* Fix warning on address list shortcode/block on empty people table.
* Graceful failure when Vimeo video ID doesn't exist.
* Graceful failure when licence check fails to get server response.
= 4.0.15 = 
* If SMTP settings saved and previous update was v4.0.14, then set correct method
= 4.0.14 =
* Tidied Email settings Page
* Better licence checking
= 4.0.13 =
* Clear email queue menu item
* Fix for emails not sending immediately when using send email on some sites.
* Twilio not for profit credit link added - https://console.twilio.com/us1/billing/nonprofit-benefits/sign-up
= 4.0.12 =
* Delete me button added to app
* Fix fatal error for schedule PDF on some sites.
* Fix app calendar event dropdown bug
* Fix app calendar edit form not showing correct category
* Fix copy schedule date not working
= 4.0.11 =
* Fixed individual photos not showing on address list
* Fixed permissions/role issue with Pastoral Module
= 4.0.10 =
* Added login_form option to [church_admintype="not-logged-in" login_form=1]
= 4.0.9 =
* Confirm email uses in front end register now uses "confirm email template"
* GDPR email uses "confirm email template"
= 4.0.8 =
* Fix "Child of ..." not showing parent's names
* Fix add new calendar event post save shows January 1970 calendar
* SMTP settings screen password hidden by default
= 4.0.7 =
* Fix "Forgotten Password" button on Our Church App.
= 4.0.6 =
* Anniversaries email fix
* Bulk emailer bug fix
* Single sermon bug on old style sermons fixed
= 4.0.5 = 
* Reply name and email for sites using SMTP settings for sending email.
= 4.0.4 = 
* Added Stripe payment links for Standard and Premium versions.
* Improved table calendar tooltip
* Global anniversary email template bug fix
= 4.0.3 =
* Database queries performance improvement.
* Fix wrong licence showing for some churches
= 4.0.2 =
* Licence check enhancements
* Fix for email filters when "classes" breaks it.
= 4.0.1 =
* Licence check button added in case you are seeing the wrong version
= 4.0.0 =
* Change in pricing structure for Church Admin - free, standard & premium.
* Fix email send not using from and reply when SMTP settings have been added.
* Improve global both anniversary table
= 3.8.68 =
* Fix for app people edit where non specified date of birth/wedding anniversary are saved as today.
* Fix for front end register, certain permissions not saving correctly
* Bulk email sends logged in sent emails
* Spelling correction on gmail less secure apps message
= 3.8.67 =
* Spelling corrections
* Follow up email template bug fix
= 3.8.66 =
* Communications module shows more detail of how emails are queued.
= 3.8.65 =
* Fixed issue where Queued email wasn't sending - table truncated
= 3.8.64 =
* Don't send Push, SMS or email to people marked as inactive
* Active/Inactive button added to people on display household screen.
* Email send table shows recipients
* Improvements to global birthday email 
* Improvements to global birthday and anniversary email
= 3.8.63 =
* Further fix for ICS calendar import
* Fix PayPal giving form not working on some sites.
* Fix event booking 'Add ticket' button failing if no currency symbol set.
= 3.8.62 =
* Fix ICS calendar import
= 3.6.61 =
* Fix error message for duplicate calendar column fixed
* Fix people not added to ministry when editing schedule job
* Initial "Heading" text removed from calendar block/shortcode
= 3.5.60 =
* add reply to information to stored email history
* extra check before sending global birthday and anniversary email 
* Fix global both date
= 3.5.59 =
* Fix "make everyone visible"
= 3.8.58 =
* More detail automations people counts.
= 3.8.56 =
* Global anniversary and birthday email flag colour corrected
* Automations - indicate if there any birthdays /anniversaries that day
= 3.8.54 =
* Admin error people form not saving marital status bug fixed
* Rota settings option to set whether to show that job on  as service calendar item (default on)
* Schedule details added to Calendar list shortcode and block
* Schedule details added to Calendar  shortcode and block
* Schedule details added to Calendar table shortcode and block
* Calendar table popup stays visible until different item mouseover
= 3.8.53 =
* Pastoral visits overdue fatal error fixed
= 3.8.52 =
* Fix global both email cron event hook
= 3.8.51 =
* Visit notes link added to  pastoral visitation list table
= 3.8.50 =
* Fix cell not showing in app address list
= 3.8.49 =
* Front end register form privacy and communication fixes


== Upgrade Notice ==
= 4.1.7 = 
* Fixes access vulnerability for subscriber account email attachment upload