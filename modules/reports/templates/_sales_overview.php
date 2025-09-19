<?php // modules/reports/templates/_sales_overview.php ?>
<div class="row">
    <div class="col-md-12">
        <h4>Monthly Sales</h4>
        <div style="height: 300px;"><canvas id="monthlySalesChart"></canvas></div>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-md-6">
        <h4>Top 5 SKUs by Sales</h4>
        <div style="height: 300px;"><canvas id="topSkusChart"></canvas></div>
    </div>
    <div class="col-md-6">
        <?php if (!empty($data['top_dealers'])): ?>
        <h4>Top 5 Dealers by Sales</h4>
        <div style="height: 300px;"><canvas id="topDealersChart"></canvas></div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Sales Chart
    new Chart(document.getElementById('monthlySalesChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($data['monthly_sales']['labels'] ?? []); ?>,
            datasets: [{
                label: 'Total Sales',
                data: <?php echo json_encode($data['monthly_sales']['data'] ?? []); ?>,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        }
    });

    // Top SKUs Chart
    new Chart(document.getElementById('topSkusChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($data['top_skus']['labels'] ?? []); ?>,
            datasets: [{
                label: 'Sales',
                data: <?php echo json_encode($data['top_skus']['data'] ?? []); ?>
            }]
        }
    });

    // Top Dealers Chart
    <?php if (!empty($data['top_dealers'])): ?>
    new Chart(document.getElementById('topDealersChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($data['top_dealers']['labels'] ?? []); ?>,
            datasets: [{
                label: 'Sales',
                data: <?php echo json_encode($data['top_dealers']['data'] ?? []); ?>,
                backgroundColor: 'rgba(153, 102, 255, 0.6)'
            }]
        },
        options: { indexAxis: 'y' }
    });
    <?php endif; ?>
});
</script>