<?php echo $this->element('admin_menu');?>
<?php echo $this->Html->css('soap');?>
<?php echo $this->Html->script('custom.js');?>
<script>
$(function(){
  setInputLengthChecker("<?php echo "#".$user_id."S"; ?>", <?php echo h($input_max_length); ?>);
  setInputLengthChecker("<?php echo "#".$user_id."O"; ?>", <?php echo h($input_max_length); ?>);
  setInputLengthChecker("<?php echo "#".$user_id."A"; ?>", <?php echo h($input_max_length); ?>);
  setInputLengthChecker("<?php echo "#".$user_id."P"; ?>", <?php echo h($input_max_length); ?>);
  setInputLengthChecker("<?php echo "#".$user_id."Comment"; ?>", <?php echo h($input_max_length); ?>);
});
</script>
<div><?php echo $this->Html->link(__('<< 戻る'), array('action' => 'find_by_student'))?></div>
<div class = "admin-student_edit-index">
  <div class = "ib-page-title"><?php echo __('個別記入')?></div>
  <br><br>
  <?php //$this->log($members);?>
  <div class = "member-input">
    <div class = "info">
      <div class = "user_name">
        <td><?php echo h($user_list[$user_id]);?>&nbsp;</td>
      </div>
      <div class = "student-photo">
        <?php
          $img_src = $this->Image->makeInlineImage(Configure::read('student_img').$pic_path);
        ?>
        <img src="<?php echo $img_src; ?>" height="150" alt="<?php echo $pic_path ?>"/>
      </div>
    </div>
    <div class = "soap">
    <?php
			echo $this->Form->create("$user_id");
			echo $this->Form->hidden('id', array('value' => $soap_inputted[$user_id]['id']));
			echo $this->Form->hidden('user_id',array('value' => $user_id));
      ?>
      <div class = "soap_teacher">
      <?php
      echo $this->Form->input('group_id',array(
						'label' => __('担当講師：'),
						'div' => false,
						'class' => 'soap_teacher',
						'options' => $group_list,
						'empty' => '',
            'value' => $group_id,
						'style' => ''
					 ));
      ?>
      </div>
      <?php
      echo "<div class = 'soap_current'>";
      echo $this->Form->input('current_status',array(
						'label' => __('現状：'),
						'div' => false,
						'class' => 'soap_select',
						'options' => $course_list,
						'value' => $soap_inputted[$user_id]['current_status'],
						'empty' => '',
						'style' => ''
					));
      echo "</div>";
      echo "<div class = 'under-element'></div>";
      echo "<div class = 'soap-time'>";
      echo $this->Form->input('today_date',array(
						'type' => 'date',
						'dateFormat' => 'YMD',
						'monthNames' => false,
						'timeFormat' => '24',
						'div' => false,
						'minYear' => date('Y') - 1,
						'maxYear' => date('Y'),
						'separator' => ' / ',
						'label' => '記入日：',
						'class' => 'soap_select',
						'style' => '',
						'value' => $today_date
					));
      echo "</div>";
      ?>
      <?php

      echo "<div class = 'soap-input'>";
  		echo $this->Form->input('S',array(
				'label' => __('S:'),
				'value' => $soap_inputted[$user_id]['S'],
  			'div' => false,
  			'class' => 'subject',
			'style' => '',
  		));
      echo "</div>";

      echo "<div class = 'soap-input'>";
      echo $this->Form->input('O',array(
				'label' => __('O:'),
				'value' => $soap_inputted[$user_id]['O'],
  			'div' => false,
  			'class' => 'object',
  			'style' => '',
  		));
      echo "</div>";

      echo "<div class = 'soap-input'>";
      echo $this->Form->input('A',array(
				'label' => __('A:'),
				'value' => $soap_inputted[$user_id]['A'],
  			'div' => false,
  			'class' => 'assessment',
  			'style' => '',
  		));
      echo "</div>";

      echo "<div class = 'soap-input'>";
      echo $this->Form->input('P',array(
				'label' => __('P:'),
				'value' => $soap_inputted[$user_id]['P'],
  			'div' => false,
  			'class' => 'plan',
  			'style' => '',
  		));
      echo "</div>";

      echo "<div class = 'soap-input'>";
      echo $this->Form->input('comment',array(
				'label' => __('自由記述:'),
				'value' => $soap_inputted[$user_id]['comment'],
  			'div' => false,
  			'class' => 'comment',
  			'style' => '',
  		));
      echo "</div>";
  	?>
    </div>
		<div class = "enquete">
			<div class="enquete_headline">アンケート内容</div>
			<div class="card today_impressions">
				<div class = "card-header"><?php echo __('今日の感想');?></div>
				<div class="card-body"><?php echo h($enquete_inputted[$user_id]['today_impressions']);?></div>
			</div>
			<div class="card today_goal">
				<div class = "card-header">
					<?php
						$cleared_or_not = $enquete_inputted[$user_id]['today_goal_cleared'] ? "達成できた" : "達成できなかった";
						echo h('今日の授業のゴール（' . $cleared_or_not .'）');
					?>
				</div>
				<div class="card-body"><?php echo h($enquete_inputted[$user_id]['today_goal']);?></div>
			</div>
			<?php if(!$enquete_inputted[$user_id]['today_goal_cleared']){?>
			<div class="card today_false_reason">
				<div class="card-header"><?php echo __('今日のゴールが達成できなかった理由');?></div>
				<div class="card-body"><?php echo h($enquete_inputted[$user_id]['today_false_reason']);?></div>
			</div>
			<?php }?>
		</div>
  </div>
  <div class = "under_element"></div>
  <input type = "submit" class = "btn btn-primary btn-add pull-right" value = "登録">
  <?php echo $this->Form->end(); ?>
</div>
