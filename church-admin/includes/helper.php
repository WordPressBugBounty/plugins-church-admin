<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly



function church_admin_helper( $what)
{
   church_admin_debug( $what);
    $out='';
    $text='';
    $apikey=get_option('church_admin_google_api_key');
    $privacy = '<p>'.esc_html( __('For personal data security, the shortcode and block only show to logged in users. To show to everybody add loggedin=FALSE to the shortcode or uncheck the option on the block settings','church-admin' ) ).'</p>';
        switch( $what)
        {
            default:
            case 'address-list':
            case 'view-address-list':
                $title=__('Address list','church-admin');
                $shortcode=TRUE;
                $text.='<p>'.esc_html( __('You can show the address list on your website using the [church_admin type="address-list"] shortcode. Options include map=1 if you have a Google API key saved, photo=1 to display any photos, member_type_id=1,2,3 to only show member types 1,2 and 3.','church-admin' ) ).'</p><p>'.esc_html( __('There is also a "Church Admin Address" Block which will show the address list. All the options are also available','church-admin' ) ).'</p>';
                $text.=$privacy;
                $text.='<ul><li><a href="https://www.churchadminplugin.com/tutorials/what-is-a-household/">'.esc_html( __('What is a household in Church Admin?','church-admin' ) ).'</a></li><li><a href="https://www.churchadminplugin.com/tutorials/addingediting-a-household/">'.esc_html( __('How to add or edit a household','church-admin' ) ).'</a></li><li><a href="https://www.churchadminplugin.com/tutorials/google-api-key/">'.esc_html( __('How to get a Google maps API key','church-admin' ) ).'</a></li></ul>';
            break;
            case 'import-csv':
                $title= __('Import CSV','church-admin');
                $text.='<p>'.esc_html( __('Create a csv spreadsheet with each row as one person and a column header row. You can create a CSV file in your favourite Office software program and save as "Comma Separated Values". Please enclose column items in double quotes, especially if they have a comma! The columns can be and or all of first name, middle name, nickname, prefix, last name, gender, marital status, date of birth, email, cellphone, home phone, address ( as one column or split as address, city, state, postal code), small group name, member type, privacy and people type. You can also have up to 5 custom fields.','church-admin' ) ).'</p>
                <p>'.esc_html( __('One column can be ministries, please use : to separate the ministries. e.g.Pastor:Elder:Worship Leader','church-admin' ) ).'</p>
                <p>'.esc_html( __('Date of birth works most reliably as yyyy-mm-dd e.g. 1970-03-08 for 8th March 1970','church-admin' ) ).'</p>
                <p>'.esc_html( __('For people types, these values are recognised...Adult, Child, Teenager','church-admin' ) ).'</p>
                <p><a href="https://www.churchadminplugin.com/tutorials/import-address-list-csv/">'.esc_html( __('Helpful tutorial','church-admin' ) ).'</a></p>';
            break;
            case 'people-map':
                $title= __('People Map','church-admin');
                
                $shortcode=TRUE;
                
                if(!$apikey)$text.='<p>'.esc_html( __('The people map requires a Google Maps API key to work.','church-admin'));
                $text.='<p>'.esc_html( __('The people map shows a map with pins for where all your address list people live. Only people who have their addresses "geocoded" will show on the map.','church-admin' ) ).'</p>';
                $text.='<p>'.esc_html( __('It is centred on your church site address','church-admin' ) ).'</p>';
                $text='<p>'.esc_html( __('You can show the people map on your website with the [church_admin_map member_type_id="#" zoom="13" small_group="1"]- zoom is Google map zoom level, small_group=1 for different colours for small groups, 0 for all in red. If you set member_type_id it restricts which member_types are shown','church-admin' ) ).'</p><p>'.esc_html( __('There is also a "Church Admin Member Map" Block which will show the address list. All the options are also available','church-admin' ) ).'</p>';
                $text.='<ul><li><a href="https://www.churchadminplugin.com/tutorials/google-api-key/">'.esc_html( __('How to get a Google maps API key','church-admin' ) ).'</a></li><li><a href="https://www.churchadminplugin.com/tutorials/geolocating//">'.esc_html( __('How to geocode a household','church-admin' ) ).'</a></li><li><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=bulk-geocode&section=people','bulk-geocode').'">'.esc_html( __('Geocode all your households','church-admin' ) ).'</li></ul>';
            break; 
            case 'add-household':
                $title=__('Add a household','church-admin');
                $text='<p>'.esc_html( __('Adding households is the very core of the plugin','church-admin' ) ).'</p>';

                $text.='<ul><li><a href="https://www.churchadminplugin.com/tutorials/what-is-a-household/">'.esc_html( __('What is a household in Church Admin?','church-admin' ) ).'</a></li><li><a href="https://www.churchadminplugin.com/tutorials/addingediting-a-household/">'.esc_html( __('How to add or edit a household','church-admin' ) ).'</a></li></ul>';
                $apikey=get_option('church_admin_google_api_key');
                if(!$apikey)
                {
                    $text.='<p>'.esc_html( __('To activate mapping for address, you will need a Google Maps API key to work.','church-admin'));
                    $text.='<p><a href="https://www.churchadminplugin.com/tutorials/google-api-key/">'.esc_html( __('How to get a Google maps API key','church-admin' ) ).'</a></p>';
                }
                $text.='<p>'.esc_html( __('You can show the address list on your website using the [church_admin type="address-list"] shortcode. Options include map=1 if you have a Google API key saved, photo=1 to display any photos, member_type_id=1,2,3 to only show member types 1,2 and 3.','church-admin' ) ).'</p><p>'.esc_html( __('There is also a "Church Admin Address" Block which will show the address list. All the options are also available','church-admin' ) ).'</p>';
                $text.=$privacy;
            break;
            case 'export-pdf':
                $title="Export PDF";
                $text='<p>'.esc_html( __('Just select which member types you want to include in the PDF','church-admin' ) ).'</p>';
            break;
            case 'member-types':
            case 'add-member-types':
            case 'edit-member-type':   
            case 'delete-member-type':     
                $title=__("Member types",'church-admin');
                $text='<p>'.esc_html( __('Member types allow you to set how connected people are to the church - the default ones are Mailing List and Member. Adding more will allow you to create a membership process and follow-up funnels as well as targeting communications.','church-admin' ) ).'</p>';
                $text.='<ul><li><a href="https://www.churchadminplugin.com/tutorials/member-types/">'.esc_html( __('Member types tutorial','church-admin' ) ).'</a></li><li><a href="https://www.churchadminplugin.com/tutorials/follow-up-funnels/">'.esc_html( __('Follow up funnels','church-admin' ) ).'</a></li></ul>';
            break;   
            case 'custom-fields':

                $title=__("Custom fields",'church-admin');
                $text='<p>'.esc_html( __('Custom fields allows you to add extra fields to the directory - they can be a date or text form field. They are added to the forms for editing households in the admin are and the registration edit form once people are confirmed users','church-admin' ) ).'</p>';
            break;
            case 'create-users':
                $title=__("Create users",'church-admin');
                $text='<p>'.esc_html( __('It is really helpful for everyone in your directory who has an email address to have a user account. Then they can login to access the address list, edit their entry and view other information with personal data like the schedule. This page allows you to giver everyone with an email address an account. It will check they have confirmed their email address, check if they already have one and create one if needed. An email is sent to them and copied to the admin email address. You can adjust the content of the email at on the settings page.','church-admin' ) ).' <a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=settings&section=settings','settings').'">'.esc_html( __('Click here','church-admin' ) ).'</p>'; 
            break;
            case 'bulk-geocode':
                $title=__("Bulk Geocode",'church-admin');
                $text.='<p>'.esc_html( __('Bulk geocoding sets the latitude and longitude for directory entries, so mapping works correctly.','church-admin' ) ).'</p>';
                if(!$apikey)
                {
                    $text.='<p>'.esc_html( __('To activate bulk geocoding, you will need a Google Maps API key to work.','church-admin'));
                    $text.='<p><a href="https://www.churchadminplugin.com/tutorials/google-api-key/">'.esc_html( __('How to get a Google maps API key','church-admin' ) ).'</a></p>';
                }
                else $text.='<p>'.esc_html( __('Google allows 10 addresses to be processed at a time, click Step 1 to get the next ten addresses and then Step 2 to save the results.','church-admin' ) ).'</p>';
            break;
            case 'download-csv':
                $title=__("Download a CSV or mailing labels",'church-admin');
                $text.='<p>'.esc_html( __('First check who you want to be on the CSV or mailing label, then select which you want at the bottom of the screen and click download','church-admin' ) ).'<br>'.esc_html( __('You can adjust the content of the email at on the settings page.','church-admin' ) ).' <a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=settings&section=settings','settings').'">'.esc_html( __('Click here','church-admin' ) ).'</p>'; ;
            break;
            case 'recent-people':
                $title=__("Download a CSV or mailing labels",'church-admin');
                $text.='<p>'.esc_html( __('This page shows the latest edits and adds to the directory. You can also sign up follow up funnels if you have them set up and send them out.','church-admin' ) ).'</p>';
                $text.='<p>'.esc_html( __('There is a block and shortcode for recent people activity [church_admin type="recent"]','church-admin' ) ).'</p>';
                $shortcode=TRUE;
                $text.='<ul><li><a href="https://www.churchadminplugin.com/tutorials/follow-up-funnels/">'.esc_html( __('Follow up funnels','church-admin' ) ).'</a></li>
                <li><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=funnel&section=follow-up','follow-up').'">'.esc_html( __('Set up follow up funnels','church-admin')).'</a></li></ul>';
            break;
            case 'check-duplicates':
                $title=__("Download a CSV or mailing labels",'church-admin');
                $text.='<p>'.esc_html( __('Any possible duplicates are shown in their households. Be careful which ones you deletes - whether there is a user accounts may help you decide.','church-admin' ) ).'</p>';
            break;
            case 'birthdays':
                $shortcode=TRUE;
                $title=__("Download a CSV or mailing labels",'church-admin');
                $text.='<p>'.esc_html( __("If a person's date of birth has been set and their birthday is within the next month, it will be shown here",'church-admin' ) ).'</p>';
                $text.='<p>'.esc_html( __('There is a block and shortcode for birthdays [church_admin type="birthdays" days=31]','church-admin' ) ).'</p>';
            break; 
            case 'photo-permissions':
                
                $title=__("Photo permissions",'church-admin');
                $text.='<p>'.esc_html( __("Respecting privacy is important in a digital world! This page shows how has allowed photos to be used and who hasn't.",'church-admin' ) ).'</p>';
            break; 
            case 'everyone-visible':
                $title=__("Everyone visible",'church-admin');
                $text.='<p>'.esc_html( __("This reset everyone's privacy setting to show me on the directory.",'church-admin' ) ).'</p>';
            break;  
            case 'delete-all':
                $title=__("Delete everyone",'church-admin');
                $text.='<p>'.esc_html( __("This is the nuclear option to delete everyone from the directory.",'church-admin' ) ).'</p>';
            break;    
            case 'giving':
            case 'edit-gift':
            case 'delete-gift':
                $title=__("Giving",'church-admin');
                $shortcode=TRUE;
                $text.='<p>'.esc_html( __("This is the giving module. Churches that have subscribed to the app, will be able to put online PayPal giving forms on the website",'church-admin' ) ).'</p>';
                $licence_level = church_admin_app_licence_check(); 
	            if($licence_level!='premium')
                {
                    $text.='<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=upgrade&section=app','upgrade').'">'.esc_html( __('Buy the app to unlock online giving module access','church-admin')).'</a></p>';
                }
                $text.='<p>'.esc_html( __('Once online giving is unlocked you can use the giving widget or block or shortcode [church_admin type="giving"] on the website','church-admin' ) ).'</p>';

                $text.='<p><a href="https://www.churchadminplugin.com/tutorials/giving/">'.esc_html( __('Tutorial on setting up giving','church-admin')).'</a></p>';
                $text.='<p><a href="https://youtu.be/PrGkPCO0N0g">'.esc_html( __('YouTube video on setting giving up','church-admin' ) ).'</a></p>';
                $text.='<h2>UK Churches</h2><p>I have just checked with a churches accountancy specialist, <a href="https://marshsolutions.uk">Chris Marsh</a>, to clarify claiming gift aid on Paypal donations. Here is what he said...<br>In the UK, the Gift Aid would be claimed on the Gross (full) donation value. The fees are a separate expense where the charge value is based on the donation value. When reporting the income, it should be the Gross donations value going to the income and the charge going to the expenditure, which the net amount then reconciles to the balance change.</p><p>You can add Gift Aid tracking in the Setup PayPal details box above</p>';
            
            break; 
            case 'funds':
            case 'edit-fund':
            case 'delete-fund':        
                $title=__("Funds",'church-admin');
                $text.='<p>'.esc_html( __("You can set up funds to direct giving. Deleting a fund doesn't change previously recorded giving for that fund",'church-admin' ) ).'</p>';
                $licence_level = church_admin_app_licence_check(); 
               if($licence_level!='premium')
                {
                    $text.='<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=upgrade&section=app','upgrade').'">'.esc_html( __('Buy the app to unlock online giving module access','church-admin')).'</a></p>';
                }
                $text.='<p><a href="https://www.churchadminplugin.com/tutorials/giving/">'.esc_html( __('Tutorial on setting up giving','church-admin')).'</a></p>';
            break;
            case 'childrens-work':
            case 'edit_kidswork':
            case 'delete_kidswork':
                $title=__("Children's work",'church-admin');
                $text.='<p>'.esc_html( __("The children tab allows you to create age range groups for your children’s ministry. If dates of birth are stored in the directory, then children are automatically sorted into the correct group, unless you check the override option.",'church-admin' ) ).'</p>';
            break;
            case 'childrens-work-pdf':
                $title=__("Children's work PDF",'church-admin');
                $text.='<p>'.esc_html( __("The PDF will show which children are in which children's group",'church-admin' ) ).'</p>';
            break;
            case 'childrens-work-checkin-pdf':
                $title=__("Children's work checkin PDF",'church-admin');
                $text.='<p>'.esc_html( __("This produces a PDF sign-in and sign-out sheet for your selected children's groups and service",'church-admin' ) ).'</p>';
            break; 
            case 'safeguarding':
                $title=__("Safeguarding",'church-admin');
                $text.='<p>'.esc_html( __("I only have statutory requirements for the UK and Australia in the plugin currently. Firstly select which country's arrangements. Then which ministries require safeguarding checks. You can edit the people in those ministries to chaeck what stage their are at for safer recruitment of staffing and volunteers",'church-admin' ) ).'<p>';

            break; 
            case 'classes':
            case 'edit_class':
            case 'delete_class':
                $title=__("Classes",'church-admin');
                $text.='<p>'.esc_html( __("Classes is for things like pre-service Bible studies, the Alpha course, group leader training and so on. Once you have created a class, the shortcode for signing up to the class can be added to your website",'church-admin' ) ).'</p>';
                $shortcode=TRUE;
            break;    
            case 'events':           
            case 'edit_event':
            case 'delete_event':
                $title=__('Events','church-admin');
                $text.='<p>'.esc_html( __("Events  is a booking system for ticketed events (which can be paid for events using PayPal for site that have the app)",'church-admin')).'</p>';
                $text.='<p>'.esc_html( __("Starting by clicking Add Event and adding some basic details, then click save. At the next step you can create some different ticket types. The shortcode for putting on your website is shown on the list of events. Use the event id if you are using a block to display the event.",'church-admin' ) ).'</p>';
                $shortcode=TRUE;
                $licence_level = church_admin_app_licence_check(); 
                if($licence_level!='premium')
                {
                    $text.='<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=upgrade&section=app','upgrade').'">'.esc_html( __('Buy the app to unlock paid tickets','church-admin')).'</a></p>';
                }
                
            break;
            case 'edit-booking':
                $title=__('Edit event booking','church-admin');
                $text.='<p>'.esc_html( __("On this page you can edit a booking",'church-admin' ) ).'</p>';
            break;  
            case 'attendance':
            case 'edit-attendance':
                $title=__('Attendance','church-admin');
                $text.='<p>'.esc_html( __("This page shows recorded global attendance for services, classes and groups - use the dropdown to pick.",'church-admin' ) ).'</p>';
                $shortcode=TRUE;
                $text.='<p>'.esc_html( __('[church_admin type="graph"] shows the graph on your website page or post','church-admin' ) ).'</p>';
            break;  
            case'weeks-attendance':
                $title=__('This weeks Attendance','church-admin');
                $text.='<p>'.esc_html( __("This allows you to record attendance of services, classes and groups between this Monday and Sunday in one go.",'church-admin' ) ).'</p>';
            
            break;  
            case 'individual-attendance':
                $title=__('Individual Attendance','church-admin');
                $text.='<p>'.esc_html( __("Individual attendance allows you to record individual attendance of services, classes and groups.",'church-admin' ) ).'</p>';
            break; 
            case 'follow-up':
            case 'add-funnel':    
                $title=__('Follow up','church-admin');
                $text.='<p><a href="https://www.churchadminplugin.com/tutorials/follow-up-funnels/">'.esc_html( __('tutorial on follow up funnels','church-admin' ) ).'</a></p>';
                $text.='<p>'.esc_html( __('There is a shortcode to allow your follow up team access on the website [church_admin type="follow-up"]','church-admin')).'</p>';
                $shortcode=TRUE;
            break;
            
            case 'units':
                $title=__('Units','church-admin');
            break; 
            case 'show-groups':
            case 'edit-group':
            case 'smallgroups-cleanup':
            case 'small-group-structure':
            case 'oversight-list': 
            case 'smaalgroup-show-pdf-form':
            case 'small-group-metrics':             
                $title=__('Small groups','church-admin');
                $text.='<p>'.esc_html( __("",'church-admin' ) ).'</p>';
            break;
            
            case 'delete-all-groups':
                $title=__('Delete all the groups','church-admin');
                $text.='<p>'.esc_html( __('This deletes al groups and oversight.','church-admin' ) ).'</p>';
            break;
            case 'services':
            case 'services-list':
            
                $title=__('Services','church-admin');
                $text.='<p>'.esc_html( __('Shortcodes for displaying service details, booking form and schedule are shown in the table on the services page','church-admin' ) ).'</p>';
            break;
            case 'service-prebookings':
                $title=__('Service pre-bookings','church-admin');
                $text.='<p>'.esc_html( __('Here you can check out your service prebookings','church-admin' ) ).'</p>';
                $text.='<p><a href="https://www.YouTube.com/watch?v=rbrqh1pEjck">'.esc_html( __('YouTube tutorial','church-admin' ) ).'</a></p>';
                $shortcode=TRUE;
                $text.='<p><a href="https://www.churchadminplugin.com/tutorials/covid-19-church-service-bookings/">'.esc_html( __('Tutorial on setting up service pre-bookings','church-admin' ) ).'</a></p>';
            break;
            case 'edit-service':
                $title=__('Edit Services','church-admin');
                $text.='<p>'.esc_html( __("Most of the form is self-explanatory! If you want to setup pre-booking form then the service must be connected to a calendar entry (even if you don't use the calendar on your site. You can either pick an event from the dropdown or check create a new event. Prebooking can be set up either by total max attendance, or if you arrange your venue in bubbles of seating (this was written in the middle of theCovid-19 global pandemic) you can specify maximum number of bubbles and maximum number in a bubble. Once you have saved the service, the shortcodes needed are shown for you.",'church-admin' ) ).'</p>';
                $shortcode=TRUE;
                $text.='<p><a href="https://www.churchadminplugin.com/tutorials/covid-19-church-service-bookings/">'.esc_html( __('Tutorial on setting up service pre-bookings','church-admin' ) ).'</a></p>';
            case 'sites':
                case 'site-list':
            case 'edit_site':
            case 'delete_site':
                $title=__('Sites','church-admin');
                $text.='<p>'.esc_html( __('You can set up multiple sites for your church services here.','church-admin' ) ).'</p>';
                $text.='<p>'.esc_html( __('If you have Google Map API setup, then geocoding the map for your main venue is important, as that is the centering for other maps.','church-admin')).'</p>';       
                if(!$apikey)
                {
                    $text.='<p>'.esc_html( __('To activate bulk geocoding, you will need a Google Maps API key to work.','church-admin'));
                    $text.='<p><a href="https://www.churchadminplugin.com/tutorials/google-api-key/">'.esc_html( __('How to get a Google maps API key','church-admin' ) ).'</a></p>';
                }
            break;
            case 'sessions':
            case 'edit-session':
            case 'delete-session':        
                $title=__('Sessions','church-admin');
                $text.='<p>'.esc_html( __('Sessions allows you to track small groups','church-admin' ) ).'</p>';
            break;
            case 'comms':
                $title=__('Communications','church-admin');
            break;
            case 'push':
                $title=__('Push notifications','church-admin');
                $text.='<p>'.esc_html( __('The app allows you to send push notifications to your app users either to all of them, or by using the filter. If you use filter, only logged in app users will get the message.','church-admin' ) ).'</p>';
                $licence_level = church_admin_app_licence_check(); 
                if($licence_level!='premium')
                {
                    $text.='<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=upgrade&section=app','upgrade').'">'.esc_html( __('Buy the app to unlock online giving module access','church-admin')).'</a></p>';
                }
            break;
            case 'send-email':
                $title=__('Push notifications','church-admin');
                $text.='<p>'.esc_html( __("Check your server sends email happily first by sending yourself a test message. If it doesn’t work, do some tweaks in the Settings area.Sending email from websites is notorious. Lots of ISP’s block emails they think have come from websites as spam. We have set up a MailChimp account (free for under 2000 contacts and 12,000 emails per month ) and sync’d Church Admin with MailChimp. MailChimp does all the heavy lifting to make sure your email will get through. But if you don’t want to do that and the test email works, then go ahead with the Send Bulk Email link. The first part allows entering names or using the filter to find recipients, then fill out Subject, From name and email (Or click Use me!) and upload any attachments and the finally create your message",'church-admin' ) ).'</p>';
            break;
            case 'sms-settings':
                case 'twilio-replies':
                $title=__('SMS settings','church-admin');
                $text.='<p><a href="https://www.churchadminplugin.com/tutorials/twilio-incoming-numbers/">'.esc_html( __('Details on how to set up bulk SMS with Twilio','church-admin')).'</a></p>';
            break;
            case 'send-sms':
                $title=__('SMS send','church-admin');
                $text.='<p>'.esc_html( __("Use the filters to choose recipients and the message and click Send",'church-admin'));
                $text.='<p><a href="https://www.churchadminplugin.com/tutorials/twilio-incoming-numbers/">'.esc_html( __('Details on how to set up bulk SMS with Twilio','church-admin')).'</a></p>';
            break;
            case 'test-email':
                $title=__('Test email','church-admin');
                $text.='<p>'.esc_html( __("This sends a test email to your admin email account and shows any errors",'church-admin' ) ).'</p>';
            break;
            case 'email-settings':
                $title=__('Email settings','church-admin');
                $text.='<p>'.esc_html( __("Many web hosting companies restrict how many emails can be sent per hour to protect themselves from spammers using websites to send millions of junk email. If your church directory has more than 100 email addresses you will need to set up queueing. Depending on your server setup you have three options available. ",'church-admin' ) ).'</p>';
                $text.='<ul><li>'.esc_html( __("The wp-cron option uses site visits to trigger sending a batch every 15mins. If your site is low traffic you will need to visit!",'church-admin' ) ).'</li>';
                $text.='<li>'.esc_html( __("Cron is available on Linux servers and is a bit more advanced but trustworthy!",'church-admin' ) ).'</li>';
                $text.='<p>'.esc_html( __("When you select that option and click save you will get a link to instructions.To setup a cron you need to login into your hosting company and find Cron jobs. The command is curl -silent DOMAINNAME/wp-admin/admin-ajax.php?action=church_admin_cronemail",'church-admin' ) ).'</p>';
            break;
            case 'smtp-settings':
                $title=__('Email server settings','church-admin');
            break;
           
            case 'view-rota':
                $title=__('Schedule','church-admin');
            break;
            case 'rota':
                $title=__('Schedule','church-admin');
                $text='<p>'.esc_html( __('Create schedule for your services.','church-admin'));
                $text.='<p><a href="https://www.churchadminplugin.com/tutorials/rota-schedule/">'.esc_html( __('Tutorial page','church-admin' ) ).'</a></p>';
            break;
            case 'rota-settings':
                $title=__('Schedule jobs','church-admin');
            break;
            case 'auto-email-rota':
                $title=__('Automatically email schedule each week','church-admin');
            break;
            case 'sms-rota':
                $title=__('SMS schedule out','church-admin');
            break;
            case 'pdf-rota':
                $title=__('PDF of schedule','church-admin');
            break;
            case 'csv-rota':
                $title=__('CSV of schedule','church-admin');
            break;
            case 'calendar':
            case 'add-calendar':
                $shortcode=TRUE;
                $title=__('Calendar','church-admin');
            break;
            case 'categories':
            case 'edit-category':
                $title=__('Categories','church-admin');
            break;
            case 'facilities':
            case 'edit-facility':
            case 'delete-facility':
                    $title=__('Facilities','church-admin');
                    $text.='<p>'.esc_html( __("You can create facilities for things like rooms, or equipment and use them as a booking calendar",'church-admin' ) ).'</p>';
                    $shortcode=TRUE;
                    $text.='<p>'.esc_html( __('The shortcode for a facility calendar is [church_admin type="calendar" facilities_id="#"] where # is you facility id. They are shown below!','church-admin' ) ).'</p>';
            break;
            case 'facility-bookings':
                $shortcode=TRUE;
                $title=__('Facility Bookings','church-admin');
                $text.='<p>'.esc_html( __('Choose your facility from the dropdown and a calendar for that facility is shown','church-admin' ) ).'</p>';
                $text.='<p>'.esc_html( __('The shortcode for a facility calendar is [church_admin type="calendar" facilities_id="#"] where # is you facility id. They current facility shortcode is shown below!','church-admin' ) ).'</p>';
            break;
            case 'ministries-list':
            case 'edit_ministry':
            case 'delete_ministry':
                $title=__('Ministries','church-admin');
            break;
            case 'volunteers':
                $title=__('Volunteers','church-admin');
            break;
            case 'media':
                $title=__('Media','church-admin');
                $text.='<p>'.esc_html( __('This page displays all your sermons, which can be viewed on your website and on the app','church-admin' ) ).'</p>';
                $text.='<p>'.esc_html( __('There is a shortcode and block to display them on your website - [church_admin type="podcast"]','church-admin' ) ).'</p>';
                $licence_level = church_admin_app_licence_check(); 
                if($licence_level!='premium')
                {
                   $text.='<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=upgrade&section=app','upgrade').'">'.esc_html( __('Buy the app to unlock online giving module access','church-admin')).'</a></p>';
                }
                $shortcode=TRUE;
            break;
            case 'upload-mp3':
                $title=__('Upload sermon media','church-admin');
                $text.='<p>'.esc_html( __('This is the normal way to add sermon media like mp3s and your YouTube/Vimeo links.','church-admin' ) ).'</p>';
                $text.='<p>'.esc_html( __('There is a shortcode and block to display them on your website - [church_admin type="podcast"]','church-admin' ) ).'</p>';
                $shortcode=TRUE;

            break;
            case 'migrate_advanced_sermons':
                $title=__('Migrate from Advanced Sermons plugin','church-admin');
                $text.='<p>'.esc_html( __('This will try to import sermons from the "Advanced Sermons" plugin into Church Admin','church-admin' ) ).'</p>';
            break;
            case 'migrate_sermon_manager':
                $title=__('Migrate from "Sermon manager" plugin','church-admin');
                $text.='<p>'.esc_html( __('This will try to import sermons from the "Sermon manager" plugin into Church Admin','church-admin' ) ).'</p>';
            break;
            case 'migrate_sermon_browser':
                $title=__('Migrate from "Sermon browser" plugin','church-admin');
                $text.='<p>'.esc_html( __('This will try to import sermons from the "Sermon browser" plugin into Church Admin','church-admin' ) ).'</p>';
            break;
            case 'check-media-files':
                $title=__('Add already uploaded media','church-admin');
                $text.='<p>'.esc_html( __('This method of adding sermon media looks in your wp-content/uploads/sermons directory for already uploaded files. Useful if you mp3s are too large to upload via the browser and you can FTP them.','church-admin' ) ).'</p>';
            break;
            case 'sermon-series':
            case 'edit-sermon-series':
            case 'delete-sermon-series':
                $title=__('Sermon series','church-admin');
                $text.='<p>'.esc_html( __('You can set up sermon series to order your sermons better. Uploading an image best (660px by 400px) allows you to use the [church_admin type="sermon-series" cols="3" sermon_page="#"] where # is the URL of your main sermons page. That shortcode displays the sermon series images in a clickable form.','church-admin' ) ).'</p>';
                $shortcode=TRUE;
            break;
            case 'app':
            case 'app-menu':
            case 'app-settings':
                $title=__('App','church-admin');
            break;  
            case 'app-visits':
                $title=__('App visits','church-admin');   
                $text.='<p>'.esc_html( __('This page shows metrics for your various app pages.','church-admin' ) ).'</p>';
                $licence_level = church_admin_app_licence_check(); 
                if($licence_level!='premium')
                {
                    $text.='<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=upgrade&section=app','upgrade').'">'.esc_html( __('Buy the app to unlock online giving module access','church-admin')).'</a></p>';
                }
            break;
            case 'app-users':
                $title=__('App user','church-admin');   
                $text.='<p>'.esc_html( __('This page shows who is logged into the app and what the last page viewed was.','church-admin' ) ).'</p>';
                $licence_level = church_admin_app_licence_check(); 
                if($licence_level!='premium')
                {
                    $text.='<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=upgrade&section=app','upgrade').'">'.esc_html( __('Buy the app to unlock online giving module access','church-admin')).'</a></p>';
                }
            break;
            case 'bible-version':
                $title=__('Bible version','church-admin');
                $text.='<p>'.esc_html( __('Set the default Bible version for the app Bible readings','church-admin' ) ).'</p>';
                $licence_level = church_admin_app_licence_check(); 
                if($licence_level!='premium')
                {
                    $text.='<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=upgrade&section=app','upgrade').'">'.esc_html( __('Buy the app to unlock online giving module access','church-admin')).'</a></p>';
                }
            break;
            case 'settings':
                $title=__('Settings','church-admin');
                $text.'<p>'.esc_html( __('Lots of settings here!','church-admin' ) ).'</p>';
                $text.'<ul><li>'.esc_html( __('Email address for prayer request moderation: is the email address that prayer requests get sent too.','church-admin' ) ).'</li>';
                $text.'<li>'.esc_html( __('Stop push notification and email send on content publishing: allows app subscribers to turn off pus notifications of new blog posts globally (can be done on a post by post basis too).','church-admin' ) ).'</li>';
                $text.'<li>'.esc_html( __('Admin approval of new users required before account given: if you are using [church_admin_register], by default a new registration gets a subscriber level account that allows them to edit themselves. Checking this stops that, so you can moderate more easily','church-admin' ) ).'</li>';
                $text.='<li>'.esc_html( __('Redirect page after login by "subscriber": if a user tries to login on a WordPress admin screen, selecting a page here prevents them going to the default  WordPress user page.','church-admin' ) ).'</li>';
                $text.='<li>'.esc_html( __('Explanation for volunteer shortcode: if you use the volunteer shortcode which is [church_admin type="volunteer"], to allow people to sign up to serve in different ministries, you can add some explainer text here','church-admin' ) ).'</li>';
                $text.='<li>'.esc_html( __('Acts of courage post type: Acts of courage is a way your users can add testimonies of having a go sharing the gospel or praying for the sick. Checking this turns it on.','church-admin' ) ).'</li>';
                $text.='<li>'.esc_html( __('Make acts of courage viewable by logged in users only: makes the acts of courage testimonies login only','church-admin' ) ).'</li>';
                $text.='<li>'.esc_html( __('Message for Acts of Courage submission form: explainer text before the acts of courage form, which is automatically appended to an Acts of Courage archive page','church-admin' ) ).'</li>';
                $text.='<li><a href="https://www.churchadminplugin.com/tutorials/menu-items-prayer-requests-bible-readings/">'.esc_html( __('How to add prayer requests or acts of courage to your website menu','church-admin' ) ).'</a></li>';
                $text.='<li>'.esc_html( __('No Bible readings custom post type: here you can turn off Bible readings','church-admin' ) ).'</li>';
                $text.='<li>'.esc_html( __('No prayer requests custom post type: here you can turn off Prayer requests','church-admin' ) ).'</li>';
                $text.='<li>'.esc_html( __('Make prayer requests viewable by logged in users only: makes prayer requests viewing login only','church-admin' ) ).'</li>';
                $text.='<li>'.esc_html( __('Message for Prayer request submission form: explainer text before the prayer request form, which is automatically appended to an Prayer request archive page','church-admin' ) ).'</li>';
                $text.='<li>'.esc_html( __('GDPR message template: if you send out an email to confirm current users are happy with their personal data on the site in a secure way - this is the template for the email.','church-admin' ) ).'</li>';
                $text.='<li>'.esc_html( __('New user email subject: Once a new user has confirmed their email after registering using the form produced by [church_admin_register] they get an email with their user account details. This the subject title.','church-admin' ) ).'</li>';
                $text.='<li>'.esc_html( __('Created user email message template: and this is the email template','church-admin' ) ).'</li>';
                $text.='<li>'.esc_html( __('Use prefix for names: allows for prefixes like "van der" and "von" etc to be stored separately from the last name, to allow for contined alphabetic sorting by last name','church-admin' ) ).'</li>';
                $text.='<li>'.esc_html( __('Use middle name for names: adds a middle name field.','church-admin' ) ).'</li>';
                $text.='<li>'.esc_html( __('Add nickname for names: adds a nickname form field.','church-admin' ) ).'</li>';
                $text.='<li>'.esc_html( __('Google Maps API key: this enables mapping features','church-admin' ) ).'</li>';
                $text.='<li><a href="https://www.churchadminplugin.com/tutorials/google-api-key/">'.esc_html( __('How to get a Google maps API key','church-admin' ) ).'</a></li>';
                $text.='<li>'.esc_html( __('Directory records per page: change how many records are shown in the admin area from the default 20','church-admin' ) ).'</li>';
                $text.='<li>'.esc_html( __('PDF Page Size: choose which PDF size to use.','church-admin' ) ).'</li>';
                $text.='<li>'.esc_html( __('Avery Label: choose which label size to use.','church-admin' ) ).'</li>';
            break;
            case 'modules':
                $title=__('Modules','church-admin');
                $text.='<p>'.esc_html( __('Here you can choose which modules are visible in the Church Admin menu','church-admin' ) ).'</p>';
            break;
            case 'filters':
                $title=__('Filters','church-admin');
                $text.='<p>'.esc_html( __('Here you can choose which filters are visible in the Church Admin admin area for people section, push, SMS, email, PDFs and labels.','church-admin' ) ).'</p>';
            break;
            case 'restrict-access':
                $title=__('Restrict Access','church-admin');
                $text.='<p>'.esc_html( __('The address list is viewable to people with same member types as specified in the shortcode or block. Here you can additionally restrict access from specified people.','church-admin' ) ).'</p>';
            break;
            case 'people-types':
            case 'edit_people_type':
            case'delete_people_type':
                $title=__('People types','church-admin');
                $text.='<p>'.esc_html( __('The default peple types are adult, teenager and child. Best not to change them if you want the address list to work! You cannot delete adult and child, but you can rename them.','church-admin' ) ).'</p>';
            break;
            case 'marital-status':
            case 'edit_marital_status':
            case'delete_marital_status':
                $title=__('Marital Status');
                $text.='<p>'.esc_html( __('Here you can adjust which marital statuses are shown in the drop down','church-admin' ) ).'</p>';
            break;
            case 'debug-log':
                $title=__('Debug log','church-admin');
                $text='<p>'.esc_html( __('Here you can download a debug log if you have debugging enabled.','church-admin' ) ).'</p>';
            break;
            case 'installation-errord':
                $title=__('Installation errors','church-admin');
                $text='<p>'.esc_html( __('Here you can view any installation errors.','church-admin' ) ).'</p>';
            break;

            case 'permissions':
                $title=__('Permissions','church-admin');
            break;
            case 'roles':
                $title=__('Roles','church-admin');
            break;
            case 'replicate-roles':
                $title=__('Replicate roles','church-admin');
                $text.='The replicate roles function replicates Church Admin Ministries to WordPress roles.
                To use this you first need to create the matching role in WordPress, there any many plugins that do this including User Role Editor and Advanced Access Manager (AAM). Once you have created a matching word press role for the Ministries you want to replicate just use the replicate roles function to add people in your ministries to the equivalent WordPress role.
                Why would you do this? It allows you then to use other plugins to restrict access based on the roles, for example if you have a “service leader” role and you want a page only service leaders see when they logon you can so this by.
                Use church admin to create a service leader ministry and add you service leaders.
                Manually create a WordPress role called “Service Leader”, this must match exactly the name of the ministry. Use the Replicate Roles function to populate your new Role with those people you assigned to the ministry. Now use an access plugin to apply the controls you want. AAM does this very easily.
                There are a couple of restrictions
                This function old “adds” people, so if you remove anyone from a ministry you have to manually delete them from the role – or deleted everyone and then use the replicate roles function again.
                As mentioned, you have to create the roles manually with another plugin.
                Once pressed you see this kind of output
                Andy Moyle already has role PCC (pcc).
                Unable to add Role (WebTeam) to user Andy Moyle. The role was not found in wordpress - please add this manually if required.
                Andy Moyle already has role Church Wardens (church_wardens).
                Adding role Tea Room (tea_room) to Andy Moyle';
            break;
            case 'shortcodes':
                $title=__('Shortcodes','church-admin');
                $text.='<p>'.esc_html( __('A full list of shortcodes you can use on your site','church-admin' ) ).'</p>';
                $shortcode=TRUE;
            break;
        }
        
        
        
        
        if ( empty( $text) )$text='<p>'.esc_html( __('Help text coming soon','church-admin' ) ).'</p>';
        
        $out='<div class="notice notice-info">';
        $out.='<h2><span class="ca-dashicons dashicons dashicons-info-outline"></span> '.esc_html(sprintf(__('Help for %1$s','church-admin' ) ,$title ) ) .'</h2>';
        $out.=$text;
        if( $shortcode)$out.='<p><a href="https://www.churchadminplugin.com/tutorials/showing-church-admin-elements-on-your-website/">'.esc_html( __('How to use shortcodes and blocks to display content on your website','church-admin' ) ).'</a></p>';
        
        $out.='<p><button class="ca-hide-help button-primary">'.esc_html( __('Stop showing this help','church-admin' ) ).'</button></p>';
        $out.='</div>';

        return $out;


}
