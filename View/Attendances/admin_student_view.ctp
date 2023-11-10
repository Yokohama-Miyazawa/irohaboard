<?php echo $this->element('admin_menu');?>
<?php echo $this->Html->css('attendance.css?20231109');?>
<?php
function getAttendanceStringAndStyle($attendance_datum)
{
   if(date('Y-m-d') > $attendance_datum["Date"]["date"]){
      switch($attendance_datum["Attendance"]["status"]){
         case 0:
            return [
               "string" => "欠席",
               "text-class" => "text-danger",
               "font-weight"  => "normal",
            ];
         case 2:
            return [
               "string" => "欠席(連絡なし)",
               "text-class" => "text-danger",
               "font-weight" => "bold",
            ];
         default:
            return [
               "string" => "出席",
               "text-class" => "text-primary",
               "font-weight" => "normal",
            ];
      }
   }else{
      switch($attendance_datum["Attendance"]["status"]){
         case 0:
            return [
               "string" => "欠席予定",
               "text-class" => "text-danger",
               "font-weight" => "normal",
            ];
         case 3:
            return [
               "string" => "遅刻予定",
               "text-class" => "text-success",
               "font-weight" => "normal",
            ];
         case 4:
            return [
               "string" => "早退予定",
               "text-class" => "text-success",
               "font-weight" => "normal",
            ];
         default:
            return [
               "string" => "出席予定",
               "text-class" => "text-primary",
               "font-weight" => "normal",
            ];
      }
   }
}
?>

<div class="row">
   <div class="col"><?php echo $this->Html->link(__('<< 戻る'), array('action' => 'index'))?></div>
</div>

<div class="container-fluid" style="padding-top:1em;">
	<div class="row">
	   <div class="col"><h3><?php echo __("出欠記録");?></h3></div>
	</div>

   <div class = "student-view">
      <div class = "student-name">
         <?php echo h($user_info["username"]);?><br/>
         <?php echo h($user_info["name"]);?><br/>
         <?php echo h($user_info["grade"]);?>
      </div>
      <div class = "student-photo">
         <?php
            $pic_path = $user_info["pic_path"] == "" ? "student_img/noPic.png" : $user_info["pic_path"];
            $img_src = $this->Image->makeInlineImage(Configure::read('student_img').$pic_path);
         ?>
      <img src="<?php echo $img_src; ?>" height="150" alt="<?php echo h($user_info["pic_path"]) ?>"/>
      </div>
   </div>

   <div class = "record-table">
	   <table cellpadding="0" cellspacing="0">
         <thead>
			   <tr>
               <th nowrap>授業日</th>
               <th nowrap>出欠</th>
               <th nowrap>理由</th>
            </tr>
         </thead>
         <tbody>
            <?php foreach($attendance_data as $datum):?>
            <?php $attendance_info = getAttendanceStringAndStyle($datum);?>
			   <tr>
               <td nowrap><?php echo h($datum["Date"]["date"]);?></td>
               <td nowrap class="<?php echo h($attendance_info["text-class"]); ?>" style="font-weight:<?php echo h($attendance_info["font-weight"]); ?>">
                  <?php echo h($attendance_info["string"]);?>
               </td>
               <td nowrap><?php echo h($datum["Attendance"]["reason"]);?></td>
            </tr>
            <?php endforeach;?>
         </tbody>
      </table>
   </div>
</div>
