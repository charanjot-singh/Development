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
    $get_company_data = array();
    $filter_array = array();
    $company = '';
    $total_records=0;
    
    if(isset($_GET['filter']) && $_GET['filter'] != '')
    {
        $filter_array = json_decode($_GET['filter'],true);
        $company = isset($filter_array['company'])?$filter_array['company']:0;
        $sort_by = isset($filter_array['sort_by'])?$filter_array['sort_by']:'';
        // 11/22/21 Query by the payroll_id instead of payroll_date
        $payroll_id = isset($filter_array['payroll_id'])?$filter_array['payroll_id']:'';
        $get_payroll_upload = $instance_payroll->get_payroll_uploads($payroll_id);
        $payroll_date = date('m/d/Y', strtotime($get_payroll_upload['payroll_date']));
        
        $get_company_data = $instance_payroll->get_company_statement_report_data($company,$sort_by,$payroll_id);
        
    }
    if($company > 0)
    {
        $company_name = '';
        foreach($get_company_data as $key=>$val)
        {
            $company_name = $key;
        }
    } 
    else
    {
        $company_name = 'All Companies';
    }   
    
    $creator                = "Foxtrot User";
    $last_modified_by       = "Foxtrot User";
    $title                  = "Foxtrot Company Statement Report Excel";
    $subject                = "Foxtrot Company Statement Report";
    $description            = "Foxtrot Company Statement Report. Generated on : ".date('Y-m-d h:i:s');
    $keywords               = "Foxtrot Company Statement report office 2007";
    $category               = "Foxtrot Company Statement Report.";
    $total_sub_sheets       = 1;
    $sub_sheet_title_array  = array("Foxtrot Company Statement");
    $default_open_sub_sheet = 0; // 0 means first indexed sub sheets.
    $excel_name             = 'Foxtrot Company Statement Report';
    $sheet_data             = array();
    $i=5;
        
            
    // Set output excel array.
    /* Headers. */
    $sheet_data = array( // Set sheet data.
        0=> // Excel sub sheet indexed.
        array(
            
            'A1'=>array('LOGO',array('bold','center','color'=>array('000000'),'size'=>array(16),'font_name'=>array('Calibri'),'merge'=>array('A1','B2'))),
            'C1'=>array('COMPANY COMMISSION STATEMENT',array('bold','center','color'=>array('000000'),'size'=>array(14),'font_name'=>array('Calibri'),'merge'=>array('C1','I2'))),
            'J1'=>array($system_company_name,array('bold','center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('J1','K2'))),
            
            'A3'=>array($company_name,array('bold','center','color'=>array('000000'),'size'=>array(12),'font_name'=>array('Calibri'),'merge'=>array('A3','K3'))),
            'A4'=>array($payroll_date,array('bold','center','color'=>array('000000'),'size'=>array(11),'font_name'=>array('Calibri'),'merge'=>array('A4','K4'))),
            'A5'=>array('REP#',array('bold','center','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'))),
            'B5'=>array('NAME',array('bold','center','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'))),
            'C5'=>array('GROSS COMMISSIONS',array('bold','center','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'))),
            'D5'=>array('NET COMMISSIONS',array('bold','center','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'))),
            'E5'=>array('CHARGE',array('bold','center','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'))),
            'F5'=>array('OVERRIDE COMMISSIONS',array('bold','center','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'))),
            'G5'=>array('PRIOR BALANCE',array('bold','center','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'))),
            'H5'=>array('ADVANCES/ ADJUSTMENTS',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri'))),
            'I5'=>array('FINRA/SIPC',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri'))),
            'J5'=>array('CHECK AMOUNT',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri'))),
            'K5'=>array('B/D RETENTION',array('bold','center','color'=>array('000000'),'size'=>array(10),'background'=>array('f1f1f1'),'font_name'=>array('Calibri'))),
            
        )
    );
            
    if(is_array($get_company_data) && count($get_company_data)>0)
    {
        $c = $i;
        $report_gross_comm_total = 0;
        $report_net_comm_total = 0;
        $report_charge_total = 0;
        $report_override_comm_total = 0;
        $report_balances_total = 0;
        $report_adjustments_total = 0;
        $report_finra_sipc_total = 0;
        $report_check_amount_total = 0;
        $report_retention_total = 0;
        foreach($get_company_data as $com_sub_key=>$com_data)
        {
            $i++;
            $total_records = $total_records+1;
            $sheet_data[0]['A'.$i] = array($instance->re_db_output($com_sub_key),array('bold','left','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'K'.$i)));
            
            $company_gross_comm_total = 0;
            $company_net_comm_total = 0;
            $company_charge_total = 0;
            $company_override_comm_total = 0;
            $company_balances_total = 0;
            $company_adjustments_total = 0;
            $company_finra_sipc_total = 0;
            $company_check_amount_total = 0;
            $company_retention_total = 0;
            foreach($com_data as $com_sub_key=>$com_sub_data)
            {
                $i++;
                $retention = $com_sub_data['commission_received']-$com_sub_data['check_amount'];
                $finra_sipc = -$com_sub_data['finra']-$com_sub_data['sipc'];
                
                $company_gross_comm_total = $company_gross_comm_total+$com_sub_data['commission_received'];
                $company_net_comm_total = $company_net_comm_total+$com_sub_data['commission_paid'];
                $company_charge_total = $company_charge_total+$com_sub_data['charge'];
                $company_override_comm_total = $company_override_comm_total+$com_sub_data['override_paid'];
                $company_balances_total = $company_balances_total+$com_sub_data['balance'];
                $company_adjustments_total = $company_adjustments_total+$com_sub_data['adjustments'];
                $company_finra_sipc_total = $company_finra_sipc_total+$finra_sipc;
                $company_check_amount_total = $company_check_amount_total+$com_sub_data['check_amount'];
                $company_retention_total = $company_retention_total+$retention;
                
                $sheet_data[0]['A'.$i] = array($instance->re_db_output($com_sub_data['fund']),array('center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[0]['B'.$i] = array($instance->re_db_output($com_sub_data['broker_firstname'].' '.$com_sub_data['broker_lastname']),array('center','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[0]['C'.$i] = array($instance->re_db_output('$'.number_format($com_sub_data['commission_received'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[0]['D'.$i] = array($instance->re_db_output('$'.number_format($com_sub_data['commission_paid'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[0]['E'.$i] = array($instance->re_db_output('$'.number_format($com_sub_data['charge'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[0]['F'.$i] = array($instance->re_db_output('$'.number_format($com_sub_data['override_paid'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[0]['G'.$i] = array($instance->re_db_output('$'.number_format($com_sub_data['balance'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[0]['H'.$i] = array($instance->re_db_output('$'.number_format($com_sub_data['adjustments'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[0]['I'.$i] = array($instance->re_db_output('$'.number_format($finra_sipc,2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[0]['J'.$i] = array($instance->re_db_output('$'.number_format($com_sub_data['check_amount'],2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                $sheet_data[0]['K'.$i] = array($instance->re_db_output('$'.number_format($retention,2)),array('right','color'=>array('000000'),'size'=>array(10),'font_name'=>array('Calibri')));
                
            }$i++;
            $report_gross_comm_total = $report_gross_comm_total+$company_gross_comm_total;
            $report_net_comm_total = $report_net_comm_total+$company_net_comm_total;
            $report_charge_total = $report_charge_total+$company_charge_total;
            $report_override_comm_total = $report_override_comm_total+$company_override_comm_total;
            $report_balances_total = $report_balances_total+$company_balances_total;
            $report_adjustments_total = $report_adjustments_total+$company_adjustments_total;
            $report_finra_sipc_total = $report_finra_sipc_total+$company_finra_sipc_total;
            $report_check_amount_total = $report_check_amount_total+$company_check_amount_total;
            $report_retention_total = $report_retention_total+$company_retention_total;
            
            $sheet_data[0]['A'.$i] = array(' * Company Total * ',array('bold','center','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'B'.$i)));
            $sheet_data[0]['C'.$i] = array($instance->re_db_output('$'.number_format($company_gross_comm_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
            $sheet_data[0]['D'.$i] = array($instance->re_db_output('$'.number_format($company_net_comm_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
            $sheet_data[0]['E'.$i] = array($instance->re_db_output('$'.number_format($company_charge_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
            $sheet_data[0]['F'.$i] = array($instance->re_db_output('$'.number_format($company_override_comm_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
            $sheet_data[0]['G'.$i] = array($instance->re_db_output('$'.number_format($company_balances_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
            $sheet_data[0]['H'.$i] = array($instance->re_db_output('$'.number_format($company_adjustments_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
            $sheet_data[0]['I'.$i] = array($instance->re_db_output('$'.number_format($company_finra_sipc_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
            $sheet_data[0]['J'.$i] = array($instance->re_db_output('$'.number_format($company_check_amount_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
            $sheet_data[0]['K'.$i] = array($instance->re_db_output('$'.number_format($company_retention_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
        }
        $i++;
        $sheet_data[0]['A'.$i] = array('*** Report Total ***',array('bold','center','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri'),'merge'=>array('A'.$i,'B'.$i)));
        $sheet_data[0]['C'.$i] = array($instance->re_db_output('$'.number_format($report_gross_comm_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
        $sheet_data[0]['D'.$i] = array($instance->re_db_output('$'.number_format($report_net_comm_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
        $sheet_data[0]['E'.$i] = array($instance->re_db_output('$'.number_format($report_charge_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
        $sheet_data[0]['F'.$i] = array($instance->re_db_output('$'.number_format($report_override_comm_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
        $sheet_data[0]['G'.$i] = array($instance->re_db_output('$'.number_format($report_balances_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
        $sheet_data[0]['H'.$i] = array($instance->re_db_output('$'.number_format($report_adjustments_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
        $sheet_data[0]['I'.$i] = array($instance->re_db_output('$'.number_format($report_finra_sipc_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
        $sheet_data[0]['J'.$i] = array($instance->re_db_output('$'.number_format($report_check_amount_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
        $sheet_data[0]['K'.$i] = array($instance->re_db_output('$'.number_format($report_retention_total,2)),array('bold','right','color'=>array('000000'),'background'=>array('f1f1f1'),'size'=>array(10),'font_name'=>array('Calibri')));
        
    }
    else
    {
        $i++;
        $sheet_data[0]['A'.$i] = array($instance->re_db_output('No record found.'),array('center','size'=>array(10),'color'=>array('000000'),'merge'=>array('A'.$i,'K'.$i)));
    }
    
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