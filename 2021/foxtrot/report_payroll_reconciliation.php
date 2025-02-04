<?php 
require_once("include/config.php");
require_once(DIR_FS."islogin.php");
$instance = new transaction();
$get_reconci_data = array();
$get_logo = $instance->get_system_logo();
$system_logo = isset($get_logo['logo'])?$instance->re_db_input($get_logo['logo']):'';
$get_company_name = $instance->get_company_name();
$system_company_name = isset($get_company_name['company_name'])?$instance->re_db_input($get_company_name['company_name']):'';

$instance_payroll = new payroll();
$filter_array = array();

//filter payroll reconciliation report
if(isset($_GET['filter']) && $_GET['filter'] != '')
{
    $filter_array = json_decode($_GET['filter'],true);
    $product_category = isset($filter_array['product_category'])?$filter_array['product_category']:0;
    // 11/23/21 Payroll ID passed instead of 'payroll_date' from the form submit
    $payroll_id = isset($filter_array['payroll_id'])?$filter_array['payroll_id']:'';
    $get_payroll_upload = $instance_payroll->get_payroll_uploads($payroll_id);
    $payroll_date = date('m/d/Y', strtotime($get_payroll_upload['payroll_date']));
    $output_type = isset($filter_array['output_type'])?$filter_array['output_type']:'';

    $get_reconci_data = $instance_payroll->get_reconciliation_report_data($product_category,$payroll_id);
}
?>
<?php

    // create new PDF document
    $pdf = new RRPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    // add a page
    $pdf->AddPage('L');
    // Title
    $img = '<img src="'.SITE_URL."upload/logo/".$system_logo.'" height="25px" />';
    
    $pdf->SetFont('times','B',12);
    $pdf->SetFont('times','',10);
    $html='<table border="0" width="100%">
                <tr>';
                if(isset($system_logo) && $system_logo != '')
                {
                    $html .='<td width="20%" align="left">'.$img.'</td>';
                }
                    $html .='<td width="60%" style="font-size:14px;font-weight:bold;text-align:center;">PAYROLL BATCH REPORT</td>';
                if(isset($system_company_name) && $system_company_name != '')
                {
                    $html.='<td width="20%" style="font-size:10px;font-weight:bold;text-align:right;">'.$system_company_name.'</td>';
                }
                $html.='</tr>
                <tr>';
                    $html .='<td width="100%" style="font-size:12px;font-weight:bold;text-align:center;">ALL BATCH GROUPS</td>';
                $html .='</tr>';
                $html .='<tr>';
                $html .='   <td width="100%" style="font-size:12px;font-weight:bold;text-align:center;">'.$payroll_date.'</td>';
                $html .='</tr>
        </table>';
    $pdf->writeHTML($html, false, 0, false, 0);
    $pdf->Ln(5);
    
    $pdf->SetFont('times','B',12);
    $pdf->SetFont('times','',10);
    $html='<table border="0" cellpadding="1" width="100%">
                <tr style="background-color: #f1f1f1;">
                    <td width="10%" style="text-align:center;"><h5>BATCH#</h5></td>
                    <td width="10%" style="text-align:center;"><h5>BATCH DATE</h5></td>
                    <td width="25%" style="text-align:center;"><h5>STATEMENT \ DESCRIPTION</h5></td>
                    <td width="10%" style="text-align:center;"><h5>TRADE COUNT</h5></td>
                    <td width="9%" style="text-align:center;"><h5>GROSS COMMISION</h5></td>
                    <td width="9%" style="text-align:center;"><h5>HOLD COMMISION</h5></td>
                    <td width="9%" style="text-align:center;"><h5>TOTAL COMMISSION</h5></td>
                    <td width="9%" style="text-align:center;"><h5>CHECK AMOUNT</h5></td>
                    <td width="9%" style="text-align:center;"><h5>DIFFERENCE</h5></td>
                </tr>
                <br/>';
    if($get_reconci_data != array())
    {
        $report_trade_count_total = 0;
        $report_gross_commission_total = 0;
        $report_hold_commission_total = 0;
        $report_total_commission_total = 0;
        $report_check_amount_total = 0;
        $report_difference_total = 0;
        foreach($get_reconci_data as $recon_key=>$recon_data)
        {
            $html.='<tr>
                   <td colspan="11" style="font-size:8px;font-weight:bold;text-align:left;">'.strtoupper($recon_key).'</td>
            </tr>
            <br/>';
            
            $category_trade_count = 0;
            $category_gross_commission = 0;
            $category_hold_commission = 0;
            $category_total_commission = 0;
            $category_check_amount = 0;
            $category_difference = 0;
            foreach($recon_data as $recon_sub_key=>$recon_sub_data)
            {
                $difference = $recon_sub_data['batch_check_amount']-$recon_sub_data['total_commission'];
                $category_trade_count = $category_trade_count+$recon_sub_data['trade_count'];
                $category_gross_commission = $category_gross_commission+$recon_sub_data['gross_commission'];
                $category_hold_commission = $category_hold_commission+$recon_sub_data['total_hold_commission'];
                $category_total_commission = $category_total_commission+$recon_sub_data['total_commission'];
                $category_check_amount = $category_check_amount+$recon_sub_data['batch_check_amount'];
                $category_difference = $category_difference+$difference;
                     
                $html.='<tr>
                   <td style="font-size:8px;font-weight:normal;text-align:center;">'.$recon_sub_data['batch_number'].'</td>
                   <td style="font-size:8px;font-weight:normal;text-align:center;">'.date('m/d/Y',strtotime($recon_sub_data['batch_date'])).'</td>
                   <td style="font-size:8px;font-weight:normal;text-align:center;">'.$recon_sub_data['batch_description'].'</td>
                   <td style="font-size:8px;font-weight:normal;text-align:center;">'.$recon_sub_data['trade_count'].'</td>
                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($recon_sub_data['gross_commission'],2).'</td>
                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($recon_sub_data['total_hold_commission'],2).'</td>
                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($recon_sub_data['total_commission'],2).'</td>
                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($recon_sub_data['batch_check_amount'],2).'</td>
                   <td style="font-size:8px;font-weight:normal;text-align:right;">'.number_format($difference,2).'</td>
                </tr>';
        
            }
            $report_trade_count_total = $report_trade_count_total+$category_trade_count;
            $report_gross_commission_total = $report_gross_commission_total+$category_gross_commission;
            $report_hold_commission_total = $report_hold_commission_total+$category_hold_commission;
            $report_total_commission_total = $report_total_commission_total+$category_total_commission;
            $report_check_amount_total = $report_check_amount_total+$category_check_amount;
            $report_difference_total = $report_difference_total+$category_difference;
            $html.='<tr style="background-color: #f1f1f1;">
               <td style="font-size:8px;font-weight:bold;text-align:right;" colspan="3">* '.strtoupper($recon_key).' SUBTOTAL * </td>
               <td style="font-size:8px;font-weight:bold;text-align:center;">'.$category_trade_count.'</td>
               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_gross_commission,2).'</td>
               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_hold_commission,2).'</td>
               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_total_commission,2).'</td>
               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_check_amount,2).'</td>
               <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($category_difference,2).'</td>
            </tr>
            <br/>';
        }
        $html.='<tr style="background-color: #f1f1f1;">
           <td style="font-size:8px;font-weight:bold;text-align:right;" colspan="3">REPORT TOTAL: </td>
           <td style="font-size:8px;font-weight:bold;text-align:center;">'.$report_trade_count_total.'</td>
           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($report_gross_commission_total,2).'</td>
           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($report_hold_commission_total,2).'</td>
           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($report_total_commission_total,2).'</td>
           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($report_check_amount_total,2).'</td>
           <td style="font-size:8px;font-weight:bold;text-align:right;">$'.number_format($report_difference_total,2).'</td>
        </tr>
        <br/>';
         
    }
    else
    {
        $html.='<tr>
            <td style="font-size:11px;font-weight:cold;text-align:center;" colspan="9">No Records Found.</td>
        </tr>';
    }           
    $html.='</table>';
    $pdf->writeHTML($html, false, 0, false, 0);
    $pdf->Ln(5);
    
    if(isset($_GET['open']) && $_GET['open'] == 'output_print')
    {
        $pdf->IncludeJS("print();");
    }
    $pdf->lastPage();
    $pdf->Output('report_payroll_adjustment.pdf', 'I');
    
    exit;
?>