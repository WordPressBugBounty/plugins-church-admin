<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


function church_admin_cancel_app()
{
	$app_id=get_option('church_admin_app_id');
	$url = esc_url('https://www.churchadminplugin.com/?app_cancel='.md5(site_url() ).'&app_id='.md5( $app_id));
	$result = wp_remote_get( $url );
	if(is_array( $result) && !is_wp_error( $result) && $result['body']=='done' )
	{
		delete_option('church_admin_app_new_licence');
		delete_option('church_admin_app_id');
	}

}

function church_admin_buy_app()
{
    global $church_admin_version;
    //no licence yet

	
	echo'<h2>'.esc_html(__('Church Admin App','church-admin') ).'</h2>';
	echo'<img src="'.plugins_url('/images/app-images.png',dirname(__FILE__) ).'" class="aligncenter" alt="iPhone App Mockup">';
	echo'<p>'.esc_html(__("You may have the best organised church on earth, but all that organisation goes for nothing if you don’t communicate well.
	In 20 years of church leadership one of the biggest lessons I have learnt is that you cannot over-communicate.The Church Admin plugin has tools for emailing and sending SMS – the new app puts the most powerful features of the plugin in your congregation’s hands and pockets with a free download.",'church-admin') ).'</p>';
		echo'<p>Fully customisable from your website, the &#8220;Our Church&#8221; app integrates the core Church Admin plugin features that you are already familiar with...</p>';
		
	echo'<ul><li> Address Book</li><li>Calendar</li><li> Schedule</li><li> Service Prebooking</li><li> Push notifications</li><li> Sermon Podcast</li><li> News articles</li><li> Prayer Requests</li><li>Bible notes</li><li>And much more.</li></ul><p>You can also add custom pages and your own CSS to change the look as much as you like.</p>';

	echo'<p>'.wp_kses_post(sprintf(__('It is FREE for your church congregation to use and just an annual payment of  %1$s  for the church. Other major currencies are available','church-admin'),'<span class="sign">USD $</span><span class="cost">129</span>' )).'</p>';
	if(!is_ssl() )echo'<p><strong>'.esc_html(__("To use the app your website must have an ssl – https:// at the start of the URL – this is for your congregation’s data security and is a  requirement for iOS and Android apps now.",'church-admin') ).'</strong></p>';
	echo'<p>'.esc_html(__("Please subscribe using the Paypal button and we will be in touch to get you set up pretty quickly (note I’m in the UK!)",'church-admin') ).'</p><br style="clear:left" />';
	echo'<p><form action="'.CA_PAYPAL.'" method="post">
    <input name="cmd" type="hidden" value="_xclick-subscriptions"> 
    <input name="item_name" type="hidden" value="Church Admin Premium Version from v'.esc_attr($church_admin_version).'"> 
    <input type="hidden" name="return" value="'.site_url().'/?licence-change=reset"/>
    <input type="hidden" name="rm" value=2/>
    <input name="notify_url" type="hidden" value="https://www.churchadminplugin.com/wp-admin/admin-ajax.php?action=church_admin_premium_ipn"> 
        <input type="hidden" name="custom" value="'.site_url().'">
        <input name="business" type="hidden" value="support@churchadminplugin.com"> 
        <input type="hidden" name="a3" class="premium-price" value="129">
       <input type="hidden" class="ca-recurring"  name="p3" value="1" /><input type="hidden" class="ca-recurring" name="t3" value="Y" /><input type="hidden" class="ca-recurring" name="src" value="1" /><input type="hidden" name="no_note" value=1>
       <div class="form-group"><select class="premium-currency_code" name="currency_code"><option value="USD">US Dollar $129 annually</option><option value="GBP">GB Pound Sterling £99 annually</option><option value="EUR">Euro €110 annually</option><option value="AUD">Australian Dollar $190 annually</option><option value="BRL">Brazilian Real 500 annually</option><option value="CAD">Canadian Dollar $170 annually</option><option value="MXN">Mexican Peso 2120 annually</option> <option value="CHF">Swiss Franc 110 annually</option></select></div><input class="button-primary" type="submit" value="Upgrade to Premium (PayPal) "></form></p><script>
               jQuery( document ).ready(function($) {
                   console.log( "ready!" );
              
                   $(".premium-currency_code").change(sortPrice);
                   $(".premium-frequency").change(sortPrice);
                   
                   function sortPrice(){
                       var currency_code=$(".premium-currency_code").val();
                       var frequency=$(".premium-frequency").val();
                       console.log("Currency "+ currency_code+ "Frequency "+frequency);
                       var price=99;
                       
                       var sign="&pound;";
                       console.log(currency_code)
                       switch(currency_code)
                       {
                           default:case "GBP":price=99;sign="GBP &pound;99";break;	
                           case "AUD":price=190;sign="AUD &dollar;190";break;
                           case "MXN":price=2120;sign="MXN Peso 2120";break;
                           case "BRL":price=600;sign="BRL Real 600";break;
                           case "CAD":price=170;sign="CAD &dollar;170";break;
                           case "USD":price=129;sign="USD &dollar;129";break;
                           case"EUR":price=110;sign="EU &euro;110";break;
                           case "CHF":price=110;sign="CHF110";break;
                           
                       }
                       
                       $(".premium-sign").html(sign);
                       var formattedPrice =parseFloat(Math.round(price * 100) / 100).toFixed(2);
                       $(".premium-cost").html(formattedPrice);
                       $(".premium-price").val(formattedPrice);
                       $(".premiumfreq").html(freq);
                       
                   };
                   
               });</script>
';
}
/**
 *
 * Admin function for app
 *
 * @author  Andy Moyle
 * @param    null
 * @return   html
 * @version  0.1
 *
 */
function church_admin_app()
{

	//initialise
	global $wpdb;
	if(!empty( $_POST['app_id'] ) )
	{
			update_option('church_admin_app_id',(int)$_POST['app_id'] );
	}
	echo'<h2>Church Admin App Admin</h2>';

    church_admin_url_check();


	$licence_level = church_admin_app_licence_check(); 
	if($licence_level!='premium')
    {

        church_admin_buy_app();

	}
	else
	{

		echo'<p><a href="https://www.churchadminplugin.com/church-admin-manual.pdf" class="button-primary">'.esc_html(__("Church Admin PDF manual with chapter on how to set up the app",'church-admin') ).'</a></p>';
		church_admin_app_menu();



	}
}

function church_admin_app_settings()
{
    $member_types=church_admin_member_types_array();
    echo '<h2>'.esc_html(__('App settings','church-admin') ).'</h2>';
	if(church_admin_app_licence_check() )
    {
        if(!empty($_POST['app_settings']))
        {
            //set up push notifications for replies
            $twilio_admin_push = !empty($_POST['twilio-admin-push']) ? church_admin_sanitize($_POST['twilio-admin-push']):null;
            if(!empty( $twilio_admin_push) )
            {
                $twilio_people_id=maybe_unserialize(church_admin_get_people_id($twilio_admin_push ) );
                update_option('church_admin_twilio_receive_push_to_admin',$twilio_people_id);
            }
            else
            {
                delete_option('church_admin_twilio_receive_push_to_admin');
            }
            
            $app_css = !empty($_POST['app_css']) ? sanitize_text_field(stripslashes($_POST['app_css'])):null;
            $app_title = !empty($_POST['app_title']) ? sanitize_text_field(stripslashes($_POST['app_title'])):null;
            
            update_option('church_admin_app_menu_title',$app_title );

            update_option('church_admin_app_style',sanitize_text_field( $app_css ) );
            echo'<div class="notice notice-success"><h2>'.esc_html(__('Settings Updated','church-admin')).'</h2></div>';
			if(!empty($_POST['member_type_id'])){

                $member_type_id = (int)church_admin_sanitize($_POST['member_type_id']);
                if(!empty($member_types[$member_type_id])){
                    update_option('church_admin_member_type_id_for_registrations',$member_type_id);
                }

            }
            
            if(!empty($_POST['gutenberg-in-app'])){
                update_option('church_admin_app_gutenberg',true);
            }
            else
            {
                delete_option('church_admin_app_gutenberg');
            }
            if(!empty($_POST['no-editing'])){
                update_option('church_admin_no_app_editing',true);
            }
            else
            {
                delete_option('church_admin_no_app_editing');
            }
            if(!empty( $_POST['no-registrations'] ) )
            {
                update_option('church_admin_no_app_registrations',TRUE);
            }
            else
            {
                delete_option('church_admin_no_app_registrations');
            }
            if(!empty( $_POST['no-push'] ) )
			{
				update_option('church_admin_no_push',TRUE);
			}
			else
			{
				delete_option('church_admin_no_push');
			}
            switch( $_REQUEST['address-style'] )
            {
                case 'new':
                default:
                    update_option('church_admin_which_app_address_list_type','new');
                break;
                case 'old':
                    update_option('church_admin_which_app_address_list_type','old');
                break;
            }
            if(!empty($_POST['home-page'])){
                $chosenMenu=get_option('church_admin_app_new_menu');
                $newHomePage = church_admin_sanitize($_POST['home-page']);
                if(!empty($chosenMenu[$newHomePage])){
                    update_option('church-admin-app-homepage',$newHomePage);
                }
            }
            else{
                update_option('church-admin-app-homepage','Home');
            }
        }
         
        echo'<form action="" method="post">';
       
        $people='';
        $twilio_people_ids=get_option('church_admin_twilio_receive_push_to_admin');
        if(!empty( $twilio_people_ids) )$people=church_admin_get_people( $twilio_people_ids);
        echo '<div class="church-admin-form-group"><label>'.esc_html(__('App users who will receive SMS reply push and get menu item "SMS replies"','church-admin') ).'</label>'.church_admin_autocomplete('twilio-admin-push','friends','to',$people,FALSE).'</div>';
        if(!empty($member_types[1])){ $saved_member_type=1;}
        $saved_member_type_id=get_option('church_admin_member_type_id_for_registrations');   
    
        echo '<div class="church-admin-form-group"><label>'.esc_html( __( 'Member type for new registrations', 'church-admin' ) ).'</label><select class="church-admin-form-control" name="member_type_id">';
        foreach($member_types AS $id=>$type){
            echo'<option '. selected( $saved_member_type_id , $id , FALSE ).' value="'.(int)$id.'">'.esc_html($type).'</option>';
        }    
        echo'</select></div>';


        $appRegistrations = get_option('church_admin_no_app_registrations');
        echo '<div class="checkbox"><label><input type="checkbox" name="no-registrations" '.checked(true,$appRegistrations,false).'/>'.esc_html(__('Disable app registrations','church-admin') ).'<label></div>';
    

        $noEditing = get_option('church_admin_no_app_editing');
        echo '<div class="checkbox"><label><input type="checkbox" name="no-editing" '.checked(true,$noEditing,false).'/>'.esc_html(__('Disable editing for non admins','church-admin') ).'<label></div>';

        echo'<div class="checkbox"><label><input  class="church-admin-form-control" type="checkbox" name="no-push" value="1" ';
        $no_push= get_option('church_admin_no_push');
        if( $no_push) echo' checked="checked" ';
        echo'/>'.esc_html(__('Stop push notification and email send on content publishing','church-admin' ) ).'</labeL></div>';

        $gutenbergapp=get_option('church_admin_app_gutenberg');
        echo'<div class="checkbox"><label>';
        echo'<input type="checkbox"  class="church-admin-form-control" name="gutenberg-in-app" value="1" ';
        if( $gutenbergapp ) echo' checked="checked" ';
        echo'/>'.esc_html(__('Use Gutenberg Editor for App','church-admin' ) ).'</label></div>';




        $whichAppAddressList=get_option('church_admin_which_app_address_list_type');
        if ( empty( $whichAppAddressList) )$whichAppAddressList='new';
        
        echo'<p><strong>'.esc_html(__('Address list style','church-admin') ).'</strong></p>';
        echo'<div class="checkbox"><label><input class="church-admin-form-control" type="radio" name="address-style" value="new" '.checked('new', $whichAppAddressList,false).'/> '.esc_html(__('Alphabetical individuals (admin can edit)','church-admin') ).'</label></div>';
        echo'<div class="checkbox"><label><input type="radio" name="address-style" value="old" '.checked('old', $whichAppAddressList,false).'/> '.esc_html(__('Alphabetical households (no admin editing)','church-admin') ).'</label></div>';
        echo'<p>&nbsp;</p>';
        echo'<div class="church-admin-form-group"><label>'.esc_html(__('App menu title','church-admin') ).'</label><input class="church-admin-form-control"  type="text" name="app_title" class="large-text" ';
        $app_title=get_option('church_admin_app_menu_title');
        if(!empty( $app_title) )echo 'value= "'.esc_html( $app_title).'"';
        echo'/></div>';

        echo'<div class="church-admin-form-group"><label>'.esc_html(__('App menu CSS','church-admin') ).'</label><textarea class="church-admin-form-control"  name="app_css" class="large-text">';
        $app_css=get_option('church_admin_app_style');
        if(!empty( $app_css) ) echo strip_tags($app_css);
        echo'</textarea></div>';
        

        //set which page is the "Home Page"
        $current='home';
        $current = get_option('church-admin-app-homepage');
        $chosenMenu=get_option('church_admin_app_new_menu');
        //church_admin_debug($chosenMenu);
        echo'<div class="church-admin-form-group"><label>'.esc_html('Select which app page is "Home"','church-admin').'</label>';
        echo'<select name="home-page" class="church-admin-form-control">';
        echo'<option value="home" '.selected($current,'home',FALSE).'>'.esc_html(__('Home','church-admin')).'</option>';
        foreach($chosenMenu AS $value=>$data){
            echo'<option value="'.esc_attr($value).'" '.selected($current,$value,FALSE).'>'.esc_html($data['item']).'</option>';
        }
        echo'</select>';
        echo'</div>';



        
        echo'<p><input type="hidden" name="app_settings" value="yes" /><input type="submit" class="button-primary" value="'.esc_html(__('Save','church-admin') ).'" /></p></form>';
    }else{church_admin_buy_app();}
}




function church_admin_bible_version()
{

        global $wpdb;
        echo'<h2  id="bible-version">'.esc_html(__('Which Bible version?','church-admin') ).'</h2>';

        $version=get_option('church_admin_bible_version');
        switch( $version)
        {
            case'KJV':
            case "ostervald":
            case "schlachter":
            case "statenvertaling":
            case "swedish":
            case "bibelselskap":
            case "sse":
            case "lithuanian":
                echo' We are offering more versions now, please update';
            break;


        }


        if(!empty( $_POST['version'] ) )
        {

            update_option('church_admin_bible_version',sanitize_text_field( stripslashes($_POST['version'] )) );
        }

            $version=get_option('church_admin_bible_version');

            echo'<form action="" method="POST"><select class="search-translation-select translation-select-default form-control" name="version">';
            if(!empty( $version) ) echo '<option selected="selected" value="'.esc_html( $version).'">'.esc_html( $version).'</option>';
            ?>

            <option class="lang" value="AMU">—Amuzgo de Guerrero (AMU)—</option>
            <option value="AMU">Amuzgo de Guerrero (AMU)</option>
            <option class="spacer" value="AMU">&nbsp;</option>
    <option class="lang" value="ERV-AR">—العربية (AR)—</option>
    <option value="ERV-AR">Arabic Bible: Easy-to-Read Version (ERV-AR)</option>
    <option value="NAV">Ketab El Hayat (NAV)</option>
    <option class="spacer" value="NAV">&nbsp;</option>
    <option class="lang" value="ERV-AWA">—अवधी (AWA)—</option>
    <option value="ERV-AWA">Awadhi Bible: Easy-to-Read Version (ERV-AWA)</option>
    <option class="spacer" value="ERV-AWA">&nbsp;</option>
    <option class="lang" value="BG1940">—Български (BG)—</option>
    <option value="BG1940">1940 Bulgarian Bible (BG1940)</option>
    <option value="BULG">Bulgarian Bible (BULG)</option>
    <option value="ERV-BG">Bulgarian New Testament: Easy-to-Read Version (ERV-BG)</option>
    <option value="CBT">Библия, нов превод от оригиналните езици (с неканоничните книги) (CBT)</option>
    <option value="BOB">Библия, синодално издание (BOB)</option>
    <option value="BPB">Библия, ревизирано издание (BPB)</option>
    <option class="spacer" value="BPB">&nbsp;</option>
    <option class="lang" value="CCO">—Chinanteco de Comaltepec (CCO)—</option>
    <option value="CCO">Chinanteco de Comaltepec (CCO)</option>
    <option class="spacer" value="CCO">&nbsp;</option>
    <option class="lang" value="APSD-CEB">—Cebuano (CEB)—</option>
    <option value="APSD-CEB">Ang Pulong Sa Dios (APSD-CEB)</option>
    <option class="spacer" value="APSD-CEB">&nbsp;</option>
    <option class="lang" value="CHR">—ᏣᎳᎩ ᎦᏬᏂᎯᏍ (CHR)—</option>
    <option value="CHR">Cherokee New Testament (CHR)</option>
    <option class="spacer" value="CHR">&nbsp;</option>
    <option class="lang" value="CKW">—Cakchiquel Occidental (CKW)—</option>
    <option value="CKW">Cakchiquel Occidental (CKW)</option>
    <option class="spacer" value="CKW">&nbsp;</option>
    <option class="lang" value="B21">—Čeština (CS)—</option>
    <option value="B21">Bible 21 (B21)</option>
    <option value="SNC">Slovo na cestu (SNC)</option>
    <option class="spacer" value="SNC">&nbsp;</option>
    <option class="lang" value="BWM">—Cymraeg (CY)—</option>
    <option value="BWM">Beibl William Morgan (BWM)</option>
    <option class="spacer" value="BWM">&nbsp;</option>
    <option class="lang" value="BPH">—Dansk (DA)—</option>
    <option value="BPH">Bibelen på hverdagsdansk (BPH)</option>
    <option value="DN1933">Dette er Biblen på dansk (DN1933)</option>
    <option class="spacer" value="DN1933">&nbsp;</option>
    <option class="lang" value="HOF">—Deutsch (DE)—</option>
    <option value="HOF">Hoffnung für Alle (HOF)</option>
    <option value="LUTH1545">Luther Bibel 1545 (LUTH1545)</option>
    <option value="NGU-DE">Neue Genfer Übersetzung (NGU-DE)</option>
    <option value="SCH1951">Schlachter 1951 (SCH1951)</option>
    <option value="SCH2000">Schlachter 2000 (SCH2000)</option>
    <option class="spacer" value="SCH2000">&nbsp;</option>
    <option class="lang" value="KJ21">—English (EN)—</option>
    <option value="KJ21">21st Century King James Version (KJ21)</option>
    <option value="ASV">American Standard Version (ASV)</option>
    <option value="AMP">Amplified Bible (AMP)</option>
    <option value="AMPC">Amplified Bible, Classic Edition (AMPC)</option>
    <option value="BRG">BRG Bible (BRG)</option>
    <option value="CSB">Christian Standard Bible (CSB)</option>
    <option value="CEB">Common English Bible (CEB)</option>
    <option value="CJB">Complete Jewish Bible (CJB)</option>
    <option value="CEV">Contemporary English Version (CEV)</option>
    <option value="DARBY">Darby Translation (DARBY)</option>
    <option value="DLNT">Disciples’ Literal New Testament (DLNT)</option>
    <option value="DRA">Douay-Rheims 1899 American Edition (DRA)</option>
    <option value="ERV">Easy-to-Read Version (ERV)</option>
    <option value="ESV">English Standard Version (ESV)</option>
    <option value="ESVUK">English Standard Version Anglicised (ESVUK)</option>
    <option value="EXB">Expanded Bible (EXB)</option>
    <option value="GNV">1599 Geneva Bible (GNV)</option>
    <option value="GW">GOD’S WORD Translation (GW)</option>
    <option value="GNT">Good News Translation (GNT)</option>
    <option value="HCSB">Holman Christian Standard Bible (HCSB)</option>
    <option value="ICB">International Children’s Bible (ICB)</option>
    <option value="ISV">International Standard Version (ISV)</option>
    <option value="PHILLIPS">J.B. Phillips New Testament (PHILLIPS)</option>
    <option value="JUB">Jubilee Bible 2000 (JUB)</option>
    <option value="KJV">King James Version (KJV)</option>
    <option value="AKJV">Authorized (King James) Version (AKJV)</option>
    <option value="LEB">Lexham English Bible (LEB)</option>
    <option value="TLB">Living Bible (TLB)</option>
    <option value="MSG">The Message (MSG)</option>
    <option value="MEV">Modern English Version (MEV)</option>
    <option value="MOUNCE">Mounce Reverse-Interlinear New Testament (MOUNCE)</option>
    <option value="NOG">Names of God Bible (NOG)</option>
    <option value="NABRE">New American Bible (Revised Edition) (NABRE)</option>
    <option value="NASB">New American Standard Bible (NASB)</option>
    <option value="NCV">New Century Version (NCV)</option>
    <option value="NET">New English Translation (NET Bible)</option>
    <option value="NIRV">New International Reader's Version (NIRV)</option>
    <option value="NIV">New International Version (NIV)</option>
    <option value="NIVUK">New International Version - UK (NIVUK)</option>
    <option value="NKJV">New King James Version (NKJV)</option>
    <option value="NLV">New Life Version (NLV)</option>
    <option value="NLT">New Living Translation (NLT)</option>
    <option value="NMB">New Matthew Bible (NMB)</option>
    <option value="NRSV">New Revised Standard Version (NRSV)</option>
    <option value="NRSVA">New Revised Standard Version, Anglicised (NRSVA)</option>
    <option value="NRSVACE">New Revised Standard Version, Anglicised Catholic Edition (NRSVACE)</option>
    <option value="NRSVCE">New Revised Standard Version Catholic Edition (NRSVCE)</option>
    <option value="NTE">New Testament for Everyone (NTE)</option>
    <option value="OJB">Orthodox Jewish Bible (OJB)</option>
    <option value="TPT">The Passion Translation (TPT)</option>
    <option value="RSV">Revised Standard Version (RSV)</option>
    <option value="RSVCE">Revised Standard Version Catholic Edition (RSVCE)</option>
    <option value="TLV">Tree of Life Version (TLV)</option>
    <option value="VOICE">The Voice (VOICE)</option>
    <option value="WEB">World English Bible (WEB)</option>
    <option value="WE">Worldwide English (New Testament) (WE)</option>
    <option value="WYC">Wycliffe Bible (WYC)</option>
    <option value="YLT">Young's Literal Translation (YLT)</option>
    <option class="spacer" value="YLT">&nbsp;</option>
    <option class="lang" value="LBLA">—Español (ES)—</option>
    <option value="LBLA">La Biblia de las Américas (LBLA)</option>
    <option value="DHH">Dios Habla Hoy (DHH)</option>
    <option value="JBS">Jubilee Bible 2000 (Spanish) (JBS)</option>
    <option value="NBD">Nueva Biblia al Día (NBD)</option>
    <option value="NBLH">Nueva Biblia Latinoamericana de Hoy (NBLH)</option>
    <option value="NTV">Nueva Traducción Viviente (NTV)</option>
    <option value="NVI">Nueva Versión Internacional (NVI)</option>
    <option value="CST">Nueva Versión Internacional (Castilian) (CST)</option>
    <option value="PDT">Palabra de Dios para Todos (PDT)</option>
    <option value="BLP">La Palabra (España) (BLP)</option>
    <option value="BLPH">La Palabra (Hispanoamérica) (BLPH)</option>
    <option value="RVA-2015">Reina Valera Actualizada (RVA-2015)</option>
    <option value="RVC">Reina Valera Contemporánea (RVC)</option>
    <option value="RVR1960">Reina-Valera 1960 (RVR1960)</option>
    <option value="RVR1977">Reina Valera 1977 (RVR1977)</option>
    <option value="RVR1995">Reina-Valera 1995 (RVR1995)</option>
    <option value="RVA">Reina-Valera Antigua (RVA)</option>
    <option value="SRV-BRG">Spanish Blue Red and Gold Letter Edition (SRV-BRG)</option>
    <option value="TLA">Traducción en lenguaje actual (TLA)</option>
    <option class="spacer" value="TLA">&nbsp;</option>
    <option class="lang" value="R1933">—Suomi (FI)—</option>
    <option value="R1933">Raamattu 1933/38 (R1933)</option>
    <option class="spacer" value="R1933">&nbsp;</option>
    <option class="lang" value="BDS">—Français (FR)—</option>
    <option value="BDS">La Bible du Semeur (BDS)</option>
    <option value="LSG">Louis Segond (LSG)</option>
    <option value="NEG1979">Nouvelle Edition de Genève – NEG1979 (NEG1979)</option>
    <option value="SG21">Segond 21 (SG21)</option>
    <option class="spacer" value="SG21">&nbsp;</option>
    <option class="lang" value="TR1550">—Κοινη (GRC)—</option>
    <option value="TR1550">1550 Stephanus New Testament (TR1550)</option>
    <option value="WHNU">1881 Westcott-Hort New Testament (WHNU)</option>
    <option value="TR1894">1894 Scrivener New Testament (TR1894)</option>
    <option value="SBLGNT">SBL Greek New Testament (SBLGNT)</option>
    <option class="spacer" value="SBLGNT">&nbsp;</option>
    <option class="lang" value="HHH">—עברית (HE)—</option>
    <option value="HHH">Habrit Hakhadasha/Haderekh (HHH)</option>
    <option value="WLC">The Westminster Leningrad Codex (WLC)</option>
    <option class="spacer" value="WLC">&nbsp;</option>
    <option class="lang" value="ERV-HI">—हिन्दी (HI)—</option>
    <option value="ERV-HI">Hindi Bible: Easy-to-Read Version (ERV-HI)</option>
    <option class="spacer" value="ERV-HI">&nbsp;</option>
    <option class="lang" value="HLGN">—Ilonggo (HIL)—</option>
    <option value="HLGN">Ang Pulong Sang Dios (HLGN)</option>
    <option class="spacer" value="HLGN">&nbsp;</option>
    <option class="lang" value="HNZ-RI">—Hrvatski (HR)—</option>
    <option value="HNZ-RI">Hrvatski Novi Zavjet – Rijeka 2001 (HNZ-RI)</option>
    <option value="CRO">Knijga O Kristu (CRO)</option>
    <option class="spacer" value="CRO">&nbsp;</option>
    <option class="lang" value="HCV">—Kreyòl ayisyen (HT)—</option>
    <option value="HCV">Haitian Creole Version (HCV)</option>
    <option class="spacer" value="HCV">&nbsp;</option>
    <option class="lang" value="KAR">—Magyar (HU)—</option>
    <option value="KAR">Hungarian Károli (KAR)</option>
    <option value="ERV-HU">Hungarian Bible: Easy-to-Read Version (ERV-HU)</option>
    <option value="NT-HU">Hungarian New Translation (NT-HU)</option>
    <option class="spacer" value="NT-HU">&nbsp;</option>
    <option class="lang" value="HWP">—Hawai‘i Pidgin (HWC)—</option>
    <option value="HWP">Hawai‘i Pidgin (HWP)</option>
    <option class="spacer" value="HWP">&nbsp;</option>
    <option class="lang" value="ICELAND">—Íslenska (IS)—</option>
    <option value="ICELAND">Icelandic Bible (ICELAND)</option>
    <option class="spacer" value="ICELAND">&nbsp;</option>
    <option class="lang" value="BDG">—Italiano (IT)—</option>
    <option value="BDG">La Bibbia della Gioia (BDG)</option>
    <option value="CEI">Conferenza Episcopale Italiana (CEI)</option>
    <option value="LND">La Nuova Diodati (LND)</option>
    <option value="NR1994">Nuova Riveduta 1994 (NR1994)</option>
    <option value="NR2006">Nuova Riveduta 2006 (NR2006)</option>
    <option class="spacer" value="NR2006">&nbsp;</option>
    <option class="lang" value="JLB">—日本語 (JA)—</option>
    <option value="JLB">Japanese Living Bible (JLB)</option>
    <option class="spacer" value="JLB">&nbsp;</option>
    <option class="lang" value="JAC">—Jacalteco, Oriental (JAC)—</option>
    <option value="JAC">Jacalteco, Oriental (JAC)</option>
    <option class="spacer" value="JAC">&nbsp;</option>
    <option class="lang" value="KEK">—Kekchi (KEK)—</option>
    <option value="KEK">Kekchi (KEK)</option>
    <option class="spacer" value="KEK">&nbsp;</option>
    <option class="lang" value="KLB">—한국어 (KO)—</option>
    <option value="KLB">Korean Living Bible (KLB)</option>
    <option class="spacer" value="KLB">&nbsp;</option>
    <option class="lang" value="VULGATE">—Latina (LA)—</option>
    <option value="VULGATE">Biblia Sacra Vulgata (VULGATE)</option>
    <option class="spacer" value="VULGATE">&nbsp;</option>
    <option class="lang" value="MAORI">—Māori (MI)—</option>
    <option value="MAORI">Maori Bible (MAORI)</option>
    <option class="spacer" value="MAORI">&nbsp;</option>
    <option class="lang" value="MNT">—Македонски (MK)—</option>
    <option value="MNT">Macedonian New Testament (MNT)</option>
    <option class="spacer" value="MNT">&nbsp;</option>
    <option class="lang" value="ERV-MR">—मराठी (MR)—</option>
    <option value="ERV-MR">Marathi Bible: Easy-to-Read Version (ERV-MR)</option>
    <option class="spacer" value="ERV-MR">&nbsp;</option>
    <option class="lang" value="MVC">—Mam, Central (MVC)—</option>
    <option value="MVC">Mam, Central (MVC)</option>
    <option class="spacer" value="MVC">&nbsp;</option>
    <option class="lang" value="MVJ">—Mam, Todos Santos (MVJ)—</option>
    <option value="MVJ">Mam de Todos Santos Chuchumatán (MVJ)</option>
    <option class="spacer" value="MVJ">&nbsp;</option>
    <option class="lang" value="REIMER">—Plautdietsch (NDS)—</option>
    <option value="REIMER">Reimer 2001 (REIMER)</option>
    <option class="spacer" value="REIMER">&nbsp;</option>
    <option class="lang" value="ERV-NE">—नेपाली (NE)—</option>
    <option value="ERV-NE">Nepali Bible: Easy-to-Read Version (ERV-NE)</option>
    <option class="spacer" value="ERV-NE">&nbsp;</option>
    <option class="lang" value="NGU">—Náhuatl de Guerrero (NGU)—</option>
    <option value="NGU">Náhuatl de Guerrero (NGU)</option>
    <option class="spacer" value="NGU">&nbsp;</option>
    <option class="lang" value="HTB">—Nederlands (NL)—</option>
    <option value="HTB">Het Boek (HTB)</option>
    <option class="spacer" value="HTB">&nbsp;</option>
    <option class="lang" value="DNB1930">—Norsk (NO)—</option>
    <option value="DNB1930">Det Norsk Bibelselskap 1930 (DNB1930)</option>
    <option value="LB">En Levende Bok (LB)</option>
    <option class="spacer" value="LB">&nbsp;</option>
    <option class="lang" value="ERV-OR">—ଓଡ଼ିଆ (OR)—</option>
    <option value="ERV-OR">Oriya Bible: Easy-to-Read Version (ERV-OR)</option>
    <option class="spacer" value="ERV-OR">&nbsp;</option>
    <option class="lang" value="ERV-PA">—ਪੰਜਾਬੀ (PA)—</option>
    <option value="ERV-PA">Punjabi Bible: Easy-to-Read Version (ERV-PA)</option>
    <option class="spacer" value="ERV-PA">&nbsp;</option>
    <option class="lang" value="NP">—Polski (PL)—</option>
    <option value="NP">Nowe Przymierze (NP)</option>
    <option value="SZ-PL">Słowo Życia (SZ-PL)</option>
    <option value="UBG">Updated Gdańsk Bible (UBG)</option>
    <option class="spacer" value="UBG">&nbsp;</option>
    <option class="lang" value="NBTN">—Nawat (PPL)—</option>
    <option value="NBTN">Ne Bibliaj Tik Nawat (NBTN)</option>
    <option class="spacer" value="NBTN">&nbsp;</option>
    <option class="lang" value="ARC">—Português (PT)—</option>
    <option value="ARC">Almeida Revista e Corrigida 2009 (ARC)</option>
    <option value="NTLH">Nova Traduҫão na Linguagem de Hoje 2000 (NTLH)</option>
    <option value="NVI-PT">Nova Versão Internacional (NVI-PT)</option>
    <option value="OL">O Livro (OL)</option>
    <option value="VFL">Portuguese New Testament: Easy-to-Read Version (VFL)</option>
    <option class="spacer" value="VFL">&nbsp;</option>
    <option class="lang" value="MTDS">—Quichua (QU)—</option>
    <option value="MTDS">Mushuj Testamento Diospaj Shimi (MTDS)</option>
    <option class="spacer" value="MTDS">&nbsp;</option>
    <option class="lang" value="QUT">—Quiché, Centro Occidenta (QUT)—</option>
    <option value="QUT">Quiché, Centro Occidental (QUT)</option>
    <option class="spacer" value="QUT">&nbsp;</option>
    <option class="lang" value="RMNN">—Română (RO)—</option>
    <option value="RMNN">Cornilescu 1924 - Revised 2010, 2014 (RMNN)</option>
    <option value="NTLR">Nouă Traducere În Limba Română (NTLR)</option>
    <option class="spacer" value="NTLR">&nbsp;</option>
    <option class="lang" value="NRT">—Русский (RU)—</option>
    <option value="NRT">New Russian Translation (NRT)</option>
    <option value="CARS">Священное Писание (Восточный Перевод) (CARS)</option>
    <option value="CARST">Священное Писание (Восточный перевод), версия для Таджикистана (CARST)</option>
    <option value="CARSA">Священное Писание (Восточный перевод), версия с «Аллахом» (CARSA)</option>
    <option value="ERV-RU">Russian New Testament: Easy-to-Read Version (ERV-RU)</option>
    <option value="RUSV">Russian Synodal Version (RUSV)</option>
    <option class="spacer" value="RUSV">&nbsp;</option>
    <option class="lang" value="NPK">—Slovenčina (SK)—</option>
    <option value="NPK">Nádej pre kazdého (NPK)</option>
    <option class="spacer" value="NPK">&nbsp;</option>
    <option class="lang" value="SOM">—Somali (SO)—</option>
    <option value="SOM">Somali Bible (SOM)</option>
    <option class="spacer" value="SOM">&nbsp;</option>
    <option class="lang" value="ALB">—Shqip (SQ)—</option>
    <option value="ALB">Albanian Bible (ALB)</option>
    <option class="spacer" value="ALB">&nbsp;</option>
    <option class="lang" value="ERV-SR">—Српски (SR)—</option>
    <option value="ERV-SR">Serbian New Testament: Easy-to-Read Version (ERV-SR)</option>
    <option class="spacer" value="ERV-SR">&nbsp;</option>
    <option class="lang" value="SVL">—Svenska (SV)—</option>
    <option value="SVL">Nya Levande Bibeln (SVL)</option>
    <option value="SV1917">Svenska 1917 (SV1917)</option>
    <option value="SFB">Svenska Folkbibeln (SFB)</option>
    <option value="SFB15">Svenska Folkbibeln 2015 (SFB15)</option>
    <option class="spacer" value="SFB15">&nbsp;</option>
    <option class="lang" value="SNT">—Kiswahili (SW)—</option>
    <option value="SNT">Neno: Bibilia Takatifu (SNT)</option>
    <option class="spacer" value="SNT">&nbsp;</option>
    <option class="lang" value="ERV-TA">—தமிழ் (TA)—</option>
    <option value="ERV-TA">Tamil Bible: Easy-to-Read Version (ERV-TA)</option>
    <option class="spacer" value="ERV-TA">&nbsp;</option>
    <option class="lang" value="TNCV">—ภาษาไทย (TH)—</option>
    <option value="TNCV">Thai New Contemporary Bible (TNCV)</option>
    <option value="ERV-TH">Thai New Testament: Easy-to-Read Version (ERV-TH)</option>
    <option class="spacer" value="ERV-TH">&nbsp;</option>
    <option class="lang" value="FSV">—Tagalog (TL)—</option>
    <option value="FSV">Ang Bagong Tipan: Filipino Standard Version (FSV)</option>
    <option value="ABTAG1978">Ang Biblia (1978) (ABTAG1978)</option>
    <option value="ABTAG2001">Ang Biblia, 2001 (ABTAG2001)</option>
    <option value="ADB1905">Ang Dating Biblia (1905) (ADB1905)</option>
    <option value="SND">Ang Salita ng Diyos (SND)</option>
    <option value="MBBTAG">Magandang Balita Biblia (MBBTAG)</option>
    <option value="MBBTAG-DC">Magandang Balita Biblia (with Deuterocanon) (MBBTAG-DC)</option>
    <option class="spacer" value="MBBTAG-DC">&nbsp;</option>
    <option class="lang" value="NA-TWI">—Twi (TWI)—</option>
    <option value="NA-TWI">Nkwa Asem (NA-TWI)</option>
    <option class="spacer" value="NA-TWI">&nbsp;</option>
    <option class="lang" value="UKR">—Українська (UK)—</option>
    <option value="UKR">Ukrainian Bible (UKR)</option>
    <option value="ERV-UK">Ukrainian New Testament: Easy-to-Read Version (ERV-UK)</option>
    <option class="spacer" value="ERV-UK">&nbsp;</option>
    <option class="lang" value="ERV-UR">—اردو (UR)—</option>
    <option value="ERV-UR">Urdu Bible: Easy-to-Read Version (ERV-UR)</option>
    <option class="spacer" value="ERV-UR">&nbsp;</option>
    <option class="lang" value="USP">—Uspanteco (USP)—</option>
    <option value="USP">Uspanteco (USP)</option>
    <option class="spacer" value="USP">&nbsp;</option>
    <option class="lang" value="VIET">—Tiêng Viêt (VI)—</option>
    <option value="VIET">1934 Vietnamese Bible (VIET)</option>
    <option value="BD2011">Bản Dịch 2011 (BD2011)</option>
    <option value="NVB">New Vietnamese Bible (NVB)</option>
    <option value="BPT">Vietnamese Bible: Easy-to-Read Version (BPT)</option>
    <option class="spacer" value="BPT">&nbsp;</option>
    <option class="lang" value="CCB">—汉语 (ZH)—</option>
    <option value="CCB">Chinese Contemporary Bible (Simplified) (CCB)</option>
    <option value="CCBT">Chinese Contemporary Bible (Traditional) (CCBT)</option>
    <option value="ERV-ZH">Chinese New Testament: Easy-to-Read Version (ERV-ZH)</option>
    <option value="CNVS">Chinese New Version (Simplified) (CNVS)</option>
    <option value="CNVT">Chinese New Version (Traditional) (CNVT)</option>
    <option value="CSBS">Chinese Standard Bible (Simplified) (CSBS)</option>
    <option value="CSBT">Chinese Standard Bible (Traditional) (CSBT)</option>
    <option value="CUVS">Chinese Union Version (Simplified) (CUVS)</option>
    <option value="CUV">Chinese Union Version (Traditional) (CUV)</option>
    <option value="CUVMPS">Chinese Union Version Modern Punctuation (Simplified) (CUVMPS)</option>
    <option value="CUVMPT">Chinese Union Version Modern Punctuation (Traditional) (CUVMPT)</option>
    </select>
    <?php

            echo'<p><input type="submit" value="'.esc_html(__('Save','church-admin') ).'" class="button-primary" /></p></form>';



}


/**
 *
 * Church Admin App Lout person
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
 function church_admin_logout_app( $user_id)
 {
    $licence_level = church_admin_app_licence_check(); 
	if($licence_level!='premium')
    {
		church_admin_buy_app();
	}
    else
    {
        global $wpdb;
        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_app WHERE user_id="'.(int)$user_id.'"');
        church_admin_app_logins();
    }
 }
function church_admin_logout_app_everyone()
 {
    $licence_level = church_admin_app_licence_check(); 
	if($licence_level!='premium')
    {
        church_admin_buy_app();
    }
    else
    {
        global $wpdb;
        $wpdb->query('TRUNCATE TABLE '.$wpdb->prefix.'church_admin_app');
        echo'<div class="notice notice-success notice-inline"><h2>'.esc_html(__("Everyone logged out",'church-admin') ).'</h2>';
    }
 }
/**
 *
 * Church Admin App Logins
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
function church_admin_app_logins()
{
	$licence_level = church_admin_app_licence_check(); 
	if($licence_level!='premium')
    {
        church_admin_buy_app();}
    else
    {
        echo '<h2>'.esc_html(__('Logged in App Users','church-admin') ).'</h2>';

        global $wpdb;
        
        $sql='SELECT a.*,CONCAT_WS(" ",b.first_name,b.last_name) AS name FROM '.$wpdb->prefix.'church_admin_app a , '.$wpdb->prefix.'church_admin_people b WHERE a.user_id=b.user_id GROUP BY a.user_id ORDER BY a.last_login DESC';
        $results=$wpdb->get_results( $sql);
        if(!empty( $results) )
        {
            $count= $wpdb->num_rows;
            echo'<h3>'.esc_html(sprintf( __('%1$s logged in app users','church-admin'),(int)$count ) ).'</h3>';
            $theader = '<div class="church-admin-form-group"><label>'.esc_html(__('Logout','church-admin') ).'</th><th>'.esc_html(__('User','church-admin') ).'</th><th>'.esc_html(__('Last login','church-admin') ).'</th><th>'.esc_html( __('Last page visited','church-admin') ).'</th></tr>';
            echo'<table class="widefat striped"><thead>'.$theader.'</thead>';
            foreach( $results AS $row)
            {
                $logout='<a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;section=App&amp;action=logout_app&amp;user_id='.(int)$row->user_id,'logout_app').'">'.esc_html(__('Logout','church-admin') ).'</a>';
                echo'<tr><td>'.$logout.'</td><td>'.esc_html( $row->name).'</td><td>'.esc_html(mysql2date(get_option('date_format').' '.get_option('time_format'),$row->last_login)).'</td><td>'.esc_html($row->last_page).'</div>';
            }
            echo'<tfoot'.$theader.'</tfoot></tbody></table>';
        }else{echo'<p>'.esc_html(__('No-one is logged in','church-admin') ).'</p>';}

    }
}

/**
 *
 * Church Admin App Member Types
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
 function church_admin_app_member_types()
 {
 		global $wpdb;
 		$member_types=church_admin_member_types_array();
         $people_types=get_option('church_admin_people_type');
 		echo'<h2>'.esc_html(__('Which member & people types are viewable on the app address list','church-admin') ).'</h2>';
        
 		if(!empty( $_POST['save-app-member-types'] ) )
 		{

 			$newmt=array();
            $saved_member_types = !empty($_POST['member_types'])? church_admin_sanitize($_POST['member_types']):array();
 			foreach( $saved_member_types  AS $key=>$value)
 			{
                if(empty($value)){continue;}
 				if(array_key_exists( $value,$member_types) )$newmt[]=(int)$value;
 			}

 			update_option('church_admin_app_member_types',$newmt);
             $newpt=array();
            $saved_people_types = !empty($_POST['people_types']) ? church_admin_sanitize($_POST['people_types']):array();
 			foreach( $saved_people_types AS $key=>$value)
 			{
                if(empty($value)){continue;}
                if(array_key_exists( $value,$people_types) )$newpt[]=(int)$value;
 			}

 			update_option('church_admin_app_people_types',$newpt);
            delete_option('church_admin_app_address_cache');
            delete_option('church_admin_app_admin_address_cache');
 		}
 		$mt=get_option('church_admin_app_member_types');

 		echo'<form action="" method="POST">';
        echo'<h3>'.esc_html(__('Member types','church-admin') ).'</h3>';
       
 		foreach( $member_types AS $key=>$value)
 		{
 			echo'<p><input type=checkbox value="'.(int)$key.'" name="member_types[]" ';
 			if(!empty( $mt)&&is_array( $mt)&& in_array( $key,$mt) )echo' checked="checked" ';
 			echo'/>'.esc_html( $value).'</p>';

 		}
         echo'<h3>'.esc_html(__('People types','church-admin') ).'</h3>';
        $people_types=get_option('church_admin_people_type');
        $appPeopleTypes=get_option('church_admin_app_people_types');
        foreach( $people_types AS $key=>$value)
 		{
 			echo'<p><input type=checkbox value="'.(int)$key.'" name="people_types[]" ';
 			if(!empty( $appPeopleTypes)&&is_array( $appPeopleTypes)&& in_array( $key,$appPeopleTypes) )echo' checked="checked" ';
 			echo'/>'.esc_html( $value).'</p>';

 		}
 		echo'<p><input type="hidden" name="save-app-member-types" value="yes" /><input type="submit" class="button-primary" value="'.esc_html(__('Save','church-admin') ).'" /></p></form>';


 }

/**
 *
 * Bible Reading Plan
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
function church_admin_bible_reading_plan()
{
	global $wpdb;
	$current_user = wp_get_current_user();
	if(!church_admin_level_check('Directory') )wp_die(__('You don\'t have permissions to do that','church-admin') );
 if(is_user_logged_in()&& current_user_can('manage_options') )
 {


 	echo'<h2>'.esc_html(__('Which Bible Reading plan? ','church-admin') ).'</h2>';

	echo	'<p>'.esc_html(__('The Bible reading post type for a particular day takes priority over any plan loaded below','church-admin') ).'</p>';
	if(!empty( $_POST['save_csv'] ) )
	{
		$mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');
		if(!empty( $_FILES) && $_FILES['file']['error'] == 0 && in_array( $_FILES['file']['type'],$mimes) )
		{
			$wpdb->query('TRUNCATE TABLE '.$wpdb->prefix.'church_admin_brp');
			$plan=sanitize_text_field( $_POST['reading_plan_name'] );
			update_option('church_admin_brp',$plan);
			$filename = $_FILES['file']['name'];
			$upload_dir = wp_upload_dir();
			$filedest = $upload_dir['path'] . '/' . $filename;
			if(move_uploaded_file( $_FILES['file']['tmp_name'], $filedest) )echo '<div class="notice notice-success notice-inline">'.esc_html(__('File Uploaded and saved','church-admin') ).'</div>';

			//ini_set('auto_detect_line_endings',TRUE);
			$file_handle = fopen( $filedest, "r");
			$ID=1;
            if($file_handle){
                while (( $data = fgetcsv( $file_handle, 1000, ",") ) !== FALSE)
                {
                    $reading=array();
                    foreach( $data AS $key=>$value)$reading[]=$value;
                    $reading=serialize( $reading);
                    $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_brplan (ID,readings)VALUES("'.$ID.'","'.esc_sql( $reading).'")');
                    $ID++;
                }
                fclose($file_handle);
            }
		}
	}
	else
	{
		$plan=get_option('church_admin_brp');
		if(!empty( $plan) ) echo'<h3>'.esc_html(__('Current Bible Reading plan name','church-admin') ).':'. esc_html( $plan).'</h3>';
		echo'<p>'.esc_html(__('Import new Bible reading CSV - 365 rows day per row, comma separated passages','church-admin') ).'</p>';
		echo'<form action="" method="POST" enctype="multipart/form-data">';
		wp_nonce_field('bible_upload','nonce');
		echo'<p><label>'.esc_html(__('Reading Plan Name','church-admin') ).'</label><input required="required" name="reading_plan_name" type="text" /></p>';
		echo'<p><label>'.esc_html(__('CSV File','church-admin') ).'</label><input type="file" name="file" accept=".csv" /><input type="hidden" name="save_csv" value="yes" /></p>';
		echo'<p><input  class="button-primary" type="submit" Value="'.esc_html(__('Upload','church-admin') ).'" /></p></form>';
	}

	}
	else{echo '<p>'.esc_html(__('Only admins can upload bible reading plans','church-admin') ).'</p>';}
}




function church_admin_app_log_visit( $loginStatus, $page){

    global $wpdb;
    if( $wpdb->get_var('SHOW TABLES LIKE "'.$wpdb->prefix.'church_admin_app_log_visit"') != $wpdb->prefix.'church_admin_app_log_visit')
    {
            $sql='CREATE TABLE   IF NOT EXISTS '.$wpdb->prefix.'church_admin_app_log_visit (`page` TEXT  NULL ,`visit_date` DATE,`visits` INT(11) NULL, ID INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY) CHARACTER SET utf8 COLLATE utf8_general_ci;';
            $wpdb->query( $sql);
    }
    $date = wp_date('Y-m-d');

    $visit_data = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_app_log_visit WHERE page = "'.esc_sql($page).'" AND visit_date = "'.esc_sql($date).'"');
    if(!empty( $visit_data ) ){
        $visits = $visit_data->visits + 1;
        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_app_log_visit SET visits = "'.(int)$visits.'" WHERE ID = "'.(int)$visit_data->ID.'"');
    }
    else
    {
        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_app_log_visit(page,visits,visit_date) VALUES ("'.esc_sql($page).'","1","'.esc_sql($date).'")');
    }
    if(empty($loginStatus->people_id)){return;}
	$sql='UPDATE '.$wpdb->prefix.'church_admin_app SET last_page="'.esc_sql( $page).'",last_login=NOW() WHERE people_id="'.esc_sql( $loginStatus->people_id).'"';
    //church_admin_debug($sql);
	$wpdb->query( $sql);

}

/****************************************************************************************************************************
*
* This function makes sure that there are app content pages called with post_name home,giving, groups for the app to use.
*
****************************************************************************************************************************/
function church_admin_fix_app_default_content()
{

	global $wpdb;
	//Check home
	$homeRow=$wpdb->get_row('SELECT ID, post_name,post_content FROM '.$wpdb->posts.' WHERE post_name="home" AND post_status="publish" AND post_type="app-content"');
	if ( empty( $homeRow) )
	{
		//make sure no "home" in app-content posts
		$wpdb->query('DELETE FROM '.$wpdb->posts.' WHERE post_name="home" AND post_type="app-content"');
		$wpdb->query('DELETE FROM '.$wpdb->posts.' WHERE post_title="'.esc_sql(__('Home','church-admin')).'" AND post_name!="home" AND post_type="app-content"');
		$home='';
		$logo=get_option('church_admin_app_logo');
		if(!empty( $logo) )  {$home.='<p><img src="'.esc_url($logo).'" class="img-responsive" /><p>';}
		$homeContent=get_option('church_admin_app_home');
		if ( empty( $homeContent) )$homeContent='<h2>'.esc_html(__('Welcome','church-admin') ).'</h2>';

		$home.=$homeContent;
		$args=array('post_title'=>esc_html( __('Home','church-admin' ) ),
					'post_name'=>'home',
					'post_status'=>'publish',
					'post_type'=>'app-content',
					'post_content'=>$home);
		$homeID=wp_insert_post( $args,TRUE);
	}else{$homeID=$homeRow->ID;}
	//check groups
	$groupsRow=$wpdb->get_row('SELECT ID, post_name,post_content FROM '.$wpdb->posts.' WHERE post_name="groups" AND post_status="publish" AND post_type="app-content"');
	if ( empty( $groupsRow) )
	{
		//make sure no "home" in app-content posts
		$wpdb->query('DELETE FROM '.$wpdb->posts.' WHERE post_name="groups" AND post_type="app-content"');
		$wpdb->query('DELETE FROM '.$wpdb->posts.' WHERE post_title="'.esc_sql(__('Groups','church-admin')).'" AND post_name!="groups" AND post_type="app-content"');

		$groupsContent=get_option('church_admin_app_groups');
		if ( empty( $groupsContent) )$groupsContent='<h2>'.esc_html(__('Small Groups','church-admin') ).'</h2>';
		$args=array('post_title'=>esc_html( __('Groups','church-admin' ) ),
					'post_name'=>'groups',
					'post_status'=>'publish',
					'post_type'=>'app-content',
					'post_content'=>$groupsContent);
		$groupsID=wp_insert_post( $args,TRUE);
	}else{$groupsID=$groupsRow->ID;}

	//check giving
	$givingRow=$wpdb->get_row('SELECT ID, post_name,post_content FROM '.$wpdb->posts.' WHERE post_name="giving" AND post_status="publish" AND post_type="app-content"');
	if ( empty( $givingRow) )
	{
		//make sure no "giving" in app-content posts
		$wpdb->query('DELETE FROM '.$wpdb->posts.' WHERE post_name="giving" AND post_type="app-content"');
		$wpdb->query('DELETE FROM '.$wpdb->posts.' WHERE post_title="'.esc_sql(__('Giving','church-admin') ).'" AND post_name!="giving" AND post_type="app-content"');
		$givingContent=get_option('church_admin_app_giving');
		if ( empty( $givingContent) )$givingContent='<h2>'.esc_html(__('Giving','church-admin') ).'</h2>';
		$args=array('post_title'=>esc_html( __('Giving','church-admin' ) ),
					'post_status'=>'publish',
					'post_name'=>'giving',
					'post_type'=>'app-content',
					'post_content'=>$givingContent);
		$givingID=wp_insert_post( $args,TRUE);
		

	}else{
        $givingID=$givingRow->ID;
    }
	update_option('church_admin_app_defaults',array('home'=>$homeID,'smallgroup'=>$groupsID,'giving'=>$givingID) );
}


function ca_build_menu( $people_id)
{
    /***********************************************************
	*
	* App menu storage changed in v2.2520
    * Account is Login/register when not logged in
	* 
	************************************************************/
	global $wpdb;
	$menu=get_option('church_admin_app_new_menu');
    if(!empty( $menu['settings'] ) )
    {
        //church_admin_debug('Still has settings item');
        unset( $menu['settings'] );
        //church_admin_debug $menu);
        update_option('church_admin_app_new_menu',$menu);

    }

    //fix where menu order means items go missing because order is duplicated
    
    $menu=get_option('church_admin_app_new_menu');
    $itemNumAlreadyUsed=array();
    foreach ( $menu AS $menuName=>$item)
    {
        if(in_array( $item['order'],$itemNumAlreadyUsed) )$menu[$menuName]['order']+=1;
        $itemNumAlreadyUsed[]=$item['order'];
    }
    update_option('church_admin_app_new_menu',$menu);
    //church_admin_debug("Building menu".print_r( $menu,TRUE) );
	$menuOutput=array();
	foreach( $menu AS $name=>$item)
	{
        //quick fix for prayer menu item
        if($name=='my-prayer')
        {
            $name='myprayer';
            $menu['myprayer']=$menu['my-prayer'];
            unset($menu['my-prayer']);
            update_option('church_admin_app_new_menu',$menu);
        }
		if( $item['show'] )
		{
			switch( $item['type'] )
			{
				case 'app-content':
					if( $item['show'] )
					{
                        if ( empty( $item['loggedinOnly'] )||(!empty( $item['loggedinOnly'] )&&!empty( $people_id) ))$menuOutput[$item['order']]='<li  class="tab-button" data-tab="#app-content" data-page-name="'.esc_html( $name).'"  data-tap-toggle="false" data-cached=1>'.esc_html( $item['item'] ).'</li>';
					}
				break;
				case 'category':
					if( $item['show'] )
					{
						if ( empty( $item['loggedinOnly'] )||(!empty( $item['loggedinOnly'] )&&!empty( $people_id) ))$menuOutput[$item['order']]='<li  class="tab-button" data-tab="#category" data-cat-name="'.esc_html( $name).'"  data-catname="'.esc_html( $name).'" data-tap-toggle="false">'.esc_html( $item['item'] ).'</li>';
					}
				break;
				default:
						switch( $name)
						{
							 case'home':case 'rota':case'courage':case 'prayer':case 'news':case 'bible':case 'smallgroup':case'messages':case 'calendar':case 'giving':case'media':
								if( $item['show'] )
								{
									if ( empty( $item['loggedinOnly'] )||(!empty( $item['loggedinOnly'] )&&!empty( $people_id) ))$menuOutput[$item['order']]='<li id="'.esc_html( $name).'-tab-button" class="tab-button" data-tab="#'.esc_html( $name).'" data-type="app-content" data-cached=1>'.esc_html( $item['item'] ).' <span id="'.esc_html( $name).'-badge"></span></li>';
								}
							break;
							case 'address':
								/*****************
								 * Do some checks
								 *****************/
								$show=FALSE;
								$member_type=$wpdb->get_var('SELECT member_type_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
								//check that member type is ok
		    					$mt=get_option('church_admin_app_member_types');
								if(is_array( $mt) &&in_array( $member_type,$mt) )$show=TRUE;
								//check not on restricted access
								$restrictedList=get_option('church-admin-restricted-access');
								if(!empty( $restrictedList) && in_array( $people_id,$restrictedList) )$show=FALSE;
								$userID=$wpdb->get_var('SELECT user_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
								if(user_can( $userID,'manage_options') )$show=TRUE;
								if(!empty( $show) )
								{
									$menuOutput[$item['order']]='<li id="'.esc_html( $name).'-tab-button" class="tab-button" data-tab="#'.esc_html( $name).'" data-type="app-content" data-cached=1>'.esc_html( $item['item'] ).' <span id="'.esc_html( $name).'-badge"></span></li>';
								}
							break;
                            case 'account':
                                if(empty($people_id)){//not logged in
                                    $menuOutput[$item['order']]='<li id="'.esc_html( $name).'-tab-button" class="tab-button" data-tab="#'.esc_html( $name).'" data-type="app-content">'.esc_html( __('Login / Register','church-admin') ).'</li>';
                                }
                                else
                                {
                                    $menuOutput[$item['order']]='<li id="'.esc_html( $name).'-tab-button" class="tab-button" data-tab="#'.esc_html( $name).'" data-type="app-content">'.esc_html( $item['item'] ).'</li>';
                                }
                            break;
							default:
								if( $item['show'] )
								{

									if ( empty( $item['loggedinOnly'] )||(!empty( $item['loggedinOnly'] )&&!empty( $people_id) ))$menuOutput[$item['order']]='<li id="'.esc_html( $name).'-tab-button" class="tab-button" data-tab="#'.esc_html( $name).'" data-type="app-content">'.esc_html( $item['item'] ).'</li>';
								}
							break;
						}
				break;
			}
		}

	}
	//look to see if contact messages person!
	$contactSettings=get_option('church_admin_contact_form_settings');
	if(!empty( $contactSettings) && $people_id == $contactSettings['pushToken'] )
	{
		//add contact form messgaes item
		$menuOutput[]='<li id="contact-form-tab-button" class="tab-button" data-tab="contact-form" data-type="app-content">'.esc_html(__('Contact form messages','church-admin') ).'</li>';
	}
    //look to see if sms replies person
    $admin_people_ids=get_option('church_admin_twilio_receive_push_to_admin');
 
    if(!empty( $admin_people_ids) && in_array( $people_id,$admin_people_ids) )
    {
        //add sms replies item
        $menuOutput[]='<li id="contact-form-tab-button" class="tab-button" data-tab="sms-replies" data-type="app-content">'.esc_html(__('SMS replies','church-admin') ).'</li>';
	
    }
    
	ksort( $menuOutput);//sort into order before output!
    
    return $menuOutput;
}


function church_admin_churchwide_my_prayer(){

    global $wp_locale;
    
    echo'<h2>'.esc_html( __( 'Churchwide prayers for the week to display in My Prayer on the app', 'church-admin' ) ).'</h2>';
    $prayers=array();
    $prayers=get_option( 'church_admin_churchwide_prayer' );

    if( !empty( $_POST ) )
    {
    
        $prayers['title'] = !empty( $_POST['title'] )? sanitize_text_field( stripslashes( $_POST['title'] ) ):__('Church prayer items','church-admin');

        for( $i = 0; $i <= 6; $i++ )
        {
            $prayers[$i] = !empty( $_POST['day'.$i] ) ?  sanitize_textarea_field( stripslashes( $_POST['day'.$i] ) ):'';
        }
       
        update_option( 'church_admin_churchwide_prayer' , $prayers );
        echo'<div class="notice notice-success"><p>'.esc_html(__( 'Prayers updated', 'church-admin')).'</p></div>';
    }
    echo'<form action="" method="POST">';
    
    //title
    
    echo'<div class="church-admin-form-group"><label>'. esc_html( __('Title', 'church-admin' ) ).'</label><input type="text" name="title" class="church-admin-form-control" ';
    if( !empty( $prayers['title'] ) ){
        echo ' value="'.esc_html( $prayers['title'] ).'" ';
    }    
    echo '/></div>';

    //daily prayer form fields
    
    for( $i=0; $i<=6; $i++ )
    {
        echo'<div class="church-admin-form-group"><label>'.esc_html ( sprintf( __('Prayer for %1$s','church-admin' ), $wp_locale->get_weekday( $i ) ) ).'</label><textarea class="church-admin-form-control" name="day'.(int)$i.'">';
        if( !empty( $prayers[$i] ) ){
            echo esc_textarea( $prayers[$i] );
        }
        echo'</textarea></div>';
    }
   
    echo'<p><input type="submit" value="'.esc_html(__('Save','church-admin') ).'" class="button-primary" /></p>';
    echo'</form>';


    



}


function church_admin_app_menu()
{
	$licence_level = church_admin_app_licence_check(); 
	if($licence_level!='premium')
    {
        church_admin_buy_app();
    }
    else{
        //church_admin_debug('*** church_admin_app_menu()  *** ');
        global $wpdb;
	    church_admin_fix_app_default_content();
	    $defaultMenu = church_admin_app_default_menu();
        //end custom post types if existant


        $chosenMenu=get_option('church_admin_app_new_menu');
    
        //church_admin_debug('*** Grabbed saved chosen menu ***');
        //church_admin_debug($chosenMenu);
       
        
        //fix menu issues
		if ( empty( $chosenMenu) )
		{
			$chosenMenu=$defaultMenu;
			update_option('church_admin_app_new_menu',$chosenMenu,'no');
            //church_admin_debug('*** Chosen menu was empty so grabbing default and saving ***');
            //church_admin_debug($chosenMenu);
		}
       
     

		//grab highest order number
		$highestOrder=0;
		foreach( $chosenMenu AS $name=>$detail)  {if( $detail['order']>$highestOrder)$highestOrder=$detail['order'];}
		$nextOrder=$highestOrder+1;

		$appContentItems=array();


		//posted category
        //church_admin_debug('***** Add posted categories *****');
		if(!empty( $_POST['ca_category'] ) )
		{
			$categories = get_categories( array(
			    'orderby' => 'name',
			    'parent'  => 0
			) );
			foreach( $categories AS $key=>$detail)
			{
				if( $detail->slug==sanitize_text_field( $_POST['ca_category'] ) )$chosenMenu[$detail->slug.'-category']=array('edit'=>TRUE,'item'=>$detail->cat_name,'show'=>TRUE,'type'=>"category",'order'=>$nextOrder,'loggedinOnly'=>FALSE);
				$nextOrder+=1;
			}
			update_option('church_admin_app_new_menu',$chosenMenu,'no');

		}
        //church_admin_debug($chosenMenu);
        //church_admin_debug('**** End Add posted categories ****');
		//Add app-content items

        //church_admin_debug('**** Add App Content Items ****');
		$defaultItemsIDs=get_option('church_admin_app_defaults');
		$sql=array();
		if(!empty( $defaultItemsIDs) )
		{
			foreach( $defaultItemsIDs AS $name=>$ID)
			{
				$sql[]= ' AND ID!="'.(int)$ID.'" ';
			}
		}
		$query='SELECT * FROM '.$wpdb->posts.' WHERE post_type="app-content" AND post_status="publish" '.implode("",$sql);
		//church_admin_debug $query);
		$appContent=$wpdb->get_results( $query);
		if(!empty( $appContent) )
		{
			foreach( $appContent AS $row)
			{

				if(!array_key_exists( $row->post_name,$chosenMenu) )
				{
					$chosenMenu[$row->post_name]=array('edit'=>TRUE,'item'=>strip_tags($row->post_title),'show'=>TRUE,'type'=>"app-content",'order'=>$nextOrder);
					$nextOrder+=1;
				}
				update_option('church_admin_app_new_menu',$chosenMenu,'no');
			}
		}
        //church_admin_debug($chosenMenu);
        //church_admin_debug('**** END Add App Content Items ****');
        //remove button done after content added!
        if(!empty( $_POST['remove-app-menu'] ) )
        {
            //church_admin_debug('***** remove-app-menu fired ****');
            $key = sanitize_text_field( sanitize_text_field( $_POST['remove-app-menu'] ) );
            //church_admin_debug('Attempt to remove '.$key);
            unset( $chosenMenu["{$key}"] );
            
            update_option('church_admin_app_new_menu',$chosenMenu,'no');
            //church_admin_debug($chosenMenu);
            //church_admin_debug('***** END remove-app-menu fired ****');
        }
		//clear off items that no longer exist
        
        //church_admin_debug('**** clear non existent items ****');
		foreach( $chosenMenu AS $name=>$menuItem)
		{
			$delete=TRUE;
			//check if a default item
			if(!empty( $defaultMenu[$name] ) )$delete=FALSE;
			//check if an app-content item
			if(!empty( $appContentItems)&& in_array( $name,$appContentItems) && $chosenMenu[$name]['type']=='app-content')$delete=FALSE;
			//check if a post category
			$categories = get_categories( array(
			    'orderby' => 'name',
			    'parent'  => 0
			) );
			foreach( $categories AS $key=>$details)
			{
				if( $name==$details->slug.'-category')$delete=FALSE;
			}
		}
        //church_admin_debug($chosenMenu);
        //church_admin_debug('**** END clear non existent items ****');
        $update_app_menu = !empty($_POST['update-app-menu'] )? sanitize_text_field( stripslashes($_POST['update-app-menu'] )):null;
		if(!empty( $update_app_menu ) )
		{
            //church_admin_debug('***** UPDATE APP MENU fired ****');
			unset( $update_app_menu );
			foreach( $chosenMenu AS $name=>$item)
			{

					if(!empty( $_POST[$name] ) )
					{
						$chosenMenu[$name]['show']=true;
					}
					else
					{
						$chosenMenu[$name]['show']=FALSE;
					}
                    switch( $name)
                    {
                        case'home':case'account':
                            $chosenMenu[$name]['loggedinOnly']=0;
                        break;
                        default:
                            if(!empty( $_POST['loggedIn-'.esc_html( $name)] ) )  {
                                $chosenMenu[$name]['loggedinOnly']=1;
                            }else{
                                $chosenMenu[$name]['loggedinOnly']=0;
                            }
                        break;
                    }


			}

			update_option('church_admin_app_new_menu',$chosenMenu,'no');
            //church_admin_debug($chosenMenu);
            //church_admin_debug('***** END UPDATE APP MENU fired ****');
			echo '<div class="notice notice-success"><p>'.esc_html(__('App menu updated','church-admin') ).'</p></div>';
		}
        //church_admin_debug('*** END App menu updated ');
		$chosenMenu=get_option('church_admin_app_new_menu');
        //church_admin_debug($chosenMenu);
        echo'<h2 class="app-menu-toggle">'.esc_html(__('App Menu ','church-admin') ).'</h2>';
		
		echo'<div class="app-menu" >';
        echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=reset-app-menu&section=app','reset-app-menu').'">'.esc_html(__("Reset app menu to default",'church-admin') ).'</a></p>';
        echo'<p><strong><em>'.esc_html(__('Click on menu titles, to edit them - use tab not enter to save!','church-admin') ).'</em></strong></p>';

			$tableOutput=array();
			foreach( $chosenMenu AS $name=>$item)
			{
                $remove='&nbsp;';
				switch( $name)
				{
                    case 'home':
                    case'account':
                    case 'logout':
                    case 'notification-settings':
                        $show='&#x2714;';
                        $loggedinOnly=__('Always Available');

                    break;
                    case'settings':
                    case'logout':
                        $show='&#x2714;';
                        if(!empty( $item['loggedinOnly'] ) && $item['loggedinOnly']==1)  {$flag=' checked="checked" ';}else{$flag='';}
                        $loggedinOnly='<input type="checkbox" class="app-login" data-name="'.esc_html( $name).'" name="loggedIn-'.esc_html( $name).'" '.$flag.' />';

                    break;
					default:
                            $show='<input type="checkbox" class="app-show" data-name="'.esc_html( $name).'" name="'.esc_html( $name).'" '.checked(1,$item['show'],FALSE).' value="true" />';
                            if(!empty( $item['loggedinOnly'] ) && $item['loggedinOnly']==1)  {$flag=' checked="checked" ';}else{$flag='';}
                            $loggedinOnly='<input type="checkbox" class="app-login-only" data-name="'.esc_html( $name).'" name="loggedIn-'.esc_html( $name).'" '.$flag.' />';

                    break;
				}
				if(!empty( $item['type'] ) )
                {
                    switch ( $item['type'] )
                    {
                        case 'app':
                            $type=__('Default app content','church-admin');
                              $button='&lt;button class="tab-button" data-tab="#'.esc_attr($name).'"&gt;'.esc_html(__('Your text','church-admin') ).'&lt;/button&gt;';
                        break;
                        case'app-content':
                            $type=__('Your edited app content','church-admin');
                            $button='&lt;button id="myButton" class="button" data-page="'.esc_attr($name).'"&gt;'.esc_html(__('Your text','church-admin') ).'&lt;/button&gt;';
                            $remove='<form action="admin.php?page=church_admin/index.php&action=app" method="post"><input type="hidden" name="remove-app-menu" value="'.esc_html( $name).'" />'.wp_nonce_field('app').'<input type="submit" class="button-secondary" value="'.esc_html(__('Remove item','church-admin') ).'" /></form>';
                        break;
                        case'category':
                            $type=__('Posts Category','church-admin');
                             $button='&lt;button class="tab-button" data-tab="#category" data-catname="'.esc_attr($name).'"&gt;'.esc_html(__('Your text','church-admin') ).'&lt;/button&gt;';
                        break;
                    }
                }else $type=__('Default app content','church-admin');

				$tableOutput[$item['order']]= '<tr class="sortable-row ui-sortable-handle" id="'.esc_html( $name).'"><td>'.$show.'</td><td>'.$loggedinOnly.'</td><td id="item-'.esc_html( $name).'"><span class="ca-editable" data-item="'.esc_html( $name).'">'.esc_html( $item['item'] ).'</td><td>'.$type.'</td><td><span id="copy'.esc_html( $name).'">'.esc_html($button).'</span> <span class="copy dashicons dashicons-clipboard" data-id="copy'.esc_html( $name).'"></span></td><td>'.$remove.'</div>';
			}
			ksort( $tableOutput);
			////church_admin_debugimplode("\r\n",$tableOutput) );
            echo'<p><strong>'.esc_html(__('Button code works from app version 2.6','church-admin') ).'</strong></p>';
			$theader='<div class="church-admin-form-group"><label>'.esc_html(__('Show','church-admin') ).'</th><th>'.esc_html(__('Only logged in user sees item in app menu','church-admin') ).'</th><th>'.esc_html(__('Item','church-admin') ).'</th><th>'.esc_html(__('Content type','church-admin') ).'</th><th>'.esc_html(__('Button code in app','church-admin') ).'</th><th>'.esc_html(__('Remove','church-admin') ).'</th></tr>';
			echo'<table id="sortable" class="widefat striped"><thead>'.$theader.'</thead><tbody class="content ui-sortable">';
			echo implode("\r\n",$tableOutput);
			echo'</tbody><tfoot>'.$theader.'</tfoot></table>';

			echo'<h3>'.esc_html(__("Add post categories to app menu choices",'church-admin') ).'</h3>';
			echo'<form action="admin.php?page=church_admin/index.php&action=app" method="post">';
            wp_nonce_field('app');
			echo '<p><select name="ca_category">';
			foreach( $categories AS $key=>$detail)
			{
				if ( empty( $chosenMenu[$detail->slug.'  - '.esc_html(__("Category",'church-admin'))] ) )
				{
					echo'<option value="'.esc_attr($detail->slug).'">'.esc_html( $detail->cat_name).'</option>';
				}
			}
			echo'</select><input type="submit" value="'.esc_html(__('Add category','church-admin') ).'" class="button-primary" /></p></form>';
	echo'</div>';
	$nonce = wp_create_nonce("church_admin_app_menu_order");
    $menuNonce = wp_create_nonce("edit-app-menu");
	echo'
    <script type="text/javascript">
	
 jQuery(document).ready(function( $) {
		$("body").on("click",".copy",function(e)  {
			$(".copy").addClass("dashicons-clipboard");
			$(".copy").removeClass("dashicons-yes");
			e.preventDefault();
			var ID=$(this).data("id");
			var copyText=$("#"+ID).text();
			navigator.clipboard.writeText(copyText);
			
			$(this).removeClass("dashicons-clipboard");
			$(this).addClass("dashicons-yes");
		});

 var status;
     $("body").on("change",".app-show",function()  {
        var menuItem=$(this).data("name");
        if( $(this).is(":checked") )  {status="ON";}else{status="OFF"}
		var data = {"action": "church_admin",
						"method": "app-menu-show",
						"nonce": "'.$menuNonce.'",
						"menuItem":menuItem,
                        "status":status
					};
                    console.log(data)
        jQuery.post(ajaxurl, data, function(response) { console.log(response) });
    });
    $("body").on("change",".app-login-only",function()  {
        var menuItem=$(this).data("name");
        console.log( $(this).checked)
        if( $(this).is(":checked") )  {status="ON";}else{status="OFF"}
		var data = {"action": "church_admin",
						"method": "app-menu-login",
						"nonce": "'.$menuNonce.'",
						"menuItem":menuItem,
                        "status":status
					};
                    console.log(data)
        jQuery.post(ajaxurl, data, function(response) { console.log(response) });
    });
    $("body").on("click",".ca-editable",function()  {

        var current=$(this).html();
        var item=$(this).data("item");

        //undo all current input fields
        $("body .ca-menu-edit").each(function()  {
			var menuTitle=$(this).val();
			var menuItem=$(this).data("item");
            var html="<span class=\"ca-editable\" data-item=\""+menuItem+"\">"+menuTitle+"</span>";
			$("#item-"+menuItem).html(html);
            })

        var html =\'<input type="text" class="ca-menu-edit" value="\'+current+\'" data-item="\'+item+\'" />\';
        $("#item-"+item).html(html);
    });
    $("body").on("change", ".ca-menu-edit", function()
    {

					console.log("Changed");
					var newMenuTitle=$(this).val();
					var menuItem=$(this).data("item");
					var data = {
						"action": "church_admin",
						"method": "edit-app-menu",
						"nonce": "'.$menuNonce.'",
						"menuTitle":newMenuTitle,
                        "menuItem":menuItem
					};
					console.log(data);
					// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
					jQuery.post(ajaxurl, data, function(response) {

                        console.log(response);
						$("#item-"+menuItem).html(response);

					});



    });
    var fixHelper = function(e,ui)  {
            ui.children().each(function() {
                $(this).width( $(this).width() );
            });
            return ui;
        };
    var sortable = $("#sortable tbody.content").sortable({
    helper: fixHelper,
    stop: function(event, ui) {
        //create an array with the new order


				var Order = $(this).sortable(\'toArray\').toString();
				var data = {
				"action": "church_admin",
				"method": "app_menu_order",
				"order": Order,
				"nonce": "'.$nonce.'"
			};
			console.log(data);
			console.log(ajaxurl);
        $.ajax({
            url: ajaxurl,
            type: "post",
            data:  data,
            error: function() {
                console.log("theres an error with AJAX");
            },
            success: function() {

            }
        });}}); $("#sortable tbody.content").disableSelection();
	});</script>';

		echo'<script type="text/javascript">jQuery(function()  {  jQuery(".app-menu-toggle").click(function()  {jQuery(".app-menu").toggle();  });});</script>';
		}
}

function ca_update_app_menu($action,$title){
    church_admin_debug('****** ca_update_app_menu *******');
    global $wpdb;
    $menu=get_option('church_admin_app_new_menu');
  

    if(empty($action)){return;}
    if(empty($title)){return;}
    $sanitized_title = sanitize_title( $title );

    switch($action){
        case 'remove':
            church_admin_debug('Remove "'.$title.'" from app menu');
            unset($menu[$sanitized_title]);
        break;
        case 'add':
            church_admin_debug('Add "'.$title.'" to app menu');
            if(empty($menu[$sanitized_title])){
                //get highest order
                $highest_order = 1;
                foreach($menu AS $key=>$item){
                    if ($item['order'] > $highest_order){
                        $highest_order = $item['order'];
                    }
                }
                $order = $highest_order++;

                $menu[$sanitized_title]=array(
                    'edit'  => 1,
                    'item'  => $title,
                    'show'  => 1,
                    'type'  => 'app-content',
                    'order' => $order
                );
            
            }else{
                church_admin_debug('Already had menu item '.$sanitized_title);
            }

        break;

    }
   //church_admin_debug($menu);
    update_option('church_admin_app_new_menu',$menu);

}