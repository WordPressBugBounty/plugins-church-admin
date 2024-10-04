<?php 

function church_admin_spiritual_gifts( $admin_email=NULL)
{
    $licence =get_option('church_admin_app_new_licence');
		if($licence!='standard' && $licence!='premium'){
			return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="https://buy.stripe.com/fZedSB9ErbQRcjm14V">Upgrade</a></p></div>';
			
		}
    global $wpdb,$church_admin_spiritual_gifts;

    $out='<h3>'.__('Spiritual gifts','church-admin').'</h3>';
    if(!empty( $admin_email))$adminEmails=explode(",",$admin_email);
    if(is_user_logged_in() )
	{
        //user logged in
        $user = wp_get_current_user();
        $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
        $personName=church_admin_get_person( $person->people_id);
        if(!empty( $_POST['save-gifts'] )&&wp_verify_nonce( $_POST['save-gifts'],'save-gifts') )
        {
            //user saved form
            $out.=church_admin_save_gifts_questionnaire( $person);
        }
        else
        {
            //output form
            $out.=church_admin_questionnaire_form();  
        }
    }
    else
    {
        //user not logged in
        if(!empty( $_POST['first-step'] )&&wp_verify_nonce( $_POST['first-step'],'first-step') )
        {
            //user done first step form
            if(!church_admin_front_end_email_check() )
            {
                //not recognised so give form with registration details
                $out.=church_admin_questionnaire_form();  
            }
            else 
            {
                //recognised so give login form
                $out.='<p>'.esc_html( __('Looks like you are on our system, please login','church-admin' ) ).'</p>'.wp_login_form(array('echo'=>FALSE) ).'<p><a href="'.esc_url( wp_lostpassword_url(get_permalink() ) ).'" alt="'.esc_html( __( 'Lost Password', 'church-admin' )).'">'.esc_html( __( 'Lost Password', 'church-admin' )).'</a></p>';
            }
        }
        elseif(!empty( $_POST['save-gifts'] )&&wp_verify_nonce( $_POST['save-gifts'],'save-gifts')
        &&!empty( $_POST['ca-email'] )&&is_email( $_POST['ca-email'] ) )
        {
            //save form of underegistered user
            $sqlsafe=array();
            foreach( $_POST AS $key=>$value)$sqlsafe[$key]=esc_sql(church_admin_sanitize( $value) );
            if(!empty( $_POST['mail_send'] ) )  {$mail_send=1;}else{$mail_send=0;}
            $check=$wpdb->get_row('SELECT household_id FROM '.$wpdb->prefix.'church_admin_people WHERE first_name="'.$sqlsafe['first_name'].'" AND last_name="'.$sqlsafe['last_name'].'" AND email="'.$sqlsafe['ca-email'].'"');
            if ( empty( $check) )
            {
                //new person so save
                $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household (address) VALUES("") ');
                $household_id=$wpdb->insert_id;
                $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people (first_name,last_name,email,head_of_household,household_id,show_me,mail_send,people_type_id,sex,first_registered) VALUES("'.$sqlsafe['first_name'].'","'.$sqlsafe['last_name'].'","'.$sqlsafe['ca-email'].'",1,"'.(int)$household_id.'",0,"'.(int)$mail_send.'",1,1,"'.esc_sql(wp_date('Y-m-d')).'") ');
                $people_id=$wpdb->insert_id;
                $people=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
                
            }
            else
            {
                $out.='<p>'.esc_html( __("Results are not being saved as you didn't login",'church-admin' ) ).'</p>';
                //not saving not logged in person in directory
                $people=NULL;
            }
            $out.=church_admin_save_gifts_questionnaire( $people);
        }
        else
        {
            /***************************************
            *
            *   First step email to check
            *
            ****************************************/      
            $out.='<div id="ca-first-step">';
            
            $out.='<form action="" method="POST"><div class="church-admin-form-group"><label>'.esc_html( __('Please start with your email address, so we can send your results','church-admin' ) ).'</label>';
            $out.='<input type="email" id="ca-email-address" name="ca-email" class="church-admin-form-control"></div>';
            $out.=wp_nonce_field('email-check','nonce',FALSE,FALSE);
            $out.='<p><input type="hidden" name="first-step" value="'.wp_create_nonce('first-step').'" /><input type="submit" class="btn btn-success" value='.esc_html( __("Next &raquo;",'church-admin' ) ).'" /></p></form></div>';
        }
    
    
    }
    
   return $out;
   
}

