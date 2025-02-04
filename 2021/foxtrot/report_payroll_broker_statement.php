<?php 
require_once("include/config.php");
require_once(DIR_FS."islogin.php");
$instance = new transaction();
$get_trans_data = array();
$get_logo = $instance->get_system_logo();
$system_logo = isset($get_logo['logo'])?$instance->re_db_input($get_logo['logo']):'';
$get_company_name = $instance->get_company_name();
$system_company_name = isset($get_company_name['company_name'])?$instance->re_db_input($get_company_name['company_name']):'';
//print_r($system_logo);exit;

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
?>
<?php

    // create new PDF document
    $pdf = new RRPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    // add a page
    //$pdf->AddPage('L');
    // Title
    $img = '<img src="'.SITE_URL."upload/logo/".$system_logo.'" height="25px" />';
    
    
    
    //$pdf->Line(10, 81, 290, 81);
    if(isset($get_broker_commission_data['broker_transactions']) && $get_broker_commission_data['broker_transactions'] != array())
    {
        foreach($get_broker_commission_data['broker_transactions'] as $brokers_comm_key=>$brokers_comm_data)
        {
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
                       
            $pdf->AddPage('L');
            
            $pdf->SetFont('times','B',12);
            $pdf->SetFont('times','',10);
            $html='<table border="0" width="100%">
                        <tr>';
                        if(isset($system_logo) && $system_logo != '')
                        {
                            $html .='<td width="20%" align="left">'.$img.'</td>';
                        }
                            $html .='<td width="60%" style="font-size:12px;font-weight:bold;text-align:center;">'.$company.'</td>';
                        if(isset($system_company_name) && $system_company_name != '')
                        {
                            $html.='<td width="20%" style="font-size:10px;font-weight:bold;text-align:right;">'.$system_company_name.'</td>';
                        }
                        $html.='</tr>
                        <tr>';
                            $html .='<td width="100%" style="font-size:14px;font-weight:bold;text-align:center;">COMMISSION STATEMENT</td>';
                        $html .='</tr>
                        <tr>';
                            $html .='<td width="100%" style="font-size:12px;font-weight:bold;text-align:center;">'.$payroll_date.'</td>';
                        $html .='</tr>
                </table>';
            $pdf->writeHTML($html, false, 0, false, 0);
            $pdf->Ln(2);
    
            $pdf->SetFont('times','B',12);
            $pdf->SetFont('times','',10);
            $html='<table border="0" width="100%">
                        <tr>
                            <td width="70%" align="left" style="font-size:8px;">'.$broker_name.'</td>
                            <td width="30%" align="left" style="font-size:8px;">BROKER#/FUND/INTERNAL : '.$broker_number.' / '.$brokers_comm_data['fund'].' / '.$brokers_comm_data['internal'].'</td>
                        </tr>
                        <tr>
                            <td width="70%" align="left" style="font-size:8px;">'.$broker_address.'</td>
                            <td width="30%" align="left" style="font-size:8px;">BRANCH# : '.strtoupper($broker_branch).'</td>
                        </tr>
                        <tr>
                            <td width="20%" align="left" style="font-size:8px;">'.$broker_city.'</td>
                        </tr>
                        <tr>
                            <td width="20%" align="left" style="font-size:8px;">'.$broker_state.''.$broker_zipcode.'</td>
                        </tr>
                   </table>';
            $pdf->writeHTML($html, false, 0, false, 0);
            $pdf->Ln(2);
            
            /*$pdf->SetFont('times','B',12);
            $pdf->SetFont('times','',10);
            $html='<table border="0" width="100%">
                        <tr>
                            <td style="font-size:12px;font-weight:bold;text-align:center;">COMMISSION STATEMENT for '.strtoupper($broker_name).'</td>
                        </tr>
                   </table>';
            $pdf->writeHTML($html, false, 0, false, 0);
            $pdf->Ln(2);*/
            
            $pdf->SetFont('times','B',12);
            $pdf->SetFont('times','',10);
            $html='<table border="0" width="100%">';
                        $html.='<tr style="background-color: #f1f1f1;">
                            <td width="10%" style="text-align:center;"><h5>TRADE DATE</h5></td>
                            <td width="15%" style="text-align:center;"><h5>CLIENT</h5></td>
                            <td width="15%" style="text-align:center;"><h5>INVESTMENT</h5></td>
                            <td width="5%" style="text-align:center;"><h5>B/S</h5></td>
                            <td width="9%" style="text-align:center;"><h5>INVESTMENT AMOUNT</h5></td>
                            <td width="9%" style="text-align:center;"><h5>GROSS COMMISSION</h5></td>
                            <td width="9%" style="text-align:center;"><h5>CLEARING CHARGE</h5></td>
                            <td width="9%" style="text-align:center;"><h5>NET COMMISSION</h5></td>
                            <td width="9%" style="text-align:center;"><h5>RATE</h5></td>
                            <td width="10%" style="text-align:center;"><h5>BROKER COMMISSION</h5></td>
                        </tr>
                        <br/>';
            if(isset($brokers_comm_data['direct_transactions']) && $brokers_comm_data['direct_transactions'] != array())
            {
                $broker_investment_amount = 0;
                $broker_commission_received = 0;
                $broker_net_commission = 0;
                $broker_charges = 0;
                $broker_rate = 0;
                $broker_broker_commission = 0;
                $html.='<tr>
                            <td colspan="10" style="font-size:8px;font-weight:bold;text-align:center;">BROKER TRANSACTIONS</td>
                        </tr>
                        <br/>';
                foreach($brokers_comm_data['direct_transactions'] as $comm_key=>$comm_data)
                {
                    $html.='<tr>
                                <td colspan="10" style="font-size:8px;font-weight:bold;text-align:left;">PRODUCT CATEGORY: '.strtoupper($comm_key).'</td>
                            </tr>
                            <br/>'; 
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
                        $html.='<tr>
                                   <td style="font-size:8px;font-weight:normal;text-align:center;">'.$trade_date.'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:center;">'.$comm_sub_data['client_firstname'].', '.$comm_sub_data['client_lastname'].'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:center;">'.$comm_sub_data['batch_description'].'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:center;">'.$buy_sell.'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($comm_sub_data['investment_amount'],2).'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($comm_sub_data['commission_received'],2).'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($comm_sub_data['charge'],2).'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($comm_sub_data['net_commission'],2).'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($comm_sub_data['rate'],2).'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($comm_sub_data['commission_paid'],2).'</td>
                                </tr>';
                    }
                    $broker_investment_amount = $broker_investment_amount+$category_investment_amount;
                    $broker_commission_received = $broker_commission_received+$category_commission_received;
                    $broker_net_commission = $broker_net_commission+$category_net_commission;
                    $broker_charges = $broker_charges+$category_charges;
                    $broker_rate = $broker_rate+$category_rate;
                    $broker_broker_commission = $broker_broker_commission+$category_broker_commission;
                    
                    $html.='<tr style="background-color: #f1f1f1;">
                               <td style="font-size:8px;font-weight:bold;text-align:right;" colspan="3">* '.strtoupper($comm_key).' SUBTOTAL * </td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;"></td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_investment_amount,2).'</td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_commission_received,2).'</td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_charges,2).'</td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_net_commission,2).'</td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;"></td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_broker_commission,2).'</td>
                            </tr>
                            <br/>';
                }
                $total_broker_transactions = $total_broker_transactions+$broker_broker_commission;
                $html.='<tr style="background-color: #f1f1f1;">
                           <td style="font-size:8px;font-weight:bold;text-align:right;" colspan="3">*** BROKER TRANSACTIONS TOTAL *** </td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;"></td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($broker_investment_amount,2).'</td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($broker_commission_received,2).'</td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($broker_charges,2).'</td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($broker_net_commission,2).'</td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;"></td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($broker_broker_commission,2).'</td>
                        </tr>
                        <br/>';
            }
            if(isset($brokers_comm_data['override_transactions']) && $brokers_comm_data['override_transactions'] != array())
            {
                $broker_investment_amount = 0;
                $broker_commission_received = 0;
                $broker_net_commission = 0;
                $broker_charges = 0;
                $broker_rate = 0;
                $broker_broker_commission = 0;
                $html.='<tr>
                            <td colspan="10" style="font-size:8px;font-weight:bold;text-align:center;">OVERRIDE TRANSACTIONS</td>
                        </tr>
                        <br/>';
                foreach($brokers_comm_data['override_transactions'] as $override_comm_key=>$override_comm_data)
                {
                    $html.='<tr>
                                <td colspan="10" style="font-size:8px;font-weight:bold;text-align:left;">OVERRIDE BROKER: '.strtoupper($override_comm_key).'</td>
                            </tr>
                            <br/>'; 
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
                        $html.='<tr>
                                   <td style="font-size:8px;font-weight:normal;text-align:center;">'.$trade_date.'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:center;">'.$override_sub_data['client_firstname'].', '.$override_sub_data['client_lastname'].'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:center;">'.$override_sub_data['batch_description'].'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:center;">'.$buy_sell.'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($override_sub_data['investment_amount'],2).'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($override_sub_data['commission_received'],2).'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($override_sub_data['charge'],2).'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($override_sub_data['net_commission'],2).'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($override_sub_data['rate_per'],2).'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($override_sub_data['rate_amount'],2).'</td>
                                </tr>';
                    }
                    $broker_investment_amount = $broker_investment_amount+$category_investment_amount;
                    $broker_commission_received = $broker_commission_received+$category_commission_received;
                    $broker_net_commission = $broker_net_commission+$category_net_commission;
                    $broker_charges = $broker_charges+$category_charges;
                    $broker_rate = $broker_rate+$category_rate;
                    $broker_broker_commission = $broker_broker_commission+$category_broker_commission;
                    $html.='<tr style="background-color: #f1f1f1;">
                               <td style="font-size:8px;font-weight:bold;text-align:right;" colspan="3">* OVERRIDE SUBTOTAL FOR '.strtoupper($override_comm_key).' * </td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;"></td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_investment_amount,2).'</td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_commission_received,2).'</td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_charges,2).'</td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_net_commission,2).'</td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;"></td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_broker_commission,2).'</td>
                            </tr>
                            <br/>';
                }
                $total_override_transactions = $total_override_transactions+$broker_broker_commission;
                $html.='<tr style="background-color: #f1f1f1;">
                           <td style="font-size:8px;font-weight:bold;text-align:right;" colspan="3">*** OVERRIDE TRANSACTIONS TOTAL *** </td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;"></td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($broker_investment_amount,2).'</td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($broker_commission_received,2).'</td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($broker_charges,2).'</td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($broker_net_commission,2).'</td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;"></td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($broker_broker_commission,2).'</td>
                        </tr>
                        <br/>';
            }
            if(isset($brokers_comm_data['split_transactions']) && $brokers_comm_data['split_transactions'] != array())
            {
                $broker_investment_amount = 0;
                $broker_commission_received = 0;
                $broker_net_commission = 0;
                $broker_charges = 0;
                $broker_rate = 0;
                $broker_broker_commission = 0;
                $html.='<tr>
                            <td colspan="10" style="font-size:8px;font-weight:bold;text-align:center;">SPLIT TRANSACTIONS</td>
                        </tr>
                        <br/>';
                foreach($brokers_comm_data['split_transactions'] as $split_comm_key=>$split_comm_data)
                {
                    $html.='<tr>
                                <td colspan="10" style="font-size:8px;font-weight:bold;text-align:left;">SPLIT BROKER: '.strtoupper($split_comm_key).'</td>
                            </tr>
                            <br/>'; 
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
                        $html.='<tr>
                                   <td style="font-size:8px;font-weight:normal;text-align:center;">'.$trade_date.'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:center;">'.$split_sub_data['client_firstname'].', '.$split_sub_data['client_lastname'].'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:center;">'.$split_sub_data['batch_description'].'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:center;">'.$buy_sell.'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($split_sub_data['investment_amount'],2).'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($split_sub_data['commission_received'],2).'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($split_sub_data['charge'],2).'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($split_sub_data['net_commission'],2).'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($split_sub_data['rate'],2).'</td>
                                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($split_sub_data['rate_amount'],2).'</td>
                                </tr>';
                    }
                    $broker_investment_amount = $broker_investment_amount+$category_investment_amount;
                    $broker_commission_received = $broker_commission_received+$category_commission_received;
                    $broker_net_commission = $broker_net_commission+$category_net_commission;
                    $broker_charges = $broker_charges+$category_charges;
                    $broker_rate = $broker_rate+$category_rate;
                    $broker_broker_commission = $broker_broker_commission+$category_broker_commission;
                    $html.='<tr style="background-color: #f1f1f1;">
                               <td style="font-size:8px;font-weight:bold;text-align:right;" colspan="3">* SPLIT SUBTOTAL FOR '.strtoupper($split_comm_key).' * </td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;"></td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_investment_amount,2).'</td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_commission_received,2).'</td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_charges,2).'</td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_net_commission,2).'</td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;"></td>
                               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_broker_commission,2).'</td>
                            </tr>
                            <br/>';
                }
                $total_split_transactions = $total_split_transactions+$broker_broker_commission;
                $html.='<tr style="background-color: #f1f1f1;">
                           <td style="font-size:8px;font-weight:bold;text-align:right;" colspan="3">*** SPLIT TRANSACTIONS TOTAL *** </td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;"></td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($broker_investment_amount,2).'</td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($broker_commission_received,2).'</td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($broker_charges,2).'</td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($broker_net_commission,2).'</td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;"></td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($broker_broker_commission,2).'</td>
                        </tr>
                        <br/>';
            }
            if(isset($brokers_comm_data['adjustments']) && $brokers_comm_data['adjustments'] != array())
            {
                $html.='<tr>
                               <td colspan="10" style="font-size:8px;font-weight:bold;text-align:center;">ADJUSTMENTS</td>
                        </tr>
                        <br/>'; 
                $adjustment_total = 0;
                foreach($brokers_comm_data['adjustments'] as $adj_key=>$adj_data)
                {
                    $adjustment_numeric = round(floatval($adj_data['adjustment_amount']),2);
                    $adjustment_total = $adjustment_total + $adjustment_numeric;
                    $html.='<tr>
                                   <td width="10%" style="font-size:8px;font-weight:normal;text-align:center;">02/28/2016</td>
                                   <td width="15%" style="font-size:8px;font-weight:normal;text-align:center;"></td>
                                   <td width="15%" style="font-size:8px;font-weight:normal;text-align:center;">'.$adj_data['description'].'</td>
                                   <td width="5%" style="font-size:8px;font-weight:normal;text-align:center;"></td>
                                   <td width="9%" style="font-size:8px;font-weight:normal;text-align:right;">0.00</td>
                                   <td width="9%" style="font-size:8px;font-weight:normal;text-align:right;">0.00</td>
                                   <td width="9%" style="font-size:8px;font-weight:normal;text-align:right;">0.00</td>
                                   <td width="9%" style="font-size:8px;font-weight:normal;text-align:right;">0.00</td>
                                   <td width="9%" style="font-size:8px;font-weight:bold;text-align:right;"></td>
                                   <td width="10%" style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($adjustment_numeric,2).'</td>
                                </tr>';
                }
                $total_adjustments = $total_adjustments+$adjustment_total;
                $html.='<tr style="background-color: #f1f1f1;">
                           <td style="font-size:8px;font-weight:bold;text-align:right;" colspan="3">*** ADJUSTMENTS TOTAL *** </td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;"></td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$0.00</td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$0.00</td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$0.00</td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$0.00</td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;"></td>
                           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($adjustment_total,2).'</td>
                        </tr>
                        <br/>';
            }
            $html.='</table>';
            $pdf->writeHTML($html, false, 0, false, 0);
            $pdf->Ln(2);
            
            $total_check_amount = ($total_broker_transactions+$total_split_transactions+$total_override_transactions+$total_adjustments+$total_base_salary+$total_prior_balance+$total_finra_assessment+$total_sipc_assessment);
            $pdf->SetFont('times','B',12);
            $pdf->SetFont('times','',10);
            $html='<table border="0" width="100%">';
                        $html.='<tr>
                               <td colspan="10" style="font-size:8px;font-weight:bold;text-align:right;">BROKER COMMISSION TOTALS</td>
                        </tr>';
                        $html.='<tr>
                            <td width="90%" style="font-size:8px;font-weight:normal;text-align:right;">Broker Transactions </td>
                            <td width="10%" style="font-size:8px;font-weight:normal;text-align:right;"> $'.number_format($total_broker_transactions,2).'</td>
                        </tr>
                        <tr>
                            <td width="90%" style="font-size:8px;font-weight:normal;text-align:right;">Split Transactions </td>
                            <td width="10%" style="font-size:8px;font-weight:normal;text-align:right;"> $'.number_format($total_split_transactions,2).'</td>
                        </tr>
                        <tr>
                            <td width="90%" style="font-size:8px;font-weight:normal;text-align:right;">Override Transactions </td>
                            <td width="10%" style="font-size:8px;font-weight:normal;text-align:right;"> $'.number_format($total_override_transactions,2).'</td>
                        </tr>
                        <tr>
                            <td width="90%" style="font-size:8px;font-weight:normal;text-align:right;">Adjustments </td>
                            <td width="10%" style="font-size:8px;font-weight:normal;text-align:right;"> $'.number_format($total_adjustments,2).'</td>
                        </tr>
                        <tr>
                            <td width="90%" style="font-size:8px;font-weight:normal;text-align:right;">Payroll Draw </td>
                            <td width="10%" style="font-size:8px;font-weight:normal;text-align:right;"> $'.number_format($total_payroll_draw,2).'</td>
                        </tr>
                        <tr>
                            <td width="90%" style="font-size:8px;font-weight:normal;text-align:right;">Base Salary </td>
                            <td width="10%" style="font-size:8px;font-weight:normal;text-align:right;"> $'.number_format($total_base_salary,2).'</td>
                        </tr>
                        <tr>
                            <td width="90%" style="font-size:8px;font-weight:normal;text-align:right;">Prior Period Balance </td>
                            <td width="10%" style="font-size:8px;font-weight:normal;text-align:right;"> $'.number_format($total_prior_balance,2).'</td>
                        </tr>
                        <tr>
                            <td width="90%" style="font-size:8px;font-weight:normal;text-align:right;">FINRA Assessment </td>
                            <td width="10%" style="font-size:8px;font-weight:normal;text-align:right;"> $'.number_format($total_finra_assessment,2).'</td>
                        </tr>
                        <tr>
                            <td width="90%" style="font-size:8px;font-weight:normal;text-align:right;">SIPC Assessment </td>
                            <td width="10%" style="font-size:8px;font-weight:normal;text-align:right;"> $'.number_format($total_sipc_assessment,2).'</td>
                        </tr>
                   </table>';
            $pdf->writeHTML($html, false, 0, false, 0);
            $pdf->Ln(2);
            
            $pdf->SetFont('times','B',12);
            $pdf->SetFont('times','',10);
            $html='<table width="100%">
                    <tr align="left" style="font-size:10px;font-weight:normal;text-align:left;">
                        <td width="70%">
                            <table width="100%">
                                <tr>
                                    <td style="font-size:8px;font-weight:normal;text-align:right;">Please Retain for Your Records </td>
                                </tr>';
                                
                                // 11/11/21 Take the minimum check amount from the data - see in the totals for the individual broker data ($brokers_comm_data['minimum_check_amount'])
                                //$check_minimum_check_amount=$instance_payroll->check_minimum_check_amount();
                                if ($check_minimum_check_amount>$total_check_amount){
                                    $html.='<tr>
                                        <td style="font-size:8px;font-weight:normal;text-align:right;">THERE WILL BE NO CHECK THIS PERIOD</td>
                                    </tr>';
                                }
                    $html.='</table>
                        </td>
                        <td width="5%"></td>
                        <td width="25%" border="1">
                            <table width="100%">';
                                $total_broker_earnings = $total_broker_earnings+$total_check_amount;
                                if ($check_minimum_check_amount>$total_check_amount){
                                $html.='<tr>
                                    <td width="70%" style="font-size:8px;font-weight:normal;text-align:right;">Balance Forward </td>
                                    <td width="30%" style="font-size:8px;font-weight:normal;text-align:right;"> $'.number_format($total_check_amount,2).'&nbsp;&nbsp;</td>
                                </tr>';
                                }
                                else
                                {
                                    $html.='<tr>
                                    <td width="70%" style="font-size:8px;font-weight:normal;text-align:right;">Check Amount </td>
                                    <td width="30%" style="font-size:8px;font-weight:normal;text-align:right;"> $'.number_format($total_check_amount,2).'&nbsp;&nbsp;</td>
                                </tr>';
                                }
                                $html.='<tr>
                                    <td width="70%" style="font-size:8px;font-weight:normal;text-align:right;">Year-to-date Earnings</td>
                                    <td width="30%" style="font-size:8px;font-weight:normal;text-align:right;"> $'.number_format($total_broker_earnings,2).'&nbsp;&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>';
                $html.='</table>';
            $pdf->writeHTML($html, false, 0, false, 0);
            $pdf->Ln(2);
        }
            
    }
    else
    {
            $pdf->AddPage('L');
            
            $pdf->SetFont('times','B',12);
            $pdf->SetFont('times','',10);
            $html='<table border="0" width="100%">
                    <tr>';
                    if(isset($system_logo) && $system_logo != '')
                    {
                        $html .='<td width="20%" align="left">'.$img.'</td>';
                    }
                        $html .='<td width="60%" style="font-size:12px;font-weight:bold;text-align:center;">'.$company.'</td>';
                    if(isset($system_company_name) && $system_company_name != '')
                    {
                        $html.='<td width="20%" style="font-size:10px;font-weight:bold;text-align:right;">'.$system_company_name.'</td>';
                    }
                    $html.='</tr>
                    <tr>';
                        $html .='<td width="100%" style="font-size:14px;font-weight:bold;text-align:center;">COMMISSION STATEMENT</td>';
                    $html .='</tr>
                    <tr>';
                        $html .='<td width="100%" style="font-size:12px;font-weight:bold;text-align:center;">'.$payroll_date.'</td>';
                    $html .='</tr>
            </table>';
        $pdf->writeHTML($html, false, 0, false, 0);
        $pdf->Ln(2);
            
        $pdf->SetFont('times','B',12);
        $pdf->SetFont('times','',10);
        $html='<table>';
        $html.='<tr style="background-color: #f1f1f1;">
                    <td width="10%" style="text-align:center;"><h5>TRADE DATE#</h5></td>
                    <td width="15%" style="text-align:center;"><h5>CLIENT</h5></td>
                    <td width="15%" style="text-align:center;"><h5>INVESTMENT</h5></td>
                    <td width="5%" style="text-align:center;"><h5>B/S</h5></td>
                    <td width="9%" style="text-align:center;"><h5>INVESTMENT AMOUNT</h5></td>
                    <td width="9%" style="text-align:center;"><h5>GROSS COMMISSION</h5></td>
                    <td width="9%" style="text-align:center;"><h5>CLEARING CHARGE</h5></td>
                    <td width="9%" style="text-align:center;"><h5>NET COMMISSION</h5></td>
                    <td width="9%" style="text-align:center;"><h5>RATE</h5></td>
                    <td width="10%" style="text-align:center;"><h5>BROKER COMMISSION</h5></td>
                </tr>
                <br/>';
        $html.='<tr>
                    <td style="font-size:11px;font-weight:cold;text-align:center;" colspan="10">No Records Found.</td>
                </tr>';
        $html.='</table>';
        $pdf->writeHTML($html, false, 0, false, 0);
        $pdf->Ln(2);
    }         
    
    if(isset($_GET['open']) && $_GET['open'] == 'output_print')
    {
        $pdf->IncludeJS("print();");
    }
    $pdf->lastPage();
    
    if($pdf_for_broker == 1)
    {
        $pdf->Output('report_payroll_broker_statement.pdf', 'D');
    }
    else
    {
        $pdf->Output('report_payroll_broker_statement.pdf', 'I');
    }
    exit;
?>