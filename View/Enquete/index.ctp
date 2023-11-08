<?php echo $this->element('menu');?>
<?php echo $this->Html->css('enquete');?>
<?php echo $this->Html->css('image-selector');?>
<?php echo $this->Html->script('image-selector');?>
<div class="enquete-input-header full-view">
  <h1 class="text-center mb-3"><?php echo __('アンケート'.'('.$today.')'.'記入')?></h1>
  <div class="col-sm-12 col-md-8 rounded shadow-lg mx-auto py-5 mb-5">
  <?php
    //echo $this->Form->create("enquete-input",array('novalidate' => true));
    echo $this->Form->create(false,['type' => 'post','url'=> ['controller' => 'enquete','action' => 'index'],'novalidate' => true]);
		echo $this->Form->hidden('group_id',array('value' => $group_id));
  ?>

  <div class="form-input-block enquete-input-group required-input">
    <label for="group-select">個別指導の担当講師：</label>
    <div id="group-select" >
      <?php foreach($group_leaders as $leader):
        $pic_path = $leader["pic_path"] == "" ? "student_img/noPic.png" : h($leader["pic_path"]);
        $leader_img = $this->Image->makeInlineImage(Configure::read('student_img').$pic_path);
      ?>
        <div class="ImageSelector__option demo-flex" data-value="<?php echo $leader["id"]; ?>">
          <img src="<?php echo $leader_img;?>" alt="<?php echo h($leader["title"]); ?>" class="group-leader-face">
          <?php echo h($leader["title"]); ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <script>
    const group_image_selector = new ImageSelector(document.getElementById('group-select'));
    group_image_selector.setValue('<?php echo $group_id; ?>');
    group_image_selector.setOnChange(() => {
      document.getElementById('group_id').value = group_image_selector.value;
    });
  </script>

  <?php
  echo "<div class = 'form-input-block before-goal-block'>";
  echo $this->Form->input('before_goal_cleared',	array(
    'type' => 'radio',
    'before' => '<label class = "before-goal-label">前回に設定したゴールは達成できましたか？</label>',
    'after'  => '<div class = "text-url-input"></div><span class="status-exp">前回設定したゴール: '.h($previous_next_goal).'</span>',
    'separator'=>"  ",
    'legend' => false,
    'div' => '',
    'class' => '',
    'style' => '',
    'required'=> 'required',
    'options' => Configure::read('true_or_false'),
    'value' => $enquete_inputted['Enquete']['before_goal_cleared']
  ));
  echo "</div>";

  echo "<div class = 'form-input-block before-goal-false-reason' >";
  echo $this->Form->input('before_false_reason',array(
    'label' => __('前回に設定したゴールが達成できなかった場合は、その理由を書いてください'),
    'type' => 'textarea',
    'div' => false,
    'class' => '',
    'style' => '',
    'value' => $enquete_inputted['Enquete']['before_false_reason']
  ));
  echo "</div>";

  echo "<div class = 'form-input-block today-goal required-input ' >";
  echo $this->Form->input('today_goal',array(
    'label' => __('今日の授業のゴールを書いてください'),
    'type' => 'textarea',
    'div' => false,
    'class' => '',
    'required'=> 'required',
    'style' => '',
    'value' => $enquete_inputted['Enquete']['today_goal']
  ));
  echo "</div>";


  echo "<div class = 'form-input-block today-goal-cleared  ' >";
  echo $this->Form->input('today_goal_cleared',array(
    'before' => '<label class = "before-goal-label">今日の授業のゴールは達成できましたか？(できた人はTrue、そうでない人はFalseを選んでください)</label></br>',
    'type' => 'radio',
    'separator'=>"  ",
    'legend' => false,
    'div' => '',
    'class' => '',
    'style' => '',
    'required'=> 'required',
    'options' => Configure::read('true_or_false'),
    'value' => $enquete_inputted['Enquete']['today_goal_cleared']
  ));
  echo "</br>";
  echo "</div>";

  echo "<div class = 'form-input-block today-false-reason' >";
  echo $this->Form->input('today_false_reason',array(
    'label' => __('今日の授業のゴールが達成できなかった場合は、その理由を書いてください'),
    'type' => 'textarea',
    //'before' => '<label class="col col-sm-3 control-label">コンテンツ種別</label>',
    'after' => '<div class = "text-url-input"></div><span class="status-exp">なぜできなかった、何がわからなかった、など</span></br>',
    'div' => false,
    'class' => '',
    'style' => '',
    'value' => $enquete_inputted['Enquete']['today_false_reason']
  ));
  echo "</div>";

  echo "<div class = 'form-input-block next-goal '>";
  echo $this->Form->input('next_goal',array(
    'label' => __('次回の授業に来る時までに達成するゴールを書いてください'),
    'type' => 'textarea',
    //'before' => '<label class="col col-sm-3 control-label">コンテンツ種別</label>',
    'after' => '<div class = "text-url-input"></div><span class="status-exp">プロジェクトのある機能を実現する、あるスキルをマスターする、など</span></br>',
    'div' => false,
    'class' => '',
    'required'=> 'required',
    'style' => '',
    'value' => $enquete_inputted['Enquete']['next_goal']
  ));
  echo "</div>";

  echo "<div class = 'form-input-block today-impressions ' >";
  echo $this->Form->input('today_impressions',array(
    'label' => __('今日の感想を書いてください'),
    'type' => 'textarea',
    //'before' => '<label class="col col-sm-3 control-label">コンテンツ種別</label>',
    'after' => '<div class = "text-url-input"></div><span class="status-exp">今日やったこと、躓いたこと、勉強になったこと、解決できなかったこと、など</span></br>',
    'div' => false,
    'class' => '',
    'required'=> 'required',
    'style' => '',
    'value' => $enquete_inputted['Enquete']['today_impressions']
  ));
  echo "</div>"
;  ?>
  <input type = "submit" class = "btn btn-primary btn-add" value = "送信">
  <?php echo $this->Form->end(); ?>
  </div>
</div>