function church_admin_questionnaire_form()
{
    global $wpdb,$church_admin_spiritual_gifts;
    $questions=array(
        1=> esc_html(__('I am compassionate for the hurting and like to help','church-admin' )) ,
        2=>esc_html(__("I often get impressions of God's thoughts and feelings about something or someone, and
        then have a strong desire to share them with the person concerned.",'church-admin' ) ),
        3=>esc_html(__("I like things to work efficiently and effectively and get frustrated when they are badly
        organised.",'church-admin' ) ),
        4=>esc_html(__("I see where people need encouragement",'church-admin' ) ),
        5=>esc_html(__("I have a strong desire to see people whole",'church-admin' ) ),
        6=>esc_html(__("I enjoy getting to know people who are not yet Christians",'church-admin' ) ),
        7=>esc_html(__("When I see people in need, I often want to give to them",'church-admin' ) ),
        8=>esc_html(__("I prefer following to leading",'church-admin' ) ),
        9=>esc_html( __('I often pray for a long time','church-admin' ) ),
        10=>esc_html(__("I prefer making people feel comfortable and safe than making a good impression",'church-admin' ) ),
        11=>esc_html(__("I want to seek out those far from Christ and bring them to the Lord",'church-admin' ) ),
        12=>esc_html(__("I have a strong desire to bring truth where there is wrong thinking about God.",'church-admin' ) ),
        13=>esc_html(__("I love to pray for the sick for healing",'church-admin' ) ),
        14=>esc_html(__("I can motivate and challenge others to get the job done.",'church-admin' ) ),
        15=>esc_html(__("I help those who are unsupported and uncared for.",'church-admin' ) ),
        16=>esc_html(__("Sometimes I experience an instant understanding about people which I didn't learn by natural means",'church-admin' ) ),
        17=>esc_html(__("I can identify and use the resources available to complete a task.",'church-admin' ) ),
        18=>esc_html(__("I will go to great lengths to encourage someone",'church-admin' ) ),
        19=>esc_html(__("I am able to discern the problems people have through what they say and don't say.",'church-admin' ) ),
        20=> esc_html( __("In social settings, I am drawn to talk with unbelievers",'church-admin' ) ),
        21=> esc_html( __("My giving is often an answer to specific prayer.",'church-admin' ) ),
        22=> esc_html( __("I enjoy saying yes to requests for help.",'church-admin' ) ),
        23=> esc_html( __("I feel honoured when asked to intercede for someone",'church-admin' ) ),
        24=> esc_html( __("I love welcoming friends, strangers and foreigners into my home.",'church-admin' ) ),
        25=> esc_html( __("I lead by example and others follow.",'church-admin' ) ),
        26=> esc_html( __("I love studying the Bible and passing on what I have learnt.",'church-admin' ) ),
        27=> esc_html( __("I have seen people healed physically when I have prayed for them.",'church-admin' ) ),
        28=> esc_html( __("I will take responsibility for a group of people.",'church-admin' ) ),
        29=> esc_html( __("I can patiently support those going through painful experiences to get through them.",'church-admin' ) ),
        30=> esc_html( __("When I share an insight about God, people have put it into practice.",'church-admin' ) ),
        31=> esc_html( __("I can see the solution to organisational problems.",'church-admin' ) ),
        32=> esc_html( __("I prefer affirming the good in someone than pointing out their mistakes.",'church-admin' ) ),
        33=> esc_html( __("I am interested in understanding more the complexities of people.",'church-admin' ) ),
        34=> esc_html( __("I am confident that people will come to Christ through me.",'church-admin' ) ),
        35=> esc_html( __("When I know what I give is really needed, I am not worried about how it will be replenished.",'church-admin' ) ),
        36=> esc_html( __("I enjoy serving others so they can do their ministry better.",'church-admin' ) ),
        37=> esc_html( __("When I become aware of a need, my first reaction is prayer.",'church-admin' ) ),
        38=> esc_html( __("I am a people person",'church-admin' ) ),
        39=> esc_html( __("I help people cast of their burdens and become more mature in Christ",'church-admin' ) ),
        40=> esc_html( __("I can make complex issues simpler to understand.",'church-admin' ) ),
        41=> esc_html( __("When I pray for someone, I am confident that God will touch them.",'church-admin' ) ),
        42=> esc_html( __("I see the need to prioritise my work and delegate well",'church-admin' ) ),
        43=> esc_html( __("I can empathise and help people heal emotionally.",'church-admin' ) ),
        44=> esc_html( __("I am willing to speak out the truth even when reactions are unpleasant.",'church-admin' ) ),
        45=> esc_html( __("I can see how an organisation functions and plan ahead to alleviate issues.",'church-admin' ) ),
        46=> esc_html( __("I feel grieved when others are discouraged.",'church-admin' ) ),
        47=> esc_html( __("I find it easy to understand the struggles others go through",'church-admin' ) ),
        48=> esc_html( __("I sense the plight of those far from God deeply.",'church-admin' ) ),
        49=> esc_html( __("I love giving anonymously to meet needs.",'church-admin' ) ),
        50=> esc_html( __("I am a behind the scenes sort of person",'church-admin' ) ),
        51=> esc_html( __("I live my like in a way that lives out prayer as the foundation for everything in the Kingdom",'church-admin' ) ),
        52=> esc_html( __("I make sure people that are alone in a social setting feel included and welcomed.",'church-admin' ) ),
        53=> esc_html( __("I like helping people grow in their faith.",'church-admin' ) ),
        54=> esc_html( __("I like challenging other people to understand and obey truth.",'church-admin' ) ),
        55=> esc_html( __("I find praying for healing exciting",'church-admin' ) ),
        56=> esc_html( __("I find I can communicate vision so that people want to get involved.",'church-admin'))
    );

    /************************
     * OUTPUT
     *************************/
    
    $out='<form action="" method="POST">';
    if(!is_user_logged_in() )
    {
        $out.='<p>'.esc_html( __('Please register your details to receive emails from us and get you gift results','church-admin' ) ).'</p>';
        $out.='<div class="church-admin-form-group"><label for="first_name">'.esc_html( __('First Name','church-admin' ) ).' *</label><input placeholder="'.esc_html( __('First Name','church-admin' ) ).'" type="text" required="required" class="church-admin-form-control" name="first_name" /></div>';
        $out.='<div class="church-admin-form-group"><label for="last_name">'.esc_html( __('Last Name','church-admin' ) ).' *</label><input placeholder="'.esc_html( __('Last Name','church-admin' ) ).'" type="text" required="required" class="church-admin-form-control" name="last_name" /></div>';
        $out.='<div class="church-admin-form-group"><label for="email">'.esc_html( __('Email address','church-admin' ) ).' </label><input placeholder="'.esc_html( __('Email','church-admin' ) ).'" type="email"  class="church-admin-form-control ca-email" name="ca-email"';
        $out.=' value="'.esc_html( sanitize_text_field(stripslashes($_POST['ca-email'])) ).'"';
        $out.='/></div>';
        $out.='<div class="church-admin-form-group"><label>'.esc_html( __('I give permission to receive email','church-admin' ) ).'</label></div>';
        $out.='<div class="checkbox"><label ><input type="checkbox" name="mail_send" value="TRUE" data-name="mail_send" ';
        if(!empty( $data->mail_send) )$out.='checked="checked" ';
        $out.='/></div>';
    }
    $out.='<p>'.esc_html( __('Please move the slider left or right for each of these statements to help discover your spiritual gifts','church-admin' ) ).'</p>';
    foreach( $questions AS $key=>$question)
    {
        $out.='<p><label>'.(int)$key.') '.esc_html( $question).'</label></p><p><span class="small">'.esc_html( __('Not at all true of me','church-admin' ) ).'</span> <input  type="range" min=1 max=6 value=2 name="q'.(int)$key.'" /><span class="small"> '.esc_html( __('Completely describes me','church-admin' ) ).'</span></p>';
    }
    $out.='<p><input type="hidden" name="save-gifts" value="'.wp_create_nonce('save-gifts').'" /><input type="submit" class="button-priamry btn btn-success" value="'.esc_html( __('Save','church-admin' ) ).'" /></p></form>';

     return $out;

}

function church_admin_save_gifts_questionnaire( $person)
{
    global $wpdb,$church_admin_spiritual_gifts;
    $out='';
    $mercy=(int)$_POST['q1']+(int)$_POST['q15']+(int)$_POST['q29']+(int)$_POST['q43'];
           $prophecy=(int)$_POST['q2']+(int)$_POST['q16']+(int)$_POST['q30']+(int)$_POST['q44'];
           $administration=(int)$_POST['q3']+(int)$_POST['q17']+(int)$_POST['q31']+(int)$_POST['q45'];
           $encouragement=(int)$_POST['q4']+(int)$_POST['q18']+(int)$_POST['q32']+(int)$_POST['q46'];
           $counselling=(int)$_POST['q5']+(int)$_POST['q19']+(int)$_POST['q33']+(int)$_POST['q47'];
           $evangelism=(int)$_POST['q6']+(int)$_POST['q20']+(int)$_POST['q34']+(int)$_POST['q48'];
           $giving=(int)$_POST['q7']+(int)$_POST['q21']+(int)$_POST['q35']+(int)$_POST['q49'];
           $helps=(int)$_POST['q8']+(int)$_POST['q22']+(int)$_POST['q36']+(int)$_POST['q50'];
           $intercession=(int)$_POST['q9']+(int)$_POST['q23']+(int)$_POST['q37']+(int)$_POST['q51'];
           $hospitality=(int)$_POST['q10']+(int)$_POST['q24']+(int)$_POST['q38']+(int)$_POST['q52'];
           $pastoring=(int)$_POST['q11']+(int)$_POST['q25']+(int)$_POST['q39']+(int)$_POST['q53'];
           $teaching=(int)$_POST['q12']+(int)$_POST['q26']+(int)$_POST['q40']+(int)$_POST['q54'];
           $healing=(int)$_POST['q13']+(int)$_POST['q27']+(int)$_POST['q41']+(int)$_POST['q55'];
           $leadership=(int)$_POST['q14']+(int)$_POST['q28']+(int)$_POST['q42']+(int)$_POST['q56'];
           $gifts=array(
                  1 => $mercy,
                  2 =>$prophecy,
                  3=>$administration ,
                  4=>$encouragement,
                   5=>$counselling,
                  6 =>$evangelism,
                  7=>$giving,
                  8=>$helps,
                  9 =>$intercession ,
                  10 =>$hospitality,
                  11=>$pastoring,
                  12 =>$teaching,
                  13 =>$healing,
                  14=>$leadership
           );
           
           arsort ( $gifts);
           $mygifts=array_keys( $gifts);
           $out.='<p>'.esc_html( __('Your top three spiritual gifts are...','church-admin' ) ).'</p>';
           $out.='<ol>';
           $out.='<li>'.$church_admin_spiritual_gifts[$mygifts[0]].'</li>';
           $out.='<li>'.$church_admin_spiritual_gifts[$mygifts[1]].'</li>';
           $out.='<li>'.$church_admin_spiritual_gifts[$mygifts[2]].'</li>';
           $out.='</ol>';
            

           if(!empty( $person->people_id) )
           {
               church_admin_update_people_meta( $mygifts[0],$person->people_id,'spiritual-gifts',date('Y-m-d') );
               church_admin_update_people_meta( $mygifts[1],$person->people_id,'spiritual-gifts',date('Y-m-d') );
               church_admin_update_people_meta( $mygifts[2],$person->people_id,'spiritual-gifts',date('Y-m-d') );
               $adminmessage='<p>'.esc_html(  sprintf(__('%1$s has filled out the spiritual gifts questionnaire','church-admin' ) ,church_admin_get_person( $person->people_id) ) ).'</p>';
               
               if(!empty( $admin_emails) )
               {
                   foreach( $admin_emails AS $key=>$adminEmail)
                 {  
                    
                       church_admin_email_send($adminEmail,esc_html( __('New spiritual gift questionnaire completed','church-admin' ) ),$adminmessage,null,null,null,null,null,TRUE); 
                      
                   }
               }
               
           }
           $ca_email = !empty($_POST['ca_email'])?sanitize_text_field(stripslashes($_POST['ca_email'])):null;
           if(!empty(  $ca_email)&&is_email( $ca_email ) )
               {
                    church_admin_email_send($ca_mail,esc_html( __('Your spiritual gift questionnaire','church-admin' ) ),$out,null,null,null,null,null,TRUE); 
               }
   return $out;
}