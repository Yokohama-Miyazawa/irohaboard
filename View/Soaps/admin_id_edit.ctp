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
<div><?php echo $this->Html->link(__('<< 戻る'), array('controller' => 'soaprecords', 'action' => 'index'))?></div>
<div class = "admin-student_edit-index">
  <div class = "ib-page-title"><?php echo __('SOAP編集')?></div>
  <br><br>
  <div class = "member-input">
    <div class = "info">
      <div class = "user_name">
        <td><?php echo h($user_info["name"]);?>&nbsp;</td>
      </div>
      <div class = "student-photo">
        <?php
          $img_src = $this->Image->makeInlineImage(Configure::read('student_img').$user_info["pic_path"]);
        ?>
        <img src="<?php echo $img_src; ?>" height="150" alt="<?php echo h($user_info["pic_path"]); ?>"/>
      </div>
    </div>
    <div class = "soap">
    <?php
			echo $this->Form->create("Soap");
			echo $this->Form->hidden('id', array('value' => $edited_soap['id']));
			echo $this->Form->hidden('user_id',array('value' => $edited_soap["user_id"]));
      ?>
      <div class = "soap_teacher">
      <?php
      echo $this->Form->input('group_id',array(
						'label' => __('担当講師：'),
						'div' => false,
						'class' => 'soap_teacher',
						'options' => $group_list,
						'empty' => '',
            'value' => $edited_soap["group_id"],
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
						'value' => $edited_soap['current_status'],
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
						'value' => $edited_soap["created"],
					));
      echo "</div>";
      ?>
      <?php

      echo "<div class = 'soap-input'>";
  		echo $this->Form->input('S',array(
				'label' => __('S:'),
				'value' => $edited_soap['S'],
  			'div' => false,
  			'class' => 'subject',
			'style' => '',
  		));
      echo "</div>";

      echo "<div class = 'soap-input'>";
      echo $this->Form->input('O',array(
				'label' => __('O:'),
				'value' => $edited_soap['O'],
  			'div' => false,
  			'class' => 'object',
  			'style' => '',
  		));
      echo "</div>";

      echo "<div class = 'soap-input'>";
      echo $this->Form->input('A',array(
				'label' => __('A:'),
				'value' => $edited_soap['A'],
  			'div' => false,
  			'class' => 'assessment',
  			'style' => '',
  		));
      echo "</div>";

      echo "<div class = 'soap-input'>";
      echo $this->Form->input('P',array(
				'label' => __('P:'),
				'value' => $edited_soap['P'],
  			'div' => false,
  			'class' => 'plan',
  			'style' => '',
  		));
      echo "</div>";

      echo "<div class = 'soap-input'>";
      echo $this->Form->input('comment',array(
				'label' => __('自由記述:'),
				'value' => $edited_soap['comment'],
  			'div' => false,
  			'class' => 'comment',
  			'style' => '',
  		));
      echo "</div>";
  	?>
    </div>
		<!--<div class = "enquete">
			<div class = "enquete_headline"><?php //echo __('今日の感想:');?></div>
			<?php //echo h($enquete_inputted[$user_id]['today_impressions']);?>
		</div>-->
  </div>
  <!--<div class = "under_element"></div>-->
  <?php
    echo $this->Form->button("更新", [
      'type' => 'button',
      'onclick' => 'submit()',
      'class' => 'btn btn-primary btn-add pull-right',
    ]);
  ?>
  <?php echo $this->Form->end(); ?>
  <br>
  <?php
    echo $this->Form->postLink(__('削除'),
      array(
        'action' => 'delete',
        $edited_soap['id'],
      ),
      array(
        'class' => 'btn btn-danger btn-delete',
        'style' => 'margin-top:20px; font-size: 110%;',
      ),
      'このSOAPを削除してもよろしいですか?'
    );
  ?>
</div>
