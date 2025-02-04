<?php
    require_once("include/config.php");
    require_once(DIR_FS."islogin.php");
    
    $instance = new transaction();

    //DEFAULT PDF DATA:
    $get_logo = $instance->get_system_logo();
    $system_logo = isset($get_logo['logo'])?$instance->re_db_input($get_logo['logo']):'';
    $get_company_name = $instance->get_company_name();
    $system_company_name = isset($get_company_name['company_name'])?$instance->re_db_input($get_company_name['company_name']):'';
    $image_path=SITE_URL."upload/logo/".$system_logo;
    
    $instance_payroll = new payroll();
    $filter_array = array();
    $get_adjustments_data = array();
    $company = 0;
    $payroll_date = '';
    $print_type = '';
    $output_type = '';
    $broker = '';
    $pdf_for_broker = '';
    
    $broker_name = '';
    $broker_address = '';
    $broker_city = '';
    $broker_state = '';
    $broker_zipcode = '';
    $broker_number = '';
    $broker_branch = '';
    
    //filter payroll adjustments log report
    if(isset($_GET['filter']) && $_GET['filter'] != '')
    {
        $filter_array = json_decode($_GET['filter'],true);//echo '<pre>';print_r($filter_array);exit;
        $company = isset($filter_array['company'])?$filter_array['company']:0;
        $print_type = isset($filter_array['print_type'])?$filter_array['print_type']:'';
        // $payroll_date = isset($filter_array['payroll_date'])?$filter_array['payroll_date']:'';
        $payroll_id = isset($filter_array['payroll_id'])?$filter_array['payroll_id']:'';
        $payroll_date = $instance_payroll->get_payroll_uploads($payroll_id);
        $payroll_date = $payroll_date['payroll_date'];
        $output_type = isset($filter_array['output_type'])?$filter_array['output_type']:'';
        $broker = isset($filter_array['broker'])?$filter_array['broker']:0;
        $pdf_for_broker = isset($filter_array['pdf_for_broker'])?$filter_array['pdf_for_broker']:'';
        
        $get_broker_commission_data = $instance_payroll->get_broker_commission_report_data($company,$payroll_id,$broker,$print_type);
        //echo '<pre>';print_r($get_broker_commission_data);exit;
        if($payroll_date != ''){ 
            $payroll_date = date('F d, Y',strtotime($payroll_date));
        }
        if($company>0)
        {
            $company = isset($get_broker_commission_data['company_name'])?$get_broker_commission_data['company_name']:'';
        }
        else
        {
            $company = 'All Company';
        }
    }
    
    $creator                = "Foxtrot User";
    $last_modified_by       = "Foxtrot User";
    $title                  = "Foxtrot Broker Statement Report Excel";
    $subject                = "Foxtrot Broker Statement Report";
    $description            = "Foxtrot Broker Statement Report. Generated on : ".date('Y-m-d h:i:s');
    $keywords               = "Foxtrot Broker Statement report office 2007";
    $category               = "Foxtrot Broker Statement Report.";
    $total_sub_sheets       = 1;
    $sub_sheet_title_array  = array("Foxtrot Broker Statement");
    $default_open_sub_sheet = 0; // 0 means first indexed sub sheets.
    $excel_name             = 'Foxtrot Broker Statement Report';
    $sheet_data             = array();
    //$i=10;
    $i=0;
            
    // Set output excel array.
    /* Headers. */
    $sheet_offset = 0;
    if(isset($get_broker_commission_data['broker_transactions']) && is_array($get_broker_commission_data['broker_transactions']) && count($get_broker_commission_data['broker_transactions'])>0)
    {
        foreach($get_broker_commission_data['broker_transactions'] as $brokers_comm_key=>$brokers_comm_data)
        {
            $z=$i+1;
            $i=$z+8;
            
            $total_broker_transactions = 0;
            $total_split_transactions = 0;
            $total_override_transactions = 0;
            $total_adjustments = 0;
            $total_payroll_draw = isset($get_broker_detail['payroll_draw'])?$get_broker_detail['payroll_draw']: 0;
            $total_base_salary = isset($get_broker_detail['salary'])?$get_broker_detail['salary']: 0;
            $total_finra_assessment = -($brokers_comm_data['finra']);
            $total_sipc_assessment = -($brokers_comm_data['sipc']); 
            $total_prior_balance = $brokers_comm_data['balance'];
            $total_forward_balance = $brokers_comm_data['prior_broker_balance'];
            $total_broker_earnings = $brokers_comm_data['prior_broker_earnings'];
            $check_minimum_check_amount = $brokers_comm_data['minimum_check_amount'];
            
            
            $get_broker_detail = $instance_payroll->get_broker_detail($brokers_comm_key);
        
            $broker_name = isset($get_broker_detail['first_name'])?$get_broker_detail['first_name'].' '.$get_broker_detail['last_name']:'';
            $broker_address1 = isset($get_broker_detail['home_address1_general'])?$get_broker_detail['home_address1_general']:'';
            $broker_address2 = isset($get_broker_detail['home_address2_general'])?$get_broker_detail['home_address2_general']:'';
            $broker_address = '';
            
            if($broker_address1 != '')
            {
                $broker_address = $broker_address1;
            }
            if($broker_address1 && $broker_address2 != '')
            {
                $broker_address .= ', '.$broker_address2;
            }
            else
            {
                $broker_address = $broker_address2;
            }
            
            $broker_city = isset($get_broker_detail['city'])?$get_broker_detail['city']:'';
            $broker_state = isset($get_broker_detail['state_name'])?$get_broker_detail['state_name']:'';
            $broker_zipcode = isset($get_broker_detail['zip_code'])?$get_broker_detail['zip_code']:'';
            $broker_number = isset($get_broker_detail['id'])?$get_broker_detail['id']:'';
            $broker_branch = isset($get_broker_detail['branch_name'])?$get_broker_detail['branch_name']:'';
            
            if($broker_state != '' && $broker_zipcode > 0)
            {
                $broker_zipcode = ', '.$broker_zipcode;
            }
            else
            {
                $broker_zipcode = '';
            } 
         //echo $i.',';
            
                    
            $sheet_data[$sheet_offset]['A'.$z]=array('LOGO',array('bold','center','color'=>array('000000'),'size'=>array(12),'font_name'=>array('Calibri'),'merge'=>array('A'.$z,'B'.$z)));
            $sheet_data[$sheet_offset]['C'.$z]=array($company,array('bold','center','color'=>array('000000'),'size'=>array(12),'font_name'=>array('Calibri'),'merge'=>array('C'.$z,'H'.$z)));
            $sheet_data[$sheet_offset]['I'.$z]=array($system_company_name,array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('I'.$z,'K'.$z)));
            
            $sheet_data[$sheet_offset]['A'.($z+1)]=array('COMMISSION STATEMENT',array('bold','center','color'=>array('000000'),'size'=>array(14),'font_name'=>array('Calibri'),'merge'=>array('A'.($z+1),'K'.($z+2))));
            $sheet_data[$sheet_offset]['A'.($z+3)]=array($payroll_date,array('bold','center','color'=>array('000000'),'size'=>array(12),'font_name'=>array('Calibri'),'merge'=>array('A'.($z+3),'K'.($z+3))));
            $sheet_data[$sheet_offset]['A'.($z+4)]=array($broker_name,array('normal','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.($z+4),'B'.($z+4))));
            $sheet_data[$sheet_offset]['I'.($z+4)]=array('BROKER#/Fund/Internal : '.$broker_number.' / '.$brokers_comm_data['fund'].' / '.$brokers_comm_data['internal'],array('normal','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('I'.($z+4),'K'.($z+4))));
            $sheet_data[$sheet_offset]['A'.($z+5)]=array($broker_address,array('normal','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.($z+5),'B'.($z+5))));
            $sheet_data[$sheet_offset]['I'.($z+5)]=array('BRANCH# : '.strtoupper($broker_branch),array('normal','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('I'.($z+5),'K'.($z+5))));
            $sheet_data[$sheet_offset]['A'.($z+6)]=array($broker_city,array('normal','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.($z+6),'B'.($z+6))));
            $sheet_data[$sheet_offset]['A'.($z+7)]=array($broker_state.''.$broker_zipcode,array('normal','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.($z+7),'B'.($z+7))));
            //$sheet_data[$sheet_offset]['A'.($z+8)]=array('COMMISSION STATEMENT for '.strtoupper($broker_name),array('bold','center','color'=>array('000000'),'size'=>array(12),'font_name'=>array('Calibri'),'merge'=>array('A'.($z+8),'K'.($z+8))));
            $sheet_data[$sheet_offset]['A'.($z+8)]=array('TRADE DATE',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri')));
            $sheet_data[$sheet_offset]['B'.($z+8)]=array('CLIENT',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri'),'merge'=>array('B'.($z+8),'C'.($z+8))));
            $sheet_data[$sheet_offset]['D'.($z+8)]=array('INVESTMENT',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri')));
            $sheet_data[$sheet_offset]['E'.($z+8)]=array('B/S',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri')));
            $sheet_data[$sheet_offset]['F'.($z+8)]=array('INVESTMENT AMOUNT',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri')));
            $sheet_data[$sheet_offset]['G'.($z+8)]=array('GROSS COMMISSION',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri')));
            $sheet_data[$sheet_offset]['H'.($z+8)]=array('CLEARING CHARGE',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri')));
            $sheet_data[$sheet_offset]['I'.($z+8)]=array('NET COMMISSION',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri')));
            $sheet_data[$sheet_offset]['J'.($z+8)]=array('RATE',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri')));
            $sheet_data[$sheet_offset]['K'.($z+8)]=array('BROKER COMMISSION',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri')));
                
            
            if(isset($brokers_comm_data['direct_transactions']) && $brokers_comm_data['direct_transactions'] != array())
            {
                $broker_investment_amount = 0;
                $broker_commission_received = 0;
                $broker_net_commission = 0;
                $broker_charges = 0;
                $broker_rate = 0;
                $broker_broker_commission = 0;
                $i++;
                $sheet_data[$sheet_offset]['A'.$i] = array('',array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'K'.$i)));
                $i++;
                $sheet_data[$sheet_offset]['A'.$i] = array('BROKER TRANSACTIONS',array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'K'.$i)));
                foreach($brokers_comm_data['direct_transactions'] as $comm_key=>$comm_data)
                {
                    $i++;
                    $sheet_data[$sheet_offset]['A'.$i] = array('PRODUCT CATEGORY: '.strtoupper($comm_key),array('bold','left','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'K'.$i)));
                    $category_investment_amount = 0;
                    $category_commission_received = 0;
                    $category_net_commission = 0;
                    $category_charges = 0;
                    $category_rate = 0;
                    $category_broker_commission = 0;
                    foreach($comm_data as $comm_sub_key=>$comm_sub_data)
                    {
                        $trade_date='';
                        $category_investment_amount = $category_investment_amount+$comm_sub_data['investment_amount'];
                        $category_commission_received = $category_commission_received+$comm_sub_data['commission_received'];
                        $category_net_commission = $category_net_commission+$comm_sub_data['net_commission'];
                        $category_charges = $category_charges+$comm_sub_data['charge'];
                        $category_rate = $category_rate+0;
                        $category_broker_commission = $category_broker_commission+$comm_sub_data['commission_paid'];
                        /*** Moved above foreach($brokers_comm_data['direct_transactions'].....) - Only needed to be updated once, since these numbers are already calculated in Payroll_Calculation() 11/9/21 ***/
                        // $total_finra_assessment = $comm_sub_data['finra'];
                        // $total_sipc_assessment = $comm_sub_data['sipc']; 
                        // $total_prior_balance = $total_prior_balance+$comm_sub_data['balance'];
                        // $total_forward_balance = $comm_sub_data['prior_broker_balance'];
                        // $total_broker_earnings = $comm_sub_data['prior_broker_earnings'];
                        
                        if(isset($comm_sub_data['buy_sell']) && $comm_sub_data['buy_sell'] == 1)
                        {
                            $buy_sell = 'B';
                        }
                        else if(isset($comm_sub_data['buy_sell']) && $comm_sub_data['buy_sell'] == 2)
                        {
                            $buy_sell = 'S';
                        }
                        else
                        {
                            $buy_sell='';
                        }
                        
                        if($comm_sub_data['trade_date'] != '0000-00-00' && $comm_sub_data['trade_date'] != ''){ $trade_date = date('m/d/Y',strtotime($comm_sub_data['trade_date'])); }
                        
                        $i++;
                        $sheet_data[$sheet_offset]['A'.$i] = array($instance->re_db_output($trade_date),array('center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['B'.$i] = array($instance->re_db_output($comm_sub_data['client_firstname'].', '.$comm_sub_data['client_lastname']),array('left','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('B'.$i,'C'.$i)));
                        $sheet_data[$sheet_offset]['D'.$i] = array($instance->re_db_output($comm_sub_data['batch_description']),array('left','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['E'.$i] = array($instance->re_db_output($buy_sell),array('center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['F'.$i] = array($instance->re_db_output('$'.number_format($comm_sub_data['investment_amount'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['G'.$i] = array($instance->re_db_output('$'.number_format($comm_sub_data['commission_received'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['H'.$i] = array($instance->re_db_output('$'.number_format($comm_sub_data['charge'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['I'.$i] = array($instance->re_db_output('$'.number_format($comm_sub_data['net_commission'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['J'.$i] = array($instance->re_db_output(number_format($comm_sub_data['rate'],2).'%'),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($comm_sub_data['commission_paid'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                    }
                    $i++;
                    $broker_investment_amount = $broker_investment_amount+$category_investment_amount;
                    $broker_commission_received = $broker_commission_received+$category_commission_received;
                    $broker_net_commission = $broker_net_commission+$category_net_commission;
                    $broker_charges = $broker_charges+$category_charges;
                    $broker_rate = $broker_rate+$category_rate;
                    $broker_broker_commission = $broker_broker_commission+$category_broker_commission;
                    
                    $sheet_data[$sheet_offset]['A'.$i] = array('* '.strtoupper($comm_key).' SUBTOTAL *',array('bold','center','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'E'.$i)));
                    $sheet_data[$sheet_offset]['F'.$i] = array($instance->re_db_output('$'.number_format($category_investment_amount,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['G'.$i] = array($instance->re_db_output('$'.number_format($category_commission_received,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['H'.$i] = array($instance->re_db_output('$'.number_format($category_charges,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['I'.$i] = array($instance->re_db_output('$'.number_format($category_net_commission,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['J'.$i] = array('',array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($category_broker_commission,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                }
                $i++;
                $total_broker_transactions = $total_broker_transactions+$broker_broker_commission;
                $sheet_data[$sheet_offset]['A'.$i] = array('*** BROKER TRANSACTIONS TOTAL ***',array('bold','center','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'E'.$i)));
                $sheet_data[$sheet_offset]['F'.$i] = array($instance->re_db_output('$'.number_format($broker_investment_amount,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['G'.$i] = array($instance->re_db_output('$'.number_format($broker_commission_received,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['H'.$i] = array($instance->re_db_output('$'.number_format($broker_charges,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['I'.$i] = array($instance->re_db_output('$'.number_format($broker_net_commission,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['J'.$i] = array('',array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($broker_broker_commission,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $i++;
                $sheet_data[$sheet_offset]['A'.$i] = array('',array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'K'.$i)));
            }
            if(isset($brokers_comm_data['override_transactions']) && $brokers_comm_data['override_transactions'] != array())
            {
                $broker_investment_amount = 0;
                $broker_commission_received = 0;
                $broker_net_commission = 0;
                $broker_charges = 0;
                $broker_rate = 0;
                $broker_broker_commission = 0;
                $i++;
                $sheet_data[$sheet_offset]['A'.$i] = array('OVERRIDE TRANSACTIONS',array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'K'.$i)));
                foreach($brokers_comm_data['override_transactions'] as $override_comm_key=>$override_comm_data)
                {
                    $i++;
                    $sheet_data[$sheet_offset]['A'.$i] = array('OVERRIDE BROKER: '.strtoupper($override_comm_key),array('bold','left','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'K'.$i)));
                    $category_investment_amount = 0;
                    $category_commission_received = 0;
                    $category_net_commission = 0;
                    $category_charges = 0;
                    $category_rate = 0;
                    $category_broker_commission = 0;
                    foreach($override_comm_data as $override_sub_key=>$override_sub_data)
                    {
                        $trade_date='';
                        $category_investment_amount = $category_investment_amount+$override_sub_data['investment_amount'];
                        $category_commission_received = $category_commission_received+$override_sub_data['commission_received'];
                        $category_net_commission = $category_net_commission+$override_sub_data['net_commission'];
                        $category_charges = $category_charges+$override_sub_data['charge'];
                        $category_rate = $category_rate+0;
                        $category_broker_commission = $category_broker_commission+$override_sub_data['rate_amount'];
                        
                        if(isset($override_sub_data['buy_sell']) && $override_sub_data['buy_sell'] == 1)
                        {
                            $buy_sell = 'B';
                        }
                        else if(isset($override_sub_data['buy_sell']) && $override_sub_data['buy_sell'] == 2)
                        {
                            $buy_sell = 'S';
                        }
                        else
                        {
                            $buy_sell='';
                        }
                        
                        if($override_sub_data['trade_date'] != '0000-00-00' && $override_sub_data['trade_date'] != ''){ $trade_date = date('m/d/Y',strtotime($override_sub_data['trade_date'])); }
                        
                        $i++;
                        $sheet_data[$sheet_offset]['A'.$i] = array($instance->re_db_output($trade_date),array('center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['B'.$i] = array($instance->re_db_output($override_sub_data['client_firstname'].', '.$override_sub_data['client_lastname']),array('lefft','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('B'.$i,'C'.$i)));
                        $sheet_data[$sheet_offset]['D'.$i] = array($instance->re_db_output($override_sub_data['batch_description']),array('left','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['E'.$i] = array($instance->re_db_output($buy_sell),array('center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['F'.$i] = array($instance->re_db_output('$'.number_format($override_sub_data['investment_amount'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['G'.$i] = array($instance->re_db_output('$'.number_format($override_sub_data['commission_received'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['H'.$i] = array($instance->re_db_output('$'.number_format($override_sub_data['charge'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['I'.$i] = array($instance->re_db_output('$'.number_format($override_sub_data['net_commission'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['J'.$i] = array(number_format($override_sub_data['rate_per'],2).'%',array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['K'.$i] = array('$'.number_format($override_sub_data['rate_amount'],2),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                    }
                    $i++;
                    $broker_investment_amount = $broker_investment_amount+$category_investment_amount;
                    $broker_commission_received = $broker_commission_received+$category_commission_received;
                    $broker_net_commission = $broker_net_commission+$category_net_commission;
                    $broker_charges = $broker_charges+$category_charges;
                    $broker_rate = $broker_rate+$category_rate;
                    $broker_broker_commission = $broker_broker_commission+$category_broker_commission;
                    
                    $sheet_data[$sheet_offset]['A'.$i] = array('* OVERRIDE SUBTOTAL FOR '.strtoupper($override_comm_key).' * ',array('bold','center','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'E'.$i)));
                    $sheet_data[$sheet_offset]['F'.$i] = array($instance->re_db_output('$'.number_format($category_investment_amount,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['G'.$i] = array($instance->re_db_output('$'.number_format($category_commission_received,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['H'.$i] = array($instance->re_db_output('$'.number_format($category_charges,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['I'.$i] = array($instance->re_db_output('$'.number_format($category_net_commission,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['J'.$i] = array('',array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($category_broker_commission,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                }
                $i++;
                $total_override_transactions = $total_override_transactions+$broker_broker_commission;
                $sheet_data[$sheet_offset]['A'.$i] = array('*** OVERRIDE TRANSACTIONS TOTAL ***',array('bold','center','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'E'.$i)));
                $sheet_data[$sheet_offset]['F'.$i] = array($instance->re_db_output('$'.number_format($broker_investment_amount,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['G'.$i] = array($instance->re_db_output('$'.number_format($broker_commission_received,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['H'.$i] = array($instance->re_db_output('$'.number_format($broker_charges,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['I'.$i] = array($instance->re_db_output('$'.number_format($broker_net_commission,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['J'.$i] = array('',array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($broker_broker_commission,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $i++;
                $sheet_data[$sheet_offset]['A'.$i] = array('',array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'K'.$i)));
            }
            if(isset($brokers_comm_data['split_transactions']) && $brokers_comm_data['split_transactions'] != array())
            {
                $broker_investment_amount = 0;
                $broker_commission_received = 0;
                $broker_net_commission = 0;
                $broker_charges = 0;
                $broker_rate = 0;
                $broker_broker_commission = 0;
                $i++;
                $sheet_data[$sheet_offset]['A'.$i] = array('SPLIT TRANSACTIONS',array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'K'.$i)));
                foreach($brokers_comm_data['split_transactions'] as $split_comm_key=>$split_comm_data)
                {
                    $i++;
                    $sheet_data[$sheet_offset]['A'.$i] = array('SPLIT BROKER: '.strtoupper($split_comm_key),array('bold','left','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'K'.$i)));
                    $category_investment_amount = 0;
                    $category_commission_received = 0;
                    $category_net_commission = 0;
                    $category_charges = 0;
                    $category_rate = 0;
                    $category_broker_commission = 0;
                    foreach($split_comm_data as $split_sub_key=>$split_sub_data)
                    {
                        $trade_date='';
                        $category_investment_amount = $category_investment_amount+$split_sub_data['investment_amount'];
                        $category_commission_received = $category_commission_received+$split_sub_data['commission_received'];
                        $category_net_commission = $category_net_commission+$split_sub_data['net_commission'];
                        $category_charges = $category_charges+$split_sub_data['charge'];
                        $category_rate = $category_rate+0;
                        $category_broker_commission = $category_broker_commission+$split_sub_data['rate_amount'];
                        
                        if(isset($split_sub_data['buy_sell']) && $split_sub_data['buy_sell'] == 1)
                        {
                            $buy_sell = 'B';
                        }
                        else if(isset($split_sub_data['buy_sell']) && $split_sub_data['buy_sell'] == 2)
                        {
                            $buy_sell = 'S';
                        }
                        else
                        {
                            $buy_sell='';
                        }
                        
                        if($split_sub_data['trade_date'] != '0000-00-00' && $split_sub_data['trade_date'] != ''){ $trade_date = date('m/d/Y',strtotime($split_sub_data['trade_date'])); }
                        
                        $i++;
                        $sheet_data[$sheet_offset]['A'.$i] = array($instance->re_db_output($trade_date),array('center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['B'.$i] = array($instance->re_db_output($split_sub_data['client_firstname'].', '.$split_sub_data['client_lastname']),array('left','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('B'.$i,'C'.$i)));
                        $sheet_data[$sheet_offset]['D'.$i] = array($instance->re_db_output($split_sub_data['batch_description']),array('left','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['E'.$i] = array($instance->re_db_output($buy_sell),array('center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['F'.$i] = array($instance->re_db_output('$'.number_format($split_sub_data['investment_amount'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['G'.$i] = array($instance->re_db_output('$'.number_format($split_sub_data['commission_received'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['H'.$i] = array($instance->re_db_output('$'.number_format($split_sub_data['charge'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['I'.$i] = array($instance->re_db_output('$'.number_format($split_sub_data['net_commission'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['J'.$i] = array(number_format($split_sub_data['rate'],2).'%',array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                        $sheet_data[$sheet_offset]['K'.$i] = array('$'.number_format($split_sub_data['rate_amount'],2),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                    }
                    $i++;
                    $broker_investment_amount = $broker_investment_amount+$category_investment_amount;
                    $broker_commission_received = $broker_commission_received+$category_commission_received;
                    $broker_net_commission = $broker_net_commission+$category_net_commission;
                    $broker_charges = $broker_charges+$category_charges;
                    $broker_rate = $broker_rate+$category_rate;
                    $broker_broker_commission = $broker_broker_commission+$category_broker_commission;
                    
                    $sheet_data[$sheet_offset]['A'.$i] = array('* SPLIT SUBTOTAL FOR '.strtoupper($split_comm_key).' *',array('bold','center','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'E'.$i)));
                    $sheet_data[$sheet_offset]['F'.$i] = array($instance->re_db_output('$'.number_format($category_investment_amount,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['G'.$i] = array($instance->re_db_output('$'.number_format($category_commission_received,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['H'.$i] = array($instance->re_db_output('$'.number_format($category_charges,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['I'.$i] = array($instance->re_db_output('$'.number_format($category_net_commission,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['J'.$i] = array('',array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($category_broker_commission,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                }
                $i++;
                $total_split_transactions = $total_split_transactions+$broker_broker_commission;
                $sheet_data[$sheet_offset]['A'.$i] = array('*** SPLIT TRANSACTIONS TOTAL ***',array('bold','center','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'E'.$i)));
                $sheet_data[$sheet_offset]['F'.$i] = array($instance->re_db_output('$'.number_format($broker_investment_amount,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['G'.$i] = array($instance->re_db_output('$'.number_format($broker_commission_received,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['H'.$i] = array($instance->re_db_output('$'.number_format($broker_charges,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['I'.$i] = array($instance->re_db_output('$'.number_format($broker_net_commission,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['J'.$i] = array('',array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($broker_broker_commission,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $i++;
                $sheet_data[$sheet_offset]['A'.$i] = array('',array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'K'.$i)));
            }
            if(isset($brokers_comm_data['adjustments']) && $brokers_comm_data['adjustments'] != array())
            {
                $i++;
                $sheet_data[$sheet_offset]['A'.$i] = array('ADJUSTMENTS',array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'K'.$i)));
                $adjustment_total = 0;
                foreach($brokers_comm_data['adjustments'] as $adj_key=>$adj_data)
                {
                    $adjustment_formatted = round(floatval($adj_data['adjustment_amount']),2);
                    $adjustment_total = $adjustment_total + $adjustment_formatted;

                    $adjustment_date_formatted = '';
                    if($adj_data['date'] != '0000-00-00' && $adj_data['date'] != ''){ 
                        $adjustment_date_formatted = date('m/d/Y',strtotime($adj_data['date']));
                    }
                    
                    $i++;
                    $sheet_data[$sheet_offset]['A'.$i] = array($adjustment_date_formatted,array('normal','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['B'.$i] = array('',array('center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('B'.$i,'C'.$i)));
                    $sheet_data[$sheet_offset]['D'.$i] = array($adj_data['description'],array('normal','left','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['E'.$i] = array('',array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['F'.$i] = array('',array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['G'.$i] = array('',array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['H'.$i] = array('',array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['I'.$i] = array('',array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['J'.$i] = array('',array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                    $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($adjustment_formatted,2,'.',',')),array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                }
                $i++;
                $total_adjustments = $total_adjustments+$adjustment_total;
                $sheet_data[$sheet_offset]['A'.$i] = array('*** ADJUSTMENTS TOTAL ***',array('bold','center','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'E'.$i)));
                $sheet_data[$sheet_offset]['F'.$i] = array('',array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['G'.$i] = array('',array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['H'.$i] = array('',array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['I'.$i] = array('',array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['J'.$i] = array('',array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($adjustment_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
                $i++;
                $sheet_data[$sheet_offset]['A'.$i] = array('',array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'K'.$i)));
            }
            $total_check_amount = ($total_broker_transactions+$total_split_transactions+$total_override_transactions+$total_adjustments+$total_base_salary+$total_prior_balance+$total_finra_assessment+$total_sipc_assessment);
            $i++;
            $sheet_data[$sheet_offset]['I'.$i] = array('BROKER COMMISSION TOTALS',array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('I'.$i,'K'.$i)));
            $i++;
            $sheet_data[$sheet_offset]['I'.$i] = array('Broker Transactions',array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('I'.$i,'J'.$i)));
            $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($total_broker_transactions,2)),array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
            $i++;
            $sheet_data[$sheet_offset]['I'.$i] = array('Split Transactions',array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('I'.$i,'J'.$i)));
            $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($total_split_transactions,2)),array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
            $i++;
            $sheet_data[$sheet_offset]['I'.$i] = array('Override Transactions',array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('I'.$i,'J'.$i)));
            $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($total_override_transactions,2)),array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
            $i++;
            $sheet_data[$sheet_offset]['I'.$i] = array('Adjustments',array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('I'.$i,'J'.$i)));
            $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($total_adjustments,2)),array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
            $i++;
            $sheet_data[$sheet_offset]['I'.$i] = array('Payroll Draw ',array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('I'.$i,'J'.$i)));
            $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($total_payroll_draw,2)),array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
            $i++;
            $sheet_data[$sheet_offset]['I'.$i] = array('Base Salary',array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('I'.$i,'J'.$i)));
            $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($total_base_salary,2)),array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
            $i++;
            $sheet_data[$sheet_offset]['I'.$i] = array('Prior Period Balance ',array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('I'.$i,'J'.$i)));
            $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($total_prior_balance,2)),array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
            $i++;
            $sheet_data[$sheet_offset]['I'.$i] = array('FINRA Assessment',array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('I'.$i,'J'.$i)));
            $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($total_finra_assessment,2)),array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
            $i++;
            $sheet_data[$sheet_offset]['I'.$i] = array('SIPC Assessment',array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('I'.$i,'J'.$i)));
            $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($total_sipc_assessment,2)),array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
            $i++;
            $total_broker_earnings = $total_broker_earnings+$total_check_amount;
            if($check_minimum_check_amount>$total_check_amount){
            //$sheet_data[$sheet_offset]['G'.$i] = array('Please Retain for Your Records ',array('normal','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('G'.$i,'H'.$i)));
            $sheet_data[$sheet_offset]['G'.$i] = array('THERE WILL BE NO CHECK THIS PERIOD ',array('bold','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('G'.$i,'H'.$i)));
            $sheet_data[$sheet_offset]['I'.$i] = array('Balance Forward ',array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('I'.$i,'J'.$i)));
            $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($total_check_amount,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
            }
            else
            {
            $sheet_data[$sheet_offset]['G'.$i] = array('Please Retain for Your Records ',array('bold','right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('G'.$i,'H'.$i)));
            $sheet_data[$sheet_offset]['I'.$i] = array('Check Amount ',array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('I'.$i,'J'.$i)));
            $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($total_check_amount,2)),array('bold','italic','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
            }
            $i++;
            $sheet_data[$sheet_offset]['I'.$i] = array('Year-to-date Earnings',array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('I'.$i,'J'.$i)));
            $sheet_data[$sheet_offset]['K'.$i] = array($instance->re_db_output('$'.number_format($total_broker_earnings,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
            $i++;
            $sheet_data[$sheet_offset]['A'.$i] = array('',array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'K'.$i)));
            //$sheet_offset++;
        }
        
        //$i++;
    }
    else
    {
        $sheet_data = array( // Set sheet data.
            $sheet_offset=> // Excel sub sheet indexed.
            array(
                
                'A1'=>array('LOGO',array('bold','center','color'=>array('000000'),'size'=>array(12),'font_name'=>array('Calibri'),'merge'=>array('A1','B1'))),
                'C1'=>array($company,array('bold','center','color'=>array('000000'),'size'=>array(12),'font_name'=>array('Calibri'),'merge'=>array('C1','H1'))),
                'I1'=>array($system_company_name,array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('I1','K1'))),
                
                'A2'=>array('COMMISSION STATEMENT',array('bold','center','color'=>array('000000'),'size'=>array(14),'font_name'=>array('Calibri'),'merge'=>array('A2','K3'))),
                'A4'=>array($payroll_date,array('bold','center','color'=>array('000000'),'size'=>array(12),'font_name'=>array('Calibri'),'merge'=>array('A4','K4'))),
                'A5'=>array($broker_name,array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A5','B5'))),
                'J5'=>array('BROKER# : '.$broker_number,array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('J5','K5'))),
                'A6'=>array($broker_address,array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A6','B6'))),
                'J6'=>array('BRANCH# : '.strtoupper($broker_branch),array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('J6','K6'))),
                'A7'=>array($broker_city,array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A7','B7'))),
                'A8'=>array($broker_state.''.$broker_zipcode,array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A8','B8'))),
                'A9'=>array('TRADE DATE#',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri'))),
                'B9'=>array('CLIENT',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri'),'merge'=>array('B9','C9'))),
                'D9'=>array('INVESTMENT',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri'))),
                'E9'=>array('B/S',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri'))),
                'F9'=>array('INVESTMENT AMOUNT',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri'))),
                'G9'=>array('GROSS COMMISSION',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri'))),
                'H9'=>array('CLEARING CHARGE',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri'))),
                'I9'=>array('NET COMMISSION',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri'))),
                'J9'=>array('RATE',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri'))),
                'K9'=>array('BROKER COMMISSION',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri'))),
            )
        );
        $i = 9;
        $i++;
        $sheet_data[$sheet_offset]['A'.$i] = array($instance->re_db_output('No record found.'),array('center','size'=>array(10),'color'=>array('000000'),'merge'=>array('A'.$i,'K'.$i)));
    }
    //echo '<pre>';print_r($sheet_data);exit;
    // Create Excel instance.
    $Excel = new Excel();
    
    
    $formPost = $Excel->generate(
        array(
            'creator'=>$creator,
            'last_modified_by'=>$last_modified_by,
            'title'=>$title,
            'subject'=>$subject,
            'description'=>$description,
            'keywords'=>$keywords,
            'category'=>$category,
            'total_sub_sheets'=>$total_sub_sheets,
            'sub_sheet_title'=>$sub_sheet_title_array,
            'default_open_sub_sheet'=>$default_open_sub_sheet,
            'sheet_data'=>$sheet_data,
            'excel_name'=>$excel_name
        )
    );
?>