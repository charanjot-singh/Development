<?php
// 05/03/22 Renamed from "ajax_hold_commissions.php - renamed to make more sense
//    * Called from transactions.tpl.php
require_once("include/config.php");
require_once(DIR_FS."islogin.php");

if((isset($_GET['broker_id']) && $_GET['broker_id'] != '')
    || (isset($_GET['action']) && $_GET['action']=='branch_company' && !empty($_GET['branch']))
){
   if(isset($_GET['action']) && $_GET['action'] == 'split_commission'){
      $broker_class = new broker_master();
      $select_broker =$broker_class->select_broker();
      $product_category = $broker_class->select_category();
      $doc_id2=0;
      $transaction_id= !empty($_GET['transaction_id']) ? $_GET['transaction_id']: 0;

      if(!$transaction_id)
         $edit_override = $broker_class->edit_override($_GET['broker_id']);
      else
         $edit_override = $broker_class->load_split_commission($transaction_id);

         foreach($edit_override as $regkey=>$regval){  $doc_id2++;?>
            <tr class="tr" data-rowid="<?php  echo $regval['id'] ?>">
               <td>
                  <input type="hidden" name="override[row_id][<?php echo $doc_id2;?>]" value="<?php  echo  $transaction_id ==0 ? "" : $regval['id']; ?>" />
                  <select name="override[receiving_rep1][<?php echo $doc_id2;?>]"  class="form-control">
                     <option value="">Select Broker</option>
                     <?php foreach($select_broker as $key => $val) {
                        if($val['id'] != $id){?>
                           <option <?php if(isset($regval['rap']) && $regval['rap']==$val['id']) {?>selected="true"<?php } ?> value="<?php echo $val['id']?>"><?php echo $val['first_name'].' '.$val['last_name']?></option>
                        <?php } } ?>
                  </select>
               </td>
               <td>
                  <div class="input-group">
                     <input type="number" step="0.001" onchange="handleChange(this);" name="override[per1][<?php echo $doc_id2;?>]" value="<?php echo $regval['per'];?>" class="form-control" /><span class="input-group-addon">%</span>
                  </div>
               </td>
               <td>
                  <div id="demo-dp-range">
                     <div class="input-daterange input-group" id="datepicker">
                        <input type="text" name="override[from1][<?php echo $doc_id2;?>]" value="<?php echo date('m/d/Y',strtotime($regval['from'])); ?>" class="form-control"  />
                        <label class="input-group-addon btn" for="override[from1][<?php echo $doc_id2;?>]">
                           <span class="fa fa-calendar"></span>
                        </label>
                     </div>
                  </div>
               </td>
               <td>
                  <div id="demo-dp-range">
                     <div class="input-daterange input-group" id="datepicker">
                        <input type="text" name="override[to1][<?php echo $doc_id2;?>]" value="<?php echo date('m/d/Y',strtotime($regval['to'])); ?>" class="form-control"  />
                        <label class="input-group-addon btn" for="override[to1][<?php echo $doc_id2;?>]">
                           <span class="fa fa-calendar"></span>
                        </label>
                     </div>
                  </div>
               </td>
               <td>
                  <select name="override[product_category1][<?php echo $doc_id2;?>]"  class="form-control">
                     <option value="">Select Category</option>
                     <option value="0" <?php if(isset($regval['product_category']) && $regval['product_category']=='0'){?> selected="true"<?php } ?>>All Product Categories</option>
                     <?php foreach($product_category as $key => $val) {?>
                        <option <?php if(isset($regval['product_category']) && $regval['product_category']==$val['id']) {?>selected="true"<?php } ?> value="<?php echo $val['id'];?>"><?php echo $val['type'];?></option>
                     <?php } ?>
                  </select>
               </td>
               <td>
                  <button type="button" tabindex="-1" class="btn remove-row btn-icon btn-circle"><i class="fa fa-minus"></i></button>
               </td>
            </tr>
         <?php }
            $doc_id2++ ; ?>

         <tr id="add_rate">
            <td>
              <select name="override[receiving_rep1][<?php echo $doc_id2;?>]"  class="form-control">
                 <option value="">Select Broker</option>
                 <?php foreach($select_broker as $key => $val) {
                    if($val['id'] != $id){?>
                 <option value="<?php echo $val['id']?>"><?php echo $val['first_name'].' '.$val['last_name']?></option>
                 <?php } } ?>
              </select>
            </td>
               <td>
               <div class="input-group">
                  <input type="number" onchange="handleChange(this);" step="0.001" name="override[per1][<?php echo $doc_id2;?>]" value="" class="form-control" /><span class="input-group-addon">%</span>
               </div>
            </td>
            <td>
               <div id="demo-dp-range">
                  <div class="input-daterange input-group" id="datepicker">
                     <input type="text" name="override[from1][<?php echo $doc_id2;?>]" class="form-control"  />
                     <label class="input-group-addon btn" for="override">
                     <span class="fa fa-calendar"></span>
                     </label>
                 </div>
               </div>
            </td>
            <td>
               <div id="demo-dp-range">
                  <div class="input-daterange input-group" id="datepicker">
                    <input type="text" name="override[to1][<?php echo $doc_id2;?>]" class="form-control"  />
                    <label class="input-group-addon btn" for="override">
                    <span class="fa fa-calendar"></span>
                    </label>
                  </div>
               </div>
            </td>
            <td>
               <select name="override[product_category1][<?php echo $doc_id2;?>]"  class="form-control">
                 <option value="">Select Category</option>
                 <option value="0">All Categories</option>
                 <?php foreach($product_category as $key => $val) {?>
                 <option value="<?php echo $val['id'];?>"><?php echo $val['type'];?></option>
                 <?php } ?>
               </select>
            </td>
            <td>
              <button type="button" onclick="add_rate(<?php echo $doc_id2;?>);" class="btn btn-purple btn-icon btn-circle"><i class="fa fa-plus"></i></button>
            </td>
         </tr>
   <?php
   //--- onChange(branch) -> Update Company dropdown
   } else if(isset($_GET['action']) AND $_GET['action']=='branch_company' AND !empty($_GET['branch'])) {
      $return = ["id"=>0, "company"=>0];
      $instance_branch = new branch_maintenance();
      $branchData = $instance_branch->select_branch_by_id((int)$_GET['branch']);

      if (!empty($branchData)){
         $return = ["id"=>(int)$branchData['id'],"company"=>(int)$branchData['company']];
      }

      echo json_encode($return);

   //--- Hold Commissions & Branch/Company return
   } else {
      $return = ['hold_commission'=>'', 'broker_id'=>$_GET['broker_id'], 'branch'=>0, 'company'=>0];
      $broker_class = new broker_master();
      $check_broker_commission = $broker_class->check_broker_commission_status($_GET['broker_id']);
      if (count($check_broker_commission)){
         $return['hold_commission'] = $check_broker_commission['hold_commissions'];
      }

      // 05/02/22 Branch & company array
      $instance_broker_master = new broker_master();
      $get_broker =$instance_broker_master->select_broker_by_branch_company($_GET['broker_id']);
      if (count($get_broker) AND (int)$get_broker[0]['branch_id1']!=0){
         $return['branch'] = (int)$get_broker[0]['branch_id1'];
      }
      if (count($get_broker) AND (int)$get_broker[0]['company_id1']!=0){
         $return['company'] = (int)$get_broker[0]['company_id1'];
      }

      echo json_encode($return);
    }

}
?>