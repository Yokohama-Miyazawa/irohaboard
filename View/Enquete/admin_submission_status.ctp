<?php echo $this->element('admin_menu');?>
<?php echo $this->Html->css('custom');?>
<?php echo $this->Html->css('enquete');?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script type="text/javascript">
  google.charts.load("current", {packages:["corechart"]});
  google.charts.setOnLoadCallback(() => {
    drawChart(
      <?php echo $period_1_submitted['Count'];?>,
      <?php echo $period_1_unsubmitted['Count'];?>,
      "<?php echo $period_1_submitted['Member'];?>",
      "<?php echo $period_1_unsubmitted['Member'];?>",
      "period1Chart"
    )
  });
  google.charts.setOnLoadCallback(() => {
    drawChart(
      <?php echo $period_2_submitted['Count'];?>,
      <?php echo $period_2_unsubmitted['Count'];?>,
      "<?php echo $period_2_submitted['Member'];?>",
      "<?php echo $period_2_unsubmitted['Member'];?>",
      "period2Chart"
    )
  });

  function drawChart(submitted_count, unsubmitted_count, submitted_member, unsubmitted_member, element) {
    let data = new google.visualization.DataTable();
    data.addColumn('string', 'Status');
    // Use custom HTML content for the domain tooltip.
    data.addColumn('number', 'Number');
    data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});

    const submitted_students = submitted_count >= 10 ? "" : "<p style='font-size : 20px; white-space : nowrap;'>" + submitted_member + "</p>";
    const unsubmitted_students = "<p style='font-size : 20px; white-space : nowrap;'>" + unsubmitted_member + "</p>";
    data.addRows([
      ['提出数',submitted_count, submitted_students],
      ['未提出数',unsubmitted_count, unsubmitted_students]
    ])
    const options = {
      pieHole: 0.6,
      fontSize: 18,
      legend: { position: 'top', alignment: 'center' },
      'chartArea': {'width': '100%', 'height': '80%'},
      tooltip: { isHtml: true }
    }
    const chart = new google.visualization.PieChart(document.getElementById(element));
    chart.draw(data, options);
  }
</script>

<div class="admin-submission-status full-view">
  <div class="row">
    <div class="col" style = "font-size : 24px;">
      <?php echo __('アンケート提出状況'); ?>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <h5><?php echo __('授業日:').$last_day;?></h5>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <p class="text-center" style="font-size:18px;">１限 出席人数: <?php echo ($period_1_submitted['Count'] + $period_1_unsubmitted['Count']);?>人, 提出: <?php echo ($period_1_submitted['Count']);?>人, 未提出: <?php echo ($period_1_unsubmitted['Count']);?>人</p>
    </div>
    <div class="col">
      <p class="text-center" style="font-size:18px;">２限 出席人数: <?php echo ($period_2_submitted['Count'] + $period_2_unsubmitted['Count']);?>人, 提出: <?php echo ($period_2_submitted['Count']);?>人, 未提出: <?php echo ($period_2_unsubmitted['Count']);?>人</p>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <div class="card" id="chart" onclick="location.href='<?php echo Router::url(array('controller' => 'enquete', 'action' => 'index')) ?>'">
        <div class="card-body" id="chart-body">
          <div class="pie-chart" id="period1Chart"></div>
          <div class="labelOverlay">
            <p class="total-caption">一限出席者</p>
            <p class="total-value"><?php echo ($period_1_submitted['Count'] + $period_1_unsubmitted['Count']);?>人</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card" id="chart" onclick="location.href='<?php echo Router::url(array('controller' => 'enquete', 'action' => 'index')) ?>'">
        <div class="card-body" id="chart-body">
          <div class="pie-chart" id="period2Chart"></div>
          <div class="labelOverlay">
            <p class="total-caption">二限出席者</p>
            <p class="total-value"><?php echo ($period_2_submitted['Count'] + $period_2_unsubmitted['Count']);?>人</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
