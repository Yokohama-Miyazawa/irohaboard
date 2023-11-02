<?php echo $this->element('admin_menu');?>
<?php echo $this->Html->css('custom');?>
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

  function drawChart(attended_count, absent_count, atended_member, absent_member, element) {
    let data = new google.visualization.DataTable();
    data.addColumn('string', 'Status');
    // Use custom HTML content for the domain tooltip.
    data.addColumn('number', 'Number');
    data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});

    const attended_students = attended_count >= 10 ? "" : "<p style='font-size : 20px; white-space : nowrap;'>" + atended_member + "</p>";
    const absent_students = "<p style='font-size : 20px; white-space : nowrap;'>" + absent_member + "</p>";
    data.addRows([
      ['出席数',attended_count, attended_students],
      ['欠席数',absent_count, absent_students],
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
      <?php echo __('出席状況'); ?>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <h5><?php echo __('授業日:').$last_day;?></h5>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <p class="text-center" style="font-size:18px;">１限 人数: <?php echo ($period_1_submitted['Count'] + $period_1_unsubmitted['Count']);?>人, 出席: <?php echo ($period_1_submitted['Count']);?>人, 欠席: <?php echo ($period_1_unsubmitted['Count']);?>人</p>
    </div>
    <div class="col">
      <p class="text-center" style="font-size:18px;">２限 人数: <?php echo ($period_2_submitted['Count'] + $period_2_unsubmitted['Count']);?>人, 出席: <?php echo ($period_2_submitted['Count']);?>人, 欠席: <?php echo ($period_2_unsubmitted['Count']);?>人</p>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <div class="card" id="chart" onclick="location.href='<?php echo Router::url(array('controller' => 'attendances', 'action' => 'index')) ?>'">
        <div class="card-body" id="chart-body">
          <div class="pie-chart" id="period1Chart"></div>
          <div class="labelOverlay">
            <p class="total-caption">一限受講生</p>
            <p class="total-value"><?php echo ($period_1_submitted['Count'] + $period_1_unsubmitted['Count']);?>人</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card" id="chart" onclick="location.href='<?php echo Router::url(array('controller' => 'attendances', 'action' => 'index', '#' => '2nd')) ?>'">
        <div class="card-body" id="chart-body">
          <div class="pie-chart" id="period2Chart"></div>
          <div class="labelOverlay">
            <p class="total-caption">二限受講生</p>
            <p class="total-value"><?php echo ($period_2_submitted['Count'] + $period_2_unsubmitted['Count']);?>人</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
